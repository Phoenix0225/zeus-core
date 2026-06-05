# Isolation Multitenant et Sécurité (ACL)

La force d'un ERP SaaS (Software as a Service) réside dans sa capacité à cloisonner de façon stricte les données de chaque locataire (Tenant) tout en maintenant un code base unique (Single Tenant vs Multi-Tenant).

Dans **Zeus Core**, le cloisonnement n'est pas "ajouté par-dessus", il est organique et indissociable du moteur de requête.

## 1. La Matrice du TenantContext

L'isolation est hiérarchique et matricielle. Un utilisateur évolue dans l'ERP en fonction d'un contexte de résolution précis définit par le `TenantContext`.

La résolution s'opère sur la hiérarchie organisationnelle (du plus global au plus granulaire) :
`Global` ➔ `Company` ➔ `Division` ➔ `Site` ➔ `Warehouse`

Le DTO immuable `TenantContext` capture ce positionnement :

```php
readonly class TenantContext
{
    public function __construct(
        public string|int|null $companyId = null,
        public string|int|null $divisionId = null,
        public string|int|null $siteId = null,
        public string|int|null $warehouseId = null,
        public array $permissions = [],
    ) {}
}
```
L'`EntityManager` se base sur le niveau maximal configuré dans le contexte actuel pour injecter la clé du périmètre dans chaque nouvelle requête ou insertion.

## 2. Le Gardien de Sécurité (SecurityEnforcer)

Le contrôle d'accès n'est jamais laissé au bon vouloir du développeur frontend ou de l'adaptateur de l'API. C'est l'un des piliers du *Zero Trust Architecture*.

Le noyau embarque le `SecurityEnforcer` qui agit systématiquement en amont des opérations. Il vérifie que le tableau de `$permissions` de l'utilisateur comporte la chaîne autorisée (ex: `invoices.create`, `products.read`), ou la directive wildcard super-admin `*`. Si ce n'est pas le cas, une `UnauthorizedActionException` est levée instantanément, neutralisant la requête.

## 3. Le Système d'ACL "Data-Driven" Ultra-Léger

Contrairement à des librairies généralistes comme "Spatie Permission" qui nécessitent un couplage fort avec Eloquent et le framework, Zeus propose un registre ACL abstrait et véloce, entièrement conservé en mémoire pour l'exécution : le `RoleRegistry`.

L'adaptateur (ex: Laravel) lit les configurations physiques (table pivot `zeus_tenant_user`) et nourrit le registre au moment de la résolution du contexte. Le noyau s'assure de l'exécution à haute fréquence.

```php
// Enregistrement ultra-rapide des rôles en mémoire
$this->roles[$role] = $permissions;

// Extraction lors de la constitution du TenantContext
$permissions = $this->roleRegistry->getPermissions($pivot->role);
```
Ce modèle "Data-Driven" offre une agilité sans égale pour l'ingénierie d'entreprise sans créer de pénalité de performance.
