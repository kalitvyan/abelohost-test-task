<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class Pagination
{
    public readonly int $lastPage;

    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $total,
    ) {
        if ($page < 1 || $perPage < 1 || $total < 0) {
            throw new InvalidArgumentException('Invalid pagination parameters');
        }

        // Integer ceil without floats. An empty list still has page 1.
        $this->lastPage = max(1, intdiv($total + $perPage - 1, $perPage));
    }

    public function isOutOfRange(): bool
    {
        return $this->page > $this->lastPage;
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function hasPages(): bool
    {
        return $this->lastPage > 1;
    }

    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    public function hasNext(): bool
    {
        return $this->page < $this->lastPage;
    }

    /**
     * @return list<int|null>
     */
    public function pages(int $window = 2): array
    {
        $pages = [];
        $previous = 0;

        for ($i = 1; $i <= $this->lastPage; $i++) {
            if ($i !== 1 && $i !== $this->lastPage && abs($i - $this->page) > $window) {
                continue;
            }

            if ($i - $previous === 2) {
                $pages[] = $previous + 1;
            } elseif ($i - $previous > 2) {
                $pages[] = null;
            }

            $pages[] = $i;
            $previous = $i;
        }

        return $pages;
    }
}
