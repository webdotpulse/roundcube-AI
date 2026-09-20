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
$labels['app_menu_title'] = 'Benutzerdefinierte Mailserver';
$labels['custom_email_account'] = 'Benutzerdefiniertes E-Mail-Konto';
$labels['use_custom_mail_servers'] = 'Benutzerdefinierte Mailserver verwenden';
$labels['incoming_server'] = 'Eingangsserver (IMAP)';
$labels['outgoing_server'] = 'Ausgehender Server (SMTP)';

$labels['server'] = 'Server';
$labels['port'] = 'Hafen';
$labels['connection_security'] = 'Sicherheit der Verbindung';
$labels['authentication'] = 'Authentifizierung';
$labels['test_connection'] = 'Verbindung testen';
$labels['use_imap_user_pass'] = 'IMAP-Benutzername und -Passwort verwenden';
$labels['specify_user_pass'] = 'Benutzernamen und Passwort angeben';

$labels['validation_required'] = '$f: Pflichtfeld.';
$labels['validation_maxlen'] = '$f: muss kürzer als $n Zeichen sein.';
$labels['validation_port'] = '$f muss eine ganze Zahl im Bereich von 1-65535 sein.';

$labels['login_successful'] = 'Anmeldung erfolgreich.';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'Anmeldung am IMAP-Server nicht möglich.';
$labels['cannot_login_to_smtp_server'] = 'Kann sich nicht am SMTP-Server anmelden.';
$labels['cannot_save_multibox_data'] = 'Multibox-Daten können nicht gespeichert werden.';
$labels['settings_not_saved'] = 'Einstellungen nicht gespeichert.';
