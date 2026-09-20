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
$labels['app_menu_title'] = 'Servidores de correo personalizados';
$labels['custom_email_account'] = 'Cuenta de correo electrónico personalizada';
$labels['use_custom_mail_servers'] = 'Utilizar servidores de correo personalizados';
$labels['incoming_server'] = 'Servidor de entrada (IMAP)';
$labels['outgoing_server'] = 'Servidor de salida (SMTP)';

$labels['server'] = 'Servidor';
$labels['port'] = 'Puerto';
$labels['connection_security'] = 'Seguridad de la conexión';
$labels['authentication'] = 'Autenticación';
$labels['test_connection'] = 'Conexión de prueba';
$labels['use_imap_user_pass'] = 'Utilizar nombre de usuario y contraseña IMAP';
$labels['specify_user_pass'] = 'Especifique el nombre de usuario y la contraseña';

$labels['validation_required'] = '$f: campo obligatorio.';
$labels['validation_maxlen'] = '$f: debe ser inferior a $n caracteres.';
$labels['validation_port'] = '$f debe ser un número entero en el rango 1-65535.';

$labels['login_successful'] = 'Inicio de sesión correcto.';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'No se puede iniciar sesión en el servidor IMAP.';
$labels['cannot_login_to_smtp_server'] = 'No se puede iniciar sesión en el servidor SMTP.';
$labels['cannot_save_multibox_data'] = 'No se pueden guardar los datos de multibox.';
$labels['settings_not_saved'] = 'Ajustes no guardados.';
