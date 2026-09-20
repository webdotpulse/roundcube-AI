<?php
/**
 * Roundcube Plus Multibox plugin.
 *
 * Copyright 2023, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

$labels = [];
$labels['plugin_name'] = 'マルチボックス';
$labels['app_menu_title'] = 'カスタム・メール・サーバー';
$labels['custom_email_account'] = 'カスタムEメールアカウント';
$labels['use_custom_mail_servers'] = 'カスタムメールサーバーの使用';
$labels['incoming_server'] = '受信サーバー（IMAP）';
$labels['outgoing_server'] = '送信サーバー (SMTP)';

$labels['server'] = 'サーバー';
$labels['port'] = 'ポート';
$labels['connection_security'] = 'コネクション・セキュリティ';
$labels['authentication'] = '認証';
$labels['test_connection'] = 'テスト接続';
$labels['use_imap_user_pass'] = 'IMAPのユーザー名とパスワードを使用する';
$labels['specify_user_pass'] = 'ユーザー名とパスワードを指定する';

$labels['validation_required'] = 'fは必須。';
$labels['validation_maxlen'] = 'fは$n文字より短くなければならない。';
$labels['validation_port'] = 'fは1-65535の範囲の整数でなければならない。';

$labels['login_successful'] = 'ログインに成功しました。';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'IMAPサーバーにログインできません。';
$labels['cannot_login_to_smtp_server'] = 'SMTPサーバーにログインできない。';
$labels['cannot_save_multibox_data'] = 'マルチボックスのデータを保存できない。';
$labels['settings_not_saved'] = '設定が保存されない。';
