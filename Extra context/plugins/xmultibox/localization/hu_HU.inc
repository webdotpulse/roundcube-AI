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
$labels['app_menu_title'] = 'Egyedi levelező szerverek';
$labels['custom_email_account'] = 'Egyéni e-mail fiók';
$labels['use_custom_mail_servers'] = 'Egyéni levelezőszerverek használata';
$labels['incoming_server'] = 'Bejövő kiszolgáló (IMAP)';
$labels['outgoing_server'] = 'Kimenő kiszolgáló (SMTP)';

$labels['server'] = 'Szerver';
$labels['port'] = 'Port';
$labels['connection_security'] = 'Kapcsolat biztonsága';
$labels['authentication'] = 'Hitelesítés';
$labels['test_connection'] = 'Tesztkapcsolat';
$labels['use_imap_user_pass'] = 'IMAP felhasználónév és jelszó használata';
$labels['specify_user_pass'] = 'Felhasználónév és jelszó megadása';

$labels['validation_required'] = '$f szükséges.';
$labels['validation_maxlen'] = '$f rövidebbnek kell lennie, mint $n karakter.';
$labels['validation_port'] = '$f egész számnak kell lennie az 1-65535 tartományban.';

$labels['login_successful'] = 'Bejelentkezés sikeres.';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'Nem tud bejelentkezni az IMAP-kiszolgálóra.';
$labels['cannot_login_to_smtp_server'] = 'Nem tud bejelentkezni az SMTP-kiszolgálóra.';
$labels['cannot_save_multibox_data'] = 'A multibox adatok mentése nem lehetséges.';
$labels['settings_not_saved'] = 'A beállításokat nem mentették el.';
