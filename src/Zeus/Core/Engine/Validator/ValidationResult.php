<?php

declare(strict_types=1);

namespace Zeus\Core\Engine\Validator;

/**
 * Class ValidationResult
 *
 * Immutable DTO representing the result of a validation check.
 */
readonly class ValidationResult
{
    /**
     * ValidationResult constructor.
     *
     * @param bool $isValid Whether the validation passed.
     * @param array<string, array<string>> $errors An associative array of field names mapped to validation errors.
     */
    public function __construct(
        public bool $isValid,
        public array $errors = []
    ) {}
}
