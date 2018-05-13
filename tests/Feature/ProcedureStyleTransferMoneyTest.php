<?php
declare(strict_types=1);

namespace Tests\Feature;

use Acme\Account\UseCase\ProcedureStyleTransferMoney\ProcedureStyleTransferMoney;
use Illuminate\Database\Connection;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\SetupDatabase;
use Tests\TestCase;

class ProcedureStyleTransferMoneyTest extends TestCase
{
    use SetupDatabase, DatabaseTransactions;

    /**
     * @test
     */
    public function transfer_money()
    {
        Mail::fake();

        $response = $this->put('/api/accounts/A0001/procedure-transfer', [
            'destination_number' => 'B0001',
            'money' => 100,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'balance' => 900,
        ]);

        $this->assertDatabaseHas('accounts', [
            'account_number' => 'A0001',
            'balance' => 900,
        ]);
        $this->assertDatabaseHas('accounts', [
            'account_number' => 'B0001',
            'balance' => 600,
        ]);

        $this->assertDatabaseHas('transactions', [
            'account_number' => 'A0001',
            'transaction_type' => ProcedureStyleTransferMoney::TRANSACTION_TYPE_WITHDRAW,
            'amount' => 100,
            'comment' => 'transferred to B0001',
        ]);
        $this->assertDatabaseHas('transactions', [
            'account_number' => 'B0001',
            'transaction_type' => ProcedureStyleTransferMoney::TRANSACTION_TYPE_DEPOSIT,
            'amount' => 100,
            'comment' => 'transferred from A0001',
        ]);

        /** @noinspection PhpUndefinedMethodInspection */
//        Mail::assertSent(TransferMoneyMail::class, function (TransferMoneyMail $mail) {
//            return $mail->hasTo('a@example.com');
//        });

        /** @noinspection PhpUndefinedMethodInspection */
//        Mail::assertSent(TransferMoneyMail::class, function (TransferMoneyMail $mail) {
//            return $mail->account->balance()->asInt() === 900;
//        });
    }

    /**
     * @test
     */
    public function transfer_no_exists_account()
    {
        $response = $this->put('/api/accounts/A0001/procedure-transfer', [
            'destination_number' => 'Z9999',
            'money' => 100,
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'account_number Z9999 not found']);
    }

    /**
     * @test
     */
    public function transfer_lack_source_balance()
    {
        $response = $this->put('/api/accounts/A0001/procedure-transfer', [
            'destination_number' => 'B0001',
            'money' => 1001,
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'source account does not have enough balance for transfer 1001']);
    }
}

final class ProcedureStyleTransferMoneyTestSeeder extends Seeder
{
    public function run(Connection $connection): void
    {
        $connection->table('accounts')->insert([
            'account_number' => 'A0001',
            'email' => 'a@example.com',
            'name' => 'Foo',
            'balance' => 1000,
        ]);
        $connection->table('accounts')->insert([
            'account_number' => 'B0001',
            'email' => 'b@example.com',
            'name' => 'Bar',
            'balance' => 500,
        ]);
    }
}
