<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Models;

final class Account
{
    /** @var AccountNumber */
    private $accountNumber;
    /** @var Email */
    private $email;
    /** @var string */
    private $name;
    /** @var Balance */
    private $balance;

    public function __construct(AccountNumber $accountNumber, Email $email, string $name, Balance $balance)
    {
        $this->accountNumber = $accountNumber;
        $this->email = $email;
        $this->name = $name;
        $this->balance = $balance;
    }

    public function accountNumber(): AccountNumber
    {
        return $this->accountNumber;
    }

    /**
     * @return Email
     */
    public function email(): Email
    {
        return $this->email;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function balance(): Balance
    {
        return clone $this->balance;
    }

    /**
     * @param Money $money
     * @throws \Acme\Account\Domain\Exceptions\InvariantException
     */
    public function deposit(Money $money)
    {
        $this->balance = $this->balance->deposit($money);
    }

    /**
     * @param Money $money
     * @throws \Acme\Account\Domain\Exceptions\InvariantException
     */
    public function withdraw(Money $money)
    {
        $this->balance = $this->balance->withdraw($money);
    }

    public static function ofByArray(array $values): self
    {
        return new self(
            AccountNumber::of($values['account_number'] ?? ''),
            Email::of($values['email'] ?? ''),
            $values['name'] ?? '',
            Balance::of($values['balance'] ?? 0)
        );
    }
}
