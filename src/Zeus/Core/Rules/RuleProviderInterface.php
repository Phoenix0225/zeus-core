<?php

declare(strict_types=1);

namespace Zeus\Core\Rules;

interface RuleProviderInterface
{
    /**
     * @return RuleMetadata[]
     */
    public function getRulesFor(string $entityCode, string $trigger): array;
}
