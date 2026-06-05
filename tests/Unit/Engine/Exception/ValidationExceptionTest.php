<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Engine\Exception;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Engine\Exception\ValidationException;
use Zeus\Core\Engine\Validator\ValidationResult;

class ValidationExceptionTest extends TestCase
{
    public function test_it_instantiates_with_validation_result(): void
    {
        $result = new ValidationResult(isValid: false, errors: ['field' => ['Error message']]);
        $exception = new ValidationException($result);

        $this->assertSame($result, $exception->result);
        $this->assertSame('Validation failed.', $exception->getMessage());
    }
}
