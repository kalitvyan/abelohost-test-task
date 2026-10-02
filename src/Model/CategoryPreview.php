<?php

declare(strict_types=1);

namespace App\Model;

final class CategoryPreview
{
    /**
     * @param non-empty-list<PostSummary> $posts
     */
    public function __construct(
        public readonly Category $category,
        public readonly array $posts,
    ) {
    }
}
