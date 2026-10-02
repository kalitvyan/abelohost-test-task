<?php

declare(strict_types=1);

namespace App\Database\Seeder;

use App\Database\Database;
use App\Support\Slugger;
use DateTimeInterface;
use Faker\Factory;
use Faker\Generator;

final class BlogSeeder
{
    private const POSTS_CHUNK_SIZE = 50;
    private const SCHEDULED_PERCENT = 5;
    private const MAX_CATEGORIES_PER_POST = 3;
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private readonly Database $db,
        private readonly PlaceholderImage $images,
        private readonly string $categoriesFile,
    ) {
    }

    /**
     * @return array{categories: int, posts: int, links: int}
     */
    public function run(int $postCount, int $seed): array
    {
        $faker = Factory::create('ru_RU');
        $faker->seed($seed);

        $this->images->clear();

        $categories = $this->buildCategories($faker);
        $posts = $this->buildPosts($faker, $postCount);
        $links = $this->buildLinks($faker, $posts, $categories);

        $this->db->transaction(function (Database $db) use ($categories, $posts, $links): void {
            // DELETE instead of TRUNCATE
            $db->execute('DELETE FROM category_post');
            $db->execute('DELETE FROM posts');
            $db->execute('DELETE FROM categories');

            $db->insertMany('categories', $categories);
            $db->insertMany('posts', $posts, self::POSTS_CHUNK_SIZE);
            $db->insertMany('category_post', $links);
        });

        return [
            'categories' => count($categories),
            'posts'      => count($posts),
            'links'      => count($links),
        ];
    }

    /**
     * @return list<array{id: int, slug: string, name: string, description: string, created_at: string, updated_at: string}>
     */
    private function buildCategories(Generator $faker): array
    {
        /** @var list<array{name: string, description: string}> $data */
        $data = require $this->categoriesFile;
        $rows = [];

        foreach ($data as $index => $category) {
            $createdAt = $this->format($faker->dateTimeBetween('-2 years', '-1 year', 'UTC'));

            $rows[] = [
                'id'          => $index + 1,
                'slug'        => Slugger::slugify($category['name']),
                'name'        => $category['name'],
                'description' => $category['description'],
                'created_at'  => $createdAt,
                'updated_at'  => $createdAt,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array<string, scalar|null>>
     */
    private function buildPosts(Generator $faker, int $count): array
    {
        $rows = [];
        $usedSlugs = [];

        for ($id = 1; $id <= $count; $id++) {
            $title = $this->sentence($faker->realText($faker->numberBetween(40, 80)));
            $slug = $this->uniqueSlug(Slugger::slugify($title), $usedSlugs);

            $isScheduled = $faker->boolean(self::SCHEDULED_PERCENT);
            $publishedAt = $isScheduled
                ? $faker->dateTimeBetween('+1 day', '+30 days', 'UTC')
                : $faker->dateTimeBetween('-1 year', 'now', 'UTC');
            $createdAt = $isScheduled ? $faker->dateTimeBetween('-7 days', 'now', 'UTC') : $publishedAt;
            $updatedAt = $faker->dateTimeBetween($createdAt, 'now', 'UTC');

            $rows[] = [
                'id'           => $id,
                'slug'         => $slug,
                'title'        => $title,
                'description'  => $faker->realText($faker->numberBetween(160, 250)),
                'content'      => $this->content($faker),
                'image'        => $this->images->create($slug, mb_strimwidth($title, 0, 40, '…', 'UTF-8')),
                'views'        => $isScheduled ? 0 : $this->views($faker),
                'published_at' => $this->format($publishedAt),
                'created_at'   => $this->format($createdAt),
                'updated_at'   => $this->format($updatedAt),
            ];
        }

        return $rows;
    }

    /**
     * @param list<array<string, scalar|null>> $posts
     * @param list<array{id: int}>             $categories
     * @return list<array{category_id: int, post_id: int}>
     */
    private function buildLinks(Generator $faker, array $posts, array $categories): array
    {
        // The last category stays empty on purpose (see database/seeders/categories.php).
        $assignable = array_column(array_slice($categories, 0, -1), 'id');
        $maxPerPost = min(self::MAX_CATEGORIES_PER_POST, count($assignable));
        $links = [];

        foreach ($posts as $post) {
            foreach ($faker->randomElements($assignable, $faker->numberBetween(1, $maxPerPost)) as $categoryId) {
                $links[] = ['category_id' => (int) $categoryId, 'post_id' => (int) $post['id']];
            }
        }

        return $links;
    }

    private function content(Generator $faker): string
    {
        $paragraphs = [];

        for ($i = 0, $n = $faker->numberBetween(4, 8); $i < $n; $i++) {
            $paragraphs[] = $faker->realText($faker->numberBetween(300, 700));
        }

        return implode("\n\n", $paragraphs);
    }

    private function views(Generator $faker): int
    {
        return (int) round(10 ** $faker->randomFloat(3, 0, 4));
    }

    private function sentence(string $text): string
    {
        return (string) preg_replace('/[\s\p{P}]+$/u', '', $text);
    }

    /**
     * @param array<string, true> $used
     */
    private function uniqueSlug(string $base, array &$used): string
    {
        $slug = $base;

        for ($i = 2; isset($used[$slug]); $i++) {
            $slug = "{$base}-{$i}";
        }

        $used[$slug] = true;

        return $slug;
    }

    private function format(DateTimeInterface $date): string
    {
        return $date->format(self::DATE_FORMAT);
    }
}
