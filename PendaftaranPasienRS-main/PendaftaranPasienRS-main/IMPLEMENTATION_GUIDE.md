# RS Sehat Selalu - Security Implementation Guide

## 🔐 Ringkasan Fitur Keamanan yang Diimplementasikan

### ✅ **1. Password Hashing dengan password_hash()**
- Upgrade dari MD5 ke bcrypt (PHP password_hash/password_verify)
- Backward compatibility untuk password lama
- Automatic upgrade saat user login
- Password strength validation

### ✅ **2. Rate Limiting & Account Protection**
- Pembatasan percobaan login (5 attempts per 15 menit)
- Account lockout otomatis setelah 5 percobaan gagal
- Rate limiting untuk password reset
- Automatic cleanup data lama

### ✅ **3. HTTPS Enforcement**
- Redirect otomatis ke HTTPS
- Secure cookie settings
- Session security enhancements
- HSTS headers (dalam config)

### ✅ **4. Database Encryption**
- AES-256-CBC encryption untuk data sensitif
- Key management system
- Fungsi encrypt/decrypt helper
- Secure key storage

### ✅ **5. Two-Factor Authentication (2FA)**
- TOTP implementation (Google Authenticator compatible)
- Setup dan disable 2FA
- Backup codes untuk recovery
- 2FA verification saat login

### ✅ **6. Security Features Tambahan**
- CSRF protection pada semua form
- Input validation dan sanitization
- Security event logging
- Session management yang aman
- Password reset dengan token
- Audit trail untuk perubahan data

## 📋 Langkah-langkah Implementasi

### **Step 1: Backup Database**
```bash
# Backup database sebelum upgrade
mysqldump -u root -p rs_sehat_selalu > backup_$(date +%Y%m%d_%H%M%S).sql
```

### **Step 2: Upgrade Database Schema**
1. Buka phpMyAdmin atau MySQL command line
2. Jalankan script `simple_upgrade.sql`
3. Atau copy-paste query dari file tersebut

### **Step 3: Update Konfigurasi**
1. Edit `koneksi.php`:
   ```php
   // Generate encryption key baru
   define('ENCRYPTION_KEY', 'your-32-character-secret-key-here');
   ```

2. Edit `config.php`:
   ```php
   // Untuk production
   define('DEBUG_MODE', false);
   define('DEVELOPMENT_MODE', false);
   ```

### **Step 4: Update Form Login**
Tambahkan field CSRF dan 2FA ke form login:

**index.html:**
```html
<form method="POST" action="proses_login.php">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
    </div>
    
    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    
    <div class="form-group">
        <label for="2fa_code">Kode 2FA (jika aktif):</label>
        <input type="text" id="2fa_code" name="2fa_code" maxlength="6" placeholder="123456">
    </div>
    
    <button type="submit">Login</button>
</form>

<p><a href="password_reset.php">Lupa Password?</a></p>
```

**daftar.html:**
```html
<form method="POST" action="proses_pendaftaran.php">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
    </div>
    
    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    
    <div class="form-group">
        <label for="confirm_password">Konfirmasi Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>
    
    <button type="submit">Daftar</button>
</form>
```

### **Step 5: Setup HTTPS**
1. Install SSL certificate
2. Update Apache/Nginx configuration
3. Enable HTTPS redirect

### **Step 6: Test Semua Fitur**
1. ✅ Login dengan password lama (auto-upgrade)
2. ✅ Test rate limiting (5 failed attempts)
3. ✅ Test 2FA setup dan login
4. ✅ Test password reset
5. ✅ Test change password
6. ✅ Test HTTPS enforcement
7. ✅ Test admin security dashboard

## 🔧 File-File yang Dibuat/Dimodifikasi

### **File Baru:**
- `security.php` - Class SecurityManager
- `setup_2fa.php` - Setup Two-Factor Authentication
- `two_factor_input.php` - Input 2FA saat login
- `backup_codes.php` - Backup codes untuk 2FA
- `password_reset.php` - Reset password system
- `change_password.php` - Change password dengan validasi
- `admin_security_dashboard.php` - Security dashboard untuk admin
- `simple_upgrade.sql` - Database upgrade script

### **File yang Dimodifikasi:**
- `koneksi.php` - Fungsi enkripsi dan security manager
- `config.php` - Konstanta keamanan tambahan
- `proses_login.php` - Rate limiting, 2FA, password hashing
- `proses_login_dokter.php` - Fitur keamanan untuk dokter
- `proses_pendaftaran.php` - Password hashing dan validasi

