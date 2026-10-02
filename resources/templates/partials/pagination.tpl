{if $pagination.hasPages}
  <nav class="pagination" aria-label="Страницы">
    {if $pagination.previous}
      <a class="pagination__link pagination__link--prev" href="{$pagination.previous}" rel="prev">← Назад</a>
    {/if}

    {foreach $pagination.pages as $page}
      {if !$page.number}
        <span class="pagination__gap" aria-hidden="true">…</span>
      {elseif $page.current}
        <span class="pagination__link pagination__link--current" aria-current="page">{$page.number}</span>
      {else}
        <a class="pagination__link" href="{$page.url}">{$page.number}</a>
      {/if}
    {/foreach}

    {if $pagination.next}
      <a class="pagination__link pagination__link--next" href="{$pagination.next}" rel="next">Вперёд →</a>
    {/if}
  </nav>
{/if}
