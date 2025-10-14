# 🔔 Panduan Sistem Notifikasi WhatsApp PSB

## 📋 Ringkasan Sistem

Sistem PSB telah dikonfigurasi untuk mengirim notifikasi WhatsApp otomatis pada jadwal berikut:
- **09:00** - Reminder pagi untuk konfirmasi kehadiran
- **10:00** - Reminder siang (backup)
- **Setiap 5 menit** - Proses auto-libur untuk yang tidak konfirmasi
- **06:00** - Cleanup anggota sementara
- **Harian** - Cleanup token expired

## 🔍 Cara Mengecek Status Sistem

### 1. **Akses System Checker**
Buka di browser: `http://domain-anda.com/check_wa_system.php`

Script ini akan mengecek:
- ✅ Konfigurasi environment variables
- 📱 Test koneksi WhatsApp API
- ⏰ Status cron jobs
- 🐘 Informasi PHP
- 📅 Scheduled tasks Laravel

### 2. **Test Manual Scheduler**
Buka di browser: `http://domain-anda.com/test_scheduler.php`

Untuk menjalankan scheduler secara manual dan melihat hasilnya.

### 3. **Monitor Logs**
Buka di browser: `http://domain-anda.com/view_logs.php`

Untuk melihat log aktivitas sistem dan notifikasi WhatsApp.

## ⚠️ Jika Notifikasi Tidak Terkirim

### A. Periksa Cron Jobs di cPanel
1. Masuk ke **cPanel** → **Cron Jobs**
2. Pastikan ada job dengan setting:
   - **Minute**: `*` (setiap menit)
   - **Hour**: `*`
   - **Day**: `*`
   - **Month**: `*`
   - **Weekday**: `*`
   - **Command**: `/usr/local/bin/php /home/username/public_html/artisan schedule:run >> /dev/null 2>&1`

### B. Periksa Path PHP
Cek path PHP yang benar di cPanel:
1. Jalankan script: `check_php_path.php`
2. Atau gunakan command di terminal: `which php`
3. Update command cron job dengan path yang benar

### C. Periksa Konfigurasi WhatsApp
1. Pastikan file `.env` memiliki:
   ```
   WHATSAPP_API_KEY=your_api_key
   WHATSAPP_WEBHOOK_SECRET=your_secret_key
   ```
2. Pastikan IP server sudah di-whitelist di dashboard Wablas
3. Test koneksi menggunakan `check_wa_system.php`

### D. Periksa Laravel Logs
1. Buka `view_logs.php` atau akses file `storage/logs/laravel.log`
2. Cari entry yang mengandung kata kunci:
   - `WA Reminder`
   - `Auto-libur`
   - `ERROR`
   - `WhatsApp`

## 🛠️ Troubleshooting Langkah demi Langkah

### Langkah 1: Verifikasi Cron Jobs
```bash
# Via SSH, cek apakah cron berjalan
ps aux | grep cron
```

### Langkah 2: Test Manual
```bash
# Jalankan scheduler manual via SSH
cd /path/to/your/project
php artisan schedule:run --verbose
```

### Langkah 3: Check Environment
```bash
# Cek file .env
cat .env | grep WHATSAPP
```

### Langkah 4: Test WhatsApp API
```bash
# Test via curl
curl -X POST https://pati.wablas.com/api/send-message \
  -H "Authorization: YOUR_TOKEN.YOUR_SECRET" \
  -d "phone=6288228815362&message=Test"
```

## 📞 Checklist Harian

### ✅ Yang Harus Diperiksa Setiap Hari:
- [ ] Buka `check_wa_system.php` - pastikan status hijau
- [ ] Periksa `view_logs.php` - cari aktivitas WA Reminder
- [ ] Konfirmasi dengan user apakah notifikasi diterima
- [ ] Periksa dashboard admin untuk status kehadiran

### ✅ Yang Harus Diperiksa Mingguan:
- [ ] Periksa statistik penggunaan WhatsApp di dashboard Wablas
- [ ] Backup file log (`storage/logs/laravel.log`)
- [ ] Update IP whitelist jika ada perubahan server

## 🚨 Error Codes dan Solusinya

### HTTP 401 - Unauthorized
- **Penyebab**: Token WhatsApp tidak valid
- **Solusi**: Periksa WHATSAPP_API_KEY dan SECRET di .env

### HTTP 403 - Forbidden
- **Penyebab**: IP tidak di-whitelist
- **Solusi**: Tambahkan IP server ke dashboard Wablas

### HTTP 500 - Server Error
- **Penyebab**: Error di aplikasi Laravel
- **Solusi**: Periksa storage/logs/laravel.log

### Cron Job Tidak Jalan
- **Penyebab**: Path PHP salah atau permission denied
- **Solusi**: Periksa path PHP dan permission folder

## 📱 Nomor Test WhatsApp

Sistem dikonfigurasi untuk test ke nomor: **6288228815362**

Untuk mengganti nomor test, edit file:
- `check_wa_system.php` pada bagian test koneksi
- `app/Helpers/sendWa.php` pada fungsi testWa()

## 📝 Log Format

Format log yang akan muncul:
```
[2025-01-07 09:00:01] local.INFO: WA Reminder sent (morning) {"sent": 5, "failed": 0}
[2025-01-07 10:00:01] local.INFO: WA Reminder sent (afternoon) {"sent": 3, "failed": 0}
[2025-01-07 09:05:01] local.INFO: Auto-libur processed successfully
```

## 🎯 Target Operasional

### Indikator Sistem Sehat:
1. **Response Time**: WhatsApp API < 5 detik
2. **Success Rate**: > 95% pesan terkirim
3. **Cron Reliability**: Jalan setiap menit tanpa error
4. **Log Cleanliness**: Minimal error di log harian

### Monitoring Metrics:
- Jumlah pesan terkirim per hari
- Tingkat keberhasilan pengiriman
- Response time rata-rata
- Downtime sistem (jika ada)

---

**💡 Pro Tips:**
- Bookmark halaman `check_wa_system.php` untuk monitoring rutin
- Set reminder harian untuk cek sistem
- Dokumentasikan setiap perubahan konfigurasi
- Simpan backup konfigurasi WhatsApp

**🔗 Quick Links:**
- [System Checker](check_wa_system.php)
- [Test Scheduler](test_scheduler.php)
- [View Logs](view_logs.php)
- [Dashboard Admin](admin/dashboard)

---
*Generated: 2025-01-07 | Version: 1.0*