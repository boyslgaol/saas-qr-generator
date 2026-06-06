<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateQRJob;
use App\Models\QROrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleMidtrans(Request $request)
    {
        Log::info('Midtrans Webhook Received', $request->all());
        
        $orderId = $request->order_id;
        $transactionStatus = $request->transaction_status;
        $fraudStatus = $request->fraud_status;
        
        // Extract order number dari order_id (format: QR-XXXXX-timestamp)
        $orderNumber = explode('-', $orderId);
        array_pop($orderNumber); // remove timestamp
        $orderNumber = implode('-', $orderNumber);
        
        $order = QROrder::where('order_number', $orderNumber)->first();
        
        if (!$order) {
            Log::error('Order not found: ' . $orderNumber);
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }
        
        // Handle payment status
        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            if ($fraudStatus == 'accept' || $transactionStatus == 'settlement') {
                // Payment success
                $order->update([
                    'payment_id' => $request->transaction_id,
                    'paid_at' => now(),
                    'status' => 'processing',
                    'payment_method' => $request->payment_type ?? null
                ]);
                
                // Dispatch job untuk generate QR
                GenerateQRJob::dispatch($order);
                
                Log::info('Payment success, QR generation dispatched for order: ' . $order->order_number);
            }
        } 
        elseif ($transactionStatus == 'pending') {
            $order->update([
                'status' => 'pending',
                'error_message' => 'Menunggu pembayaran'
            ]);
        } 
        elseif ($transactionStatus == 'deny' || $transactionStatus == 'cancel' || $transactionStatus == 'expire') {
            $order->update([
                'status' => 'failed',
                'error_message' => 'Pembayaran gagal: ' . $transactionStatus
            ]);
        }
        
        return response()->json(['status' => 'ok']);
    }
}