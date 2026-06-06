<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('qr_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_whatsapp')->nullable();
            
            // Data QR
            $table->string('target_url');
            $table->enum('qr_type', ['static', 'dynamic', 'qris_dynamic']);
            $table->json('customization')->nullable(); // {color, logo, size, margin}
            
            // Dynamic QR specific
            $table->string('short_code')->nullable()->unique();
            $table->string('dynamic_url')->nullable();
            
            // QRIS Dynamic specific
            $table->decimal('qris_amount', 15, 2)->nullable();
            $table->string('qris_note')->nullable();
            
            // File & status
            $table->string('file_path')->nullable();
            $table->string('file_format')->default('png');
            $table->enum('status', [
                'pending', 
                'processing', 
                'completed', 
                'failed', 
                'sent'
            ])->default('pending');
            $table->text('error_message')->nullable();
            
            // Payment (dari LinkID)
            $table->string('payment_id')->nullable();
            $table->decimal('amount_paid', 15, 2);
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('status');
            $table->index('short_code');
            $table->index('customer_email');
        });
    }

    public function down()
    {
        Schema::dropIfExists('qr_orders');
    }
};