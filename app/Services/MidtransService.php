<?php

namespace App\Services;

use App\Models\QROrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected $serverKey;
    protected $clientKey;
    protected $isProduction;
    
    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->clientKey = config('midtrans.client_key');
        $this->isProduction = config('midtrans.is_production', false);
        
        Log::info('MidtransService Initialized', [
            'server_key_exists' => !empty($this->serverKey),
            'is_production' => $this->isProduction
        ]);
    }
    
    public function createTransaction(QROrder $order): array
    {
        // Validasi server key
        if (empty($this->serverKey)) {
            Log::error('Midtrans Server Key is empty!');
            return [
                'success' => false,
                'error' => 'Server key tidak ditemukan. Silakan cek file .env'
            ];
        }
        
        $baseUrl = $this->isProduction 
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
        
        // Order ID unik
        $orderId = $order->order_number . '-' . time();
        
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $order->amount_paid,
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_whatsapp ?? '',
            ],
            'item_details' => [
                [
                    'id' => $order->qr_type,
                    'price' => (int) $order->amount_paid,
                    'quantity' => 1,
                    'name' => $this->getProductName($order->qr_type),
                ]
            ],
        ];
        
        try {
            Log::info('Calling Midtrans API', ['url' => $baseUrl, 'order_id' => $orderId]);
            
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($baseUrl, $payload);
            
            Log::info('Midtrans Response Status: ' . $response->status());
            
            if ($response->successful()) {
                $data = $response->json();
                Log::info('Midtrans Success', ['token' => substr($data['token'] ?? '', 0, 20)]);
                
                return [
                    'success' => true,
                    'token' => $data['token']
                ];
            }
            
            $errorMsg = 'HTTP ' . $response->status() . ': ' . $response->body();
            Log::error('Midtrans Failed: ' . $errorMsg);
            
            return [
                'success' => false,
                'error' => $errorMsg
            ];
            
        } catch (\Exception $e) {
            Log::error('Midtrans Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function getProductName(string $type): string
    {
        return match($type) {
            'static' => 'QR Code Static - Custom Design',
            'dynamic' => 'QR Code Dynamic + Custom Design',
            'qris_dynamic' => 'QRIS Dynamic + QR Code Custom',
            default => 'QR Code Custom'
        };
    }
}