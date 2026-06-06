<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QROrder extends Model
{
    use SoftDeletes;
    
    protected $table = 'qr_orders';
    
    protected $fillable = [
    'order_number',
    'customer_name',
    'customer_email',
    'customer_whatsapp',
    'target_url',
    'qr_type',
    'customization',
    'short_code',
    'dynamic_url',
    'qris_amount',
    'qris_note',
    'file_path',
    'file_format',
    'status',
    'error_message',
    'payment_id',
    'amount_paid',
    'payment_method',
    'paid_at'
];
    
    protected $casts = [
        'customization' => 'array',
        'qris_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    
    // PERBAIKAN: Pastikan order_number terisi saat create
    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                // Format: QR-{tanggal}-{random}
                $order->order_number = 'QR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }
    
    public function getQrUrlAttribute()
    {
        if ($this->qr_type === 'dynamic' && $this->dynamic_url) {
            return $this->dynamic_url;
        }
        return $this->target_url;
    }
    
    public function scopePaid($query)
    {
        return $query->whereNotNull('paid_at');
    }
}