#!make
include .env
ifneq ("$(wildcard .env.local)","")
	include .env.local
endif

# Setup ————————————————————————————————————————————————————————————————————————
DOCKER				:= @docker compose
DOCKER_EXEC			:=
DOCKER_TEST_EXEC	:= APP_ENV=test
CONSOLE 			:= symfony console
COMPOSER 			:= symfony composer
.DEFAULT_GOAL 		:= up
.PHONY: tests
SHELL := /bin/bash

isContainerRunning := $(shell docker version > /dev/null 2>&1 &&  docker-compose ps | grep education-app > /dev/null 2>&1 && echo 1)

user=
ifeq ($(isContainerRunning), 1)
	DOCKER_EXEC := $(DOCKER) exec -T $(user) app
	DOCKER_TEST_EXEC := $(DOCKER) exec -T -e APP_ENV=test $(user) app
	CONSOLE := $(DOCKER_EXEC) $(CONSOLE)
	COMPOSER := $(DOCKER_EXEC) $(COMPOSER)
endif

## —— 🐝 The Symfony Makefile 🐝 ———————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?## .*$$)|(^## )' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Composer 🧙‍♂️ ————————————————————————————————————————————————————————————
composer-install: composer.lock ## Install vendors according to the current composer.lock file
	$(COMPOSER) install -n

composer-update: composer.json ## Update vendors according to the composer.json file
	$(COMPOSER) update -W

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————

cc: ## Clear the cache.
	$(CONSOLE) cache:clear $(c)

warmup: ## Warmup the cache
	$(CONSOLE) cache:warmup

