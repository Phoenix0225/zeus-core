<?php

declare(strict_types=1);

namespace Zeus\Core\Engine\Exception;

use RuntimeException;
use Zeus\Core\Engine\Validator\ValidationResult;

/**
 * Class ValidationException
 *
 * Exception thrown when entity data fails metadata validation.
 */
class ValidationException extends RuntimeException
{
    /**
     * @param ValidationResult $result The result containing validation errors.
     */
    public function __construct(
        public readonly ValidationResult $result,
    ) {
        parent::__construct('Validation failed.');
    }
}
