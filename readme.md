# Independent Core Application pattern with Laravel



## Requirements

* Docker ( I tested on Docker for Mac. )
* docker-compose
* make

## Installation

```bash
$ git this_repo
$ cd this_repo
$ make
```

If you do not have make command.

```bash
$ docker-compose up -d
$ cp .env.example .env
$ chmod -R a+w storage/*
$ docker-compose run composer install --prefer-dist --no-interaction
$ docker-compose exec php-fpm php artisan key:generate
$ docker-compose exec php-fpm php artisan migrate
$ docker-compose exec php-fpm php artisan db:seed
```
