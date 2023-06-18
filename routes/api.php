<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Customer;
use App\Http\Controllers\Api\AnalysisController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')
    ->get('/analysis',[AnalysisController::class,'index'])
    ->name('api.analysis');

Route::middleware('auth:sanctum')->get('/searchCustomers', function (Request $request) {
    return Customer::searchCustomer($request->search)//ローカルscope
    ->select('id','name','kana','tel')->paginate(50);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
