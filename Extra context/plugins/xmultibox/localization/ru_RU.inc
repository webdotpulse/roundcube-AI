<?php
/**
 * Roundcube Plus Multibox plugin.
 *
 * Copyright 2023, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

$labels = [];
$labels['plugin_name'] = 'Мультибокс';
$labels['app_menu_title'] = 'Пользовательские почтовые серверы';
$labels['custom_email_account'] = 'Пользовательская учетная запись электронной почты';
$labels['use_custom_mail_servers'] = 'Используйте пользовательские почтовые серверы';
$labels['incoming_server'] = 'Сервер входящих сообщений (IMAP)';
$labels['outgoing_server'] = 'Исходящий сервер (SMTP)';

$labels['server'] = 'Сервер';
$labels['port'] = 'Порт';
$labels['connection_security'] = 'Безопасность подключения';
$labels['authentication'] = 'Аутентификация';
$labels['test_connection'] = 'Тестовое соединение';
$labels['use_imap_user_pass'] = 'Используйте имя пользователя и пароль IMAP';
$labels['specify_user_pass'] = 'Укажите имя пользователя и пароль';

$labels['validation_required'] = '$f: обязательное поле.';
$labels['validation_maxlen'] = '$f: должен быть короче, чем $n символов.';
$labels['validation_port'] = '$f должно быть целым числом в диапазоне 1-65535.';

$labels['login_successful'] = 'Вход в систему успешный.';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'Невозможно войти в систему на сервере IMAP.';
$labels['cannot_login_to_smtp_server'] = 'Невозможно войти в систему на SMTP-сервере.';
$labels['cannot_save_multibox_data'] = 'Невозможно сохранить данные мультибокса.';
$labels['settings_not_saved'] = 'Настройки не сохраняются.';
