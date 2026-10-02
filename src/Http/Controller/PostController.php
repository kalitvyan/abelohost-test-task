<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Exception\NotFoundException;
use App\Http\Request;
use App\Http\Response;
use App\Model\Post;
use App\Model\PostSummary;
use App\Repository\PostRepository;
use App\View\SmartyRenderer;

final class PostController
{
    private const RELATED_LIMIT = 3;

    public function __construct(
        private readonly SmartyRenderer $view,
        private readonly PostRepository $posts,
    ) {
    }

    public function show(Request $request): Response
    {
        $slug = $request->route('slug');

        if ($request->method === 'GET') {
            $this->posts->incrementViews($slug);
        }

        $post = $this->posts->findPublishedBySlug($slug)
            ?? throw new NotFoundException('Post not found');

        return Response::html($this->view->render('pages/post.tpl', [
            'post'    => $post,
            'related' => $this->related($post),
        ]));
    }

    /**
     * @return list<PostSummary>
     */
    private function related(Post $post): array
    {
        $related = $this->posts->findRelated($post->id, self::RELATED_LIMIT);
        $missing = self::RELATED_LIMIT - count($related);

        if ($missing > 0) {
            $exclude = [$post->id, ...array_map(static fn (PostSummary $p): int => $p->id, $related)];
            $related = [...$related, ...$this->posts->findLatestPublished($missing, $exclude)];
        }

        return $related;
    }
}
