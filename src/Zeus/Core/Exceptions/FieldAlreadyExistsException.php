<?php

declare(strict_types=1);

namespace Zeus\Core\Exceptions;

use RuntimeException;

class FieldAlreadyExistsException extends RuntimeException
{
    public function __construct(string $entityName, string $fieldCode)
    {
        parent::__construct(sprintf("Le champ '%s' existe déjà sur l'entité '%s'.", $fieldCode, $entityName));
    }
}
