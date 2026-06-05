<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Query;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Context\TenantContext;
use Zeus\Core\Context\TenantEnforcer;
use Zeus\Core\Contracts\TenantContextResolverInterface;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Query\EntityQueryBuilder;

class EntityQueryBuilderTest extends TestCase
{
    private function createEntity(): EntityMetadata
    {
        return new EntityMetadata(
            id: 1,
            uuid: 'uuid-1',
            code: 'test_entities',
            name: 'Test Entities',
            description: null,
            module_code: 'core',
            is_active: true,
            version: 1,
        );
    }

    public function test_it_builds_a_query_with_custom_conditions(): void
    {
        $entity = $this->createEntity();

        $context = new TenantContext();
        
        $resolver = $this->createMock(TenantContextResolverInterface::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->willReturn($context);

        $enforcer = $this->createMock(TenantEnforcer::class);
        $enforcer->expects($this->once())
            ->method('getReadCriteria')
            ->with($context)
            ->willReturn([]);

        $builder = new EntityQueryBuilder($enforcer, $resolver);

        $query = $builder->forEntity($entity)
            ->where('status', '=', 'active')
            ->getQuery();

        $conditions = $query->getConditions();
        $this->assertCount(1, $conditions);

        $this->assertEquals('status', $conditions[0]->field);
        $this->assertEquals('=', $conditions[0]->operator);
        $this->assertEquals('active', $conditions[0]->value);
        $this->assertFalse($conditions[0]->allowNull);
    }

    public function test_it_auto_injects_tenant_scoping_conditions(): void
    {
        $entity = $this->createEntity();

        $context = new TenantContext();
        
        $resolver = $this->createMock(TenantContextResolverInterface::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->willReturn($context);

        $enforcer = $this->createMock(TenantEnforcer::class);
        $enforcer->expects($this->once())
            ->method('getReadCriteria')
            ->with($context)
            ->willReturn([
                ['field' => 'site_id', 'value' => 5, 'allow_null' => true]
            ]);

        $builder = new EntityQueryBuilder($enforcer, $resolver);

        $query = $builder->forEntity($entity)
            ->where('status', '=', 'active')
            ->getQuery();

        $conditions = $query->getConditions();
        $this->assertCount(2, $conditions);

        $this->assertEquals('site_id', $conditions[0]->field);
        $this->assertEquals('=', $conditions[0]->operator);
        $this->assertEquals(5, $conditions[0]->value);
        $this->assertTrue($conditions[0]->allowNull);

        $this->assertEquals('status', $conditions[1]->field);
        $this->assertEquals('=', $conditions[1]->operator);
        $this->assertEquals('active', $conditions[1]->value);
        $this->assertFalse($conditions[1]->allowNull);
    }
}
