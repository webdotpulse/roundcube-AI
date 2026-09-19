<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Calendario';
$labels['app_menu_title'] = 'Calendario';

// menu
$labels['create_event'] = 'Crear evento';
$labels['create_event_button'] = 'Nuevo evento';
$labels['day'] = 'Día';
$labels['week'] = 'Semana';
$labels['month'] = 'Mes';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Calendarios';
$labels['my_calendar'] = 'Mi calendario';
$labels['create_calendar'] = 'Crear calendario';
$labels['new_calendar'] = 'Nuevo calendario';
$labels['edit_calendar'] = 'Editar calendario';
$labels['remove_calendar'] = 'Borrar calendario';
$labels['confirm_remove_calendar'] = '¿Estás seguro de que quieres borrar este calendario?';
$labels['calendar_removed'] = 'Calendario borrado.';
$labels['notifications'] = 'Notificaciones';
$labels['default_event_visibility'] = 'Visibilidad de eventos por defecto';
$labels['time_zone'] = 'Huso horario del calendario';
$labels['share_calendar'] = 'Compartir calendario';
$labels['permissions'] = 'Permisos';
$labels['permission_publish_calendar'] = 'Publicar calendario';
$labels['permission_share_calendar'] = 'Compartir calendario';
$labels['permission_edit_events'] = 'Editar eventos';
$labels['permission_see_details'] = 'Ver detalles';
$labels['permission_publish_calendar_info'] = 'Si está marcada, el usuario podrá publicar el calendario.';
$labels['permission_share_calendar_info'] = 'Si se marca, el usuario podrá compartir el calendario con otros usuarios.';
$labels['permission_edit_events_info'] = 'Si está marcada, el usuario podrá editar y borrar eventos del calendario.';
$labels['permission_see_details_info'] = 'Si está marcada, se compartirán todos los detalles del evento. Si no está marcada, solo se compartirá la información disponible/ocupada.';
$labels['share_send_notification'] = 'Notificar a';
$labels['share_send_notification_info'] = 'Si se marca, se enviará un correo electrónico al usuario informándole del nuevo calendario compartido.';
$labels['new_share_not_saved_warning'] = 'Has escrito un correo electrónico con el que compartir el calendario pero no has hecho clic en el botón Compartir para añadirlo a la lista compartida. ¿Seguir guardando sin compartir el calendario con el usuario especificado?';
$labels['share'] = 'Compartir';
$labels['shared_marker'] = '(Compartido)';
$labels['owner'] = 'Propietario';
$labels['publish_calendar'] = 'Publicar calendario';
$labels['create_link'] = 'Crear enlace';
$labels['reset'] = 'Restablecer';
$labels['remove'] = 'Eliminar';
$labels['publish_full'] = 'Publica toda la información del evento.';
$labels['publish_busy'] = 'Publique sólo la información libre/ocupada.';
$labels['sync_no_id'] = 'Las opciones de CalDAV estarán disponibles después de guardar el calendario.';
$labels['share_no_id'] = 'Las opciones para compartir el calendario estarán disponibles después de guardar el calendario.';
$labels['publish_no_id'] = 'Las opciones de publicación del calendario estarán disponibles después de guardar el calendario.';
$labels['import_no_id'] = 'Las opciones de importación y exportación estarán disponibles después de guardar el calendario.';
$labels['unable_to_save_calendar_data'] = 'No se pueden guardar los datos de la agenda.';
$labels['unable_to_retrieve_calendar_data'] = 'No se han podido recuperar los datos del calendario. Es posible que el calendario ya no esté disponible.';
$labels['unable_add_shared_calendar'] = 'No se ha podido añadir el calendario compartido. Es posible que el calendario ya no esté disponible.';
$labels['itip_organizer_message'] = 'Has sido invitado a este evento pero no eres el organizador. Los cambios que realices en este evento no se enviarán a los demás asistentes.';
$labels['cannot_share_with_yourself'] = 'No puedes compartir tu propio calendario contigo mismo.';
$labels['cannot_share_with_calendar_owner'] = 'No puede compartir este calendario con el propietario del calendario ($e)';
$labels['setting_default_notification_type'] = 'Notificaciones por defecto para nuevos eventos';
$labels['calendar_name_cannot_be_empty'] = 'El nombre del calendario no puede estar vacío.';

