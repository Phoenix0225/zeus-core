<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Engine\Query;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Engine\Query\EntityQuery;
use Zeus\Core\Engine\Query\QueryCriteria;
use Zeus\Core\Metadata\EntityMetadata;

class EntityQueryTest extends TestCase
{
    public function test_it_can_accumulate_state(): void
    {
        $entity = new EntityMetadata(1, 'uuid-1', 'sales_order', 'Sales Order', null, 'sales', true, 1);
        $query = new EntityQuery($entity);

        $query->addSelectedField('order_number');
        $query->addSelectedField('total_amount');

        $criteria = new QueryCriteria('total_amount', '>', 100);
        $query->addCriteria($criteria);

        $query->addRelation('customer');

        $this->assertSame($entity, $query->entity);
        $this->assertSame(['order_number', 'total_amount'], $query->selectedFields);
        $this->assertCount(1, $query->criteria);
        $this->assertSame($criteria, $query->criteria[0]);
        $this->assertSame(['customer'], $query->relationsToLoad);
    }
}
