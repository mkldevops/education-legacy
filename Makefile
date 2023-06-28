#!make
include .env
ifneq ("$(wildcard .env.local)","")
	include .env.local
endif

# Setup ————————————————————————————————————————————————————————————————————————
DOCKER				:= @docker-compose --env-file=docker/.env.docker
DOCKER_EXEC			:=
DOCKER_TEST_EXEC	:= APP_ENV=test
CONSOLE 			:= symfony console
COMPOSER 			:= symfony composer
.DEFAULT_GOAL 		:= help
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
	$(COMPOSER) update -w

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————

cc: ## Clear the cache. DID YOU CLEAR YOUR CACHE????
	$(CONSOLE) c:c

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

## —— Tests ✅ —————————————————————————————————————————————————————————————————
test-load-fixtures: ## load database schema & fixtures
	$(DOCKER_TEST_EXEC) php bin/console doctrine:database:drop --if-exists --force
	$(DOCKER_TEST_EXEC) php bin/console doctrine:database:create --if-not-exists
	$(DOCKER_TEST_EXEC) php bin/console doctrine:migration:migrate -n --all-or-nothing
	$(DOCKER_TEST_EXEC) php bin/console doctrine:fixtures:load -n

test: phpunit.xml.dist ## Launch main functional and unit tests, stopped on failure
	$(DOCKER_TEST_EXEC) ./vendor/bin/simple-phpunit --stop-on-failure

test-all: phpunit.xml.dist test-load-fixtures ## Launch main functional and unit tests
	$(DOCKER_TEST_EXEC) ./vendor/bin/simple-phpunit $c

test-report: phpunit.xml.dist test-load-fixtures ## Launch main functional and unit tests with report
	$(DOCKER_TEST_EXEC) ./vendor/bin/simple-phpunit --coverage-text --colors=never --log-junit report.xml $c

## —— Coding standards ✨ ——————————————————————————————————————————————————————
stan: ## Run PHPStan only
	@test -d var/cache/dev || $(CONSOLE) cache:clear
	$(DOCKER_EXEC) ./vendor/bin/phpstan analyse --no-progress --memory-limit 256M

cs-fix: ## Run php-cs-fixer and fix the code.
	$(DOCKER_EXEC) ./vendor/bin/php-cs-fixer fix src/ --allow-risky=yes

rector: ## Run php-cs-fixer and fix the code.
	$(DOCKER_EXEC) ./vendor/bin/rector process src

cs-dry: ## Run php-cs-fixer and fix the code.
	$(DOCKER_EXEC) fix src --dry-run --allow-risky=yes

analyze: stan cs-fix rector ## Run php-cs-fixer and fix the code.

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
config: docker-compose.yaml ## build services to image
	$(DOCKER) config

build: docker-compose.yaml ## build services to image
	$(DOCKER) build

up: docker-compose.yaml ## up services for running containers
	$(DOCKER) up -d
	$(DOCKER) ps

build-up: docker-compose.yaml ## up services for running containers
	$(DOCKER) up -d --build
	$(DOCKER) ps

start: docker-compose.yaml ## start containers
	$(DOCKER) start

down: docker-compose.yaml ## down containers
	$(DOCKER) down

destroy: docker-compose.yaml ## down containers & removes orphans
	$(DOCKER) down -v --remove-orphans

stop: docker-compose.yaml ## stop containers
	$(DOCKER) stop

restart: docker-compose.yaml stop up ## stop & re-up containers

logs: docker-compose.yaml ## logs of all containers
	$(DOCKER) logs --tail=100 -f

app-logs: docker-compose.yaml ## logs of container app
	$(DOCKER) logs --tail=100 -f app

ps: docker-compose.yaml ## ps containers
	$(DOCKER) ps

app: docker-compose.yaml ## exec bash command for containers app
	$(DOCKER) exec app zsh

nice: docker-compose.yaml ## exec bash command for containers app
	$(DOCKER) exec nice zsh $(c)

prune: ## clean all containers unused
	$(DOCKER) system prune -a

login:
	docker login $(REGISTRY) -u $(USER) -p $(TOKEN)

## —— Deployments ————————————————————————————————————————————————————————————————

docker-build-base: login
	@docker build --target php-base -t $(REGISTRY_IMAGE)/php:8.2-base ./
	@docker push $(REGISTRY_IMAGE)/php:8.2-base

## —— Git ———————————————————————————————————
git-clean-branches: ## Clean merged branches
	git remote prune origin
	(git branch --merged | egrep -v "(^\*|main|master|dev)" | xargs git branch -d) || true

git-rebase: ## Rebase current branch
	git pull --rebase origin main

message ?= \#$(shell git branch --show-current | sed -E 's/^([0-9]+)-([^-]+)-(.+)/\2: #\1 \3/' | sed "s/-/ /g")
auto-commit: ## Auto commit
	git add .
	@git commit -m "${message}" || true

current_branch=$(shell git rev-parse --abbrev-ref HEAD)
push: ## Push current branch
	git push origin "$(current_branch)" --force-with-lease

commit: git-clean-branches analyze auto-commit git-rebase push ## Commit and push