<?php
declare(strict_types=1);

namespace App\Mail;

use Acme\Account\Domain\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransferMoneyMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var Account */
    public $account;

    /**
     * @param Account $account
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('TransferMoney')
            ->text('emails.transfer_money', [
                'balance' => $this->account->balance()->asInt(),
            ]);
    }
}
