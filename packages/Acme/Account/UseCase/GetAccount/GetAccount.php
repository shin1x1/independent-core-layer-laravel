<?php
declare(strict_types=1);

namespace Acme\Account\UseCase\GetAccount;

use Acme\Account\Domain\Exceptions\NotFoundException;
use Acme\Account\Domain\Models\Account;
use Acme\Account\Domain\Models\AccountNumber;

final class GetAccount
{
    /** @var GetAccountQueryPort */
    private $query;

    /**
     * @param GetAccountQueryPort $query
     */
    public function __construct(GetAccountQueryPort $query)
    {
        $this->query = $query;
    }

    /**
     * @param AccountNumber $accountNumber
     * @return Account
     * @throws NotFoundException
     */
    public function execute(AccountNumber $accountNumber): Account
    {
        return $this->query->findAccount($accountNumber);
    }
}

interface GetAccountQueryPort
{
    /**
     * @param AccountNumber $accountNumber
     * @return Account
     * @throws NotFoundException
     */
    public function findAccount(AccountNumber $accountNumber): Account;
}
