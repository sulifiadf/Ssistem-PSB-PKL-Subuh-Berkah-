<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Tidak Valid</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ff7043 0%, #f44336 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .header {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
            padding: 40px 20px;
        }

        .error-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .content {
            padding: 30px 20px;
        }

        .error-message {
            background: #ffebee;
            border: 1px solid #f44336;
            color: #d32f2f;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: left;
        }

        .info-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .info-list {
            color: #666;
            line-height: 1.6;
        }

        .info-list li {
            margin-bottom: 5px;
        }

        .contact-info {
            background: #e3f2fd;
            border: 1px solid #2196F3;
            border-radius: 10px;
            padding: 15px;
            color: #1976D2;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="error-icon">❌</div>
            <h1>Link Tidak Valid</h1>
        </div>

        <div class="content">
            <div class="error-message">
                <strong>{{ $message }}</strong>
            </div>

            <div class="info-box">
                <div class="info-title">Kemungkinan Penyebab:</div>
                <ul class="info-list">
                    <li>Link sudah kedaluwarsa (hanya berlaku 1 hari)</li>
                    <li>Link sudah pernah digunakan untuk konfirmasi</li>
                    <li>Link rusak atau tidak lengkap</li>
                    <li>Akses dari perangkat yang berbeda</li>
                </ul>
            </div>

            <div class="info-box">
                <div class="info-title">Solusi:</div>
                <ul class="info-list">
                    <li>Gunakan link terbaru yang dikirim via WhatsApp</li>
                    <li>Login langsung ke sistem presensi</li>
                    <li>Hubungi admin jika masalah berlanjut</li>
                </ul>
            </div>

            <div class="contact-info">
                <strong>Butuh Bantuan?</strong><br>
                Silakan hubungi admin sistem untuk mendapatkan bantuan lebih lanjut.
            </div>
        </div>
    </div>
</body>

</html>
