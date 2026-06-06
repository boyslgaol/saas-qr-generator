<?php

namespace App\Mail;

use App\Models\QROrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QRCodeReadyMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $order;
    
    public function __construct(QROrder $order)
    {
        $this->order = $order;
    }
    
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'QR Code Anda Sudah Siap - ' . $this->order->order_number,
        );
    }
    
    public function content(): Content
    {
        return new Content(
            view: 'emails.qr-ready',
        );
    }
    
    public function attachments(): array
    {
        // Attach QR Code file
        $attachments = [];
        
        if ($this->order->file_path && file_exists(storage_path('app/public/' . $this->order->file_path))) {
            $attachments[] = Attachment::fromPath(storage_path('app/public/' . $this->order->file_path))
                ->as($this->order->order_number . '.png')
                ->withMime('image/png');
        }
        
        // Attach ZIP dengan multiple format (opsional)
        if ($this->order->qr_type === 'dynamic') {
            $attachments[] = Attachment::fromPath($this->generateInstructionPdf())
                ->as('panduan-dynamic-qr.pdf');
        }
        
        return $attachments;
    }
    
    private function generateInstructionPdf()
    {
        // Generate PDF petunjuk edit dynamic URL
        // Bisa pakai Barryvdh\DomPDF atau niklasravnsborg/laravel-pdf
        return storage_path('app/templates/panduan-dynamic-qr.pdf');
    }
}