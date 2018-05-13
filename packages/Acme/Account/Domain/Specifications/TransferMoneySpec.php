<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Specifications;

use Acme\Account\Domain\Aggregates\TransferMoneyAggregate;
use Acme\Account\Domain\Models\Money;

final class TransferMoneySpec
{
    /**
     * @param TransferMoneyAggregate $aggregate
     * @param Money $amount
     * @return bool
     */
    public function isSatisfiedBy(TransferMoneyAggregate $aggregate, Money $amount): bool
    {
        return (new DifferentAccountSpec())->isSatisfiedBy($aggregate->source(), $aggregate->destination())
            && (new WithdrawSpec())->isSatisfiedBy($aggregate->source(), $amount);
    }
}
