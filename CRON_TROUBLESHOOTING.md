# 🚨 TROUBLESHOOTING CRON JOB - STEP BY STEP

## 📋 Masalah: Cron job tidak menghasilkan log dan WhatsApp tidak terkirim

### 🔍 LANGKAH 1: Diagnosa Dasar
1. **Akses:** `http://yourdomain.com/cron_diagnostic.php`
2. **Periksa:**
   - ✅ Path PHP yang benar
   - ✅ File artisan ada dan bisa dibaca
   - ✅ Permission direktori storage
   - ✅ Kredensial WhatsApp di .env

### 📱 LANGKAH 2: Test WhatsApp Manual
1. **Akses:** `http://yourdomain.com/manual_wa_test.php`
2. **Test kirim WhatsApp manual**
3. **Jika berhasil:** API WhatsApp normal ✅
4. **Jika gagal:** Perbaiki konfigurasi WhatsApp dulu

### 🔧 LANGKAH 3: Fix Permission & Setup Test
1. **Akses:** `http://yourdomain.com/fix_permissions.php`
2. **Script akan:**
   - ✅ Membuat `simple_cron_test.php`
   - ✅ Fix permission direktori
   - ✅ Memberikan command cron job yang benar

### ⏰ LANGKAH 4: Setup Cron Job Bertahap

#### Phase 1: Test Simple Cron (5 menit)
**Di cPanel → Cron Jobs, buat job:**
- **Minute:** `*/5`
- **Hour:** `*`
- **Day:** `*`
- **Month:** `*`
- **Weekday:** `*`
- **Command:** `/usr/local/bin/php /home/username/public_html/simple_cron_test.php`

**Ganti `/usr/local/bin/php` dengan path yang terdeteksi di diagnosa**
**Ganti `/home/username/public_html/` dengan path sebenarnya**

#### Phase 2: Monitor Simple Test
**Tunggu 5-10 menit, lalu periksa:**
1. File `simple_cron.log` terbuat di root direktori
2. Ada isi log dengan timestamp
3. Ada log WhatsApp test

**Jika Phase 1 BERHASIL, lanjut ke Phase 3**
**Jika Phase 1 GAGAL, ada masalah di:**
- Path PHP salah
- Permission denied
- Cron job tidak aktif di cPanel

#### Phase 3: Laravel Scheduler
**Update/Tambah cron job:**
- **Command:** `/usr/local/bin/php /home/username/public_html/artisan schedule:run >> /home/username/public_html/cron.log 2>&1`
- **Setting:** `* * * * *` (setiap menit)

#### Phase 4: Monitor Laravel
**Periksa file:**
1. `cron.log` - Output Laravel scheduler
2. `storage/logs/laravel.log` - Log aplikasi Laravel

## 🔍 MONITORING & DEBUGGING

### File Log yang Harus Dipantau:
1. **`simple_cron.log`** - Test cron sederhana
2. **`cron.log`** - Output Laravel scheduler
3. **`storage/logs/laravel.log`** - Log aplikasi Laravel

### Script Monitoring:
- **`cron_diagnostic.php`** - Diagnosa lengkap sistem
- **`manual_wa_test.php`** - Test WhatsApp manual
- **`fix_permissions.php`** - Fix permission & setup
- **`check_wa_system.php`** - System checker utama

## ❌ TROUBLESHOOTING UMUM

### Masalah: "No such file or directory"
**Solusi:**
- Path PHP salah → Gunakan hasil dari `cron_diagnostic.php`
- Path artisan salah → Gunakan path absolut penuh

### Masalah: "Permission denied"
**Solusi:**
- Jalankan `fix_permissions.php`
- Pastikan direktori storage writable (755)
- Pastikan file .env readable (644)

### Masalah: WhatsApp API Error
**Solusi:**
- Test manual di `manual_wa_test.php`
- Periksa kredensial di .env
- Pastikan IP server di-whitelist di Wablas

### Masalah: Cron Job "Running" tapi Tidak Ada Output
**Solusi:**
- Gunakan command dengan logging: `>> /path/to/cron.log 2>&1`
- Periksa user permission di hosting
- Pastikan cron job aktif di cPanel

## 🎯 EKSPEKTASI NORMAL

### Jika Sistem Berjalan Normal:
1. **File `simple_cron.log`** bertambah setiap 5 menit
2. **File `cron.log`** bertambah setiap menit
3. **WhatsApp terkirim** pada jam 09:00 dan 10:00
4. **Log Laravel** menunjukkan aktivitas scheduler

### Format Log Normal:
```
[2025-01-07 10:00:01] Cron job berjalan dari: /home/username/public_html
[2025-01-07 10:00:01] WhatsApp Test - HTTP: 200, Response: {"status":true,...}
```

## 📞 CHECKLIST DEBUGGING

### ✅ Basic Setup:
- [ ] PHP path terdeteksi benar
- [ ] File artisan exists dan readable
- [ ] Directory permission 755
- [ ] File .env exists dan readable
- [ ] Kredensial WhatsApp valid

### ✅ Cron Job Setup:
- [ ] Simple cron test berjalan (setiap 5 menit)
- [ ] File simple_cron.log terbuat dan bertambah
- [ ] Laravel scheduler berjalan (setiap menit)
- [ ] File cron.log terbuat dan bertambah

### ✅ WhatsApp Integration:
- [ ] Manual test WhatsApp berhasil
- [ ] Kredensial valid di .env
- [ ] IP server di-whitelist Wablas
- [ ] Auto WhatsApp terkirim pada jadwal

---

## 🚀 QUICK FIX COMMANDS

### Command Cron Job yang Paling Umum:
```bash
# Ganti path sesuai server Anda
/usr/local/bin/php /home/username/public_html/artisan schedule:run >> /home/username/public_html/cron.log 2>&1
```

### Alternative PHP Paths (pilih yang ada):
- `/usr/local/bin/php`
- `/usr/bin/php`
- `/usr/local/bin/php82`
- `/usr/local/bin/php81`

### Test Manual via SSH (jika ada akses):
```bash
cd /home/username/public_html
/usr/local/bin/php artisan schedule:run --verbose
```

---

**💡 Pro Tips:**
1. **Selalu test simple cron dulu** sebelum Laravel scheduler
2. **Gunakan logging** untuk melihat output cron job
3. **Monitor file log** secara berkala
4. **Jangan lupa whitelist IP** di dashboard Wablas
5. **Backup konfigurasi** yang sudah berjalan

---
*Updated: 2025-01-07 | Status: Comprehensive Troubleshooting Guide*