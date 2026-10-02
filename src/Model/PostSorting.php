<?php

declare(strict_types=1);

namespace App\Model;

enum PostSorting: string
{
    case Newest = 'date';
    case Popular = 'views';

    public static function fromQuery(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::default();
    }

    public static function default(): self
    {
        return self::Newest;
    }

    public function isDefault(): bool
    {
        return $this === self::default();
    }

    public function label(): string
    {
        return match ($this) {
            self::Newest  => 'Сначала новые',
            self::Popular => 'Популярные',
        };
    }
}
