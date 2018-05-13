<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Specifications;

use Acme\Account\Domain\Models\Account;

final class DifferentAccountSpec
{
    /**
     * @param Account $accountA
     * @param Account $accountB
     * @return bool
     */
    public function isSatisfiedBy(Account $accountA, Account $accountB): bool
    {
        return !$accountA->accountNumber()->equals($accountB->accountNumber());
    }
}
