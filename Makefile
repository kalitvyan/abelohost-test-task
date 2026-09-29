DC  = docker compose
PHP = $(DC) exec php

.PHONY: init up down restart logs sh install composer migrate seed

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
	@echo "TODO: stage 2"

seed:
	@echo "TODO: stage 5"
