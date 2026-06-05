# Le Moteur de Relations (Relation Engine)

Dans une architecture hautement abstraite (Data-Driven), dépendre de jointures SQL (Foreign Keys) statiques n'est pas soutenable pour créer de nouvelles entités No-Code à la volée. 
Zeus Core dispose de son propre moteur de résolution des relations applicatives, conçu pour être massivement scalable.

## 1. La Résolution sans Clés Étrangères Physiques

Puisque Zeus n'oblige pas l'implémentation de clés étrangères dans la structure de base de données (ce qui favorise un partitionnement noSQL ou une flexibilité des schémas), les relations (1:N, N:1) sont gérées en mémoire de manière "lazy-loaded" ou "eager-loaded" grâce au `RelationLoader`.

### L'Opérateur IN (Le contournement du N+1 Query Problem)

Le plus grand fléau des ORMs traditionnels est le fameux *N+1 Query Problem*, où chaque itération d'une collection déclenche une nouvelle requête à la base de données.

Notre `RelationLoader` extrait intelligemment toutes les clés étrangères d'une collection initiale. Il effectue ensuite **une seule et unique** requête vers le stockage de données cible en utilisant l'opérateur `IN`.

```php
// Pseudo-code d'architecture :
$foreignIds = array_unique(array_column($sourceRecords, 'user_id'));
$queryBuilder->whereIn('id', $foreignIds);
// Exécution d'un seul fetch global...
```
Une fois les résultats de la relation rapatriés en masse, un dictionnaire (Hash Map) est construit localement et les enregistrements sont hydratés (zippés) avec leurs relations correspondantes.

## 2. Immutabilité avec `withRelation()`

Dans le paradigme fonctionnel imposé par Zeus Core, un `EntityRecord` une fois généré par l'`EntityReader` ne doit jamais muter. 

Le `RelationLoader` n'altère pas les entités originales ; il génère un tout nouveau DTO `EntityRecord` incluant le tableau fusionné des relations à travers l'appel explicite de `withRelation()`.

```php
/**
 * @param string $relationName
 * @param array|null $relationData
 * @return self
 */
public function withRelation(string $relationName, ?array $relationData): self
{
    $newRelations = $this->relations;
    $newRelations[$relationName] = $relationData;

    // Retourne une nouvelle instance Readonly
    return new self($this->entity, $this->id, $this->data, $newRelations);
}
```

Cette immutabilité absolue élimine les problèmes de mémoire partagée entre les processus et sécurise le transport asynchrone des enregistrements (Serialization / Queues).
