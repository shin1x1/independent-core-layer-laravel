<?php
declare(strict_types=1);

namespace App\Eloquents;

use Acme\Account\Domain\Models\Account;
use Acme\Account\Domain\Models\AccountNumber;
use Acme\Account\Domain\Models\Balance;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $account_number
 * @property string $name
 * @property int $balance
 */
final class EloquentAccount extends Model
{
    protected $table = 'accounts';
    public $timestamps = false;

    /**
     * @param AccountNumber $accountNumber
     * @return Model|\Illuminate\Database\Query\Builder|null|object
     */
    public function findByAccountNumberWithLockForUpdate(AccountNumber $accountNumber)
    {
        return $this->newQuery()
            ->where('account_number', $accountNumber->asString())
            ->lockForUpdate()
            ->first();
    }

    /**
     * @param AccountNumber $accountNumber
     * @return Model|\Illuminate\Database\Query\Builder|null|object
     */
    public function findByAccountNumber(AccountNumber $accountNumber)
    {
        return $this->newQuery()
            ->where('account_number', $accountNumber->asString())
            ->first();
    }

    /**
     * @param Balance $balance
     * @param AccountNumber $accountNumber
     */
    public function updateBalance(AccountNumber $accountNumber, Balance $balance)
    {
        $this->newQuery()
            ->where('account_number', $accountNumber->asString())
            ->update(['balance' => $balance->asInt()]);
    }

    /**
     * @return Account
     */
    public function toModel(): Account
    {
        return Account::ofByArray($this->attributesToArray());
    }
}
