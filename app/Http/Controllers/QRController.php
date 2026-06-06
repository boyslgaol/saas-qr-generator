<?php

namespace App\Http\Controllers;

use App\Http\Requests\QRGenerateRequest;
use App\Models\QROrder;
use App\Services\MidtransService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QRController extends Controller
{
    protected $midtrans;
    
    public function __construct(MidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }
    
    // Halaman order form
    public function orderForm()
    {
        return view('order-form', [
            'qrTypes' => [
                'static' => 'QR Statis (URL tetap)',
                'dynamic' => 'QR Dinamis (URL bisa diubah)',
                'qris_dynamic' => 'QRIS Dinamis (dengan nominal)'
            ]
        ]);
    }
    
    // Proses order
   public function storeOrder(QRGenerateRequest $request)
{
    // Log data yang masuk
    Log::info('storeOrder called', $request->all());
    
    try {
        DB::beginTransaction();
        
        // Upload logo (jika ada)
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            Log::info('Logo uploaded: ' . $logoPath);
        }
        
        // Generate order number
        $orderNumber = 'QR-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));
        
        // Simpan order
        $order = QROrder::create([
            'order_number' => $orderNumber,
            'customer_name' => $request->customer_name ?? 'Customer',
            'customer_email' => $request->customer_email ?? 'customer@email.com',
            'customer_whatsapp' => $request->customer_whatsapp,
            'target_url' => $request->target_url ?? 'https://example.com',
            'qr_type' => $request->qr_type ?? 'static',
            'customization' => [
                'color' => $request->qr_color ?? '#000000',
                'size' => $request->qr_size ?? 800,
                'margin' => $request->qr_margin ?? 10,
                'logo_path' => $logoPath
            ],
            'qris_amount' => $request->qris_amount,
            'qris_note' => $request->qris_note,
            'amount_paid' => $this->calculatePrice($request->qr_type ?? 'static'),
            'status' => 'pending'
        ]);
        
        DB::commit();
        
        Log::info('Order created: ' . $orderNumber);
        
        // Redirect ke payment page
        return redirect('/payment/' . $orderNumber);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Store order error: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}
    
    // Halaman payment
    public function paymentPage($orderNumber)
    {
        Log::info('Payment page accessed for order: ' . $orderNumber);
        
        $order = QROrder::where('order_number', $orderNumber)
            ->whereNull('paid_at')
            ->first();
        
        if (!$order) {
            Log::error('Order not found: ' . $orderNumber);
            return redirect('/')->with('error', 'Order tidak ditemukan');
        }
        
        // Create Midtrans transaction
        $result = $this->midtrans->createTransaction($order);
        
        if (!$result['success']) {
            Log::error('Midtrans payment failed: ' . $result['error']);
            return back()->with('error', 'Gagal memproses pembayaran: ' . $result['error']);
        }
        
        return view('payment', [
            'order' => $order,
            'snapToken' => $result['token'],
            'clientKey' => config('midtrans.client_key')
        ]);
    }
    
    // Halaman sukses
    public function paymentSuccess($orderNumber)
    {
        Log::info('Payment success page for order: ' . $orderNumber);
        
        $order = QROrder::where('order_number', $orderNumber)
            ->whereNotNull('paid_at')
            ->first();
        
        if (!$order) {
            return redirect('/')->with('error', 'Order tidak ditemukan');
        }
        
        return view('payment-success', ['order' => $order]);
    }
    
    // Halaman cancel
    public function paymentCancel($orderNumber)
    {
        return view('payment-cancel', ['orderNumber' => $orderNumber]);
    }
    
    // Hitung harga
    private function calculatePrice($type)
    {
        return match($type) {
            'static' => 25000,
            'dynamic' => 65000,
            'qris_dynamic' => 125000,
            default => 25000
        };
    }
    
    // Download QR
    public function download($orderNumber)
    {
        $order = QROrder::where('order_number', $orderNumber)
            ->whereNotNull('paid_at')
            ->firstOrFail();
            
        if (!$order->file_path || !Storage::disk('public')->exists($order->file_path)) {
            abort(404, 'File belum siap, silahkan coba beberapa saat lagi');
        }
        
        return response()->download(Storage::disk('public')->path($order->file_path), $order->order_number . '.png');
    }
}