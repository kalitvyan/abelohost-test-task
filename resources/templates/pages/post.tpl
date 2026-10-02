{extends file='layout.tpl'}

{block name=title}{$post->title} — {$smarty.block.parent}{/block}

{block name=head}
  <meta name="description" content="{$post->description}">
{/block}

{block name=content}
  <article class="post">
    <header class="post__header">
      <nav class="post__categories" aria-label="Категории статьи">
        {foreach $post->categories as $category}
          <a class="tag" href="{url name='category.show' slug=$category->slug}">{$category->name}</a>
        {/foreach}
      </nav>

      <h1 class="post__title">{$post->title}</h1>
      <p class="post__lead">{$post->description}</p>

      <div class="post__meta">
        <time datetime="{$post->publishedAt|date:'c'}">{$post->publishedAt|date}</time>
        {if $post->updatedAt|date != $post->publishedAt|date}
          <span>обновлено <time datetime="{$post->updatedAt|date:'c'}">{$post->updatedAt|date}</time></span>
        {/if}
        <span>{$post->views} {$post->views|plural:'просмотр':'просмотра':'просмотров'}</span>
      </div>
    </header>

    {if $post->image}
      <img class="post__image" src="{$post->image}" alt="" width="1200" height="630" fetchpriority="high">
    {/if}

    <div class="post__content">
      {foreach $post->paragraphs() as $paragraph}
        <p>{$paragraph}</p>
      {/foreach}
    </div>
  </article>

  {if $related}
    <section class="related" aria-labelledby="related-title">
      <h2 id="related-title" class="related__title">Похожие статьи</h2>
      <div class="post-grid">
        {foreach $related as $relatedPost}
          {include file='partials/post-card.tpl' post=$relatedPost}
        {/foreach}
      </div>
    </section>
  {/if}
{/block}
