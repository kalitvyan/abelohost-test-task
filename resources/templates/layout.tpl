<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{block name=title}{$app.name}{/block}</title>
  <link rel="stylesheet" href="{asset path='/assets/css/app.css'}">
  {block name=head}{/block}
</head>
<body>
  <header class="site-header">
    <div class="container site-header__inner">
      <a class="site-header__logo" href="{url name='home'}">{$app.name}</a>
    </div>
  </header>

  <main class="site-main container">
    {block name=content}{/block}
  </main>

  <footer class="site-footer">
    <div class="container">&copy; {$app.year} {$app.name}</div>
  </footer>
</body>
</html>
