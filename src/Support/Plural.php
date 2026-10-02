<?php

declare(strict_types=1);

namespace App\Support;

final class Plural
{
    /**
     * Russian plural form: 1 просмотр, 3 просмотра, 5 просмотров, 11 просмотров, 21 просмотр.
     */
    public static function ru(int $n, string $one, string $few, string $many): string
    {
        $mod10 = abs($n) % 10;
        $mod100 = abs($n) % 100;

        return match (true) {
            $mod10 === 1 && $mod100 !== 11                       => $one,
            $mod10 >= 2 && $mod10 <= 4 && ($mod100 < 12 || $mod100 > 14) => $few,
            default                                              => $many,
        };
    }
}
