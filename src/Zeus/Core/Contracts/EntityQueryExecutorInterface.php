<?php

declare(strict_types=1);

namespace Zeus\Core\Contracts;

use Zeus\Core\Query\EntityQuery;

interface EntityQueryExecutorInterface
{
    public function execute(EntityQuery $query): array;
}
