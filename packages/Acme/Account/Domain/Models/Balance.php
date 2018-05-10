<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Models;

use Acme\Account\Domain\Exceptions\InvariantException;

final class Balance
{
    use ValueObjectInt;

    /**
     * @param int $value
     * @throws InvariantException
     */
    private function __construct(int $value)
    {
        if ($value < 0) {
            throw new InvariantException('balance_should_not_be_less_than_zero');
        }
        $this->value = $value;
    }

    /**
     * @param Money $money
     * @return Balance
     * @throws InvariantException
     */
    public function deposit(Money $money): self
    {
        return new self($this->value + $money->asInt());
    }

    /**
     * @param Money $money
     * @return Balance
     * @throws InvariantException
     */
    public function withdraw(Money $money): self
    {
        return new self($this->value - $money->asInt());
    }

    /**
     * @param Money $money
     * @return bool
     */
    public function lessThan(Money $money): bool
    {
        return $this->value < $money->asInt();
    }
}
