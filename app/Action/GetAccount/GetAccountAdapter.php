<?php
declare(strict_types=1);

namespace App\Action\GetAccount;

use Acme\Account\Domain\Exceptions\NotFoundException;
use Acme\Account\Domain\Models\Account;
use Acme\Account\Domain\Models\AccountNumber;
use Acme\Account\UseCase\GetAccount\GetAccountQueryPort;
use App\Eloquents\EloquentAccount;

final class GetAccountAdapter implements GetAccountQueryPort
{
    /** @var EloquentAccount */
    private $account;

    /**
     * @param EloquentAccount $account
     */
    public function __construct(EloquentAccount $account)
    {
        $this->account = $account;
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
            throw new NotFoundException(sprintf('account_number %s not found', $accountNumber->__toString()));
        }

        return $account->toModel();
    }
}
