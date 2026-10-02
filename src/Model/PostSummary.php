<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use DateTimeZone;

final class PostSummary
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $description,
        public readonly ?string $image,
        public readonly int $views,
        public readonly DateTimeImmutable $publishedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            slug: (string) $row['slug'],
            title: (string) $row['title'],
            description: (string) $row['description'],
            image: $row['image'] !== null ? (string) $row['image'] : null,
            views: (int) $row['views'],
            publishedAt: new DateTimeImmutable((string) $row['published_at'], new DateTimeZone('UTC')),
        );
    }
}
