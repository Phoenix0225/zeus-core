<?php

declare(strict_types=1);

namespace Zeus\Core\Contracts;

use Zeus\Core\Context\TenantContext;

interface TenantContextResolverInterface
{
    public function resolve(): TenantContext;
}
