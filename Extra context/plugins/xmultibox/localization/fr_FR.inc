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
$labels['app_menu_title'] = 'Serveurs de messagerie personnalisés';
$labels['custom_email_account'] = 'Compte e-mail personnalisé';
$labels['use_custom_mail_servers'] = 'Utiliser des serveurs de messagerie personnalisés';
$labels['incoming_server'] = 'Serveur entrant (IMAP)';
$labels['outgoing_server'] = 'Serveur sortant (SMTP)';

$labels['server'] = 'Serveur';
$labels['port'] = 'Port';
$labels['connection_security'] = 'Sécurité de la connexion';
$labels['authentication'] = 'Authentification';
$labels['test_connection'] = 'Test de connexion';
$labels['use_imap_user_pass'] = 'Utiliser le nom d\'utilisateur et le mot de passe IMAP';
$labels['specify_user_pass'] = 'Spécifier le nom d\'utilisateur et le mot de passe';

$labels['validation_required'] = '$f : champ obligatoire.';
$labels['validation_maxlen'] = '$f : doit être plus court que $n caractères.';
$labels['validation_port'] = '$f doit être un nombre entier compris entre 1 et 65535.';

$labels['login_successful'] = 'Connexion réussie.';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'Impossible de se connecter au serveur IMAP.';
$labels['cannot_login_to_smtp_server'] = 'Impossible de se connecter au serveur SMTP.';
$labels['cannot_save_multibox_data'] = 'Impossible d\'enregistrer les données multibox.';
$labels['settings_not_saved'] = 'Les paramètres ne sont pas sauvegardés.';
