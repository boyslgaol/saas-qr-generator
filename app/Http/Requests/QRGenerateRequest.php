<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QRGenerateRequest extends FormRequest
{
    public function authorize()
    {
        return true; // HARUS true
    }
    
    public function rules()
    {
        return [
            'customer_name' => 'nullable|string|max:100',
            'customer_email' => 'nullable|email|max:100',
            'customer_whatsapp' => 'nullable|string|max:20',
            'target_url' => 'nullable|url|max:500',
            'qr_type' => 'nullable|in:static,dynamic,qris_dynamic',
            'qr_color' => 'nullable|string',
            'qr_size' => 'nullable|integer|min:300|max:2000',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:512',
            'qris_amount' => 'nullable|numeric|min:1000',
            'qris_note' => 'nullable|string|max:100'
        ];
    }
    
    public function messages()
    {
        return [
            'customer_email.email' => 'Format email tidak valid',
            'target_url.url' => 'Format URL tidak valid',
            'qr_type.in' => 'Tipe QR tidak valid',
            'logo.image' => 'File harus berupa gambar',
            'logo.max' => 'Ukuran logo maksimal 512KB',
        ];
    }
}