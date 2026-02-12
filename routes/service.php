<?php

use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StoreInfoController;
use Illuminate\Support\Facades\Route;


//Services  Routes *for example whatsapp services
Route::get('/admin/whatsapp/status', [ServiceController::class, 'GetWhatsAppStatus']);
Route::post('/admin/whatsapp/enable', [ServiceController::class, 'enableWhatsappService']);
Route::post('/admin/whatsapp/disable', [ServiceController::class, 'disableWhatsappService']);
Route::post('/admin/whatsapp/add_limit', [ServiceController::class, 'AddWhatsappLimit']);


//Enable shipping service from The main dashboard

Route::get('/admin/shipping/status', [ServiceController::class, 'GetShippingServiceStatus']);
Route::post('/admin/shipping/enable', [ServiceController::class, 'enableShippingService']);



//Enable Sms Service from dashbaord  
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/sms_taqnyat/enable', [ServiceController::class, 'EnableTaqnyatSms']);
    
    Route::get('/admin/sms_taqnyat', [ServiceController::class, 'getTaqnyatSmsToken']);

    //Get customers based on type or rule to send marketting emails or smss 
    Route::get('/admin/users', [ServiceController::class, 'getUsers']);

});






//Store Managment 


Route::post('/store/active', [StoreInfoController::class, 'enableStore']);
Route::post('/store/deactive', [StoreInfoController::class, 'disableStore']);
Route::post('/store/update-subscription', [StoreInfoController::class, 'updateSubscriptionPackage']);


//te