# Independent core layer pattern with Laravel

[![CircleCI](https://circleci.com/gh/shin1x1/independent-core-layer-laravel.svg?style=svg)](https://circleci.com/gh/shin1x1/independent-core-layer-laravel)

![independent-core-layer-pattern](https://user-images.githubusercontent.com/88324/39868526-f39c9354-5494-11e8-8012-1170e7004ff4.png)

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

## Usage

### GetAccount

```
$ curl -H 'Content-Type: application/json' http://localhost:8000/api/accounts/A00001 | jq .
{
  "account_number": "A00001",
  "balance": 3000
}
```

### TransferMoney

```
$ curl -X PUT -d '{"destination_number":"B00001","money":100}' -H 'Content-Type: application/json' http://localhost:8000/api/accounts/A00001/transfer | jq .
{
  "balance": 2900
}
```
