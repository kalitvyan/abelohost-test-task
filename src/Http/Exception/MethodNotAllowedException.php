<?php

declare(strict_types=1);

namespace App\Http\Exception;

final class MethodNotAllowedException extends HttpException
{
    /**
     * @param list<string> $allowed
     */
    public function __construct(array $allowed)
    {
        parent::__construct(405, 'Method Not Allowed', ['Allow' => implode(', ', $allowed)]);
    }
}
