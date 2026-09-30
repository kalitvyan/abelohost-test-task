<?php

declare(strict_types=1);

use App\Http\Controller\CategoryController;
use App\Http\Controller\HomeController;
use App\Http\Controller\PostController;
use App\Http\Routing\Router;

return static function (Router $router): void {
    $router->get('/', [HomeController::class, 'index'], 'home');
    $router->get('/category/{slug:[a-z0-9-]+}', [CategoryController::class, 'show'], 'category.show');
    $router->get('/post/{slug:[a-z0-9-]+}', [PostController::class, 'show'], 'post.show');
};
