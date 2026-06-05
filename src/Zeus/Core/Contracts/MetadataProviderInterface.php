<?php

declare(strict_types=1);

namespace Zeus\Core\Contracts;

use Zeus\Core\Metadata\BusinessKeyMetadata;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\FieldMetadata;
use Zeus\Core\Metadata\RelationMetadata;

/**
 * Interface MetadataProviderInterface
 *
 * Defines the contract for fetching metadata definitions from an external source
 * (e.g., database, static configuration, API). This allows the Zeus Kernel to boot
 * without depending on concrete metadata persistence implementations.
 */
interface MetadataProviderInterface
{
    /**
     * Retrieves all entity metadata definitions.
     *
     * @return iterable<EntityMetadata>
     */
    public function getEntities(): iterable;

    /**
     * Retrieves all field metadata definitions.
     *
     * @return iterable<FieldMetadata>
     */
    public function getFields(): iterable;

    /**
     * Retrieves all business key metadata definitions.
     *
     * @return iterable<BusinessKeyMetadata>
     */
    public function getBusinessKeys(): iterable;

    /**
     * Retrieves all relation metadata definitions.
     *
     * @return iterable<RelationMetadata>
     */
    public function getRelations(): iterable;
}
