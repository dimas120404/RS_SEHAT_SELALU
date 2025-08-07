# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Nothing yet

### Changed
- Nothing yet

### Fixed
- Nothing yet

## [2.0.0] - 2025-01-14

### 🔒 Security Major Release

#### Added
- **Enterprise-grade security features**
- **Two-Factor Authentication (2FA)** with TOTP support
- **Advanced password hashing** using bcrypt with automatic MD5 upgrade
- **Rate limiting** for login attempts and password resets
- **HTTPS enforcement** with secure cookie settings
- **Database encryption** using AES-256-CBC for sensitive data
- **Security monitoring dashboard** for administrators
- **Backup codes system** for 2FA recovery
- **Password reset system** with secure tokens
- **Comprehensive audit logging** for all security events
- **CSRF protection** for all forms
- **Input validation and sanitization** throughout the application
- **Session management** with timeout and security controls
- **Account lockout** after failed login attempts

#### Security Enhancements
- **SQL injection protection** with prepared statements
- **XSS prevention** with proper output encoding
- **Session hijacking protection** with secure session handling
- **Brute force protection** with intelligent rate limiting
- **Password policy enforcement** with complexity requirements
- **Secure key management** for encryption keys

#### New Files
- `security.php` - Security manager class with all security functions
- `setup_2fa.php` - Two-Factor Authentication setup interface
- `two_factor_input.php` - 2FA verification during login
- `backup_codes.php` - 2FA backup codes management
- `password_reset.php` - Secure password reset system
- `change_password.php` - Password change with validation
- `admin_security_dashboard.php` - Security monitoring dashboard
- `simple_upgrade.sql` - Database security upgrade script

#### Database Changes
- Added security columns to `users` and `dokter` tables
- Created `login_attempts` table for rate limiting
- Created `security_logs` table for audit trail
- Created `backup_codes` table for 2FA recovery
- Created `user_sessions` table for session management
- Created `encrypted_data` table for sensitive information
- Created `audit_logs` table for data change tracking
- Created `password_reset_tokens` table for secure recovery
- Created `encryption_keys` table for key management

#### Updated Files
- `koneksi.php` - Enhanced with encryption functions and security manager
- `proses_login.php` - Added 2FA, rate limiting, and password hashing
- `proses_login_dokter.php` - Enhanced with security features
- `proses_pendaftaran.php` - Added password hashing and validation
- `config.php` - Enhanced security configuration options

### Changed
- **Password storage** upgraded from MD5 to bcrypt hashing
- **Session handling** improved with secure timeout management
- **Error handling** enhanced to prevent information disclosure
- **User interface** updated with modern security indicators
- **Database queries** optimized with proper indexing

### Security Fixes
- Fixed potential SQL injection vulnerabilities
- Resolved session fixation issues
- Improved CSRF token implementation
- Enhanced input validation across all forms
- Strengthened password policies

## [1.0.0] - 2024-12-01

### Initial Release

#### Added
- **Multi-role user system** (Admin, Doctor, Patient)
- **Patient registration** and management
- **Doctor management** with scheduling
- **Appointment booking** system
- **Medical records** management
- **Payment processing** integration
- **Inpatient management** system
- **Admin dashboard** with comprehensive controls
- **Doctor dashboard** for patient management
- **Patient dashboard** with booking capabilities

#### Core Features
- User authentication system
- Role-based access control
- Database integration with MySQL
- Responsive web design
- Basic security measures

#### Database Tables
- `users` - User accounts (admin & patients)
- `dokter` - Doctor profiles and information
- `booking` - Appointment bookings
- `pemeriksaan` - Medical examination records
- `pembayaran` - Payment transactions
- `rawat_inap` - Inpatient management
- `dokter_jadwal` - Doctor schedules
- `riwayat` - Medical history records

#### User Interface
- Modern gradient design
- Glassmorphism effects
- Responsive layout
- Interactive forms
- 3D particle effects

---

## Version Numbering

We use [Semantic Versioning](https://semver.org/) for version numbers:

- **MAJOR** version when making incompatible API changes
- **MINOR** version when adding functionality in a backwards compatible manner
- **PATCH** version when making backwards compatible bug fixes

## Security Updates

Security updates are marked with 🔒 and are given high priority. All security-related changes are thoroughly tested and documented.

## Migration Guides

When upgrading between major versions, please refer to our [Implementation Guide](IMPLEMENTATION_GUIDE.md) for detailed migration instructions.

---

**Legend:**
- 🔒 Security update
- ⚡ Performance improvement
- 🐛 Bug fix
- ✨ New feature
- 💥 Breaking change
- 📝 Documentation update
