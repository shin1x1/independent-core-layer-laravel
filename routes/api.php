<?php
/**
 * @var Router $router
 */

use App\Action\GetAccount\GetAccountAction;
use App\Action\TransferMoney\TransferMoneyAction;
use Illuminate\Routing\Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
$router->get('/accounts/{accountNumber}', GetAccountAction::class)
    ->where('accountNumber', '[A-Z][0-9]{1,10}');
$router->put('/accounts/{accountNumber}/transfer', TransferMoneyAction::class)
    ->where('accountNumber', '[A-Z][0-9]{1,10}');

