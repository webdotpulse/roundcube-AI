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
$labels['app_menu_title'] = 'Servidores de correio personalizados';
$labels['custom_email_account'] = 'Conta de e-mail personalizada';
$labels['use_custom_mail_servers'] = 'Usar servidores de correio personalizados';
$labels['incoming_server'] = 'Servidor de entrada (IMAP)';
$labels['outgoing_server'] = 'Servidor de Saída (SMTP)';

$labels['server'] = 'Servidor';
$labels['port'] = 'Porto';
$labels['connection_security'] = 'Segurança na conexão';
$labels['authentication'] = 'Autenticação';
$labels['test_connection'] = 'Conexão de teste';
$labels['use_imap_user_pass'] = 'Use o nome de usuário e a senha IMAP';
$labels['specify_user_pass'] = 'Especificar nome de usuário e senha';

$labels['validation_required'] = '$f: campo obrigatório.';
$labels['validation_maxlen'] = '$f: deve ser menor do que $n caracteres.';
$labels['validation_port'] = '$f deve ser um número inteiro no intervalo 1-65535.';

$labels['login_successful'] = 'Login com sucesso.';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'Não é possível fazer login no servidor IMAP.';
$labels['cannot_login_to_smtp_server'] = 'Não é possível fazer login no servidor SMTP.';
$labels['cannot_save_multibox_data'] = 'Não é possível salvar dados de várias caixas.';
$labels['settings_not_saved'] = 'Configurações não salvas.';
