<?php
declare(strict_types=1);

namespace App\Action\DDDStyleTransferMoney;

use Acme\Account\Domain\Aggregates\TransferMoneyAggregate;
use Acme\Account\Domain\Exceptions\NotFoundException;
use Acme\Account\Domain\Models\Account;
use Acme\Account\Domain\Models\AccountNumber;
use Acme\Account\Domain\Models\Transaction;
use Acme\Account\UseCase\DDDStyleTransferMoney\DDDStyleTransferMoneyCommandPort;
use Acme\Account\UseCase\DDDStyleTransferMoney\DDDStyleTransferMoneyQuery;
use App\Eloquents\EloquentAccount;
use App\Eloquents\EloquentTransaction;
use App\Mail\TransferMoneyMail;
use Illuminate\Contracts\Mail\Mailer;

final class DDDStyleTransferMoneyAdapter implements
    DDDStyleTransferMoneyQuery,
    DDDStyleTransferMoneyCommandPort
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
     * @param AccountNumber $sourceNumber
     * @param AccountNumber $destinationNumber
     * @return TransferMoneyAggregate
     * @throws NotFoundException
     */
    public function find(AccountNumber $sourceNumber, AccountNumber $destinationNumber): TransferMoneyAggregate
    {
        if ($sourceNumber->lessThan($destinationNumber)) {
            $source = $this->findAndLockAccount($sourceNumber);
            $destination = $this->findAndLockAccount($destinationNumber);
        } else {
            $destination = $this->findAndLockAccount($destinationNumber);
            $source = $this->findAndLockAccount($sourceNumber);
        }

        return new TransferMoneyAggregate($source, $destination);
    }

    /**
     * @param TransferMoneyAggregate $aggregate
     */
    public function store(TransferMoneyAggregate $aggregate): void
    {
        $source = $aggregate->source();
        $destination = $aggregate->destination();

        $this->account->updateBalance($source->accountNumber(), $source->balance());
        $this->account->updateBalance($destination->accountNumber(), $destination->balance());

        $this->addTransaction($aggregate->sourceTransaction());
        $this->addTransaction($aggregate->destinationTransaction());
    }

    /**
     * @param AccountNumber $accountNumber
     * @return Account
     * @throws NotFoundException
     */
    public function findAccount(AccountNumber $accountNumber): Account
    {
        /** @var EloquentAccount $account */
        $account = $this->account->findByAccountNumber($accountNumber);
        if (is_null($account)) {
            throw $this->notFoundException($accountNumber);
        }

        return $account->toModel();
    }

    /**
     * @param Account $account
     */
    public function notify(Account $account): void
    {
        $this->mailer->to($account->email()->asString())->send(new TransferMoneyMail($account));
    }

    /**
     * @param AccountNumber $accountNumber
     * @return Account
     * @throws NotFoundException
     */
    private function findAndLockAccount(AccountNumber $accountNumber): Account
    {
        /** @var EloquentAccount $account */
        $account = $this->account->findByAccountNumberWithLockForUpdate($accountNumber);
        if (is_null($account)) {
            throw $this->notFoundException($accountNumber);
        }

        return $account->toModel();
    }

    /**
     * @param AccountNumber $accountNumber
     * @return NotFoundException
     */
    private function notFoundException(AccountNumber $accountNumber): NotFoundException
    {
        return new NotFoundException(sprintf('account_number %s not found', $accountNumber->__toString()));
    }

    /**
     * @param Transaction $transaction
     */
    private function addTransaction(Transaction $transaction): void
    {
        $this->transaction->store($transaction);
    }
}
