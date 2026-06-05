<?php

declare(strict_types=1);

namespace Zeus\Core\Kernel;

use RuntimeException;
use Zeus\Core\Contracts\MetadataProviderInterface;
use Zeus\Core\Registry\BusinessKeyRegistry;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Core\Registry\FieldRegistry;
use Zeus\Core\Registry\RelationRegistry;

/**
 * Class ZeusKernel
 *
 * The central orchestrator of the zeus-core engine.
 * It boots the system by retrieving metadata definitions from the injected
 * MetadataProviderInterface and registering them into their respective registries.
 */
class ZeusKernel
{
    /**
     * Whether the kernel has been booted.
     */
    private bool $isBooted = false;

    /**
     * ZeusKernel constructor.
     *
     * Uses PHP 8 property promotion with asymmetric visibility to inject the provider
     * and registries, making them readable from the outside but immutable.
     */
    public function __construct(
        public private(set) MetadataProviderInterface $metadataProvider,
        public private(set) EntityRegistry $entityRegistry,
        public private(set) FieldRegistry $fieldRegistry,
        public private(set) BusinessKeyRegistry $businessKeyRegistry,
        public private(set) RelationRegistry $relationRegistry,
    ) {}

    /**
     * Boots the kernel by loading metadata definitions into the registries.
     *
     * @return void
     * @throws RuntimeException if the kernel has already been booted.
     */
    public function boot(): void
    {
        if ($this->isBooted) {
            throw new RuntimeException('The Zeus Kernel has already been booted.');
        }

        // 1. Boot Entity Metadata
        foreach ($this->metadataProvider->getEntities() as $entity) {
            $this->entityRegistry->register($entity);
        }

        // 2. Boot Field Metadata
        foreach ($this->metadataProvider->getFields() as $field) {
            $this->fieldRegistry->register($field);
        }

        // 3. Boot Business Key Metadata
        foreach ($this->metadataProvider->getBusinessKeys() as $businessKey) {
            $this->businessKeyRegistry->register($businessKey);
        }

        // 4. Boot Relation Metadata
        foreach ($this->metadataProvider->getRelations() as $relation) {
            $this->relationRegistry->register($relation);
        }

        $this->isBooted = true;
    }

    /**
     * Returns whether the kernel has been booted.
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->isBooted;
    }
}
