<?php

declare(strict_types=1);

namespace App\Database\Seeder;

use RuntimeException;

/**
 * Generates SVG cover placeholders (1200×630, Open Graph ratio).
 */
final class PlaceholderImage
{
    public function __construct(
        private readonly string $directory,
        private readonly string $publicPrefix,
    ) {
    }

    public function clear(): void
    {
        foreach (glob($this->directory . '/*.svg') ?: [] as $file) {
            unlink($file);
        }
    }

    /**
     * @return string public path to the generated file
     */
    public function create(string $name, string $label): string
    {
        if (!is_dir($this->directory) && !mkdir($this->directory, 0775, true) && !is_dir($this->directory)) {
            throw new RuntimeException("Cannot create directory {$this->directory}");
        }

        $hue = crc32($name) % 360;

        $svg = sprintf(
            <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630">
              <defs>
                <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
                  <stop offset="0" stop-color="hsl(%1$d, 65%%, 55%%)"/>
                  <stop offset="1" stop-color="hsl(%2$d, 70%%, 30%%)"/>
                </linearGradient>
              </defs>
              <rect width="1200" height="630" fill="url(#g)"/>
              <circle cx="%3$d" cy="%4$d" r="%5$d" fill="rgba(255,255,255,0.12)"/>
              <text x="60" y="570" font-family="system-ui, sans-serif" font-size="48" fill="rgba(255,255,255,0.9)">%6$s</text>
            </svg>
            SVG,
            $hue,
            ($hue + 40) % 360,
            800 + $hue % 300,
            100 + $hue % 200,
            150 + $hue % 150,
            htmlspecialchars($label, ENT_XML1 | ENT_QUOTES, 'UTF-8'),
        );

        $file = "{$name}.svg";

        if (file_put_contents("{$this->directory}/{$file}", $svg) === false) {
            throw new RuntimeException("Cannot write {$file}");
        }

        return "{$this->publicPrefix}/{$file}";
    }
}
