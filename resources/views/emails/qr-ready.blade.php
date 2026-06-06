<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; background: #f9fafb; }
        .button { background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 QR Code Siap!</h1>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $order->customer_name }}</strong>,</p>
            <p>QR Code Anda telah berhasil dibuat dengan kode order: <strong>{{ $order->order_number }}</strong></p>
            
            @if($order->qr_type === 'dynamic')
            <div style="background: #e0e7ff; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <strong>✨ Fitur Khusus QR Dinamis:</strong>
                <p>Anda bisa mengubah URL tujuan kapan saja tanpa mencetak ulang QR Code!</p>
                <p>Link manajemen: <a href="{{ url('/manage/' . $order->short_code) }}">klik di sini</a></p>
            </div>
            @endif
            
            <p>File QR Code terlampir dalam email ini. Bisa langsung digunakan untuk:</p>
            <ul>
                <li>📱 Dipasang di bio Linktree/LinkID</li>
                <li>🖨️ Dicetak untuk stiker, kartu nama, atau banner</li>
                <li>💻 Digunakan untuk keperluan digital lainnya</li>
            </ul>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/download/' . $order->order_number) }}" class="button">Download Ulang QR Code</a>
            </div>
            
            <p>Terima kasih telah menggunakan layanan kami!</p>
            <p>Salam,<br><strong>QR Premium+ Team</strong></p>
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} QR Premium+. All rights reserved.</p>
        </div>
    </div>
</body>
</html>