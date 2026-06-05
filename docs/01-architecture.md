# Architecture & Inversion de Contrôle (IoC)

Le framework **Zeus Core** a été pensé dès le premier jour comme une fondation **agnostique**, pure et immuable. 
Conçu en PHP 8.4 strict, le noyau représente les règles d'affaires (Business Rules) absolues de notre ERP No-Code, sans aucune dépendance extérieure.

## 1. Agnosticisme et Indépendance

L'un des plus grands pièges dans le développement d'applications d'entreprise est le couplage fort avec l'infrastructure. 
**Zeus Core** ne connaît ni :
- **Laravel** (ou tout autre framework applicatif)
- **HTTP** (les requêtes, les réponses, les sessions)
- **SQL / Bases de données** (Eloquence, requêtes natives, schémas de base de données)

Cette isolation est primordiale. Si demain nous devons basculer de Laravel vers Symfony, ou passer d'une base de données relationnelle (MySQL/PostgreSQL) vers du NoSQL (MongoDB), les règles d'affaires de Zeus n'ont besoin d'aucune modification. Tout est encapsulé.

## 2. L'Inversion de Contrôle (IoC) au cœur du système

Pour parvenir à cet agnosticisme, l'ensemble du framework s'articule autour du principe d'Inversion de Contrôle (IoC). Le Noyau définit les contrats (Interfaces) que l'infrastructure externe devra respecter.

### L'Interface de Stockage (`EntityStorageInterface`)

Le noyau ne sauvegarde aucune donnée. Il délègue l'exécution physique au système implémentant l'`EntityStorageInterface` :

```php
<?php

declare(strict_types=1);

namespace Zeus\Core\Contracts;

use Zeus\Core\Metadata\EntityMetadata;

interface EntityStorageInterface
{
    public function insert(EntityMetadata $entity, array $payload): string|int;
    public function update(EntityMetadata $entity, string|int $id, array $payload, array $criteria = []): bool;
    public function delete(EntityMetadata $entity, string|int $id, array $criteria = []): bool;
    public function search(EntityMetadata $entity, array $criteria = []): array;
}
```

### La Résolution du Contexte (`TenantContextResolverInterface`)

De la même manière, le noyau doit être conscient du tenant (locataire) actuel pour assurer la ségrégation des données, mais il ne lit pas lui-même le contexte (il ignore ce qu'est un "Header HTTP" ou un "Token JWT"). 
Il exige de l'adaptateur externe de lui fournir un `TenantContext` standardisé :

```php
<?php

declare(strict_types=1);

namespace Zeus\Core\Contracts;

use Zeus\Core\Context\TenantContext;

interface TenantContextResolverInterface
{
    public function resolve(): TenantContext;
}
```

## 3. Immutabilité Absolue

Afin de garantir la stabilité et la prédictibilité des états dans des environnements fortement concurrentiels, tous les DTOs et métadonnées (tels que `EntityMetadata`, `TenantContext`, `MenuNode`) sont définis en tant que classes `readonly`. Une fois instanciée, la structure ne peut être modifiée à la volée. Cela prévient les "effets de bord" imprévus et force le développeur à traiter le cycle de vie des objets via de nouvelles instanciations explicites.
