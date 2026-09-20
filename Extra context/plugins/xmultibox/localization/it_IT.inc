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
$labels['app_menu_title'] = 'Server di posta personalizzati';
$labels['custom_email_account'] = 'Account e-mail personalizzato';
$labels['use_custom_mail_servers'] = 'Utilizzare server di posta personalizzati';
$labels['incoming_server'] = 'Server in entrata (IMAP)';
$labels['outgoing_server'] = 'Server in uscita (SMTP)';

$labels['server'] = 'Server';
$labels['port'] = 'Porto';
$labels['connection_security'] = 'Sicurezza della connessione';
$labels['authentication'] = 'Autenticazione';
$labels['test_connection'] = 'Collegamento di prova';
$labels['use_imap_user_pass'] = 'Utilizzare nome utente e password IMAP';
$labels['specify_user_pass'] = 'Specificare nome utente e password';

$labels['validation_required'] = '$f: campo obbligatorio.';
$labels['validation_maxlen'] = '$f: deve essere più breve di $n caratteri.';
$labels['validation_port'] = '$f deve essere un numero intero compreso nell\'intervallo 1-65535.';

$labels['login_successful'] = 'Accesso riuscito.';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'Impossibile accedere al server IMAP.';
$labels['cannot_login_to_smtp_server'] = 'Impossibile accedere al server SMTP.';
$labels['cannot_save_multibox_data'] = 'Impossibile salvare i dati multibox.';
$labels['settings_not_saved'] = 'Le impostazioni non sono state salvate.';
