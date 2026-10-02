<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use DateTimeZone;

final class Post
{
    /**
     * @param list<Category> $categories
     */
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $description,
        public readonly string $content,
        public readonly ?string $image,
        public readonly int $views,
        public readonly DateTimeImmutable $publishedAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly array $categories,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     * @param list<Category>       $categories
     */
    public static function fromRow(array $row, array $categories): self
    {
        $utc = new DateTimeZone('UTC');

        return new self(
            id: (int) $row['id'],
            slug: (string) $row['slug'],
            title: (string) $row['title'],
            description: (string) $row['description'],
            content: (string) $row['content'],
            image: $row['image'] !== null ? (string) $row['image'] : null,
            views: (int) $row['views'],
            publishedAt: new DateTimeImmutable((string) $row['published_at'], $utc),
            updatedAt: new DateTimeImmutable((string) $row['updated_at'], $utc),
            categories: $categories,
        );
    }

    /**
     * @return list<string>
     */
    public function paragraphs(): array
    {
        $parts = preg_split('/\R\s*\R/u', trim($this->content)) ?: [];

        return array_values(array_filter(
            array_map('trim', $parts),
            static fn (string $paragraph): bool => $paragraph !== '',
        ));
    }
}