// caldav server
$labels['sync_calendar'] = 'Sincronizar calendario';
$labels['password_too_short'] = 'La contraseña debe tener al menos 10 caracteres';
$labels['read_only'] = 'Sólo lectura';
$labels['add_connection'] = 'Añadir conexión';
$labels['show_details'] = 'Mostrar detalles';
$labels['hide_details'] = 'Ocultar detalles';
$labels['caldav_connection_password'] = 'La contraseña que especificó al crear esta conexión.';
$labels['yes'] = 'Sí';
$labels['no'] = 'No';
$labels['confirm_remove_sync'] = '¿Estás seguro de que quieres eliminar permanentemente esta conexión?';
$labels['remove_sync_error'] = 'Se ha producido un error al eliminar la conexión.';
$labels['create_new_caldav_connection'] = 'Crear nueva conexión CalDav';
$labels['your_caldav_connections'] = 'Sus conexiones CalDav';
$labels['created_at'] = 'Creado';
$labels['connected_at'] = 'Última sincronización';
$labels['never'] = 'Nunca';
$labels['alternate_url'] = 'URL alternativa';
$labels['caldav_password_info'] = 'Por razones de seguridad, esta contraseña no se guarda en el servidor. Si la ha olvidado, elimine esta conexión y cree una nueva.';
$labels['caldav_url_info'] = 'Utilice esta URL con aplicaciones que admitan la detección de calendarios, por ejemplo: iOS y Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Utilice esta URL con aplicaciones que no funcionen con la URL corta anterior, por ejemplo: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'URL del servidor CalDAV no válida.';
$labels['cannot_resolve_caldav_host'] = 'No se puede resolver el host del servidor CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'No se permiten direcciones CalDAV privadas ni reservadas.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Añadir calendario compartido';
$labels['shared_calendars'] = 'Calendarios compartidos';
$labels['no_shared_calendars'] = 'No hay calendarios compartidos que puedas añadir.';
$labels['add_to_my_calendars'] = 'Añadir a mis calendarios';
$labels['remove_from_my_calendars'] = 'Eliminar de mis calendarios';
$labels['unshare'] = 'Descompartir';
$labels['unshare_warning'] = 'Si deja de compartir este calendario, desaparecerá de esta lista y no podrá volver a añadirlo.';
$labels['calendar_name'] = 'Nombre del calendario';
$labels['calendar_owner'] = 'Propietario del calendario';
$labels['shared_date'] = 'Compartido';
$labels['unable_to_share'] = 'El calendario no se puede compartir con los siguientes usuarios porque sus cuentas de correo electrónico se encuentran en un servidor remoto:';

// shared calendar email
$labels['shared_email_subject'] = '$u ha compartido un calendario contigo';
$labels['shared_email_body'] = 'El usuario $u le ha dado acceso a un calendario llamado "$c". Para añadir este calendario a su lista de calendarios activos, haga clic en el siguiente enlace:';
$labels['shared_email_body_cpanel'] = 'El usuario $u le ha dado acceso a un calendario llamado "$c". Para añadir este calendario a su lista de calendarios activos, inicie sesión en Roundcube, vaya a Calendario, haga clic en el icono más de la barra lateral y seleccione Añadir calendario compartido.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Calendarios de vacaciones';
$labels['edit_holiday_calendar'] = 'Editar el calendario de vacaciones';
$labels['religious_holidays'] = 'Fiestas religiosas';
$labels['country_holidays'] = 'Vacaciones en el campo';
$labels['add'] = 'Añadir';
$labels['no_more_holidays_allowed'] = 'No puede añadir más calendarios de vacaciones.';
$labels['cannot_load_holidays'] = 'No se pueden cargar las vacaciones desde el servidor de Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Añadir calendario de cumpleaños';
$labels['edit_birthday_calendar'] = 'Editar calendario de cumpleaños';
$labels['contact_birthdays'] = 'Contacto Cumpleaños';
$labels['contact_birthdays_info'] = 'Este calendario recupera la información de cumpleaños de tu lista de contactos y la muestra como eventos de calendario.';
$labels['birthday_event_title'] = 'Cumpleaños: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Añadir calendario de Google';
$labels['edit_google_calendar'] = 'Editar el calendario de Google';
$labels['google_id'] = 'ID del calendario de Google';
$labels['new_google_calendar'] = 'Nuevo calendario de Google';
$labels['specify_google_calendar_id'] = 'Introduce el ID del calendario de Google';
$labels['calendar_id_format_error'] = 'El ID del calendario de Google debe incluir el carácter @.';
$labels['cannot_load_google_calendar'] = 'No se puede cargar el calendario de Google "$c". Asegúrate de que el ID del calendario es correcto y de que el calendario se ha puesto a disposición del público.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Añadir calendario CalDAV';
$labels['edit_caldav_calendar'] = 'Editar calendario CalDAV';
$labels['add_caldav_calendar_information'] = 'Para suscribirte a un calendario CalDAV, especifica la información del servidor CalDAV y haz clic en Buscar calendarios.';
$labels['caldav_url'] = 'URL CalDAV';
$labels['find_calendars'] = 'Buscar calendarios';
$labels['searching_for_calendars'] = 'Buscando calendarios, por favor espere...';
$labels['select_calendars'] = 'Seleccione los calendarios que desea añadir:';
$labels['read_only_status_yes'] = 'Sí';
$labels['read_only_status_no'] = 'No';
$labels['subscribed'] = 'Suscrito';