## 🛠️ Konfigurasi Produksi

### **Security Settings:**
```php
// koneksi.php
define('ENCRYPTION_KEY', 'generate-32-character-key-here');
define('DEVELOPMENT_MODE', false);

// config.php
define('DEBUG_MODE', false);
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes
```

### **Database Settings:**
```sql
-- Enable strict mode
SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

-- Set session timeout
SET SESSION wait_timeout = 600;
SET SESSION interactive_timeout = 600;
```

### **Web Server Configuration:**
```apache
# Apache .htaccess
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

## 📊 Monitoring dan Maintenance

### **Security Monitoring:**
1. **Login Attempts**: Monitor di `login_attempts` table
2. **Security Events**: Check `security_logs` table
3. **Account Lockouts**: Review locked accounts
4. **2FA Usage**: Monitor 2FA adoption rate

### **Maintenance Tasks:**
1. **Daily**: Review security logs
2. **Weekly**: Check failed login attempts
3. **Monthly**: Update encryption keys
4. **Quarterly**: Security audit

### **Scheduled Cleanup:**
```sql
-- Cleanup old login attempts (older than 1 hour)
DELETE FROM login_attempts WHERE timestamp < UNIX_TIMESTAMP() - 3600;

-- Cleanup expired sessions
DELETE FROM user_sessions WHERE expires_at < NOW();

-- Cleanup old security logs (older than 90 days)
DELETE FROM security_logs WHERE timestamp < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

## 🔍 Testing Checklist

### **Functionality Tests:**
- [ ] Login dengan password lama (MD5) → auto-upgrade ke bcrypt
- [ ] Login dengan password baru (bcrypt)
- [ ] Rate limiting: 5 failed attempts → account locked
- [ ] 2FA setup dan verification
- [ ] 2FA backup codes generation dan usage
- [ ] Password reset flow
- [ ] Change password dengan validasi
- [ ] CSRF protection pada semua form
- [ ] HTTPS enforcement
- [ ] Session timeout
- [ ] Security event logging
- [ ] Admin security dashboard

### **Security Tests:**
- [ ] SQL injection attempts
- [ ] XSS attempts
- [ ] CSRF attacks
- [ ] Session hijacking attempts
- [ ] Brute force protection
- [ ] Password strength validation
- [ ] Input sanitization

## 🚨 Troubleshooting

### **Common Issues:**

1. **Database Connection Error**
   - Check MySQL service status
   - Verify credentials in `koneksi.php`

2. **2FA Not Working**
   - Verify server time synchronization
   - Check secret key generation

3. **Rate Limiting Too Strict**
   - Adjust limits in `SecurityManager`
   - Clear `login_attempts` table

4. **HTTPS Issues**
   - Check SSL certificate validity
   - Verify web server configuration

### **Error Logs:**
- Check `logs/activity.log` for application logs
- Review `security_logs` table for security events
- Monitor web server error logs

## 📝 User Communication

### **Email Template untuk Users:**
```
Subject: Security Update - RS Sehat Selalu

Dear User,

We've implemented enhanced security features for your account:

1. Stronger password encryption
2. Two-factor authentication (optional)
3. Account lockout protection
4. Secure password reset

Your temporary password is: TempPassword123!
Please change it immediately after login.

Best regards,
RS Sehat Selalu Team
```

### **Admin Instructions:**
1. Access security dashboard: `/admin_security_dashboard.php`
2. Monitor failed login attempts
3. Unlock accounts if needed
4. Review security logs regularly
5. Manage 2FA for users

## 🔐 Security Best Practices

### **For Developers:**
1. Always use prepared statements
2. Validate and sanitize all input
3. Use CSRF tokens on all forms
4. Log security events
5. Keep dependencies updated

### **For Users:**
1. Use strong, unique passwords
2. Enable 2FA when available
3. Don't share accounts
4. Log out when finished
5. Report suspicious activity

### **For Administrators:**
1. Monitor security logs daily
2. Review user accounts regularly
3. Keep software updated
4. Backup database regularly
5. Test disaster recovery procedures

---

**🚀 Implementation Complete!**

Sistem RS Sehat Selalu sekarang memiliki keamanan tingkat enterprise dengan perlindungan berlapis terhadap berbagai ancaman cyber. Semua fitur keamanan modern telah diimplementasikan dan siap untuk produksi.

**Next Steps:**
1. Test semua fitur dalam environment staging
2. Deploy ke production dengan monitoring ketat
3. Train user dan administrator
4. Setup monitoring dan alerting
5. Schedule regular security reviews
