<?php

declare(strict_types=1);

namespace Zeus\Core\Metadata\Enums;

enum RelationType: string
{
    case BELONGS_TO = 'belongsTo';
    case HAS_MANY = 'hasMany';
}
