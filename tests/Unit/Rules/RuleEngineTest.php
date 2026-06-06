<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Query\EntityRecord;
use Zeus\Core\Rules\ActionInterface;
use Zeus\Core\Rules\ActionResolverInterface;
use Zeus\Core\Rules\RuleEngine;
use Zeus\Core\Rules\RuleMetadata;
use Zeus\Core\Rules\RuleProviderInterface;

class RuleEngineTest extends TestCase
{
    public function test_it_does_nothing_when_no_rules_are_found(): void
    {
        $provider = $this->createMock(RuleProviderInterface::class);
        $provider->method('getRulesFor')
            ->with('users', 'after_create')
            ->willReturn([]);

        $resolver = $this->createMock(ActionResolverInterface::class);
        $resolver->expects($this->never())->method('resolve');

        $engine = new RuleEngine($provider, $resolver);
        $record = new EntityRecord(['id' => 1]);

        $engine->dispatch('after_create', 'users', $record);
    }

    public function test_it_executes_multiple_actions_for_a_triggered_rule(): void
    {
        $provider = $this->createMock(RuleProviderInterface::class);
        $provider->method('getRulesFor')
            ->with('users', 'after_create')
            ->willReturn([
                new RuleMetadata(
                    trigger: 'after_create',
                    entityCode: 'users',
                    conditions: [],
                    actions: [
                        ['class' => 'ActionA', 'params' => ['foo' => 'bar']],
                        ['class' => 'ActionB', 'params' => []]
                    ]
                )
            ]);

        $actionA = $this->createMock(ActionInterface::class);
        $actionA->expects($this->once())->method('execute');

        $actionB = $this->createMock(ActionInterface::class);
        $actionB->expects($this->once())->method('execute');

        $resolver = $this->createMock(ActionResolverInterface::class);
        $resolver->expects($this->exactly(2))
            ->method('resolve')
            ->willReturnMap([
                ['ActionA', $actionA],
                ['ActionB', $actionB]
            ]);

        $engine = new RuleEngine($provider, $resolver);
        $record = new EntityRecord(['id' => 1]);

        $engine->dispatch('after_create', 'users', $record);
    }
}
