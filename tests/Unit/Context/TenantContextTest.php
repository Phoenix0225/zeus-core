<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Context;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Context\TenantContext;

class TenantContextTest extends TestCase
{
    public function test_it_identifies_as_global_when_no_company_is_set(): void
    {
        $context = new TenantContext();

        $this->assertTrue($context->isGlobal());
        $this->assertEquals('global', $context->getLevel());
    }

    public function test_it_identifies_the_correct_hierarchical_level(): void
    {
        $companyContext = new TenantContext(companyId: 1);
        $this->assertEquals('company', $companyContext->getLevel());

        $siteContext = new TenantContext(companyId: 1, siteId: 5);
        $this->assertEquals('site', $siteContext->getLevel());
    }

    public function test_it_holds_tenant_identifiers_correctly(): void
    {
        $context = new TenantContext(companyId: 'COMP-A', warehouseId: 12);

        $this->assertEquals('COMP-A', $context->companyId);
        $this->assertNull($context->divisionId);
        $this->assertNull($context->siteId);
        $this->assertEquals(12, $context->warehouseId);
        
        $this->assertFalse($context->isGlobal());
        $this->assertEquals('warehouse', $context->getLevel());
    }
}