$labels['refresh_calendar'] = 'Actualizar calendario';
$labels['refresh_every_minute'] = 'Cada minuto';
$labels['refresh_every_5_minutes'] = 'Cada 5 minutos';
$labels['refresh_every_15_minutes'] = 'Cada 15 minutos';
$labels['refresh_every_30_minutes'] = 'Cada 30 minutos';
$labels['refresh_every_60_minutes'] = 'Cada 60 minutos';
$labels['refresh_never'] = 'Nunca';

$labels['cannot_connect_to_caldav_server'] = 'No se puede conectar al servidor CalDAV "$n".';
$labels['caldav_client_error_url'] = 'No se puede conectar al servidor CalDAV. Comprueba la URL del servidor.';
$labels['caldav_client_error_credentials'] = 'Nombre de usuario o contraseña de CalDAV incorrectos.';
$labels['caldav_client_error_empty'] = 'El servidor no ha devuelto ningún calendario que se pueda añadir. Compruebe la URL CalDAV.';
$labels['caldav_client_error_server_info'] = 'Información incorrecta del servidor CalDAV.';
$labels['caldav_client_error_add_calendar'] = 'No se puede guardar el calendario "$n".';
$labels['caldav_client_error_delete_event'] = 'No se puede borrar el evento';
$labels['caldav_client_error_save_event'] = 'No se puede guardar el evento de CalDAV: es posible que se haya movido, eliminado o que el calendario no esté disponible.';
$labels['caldav_client_error_calendar_added'] = 'El calendario "$n" ya está en tu lista de calendarios.';

// previewing event
$labels['remove_recurring_event'] = 'Borrar evento recurrente';
$labels['remove_one'] = 'Borrar sólo esta instancia';
$labels['remove_all'] = 'Borrar todas las instancias';
$labels['edit_event'] = 'Editar evento';
$labels['event_removed'] = 'Evento eliminado.';
$labels['go_to_url'] = 'Ir a la URL';
$labels['confirm_remove_event'] = '¿Estás seguro de que quieres borrar este evento?';

// calendar/agenda grid
$labels['no_events'] = 'No hay eventos que mostrar.';

// importing events
$labels['select_calendar'] = 'Seleccionar calendario';
$labels['select_calendar_for_events'] = 'Seleccione el calendario al que desea añadir los eventos:';
$labels['add_events'] = 'Añadir eventos';
$labels['add_events_to_calendar'] = 'Añadir eventos al calendario';
$labels['events_imported_successfully'] = 'Eventos importados correctamente';
$labels['events_imported_with_errors'] = 'No se ha podido importar algún otro evento.';
$labels['error_importing_events'] = 'Se ha producido un error al importar eventos en el calendario';

