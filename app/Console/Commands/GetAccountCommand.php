<?php

namespace App\Console\Commands;

use Acme\Account\Domain\Models\AccountNumber;
use Acme\Account\UseCase\GetAccount\GetAccount;
use Illuminate\Console\Command;

class GetAccountCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'account:get-account {account_number}';

    /**
     * @var string
     */
    protected $description = 'Get account information';

    /** @var GetAccount */
    private $getAccount;

    /**
     * @param GetAccount $getAccount
     */
    public function __construct(GetAccount $getAccount)
    {
        parent::__construct();

        $this->getAccount = $getAccount;
    }

    /**
     * @throws \Acme\Account\Domain\Exceptions\NotFoundException
     */
    public function handle()
    {
        $account = $this->getAccount->execute(
            AccountNumber::of($this->argument('account_number'))
        );

        $this->info(json_encode([
            'account_number' => $account->accountNumber()->asString(),
            'name'           => $account->name(),
            'email'          => $account->email()->asString(),
            'balance'        => $account->balance()->asInt(),
        ]));
    }
}
