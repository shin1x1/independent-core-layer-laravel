<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Models;

trait ValueObjectInt
{
    use ValueObjectOf;

    /** @var int */
    private $value;

    /**
     * @param int $value
     */
    private function __construct(int $value)
    {
        $this->value = $value;
    }

    public function asInt(): int
    {
        return $this->value;
    }
}
