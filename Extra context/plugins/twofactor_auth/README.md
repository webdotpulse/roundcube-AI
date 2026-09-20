# Two-Factor Authentication Plugin for Roundcube Webmail

Enterprise-grade Two-Factor Authentication (2FA) plugin for Roundcube Webmail supporting RFC 6238 TOTP, SMS OTP, Email OTP, and single-use emergency recovery codes.

## Features
- **Mobile App Authentication (TOTP)**: Fully compatible with Google Authenticator, Microsoft Authenticator, Authy, FreeOTP, Bitwarden, and 3rd party TOTP apps.
- **Native SVG QR Code Generation**: Zero external dependencies and no external API calls to avoid leaking secret keys to third parties.
- **Email One-Time Passcodes (OTP)**: Automatic verification code delivery to secondary or primary email.
- **SMS One-Time Passcodes (OTP)**: Webhook and shell gateway support for cellular SMS delivery.
- **Emergency Recovery Codes**: Generates 10 single-use recovery codes, stored using cryptographically secure hashing.
- **Administrative Enforcement**: Option to require all users to configure and complete 2FA upon login.
- **Global Disable Switch**: Option to temporarily bypass 2FA checks system-wide.
- **Brute-Force Rate Limiting**: Exponential lockout on invalid attempts to protect against credential stuffing and brute-force guessing.

## Installation
Add `twofactor_auth` to the `$config['plugins']` array in your Roundcube `config/config.inc.php`:
```php
$config['plugins'] = [
    // ...
    'twofactor_auth',
];
```
Copy `plugins/twofactor_auth/config.inc.php.dist` to `plugins/twofactor_auth/config.inc.php` and configure as needed.
