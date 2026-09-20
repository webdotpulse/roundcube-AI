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
$labels['app_menu_title'] = 'Vlastní poštovní servery';
$labels['custom_email_account'] = 'Vlastní e-mailový účet';
$labels['use_custom_mail_servers'] = 'Použití vlastních poštovních serverů';
$labels['incoming_server'] = 'Příchozí server (IMAP)';
$labels['outgoing_server'] = 'Odchozí server (SMTP)';

$labels['server'] = 'Server';
$labels['port'] = 'Přístav';
$labels['connection_security'] = 'Zabezpečení připojení';
$labels['authentication'] = 'Ověřování';
$labels['test_connection'] = 'Testovací připojení';
$labels['use_imap_user_pass'] = 'Použití uživatelského jména a hesla IMAP';
$labels['specify_user_pass'] = 'Zadejte uživatelské jméno a heslo';

$labels['validation_required'] = '$f: povinné pole.';
$labels['validation_maxlen'] = '$f: musí být kratší než $n znaků.';
$labels['validation_port'] = '$f musí být celé číslo vrozsahu 1-65535.';

$labels['login_successful'] = 'Přihlášení proběhlo úspěšně.';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'Nelze se přihlásit k serveru IMAP.';
$labels['cannot_login_to_smtp_server'] = 'Nelze se přihlásit k serveru SMTP.';
$labels['cannot_save_multibox_data'] = 'Nelze uložit data multiboxu.';
$labels['settings_not_saved'] = 'Nastavení není uloženo.';
