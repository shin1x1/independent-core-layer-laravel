<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Models;

/**
 * @method static TransactionType DEPOSIT()
 * @method static TransactionType WITHDRAW()
 */
final class TransactionType
{
    use ValueObjectString;

    private static $items = [
        'DEPOSIT',
        'WITHDRAW',
    ];

    /**
     * @param string $name
     * @param array $args
     * @return TransactionType
     */
    public static function __callStatic($name, array $args): self
    {
        if (!in_array($name, self::$items)) {
            $message = sprintf('%s is not value of %s ', $name, self::class);
            throw new \InvalidArgumentException($message);
        }

        return new self($name);
    }

    /**
     * @param mixed $value
     * @return TransactionType
     */
    public static function of($value): self
    {
        return self::$value();
    }
}
