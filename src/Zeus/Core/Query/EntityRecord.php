<?php

declare(strict_types=1);

namespace Zeus\Core\Query;

readonly class EntityRecord
{
    public function __construct(
        public array $data,
        public array $relations = []
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function withRelation(string $name, mixed $data): self
    {
        return new self(
            $this->data,
            array_merge($this->relations, [$name => $data])
        );
    }

    public function getRelation(string $name): mixed
    {
        return $this->relations[$name] ?? null;
    }
}
