<?php
declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Database\Connection;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\SetupDatabase;
use Tests\TestCase;

class GetAccountTest extends TestCase
{
    use SetupDatabase, DatabaseTransactions;

    /**
     * @test
     */
    public function get_account()
    {
        $response = $this->get('/api/accounts/A0001');

        $response->assertStatus(200);
        $response->assertJson([
            'account_number' => 'A0001',
            'balance'        => 1000,
        ]);
    }

    /**
     * @test
     */
    public function get_404()
    {
        $response = $this->get('/api/accounts/none');

        $response->assertStatus(404);
    }
}

final class GetAccountTestSeeder extends Seeder
{
    public function run(Connection $connection): void
    {
        $connection->table('accounts')->insert([
            'account_number' => 'A0001',
            'email'          => 'a@example.com',
            'name'           => 'Foo',
            'balance'        => 1000,
        ]);
    }
}
