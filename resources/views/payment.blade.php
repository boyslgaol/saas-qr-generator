<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - QR Premium+</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-8 text-center">
                <h1 class="text-2xl font-bold text-white">💳 Selesaikan Pembayaran</h1>
                <p class="text-purple-100 mt-2">Kode Order: {{ $order->order_number }}</p>
            </div>
            
            <div class="p-6">
                <div class="border-b pb-4 mb-4">
                    <h2 class="font-semibold text-gray-700 mb-2">Detail Pesanan</h2>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Tipe QR:</span>
                        <span class="font-medium">
                            @if($order->qr_type == 'static') QR Static
                            @elseif($order->qr_type == 'dynamic') QR Dynamic
                            @else QRIS Dynamic
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">URL Tujuan:</span>
                        <span class="font-medium text-sm truncate">{{ $order->target_url }}</span>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-semibold">Total Pembayaran:</span>
                        <span class="text-2xl font-bold text-purple-600">
                            Rp {{ number_format($order->amount_paid, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                
                <button id="pay-button" 
                        class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold py-3 rounded-lg hover:opacity-90 transition text-lg">
                    Bayar Sekarang 💳
                </button>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('pay-button').addEventListener('click', function() {
            console.log('Token:', '{{ $snapToken }}');
            console.log('Order Number:', '{{ $order->order_number }}');
            
            snap.pay('{{ $snapToken }}', {
                onSuccess: function(result) {
                    console.log('Payment Success:', result);
                    // GANTI dengan URL manual
                    window.location.href = '/payment-success/{{ $order->order_number }}';
                },
                onPending: function(result) {
                    console.log('Payment Pending:', result);
                    alert('Menunggu pembayaran. Silakan selesaikan pembayaran Anda.');
                },
                onError: function(result) {
                    console.log('Payment Error:', result);
                    alert('Terjadi kesalahan: ' + (result.status_message || 'Silakan coba lagi'));
                },
                onClose: function() {
                    console.log('Payment popup closed');
                }
            });
        });
    </script>
</body>
</html>