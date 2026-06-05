<?php

declare(strict_types=1);

namespace Zeus\Core\Security;

class RoleRegistry
{
    private array $roles = [];

    public function registerRole(string $role, array $permissions): void
    {
        $this->roles[$role] = $permissions;
    }

    public function getPermissions(string $role): array
    {
        return $this->roles[$role] ?? [];
    }
}
