<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Models;

final class AccountNumber
{
    use ValueObjectString;

    /**
     * @param string $value
     */
    private function __construct(string $value)
    {
        if (!self::validate($value)) {
            throw new \InvalidArgumentException($value . ' is invalid AccountNumber.');
        }
        $this->value = $value;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function validate(string $value)
    {
        return preg_match('/\A[A-Z][0-9]{4,10}\z/', $value) > 0;
    }

    /**
     * @param self $accountNumber
     * @return bool
     */
    public function lessThan(AccountNumber $accountNumber): bool
    {
        return $this->value < $accountNumber->value;
    }

    /**
     * @param self $accountNumber
     * @return bool
     */
    public function equals(AccountNumber $accountNumber): bool
    {
        return $this->value === $accountNumber->value;
    }
}
