<?php
/**
 * Roundcube Plus Multibox plugin.
 *
 * Copyright 2023, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

$labels = [];
$labels['plugin_name'] = 'Multibox';
$labels['app_menu_title'] = 'Özel Posta Sunucuları';
$labels['custom_email_account'] = 'Özel E-posta Hesabı';
$labels['use_custom_mail_servers'] = 'Özel posta sunucuları kullanma';
$labels['incoming_server'] = 'Gelen Sunucu (IMAP)';
$labels['outgoing_server'] = 'Giden Sunucu (SMTP)';

$labels['server'] = 'Sunucu';
$labels['port'] = 'Liman';
$labels['connection_security'] = 'Bağlantı güvenliği';
$labels['authentication'] = 'Kimlik Doğrulama';
$labels['test_connection'] = 'Test bağlantısı';
$labels['use_imap_user_pass'] = 'IMAP kullanıcı adı ve parolasını kullanma';
$labels['specify_user_pass'] = 'Kullanıcı adı ve şifre belirleme';

$labels['validation_required'] = '$f: gerekli alan.';
$labels['validation_maxlen'] = 'f: $n karakterden daha kısa olmalıdır.';
$labels['validation_port'] = 'f 1-65535 aralığında bir tamsayı olmalıdır.';

$labels['login_successful'] = 'Giriş başarılı.';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'IMAP sunucusunda oturum açılamıyor.';
$labels['cannot_login_to_smtp_server'] = 'SMTP sunucusunda oturum açılamıyor.';
$labels['cannot_save_multibox_data'] = 'Çoklu kutu verileri kaydedilemiyor.';
$labels['settings_not_saved'] = 'Ayarlar kaydedilmedi.';