// editing events
$labels['calendar'] = 'Calendario';
$labels['new_event'] = 'Nuevo evento';
$labels['summary'] = 'Resumen';
$labels['recurrence'] = 'Recurrencia';
$labels['title'] = 'Título';
$labels['location'] = 'Ubicación';
$labels['description'] = 'Descripción';
$labels['comment'] = 'Comentario';
$labels['url'] = 'URL';
$labels['name'] = 'Nombre';
$labels['start'] = 'Inicio';
$labels['end'] = 'Fin';
$labels['show_timezones'] = 'Mostrar zonas horarias';
$labels['reset_timezones'] = 'Restablecer las zonas horarias por defecto';
$labels['all_day'] = 'Todo el día';
$labels['use_calendar_colors'] = 'Utilizar los colores del calendario';
$labels['color'] = 'Color';
$labels['background'] = 'Fondo';
$labels['text'] = 'Texto';
$labels['show_me_as'] = 'Muéstrame como';
$labels['available'] = 'Disponible';
$labels['busy'] = 'Ocupado';
$labels['visibility'] = 'Visibilidad';
$labels['calendar_default'] = 'Calendario por defecto';
$labels['public'] = 'Público';
$labels['private'] = 'Privado';
$labels['repeat'] = 'Repita';
$labels['repeat_every'] = 'Repetir cada';
$labels['never'] = 'Nunca';
$labels['daily'] = 'Diario';
$labels['weekly'] = 'Semanal';
$labels['monthly'] = 'Mensualmente';
$labels['yearly'] = 'Anualmente';
$labels['days'] = 'día(s)';
$labels['weeks'] = 'semana(s)';
$labels['months'] = 'mes(es)';
$labels['years'] = 'año(s)';
$labels['repeat_on'] = 'Repetir en';
$labels['repeat_by'] = 'Repetir por';
$labels['day_of_month'] = 'día del mes';
$labels['day_of_week'] = 'día de la semana';
$labels['not_specified'] = 'No especificado';
$labels['first'] = 'En primer lugar';
$labels['second'] = 'Segundo';
$labels['third'] = 'Tercera';
$labels['fourth'] = 'Cuarto';
$labels['fifth'] = 'Quinta';
$labels['second_to_last'] = 'Penúltimo';
$labels['last'] = 'Última';
$labels['attachment_too_large'] = 'El archivo adjunto es demasiado grande. El tamaño máximo permitido es $s.';
$labels['bg_color'] = 'Color de fondo';
$labels['tx_color'] = 'Color del texto';
$labels['add_notification'] = 'Añadir notificación';
$labels['popup'] = 'Ventana emergente';
$labels['no_notifications'] = 'Este evento no tiene notificaciones.';
$labels['minutes'] = 'minutos';
$labels['hours'] = 'horas';
$labels['link'] = 'Enlace';
$labels['attach_file_from_web'] = 'Adjuntar archivo';
$labels['attachment_url'] = 'Adjuntar URL';
$labels['invalid_url'] = 'Esta URL no es válida.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Duplicar';
$labels['duplicate_suffix'] = '(duplicado)';
$labels['no_notifications'] = 'Este evento no tiene notificaciones.';
$labels['this_calendar_is_hidden'] = 'Este calendario está oculto. Los eventos guardados no se mostrarán hasta que el calendario esté visible.';
$labels['make_calendar_visible'] = 'Haz que este calendario sea visible.';

// recurrence
$labels['recurrence_range'] = 'Intervalo de recurrencia';
$labels['repeat_forever'] = 'Repite para siempre';
$labels['repeat_every_weekday'] = 'Repetir todos los días de la semana';
$labels['last_day'] = 'Último día';
$labels['every'] = 'Cada';
$labels['weekday'] = 'Entre semana';
$labels['weekend_day'] = 'Fin de semana';
$labels['day_of_week'] = 'día de la semana';
$labels['day_of_month'] = 'día del mes';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Fin después de';
$labels['occurrences'] = 'ocurrencias';

// end recurrence on date:
$labels['end_by_date'] = 'Fecha límite';
$labels['removed_events_title'] = 'Eventos eliminados de las series recurrentes';
$labels['restore'] = 'Restaurar';

// settings page
$labels['setting_view'] = 'Vista inicial del calendario';
$labels['setting_first_day'] = 'La semana comienza el';
$labels['setting_agenda_week_span'] = 'Duración de la agenda (semanas)';
$labels['setting_slot_duration'] = 'Franja horaria del día (minutos)';
$labels['setting_scroll_time'] = 'Desplácese hasta la hora';
$labels['setting_calendar'] = 'Calendario de eventos por defecto';
$labels['setting_refresh'] = 'Actualizar calendarios';
$labels['setting_week_numbers'] = 'Números de semana en el calendario pequeño';
$labels['setting_event_border'] = 'Colores de categorías como bordes de eventos';
$labels['show_sunrise'] = 'Mostrar la salida del sol en la vista mensual';
$labels['show_sunset'] = 'Mostrar la puesta de sol en la vista mensual';
$labels['categories'] = 'Categorías';
$labels['category'] = 'Categoría';
$labels['no_category'] = 'Sin categoría';
$labels['add_category'] = 'Añadir categoría';
$labels['setting_alarm_sound'] = 'Sonido de notificación';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambú';
$labels['alarm_sound_bells'] = 'Campanas';
$labels['alarm_sound_chord'] = 'Acorde';
$labels['alarm_sound_circles'] = 'Círculos';
$labels['alarm_sound_glass'] = 'Vidrio';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metal';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Silbato';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Reproducir sonido';

