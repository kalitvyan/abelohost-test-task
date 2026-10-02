{extends file='layout.tpl'}

{block name=content}
  {foreach $previews as $preview}
    <section class="category-section">
      <header class="category-section__header">
        <h2 class="category-section__title">
          <a href="{url name='category.show' slug=$preview->category->slug}">{$preview->category->name}</a>
        </h2>
        <a class="button" href="{url name='category.show' slug=$preview->category->slug}">Все статьи</a>
      </header>

      <div class="post-grid">
        {foreach $preview->posts as $post}
          {include file='partials/post-card.tpl' post=$post}
        {/foreach}
      </div>
    </section>
  {foreachelse}
    <p class="empty-state">Статей пока нет.</p>
  {/foreach}
{/block}
