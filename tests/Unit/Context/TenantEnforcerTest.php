<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Context;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Context\TenantContext;
use Zeus\Core\Context\TenantEnforcer;

class TenantEnforcerTest extends TestCase
{
    private TenantEnforcer $enforcer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enforcer = new TenantEnforcer();
    }

    public function test_it_returns_empty_criteria_for_global_context(): void
    {
        $context = new TenantContext();
        $criteria = $this->enforcer->getReadCriteria($context);

        $this->assertEmpty($criteria);
    }

    public function test_it_generates_read_criteria_with_master_data_allowance(): void
    {
        $context = new TenantContext(companyId: 1, siteId: 5);
        $criteria = $this->enforcer->getReadCriteria($context);

        $this->assertCount(2, $criteria);
        
        $this->assertEquals([
            ['field' => 'company_id', 'value' => 1, 'allow_null' => true],
            ['field' => 'site_id', 'value' => 5, 'allow_null' => true]
        ], $criteria);
    }

    public function test_it_enriches_payload_with_context_identifiers(): void
    {
        $context = new TenantContext(siteId: 2);
        $payload = ['name' => 'Test Product'];

        $enriched = $this->enforcer->enrichPayload($context, $payload);

        $this->assertArrayHasKey('site_id', $enriched);
        $this->assertEquals(2, $enriched['site_id']);
        $this->assertEquals('Test Product', $enriched['name']);
    }

    public function test_it_does_not_overwrite_existing_identifiers_in_payload(): void
    {
        $context = new TenantContext(companyId: 1);
        $payload = ['name' => 'Admin Product', 'company_id' => 99];

        $enriched = $this->enforcer->enrichPayload($context, $payload);

        $this->assertEquals(99, $enriched['company_id']);
        $this->assertEquals('Admin Product', $enriched['name']);
    }
}
