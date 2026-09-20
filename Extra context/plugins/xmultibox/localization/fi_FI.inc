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
$labels['app_menu_title'] = 'Mukautetut sähköpostipalvelimet';
$labels['custom_email_account'] = 'Mukautettu sähköpostitili';
$labels['use_custom_mail_servers'] = 'Käytä mukautettuja sähköpostipalvelimia';
$labels['incoming_server'] = 'Saapuva palvelin (IMAP)';
$labels['outgoing_server'] = 'Lähtevä palvelin (SMTP)';

$labels['server'] = 'Palvelin';
$labels['port'] = 'Satama';
$labels['connection_security'] = 'Yhteyden turvallisuus';
$labels['authentication'] = 'Tunnistus';
$labels['test_connection'] = 'Testiliitäntä';
$labels['use_imap_user_pass'] = 'Käytä IMAP-käyttäjätunnusta ja salasanaa';
$labels['specify_user_pass'] = 'Määritä käyttäjätunnus ja salasana';

$labels['validation_required'] = '$f: pakollinen kenttä.';
$labels['validation_maxlen'] = '$f: on oltava lyhyempi kuin $n merkkiä.';
$labels['validation_port'] = '$f:n on oltava kokonaisluku alueella 1-65535.';

$labels['login_successful'] = 'Sisäänkirjautuminen onnistui.';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'IMAP-palvelimeen ei voi kirjautua.';
$labels['cannot_login_to_smtp_server'] = 'SMTP-palvelimeen ei voi kirjautua.';
$labels['cannot_save_multibox_data'] = 'Monilaatikkotietoja ei voida tallentaa.';
$labels['settings_not_saved'] = 'Asetuksia ei tallenneta.';