// alarms
$labels['upcoming_events'] = 'Próximos eventos';
$labels['dismiss'] = 'Desestimar';
$labels['dismiss_all'] = 'Despedir a todos';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minutos';
$labels['10_minutes'] = '10 minutos';
$labels['20_minutes'] = '20 minutos';
$labels['30_minutes'] = '30 minutos';
$labels['1_hour'] = '1 hora';
$labels['2_hours'] = '2 horas';
$labels['tomorrow'] = 'Hasta mañana';
$labels['next_week'] = 'Hasta la semana que viene';

// sidebar
$labels['setting_show_xcalendar'] = 'Mostrar calendario Agenda';
$labels['todays_agenda'] = 'Agenda de hoy';
$labels['no_events_today'] = 'No hay actos programados para hoy.';
$labels['sidebar_more'] = 'Más...';

// event invitation
$labels['itip_title_request'] = 'Está invitado a un acto.';
$labels['itip_title_modified'] = 'Este evento ha sido modificado.';
$labels['itip_title_reply_accepted'] = '$u ha aceptado su invitación';
$labels['itip_title_reply_tentative'] = '$u ha aceptado provisionalmente su invitación';
$labels['itip_title_reply_declined'] = '$u ha rechazado su invitación';
$labels['itip_title_cancel'] = 'Este acto ha sido cancelado.';

$labels['itip_email_subject_request'] = 'Invitación:';
$labels['itip_email_subject_request_modified'] = 'Evento modificado:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Cancelado:';

$labels['itip_popup_event_added'] = 'Evento añadido al calendario.';
$labels['itip_popup_event_updated'] = 'Evento actualizado en el calendario.';
$labels['itip_popup_event_deleted'] = 'Evento borrado del calendario.';
$labels['itip_popup_response_sent'] = 'Respuesta enviada al organizador.';

$labels['itip_message_invitation_accepted'] = 'Ha aceptado esta invitación.';
$labels['itip_message_invitation_tentative'] = 'Ha aceptado provisionalmente esta invitación.';
$labels['itip_message_invitation_declined'] = 'Ha rechazado esta invitación.';
$labels['itip_message_event_deleted'] = 'El evento ha sido eliminado de su calendario.';
$labels['itip_message_event_updated'] = 'El evento en su calendario ha sido actualizado.';
$labels['itip_message_event_missing'] = 'Este evento no existe en ninguno de sus calendarios.';

$labels['itip_accept'] = 'Acepte';
$labels['itip_tentative'] = 'Tentativa';
$labels['itip_decline'] = 'Disminución';
$labels['itip_change_reponse'] = 'Cambia la respuesta.';
$labels['itip_add_to_calendar'] = 'Añadir al calendario';
$labels['itip_reply_to_sender'] = 'Responder al remitente';
$labels['itip_dont_import'] = 'No añadir al calendario';
$labels['itip_update_in_calendar'] = 'Actualizar evento en el calendario';
$labels['itip_delete_from_calendar'] = 'Borrar evento del calendario';

$labels['mail_subject_invitation'] = 'Invitación:';
$labels['mail_subject_update'] = 'Invitación actualizada:';
$labels['mail_subject_cancel'] = 'Evento cancelado:';

// event email notifications
$labels['mail_title'] = 'Título:';
$labels['mail_start'] = 'Comienza:';
$labels['mail_end'] = 'Fin:';
$labels['mail_description'] = 'Descripción:';
$labels['mail_location'] = 'Ubicación:';

