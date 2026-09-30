<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Request;
use App\Http\Response;

final class CategoryController
{
    public function show(Request $request): Response
    {
        return Response::text(sprintf(
            'category=%s sort=%s page=%d',
            $request->route('slug'),
            $request->query('sort', 'date'),
            $request->queryInt('page', 1, min: 1),
        ));
    }
}
