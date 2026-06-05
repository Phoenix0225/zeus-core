# ⚡ Zeus Core

![License](https://img.shields.io/badge/license-BSL%201.1-blue)
![PHP Version](https://img.shields.io/badge/PHP-8.4+-blue.svg)
![Architecture](https://img.shields.io/badge/Architecture-Agnostic-success.svg)


Zeus Core est le moteur pur et framework agnostique conçu pour bâtir des ERP "Data-Driven" et des architectures No-Code. Il fournit les fondations robustes nécessaires pour gérer dynamiquement des modèles de données complexes sans dépendre d'un framework spécifique.

## Concepts Clés

- **Agnosticisme** : Conçu sans aucune dépendance à un framework d'application, permettant une intégration libre avec n'importe quelle architecture existante.
- **Multi-Tenant Natif** : Isolation totale des données par tenant intégrée au cœur du moteur, garantissant la sécurité dans les environnements SaaS.
- **Eager Loading Sans Clés Étrangères** : Un système de relations performant résolvant l'eager loading en mémoire, adapté pour des schémas dynamiques et des bases de données distribuées.
- **ACL Data-Driven en Mémoire** : Sécurité granulaire basée sur les données elles-mêmes, évaluée extrêmement rapidement en mémoire pour le filtrage et les autorisations.

## Installation

```bash
composer require ton-nom/zeus-core
```

## 📖 Documentation

- [1. Architecture & Inversion de Contrôle (IoC)](docs/01-architecture.md)
- [2. L'EntityManager et le QueryBuilder](docs/02-data-engine.md)
- [3. Le Contexte Multi-Tenant & La Sécurité](docs/03-security-tenant.md)
- [4. Le Registre UI (Menus & Écrans)](docs/04-ui-engine.md)
- [5. Les Relations Agnostiques (Eager Loading)](docs/05-relations.md)

## Exemple d'utilisation rapide

```php
<?php

use Zeus\Core\DataEngine\Query\EntityQueryBuilder;
use Zeus\Core\DataEngine\EntityReader;

// Construction d'une requête dynamique
$query = (new EntityQueryBuilder('invoice'))
    ->where('status', '=', 'PAID')
    ->where('amount', '>', 1000)
    ->with(['customer', 'lines'])
    ->build();

// Exécution de la lecture via le moteur agnostique
$reader = new EntityReader($context, $storageAdapter);
$invoices = $reader->read($query);

foreach ($invoices as $invoice) {
    echo "Facture: " . $invoice->get('number') . " - Client: " . $invoice->getRelation('customer')->get('name');
}
```