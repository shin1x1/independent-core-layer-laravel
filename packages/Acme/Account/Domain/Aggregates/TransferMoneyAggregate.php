<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Aggregates;

use Acme\Account\Domain\Models\Account;
use Acme\Account\Domain\Models\Money;
use Acme\Account\Domain\Models\Transaction;
use Acme\Account\Domain\Models\TransactionTime;
use Acme\Account\Domain\Models\TransactionType;

final class TransferMoneyAggregate
{
    /** @var Account */
    private $source;
    /** @var Account */
    private $destination;

    /** @var Transaction */
    private $sourceTransaction;
    /** @var Transaction */
    private $destinationTransaction;

    /**
     * @param Account $source
     * @param Account $destination
     */
    public function __construct(Account $source, Account $destination)
    {
        $this->source = $source;
        $this->destination = $destination;
    }

    /**
     * @param Money $amount
     * @param TransactionTime $now
     * @throws \Acme\Account\Domain\Exceptions\InvariantException
     */
    public function transfer(Money $amount, TransactionTime $now)
    {
        $this->source->withdraw($amount);
        $this->destination->deposit($amount);

        $this->sourceTransaction = new Transaction(
            $this->source->accountNumber(),
            TransactionType::WITHDRAW(),
            $now,
            $amount,
            'transferred to ' . $this->destination->accountNumber()->asString()
        );

        $this->destinationTransaction = new Transaction(
            $this->destination->accountNumber(),
            TransactionType::DEPOSIT(),
            $now,
            $amount,
            'transferred from ' . $this->source->accountNumber()->asString()
        );
    }

    /**
     * @return Account
     */
    public function source(): Account
    {
        return $this->source;
    }

    /**
     * @return Account
     */
    public function destination(): Account
    {
        return $this->destination;
    }

    /**
     * @return Transaction
     */
    public function sourceTransaction(): Transaction
    {
        return $this->sourceTransaction;
    }

    /**
     * @return Transaction
     */
    public function destinationTransaction(): Transaction
    {
        return $this->destinationTransaction;
    }
}
