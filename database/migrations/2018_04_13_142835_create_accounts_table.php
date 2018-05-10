<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->string('account_number', 10)->unique();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->string('email', 255)->unique();
            $table->string('name');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->bigInteger('balance')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
