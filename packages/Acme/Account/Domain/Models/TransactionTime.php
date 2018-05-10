<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Models;

use Cake\Chronos\Chronos;

final class TransactionTime
{
    use ValueObjectOf;

    /** @var Chronos */
    private $date;

    /**
     * @param Chronos $date
     */
    private function __construct(Chronos $date)
    {
        $this->date = $date;
    }

    /**
     * @return TransactionTime
     */
    public static function now(): self
    {
        return new self(Chronos::now());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->date->toDateTimeString();
    }
}
