<article class="post-card">
  <a class="post-card__image-link" href="{url name='post.show' slug=$post->slug}" tabindex="-1" aria-hidden="true">
    {if $post->image}
      <img class="post-card__image" src="{$post->image}" alt="" width="1200" height="630" loading="lazy">
    {/if}
  </a>

  <div class="post-card__body">
    <h3 class="post-card__title">
      <a href="{url name='post.show' slug=$post->slug}">{$post->title}</a>
    </h3>
    <p class="post-card__description">{$post->description}</p>

    <footer class="post-card__meta">
      <time datetime="{$post->publishedAt|date:'c'}">{$post->publishedAt|date}</time>
      <span>{$post->views} {$post->views|plural:'просмотр':'просмотра':'просмотров'}</span>
    </footer>
  </div>
</article>
