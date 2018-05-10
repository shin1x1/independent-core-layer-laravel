<?php
declare(strict_types=1);

namespace Acme\Account\Domain\Models;

final class Transaction
{
    /** @var AccountNumber */
    private $accountNumber;
    /** @var TransactionType */
    private $transactionType;
    /** @var TransactionTime */
    private $transactionTime;
    /** @var Money */
    private $amount;
    /** @var string */
    private $comment;

    /**
     * @param AccountNumber $accountNumber
     * @param TransactionType $transactionType
     * @param TransactionTime $transactionTime
     * @param Money $amount
     * @param string $comment
     */
    public function __construct(
        AccountNumber $accountNumber,
        TransactionType $transactionType,
        TransactionTime $transactionTime,
        Money $amount,
        string $comment
    ) {
        $this->accountNumber = $accountNumber;
        $this->transactionType = $transactionType;
        $this->transactionTime = $transactionTime;
        $this->amount = $amount;
        $this->comment = $comment;
    }

    /**
     * @return AccountNumber
     */
    public function accountNumber(): AccountNumber
    {
        return $this->accountNumber;
    }

    /**
     * @return TransactionType
     */
    public function transactionType(): TransactionType
    {
        return $this->transactionType;
    }

    /**
     * @return TransactionTime
     */
    public function transactionTime(): TransactionTime
    {
        return $this->transactionTime;
    }

    /**
     * @return Money
     */
    public function amount(): Money
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function comment(): string
    {
        return $this->comment;
    }

    /**
     * @param array $values
     * @return Transaction
     */
    public static function ofByArray(array $values): self
    {
        return new self(
            AccountNumber::of($values['account_number'] ?? ''),
            TransactionType::of($values['transaction_type'] ?? ''),
            TransactionTime::of($values['transaction_time'] ?? ''),
            Money::of($values['amount'] ?? 0),
            $values['comment'] ?? ''
        );
    }
}
