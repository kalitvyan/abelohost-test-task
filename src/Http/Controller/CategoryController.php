<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Exception\NotFoundException;
use App\Http\Request;
use App\Http\Response;
use App\Http\Routing\Router;
use App\Model\Category;
use App\Model\PostSorting;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Support\Pagination;
use App\View\PaginationLinks;
use App\View\SmartyRenderer;

final class CategoryController
{
    private const PER_PAGE = 9;

    public function __construct(
        private readonly SmartyRenderer $view,
        private readonly Router $router,
        private readonly CategoryRepository $categories,
        private readonly PostRepository $posts,
    ) {
    }

    public function show(Request $request): Response
    {
        $category = $this->categories->findBySlug($request->route('slug'))
            ?? throw new NotFoundException('Category not found');

        $sorting = PostSorting::fromQuery($request->query('sort'));
        $pagination = new Pagination(
            page: $request->queryInt('page', 1, min: 1),
            perPage: self::PER_PAGE,
            total: $this->posts->countPublishedInCategory($category->id),
        );

        if ($pagination->isOutOfRange()) {
            throw new NotFoundException('Page not found');
        }

        $posts = $pagination->total === 0
            ? []
            : $this->posts
                ->findPublishedInCategory(
                    categoryId: $category->id,
                    sorting: $sorting,
                    limit: $pagination->perPage,
                    offset: $pagination->offset(),
                );

        return Response::html($this->view->render('pages/category.tpl', [
            'category'   => $category,
            'posts'      => $posts,
            'sortLinks'  => array_map(
                fn (PostSorting $option): array => [
                    'label'  => $option->label(),
                    'url'    => $this->url($category, $option, 1),
                    'active' => $option === $sorting,
                ],
                PostSorting::cases(),
            ),
            'pagination' => PaginationLinks::build(
                $pagination,
                fn (int $page): string => $this->url($category, $sorting, $page),
            ),
        ]));
    }

    private function url(
        Category $category,
        PostSorting $sorting,
        int $page
    ): string {
        return $this->router->url('category.show', ['slug' => $category->slug], [
            'sort' => $sorting->isDefault() ? null : $sorting->value,
            'page' => $page > 1 ? $page : null,
        ]);
    }
}
