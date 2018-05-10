<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Models;

trait ValueObjectString
{
    use ValueObjectOf;

    /** @var string */
    private $value;

    /**
     * @param string $value
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function asString(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->asString();
    }
}
