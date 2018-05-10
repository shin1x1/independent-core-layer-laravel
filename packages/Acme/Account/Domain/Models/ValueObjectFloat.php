<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Models;

trait ValueObjectFloat
{
    use ValueObjectOf;

    /** @var float */
    private $value;

    private function __construct(float $value)
    {
        $this->value = $value;
    }

    public function asFloat(): float
    {
        return $this->value;
    }
}
