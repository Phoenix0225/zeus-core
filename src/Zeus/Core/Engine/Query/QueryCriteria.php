<?php

declare(strict_types=1);

namespace Zeus\Core\Engine\Query;

/**
 * Class QueryCriteria
 *
 * Immutable DTO representing a logical WHERE clause in an EntityQuery.
 */
readonly class QueryCriteria
{
    /**
     * @param string $field The field column name to filter on.
     * @param string $operator The logical operator (e.g., '=', '>', 'IN', 'LIKE').
     * @param mixed $value The value to compare against.
     */
    public function __construct(
        public string $field,
        public string $operator,
        public mixed $value,
    ) {}
}
