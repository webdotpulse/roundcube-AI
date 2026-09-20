<?php
/**
 * Roundcube Plus Multibox plugin.
 *
 * Copyright 2023, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

$labels = [];
$labels['plugin_name'] = '멀티박스';
$labels['app_menu_title'] = '사용자 지정 메일 서버';
$labels['custom_email_account'] = '사용자 지정 이메일 계정';
$labels['use_custom_mail_servers'] = '사용자 지정 메일 서버 사용';
$labels['incoming_server'] = '수신 서버(IMAP)';
$labels['outgoing_server'] = '발신 서버(SMTP)';

$labels['server'] = '서버';
$labels['port'] = '포트';
$labels['connection_security'] = '연결 보안';
$labels['authentication'] = '인증';
$labels['test_connection'] = '연결 테스트';
$labels['use_imap_user_pass'] = 'IMAP 사용자 이름 및 비밀번호 사용';
$labels['specify_user_pass'] = '사용자 이름 및 비밀번호 지정';

$labels['validation_required'] = 'f는 필수입니다.';
$labels['validation_maxlen'] = 'f는 $n 문자보다 짧아야 합니다.';
$labels['validation_port'] = 'f는 1-65535 범위의 정수여야 합니다.';

$labels['login_successful'] = '로그인에 성공했습니다.';
$labels['text_in_parentheses'] = '($s)';
$labels['cannot_login_to_imap_server'] = 'IMAP 서버에 로그인할 수 없습니다.';
$labels['cannot_login_to_smtp_server'] = 'SMTP 서버에 로그인할 수 없습니다.';
$labels['cannot_save_multibox_data'] = '멀티박스 데이터를 저장할 수 없습니다.';
$labels['settings_not_saved'] = '설정이 저장되지 않았습니다.';
