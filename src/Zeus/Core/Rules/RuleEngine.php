<?php

declare(strict_types=1);

namespace Zeus\Core\Rules;

use Zeus\Core\Query\EntityRecord;

class RuleEngine
{
    public function __construct(
        private readonly RuleProviderInterface $provider,
        private readonly ActionResolverInterface $resolver
    ) {}

    public function dispatch(string $trigger, string $entityCode, EntityRecord $record): void
    {
        $rules = $this->provider->getRulesFor($entityCode, $trigger);

        foreach ($rules as $rule) {
            // Pour l'instant, disons que si c'est vide, la condition est vraie
            if (!empty($rule->conditions)) {
                // Condition evaluation logic
            }

            foreach ($rule->actions as $actionConfig) {
                $actionClass = $actionConfig['class'] ?? null;
                $params = $actionConfig['params'] ?? [];

                if ($actionClass) {
                    $action = $this->resolver->resolve($actionClass);
                    $action->execute($record, $params);
                }
            }
        }
    }
}
