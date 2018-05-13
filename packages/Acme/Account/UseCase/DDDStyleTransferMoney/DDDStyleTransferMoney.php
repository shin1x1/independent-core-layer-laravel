<?php
declare(strict_types=1);

namespace Acme\Account\UseCase\DDDStyleTransferMoney;

use Acme\Account\Domain\Aggregates\TransferMoneyAggregate;
use Acme\Account\Domain\Exceptions\DomainRuleException;
use Acme\Account\Domain\Exceptions\NotFoundException;
use Acme\Account\Domain\Models\Account;
use Acme\Account\Domain\Models\AccountNumber;
use Acme\Account\Domain\Models\Balance;
use Acme\Account\Domain\Models\Money;
use Acme\Account\Domain\Models\TransactionTime;
use Acme\Account\Domain\Specifications\TransferMoneySpec;
use Acme\Account\UseCase\Ports\TransactionPort;

final class DDDStyleTransferMoney
{
    /** @var DDDStyleTransferMoneyQuery */
    private $query;
    /** @var DDDStyleTransferMoneyCommandPort */
    private $command;
    /** @var TransactionPort */
    private $transaction;

    /**
     * @param DDDStyleTransferMoneyQuery $query
     * @param DDDStyleTransferMoneyCommandPort $command
     * @param TransactionPort $transaction
     */
    public function __construct(
        DDDStyleTransferMoneyQuery $query,
        DDDStyleTransferMoneyCommandPort $command,
        TransactionPort $transaction
    ) {
        $this->query = $query;
        $this->command = $command;
        $this->transaction = $transaction;
    }

    /**
     * @param AccountNumber $sourceNumber
     * @param AccountNumber $destinationNumber
     * @param Money $amount
     * @param TransactionTime $now
     * @return Balance
     */
    public function execute(
        AccountNumber $sourceNumber,
        AccountNumber $destinationNumber,
        Money $amount,
        TransactionTime $now
    ): Balance {
        return $this->transaction->transaction(function () use ($sourceNumber, $destinationNumber, $amount, $now) {
            $aggregate = $this->query->find($sourceNumber, $destinationNumber);

            if (!(new TransferMoneySpec())->isSatisfiedBy($aggregate, $amount)) {
                throw new DomainRuleException('TransferMoneySpec is not satisfied.');
            }

            $aggregate->transfer($amount, $now);

            $this->command->store($aggregate);
            $this->command->notify($aggregate->source());

            return $this->query->findAccount($sourceNumber)->balance();
        });
    }
}

interface DDDStyleTransferMoneyQuery
{
    /**
     * @param AccountNumber $sourceNumber
     * @param AccountNumber $destinationNumber
     * @return TransferMoneyAggregate
     */
    public function find(AccountNumber $sourceNumber, AccountNumber $destinationNumber): TransferMoneyAggregate;

    /**
     * @param AccountNumber $accountNumber
     * @return Account
     * @throws NotFoundException
     */
    public function findAccount(AccountNumber $accountNumber): Account;
}

interface DDDStyleTransferMoneyCommandPort
{
    /**
     * @param TransferMoneyAggregate $aggregate
     */
    public function store(TransferMoneyAggregate $aggregate): void;

    /**
     * @param Account $account
     */
    public function notify(Account $account): void;
}
