help:
		@echo "cs-check                    Validating coding standards rules"
		@echo "cs-fix                      Fix coding standards"
		@echo "phpmd                       Run PHP Mess Detector"
		@echo "phpcpd                      Run Copy/Paste Detector"
		@echo "phpstan                     Run PHPstan analyse"
		@echo "psalm                       Run Psalm analyse"
		@echo "analysis                    Run cs-check + phpmd + phpcpd + phpstan"
		@echo ""

		@echo "unit-test                   Run unit tests"
		@echo "integration-test            Run integration tests"
		@echo "test-all                    Run all tests"
		@echo "single-test                 Run single test"
		@echo ""

		@echo "before-push                 Run before pushing changes to origin"
		@echo "migrate                     Run migrations"
		@echo ""

		@echo "entity                      Create entity"
		@echo ""

cs-check:
	@echo "Validating coding standards rules"
	php vendor/bin/phpcs --standard=PSR12 -s --colors --extensions=php src

cs-fix:
	@echo "Fix coding standards"
	php vendor/bin/php-cs-fixer fix \
	src/ \
	tests/Support/Helper/ \
	tests/Support/UnitTester.php \
	tests/Unit \
	migrations/ \
	--verbose --config=.php-cs-fixer.dist.php

phpstan:
	@echo "Running PHPStan"
	php -d memory_limit=4G vendor/bin/phpstan -v analyse src tests migrations

psalm:
	@echo "Running Psalm"
	php -d memory_limit=4G vendor/bin/psalm --taint-analysis

phpmd:
	@echo "Running PHP Mess Detector"
	php -d memory_limit=4G vendor/bin/phpmd src text phpmd.xml

analysis:
	$(MAKE) cs-check
	$(MAKE) phpstan
	$(MAKE) psalm
	$(MAKE) phpmd

# Tests

unit-test:
	@echo "Running Unit tests"
	@docker exec -ti box-php-fpm php vendor/bin/codecept run Unit $(args)

e2e-test:
	@echo "Running Unit tests"
	@docker exec -ti box-php-fpm php vendor/bin/codecept run E2E $(args)

coverage-test:
	@echo "Run all tests and get html coverage"
	@docker exec -ti box-php-fpm bash -c 'XDEBUG_MODE=coverage php vendor/bin/codecept run Unit,E2E --coverage-html'

test-all:
	$(MAKE) reset-test-db
	$(MAKE) coverage-test

single-test:
	@docker exec -ti box-php-fpm bash ./dev/run_single_test.sh $(filter-out $@,$(MAKECMDGOALS))

before-push:
	$(MAKE) cs-fix
	$(MAKE) analysis
	$(MAKE) test-all

migrate:
	@echo "Running Migrate for current and test environment"
	@docker exec -ti box-php-fpm php bin/console doctrine:migration:migrate --no-interaction --allow-no-migration
	@docker exec -ti box-php-fpm bash -c 'APP_ENV=test php bin/console doctrine:migration:migrate --no-interaction --allow-no-migration'

migrate-down:
	php bin/console doctrine:migrations:execute --down DoctrineMigrations\\Version$(filter-out $@,$(MAKECMDGOALS))
	APP_ENV=test php bin/console doctrine:migrations:execute --down DoctrineMigrations\\Version$(filter-out $@,$(MAKECMDGOALS))

migration:
	php bin/console make:migration

tests-build:
	@echo "Build codeception generated settings and methods"
	php vendor/bin/codecept build

entity:
	@docker exec -ti box-php-fpm php bin/console make:entity \\App\\Adapter\\Secondary\\Entity\\$(filter-out $@,$(MAKECMDGOALS))

stop:
	@docker stop $(shell docker ps -aq)

start:
	@docker compose up -d

shell-php:
	@docker exec -ti box-php-fpm bash

shell-mysql:
	@docker exec -ti box-db bash

reset-test-db:
	@echo "Resetting test DB"
	@docker exec -ti box-php-fpm bash -c 'APP_ENV=test php bin/console doctrine:fixtures:load --no-interaction --group=test  --purger=test_purger'
	@echo "Reset cache"
	@docker exec -ti box-redis redis-cli FLUSHALL
