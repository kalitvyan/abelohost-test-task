<?php

declare(strict_types=1);

namespace App\View;

use App\Http\Routing\Router;
use App\Support\Plural;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Smarty\Smarty;

final class SmartyRenderer
{
    private readonly Smarty $smarty;

    public function __construct(
        string $templateDir,
        string $compileDir,
        Router $router,
        DateTimeZone $timezone,
        bool $debug,
    ) {
        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir($templateDir);
        $this->smarty->setCompileDir($compileDir);
        $this->smarty->setEscapeHtml(true);
        $this->smarty->setCompileCheck($debug ? Smarty::COMPILECHECK_ON : Smarty::COMPILECHECK_OFF);

        $this->smarty->registerPlugin(
            Smarty::PLUGIN_FUNCTION,
            'url',
            static function (array $params) use ($router): string {
                $name = (string) ($params['name'] ?? throw new \InvalidArgumentException('{url} requires "name"'));
                $query = (array) ($params['query'] ?? []);
                unset($params['name'], $params['query']);

                return htmlspecialchars($router->url($name, $params, $query), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            },
        );

        // {$post->publishedAt|date} → 05.03.2026; {$post->publishedAt|date:'c'} → ISO 8601 for <time datetime>
        $this->smarty->registerPlugin(
            Smarty::PLUGIN_MODIFIER,
            'date',
            static fn (DateTimeInterface $date, string $format = 'd.m.Y'): string
                => DateTimeImmutable::createFromInterface($date)->setTimezone($timezone)->format($format),
        );

        // {$post->views|plural:'просмотр':'просмотра':'просмотров'}
        $this->smarty->registerPlugin(
            Smarty::PLUGIN_MODIFIER,
            'plural',
            static fn (int $n, string $one, string $few, string $many): string => Plural::ru($n, $one, $few, $many),
        );
    }

    /**
     * Variables shared by every template (site name, etc.).
     */
    public function share(string $key, mixed $value): void
    {
        $this->smarty->assign($key, $value);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $tpl = $this->smarty->createTemplate($template);
        $tpl->assign($data);

        return $tpl->fetch();
    }
}
