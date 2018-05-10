<?php
declare(strict_types=1);

namespace Acme\Test\Account\UseCase;

use Acme\Account\Domain\Exceptions\NotFoundException;
use Acme\Account\Domain\Models\Account;
use Acme\Account\Domain\Models\AccountNumber;
use Acme\Account\Domain\Models\Balance;
use Acme\Account\Domain\Models\Money;
use Acme\Account\Domain\Models\Transaction;
use Acme\Account\Domain\Models\TransactionTime;
use Acme\Account\UseCase\Ports\TransactionPort;
use Acme\Account\UseCase\TransportMoney\TransferMoney;
use Acme\Account\UseCase\TransportMoney\TransferMoneyCommandPort;
use Acme\Account\UseCase\TransportMoney\TransferMoneyQueryPort;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;

final class TransferMoneyTest extends TestCase
{
    /** @var ArrayRepository */
    public $storedBalance;
    /** @var ArrayRepository */
    public $storedTransaction;
    /** @var TransactionTime */
    private $now;
    /** @var Account */
    public $notifyAccount;

    protected function setUp()
    {
        parent::setUp();

        $this->storedBalance = new ArrayRepository();
        $this->storedTransaction = new ArrayRepository();

        $now = Chronos::now();
        Chronos::setTestNow($this->now);

        $this->now = TransactionTime::of($now);
    }

    /**
     * @test
     */
    public function execute()
    {
        $sut = new TransferMoney(
            $this->mockQuery(),
            $this->mockCommand(),
            $this->mockTransaction()
        );

        $actual = $sut->execute(
            AccountNumber::of('A0001'),
            AccountNumber::of('B0001'),
            Money::of(100),
            $this->now
        );

        $this->assertSame(900, $actual->asInt());

        $this->assertSame($actual->asInt(), $this->storedBalance['A0001']->asInt());
        $this->assertSame(2100, $this->storedBalance['B0001']->asInt());

        $this->assertEquals(Transaction::ofByArray([
            'account_number'   => 'A0001',
            'transaction_type' => 'WITHDRAW',
            'transaction_time' => $this->now,
            'amount'           => 100,
            'comment'          => 'transferred to B0001',
        ]), $this->storedTransaction[0]);

        $this->assertEquals(Transaction::ofByArray([
            'account_number'   => 'B0001',
            'transaction_type' => 'DEPOSIT',
            'transaction_time' => $this->now,
            'amount'           => 100,
            'comment'          => 'transferred from A0001',
        ]), $this->storedTransaction[1]);

        $this->assertEquals(AccountNumber::of('A0001'), $this->notifyAccount->accountNumber());
    }

    /**
     * @test
     * @expectedException \Acme\Account\Domain\Exceptions\NotFoundException
     */
    public function error_account_not_found()
    {
        $sut = new TransferMoney(
            new class implements TransferMoneyQueryPort
            {
                public function findAndLockAccount(AccountNumber $accountNumber): Account
                {
                    throw new NotFoundException();
                }

                public function findAccount(AccountNumber $accountNumber): Account
                {
                    return Account::ofByArray([
                        'account_number' => 'A0001',
                        'balance'        => 1000,
                    ]);
                }
            },
            $this->mockCommand(),
            $this->mockTransaction()
        );

        $sut->execute(
            AccountNumber::of('A9999'),
            AccountNumber::of('B0001'),
            Money::of(1001),
            $this->now
        );
    }

    /**
     * @test
     * @expectedException \Acme\Account\Domain\Exceptions\DomainRuleException
     */
    public function error_same_account_number()
    {
        $sut = new TransferMoney(
            $this->mockQuery(),
            $this->mockCommand(),
            $this->mockTransaction()
        );

        $sut->execute(
            AccountNumber::of('A0001'),
            AccountNumber::of('A0001'),
            Money::of(1001),
            $this->now
        );
    }

    /**
     * @test
     * @expectedException \Acme\Account\Domain\Exceptions\DomainRuleException
     */
    public function error_lack_balance()
    {
        $sut = new TransferMoney(
            $this->mockQuery(),
            $this->mockCommand(),
            $this->mockTransaction()
        );

        $sut->execute(
            AccountNumber::of('A0001'),
            AccountNumber::of('B0001'),
            Money::of(1001),
            $this->now
        );
    }

    private function mockQuery(): TransferMoneyQueryPort
    {
        $storedBalance = $this->storedBalance;

        return new class($storedBalance) implements TransferMoneyQueryPort
        {
            /** @var ArrayRepository */
            private $result;

            public function __construct(ArrayRepository $result)
            {
                $this->result = $result;
            }

            public function findAndLockAccount(AccountNumber $accountNumber): Account
            {
                if ($accountNumber->equals(AccountNumber::of('A0001'))) {
                    return Account::ofByArray([
                        'account_number' => 'A0001',
                        'email'          => 'a@example.com',
                        'balance'        => 1000,
                    ]);
                }
                if ($accountNumber->equals(AccountNumber::of('B0001'))) {
                    return Account::ofByArray([
                        'account_number' => 'B0001',
                        'email'          => 'b@example.com',
                        'balance'        => 2000,
                    ]);
                }

                throw new NotFoundException();
            }

            public function findAccount(AccountNumber $accountNumber): Account
            {
                return Account::ofByArray([
                    'account_number' => $accountNumber,
                    'email'          => 'a@example.com',
                    'balance'        => $this->result[$accountNumber->asString()],
                ]);
            }
        };
    }

    private function mockCommand(): TransferMoneyCommandPort
    {
        $self = $this;

        return new class($self) implements TransferMoneyCommandPort
        {
            /** @var TransferMoneyTest */
            private $test;

            public function __construct(TransferMoneyTest $test)
            {
                $this->test = $test;
            }

            public function storeBalance(AccountNumber $accountNumber, Balance $balance): void
            {
                $this->test->storedBalance[$accountNumber->asString()] = $balance;
            }

            public function addTransaction(Transaction $transaction): void
            {
                $this->test->storedTransaction[] = $transaction;
            }

            public function notify(Account $account): void
            {
                $this->test->notifyAccount = $account;
            }
        };
    }

    private function mockTransaction(): TransactionPort
    {
        return new class implements TransactionPort
        {
            public function transaction(callable $callee)
            {
                return call_user_func($callee);
            }
        };
    }
}