fix-perms: ## Fix permissions of all var files
	@chmod -R 777 var/*

assets: purge ## Install the assets with symlinks in the public folder
	$(CONSOLE) assets:install public/ --symlink --relative

rm-var: ## Purge cache and logs
	@rm -rf var/cache/* var/logs/*

purge: rm-var cc warmup assets fix-perms ## Purge symfony project (assets, permissions, wramup)

routes: ## get routes of project
	$(CONSOLE) debug:router

controller:
	$(CONSOLE) make:controller $@

entity:
	$(CONSOLE) make:entity $@

## —— Symfony doctrine 💻 ————————————————————————————————————————————————————————
doctrine-validate:  ## Check validate schema
	$(CONSOLE) doctrine:schema:validate  --skip-sync -n

migration: ## Make migration structure of databases
	$(CONSOLE) make:migration

migrate: create-database ## Migrate database structure
	$(CONSOLE) doctrine:migrations:migrate -n

drop-database: ## Add database if not exists
	$(CONSOLE) doctrine:database:drop --force --if-exists

create-database: ## Add database if not exists
	$(CONSOLE) doctrine:database:create --if-not-exists

doctrine-schema: create-database ## implement schema of database if not exists
	$(CONSOLE) doctrine:schema:create

load-fixtures: ## load fixtures
	$(CONSOLE) doctrine:fixtures:load -n

reset-database:  drop-database migrate load-fixtures  doctrine-validate ## Build the db, control the schema validity, load fixtures and check the migration status

## —— Database Backup & Restore 💾 ———————————————————————————————————————————
# Example PROD_DB_URL: mysql://user:password@host:port/database
PROD_DB_URL ?=
BACKUP_FILE ?= backup-prod-$$(date +%Y%m%d-%H%M%S).sql

# Parse database URL components
parse-db-url = $(shell echo $(1) | sed -E 's|mysql://([^:]+):([^@]+)@([^:]+):([^/]+)/(.+)|\1 \2 \3 \4 \5|')

fetch-prod-backup: ## Dump production database from MySQL URL
	@if [ -z "$(PROD_DB_URL)" ]; then \
		echo "Error: PROD_DB_URL is required. Usage: make fetch-prod-backup PROD_DB_URL=mysql://user:pass@host:3306/dbname"; \
		exit 1; \
	fi
	@echo "Parsing database URL..."
	@DB_PARTS=$$(echo "$(PROD_DB_URL)" | sed -E 's|mysql://([^:]+):([^@]+)@([^:]+):([^/]+)/(.+)|\1 \2 \3 \4 \5|'); \
	set -- $$DB_PARTS; \
	PROD_USER=$$1; \
	PROD_PASS=$$2; \
	PROD_HOST=$$3; \
	PROD_PORT=$$4; \
	PROD_NAME=$$5; \
	echo "Connecting to $$PROD_HOST:$$PROD_PORT as $$PROD_USER..."; \
	echo "Dumping database $$PROD_NAME..."; \
	mysqldump -h$$PROD_HOST -P$$PROD_PORT -u$$PROD_USER -p$$PROD_PASS $$PROD_NAME > var/$(BACKUP_FILE); \
	echo "✅ Backup saved to var/$(BACKUP_FILE)"

restore-backup: drop-database create-database ## Restore database from backup file
	@if [ ! -f "var/$(BACKUP_FILE)" ]; then \
		echo "Error: Backup file var/$(BACKUP_FILE) not found"; \
		echo "Available backups:"; \
		ls -la var/*.sql 2>/dev/null || echo "No backup files found"; \
		exit 1; \
	fi
	@echo "Restoring database from var/$(BACKUP_FILE)..."
	@cat var/$(BACKUP_FILE) | docker compose exec -T database mysql -u$(DATABASE_USER) -p$(DATABASE_PASSWORD) $(DATABASE_NAME)
	@echo "✅ Database restored successfully"

sync-from-prod: fetch-prod-backup restore-backup ## Sync local database with production (dump and restore)

backup-local: ## Create a backup of local database
	@echo "Creating local database backup..."
	@BACKUP_NAME=local-backup-$$(date +%Y%m%d-%H%M%S).sql; \
	docker compose exec -T database mysqldump -u$(DATABASE_USER) -p$(DATABASE_PASSWORD) $(DATABASE_NAME) > var/$$BACKUP_NAME; \
	echo "✅ Local backup created: var/$$BACKUP_NAME"

list-backups: ## List all available backups
	@echo "Available backups in var/ directory:"
	@ls -lah var/*.sql 2>/dev/null || echo "No backup files found"

## —— Tests ✅ —————————————————————————————————————————————————————————————————
test-load-fixtures: ## load database schema & fixtures
	$(DOCKER_TEST_EXEC) php bin/console doctrine:database:drop --if-exists --force
	$(DOCKER_TEST_EXEC) php bin/console doctrine:database:create --if-not-exists
	$(DOCKER_TEST_EXEC) php bin/console doctrine:migration:migrate -n --no-all-or-nothing
	$(DOCKER_TEST_EXEC) php bin/console doctrine:fixtures:load -n

test: phpunit.xml.dist ## Launch main functional and unit tests, stopped on failure
	$(DOCKER_TEST_EXEC) ./vendor/bin/phpunit --stop-on-failure

test-all: phpunit.xml.dist test-load-fixtures ## Launch main functional and unit tests
	$(DOCKER_TEST_EXEC) ./vendor/bin/phpunit $c

test-report: phpunit.xml.dist test-load-fixtures ## Launch main functional and unit tests with report
	$(DOCKER_TEST_EXEC) ./vendor/bin/phpunit --coverage-text --colors=never --log-junit report.xml $c

## —— Coding standards ✨ ——————————————————————————————————————————————————————
stan: ## Run PHPStan only
	@test -d var/cache/dev || $(CONSOLE) cache:clear
	$(DOCKER_EXEC) ./vendor/bin/phpstan analyse --no-progress --memory-limit 256M

cs-fix: ## Run php-cs-fixer and fix the code.
	$(DOCKER_EXEC) ./vendor/bin/php-cs-fixer fix src/ --allow-risky=yes

rector: ## Run rector process
	$(DOCKER_EXEC) ./vendor/bin/rector process src

cs-dry: ## Run php-cs-fixer and fix the code.
	$(DOCKER_EXEC) ./vendor/bin/php-cs-fixer fix src --dry-run --allow-risky=yes

analyze: stan cs-fix rector ## Run php-cs-fixer and fix the code.

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
config: compose.yaml ## build services to image
	$(DOCKER) config

build: compose.yaml ## build services to image
	$(DOCKER) build

up: compose.yaml ## up services for running containers
	$(DOCKER) up -d --wait
	$(DOCKER) ps

build-up: compose.yaml ## up services for running containers
	$(DOCKER) up -d --build --wait
	$(DOCKER) ps

start: compose.yaml ## start containers
	$(DOCKER) start

down: compose.yaml ## down containers
	$(DOCKER) down --remove-orphans

destroy: compose.yaml ## down containers & removes orphans
	$(DOCKER) down -v --remove-orphans --volumes

stop: compose.yaml ## stop containers
	$(DOCKER) stop

restart: compose.yaml down up ## stop & re-up containers

logs: compose.yaml ## logs of all containers
	$(DOCKER) logs --tail=100 -f $(c)

app-logs: compose.yaml ## logs of container app
	$(DOCKER) logs --tail=100 -f app

ps: compose.yaml ## ps containers
	$(DOCKER) ps

app: compose.yaml ## exec bash command for containers app
	$(DOCKER) exec app zsh

prune: ## clean all containers unused
	$(DOCKER) system prune -a

login:
	docker login $(REGISTRY) -u $(DOCKERHUB_USERNAME) -p $(DOCKERHUB_TOKEN)

## —— Deployments ————————————————————————————————————————————————————————————————

docker-build-push: login
	@docker build --target prod -t $(DOCKER_IMAGE):latest ./
	@docker push $(DOCKER_IMAGE):latest

## —— Git ———————————————————————————————————
git-clean-branches: ## Clean merged branches
	git remote prune origin
	(git branch --merged | egrep -v "(^\*|main|master|dev)" | xargs git branch -d) || true

git-rebase: ## Rebase current branch
	git pull --rebase origin main

m ?= \#$(shell git branch --show-current | sed -E 's/^([0-9]+)-([^-]+)-(.+)/\2: #\1 \3/' | sed "s/-/ /g")
auto-commit: ## Auto commit
	git add .
	@git commit -m "$(m)" || true

current_branch=$(shell git rev-parse --abbrev-ref HEAD)
git-push: ## Push current branch
	git push origin "$(current_branch)" --force-with-lease

push: analyze auto-commit git-rebase push ## Commit and push
