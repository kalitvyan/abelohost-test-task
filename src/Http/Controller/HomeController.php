<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Request;
use App\Http\Response;
use App\View\SmartyRenderer;

final class HomeController
{
    public function __construct(private readonly SmartyRenderer $view)
    {
    }

    public function index(Request $request): Response
    {
        return Response::html(
            body: $this->view->render('pages/home.tpl'),
        );
    }
}
