#!make

# Setup ————————————————————————————————————————————————————————————————————————
SHELL			= bash
EXEC_PHP	  	= php
GIT			 	= git
SYMFONY_BIN		= ./symfony
SYMFONY		 	= $(SYMFONY_BIN) console
COMPOSER	  	= $(SYMFONY_BIN) composer
DOCKER-COMPOSE	= @docker-compose --env-file=docker/.env.docker
.DEFAULT_GOAL 	= help
#.PHONY		 = # Not needed for now

-include .env
-include .env.local

## —— 🐝 The Symfony Makefile 🐝 ———————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?## .*$$)|(^## )' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Composer 🧙‍♂️ ————————————————————————————————————————————————————————————
install: symfony composer.lock ## Install vendors according to the current composer.lock file
	$(COMPOSER) install -n -q

update: bin-install composer.json ## Update vendors according to the composer.json file
	$(COMPOSER) update -w

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
symfony: bin-install

sf: symfony ## List all Symfony commands
	$(SYMFONY)

cc: symfony ## Clear the cache. DID YOU CLEAR YOUR CACHE????
	$(SYMFONY) c:c

warmup: symfony ## Warmump the cache
	$(SYMFONY) cache:warmup

fix-perms: ## Fix permissions of all var files
	@chmod -R 777 var/*

assets: symfony purge ## Install the assets with symlinks in the public folder
	$(SYMFONY) assets:install public/ --symlink --relative

rm-var: ## Purge cache and logs
	@rm -rf var/cache/* var/logs/*

purge: rm-var cc warmup assets fix-perms ## Purge symfony project (assets, permissions, wramup)

routes: symfony ## get routes of project
	$(SYMFONY) debug:router

controller: symfony
	$(SYMFONY) make:controller

entity: symfony
	$(SYMFONY) make:entity

## —— Symfony doctrine 💻 ————————————————————————————————————————————————————————
doctrine-validate: symfony ## Check validate schema
	$(SYMFONY) doctrine:schema:validate

doctrine-migration: symfony ## Make migration structure of databases
	$(SYMFONY) make:migration

doctrine-migrate: symfony doctrine-database ## Migrate database structure
	$(SYMFONY) doctrine:migrations:migrate -n

doctrine-database: symfony ## Add database if not exists
	$(SYMFONY) doctrine:database:create --if-not-exists

doctrine-schema: symfony doctrine-database ## implement schema of database if not exists
	$(SYMFONY) doctrine:schema:create

load-fixtures: symfony ## load fixtures
	 $(SYMFONY) doctrine:fixtures:load -n

load-database: symfony ## Build the db, control the schema validity, load fixtures and check the migration status
	$(SYMFONY) doctrine:cache:clear-metadata
	$(SYMFONY) doctrine:database:create --if-not-exists
	$(SYMFONY) doctrine:schema:drop --force
	$(SYMFONY) doctrine:schema:create
	$(SYMFONY) doctrine:schema:validate
	$(SYMFONY) doctrine:fixtures:load -n
	$(SYMFONY) doctrine:schema:validate

## —— Symfony binary 💻 ————————————————————————————————————————————————————————
bin-install: ## Download and install the binary in the project (file is ignored)
	@test -f ./symfony || ( curl -sS https://get.symfony.com/cli/installer | bash &&	mv ~/.symfony/bin/symfony . )

cert-install: symfony ## Install the local HTTPS certificates
	$(SYMFONY_BIN) server:ca:install

serve: symfony ## Serve the application with HTTPS support
	$(SYMFONY_BIN) serve --port=$(PROJECT_APP_PORT)

unserve: symfony ## Stop the web server
	$(SYMFONY_BIN) server:stop

## —— Tests ✅ —————————————————————————————————————————————————————————————————
test: phpunit.xml* ## Launch main functionnal and unit tests
	./bin/phpunit --testsuite=main --stop-on-failure

test-external: phpunit.xml* ## Launch tests implying external resources (api, services...)
	./bin/phpunit --testsuite=external --stop-on-failure

test-all: phpunit.xml* ## Launch all tests
	./bin/phpunit --stop-on-failure

## —— Coding standards ✨ ——————————————————————————————————————————————————————
cs: codesniffer stan ## Launch check style and static analysis

codesniffer: ## Run php_codesniffer only
	./vendor/squizlabs/php_codesniffer/bin/phpcs --standard=phpcs.xml -n -p src/ vendor/fardus

stan: ## Run PHPStan only
	./vendor/bin/phpstan analyse --memory-limit 1G -c phpstan.neon

psalm: ## Run psalm only
	./vendor/bin/psalm --show-info=false

init-psalm: ## Init a new psalm config file for a given level, it must be decremented to have stricter rules
	[ -f ./psalm.xml ] && rm ./psalm.xml || echo 'no ./psalm.xml'
	./vendor/bin/psalm --init src/ 3

cs-fix: ## Run php-cs-fixer and fix the code.
	./vendor/bin/php-cs-fixer fix src/

sonar: sonar-project.properties ## sonar scan src directory
	sonar-scanner -Dsonar.projectVersion=$(VERSION)

## —— Stats 📜 —————————————————————————————————————————————————————————————————
stats: ## Commits by hour for the main author of this project
	$(GIT) log --date=iso | perl -nalE 'if (/^Date:\s+[\d-]{10}\s(\d{2})/) { say $$1+0 }' | sort | uniq -c|perl -MList::Util=max -nalE '$$h{$$F[1]} = $$F[0]; }{ $$m = max values %h; foreach (0..23) { $$h{$$_} = 0 if not exists $$h{$$_} } foreach (sort {$$a <=> $$b } keys %h) { say sprintf "%02d - %4d %s", $$_, $$h{$$_}, "*"x ($$h{$$_} / $$m * 50); }'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
config: docker-compose.yaml ## build services to image
	$(DOCKER-COMPOSE) config $(c)

build: docker-compose.yaml ## build services to image
	$(DOCKER-COMPOSE) build $(c)

up: docker-compose.yaml ## up services for running containers
	$(DOCKER-COMPOSE) up -d $(c)
	$(DOCKER-COMPOSE) ps

build-up: docker-compose.yaml ## up services for running containers
	$(DOCKER-COMPOSE) up -d --build $(c)
	$(DOCKER-COMPOSE) ps

start: docker-compose.yaml ## start containers
	$(DOCKER-COMPOSE) start $(c)

down: docker-compose.yaml ## down containers
	$(DOCKER-COMPOSE) down $(c)

destroy: docker-compose.yaml ## down containers & removes orphans
	$(DOCKER-COMPOSE) down -v --remove-orphans $(c)

stop: docker-compose.yaml ## stop containers
	$(DOCKER-COMPOSE) stop $(c)

restart: docker-compose.yaml stop up ## stop & re-up containers

logs: docker-compose.yaml ## logs of all containers
	$(DOCKER-COMPOSE) logs --tail=100 -f $(c)

app-logs: docker-compose.yaml ## logs of container app
	$(DOCKER-COMPOSE) logs --tail=100 -f app

ps: docker-compose.yaml ## ps containers
	$(DOCKER-COMPOSE) ps

app: docker-compose.yaml ## exec bash command for containers app
	$(DOCKER-COMPOSE) exec app zsh $(c)

prune: ## clean all containers unused
	$(DOCKER-COMPOSE) system prune -a
