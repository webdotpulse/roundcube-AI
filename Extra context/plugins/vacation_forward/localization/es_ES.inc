<?php

$labels = [];
$labels['vacation_forward_title'] = 'Vacaciones y Reenvío';
$labels['vacation_title'] = 'Vacaciones / Respuesta automática';
$labels['forward_title'] = 'Reenvío de correo';
$labels['tab_vacation'] = 'Vacaciones y Auto-respuesta';
$labels['tab_templates'] = 'Plantillas por idioma';
$labels['tab_forward'] = 'Reglas de reenvío';
$labels['tab_exclusions'] = 'Anti-bucle y Filtros';
$labels['tab_logs'] = 'Registro de actividad';

$labels['vacation_status'] = 'Estado de respuesta automática';
$labels['status_disabled'] = 'Desactivado';
$labels['status_enabled'] = 'Activado (Siempre activo)';
$labels['status_scheduled'] = 'Programado (Periodo de vacaciones)';
$labels['status_active_badge'] = 'Activo ahora';
$labels['status_scheduled_badge'] = 'Programado';
$labels['status_expired_badge'] = 'Caducado';

$labels['vacation_start'] = 'Fecha y hora de inicio';
$labels['vacation_end'] = 'Fecha y hora de fin';
$labels['vacation_auto_disable'] = 'Desactivar automáticamente tras el fin del periodo';
$labels['preset_weekend'] = 'Este fin de semana';
$labels['preset_next_week'] = 'La próxima semana';
$labels['preset_two_weeks'] = 'Próximas 2 semanas';

$labels['rate_limit_interval'] = 'Intervalo de respuesta (Límite por remitente)';
$labels['interval_1_hour'] = 'Cada 1 hora';
$labels['interval_6_hours'] = 'Cada 6 horas';
$labels['interval_12_hours'] = 'Cada 12 horas';
$labels['interval_1_day'] = 'Cada 24 horas (1 día)';
$labels['interval_2_days'] = 'Cada 2 días';
$labels['interval_3_days'] = 'Cada 3 días';
$labels['interval_7_days'] = 'Cada 7 días';
$labels['interval_14_days'] = 'Cada 14 días';
$labels['interval_unlimited'] = 'Responder siempre (Sin límite)';

$labels['default_subject'] = 'Asunto predeterminado';
$labels['default_body'] = 'Cuerpo del mensaje predeterminado';

$labels['forward_status'] = 'Estado de reenvío';
$labels['forward_destinations'] = 'Reenviar a (Direcciones de correo)';
$labels['forward_destinations_help'] = 'Introduzca una o más direcciones de correo separadas por coma o salto de línea.';
$labels['forward_keep_copy'] = 'Guardar una copia en la bandeja de entrada';
$labels['forward_mode'] = 'Modo de reenvío';
$labels['forward_mode_redirect'] = 'Redirección (Conserva remitente original)';
$labels['forward_mode_inline'] = 'Reenvío con encabezado en línea';
$labels['forward_condition'] = 'Filtro de reenvío';
$labels['forward_all'] = 'Reenviar todos los mensajes entrantes';
$labels['forward_matching_only'] = 'Reenviar solo según criterios';
$labels['forward_keywords'] = 'Palabras clave en el asunto (separadas por coma)';

$labels['templates_title'] = 'Plantillas multilingües de respuesta';
$labels['templates_desc'] = 'Configure respuestas de ausencia personalizadas por idioma o dominio. El sistema detecta el idioma del remitente y selecciona la plantilla adecuada.';
$labels['btn_add_template'] = 'Añadir plantilla';
$labels['btn_edit'] = 'Editar';
$labels['btn_delete'] = 'Eliminar';
$labels['btn_preview'] = 'Probar / Vista previa';
$labels['template_name'] = 'Nombre de la plantilla';
$labels['template_lang'] = 'Idioma';
$labels['template_is_default'] = 'Plantilla predeterminada de respaldo';
$labels['template_domain_rule'] = 'Filtro por dominio o remitente (opcional)';
$labels['template_subject'] = 'Plantilla de asunto';
$labels['template_body'] = 'Cuerpo del mensaje';
$labels['placeholders_legend'] = 'Variables disponibles';
$labels['placeholder_start_date'] = 'Fecha de inicio';
$labels['placeholder_end_date'] = 'Fecha de fin';
$labels['placeholder_return_date'] = 'Fecha estimada de regreso';
$labels['placeholder_sender_name'] = 'Nombre del remitente';
$labels['placeholder_sender_email'] = 'Correo del remitente';
$labels['placeholder_original_subject'] = 'Asunto original';
$labels['placeholder_user_name'] = 'Su nombre mostrado';
$labels['placeholder_user_email'] = 'Su correo electrónico';

$labels['anti_loop_title'] = 'Anti-bucle y Supresión inteligente';
$labels['ignore_auto_submitted'] = 'Ignorar mensajes automáticos y listas de correo (Auto-Submitted, List-Id)';
$labels['ignore_daemons'] = 'Ignorar rebotes y demonios del sistema (mailer-daemon, postmaster, noreply)';
$labels['sender_blacklist'] = 'Lista negra de remitentes (correos o @dominios, uno por línea)';
$labels['subject_prefix'] = 'Prefijo del asunto';
$labels['max_replies_daily'] = 'Máx. respuestas diarias (Límite global)';

$labels['col_timestamp'] = 'Fecha y hora';
$labels['col_sender'] = 'Remitente';
$labels['col_recipient'] = 'Destinatario / Objetivo';
$labels['col_action'] = 'Acción';
$labels['col_template'] = 'Plantilla';
$labels['col_status'] = 'Estado';
$labels['action_auto_reply'] = 'Respuesta automática';
$labels['action_forwarded'] = 'Reenviado';
$labels['action_suppressed'] = 'Suprimido (Límite)';
$labels['no_logs_found'] = 'Sin actividad de vacaciones o reenvío registrada todavía.';
$labels['btn_clear_logs'] = 'Borrar registro';
$labels['btn_export_sieve'] = 'Exportar script Sieve';
$labels['saved_success'] = 'Configuración guardada correctamente.';
$labels['cleared_logs_success'] = 'Registro de actividad borrado correctamente.';
$labels['simulate_title'] = 'Simulación de plantilla';
$labels['simulate_sender'] = 'Remitente de prueba';
$labels['simulate_subject'] = 'Asunto de prueba';
$labels['simulate_body'] = 'Cuerpo de prueba';
$labels['simulate_run'] = 'Ejecutar simulación';
$labels['simulate_matched'] = 'Plantilla seleccionada';
$labels['simulate_detected_lang'] = 'Idioma detectado';
