<?php
declare(strict_types=1);

namespace App\Providers;

use Acme\Account\UseCase\DDDStyleTransferMoney\DDDStyleTransferMoney;
use Acme\Account\UseCase\GetAccount\GetAccount;
use Acme\Account\UseCase\Ports\TransactionPort;
use Acme\Account\UseCase\TransportMoney\TransferMoney;
use App\Action\DDDStyleTransferMoney\DDDStyleTransferMoneyAdapter;
use App\Action\GetAccount\GetAccountAdapter;
use App\Action\TransferMoney\TransferMoneyAdapter;
use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;

final class CoreApplicationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }

    public function register(): void
    {
        $this->app->bind(TransactionPort::class, function () {
            return new class implements TransactionPort
            {
                /**
                 * @param callable $callee
                 * @return mixed
                 * @throws \Throwable
                 */
                public function transaction(callable $callee)
                {
                    /** @var Connection $connection */
                    $connection = app(Connection::class);

                    return $connection->transaction($callee);
                }
            };
        });

        $this->app->bind(GetAccount::class, function () {
            $adapter = app(GetAccountAdapter::class);

            return new GetAccount($adapter);
        });

        $this->app->bind(TransferMoney::class, function () {
            $adapter = app(TransferMoneyAdapter::class);

            return new TransferMoney(
                $adapter,
                $adapter,
                app(TransactionPort::class)
            );
        });

        $this->app->bind(DDDStyleTransferMoney::class, function () {
            $adapter = app(DDDStyleTransferMoneyAdapter::class);

            return new DDDStyleTransferMoney(
                $adapter,
                $adapter,
                app(TransactionPort::class)
            );
        });
    }
}
