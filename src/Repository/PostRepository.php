<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Database;
use App\Model\Category;
use App\Model\Post;
use App\Model\PostSorting;
use App\Model\PostSummary;

final class PostRepository
{
    private const SUMMARY_COLUMNS = 'p.id, p.slug, p.title, p.description, p.image, p.views, p.published_at';

    public function __construct(private readonly Database $db)
    {
    }

    public function countPublishedInCategory(int $categoryId): int
    {
        return (int) $this->db->fetchValue(
            <<<'SQL'
            SELECT COUNT(*)
            FROM category_post cp
            JOIN posts p ON p.id = cp.post_id
            WHERE cp.category_id = ?
              AND p.published_at <= UTC_TIMESTAMP()
            SQL,
            [$categoryId],
        );
    }

    /**
     * @return list<PostSummary>
     */
    public function findPublishedInCategory(
        int $categoryId,
        PostSorting $sorting,
        int $limit,
        int $offset,
    ): array {
        $rows = $this->db->fetchAll(
            sprintf(
                <<<'SQL'
                SELECT %s
                FROM category_post cp
                JOIN posts p ON p.id = cp.post_id
                WHERE cp.category_id = ?
                  AND p.published_at <= UTC_TIMESTAMP()
                ORDER BY %s
                LIMIT ? OFFSET ?
                SQL,
                self::SUMMARY_COLUMNS,
                self::orderBy($sorting),
            ),
            [$categoryId, $limit, $offset],
        );

        return array_map(PostSummary::fromRow(...), $rows);
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        $row = $this->db->fetchOne(
            <<<'SQL'
            SELECT id, slug, title, description, content, image, views, published_at, updated_at
            FROM posts
            WHERE slug = ?
              AND published_at <= UTC_TIMESTAMP()
            SQL,
            [$slug],
        );

        if ($row === null) {
            return null;
        }

        $categories = array_map(
            Category::fromRow(...),
            $this->db->fetchAll(
                <<<'SQL'
                SELECT c.id, c.slug, c.name, c.description
                FROM category_post cp
                JOIN categories c ON c.id = cp.category_id
                WHERE cp.post_id = ?
                ORDER BY c.id
                SQL,
                [(int) $row['id']],
            ),
        );

        return Post::fromRow($row, $categories);
    }

    public function incrementViews(string $slug): void
    {
        $this->db->execute(
            <<<'SQL'
            UPDATE posts
            SET views = views + 1,
                updated_at = updated_at
            WHERE slug = ?
              AND published_at <= UTC_TIMESTAMP()
            SQL,
            [$slug],
        );
    }

    /**
     * @return list<PostSummary>
     */
    public function findRelated(int $postId, int $limit): array
    {
        $rows = $this->db->fetchAll(
            sprintf(
                <<<'SQL'
                SELECT %s, COUNT(*) AS shared_categories
                FROM category_post mine
                JOIN category_post other
                  ON other.category_id = mine.category_id
                 AND other.post_id <> mine.post_id
                JOIN posts p ON p.id = other.post_id
                WHERE mine.post_id = ?
                  AND p.published_at <= UTC_TIMESTAMP()
                GROUP BY p.id
                ORDER BY shared_categories DESC, p.published_at DESC, p.id DESC
                LIMIT ?
                SQL,
                self::SUMMARY_COLUMNS,
            ),
            [$postId, $limit],
        );

        return array_map(PostSummary::fromRow(...), $rows);
    }

    /**
     * @param list<int> $excludeIds
     * @return list<PostSummary>
     */
    public function findLatestPublished(int $limit, array $excludeIds = []): array
    {
        $exclude = $excludeIds === []
            ? ''
            : sprintf('AND p.id NOT IN (%s)', implode(', ', array_fill(0, count($excludeIds), '?')));

        $rows = $this->db->fetchAll(
            sprintf(
                <<<'SQL'
                SELECT %s
                FROM posts p
                WHERE p.published_at <= UTC_TIMESTAMP()
                  %s
                ORDER BY p.published_at DESC, p.id DESC
                LIMIT ?
                SQL,
                self::SUMMARY_COLUMNS,
                $exclude,
            ),
            [...$excludeIds, $limit],
        );

        return array_map(PostSummary::fromRow(...), $rows);
    }

    private static function orderBy(PostSorting $sorting): string
    {
        return match ($sorting) {
            PostSorting::Newest  => 'p.published_at DESC, p.id DESC',
            PostSorting::Popular => 'p.views DESC, p.id DESC',
        };
    }
}
