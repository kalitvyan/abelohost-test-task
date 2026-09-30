<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Request;
use App\Http\Response;

final class HomeController
{
    public function index(Request $request): Response
    {
        return Response::text('home');
    }
}
