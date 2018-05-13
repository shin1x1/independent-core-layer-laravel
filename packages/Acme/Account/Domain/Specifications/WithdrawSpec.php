<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Specifications;

use Acme\Account\Domain\Models\Account;
use Acme\Account\Domain\Models\Money;

final class WithdrawSpec
{
    /**
     * @param Account $account
     * @param Money $amount
     * @return bool
     */
    public function isSatisfiedBy(Account $account, Money $amount): bool
    {
        return !$account->balance()->lessThan($amount);
    }
}
