<?php
declare(strict_types=1);

namespace Acme\Account\UseCase\Ports;

interface TransactionPort
{
    /**
     * @param callable $callee
     * @return mixed
     */
    public function transaction(callable $callee);
}
