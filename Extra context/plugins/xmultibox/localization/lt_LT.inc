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
$labels['app_menu_title'] = 'Pasirinktiniai pašto serveriai';
$labels['custom_email_account'] = 'Pasirinktinė el. pašto paskyra';
$labels['use_custom_mail_servers'] = 'Naudokite pasirinktinius pašto serverius';
$labels['incoming_server'] = 'Įeinantis serveris (IMAP)';
$labels['outgoing_server'] = 'Išeinantis serveris (SMTP)';

$labels['server'] = 'Serveris';
$labels['port'] = 'Uostas';
$labels['connection_security'] = 'Ryšio saugumas';
$labels['authentication'] = 'Autentiškumo nustatymas';
$labels['test_connection'] = 'Bandomasis sujungimas';
$labels['use_imap_user_pass'] = 'Naudokite IMAP vartotojo vardą ir slaptažodį';
$labels['specify_user_pass'] = 'Nurodykite vartotojo vardą ir slaptažodį';

$labels['validation_required'] = '$f: privalomas laukas.';
$labels['validation_maxlen'] = '$f: turi būti trumpesnis nei $n simbolių.';
$labels['validation_port'] = '$f turi būti sveikasis skaičius iš intervalo 1-65535.';

$labels['login_successful'] = 'Prisijungimas sėkmingas.';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'Nepavyksta prisijungti prie IMAP serverio.';
$labels['cannot_login_to_smtp_server'] = 'Nepavyksta prisijungti prie SMTP serverio.';
$labels['cannot_save_multibox_data'] = 'Negalima išsaugoti kelių dėžučių duomenų.';
$labels['settings_not_saved'] = 'Nustatymai neišsaugoti.';
