<?php

declare(strict_types=1);

namespace Zeus\Core\Context;

class TenantEnforcer
{
    public function getReadCriteria(TenantContext $context): array
    {
        if ($context->isGlobal()) {
            return [];
        }

        $criteria = [];

        if ($context->companyId !== null) {
            $criteria[] = ['field' => 'company_id', 'value' => $context->companyId, 'allow_null' => true];
        }

        if ($context->divisionId !== null) {
            $criteria[] = ['field' => 'division_id', 'value' => $context->divisionId, 'allow_null' => true];
        }

        if ($context->siteId !== null) {
            $criteria[] = ['field' => 'site_id', 'value' => $context->siteId, 'allow_null' => true];
        }

        if ($context->warehouseId !== null) {
            $criteria[] = ['field' => 'warehouse_id', 'value' => $context->warehouseId, 'allow_null' => true];
        }

        return $criteria;
    }

    public function enrichPayload(TenantContext $context, array $payload): array
    {
        if ($context->companyId !== null && !array_key_exists('company_id', $payload)) {
            $payload['company_id'] = $context->companyId;
        }

        if ($context->divisionId !== null && !array_key_exists('division_id', $payload)) {
            $payload['division_id'] = $context->divisionId;
        }

        if ($context->siteId !== null && !array_key_exists('site_id', $payload)) {
            $payload['site_id'] = $context->siteId;
        }

        if ($context->warehouseId !== null && !array_key_exists('warehouse_id', $payload)) {
            $payload['warehouse_id'] = $context->warehouseId;
        }

        return $payload;
    }
}
