<?php

declare(strict_types=1);

namespace App;

use App\Database\Database;
use App\Database\Migrator;
use App\Http\Controller\CategoryController;
use App\Http\Controller\HomeController;
use App\Http\Controller\PostController;
use App\Http\Kernel;
use App\Http\Routing\Router;
use LogicException;

final class Container
{
    /** @var array<string, array<string, mixed>> */
    private array $config = [];

    private ?Database $database = null;
    private ?Migrator $migrator = null;
    private ?Router $router = null;
    private ?Kernel $kernel = null;

    public function __construct(private readonly string $basePath)
    {
    }

    public function path(string $relative = ''): string
    {
        return rtrim($this->basePath . '/' . ltrim($relative, '/'), '/');
    }

    /**
     * @return array<string, mixed>
     */
    public function config(string $name): array
    {
        return $this->config[$name] ??= require $this->path("config/{$name}.php");
    }

    public function database(): Database
    {
        if ($this->database === null) {
            /** @var array{host: string, port: int, database: string, username: string, password: string} $config */
            $config = $this->config('database');
            $this->database = Database::connect($config);
        }

        return $this->database;
    }

    public function migrator(): Migrator
    {
        return $this->migrator ??= new Migrator($this->database(), $this->path('database/migrations'));
    }

    public function router(): Router
    {
        if ($this->router === null) {
            $this->router = new Router();
            (require $this->path('config/routes.php'))($this->router);
        }

        return $this->router;
    }

    public function kernel(): Kernel
    {
        return $this->kernel ??= new Kernel(
            $this->router(),
            fn (string $class): object => $this->controller($class),
            (bool) $this->config('app')['debug'],
        );
    }

    /**
     * @param class-string $class
     */
    private function controller(string $class): object
    {
        return match ($class) {
            HomeController::class     => new HomeController(),
            CategoryController::class => new CategoryController(),
            PostController::class     => new PostController(),
            default                   => throw new LogicException("Controller {$class} is not registered"),
        };
    }
}
