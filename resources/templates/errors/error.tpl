{extends file='layout.tpl'}

{block name=title}{$status} — {$smarty.block.parent}{/block}

{block name=content}
  <section class="error-page">
    <h1 class="error-page__code">{$status}</h1>
    <p class="error-page__message">

      {if $status == 404}
        Страница не найдена
      {elseif $status == 405}
        Метод не поддерживается
      {else}
        Что-то пошло не так
      {/if}

    </p>

    <a class="button" href="{url name='home'}">На главную</a>

    {if $details}
      <pre class="error-page__details">{$details}</pre>
    {/if}
  </section>
{/block}
