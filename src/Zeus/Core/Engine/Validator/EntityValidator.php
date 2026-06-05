<?php

declare(strict_types=1);

namespace Zeus\Core\Engine\Validator;

use InvalidArgumentException;
use Zeus\Core\Metadata\FieldMetadata;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Core\Registry\FieldRegistry;

/**
 * Class EntityValidator
 *
 * Performs metadata-driven validation on raw associative arrays based on FieldMetadata rules.
 */
class EntityValidator
{
    /**
     * EntityValidator constructor.
     *
     * @param EntityRegistry $entityRegistry The registered entities definitions.
     * @param FieldRegistry $fieldRegistry The registered fields definitions.
     */
    public function __construct(
        private readonly EntityRegistry $entityRegistry,
        private readonly FieldRegistry $fieldRegistry
    ) {}

    /**
     * Validates raw data against an entity's metadata constraints.
     *
     * @param string $entityCode The code of the entity to validate against.
     * @param array<string, mixed> $data The raw key-value data to validate.
     * @return ValidationResult The outcome of the validation.
     * @throws InvalidArgumentException if the entity code is not registered.
     */
    public function validate(string $entityCode, array $data): ValidationResult
    {
        $entity = $this->entityRegistry->get($entityCode);

        if ($entity === null) {
            throw new InvalidArgumentException(sprintf(
                'Entity with code "%s" is not registered.',
                $entityCode
            ));
        }

        // Retrieve all fields associated with the entity ID from the registry
        $fields = array_filter(
            $this->fieldRegistry->all(),
            fn(FieldMetadata $field) => $field->entity_id === $entity->id
        );

        $errors = [];

        foreach ($fields as $field) {
            $column = $field->column_name;
            $hasKey = array_key_exists($column, $data);
            $value = $hasKey ? $data[$column] : null;

            // Rule 1: Nullable check (ignore if is_system === true)
            if (!$field->nullable && !$field->is_system) {
                if (!$hasKey || $value === null) {
                    $errors[$column][] = sprintf('The field "%s" is required and cannot be null.', $column);
                    continue; // Skip further checks for this field as it is missing or null
                }
            }

            // If the value is null (and is nullable), skip length and type checks
            if ($value === null) {
                continue;
            }

            // Rule 2: Length check (for string values)
            if (is_string($value) && $field->length !== null) {
                if (mb_strlen($value) > $field->length) {
                    $errors[$column][] = sprintf(
                        'The field "%s" length cannot exceed %d characters.',
                        $column,
                        $field->length
                    );
                }
            }

            // Rule 3: Data type check
            if (!$this->validateType($value, $field->data_type)) {
                $errors[$column][] = sprintf(
                    'The field "%s" must be of type "%s".',
                    $column,
                    $field->data_type
                );
            }
        }

        return new ValidationResult(
            isValid: empty($errors),
            errors: $errors
        );
    }

    /**
     * Helper to validate that a value conforms to a logical data type.
     *
     * @param mixed $value The value to check.
     * @param string $dataType The expected metadata data type.
     * @return bool
     */
    private function validateType(mixed $value, string $dataType): bool
    {
        return match (strtolower($dataType)) {
            'int', 'integer' => is_int($value) || (is_string($value) && filter_var($value, FILTER_VALIDATE_INT) !== false),
            'float', 'double', 'decimal' => is_float($value) || is_int($value) || (is_string($value) && filter_var($value, FILTER_VALIDATE_FLOAT) !== false),
            'bool', 'boolean' => is_bool($value) || $value === 1 || $value === 0 || $value === '1' || $value === '0' || strcasecmp((string)$value, 'true') === 0 || strcasecmp((string)$value, 'false') === 0,
            'string', 'text' => is_string($value),
            default => true, // Fallback for other logical types
        };
    }
}
