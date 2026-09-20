<?php

/**
 * Two-Factor Authentication TOTP Engine (RFC 6238 & RFC 4226)
 *
 * Standalone, zero-dependency Time-Based One-Time Password generator and verifier.
 * Compatible with Google Authenticator, Microsoft Authenticator, Authy, FreeOTP,
 * 1Password, Bitwarden, and any standard TOTP application.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace TwoFactorAuth;

class Totp
{
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a cryptographically secure random Base32 secret key.
     *
     * @param int $length Number of Base32 characters (16-32 recommended)
     * @return string
     */
    public static function generateSecret(int $length = 16): string
    {
        $secret = '';
        $maxIndex = strlen(self::BASE32_CHARS) - 1;

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_CHARS[random_int(0, $maxIndex)];
        }

        return $secret;
    }

    /**
     * Decode a Base32 encoded string into binary bytes.
     *
     * @param string $base32
     * @return string Binary representation
     */
    public static function base32Decode(string $base32): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z2-7]/i', '', $base32) ?? '');
        if ($clean === '') {
            return '';
        }

        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        for ($i = 0, $len = strlen($clean); $i < $len; $i++) {
            $val = strpos(self::BASE32_CHARS, $clean[$i]);
            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }

    /**
     * Generate a TOTP code for a given secret and timestamp.
     *
     * @param string $secret Base32 secret key
     * @param int|null $timestamp Unix timestamp (defaults to current time)
     * @param int $period Time step in seconds (default: 30)
     * @param int $digits Number of code digits (default: 6)
     * @return string
     */
    public static function generateCode(string $secret, ?int $timestamp = null, int $period = 30, int $digits = 6): string
    {
        $timestamp = $timestamp ?? time();
        $counter = (int)floor($timestamp / $period);

        // Pack counter into 8-byte big-endian binary string
        $binaryCounter = pack('N*', 0) . pack('N*', $counter);
        $binarySecret = self::base32Decode($secret);

        $hash = hash_hmac('sha1', $binaryCounter, $binarySecret, true);

        // Dynamic truncation (RFC 4226)
        $offset = ord($hash[19]) & 0x0F;
        $truncatedHash = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        $code = $truncatedHash % (10 ** $digits);
        return str_pad((string)$code, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a submitted TOTP code with time-drift tolerance.
     *
     * @param string $secret Base32 secret key
     * @param string $code 6-digit code submitted by user
     * @param int $drift Allowed time step window before/after (default: 1, allowing ±30 seconds)
     * @param int|null $timestamp Unix timestamp (defaults to current time)
     * @param int $period Time step in seconds (default: 30)
     * @param int $digits Number of digits (default: 6)
     * @return bool
     */
    public static function verifyCode(string $secret, string $code, int $drift = 1, ?int $timestamp = null, int $period = 30, int $digits = 6): bool
    {
        $code = trim($code);
        if (strlen($code) !== $digits || !ctype_digit($code)) {
            return false;
        }

        $timestamp = $timestamp ?? time();

        for ($step = -$drift; $step <= $drift; $step++) {
            $calcTime = $timestamp + ($step * $period);
            $expected = self::generateCode($secret, $calcTime, $period, $digits);

            if (hash_equals($expected, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build standard otpauth:// URI for authenticator app configuration.
     *
     * @param string $accountName User identifier (e.g. user@domain.com)
     * @param string $issuer Service issuer (e.g. Roundcube Webmail)
     * @param string $secret Base32 secret key
     * @return string
     */
    public static function getOtpAuthUri(string $accountName, string $issuer, string $secret): string
    {
        $label = rawurlencode($issuer) . ':' . rawurlencode($accountName);
        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            $label,
            rawurlencode($secret),
            rawurlencode($issuer)
        );
    }
}
