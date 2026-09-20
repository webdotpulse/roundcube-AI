<?php
/**
 * Roundcube Plus Multibox plugin.
 *
 * Copyright 2023, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

$labels = [];
$labels['plugin_name'] = '多箱';
$labels['app_menu_title'] = '定制邮件服务器';
$labels['custom_email_account'] = '自定义电子邮件帐户';
$labels['use_custom_mail_servers'] = '使用自定义邮件服务器';
$labels['incoming_server'] = '接收服务器（IMAP）';
$labels['outgoing_server'] = '发件服务器（SMTP）';

$labels['server'] = '服务器';
$labels['port'] = '港口';
$labels['connection_security'] = '连接安全';
$labels['authentication'] = '认证';
$labels['test_connection'] = '测试连接';
$labels['use_imap_user_pass'] = '使用 IMAP 用户名和密码';
$labels['specify_user_pass'] = '指定用户名和密码';

$labels['validation_required'] = '需要 $f。';
$labels['validation_maxlen'] = '$f 必须短于 $n 个字符。';
$labels['validation_port'] = '$f 必须是范围在 1-65535 之间的整数。';

$labels['login_successful'] = '登录成功。';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = '无法登录 IMAP 服务器。';
$labels['cannot_login_to_smtp_server'] = '无法登录 SMTP 服务器。';
$labels['cannot_save_multibox_data'] = '无法保存多箱数据。';
$labels['settings_not_saved'] = '未保存设置。';
