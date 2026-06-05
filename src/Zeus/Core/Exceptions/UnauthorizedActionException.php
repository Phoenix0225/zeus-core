<?php

declare(strict_types=1);

namespace Zeus\Core\Exceptions;

use RuntimeException;

class UnauthorizedActionException extends RuntimeException
{
    public function __construct(string $action, string $entityCode)
    {
        parent::__construct(sprintf("Action '%s' non autorisée sur l'entité '%s'.", $action, $entityCode));
    }
}
