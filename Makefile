all: install test
.PHONY: all

install: start
	cp .env.example .env
	chmod -R a+w storage/*
	docker-compose run composer install --prefer-dist --no-interaction
	docker-compose exec php-fpm php artisan key:generate
	docker-compose exec php-fpm php artisan migrate
	docker-compose exec php-fpm php artisan db:seed
.PHONY: install

start:
	docker-compose up -d
.PHONY: start

test:
	docker-compose run php-fpm ./vendor/bin/phpunit
.PHONY: test

phpcs:
	docker-compose run php-fpm ./vendor/bin/phpcs --standard=/var/www/html/ruleset.xml
.PHONY: phpcs

phpcbf:
	docker-compose run php-fpm ./vendor/bin/phpcbf --standard=/var/www/html/ruleset.xml
.PHONY: phpcbf

clean:
	docker-compose down
.PHONY: clean

phpstan:
	docker-compose run phpstan analyze --level 7 packages
.PHONY: phpstan
