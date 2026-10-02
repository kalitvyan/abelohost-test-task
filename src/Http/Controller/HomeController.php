<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Repository\CategoryRepository;
use App\View\SmartyRenderer;

final class HomeController
{
    private const LATEST_POSTS_PER_CATEGORY = 3;

    public function __construct(
        private readonly SmartyRenderer $view,
        private readonly CategoryRepository $categories,
    ) {
    }

    public function index(Request $request): Response
    {
        return Response::html($this->view->render('pages/home.tpl', [
            'previews' => $this->categories->findPreviewsWithLatestPosts(self::LATEST_POSTS_PER_CATEGORY),
        ]));
    }
}
