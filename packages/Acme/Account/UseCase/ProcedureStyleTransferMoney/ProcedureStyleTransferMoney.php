<?php
declare(strict_types=1);

namespace Acme\Account\UseCase\ProcedureStyleTransferMoney;

use Acme\Account\Domain\Exceptions\DomainRuleException;
use Acme\Account\Domain\Exceptions\NotFoundException;
use Acme\Account\UseCase\Ports\TransactionPort;
use Cake\Chronos\Chronos;

final class ProcedureStyleTransferMoney
{
    const TRANSACTION_TYPE_WITHDRAW = 'WITHDRAW';
    const TRANSACTION_TYPE_DEPOSIT = 'DEPOSIT';

    /** @var ProcedureStyleTransferMoneyQueryPort */
    private $query;
    /** @var ProcedureStyleTransferMoneyCommandPort */
    private $command;
    /** @var TransactionPort */
    private $transaction;

    /**
     * @param ProcedureStyleTransferMoneyQueryPort $query
     * @param ProcedureStyleTransferMoneyCommandPort $command
     * @param TransactionPort $transaction
     */
    public function __construct(
        ProcedureStyleTransferMoneyQueryPort $query,
        ProcedureStyleTransferMoneyCommandPort $command,
        TransactionPort $transaction
    ) {
        $this->query = $query;
        $this->command = $command;
        $this->transaction = $transaction;
    }

    /**
     * @param string $sourceNumber
     * @param string $destinationNumber
     * @param int $amount
     * @param Chronos $now
     * @return int
     */
    public function execute(
        string $sourceNumber,
        string $destinationNumber,
        int $amount,
        Chronos $now
    ): int {
        return $this->transaction->transaction(function () use ($sourceNumber, $destinationNumber, $amount, $now) {
            [$source, $destination] = $this->query($sourceNumber, $destinationNumber);

            if ($sourceNumber === $destinationNumber) {
                throw new DomainRuleException('source can not transfer to same account');
            }

            if ($source['balance'] < $amount) {
                $message = sprintf('source account does not have enough balance for transfer %s', $amount);
                throw new DomainRuleException($message);
            }

            $source['balance'] -= $amount;
            $destination['balance'] += $amount;

            $this->store($source, $destination, $amount, $now);

            $this->command->notify($source);

            return $this->query->findAccount($sourceNumber)['balance'];
        });
    }

    /**
     * @param string $sourceNumber
     * @param string $destinationNumber
     * @return array
     */
    private function query(string $sourceNumber, string $destinationNumber): array
    {
        if ($sourceNumber < $destinationNumber) {
            $source = $this->query->findAndLockAccount($sourceNumber);
            $destination = $this->query->findAndLockAccount($destinationNumber);
        } else {
            $destination = $this->query->findAndLockAccount($destinationNumber);
            $source = $this->query->findAndLockAccount($sourceNumber);
        }

        return [$source, $destination];
    }

    /**
     * @param array $source
     * @param array $destination
     * @param int $amount
     * @param Chronos $now
     */
    private function store(array $source, array $destination, int $amount, Chronos $now): void
    {
        $this->command->storeBalance($source['account_number'], $source['balance']);
        $this->command->storeBalance($destination['account_number'], $destination['balance']);

        $this->command->addTransaction([
            'account_number' => $source['account_number'],
            'transaction_type' => self::TRANSACTION_TYPE_WITHDRAW,
            'transaction_time' => $now,
            'amount' => $amount,
            'comment' => 'transferred to ' . $destination['account_number'],
        ]);
        $this->command->addTransaction([
            'account_number' => $destination['account_number'],
            'transaction_type' => self::TRANSACTION_TYPE_DEPOSIT,
            'transaction_time' => $now,
            'amount' => $amount,
            'comment' => 'transferred from ' . $source['account_number'],
        ]);
    }
}

interface ProcedureStyleTransferMoneyQueryPort
{
    /**
     * @param string $accountNumber
     * @return array
     */
    public function findAndLockAccount(string $accountNumber): array;

    /**
     * @param string $accountNumber
     * @return array
     * @throws NotFoundException
     */
    public function findAccount(string $accountNumber): array;
}

interface ProcedureStyleTransferMoneyCommandPort
{
    /**
     * @param string $accountNumber
     * @param int $balance
     */
    public function storeBalance(string $accountNumber, int $balance): void;

    /**
     * @param array $transaction
     */
    public function addTransaction(array $transaction): void;

    /**
     * @param array $account
     */
    public function notify(array $account): void;
}
