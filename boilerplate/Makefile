.PHONY: help build up down restart logs shell composer sf db-create db-migrate db-diff cache-clear test test-unit test-integration test-db-create test-db-migrate test-db-setup cron-logs cron-test init

# Override if you only have the legacy hyphenated binary: make COMPOSE=docker-compose up
COMPOSE ?= docker compose

help:
	@echo "Available commands:"
	@echo "  make build            - Build Docker containers"
	@echo "  make up               - Start Docker containers"
	@echo "  make down             - Stop Docker containers"
	@echo "  make restart          - Restart Docker containers"
	@echo "  make logs             - Follow Docker logs"
	@echo "  make shell            - Shell into the PHP container"
	@echo "  make composer c='...' - Run a Composer command"
	@echo "  make sf c='...'       - Run a Symfony console command"
	@echo "  make db-create        - Create the database"
	@echo "  make db-migrate       - Run migrations"
	@echo "  make db-diff          - Diff entities into a migration (REVIEW IT — see dev-gotchas)"
	@echo "  make cache-clear      - Clear the Symfony cache"
	@echo "  make test             - Run the full PHPUnit suite"
	@echo "  make test-unit        - Unit suite only (no DB)"
	@echo "  make test-integration - Integration suite only"
	@echo "  make test-db-setup    - Create + migrate the separate test database"
	@echo "  make cron-logs        - Follow cron container logs"
	@echo "  make cron-test        - Run the scheduled commands by hand"
	@echo "  make init             - Bootstrap a fresh project in the container"

build:
	$(COMPOSE) build

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

restart:
	$(COMPOSE) restart

logs:
	$(COMPOSE) logs -f

shell:
	$(COMPOSE) exec php bash

composer:
	$(COMPOSE) exec php composer $(c)

sf:
	$(COMPOSE) exec php php bin/console $(c)

db-create:
	$(COMPOSE) exec php php bin/console doctrine:database:create --if-not-exists

db-migrate:
	$(COMPOSE) exec php php bin/console doctrine:migrations:migrate --no-interaction

# Entities declare no relations, so diff proposes DROPping every hand-written FK
# and index. Read the generated migration before keeping it.
db-diff:
	$(COMPOSE) exec php php bin/console doctrine:migrations:diff

cache-clear:
	$(COMPOSE) exec php php bin/console cache:clear

test:
	$(COMPOSE) exec php vendor/bin/phpunit

test-unit:
	$(COMPOSE) exec php vendor/bin/phpunit --testsuite=Unit

test-integration:
	$(COMPOSE) exec php vendor/bin/phpunit --testsuite=Integration

# The test database is separate and persistent. Run test-db-setup once after a
# fresh clone, and again after `down -v` or any new migration.
test-db-create:
	$(COMPOSE) exec php php bin/console doctrine:database:create --env=test --if-not-exists

test-db-migrate:
	$(COMPOSE) exec php php bin/console doctrine:migrations:migrate --env=test --no-interaction

test-db-setup: test-db-create test-db-migrate

cron-logs:
	$(COMPOSE) logs -f cron

# {{FILL: one line per scheduled command, mirroring API/docker/cron/crontab}}
cron-test:
	$(COMPOSE) exec cron php bin/console list

init:
	$(COMPOSE) exec php bash /var/www/html/docker/scripts/init-project.sh
