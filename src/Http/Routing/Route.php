<?php

declare(strict_types=1);

namespace App\Http\Routing;

final class Route
{
    private const PLACEHOLDER = '/\{(\w+)(?::([^{}]+))?\}/';
    private const DEFAULT_SEGMENT = '[^/]+';

    public readonly string $regex;

    /**
     * @param array{class-string, non-empty-string} $handler
     */
    public function __construct(
        public readonly string $method,
        public readonly string $pattern,
        public readonly array $handler,
        public readonly string $name,
    ) {
        $this->regex = self::compile($pattern);
    }

    /**
     * @return array<string, string>|null named params, or null if the path does not match
     */
    public function match(string $path): ?array
    {
        if (preg_match($this->regex, $path, $matches) !== 1) {
            return null;
        }

        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array<string, string|int> $params
     */
    public function path(array $params): string
    {
        return (string) preg_replace_callback(
            self::PLACEHOLDER,
            static function (array $m) use ($params): string {
                $value = $params[$m[1]] ?? throw new \InvalidArgumentException("Missing route parameter '{$m[1]}'");

                return rawurlencode((string) $value);
            },
            $this->pattern,
        );
    }

    /**
     * '/post/{slug:[a-z0-9-]+}' → '#^/post/(?P<slug>[a-z0-9-]+)$#'
     */
    private static function compile(string $pattern): string
    {
        preg_match_all(self::PLACEHOLDER, $pattern, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $regex = '';
        $offset = 0;

        foreach ($matches as $m) {
            [$placeholder, $position] = $m[0];

            $regex .= preg_quote(substr($pattern, $offset, $position - $offset), '#');
            $regex .= sprintf('(?P<%s>%s)', $m[1][0], ($m[2][0] ?? '') !== '' ? $m[2][0] : self::DEFAULT_SEGMENT);

            $offset = $position + strlen($placeholder);
        }

        return '#^' . $regex . preg_quote(substr($pattern, $offset), '#') . '$#';
    }
}
