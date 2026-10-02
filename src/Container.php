<?php

declare(strict_types=1);

namespace App;

use App\Database\Database;
use App\Database\Migrator;
use App\Database\Seeder\BlogSeeder;
use App\Database\Seeder\PlaceholderImage;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Http\Controller\CategoryController;
use App\Http\Controller\HomeController;
use App\Http\Controller\PostController;
use App\Http\Kernel;
use App\Http\Routing\Router;
use App\View\SmartyRenderer;
use LogicException;

final class Container
{
    /** @var array<string, array<string, mixed>> */
    private array $config = [];

    private ?Database $database = null;
    private ?Migrator $migrator = null;
    private ?Router $router = null;
    private ?SmartyRenderer $view = null;
    private ?Kernel $kernel = null;
    private ?CategoryRepository $categoryRepository = null;
    private ?PostRepository $postRepository = null;

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

    public function seeder(): BlogSeeder
    {
        return new BlogSeeder(
            db: $this->database(),
            images: new PlaceholderImage($this->path('public/uploads/posts'), '/uploads/posts'),
            categoriesFile: $this->path('database/seeders/categories.php'),
        );
    }

    public function categoryRepository(): CategoryRepository
    {
        return $this->categoryRepository ??= new CategoryRepository($this->database());
    }

    public function postRepository(): PostRepository
    {
        return $this->postRepository ??= new PostRepository($this->database());
    }

    public function router(): Router
    {
        if ($this->router === null) {
            $this->router = new Router();
            (require $this->path('config/routes.php'))($this->router);
        }

        return $this->router;
    }

    public function view(): SmartyRenderer
    {
        if ($this->view === null) {
            $app = $this->config('app');

            $this->view = new SmartyRenderer(
                templateDir: $this->path('resources/templates'),
                compileDir: $this->path('var/cache/smarty'),
                router: $this->router(),
                timezone: new \DateTimeZone((string) $app['timezone']),
                debug: (bool) $app['debug'],
            );

            $this->view->share('app', [
                'name' => (string) $app['name'],
                'year' => date('Y'),
            ]);
        }

        return $this->view;
    }

    public function kernel(): Kernel
    {
        return $this->kernel ??= new Kernel(
            router: $this->router(),
            resolveController: fn (string $class): object => $this->controller($class),
            view: $this->view(),
            debug: (bool) $this->config('app')['debug'],
        );
    }

    /**
     * @param class-string $class
     */
    private function controller(string $class): object
    {
        return match ($class) {
            HomeController::class => new HomeController(
                $this->view(),
                $this->categoryRepository(),
            ),
            CategoryController::class => new CategoryController(
                $this->view(),
                $this->router(),
                $this->categoryRepository(),
                $this->postRepository(),
            ),
            PostController::class => new PostController(
                $this->view(),
                $this->postRepository(),
            ),
            default => throw new LogicException("Controller {$class} is not registered"),
        };
    }
}
