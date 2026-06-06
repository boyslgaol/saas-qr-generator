<?php

namespace App\Jobs;

use App\Jobs\SendQRCodeEmailJob;
use App\Models\QROrder;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateQRJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $timeout = 120; // 2 menit timeout
    public $tries = 3; // coba 3x jika gagal
    
    protected $order;
    
    public function __construct(QROrder $order)
    {
        $this->order = $order;
    }
    
    public function handle()
    {
        try {
            $this->order->update(['status' => 'processing']);
            
            // Siapkan data URL
            $url = $this->prepareUrl();
            
            // Build QR Code
            $builder = Builder::create()
                ->writer(new PngWriter())
                ->writerOptions([])
                ->data($url)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size($this->order->customization['size'] ?? 800)
                ->margin($this->order->customization['margin'] ?? 10)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->validateResult(false);
            
            // Set warna kustom (jika ada)
            if (!empty($this->order->customization['color'])) {
                // Endroid QR Code mendukung setForegroundColor
                // Atau kita bisa post-process dengan Intervention Image
            }
            
            // Tambahkan logo (jika ada)
            if (!empty($this->order->customization['logo_path'])) {
                $builder->logoPath(storage_path('app/' . $this->order->customization['logo_path']));
                $builder->logoResizeToWidth(150);
                $builder->logoPunchoutBackground(true);
            }
            
            $qrCode = $builder->build();
            
            // Simpan file
            $filename = 'qrcodes/' . $this->order->order_number . '.' . $this->order->file_format;
            Storage::disk('public')->put($filename, $qrCode->getString());
            
            // Update order
            $this->order->update([
                'file_path' => $filename,
                'status' => 'completed'
            ]);
            
            // Kirim email (akan di trigger setelah job selesai)
            dispatch(new SendQRCodeEmailJob($this->order));
            
        } catch (\Exception $e) {
            $this->order->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    private function prepareUrl(): string
    {
        if ($this->order->qr_type === 'dynamic') {
            // Dynamic QR: buat short URL
            return $this->createDynamicUrl();
        }
        
        if ($this->order->qr_type === 'qris_dynamic') {
            // QRIS Dynamic: generate QRIS dengan nominal
            return $this->generateQrisDynamic();
        }
        
        return $this->order->target_url;
    }
    
    private function createDynamicUrl(): string
    {
        $shortCode = $this->order->short_code ?? $this->generateShortCode();
        
        $dynamicUrl = url('/r/' . $shortCode);
        
        $this->order->update([
            'short_code' => $shortCode,
            'dynamic_url' => $dynamicUrl
        ]);
        
        // Simpan mapping ke cache/redis untuk redirect cepat
        cache(['redirect:' . $shortCode => $this->order->target_url], 86400 * 30);
        
        return $dynamicUrl;
    }
    
    private function generateShortCode(): string
    {
        do {
            $code = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
        } while (QROrder::where('short_code', $code)->exists());
        
        return $code;
    }
    
    private function generateQrisDynamic(): string
    {
        // Format QRIS Dinamis (Standar QRIS)
        // Ini contoh sederhana, untuk production perlu library khusus
        $amount = $this->order->qris_amount;
        $note = $this->order->qris_note ?? 'Pembayaran QR Code';
        
        // Format QRIS string (simplified)
        // QRIS: 00020101021126660016COM.QRIS.WWW01189360091234567890215$note
        $qrisString = $this->buildQrisString($amount, $note);
        
        return $qrisString;
    }
    
    private function buildQrisString($amount, $note): string
    {
        // Implementasi build QRIS string sesuai standar
        // Atau gunakan library: https://github.com/andriannus/qris-dynamicify
        return "00020101021126660016COM.QRIS.WWW01189360091234567890215" . $note;
    }
}