// event attendees
$labels['attendees'] = 'Asistentes';
$labels['attendee_role'] = 'Papel';
$labels['attendee_role_required'] = 'Requerido';
$labels['attendee_role_optional'] = 'Opcional';
$labels['attendee_role_chair'] = 'Silla';
$labels['attendee_role_none'] = 'No participante';
$labels['organizer'] = 'Organizador';
$labels['attendance'] = 'Asistencia';
$labels['your_status'] = 'Su situación';
$labels['attendance_going'] = '¿Te vas?';
$labels['attendee_status_yes'] = 'Sí';
$labels['attendee_status_no'] = 'No';
$labels['attendee_status_maybe'] = 'Tal vez';
$labels['attendee_status_delegated'] = 'Delegado';
$labels['attendee_status_waiting'] = 'En espera';
$labels['attendee_status_waiting_for_confirmation'] = 'A la espera de confirmación';
$labels['attendee_status_info_yes'] = 'Asistirá';
$labels['attendee_status_info_no'] = 'No asistirá';
$labels['attendee_status_info_maybe'] = 'Podría asistir';
$labels['attendee_status_info_delegated'] = 'Asistencia delegada';
$labels['attendee_role'] = 'Papel';
$labels['attendee_role_info'] = 'Especifique si el asistente debe asistir o presidir la reunión.';
$labels['attendee_notify'] = 'Notificar';
$labels['attendee_notify_info'] = 'Si se marca, se enviará un correo electrónico de invitación al asistente.';
$labels['add_attendee'] = 'Añadir asistente';
$labels['attendee_notify_question'] = '¿Enviar correos electrónicos de notificación a los asistentes?';
$labels['attendee_notify_textarea'] = 'Añadir un mensaje opcional';
$labels['attendee_notify_send'] = 'Enviar';
$labels['attendee_notify_dont'] = 'No envíe';
$labels['attendee_notify_cancel'] = 'Cancelar';
$labels['new_attendee_not_saved_warning'] = 'Has introducido la dirección de correo electrónico de un asistente, pero no la has añadido a la lista de asistentes. ¿Quieres guardar los cambios sin incluir a este asistente?';

// import / export
$labels['import_export'] = 'Importar / Exportar';
$labels['export_to_file'] = 'Exportar a archivo';
$labels['export_info'] = 'Pulsa este botón para exportar y descargar los eventos de este calendario en formato iCalendar (ics). Si el calendario contiene muchos eventos, el proceso de exportación puede tardar unos instantes.';
$labels['export_no_events'] = 'Este calendario no tiene eventos para exportar.';
$labels['import_from_file'] = 'Importar desde archivo';
$labels['import_info'] = 'Pulse este botón para importar eventos a este calendario desde un archivo iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Recordatorio de cita';
$labels['email_notification_subject'] = 'Recordatorio: $t en $d ($z)';
$labels['email_notification_cron_error'] = 'Las notificaciones por correo electrónico están desactivadas. Póngase en contacto con el administrador.';
$labels['alarm_before_event_starts'] = 'Antes de que comience el acto';
$labels['alarm_after_event_starts'] = 'Tras el inicio del evento';
$labels['alarm_before_event_ends'] = 'Antes de que finalice el acto';
$labels['alarm_after_event_ends'] = 'Una vez finalizado el acto';
$labels['alarm_at_specific_time'] = 'En un momento determinado';

// search
$labels['specify_at_least_one_calendar'] = 'Especifique al menos un calendario en el que buscar.';
$labels['specify_text_to_search'] = 'Escriba el texto que desea buscar.';
$labels['clear_search_results'] = 'Claro';
$labels['search_outdated_message'] = 'Los resultados de la búsqueda pueden estar obsoletos. Considere la posibilidad de volver a realizar la búsqueda.';
$labels['date_range'] = 'Intervalo de fechas';
$labels['date_range_subtitle'] = 'Intervalo de fechas:';
$labels['search_results'] = 'Resultados encontrados: $n';
$labels['modify_search_options'] = 'Prueba a modificar las opciones de búsqueda.';
$labels['show_shortcuts'] = 'Mostrar accesos directos';
$labels['hide_shortcuts'] = 'Ocultar accesos directos';
$labels['this_month'] = 'Este mes';
$labels['this_year'] = 'Este año';
$labels['past_30_days'] = 'Últimos 30 días';
$labels['next_30_days'] = 'Próximos 30 días';
$labels['past_6_months'] = 'Últimos 6 meses';
$labels['next_6_months'] = 'Próximos 6 meses';

// calendar grid
$labels['cannot_load_events'] = 'No se pueden cargar eventos para el calendario "$c".';
