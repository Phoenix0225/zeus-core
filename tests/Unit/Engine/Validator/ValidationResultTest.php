<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Engine\Validator;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Engine\Validator\ValidationResult;

/**
 * Class ValidationResultTest
 *
 * Tests the instantiation and immutability of the ValidationResult DTO.
 */
class ValidationResultTest extends TestCase
{
    /**
     * Test successful instantiation and property mapping of ValidationResult.
     */
    public function test_it_instantiates_correctly(): void
    {
        $errors = [
            'email' => [
                'The field "email" is required.',
                'The field "email" must be of type "string".'
            ]
        ];

        $result = new ValidationResult(false, $errors);

        $this->assertFalse($result->isValid);
        $this->assertSame($errors, $result->errors);
    }
}
