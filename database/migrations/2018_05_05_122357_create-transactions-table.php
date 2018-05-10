<?php
declare(strict_types=1);

use Acme\Account\Domain\Models\TransactionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            Schema::create('transaction_types', function (Blueprint $table) {
                /** @noinspection PhpUndefinedMethodInspection */
                $table->string('type')->primary();
            });

            DB::table('transaction_types')->insert([
                'type' => TransactionType::WITHDRAW()->asString(),
            ]);
            DB::table('transaction_types')->insert([
                'type' => TransactionType::DEPOSIT()->asString(),
            ]);

            Schema::create('transactions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('account_number');
                $table->string('transaction_type');
                $table->dateTime('transaction_time');
                $table->integer('amount');
                $table->text('comment');

                /** @noinspection PhpUndefinedMethodInspection */
                $table->foreign('account_number')
                    ->references('account_number')
                    ->on('accounts');
                /** @noinspection PhpUndefinedMethodInspection */
                $table->foreign('transaction_type')
                    ->references('type')
                    ->on('transaction_types');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transactions');
        Schema::drop('transaction_types');
    }
}
