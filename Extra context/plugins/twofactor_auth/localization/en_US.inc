<?php

/**
 * Localization strings for Two-Factor Authentication Plugin (English en_US)
 */

$labels = [];

$labels['twofactor_title'] = 'Two-Factor Authentication';
$labels['twofactor_desc'] = 'Enhance your webmail account security by requiring an additional verification code upon login.';
$labels['twofactor_status'] = '2FA Status';
$labels['twofactor_enabled'] = 'Enabled';
$labels['twofactor_disabled'] = 'Disabled';
$labels['twofactor_enforced'] = 'Enforced by Administrator';
$labels['twofactor_active_method'] = 'Primary Verification Method';
$labels['twofactor_method_totp'] = 'Mobile App (TOTP)';
$labels['twofactor_method_email'] = 'Email One-Time Passcode';
$labels['twofactor_method_sms'] = 'SMS One-Time Passcode';
$labels['twofactor_method_recovery'] = 'Emergency Recovery Code';

$labels['twofactor_setup'] = 'Set Up Two-Factor Authentication';
$labels['twofactor_reconfigure'] = 'Reconfigure 2FA';
$labels['twofactor_disable_btn'] = 'Disable 2FA';
$labels['twofactor_confirm_disable'] = 'Are you sure you want to disable Two-Factor Authentication? Your account will only be protected by your password.';

$labels['twofactor_step1_scan'] = '1. Scan this QR code with your authenticator app (Google Authenticator, Microsoft Authenticator, Authy, etc.):';
$labels['twofactor_step1_manual'] = 'Or enter this secret key manually into your app:';
$labels['twofactor_step2_verify'] = '2. Enter the 6-digit code displayed in your app to confirm setup:';
$labels['twofactor_activate_btn'] = 'Verify & Activate 2FA';

$labels['twofactor_email_input_label'] = 'Verification Email Address:';
$labels['twofactor_email_input_desc'] = 'One-time codes will be delivered to this email address.';
$labels['twofactor_sms_input_label'] = 'Mobile Phone Number:';
$labels['twofactor_sms_input_desc'] = 'Include international country code (e.g. +1 555 123 4567).';

$labels['twofactor_recovery_title'] = 'Emergency Recovery Codes';
$labels['twofactor_recovery_desc'] = 'Store these one-time recovery codes in a safe place. If you lose access to your phone or email, each code can be used once to log in.';
$labels['twofactor_recovery_remaining'] = 'Remaining recovery codes:';
$labels['twofactor_recovery_generate'] = 'Generate New Recovery Codes';
$labels['twofactor_recovery_copy'] = 'Copy All Codes';
$labels['twofactor_recovery_download'] = 'Download Codes (.txt)';
$labels['twofactor_recovery_copied'] = 'Recovery codes copied to clipboard.';

$labels['twofactor_check_title'] = 'Two-Factor Authentication Required';
$labels['twofactor_check_subtitle'] = 'Please enter your verification code to complete sign-in.';
$labels['twofactor_prompt_totp'] = 'Enter the 6-digit verification code from your Authenticator app:';
$labels['twofactor_prompt_email'] = 'Enter the one-time code sent to your email address:';
$labels['twofactor_prompt_sms'] = 'Enter the one-time code sent via SMS to your phone:';
$labels['twofactor_prompt_recovery'] = 'Enter one of your emergency recovery codes:';

$labels['twofactor_verify_btn'] = 'Verify & Log In';
$labels['twofactor_resend_otp'] = 'Send New Code';
$labels['twofactor_otp_sent'] = 'A verification code has been dispatched. Please check your inbox or phone.';
$labels['twofactor_use_other_method'] = 'Try another verification method';
$labels['twofactor_logout_btn'] = 'Cancel and Log Out';

$labels['twofactor_invalid_code'] = 'Invalid verification code. Please try again.';
$labels['twofactor_code_expired'] = 'Verification code has expired. Please request a new code.';
$labels['twofactor_rate_limited'] = 'Too many failed attempts. Please wait %seconds% seconds before trying again.';
$labels['twofactor_resend_wait'] = 'Please wait %seconds% seconds before requesting another code.';
$labels['twofactor_setup_success'] = 'Two-Factor Authentication has been successfully activated on your account!';
$labels['twofactor_disable_success'] = 'Two-Factor Authentication has been disabled.';
$labels['twofactor_recovery_success'] = 'New emergency recovery codes have been generated.';
$labels['twofactor_enforced_notice'] = 'Your administrator requires Two-Factor Authentication. Please complete setup to continue using webmail.';
