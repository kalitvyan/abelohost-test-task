<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Database;
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

    private static function orderBy(PostSorting $sorting): string
    {
        return match ($sorting) {
            PostSorting::Newest  => 'p.published_at DESC, p.id DESC',
            PostSorting::Popular => 'p.views DESC, p.id DESC',
        };
    }
}
