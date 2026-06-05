<?php

declare(strict_types=1);

namespace Zeus\Core\Context;

readonly class TenantContext
{
    public function __construct(
        public string|int|null $companyId = null,
        public string|int|null $divisionId = null,
        public string|int|null $siteId = null,
        public string|int|null $warehouseId = null,
        public array $permissions = [],
    ) {}

    public function isGlobal(): bool
    {
        return $this->companyId === null;
    }

    public function getLevel(): string
    {
        if ($this->warehouseId !== null) {
            return 'warehouse';
        }

        if ($this->siteId !== null) {
            return 'site';
        }

        if ($this->divisionId !== null) {
            return 'division';
        }

        if ($this->companyId !== null) {
            return 'company';
        }

        return 'global';
    }

    public function hasPermission(string $permission): bool
    {
        return in_array('*', $this->permissions, true) || in_array($permission, $this->permissions, true);
    }
}
