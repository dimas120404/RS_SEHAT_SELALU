# RS Sehat Selalu - Security Upgrade Implementation

## Fitur Keamanan yang Diimplementasikan

### 1. ✅ Password Hashing dengan password_hash()
- Upgrade dari MD5 ke bcrypt (password_hash/password_verify)
- Backward compatibility untuk password lama
- Automatic upgrade saat user login

### 2. ✅ Rate Limiting
- Pembatasan percobaan login berdasarkan IP dan username
- Lockout otomatis setelah 5 percobaan gagal
- Pembersihan otomatis data lama

### 3. ✅ HTTPS Enforcement
- Redirect otomatis ke HTTPS
- Pengaturan cookie secure
- Session security enhancements

### 4. ✅ Database Encryption
- Enkripsi data sensitif dengan AES-256-CBC
- Key management system
- Fungsi encrypt/decrypt untuk data

### 5. ✅ Two-Factor Authentication (2FA)
- TOTP implementation
- Setup dan disable 2FA
- Backup codes untuk recovery

## Instruksi Implementasi

### Langkah 1: Backup Database
```bash
mysqldump -u root -p rs_sehat_selalu > backup_before_upgrade.sql
```

### Langkah 2: Jalankan Upgrade Database
1. Buka browser dan kunjungi:
   ```
   http://localhost/rs_sehat_selalu/run_security_upgrade.php?key=security_upgrade_2024
   ```

2. Script akan:
   - Membuat tabel keamanan baru
   - Upgrade password existing users
   - Generate encryption key
   - Setup stored procedures

### Langkah 3: Update Konfigurasi
1. Edit `koneksi.php`:
   - Update `ENCRYPTION_KEY` dengan key yang dihasilkan
   - Uncomment SSL options jika menggunakan SSL database

2. Edit `config.php`:
   - Set `DEBUG_MODE` ke `false` untuk production
   - Sesuaikan `SESSION_TIMEOUT` sesuai kebutuhan

### Langkah 4: Update Form Login
Tambahkan field 2FA ke form login (`index.html`):
```html
<div class="form-group">
    <label for="2fa_code">Kode 2FA (jika aktif):</label>
    <input type="text" id="2fa_code" name="2fa_code" maxlength="6" placeholder="123456">
</div>
<input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
```

### Langkah 5: Setup HTTPS
1. Install SSL certificate
2. Update Apache/Nginx configuration
3. Force HTTPS redirect

### Langkah 6: Testing
1. Test login dengan password lama (akan di-upgrade otomatis)
2. Test rate limiting dengan multiple failed attempts
3. Test 2FA setup dan login
4. Test HTTPS enforcement

## File-File Baru yang Ditambahkan

1. **security.php** - Class SecurityManager dengan semua fungsi keamanan
2. **security_upgrade.sql** - Database schema upgrade
3. **run_security_upgrade.php** - Script untuk menjalankan upgrade
4. **setup_2fa.php** - Halaman setup Two-Factor Authentication
5. **two_factor_input.php** - Halaman input 2FA saat login

## File-File yang Dimodifikasi

1. **koneksi.php** - Ditambahkan fungsi enkripsi dan security manager
2. **config.php** - Sudah ada, ditambahkan konstanta keamanan
3. **proses_login.php** - Upgrade dengan rate limiting dan 2FA
4. **proses_login_dokter.php** - Upgrade dengan fitur keamanan
5. **proses_pendaftaran.php** - Upgrade dengan password hashing

## Tabel Database Baru

1. **login_attempts** - Tracking percobaan login
2. **security_logs** - Log event keamanan
3. **backup_codes** - Backup codes untuk 2FA
4. **user_sessions** - Management session
5. **encrypted_data** - Data terenkripsi
6. **audit_logs** - Audit trail perubahan data
7. **password_reset_tokens** - Token reset password
8. **encryption_keys** - Management encryption keys

## Perubahan pada Tabel Existing

### Tabel `users`:
- `two_factor_secret` - Secret untuk 2FA
- `two_factor_enabled` - Status 2FA
- `last_login` - Timestamp login terakhir
- `login_attempts` - Jumlah percobaan login
- `account_locked` - Status akun terkunci
- `password_changed_at` - Timestamp perubahan password

### Tabel `dokter`:
- Perubahan yang sama seperti tabel users

## Konfigurasi Keamanan

### Session Security
- `session.cookie_httponly = 1`
- `session.cookie_secure = 1`
- `session.use_strict_mode = 1`
- Session regeneration berkala

### Input Validation
- Sanitasi input dengan htmlspecialchars
- Validasi format username, password, email
- CSRF token protection

### Rate Limiting
- 5 percobaan login per 15 menit
- Lockout otomatis akun
- Pembersihan data lama otomatis

## Monitoring dan Logging

### Security Logs
- Login attempts (success/failed)
- 2FA events
- Account lockouts
- Password changes
- CSRF violations

### Audit Logs
- Data changes (INSERT/UPDATE/DELETE)
- User activities
- System events

## Maintenance

### Scheduled Tasks
1. **Cleanup login attempts** - Setiap 1 jam
2. **Cleanup expired sessions** - Setiap 30 menit
3. **Security log rotation** - Harian

### Manual Tasks
1. Review security logs mingguan
2. Update encryption keys bulanan
3. Audit user access quarterly

## Troubleshooting

### Common Issues

1. **SSL Certificate Error**
   - Ensure valid SSL certificate installed
   - Check certificate chain

2. **Database Connection Error**
   - Verify database credentials
   - Check MySQL service status

3. **2FA Not Working**
   - Verify server time synchronization
   - Check secret key generation

4. **Rate Limiting Too Strict**
   - Adjust limits in SecurityManager
   - Clear login_attempts table if needed

## Security Best Practices

1. **Regular Updates**
   - Keep PHP updated
   - Update dependencies
   - Monitor security advisories

2. **Backup Strategy**
   - Regular database backups
   - Test restore procedures
   - Secure backup storage

3. **Access Control**
   - Principle of least privilege
   - Regular access reviews
   - Strong password policies

4. **Monitoring**
   - Log analysis
   - Intrusion detection
   - Performance monitoring

## Post-Upgrade Checklist

- [ ] Database upgrade completed successfully
- [ ] Encryption key updated in config
- [ ] HTTPS configured and working
- [ ] Login functionality tested
- [ ] 2FA setup and tested
- [ ] Rate limiting verified
- [ ] Security logs working
- [ ] All users notified about new features
- [ ] Temporary passwords changed
- [ ] Backup verified
- [ ] Documentation updated
- [ ] Upgrade script deleted

## Support

Jika mengalami masalah:
1. Check error logs di `logs/` directory
2. Review security logs di database
3. Verify configuration settings
4. Test with debug mode enabled

---

**PENTING:** Setelah upgrade berhasil, hapus file `run_security_upgrade.php` untuk keamanan.

**Password Sementara:** Semua user existing akan memiliki password sementara `TempPassword123!` yang harus diganti setelah login pertama.
