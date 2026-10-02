<?php

declare(strict_types=1);

namespace App\Support;

final class Slugger
{
    private const TRANSLIT = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];

    public static function slugify(string $text, int $maxLength = 80): string
    {
        $slug = strtr(mb_strtolower($text, 'UTF-8'), self::TRANSLIT);
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $slug), '-');

        if (strlen($slug) > $maxLength) {
            $slug = substr($slug, 0, $maxLength);
            $lastDash = strrpos($slug, '-');
            $slug = $lastDash !== false ? substr($slug, 0, $lastDash) : $slug;
        }

        return $slug !== '' ? $slug : 'n-a';
    }
}
