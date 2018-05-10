<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Models;

trait ValueObjectOf
{
    /**
     * @param $value
     * @return self
     */
    public static function of($value): self
    {
        if ($value instanceof static) {
            return $value;
        }

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return new self($value);
    }
}
