# Le Moteur de Données (Data Engine)

Le Data Engine est le cœur névralgique de **Zeus Core**. Il agit en tant qu'orchestrateur strict entre la réception de la requête utilisateur (l'action), la validation métier, le contrôle de sécurité (ACL / Multi-Tenant), et l'exécution physique des opérations (Storage).

## 1. L'EntityManager : Le Chef d'Orchestre

La classe `EntityManager` garantit l'intégrité de toutes les opérations d'écriture (Create, Update, Delete). Aucune donnée ne peut être persistée dans l'ERP sans transiger par ce canal.

Lors d'une action d'écriture (ex: `create`), l'`EntityManager` :
1. **Valide le format des données** : S'assure que seules les colonnes déclarées dans le registre (`MetadataProviderInterface`) sont envoyées.
2. **Autorise la transaction** : Sollicite le `SecurityEnforcer` pour valider que le contexte actuel possède la permission `entité.create`.
3. **Injecte le périmètre Tenant** : Le `TenantEnforcer` force l'injection silencieuse des IDs du tenant (ex: `company_id`, `site_id`) directement dans le payload, assurant que la donnée appartiendra définitivement au bon propriétaire sans intervention du développeur.
4. **Délègue l'exécution** : Confie le payload sécurisé et final à l'`EntityStorageInterface`.

```php
// Extrait conceptuel :
$context = $this->contextResolver->resolve();
$this->securityEnforcer->authorize($context, $entity->code, 'create');
$enrichedPayload = $this->tenantEnforcer->enrichPayload($context, $payload);
return $this->storage->insert($entity, $enrichedPayload);
```

## 2. EntityQueryBuilder : L'AST Abstrait de Lecture

Tout comme les écritures, les requêtes de lecture doivent être contrôlées de manière absolue et sans faille quant à la frontière des données (isolation tenant).

L'`EntityQueryBuilder` n'interagit pas avec SQL. Il construit un **Abstract Syntax Tree (AST)** de la requête (via le DTO `EntityQuery`).

Lors de la méthode d'initiation `forEntity()`, le builder injecte silencieusement et de manière obligatoire les clauses restrictives liées au Tenant via le `TenantEnforcer` :

```php
public function forEntity(EntityMetadata $entity): self
{
    $this->query = new EntityQuery($entity);
    $context = $this->contextResolver->resolve();
    $this->securityEnforcer->authorize($context, $entity->code, 'read');
    
    // Auto-injection des barrières physiques du Locataire (Tenant isolation)
    $criteria = $this->tenantEnforcer->getReadCriteria($context);
    foreach ($criteria as $criterion) {
        $this->query->addCondition(new Condition(...));
    }
    
    return $this;
}
```

## 3. L'EntityReader et l'Immutabilité

L'exécution de la lecture et la récupération des résultats sont traitées par l'`EntityReader`, qui retourne une collection d'`EntityRecord`.

La classe `EntityRecord` est le DTO final et **read-only** représentant la ligne métier. Tout enrichissement ultérieur (comme le chargement de relations) retournera obligatoirement une **nouvelle instance** avec les attributs fusionnés, respectant le paradigme d'immutabilité du framework.
