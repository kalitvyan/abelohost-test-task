<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Exception\HttpException;
use App\Http\Routing\Router;
use App\View\SmartyRenderer;
use Closure;
use LogicException;
use Throwable;

final class Kernel
{
    /**
     * @param Closure(class-string): object $resolveController
     */
    public function __construct(
        private readonly Router $router,
        private readonly Closure $resolveController,
        private readonly SmartyRenderer $view,
        private readonly bool $debug,
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            if ($request->path !== '/' && str_ends_with($request->path, '/')) {
                return $this->redirectWithoutTrailingSlash($request);
            }

            $match = $this->router->match($request->method, $request->path);
            [$class, $method] = $match->route->handler;

            $response = ($this->resolveController)($class)
                ->{$method}($request->withRouteParams($match->params));

            if (!$response instanceof Response) {
                throw new LogicException(
                    sprintf('%s::%s() must return %s', $class, $method, Response::class),
                );
            }

            return $response;
        } catch (HttpException $e) {
            return $this->error($e->statusCode, $e->getMessage(), $e->headers);
        } catch (Throwable $e) {
            error_log((string) $e);

            return $this->error(500, 'Internal Server Error', [], $e);
        }
    }

    private function redirectWithoutTrailingSlash(Request $request): Response
    {
        // Collapse leading slashes: '//evil.com/'
        // must not become a protocol-relative Location.
        $path = '/' . trim($request->path, '/');

        return Response::redirect(
            location: $path . $request->queryString(),
            status: 301,
        );
    }

    /**
     * @param array<string, string> $headers
     */
    private function error(
        int $status,
        string $message,
        array $headers = [],
        ?Throwable $e = null,
    ): Response {
        $details = $this->debug && $e !== null ? (string) $e : null;

        try {
            $body = $this->view->render('errors/error.tpl', [
                'status'  => $status,
                'message' => $message,
                'details' => $details,
            ]);

            return Response::html($body, $status, $headers);
        } catch (Throwable $renderError) {
            // The error page itself failed
            // (broken layout, unwritable compile dir): degrade to plain text.
            error_log((string) $renderError);

            return Response::text(
                body: $details === null ? $message : $message . "\n\n" . $details,
                status: $status,
                headers: $headers,
            );
        }
    }
}
