<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat QR Code Custom - QR Premium+</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-8">
                <h1 class="text-3xl font-bold text-white">Custom QR Code Generator</h1>
                <p class="text-purple-100 mt-2">Buat QR Code premium dengan warna dan logo custom</p>
            </div>
            
            <form action="{{ route('qr.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                
                {{-- Informasi Customer --}}
                <div class="border-b pb-4">
                    <h2 class="text-xl font-semibold mb-4">Data Diri</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nama Lengkap *</label>
                            <input type="text" name="customer_name" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Email *</label>
                            <input type="email" name="customer_email" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">WhatsApp (opsional)</label>
                            <input type="tel" name="customer_whatsapp" class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>
                </div>
                
                {{-- Tipe QR --}}
                <div class="border-b pb-4">
                    <h2 class="text-xl font-semibold mb-4">Pilih Tipe QR</h2>
                    <div class="grid md:grid-cols-3 gap-4">
                        @foreach($qrTypes as $value => $label)
                        <label class="border rounded-lg p-4 cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="qr_type" value="{{ $value }}" class="mr-2" required>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>
                
                {{-- URL & Customisasi --}}
                <div class="border-b pb-4">
                    <h2 class="text-xl font-semibold mb-4">Konten QR Code</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">URL Tujuan *</label>
                            <input type="url" name="target_url" placeholder="https://..." required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Warna QR</label>
                                <input type="color" name="qr_color" value="#000000" class="w-full h-10 border rounded">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Ukuran (px)</label>
                                <input type="number" name="qr_size" value="800" min="300" max="2000" class="w-full border rounded-lg px-3 py-2">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Logo Tengah (opsional)</label>
                            <input type="file" name="logo" accept="image/png,image/jpg,image/jpeg" class="w-full border rounded-lg px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Max 512KB, format PNG/JPG (rekomendasi PNG transparan)</p>
                        </div>
                    </div>
                </div>
                
                {{-- QRIS Dynamic (conditional) --}}
                <div class="border-b pb-4 qris-fields" style="display: none;">
                    <h2 class="text-xl font-semibold mb-4">Data QRIS Dinamis</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nominal (Rp) *</label>
                            <input type="number" name="qris_amount" placeholder="10000" class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Catatan (opsional)</label>
                            <input type="text" name="qris_note" placeholder="Pembayaran untuk..." class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>
                </div>
                
                {{-- Price & Submit --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between items-center mb-4">
                        <span class="font-semibold">Total Pembayaran:</span>
                        <span class="text-2xl font-bold text-purple-600" id="priceDisplay">Rp 25.000</span>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-blue-600 text-white font-semibold py-3 rounded-lg hover:opacity-90 transition">
                        Lanjut ke Pembayaran 💳
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Toggle QRIS fields based on selected type
        $('input[name="qr_type"]').on('change', function() {
            if ($(this).val() === 'qris_dynamic') {
                $('.qris-fields').show();
            } else {
                $('.qris-fields').hide();
            }
            
            // Update price
            let price = 25000;
            if ($(this).val() === 'dynamic') price = 65000;
            if ($(this).val() === 'qris_dynamic') price = 125000;
            
            $('#priceDisplay').text('Rp ' + price.toLocaleString('id-ID'));
        });
    </script>
</body>
</html>