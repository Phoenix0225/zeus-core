<?php

declare(strict_types=1);

namespace Zeus\Core\Exceptions;

use RuntimeException;

class FieldNotFoundException extends RuntimeException
{
    public function __construct(string $entityName, string $columnName)
    {
        parent::__construct(sprintf("Le champ '%s' est introuvable sur l'entité '%s'.", $columnName, $entityName));
    }
}
