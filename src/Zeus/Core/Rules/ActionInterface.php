<?php

declare(strict_types=1);

namespace Zeus\Core\Rules;

use Zeus\Core\Query\EntityRecord;

interface ActionInterface
{
    public function execute(EntityRecord $record, array $params): void;
}
