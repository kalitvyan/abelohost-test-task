# AbeloHost Test Task

Блог с категориями и статьями на чистом PHP без фреймворков.

**Стек:** PHP 8.1+ (рантайм 8.3), MySQL 8.0, Smarty 5, Nginx, Docker.

## Быстрый старт

Нужны Docker с Compose v2 и `make`.

```bash
git clone git@github.com:kalitvyan/abelohost-test-task.git
cd abelohost-test-task
make setup
```

Откройте http://127.0.0.1:8080.

`make setup` создаёт `.env` из `.env.example`, поднимает контейнеры, ставит зависимости, накатывает миграции и заполняет базу тестовыми данными.

## Команды

| Команда | Что делает |
|---|---|
| `make setup` | Полная установка с нуля |
| `make up` / `make down` | Запуск / остановка контейнеров |
| `make migrate` | Применить новые миграции |
| `make fresh` | Удалить все таблицы и накатить миграции заново |
| `make seed` | Пересоздать тестовые данные (`args="--posts=200 --seed=7"`) |
| `make reset` | `fresh` + `seed` |
| `make db` | MySQL-консоль |
| `make sh` | Shell в PHP-контейнере |
| `make logs` | Логи контейнеров |
| `make css` | Собрать CSS из SCSS (сжатый, без source map) |
| `make css-watch` | Пересборка CSS при изменениях, с source map |

Сидинг детерминирован: при одинаковом `--seed` генерируются одинаковые тексты, slug'и и распределение по категориям.

## Страницы

| URL | Описание |
|---|---|
| `/` | Категории, в которых есть статьи, по 3 последних поста в каждой |
| `/category/{slug}` | Статьи категории: сортировка `?sort=date\|views`, пагинация `?page=N` |
| `/post/{slug}` | Статья, счётчик просмотров, 3 похожие статьи |

## Структура

```
bin/console            CLI: migrate, migrate:fresh, db:seed
bootstrap/app.php      Автозагрузка, обработчик ошибок, контейнер
config/                Конфигурация (из переменных окружения) и маршруты
database/migrations/   SQL-миграции, одна команда на файл
database/seeders/      Данные для сидинга
docker/                Конфиги Nginx и PHP
public/                Document root: index.php, статика
resources/templates/   Smarty-шаблоны: layout, страницы, партиалы
src/
  Container.php        Composition root
  Database/            PDO-обёртка, мигратор, сидер
  Http/                Request, Response, Router, Kernel, контроллеры
  Model/               Read-модели и enum сортировки
  Repository/          SQL-запросы
  Support/             Env, пагинация, slug, склонения
  View/                Smarty-рендерер, презентер пагинации
```
