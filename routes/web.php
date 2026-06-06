<?php

use App\Http\Controllers\QRController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/simple-form', function() {
    return '
    <!DOCTYPE html>
    <html>
    <head><title>Simple Form</title></head>
    <body>
        <h1>Simple Form Test</h1>
        <form action="/simple-submit" method="POST">
            <input type="hidden" name="_token" value="' . csrf_token() . '">
            <input type="text" name="name" placeholder="Nama" value="Test">
            <button type="submit">Submit</button>
        </form>
    </body>
    </html>
    ';
});

Route::post('/simple-submit', function() {
    return 'SUCCESS! Form berhasil di-submit! Data: ' . json_encode(request()->all());
});
// Halaman utama
Route::get('/', [QRController::class, 'orderForm'])->name('home');
Route::get('/order', [QRController::class, 'orderForm'])->name('order.form');

// Proses order & payment
Route::post('/order', [QRController::class, 'storeOrder'])->name('qr.store');

// PERBAIKAN: pastikan parameter name sama dengan di controller
Route::get('/payment/{orderNumber}', [QRController::class, 'paymentPage']);
Route::get('/payment-success/{orderNumber}', [QRController::class, 'paymentSuccess']);
Route::get('/payment-cancel/{orderNumber}', [QRController::class, 'paymentCancel']);

// Download
Route::get('/download/{orderNumber}', [QRController::class, 'download']);

// Webhook Midtrans
Route::post('/webhook/midtrans', [WebhookController::class, 'handleMidtrans']);
// Dynamic redirect (temporary)
Route::get('/r/{shortCode}', function($shortCode) {
    return redirect('https://google.com');
})->name('dynamic.redirect');
