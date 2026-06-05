<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Engine\Validator;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Engine\Validator\EntityValidator;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\FieldMetadata;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Core\Registry\FieldRegistry;

/**
 * Class EntityValidatorTest
 *
 * Tests metadata-driven validation rules and validations exceptions in EntityValidator.
 */
class EntityValidatorTest extends TestCase
{
    private EntityRegistry $entityRegistry;
    private FieldRegistry $fieldRegistry;
    private EntityValidator $validator;

    protected function setUp(): void
    {
        $this->entityRegistry = new EntityRegistry();
        $this->fieldRegistry = new FieldRegistry();
        $this->validator = new EntityValidator($this->entityRegistry, $this->fieldRegistry);

        // Register dummy entity "customer"
        $entity = new EntityMetadata(
            id: 1,
            uuid: 'e57c1d7e-7b7d-4299-a9a7-96a8e805566f',
            code: 'customer',
            name: 'Customer',
            description: null,
            module_code: 'crm',
            is_active: true,
            version: 1
        );
        $this->entityRegistry->register($entity);

        // Register fields for "customer"
        // 1. Required standard field (name)
        $this->fieldRegistry->register(new FieldMetadata(
            id: 101,
            uuid: 'f-customer-name',
            entity_id: 1,
            table_name: 'customers',
            column_name: 'name',
            label: 'Name',
            data_type: 'string',
            length: null,
            nullable: false,
            is_business_key: false,
            is_system: false,
            version: 1
        ));

        // 2. Optional field (notes)
        $this->fieldRegistry->register(new FieldMetadata(
            id: 102,
            uuid: 'f-customer-notes',
            entity_id: 1,
            table_name: 'customers',
            column_name: 'notes',
            label: 'Notes',
            data_type: 'string',
            length: null,
            nullable: true,
            is_business_key: false,
            is_system: false,
            version: 1
        ));

        // 3. Field with length limit (code, length: 50)
        $this->fieldRegistry->register(new FieldMetadata(
            id: 103,
            uuid: 'f-customer-code',
            entity_id: 1,
            table_name: 'customers',
            column_name: 'code',
            label: 'Code',
            data_type: 'string',
            length: 50,
            nullable: false,
            is_business_key: true,
            is_system: false,
            version: 1
        ));

        // 4. Required system field (uuid)
        $this->fieldRegistry->register(new FieldMetadata(
            id: 104,
            uuid: 'f-customer-uuid',
            entity_id: 1,
            table_name: 'customers',
            column_name: 'uuid',
            label: 'UUID',
            data_type: 'string',
            length: null,
            nullable: false,
            is_system: true,
            is_business_key: false,
            version: 1
        ));
    }

    /**
     * Valide que l'InvalidArgumentException est lancée avec un code entité inconnu.
     */
    public function test_it_throws_exception_if_entity_does_not_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity with code "sales_order" is not registered.');
        $this->validator->validate('sales_order', []);
    }

    /**
     * Fournir un tableau $data qui respecte toutes les règles. isValid doit être true et la liste d'erreurs vide.
     */
    public function test_it_returns_valid_result_for_perfect_data(): void
    {
        $data = [
            'name' => 'John Doe',
            'notes' => 'Some test notes',
            'code' => 'CUST-001',
            'uuid' => 'some-uuid-value'
        ];

        $result = $this->validator->validate('customer', $data);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
    }

    /**
     * Omettre un champ standard requis. isValid doit être false.
     */
    public function test_it_adds_error_if_required_field_is_missing(): void
    {
        // "name" is required and missing
        $data = [
            'code' => 'CUST-001'
        ];

        $result = $this->validator->validate('customer', $data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('name', $result->errors);
        $this->assertStringContainsString('is required and cannot be null', $result->errors['name'][0]);
    }

    /**
     * Omettre le champ système requis. La validation doit tout de même passer (isValid === true).
     */
    public function test_it_ignores_required_fields_if_they_are_system_fields(): void
    {
        // "uuid" is required but is_system is true, so should be ignored
        $data = [
            'name' => 'John Doe',
            'code' => 'CUST-001'
        ];

        $result = $this->validator->validate('customer', $data);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
    }

    /**
     * Fournir une chaîne plus longue que la length définie.
     */
    public function test_it_adds_error_if_string_exceeds_max_length(): void
    {
        // "code" is limited to 50 characters
        $longCode = str_repeat('A', 51);
        $data = [
            'name' => 'John Doe',
            'code' => $longCode
        ];

        $result = $this->validator->validate('customer', $data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('code', $result->errors);
        $this->assertStringContainsString('length cannot exceed 50 characters', $result->errors['code'][0]);
    }

    /**
     * Soumettre un tableau avec plusieurs infractions simultanées pour s'assurer que le
     * tableau d'erreurs contient bien toutes les fautes et ne s'arrête pas à la première.
     */
    public function test_it_can_accumulate_multiple_errors(): void
    {
        // Missing "name" and "code" length exceeded (51 chars)
        $longCode = str_repeat('A', 51);
        $data = [
            'code' => $longCode
        ];

        $result = $this->validator->validate('customer', $data);

        $this->assertFalse($result->isValid);
        $this->assertCount(2, $result->errors);
        $this->assertArrayHasKey('name', $result->errors);
        $this->assertArrayHasKey('code', $result->errors);
    }
}
