<?php
declare(strict_types=1);

namespace Acme\Test\Account\UseCase;

use Acme\Account\Domain\Exceptions\NotFoundException;
use Acme\Account\Domain\Models\Account;
use Acme\Account\Domain\Models\AccountNumber;
use Acme\Account\UseCase\GetAccount\GetAccount;
use Acme\Account\UseCase\GetAccount\GetAccountQueryPort;
use PHPUnit\Framework\TestCase;

final class GetAccountTest extends TestCase
{
    /**
     * @test
     * @throws NotFoundException
     */
    public function execute()
    {
        $sut = new GetAccount(
            new class implements GetAccountQueryPort
            {
                public function findAccount(AccountNumber $accountNumber): Account
                {
                    return Account::ofByArray([
                        'account_number' => $accountNumber,
                        'email'          => 'a@example.com',
                        'balance'        => 1000,
                    ]);
                }
            }
        );

        $accountNumber = 'A0001';
        $actual = $sut->execute(AccountNumber::of($accountNumber));

        $this->assertSame($accountNumber, $actual->accountNumber()->asString());
        $this->assertSame(1000, $actual->balance()->asInt());
    }

    /**
     * @test
     * @expectedException \Acme\Account\Domain\Exceptions\NotFoundException
     */
    public function error_account_not_found()
    {
        $sut = new GetAccount(
            new class implements GetAccountQueryPort
            {
                public function findAccount(AccountNumber $accountNumber): Account
                {
                    throw new NotFoundException();
                }
            }
        );

        $sut->execute(AccountNumber::of('Z9999'));
    }
}
