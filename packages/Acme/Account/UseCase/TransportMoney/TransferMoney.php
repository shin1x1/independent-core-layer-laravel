<?php
declare(strict_types=1);

namespace Acme\Account\UseCase\TransportMoney;

use Acme\Account\Domain\Exceptions\DomainRuleException;
use Acme\Account\Domain\Exceptions\NotFoundException;
use Acme\Account\Domain\Models\Account;
use Acme\Account\Domain\Models\AccountNumber;
use Acme\Account\Domain\Models\Balance;
use Acme\Account\Domain\Models\Money;
use Acme\Account\Domain\Models\Transaction;
use Acme\Account\Domain\Models\TransactionTime;
use Acme\Account\Domain\Models\TransactionType;
use Acme\Account\UseCase\Ports\TransactionPort;

final class TransferMoney
{
    /** @var TransferMoneyQueryPort */
    private $query;
    /** @var TransferMoneyCommandPort */
    private $command;
    /** @var TransactionPort */
    private $transaction;

    /**
     * @param TransferMoneyQueryPort $query
     * @param TransferMoneyCommandPort $command
     * @param TransactionPort $transaction
     */
    public function __construct(
        TransferMoneyQueryPort $query,
        TransferMoneyCommandPort $command,
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
            /** @var Account $source */
            /** @var Account $destination */
            [$source, $destination] = $this->query($sourceNumber, $destinationNumber);

            if ($source->accountNumber()->equals($destination->accountNumber())) {
                throw new DomainRuleException('source can not transfer to same account');
            }

            if ($source->balance()->lessThan($amount)) {
                $message = sprintf('source account does not have enough balance for transfer %s', $amount->asInt());
                throw new DomainRuleException($message);
            }

            $source->withdraw($amount);
            $destination->deposit($amount);

            $this->store($source, $destination, $amount, $now);

            $this->command->notify($source);

            return $this->query->findAccount($sourceNumber)->balance();
        });
    }

    /**
     * @param AccountNumber $sourceNumber
     * @param AccountNumber $destinationNumber
     * @return array
     */
    private function query(AccountNumber $sourceNumber, AccountNumber $destinationNumber): array
    {
        if ($sourceNumber->lessThan($destinationNumber)) {
            $source = $this->query->findAndLockAccount($sourceNumber);
            $destination = $this->query->findAndLockAccount($destinationNumber);
        } else {
            $destination = $this->query->findAndLockAccount($destinationNumber);
            $source = $this->query->findAndLockAccount($sourceNumber);
        }

        return [$source, $destination];
    }

    /**
     * @param Account $source
     * @param Account $destination
     * @param Money $amount
     * @param TransactionTime $now
     */
    private function store(Account $source, Account $destination, Money $amount, TransactionTime $now): void
    {
        $this->command->storeBalance($source->accountNumber(), $source->balance());
        $this->command->storeBalance($destination->accountNumber(), $destination->balance());

        $this->command->addTransaction(new Transaction(
            $source->accountNumber(),
            TransactionType::WITHDRAW(),
            $now,
            $amount,
            'transferred to ' . $destination->accountNumber()->asString()
        ));
        $this->command->addTransaction(new Transaction(
            $destination->accountNumber(),
            TransactionType::DEPOSIT(),
            $now,
            $amount,
            'transferred from ' . $source->accountNumber()->asString()
        ));
    }
}

interface TransferMoneyQueryPort
{
    /**
     * @param AccountNumber $accountNumber
     * @return Account
     */
    public function findAndLockAccount(AccountNumber $accountNumber): Account;

    /**
     * @param AccountNumber $accountNumber
     * @return Account
     * @throws NotFoundException
     */
    public function findAccount(AccountNumber $accountNumber): Account;
}

interface TransferMoneyCommandPort
{
    /**
     * @param AccountNumber $accountNumber
     * @param Balance $balance
     */
    public function storeBalance(AccountNumber $accountNumber, Balance $balance): void;

    /**
     * @param Transaction $transaction
     */
    public function addTransaction(Transaction $transaction): void;

    /**
     * @param Account $account
     */
    public function notify(Account $account): void;
}
