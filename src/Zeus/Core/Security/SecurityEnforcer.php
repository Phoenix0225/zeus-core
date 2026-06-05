<?php

declare(strict_types=1);

namespace Zeus\Core\Security;

use Zeus\Core\Context\TenantContext;
use Zeus\Core\Exceptions\UnauthorizedActionException;

class SecurityEnforcer
{
    public function authorize(TenantContext $context, string $entityCode, string $action): void
    {
        $permission = sprintf('%s.%s', $entityCode, $action);

        if (!$context->hasPermission($permission)) {
            throw new UnauthorizedActionException($action, $entityCode);
        }
    }
}
