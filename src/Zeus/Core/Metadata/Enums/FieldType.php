<?php

declare(strict_types=1);

namespace Zeus\Core\Metadata\Enums;

enum FieldType: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case DECIMAL = 'decimal';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case TEXT = 'text';
    case DICTIONARY = 'dictionary';
    case RELATION_ID = 'relation_id';
}
