{extends file='layout.tpl'}

{block name=content}
  <h1>Главная</h1>
  <p>Скоро здесь будут статьи.</p>

  {* TODO: remove it *}
  <a href="{url name='category.show' slug='php' query=['sort' => 'views', 'page' => 2]}">test url</a>
{/block}
