DC  = docker compose
PHP = $(DC) exec php
SASS = $(DC) run --rm sass

.PHONY: init setup up down restart logs sh install composer migrate fresh db seed reset css css-watch

setup: up install css reset

css:
	$(SASS) --style=compressed --no-source-map $(CSS)

css-watch:
	$(SASS) --watch --style=expanded --embed-source-map $(CSS)

init:
	@test -f .env || cp .env.example .env
	@echo ".env ready"

up: init
	$(DC) up -d --build

down:
	$(DC) down

restart: down up

logs:
	$(DC) logs -f

sh:
	$(PHP) sh

install:
	$(PHP) composer install

composer:
	$(PHP) composer $(c)

migrate:
	$(PHP) php bin/console migrate

fresh:
	$(PHP) php bin/console migrate:fresh

seed:
	$(PHP) php bin/console db:seed $(args)

reset: fresh seed

db:
	$(DC) exec mysql sh -c 'mysql -u"$$MYSQL_USER" -p"$$MYSQL_PASSWORD" "$$MYSQL_DATABASE"'
