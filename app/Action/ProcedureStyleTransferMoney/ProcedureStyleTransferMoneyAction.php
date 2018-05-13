<?php
declare(strict_types=1);

namespace App\Action\ProcedureStyleTransferMoney;

use Acme\Account\UseCase\ProcedureStyleTransferMoney\ProcedureStyleTransferMoney;
use App\Http\Requests\TransferMoneyRequest;
use Cake\Chronos\Chronos;

final class ProcedureStyleTransferMoneyAction
{
    /** @var ProcedureStyleTransferMoney */
    private $useCase;

    public function __construct(ProcedureStyleTransferMoney $useCase)
    {
        $this->useCase = $useCase;
    }

    /**
     * @param TransferMoneyRequest $request
     * @param string $accountNumber
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(TransferMoneyRequest $request, string $accountNumber)
    {
        $validated = $request->validated();

        $balance = $this->useCase->execute(
            $accountNumber,
            $validated['destination_number'],
            (int)$validated['money'],
            Chronos::now()
        );

        return response()->json([
            'balance' => $balance,
        ]);
    }
}
