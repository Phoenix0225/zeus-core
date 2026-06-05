# Moteur d'Interface Utilisateur (UI Engine)

Afin de concrétiser la promesse d'une architecture orientée "Metadata-Driven" et No-Code, le frontend ne doit pas détenir la vérité de la navigation ou des structures d'écrans. C'est le backend (Zeus Core) qui dessine l'interface logicielle de manière centralisée.

Le **UI Engine** est responsable de compiler la taxonomie visuelle de l'application et de l'exposer sous forme de configuration exploitable pour l'interface utilisateur.

## 1. Le Registre UI (`UiRegistry`)

L'intégralité du menu de navigation et de la cartographie des écrans (grids, formulaires, tableaux de bord) est enregistrée dynamiquement en mémoire via l'`UiRegistry`. 

Le registre permet à d'autres packages métier de brancher de nouvelles fonctionnalités de manière modulaire, respectant le principe Ouvert/Fermé (Open/Closed Principle) de SOLID.

## 2. Les DTOs de Définition Visuelle

Pour assurer la cohérence et la pureté des données, les éléments d'interface sont encapsulés dans des objets immuables : `MenuNode` et `ScreenMetadata`.

- **MenuNode** : Gère l'architecture arborescente de la navigation (Labels, icônes, sous-menus et lien éventuel vers un écran).
- **ScreenMetadata** : Spécifie le type de rendu attendu sur le client (Grille de données `grid`, Formulaire `form`) et sa configuration associée.

## 3. Exemple de Configuration

Dans un package ou lors du démarrage applicatif, le développeur enregistre de nouvelles interfaces comme suit :

```php
use Zeus\Core\UI\MenuNode;
use Zeus\Core\UI\ScreenMetadata;

// Création d'un écran de type grille rattaché à l'entité "users"
$usersGrid = new ScreenMetadata(
    id: 'users_grid_view',
    type: 'grid',
    entityCode: 'users',
    config: ['columns' => ['id', 'name', 'email']]
);
$uiRegistry->registerScreen($usersGrid);

// Création d'un menu parent et d'un sous-menu pointant vers l'écran
$usersMenu = new MenuNode(
    id: 'nav_users',
    label: 'Utilisateurs',
    icon: 'user-icon',
    screenId: 'users_grid_view'
);

$settingsMenu = new MenuNode(
    id: 'nav_settings',
    label: 'Paramètres',
    icon: 'cog-icon',
    children: [$usersMenu]
);
$uiRegistry->registerMenu($settingsMenu);
```

Une fois enregistré, l'adaptateur Laravel sera en mesure d'exposer ces arbres de navigation dynamiquement au format JSON via des endpoints REST natifs (`/api/ui/config`), propulsant ainsi la génération du DOM frontend en temps réel.
