<?php

declare(strict_types=1);

set_exception_handler(static function (Throwable $e): void {
    error_log((string) $e);

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
    }

    echo 'Internal Server Error';
});

/** @var App\Container $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';

$request = App\Http\Request::fromGlobals();

$app->kernel()->handle($request)->send(withBody: $request->method !== 'HEAD');
