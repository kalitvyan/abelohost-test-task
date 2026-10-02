<?php

declare(strict_types=1);

namespace App\Model;

final class Category
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $name,
        public readonly string $description,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row, string $prefix = ''): self
    {
        return new self(
            id: (int) $row[$prefix . 'id'],
            slug: (string) $row[$prefix . 'slug'],
            name: (string) $row[$prefix . 'name'],
            description: (string) $row[$prefix . 'description'],
        );
    }
}
