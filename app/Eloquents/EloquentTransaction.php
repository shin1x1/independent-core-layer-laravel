<?php
declare(strict_types=1);

namespace App\Eloquents;

use Acme\Account\Domain\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $account_number
 * @property string $transaction_type
 * @property string $transaction_time
 * @property int $amount
 * @property string $comment
 */
final class EloquentTransaction extends Model
{
    protected $table = 'transactions';
    public $timestamps = false;

    /**
     * @param Transaction $transaction
     */
    public function store(Transaction $transaction)
    {
        $eloquent = $this->newInstance();
        $eloquent->account_number = $transaction->accountNumber()->asString();
        $eloquent->transaction_type = $transaction->transactionType()->asString();
        $eloquent->transaction_time = $transaction->transactionTime();
        $eloquent->amount = $transaction->amount()->asInt();
        $eloquent->comment = $transaction->comment();
        $eloquent->save();
    }
}
