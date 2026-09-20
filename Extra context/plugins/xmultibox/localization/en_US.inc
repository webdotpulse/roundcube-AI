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
$labels['app_menu_title'] = 'Custom Mail Servers';
$labels['custom_email_account'] = 'Custom E-mail Account';
$labels['use_custom_mail_servers'] = 'Use custom mail servers';
$labels['incoming_server'] = 'Incoming Server (IMAP)';
$labels['outgoing_server'] = 'Outgoing Server (SMTP)';

$labels['server'] = 'Server';
$labels['port'] = 'Port';
$labels['connection_security'] = 'Connection security';
$labels['authentication'] = 'Authentication';
$labels['test_connection'] = 'Test connection';
$labels['use_imap_user_pass'] = 'Use IMAP username and password';
$labels['specify_user_pass'] = 'Specify username and password';

$labels['validation_required'] = '$f is required.'; // $f - field
$labels['validation_maxlen'] = '$f must be shorter than $n characters.';
$labels['validation_port'] = '$f must be an integer in the range 1-65535.';

$labels['login_successful'] = 'Login successful.';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'Cannot log in to IMAP server.';
$labels['cannot_login_to_smtp_server'] = 'Cannot log in to SMTP server.';
$labels['cannot_save_multibox_data'] = 'Cannot save multibox data.';
$labels['settings_not_saved'] = 'Settings not saved.';
