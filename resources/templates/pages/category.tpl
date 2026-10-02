{extends file='layout.tpl'}

{block name=title}{$category->name} — {$smarty.block.parent}{/block}

{block name=content}
  <header class="page-header">
    <h1 class="page-header__title">{$category->name}</h1>
    <p class="page-header__description">{$category->description}</p>
  </header>

  {if $posts}
    <nav class="sorting" aria-label="Сортировка статей">
      {foreach $sortLinks as $link}
        <a class="sorting__link{if $link.active} sorting__link--active{/if}"
           href="{$link.url}"{if $link.active} aria-current="true"{/if}>{$link.label}</a>
      {/foreach}
    </nav>

    <div class="post-grid">
      {foreach $posts as $post}
        {include file='partials/post-card.tpl' post=$post}
      {/foreach}
    </div>

    {include file='partials/pagination.tpl' pagination=$pagination}
  {else}
    <p class="empty-state">В этой категории пока нет статей.</p>
  {/if}
{/block}
