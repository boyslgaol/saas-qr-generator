<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - QR Premium+</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden text-center">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-12">
                <div class="text-6xl mb-4">✅</div>
                <h1 class="text-2xl font-bold text-white">Pembayaran Berhasil!</h1>
                <p class="text-green-100 mt-2">Order: {{ $order->order_number }}</p>
            </div>
            
            <div class="p-6">
                <p class="text-gray-700 mb-4">
                    Terima kasih {{ $order->customer_name }}! Pembayaran Anda telah kami terima.
                </p>
                
                <p class="text-gray-600 mb-6">
                    QR Code sedang kami proses. File akan dikirim ke email Anda:
                    <strong>{{ $order->customer_email }}</strong>
                    dalam beberapa menit.
                </p>
                
                <div class="bg-blue-50 p-4 rounded-lg mb-6">
                    <p class="text-sm text-blue-800">
                        💡 <strong>Tips:</strong> Cek folder Spam/Promosi jika email tidak masuk dalam 5 menit.
                    </p>
                </div>
                
                <a href="{{ route('home') }}" 
                   class="inline-block bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition">
                    Buat QR Code Lagi
                </a>
            </div>
        </div>
    </div>
</body>
</html>