<?php
declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Psr\Log\LoggerInterface;

final class DatabaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        parent::boot();

        /** @var \Illuminate\Database\Connection $connection */
        $connection = $this->app->make(Connection::class);
        $connection->listen(function (QueryExecuted $event) {
            /** @var LoggerInterface $logger */
            $logger = $this->app->make(LoggerInterface::class);

            $message = sprintf(
                '[%s] %s %s %dms',
                $event->connectionName,
                $event->sql,
                json_encode($event->bindings),
                $event->time
            );
            $logger->debug($message);
        });
    }
}
