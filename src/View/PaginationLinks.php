<?php

declare(strict_types=1);

namespace App\View;

use App\Support\Pagination;

final class PaginationLinks
{
    /**
     * @param callable(int): string $url
     * @return array{
     *     hasPages: bool,
     *     previous: string|null,
     *     next: string|null,
     *     pages: list<array{number: int|null, url: string|null, current: bool}>
     * }
     */
    public static function build(Pagination $pagination, callable $url): array
    {
        return [
            'hasPages' => $pagination->hasPages(),
            'previous' => $pagination->hasPrevious() ? $url($pagination->page - 1) : null,
            'next'     => $pagination->hasNext() ? $url($pagination->page + 1) : null,
            'pages'    => array_map(
                static fn (?int $number): array => [
                    'number'  => $number,
                    'url'     => $number === null ? null : $url($number),
                    'current' => $number === $pagination->page,
                ],
                $pagination->pages(),
            ),
        ];
    }
}
