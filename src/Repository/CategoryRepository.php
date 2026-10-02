<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Database;
use App\Model\Category;
use App\Model\CategoryPreview;
use App\Model\PostSummary;

final class CategoryRepository
{
    public function __construct(private readonly Database $db)
    {
    }

    /**
     * @param positive-int $postsPerCategory
     * @return list<CategoryPreview>
     */
    public function findPreviewsWithLatestPosts(int $postsPerCategory): array
    {
        $rows = $this->db->fetchAll(
            <<<'SQL'
            SELECT
                c.id          AS c_id,
                c.slug        AS c_slug,
                c.name        AS c_name,
                c.description AS c_description,
                p.id,
                p.slug,
                p.title,
                p.description,
                p.image,
                p.views,
                p.published_at
            FROM (
                SELECT
                    cp.category_id,
                    cp.post_id,
                    ROW_NUMBER() OVER (
                        PARTITION BY cp.category_id
                        ORDER BY p.published_at DESC, p.id DESC
                    ) AS position
                FROM category_post cp
                JOIN posts p ON p.id = cp.post_id
                WHERE p.published_at <= UTC_TIMESTAMP()
            ) ranked
            JOIN categories c ON c.id = ranked.category_id
            JOIN posts p      ON p.id = ranked.post_id
            WHERE ranked.position <= ?
            ORDER BY c.id, ranked.position
            SQL,
            [$postsPerCategory],
        );

        /** @var array<int, array{category: Category, posts: non-empty-list<PostSummary>}> $groups */
        $groups = [];

        foreach ($rows as $row) {
            $categoryId = (int) $row['c_id'];
            $post = PostSummary::fromRow($row);

            if (isset($groups[$categoryId])) {
                $groups[$categoryId]['posts'][] = $post;
            } else {
                $groups[$categoryId] = ['category' => Category::fromRow($row, 'c_'), 'posts' => [$post]];
            }
        }

        return array_values(array_map(
            static fn (array $group): CategoryPreview => new CategoryPreview($group['category'], $group['posts']),
            $groups,
        ));
    }

    public function findBySlug(string $slug): ?Category
    {
        $row = $this->db->fetchOne(
            'SELECT id, slug, name, description FROM categories WHERE slug = ?',
            [$slug],
        );

        return $row === null ? null : Category::fromRow($row);
    }
}
