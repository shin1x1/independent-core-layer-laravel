<?php
declare(strict_types=1);

namespace App\Action\ProcedureStyleTransferMoney;

use Acme\Account\Domain\Exceptions\NotFoundException;
use Acme\Account\UseCase\ProcedureStyleTransferMoney\ProcedureStyleTransferMoneyCommandPort;
use Acme\Account\UseCase\ProcedureStyleTransferMoney\ProcedureStyleTransferMoneyQueryPort;
use App\Eloquents\EloquentAccount;
use App\Eloquents\EloquentTransaction;
use Illuminate\Contracts\Mail\Mailer;

final class ProcedureStyleTransferMoneyAdapter implements
    ProcedureStyleTransferMoneyQueryPort,
    ProcedureStyleTransferMoneyCommandPort
{
    /** @var EloquentAccount */
    private $account;
    /** @var EloquentTransaction */
    private $transaction;
    /** @var Mailer */
    private $mailer;

    /**
     * @param EloquentAccount $account
     * @param EloquentTransaction $transaction
     * @param Mailer $mail
     */
    public function __construct(EloquentAccount $account, EloquentTransaction $transaction, Mailer $mail)
    {
        $this->account = $account;
        $this->transaction = $transaction;
        $this->mailer = $mail;
    }

    /**
     * @param string $accountNumber
     * @return array
     * @throws NotFoundException
     */
    public function findAndLockAccount(string $accountNumber): array
    {
        $account = $this->account->newQuery()
            ->where('account_number', $accountNumber)
            ->lockForUpdate()
            ->first();

        if (is_null($account)) {
            throw $this->notFoundException($accountNumber);
        }

        return $account->toArray();
    }

    /**
     * @param string $accountNumber
     * @return array
     * @throws NotFoundException
     */
    public function findAccount(string $accountNumber): array
    {
        $account = $this->account->newQuery()
            ->where('account_number', $accountNumber)
            ->first();

        if (is_null($account)) {
            throw $this->notFoundException($accountNumber);
        }

        return $account->toArray();
    }

    /**
     * @param string $accountNumber
     * @param int $balance
     */
    public function storeBalance(string $accountNumber, int $balance): void
    {
        $this->account->newQuery()
            ->where('account_number', $accountNumber)
            ->update(['balance' => $balance]);
    }

    /**
     * @param array $transaction
     */
    public function addTransaction(array $transaction): void
    {
        $eloquent = $this->transaction->newInstance();
        $eloquent->account_number = $transaction['account_number'];
        $eloquent->transaction_type = $transaction['transaction_type'];
        $eloquent->transaction_time = $transaction['transaction_time'];
        $eloquent->amount = $transaction['amount'];
        $eloquent->comment = $transaction['comment'];
        $eloquent->save();
    }

    /**
     * @param array $account
     */
    public function notify(array $account): void
    {
        // not yet
    }

    /**
     * @param string $accountNumber
     * @return NotFoundException
     */
    private function notFoundException(string $accountNumber): NotFoundException
    {
        return new NotFoundException(sprintf('account_number %s not found', $accountNumber));
    }
}
