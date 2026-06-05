<?php

declare(strict_types=1);

namespace Zeus\Core\Exceptions;

use RuntimeException;

class UnknownFieldException extends RuntimeException
{
    public function __construct(string $entityCode, string $invalidColumnName)
    {
        parent::__construct(sprintf("Le champ '%s' n'existe pas sur l'entité '%s'.", $invalidColumnName, $entityCode));
    }
}
