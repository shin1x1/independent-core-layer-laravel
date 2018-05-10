<?php
declare(strict_types=1);

namespace App\Providers;

use Acme\Account\Domain\Models\AccountNumber;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;

final class ValidationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Factory $validator */
        $validator = $this->app->make('validator');

        $validator->extend('account_number', function (
            /** @noinspection PhpUnusedParameterInspection */
            $attribute,
            $value
        ) {
            return AccountNumber::validate($value);
        });
    }

    public function register(): void
    {
    }
}
