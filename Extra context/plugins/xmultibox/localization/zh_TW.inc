<?php
/**
 * Roundcube Plus Multibox plugin.
 *
 * Copyright 2023, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

$labels = [];
$labels['plugin_name'] = '多重盒';
$labels['app_menu_title'] = '自訂郵件伺服器';
$labels['custom_email_account'] = '自訂電子郵件帳號';
$labels['use_custom_mail_servers'] = '使用自訂郵件伺服器';
$labels['incoming_server'] = '傳入伺服器 (IMAP)';
$labels['outgoing_server'] = '外寄伺服器 (SMTP)';

$labels['server'] = '伺服器';
$labels['port'] = '港口';
$labels['connection_security'] = '連接安全性';
$labels['authentication'] = '驗證';
$labels['test_connection'] = '測試連接';
$labels['use_imap_user_pass'] = '使用 IMAP 使用者名稱和密碼';
$labels['specify_user_pass'] = '指定使用者名稱和密碼';

$labels['validation_required'] = '需要 $f。';
$labels['validation_maxlen'] = '$f 必須短於 $n 個字元。';
$labels['validation_port'] = '$f 必須是 1-65535 範圍內的整數。';

$labels['login_successful'] = '登入成功。';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = '無法登入 IMAP 伺服器。';
$labels['cannot_login_to_smtp_server'] = '無法登入 SMTP 伺服器。';
$labels['cannot_save_multibox_data'] = '無法儲存多重方塊資料。';
$labels['settings_not_saved'] = '設定未儲存。';
