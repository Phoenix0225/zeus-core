<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Engine\Query;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Engine\Query\QueryCriteria;

class QueryCriteriaTest extends TestCase
{
    public function test_it_instantiates_and_assigns_properties_correctly(): void
    {
        $criteria = new QueryCriteria('total_amount', '>', 100);

        $this->assertSame('total_amount', $criteria->field);
        $this->assertSame('>', $criteria->operator);
        $this->assertSame(100, $criteria->value);
    }
}
