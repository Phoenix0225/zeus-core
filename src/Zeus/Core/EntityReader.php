<?php

declare(strict_types=1);

namespace Zeus\Core;

use Zeus\Core\Contracts\EntityQueryExecutorInterface;
use Zeus\Core\Query\EntityQuery;
use Zeus\Core\Query\EntityRecord;

class EntityReader
{
    public function __construct(
        private readonly EntityQueryExecutorInterface $executor
    ) {}

    /**
     * @return EntityRecord[]
     */
    public function fetch(EntityQuery $query): array
    {
        $rawResults = $this->executor->execute($query);

        return array_map(
            fn(array $row) => new EntityRecord($row),
            $rawResults
        );
    }
}
