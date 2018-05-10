<?php
declare(strict_types=1);

namespace App\Action\GetAccount;

use Acme\Account\Domain\Models\AccountNumber;
use Acme\Account\UseCase\GetAccount\GetAccount;
use Illuminate\Http\Request;

final class GetAccountAction
{
    /** @var GetAccount */
    private $useCase;

    /**
     * @param GetAccount $useCase
     */
    public function __construct(GetAccount $useCase)
    {
        $this->useCase = $useCase;
    }

    /**
     * @param Request $request
     * @param string $accountNumber
     * @return \Illuminate\Http\JsonResponse
     * @throws \Acme\Account\Domain\Exceptions\NotFoundException
     */
    public function __invoke(Request $request, string $accountNumber)
    {
        $account = $this->useCase->execute(AccountNumber::of($accountNumber));

        return response()->json([
            'account_number' => $account->accountNumber()->asString(),
            'balance'        => $account->balance()->asInt(),
        ]);
    }
}
