<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Calendário';
$labels['app_menu_title'] = 'Calendário';

// menu
$labels['create_event'] = 'Criar evento';
$labels['create_event_button'] = 'Novo evento';
$labels['day'] = 'Dia';
$labels['week'] = 'Semana';
$labels['month'] = 'Mês';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Calendários';
$labels['my_calendar'] = 'O meu calendário';
$labels['create_calendar'] = 'Criar calendário';
$labels['new_calendar'] = 'Novo calendário';
$labels['edit_calendar'] = 'Editar calendário';
$labels['remove_calendar'] = 'Eliminar o calendário';
$labels['confirm_remove_calendar'] = 'Tem a certeza de que pretende apagar este calendário?';
$labels['calendar_removed'] = 'Calendário suprimido.';
$labels['notifications'] = 'Notificações';
$labels['default_event_visibility'] = 'Visibilidade predefinida do evento';
$labels['time_zone'] = 'Fuso horário do calendário';
$labels['share_calendar'] = 'Partilhar o calendário';
$labels['permissions'] = 'Permissões';
$labels['permission_publish_calendar'] = 'Publicar calendário';
$labels['permission_share_calendar'] = 'Partilhar o calendário';
$labels['permission_edit_events'] = 'Editar eventos';
$labels['permission_see_details'] = 'Ver detalhes';
$labels['permission_publish_calendar_info'] = 'Se estiver selecionada, o utilizador poderá publicar o calendário.';
$labels['permission_share_calendar_info'] = 'Se estiver selecionada, o utilizador poderá partilhar o calendário com outros utilizadores.';
$labels['permission_edit_events_info'] = 'Se estiver selecionada, o utilizador poderá editar e apagar eventos do calendário.';
$labels['permission_see_details_info'] = 'Se estiver selecionado, todos os detalhes do evento serão partilhados. Se não estiver selecionado, apenas serão partilhadas as informações disponíveis/ocupadas.';
$labels['share_send_notification'] = 'Notificar';
$labels['share_send_notification_info'] = 'Se estiver selecionada, será enviada uma mensagem de correio eletrónico ao utilizador informando-o do novo calendário partilhado.';
$labels['new_share_not_saved_warning'] = 'Introduziu um endereço de correio eletrónico para partilhar o calendário, mas não clicou no botão Partilhar para o adicionar à lista partilhada. Gostaria de continuar a guardar sem partilhar o calendário com este utilizador?';
$labels['share'] = 'Partilhar';
$labels['shared_marker'] = '(Partilhado)';
$labels['owner'] = 'Proprietário';
$labels['publish_calendar'] = 'Publicar calendário';
$labels['create_link'] = 'Criar ligação';
$labels['reset'] = 'Reiniciar';
$labels['remove'] = 'Remover';
$labels['publish_full'] = 'Publicar todas as informações sobre o evento.';
$labels['publish_busy'] = 'Publicar apenas as informações livres/ocupadas.';
$labels['sync_no_id'] = 'As opções CalDAV ficarão disponíveis depois de guardar o calendário.';
$labels['share_no_id'] = 'As opções de partilha do calendário ficarão disponíveis depois de guardar o calendário.';
$labels['publish_no_id'] = 'As opções de publicação do calendário ficarão disponíveis depois de guardar o calendário.';
$labels['import_no_id'] = 'As opções de importação e exportação ficarão disponíveis depois de guardar o calendário.';
$labels['unable_to_save_calendar_data'] = 'Não é possível guardar os dados do calendário.';
$labels['unable_to_retrieve_calendar_data'] = 'Não foi possível recuperar os dados do calendário. O calendário pode já não estar disponível.';
$labels['unable_add_shared_calendar'] = 'Não é possível adicionar o calendário partilhado. O calendário pode já não estar disponível.';
$labels['itip_organizer_message'] = 'Foi convidado para este evento mas não é o organizador. As alterações que efetuar a este evento não serão enviadas aos outros participantes.';
$labels['cannot_share_with_yourself'] = 'Não pode partilhar o seu próprio calendário consigo próprio.';
$labels['cannot_share_with_calendar_owner'] = 'Não é possível partilhar este calendário com o proprietário do calendário ($e)';
$labels['setting_default_notification_type'] = 'Notificações predefinidas para novos eventos';
$labels['calendar_name_cannot_be_empty'] = 'O nome do calendário não pode estar vazio.';

// caldav server
$labels['sync_calendar'] = 'Sincronizar calendário';
$labels['password_too_short'] = 'A palavra-passe deve ter pelo menos 10 caracteres';
$labels['read_only'] = 'Apenas leitura';
$labels['add_connection'] = 'Adicionar ligação';
$labels['show_details'] = 'Mostrar detalhes';
$labels['hide_details'] = 'Ocultar detalhes';
$labels['caldav_connection_password'] = 'A palavra-passe que especificou ao criar esta ligação.';
$labels['yes'] = 'Sim';
$labels['no'] = 'Não';
$labels['confirm_remove_sync'] = 'Tem a certeza de que pretende remover permanentemente esta ligação?';
$labels['remove_sync_error'] = 'Ocorreu um erro ao remover a ligação.';
$labels['create_new_caldav_connection'] = 'Criar uma nova ligação CalDav';
$labels['your_caldav_connections'] = 'As suas ligações CalDav';
$labels['created_at'] = 'Criado';
$labels['connected_at'] = 'Última sincronização';
$labels['never'] = 'Nunca';
$labels['alternate_url'] = 'URL alternativo';
$labels['caldav_password_info'] = 'Por razões de segurança, esta palavra-passe não é guardada no servidor. Se se esqueceu dela, apague esta ligação e crie uma nova.';
$labels['caldav_url_info'] = 'Utilize este URL com aplicações que suportam a descoberta de calendários, por exemplo: iOS e Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Utilize este URL com aplicações que não funcionem com o URL curto acima, por exemplo: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'URL do servidor CalDAV inválida.';
$labels['cannot_resolve_caldav_host'] = 'Não é possível resolver o host do servidor CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'Não são permitidos endereços CalDAV privados e reservados.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Adicionar calendário partilhado';
$labels['shared_calendars'] = 'Calendários partilhados';
$labels['no_shared_calendars'] = 'Não existem calendários partilhados que possa adicionar.';
$labels['add_to_my_calendars'] = 'Adicionar aos meus calendários';
$labels['remove_from_my_calendars'] = 'Remover dos meus calendários';
$labels['unshare'] = 'Não partilhar';
$labels['unshare_warning'] = 'Se anular a partilha deste calendário, ele desaparecerá desta lista e não poderá voltar a adicioná-lo.';
$labels['calendar_name'] = 'Nome do calendário';
$labels['calendar_owner'] = 'Proprietário do calendário';
$labels['shared_date'] = 'Partilhado';
$labels['unable_to_share'] = 'O calendário não pode ser partilhado com os seguintes utilizadores porque as suas contas de correio eletrónico estão localizadas num servidor remoto:';

// shared calendar email
$labels['shared_email_subject'] = '$u partilhou um calendário consigo';
$labels['shared_email_body'] = 'O utilizador $u deu-lhe acesso a um calendário chamado "$c". Para adicionar este calendário à sua lista de calendários activos, clique na ligação abaixo:';
$labels['shared_email_body_cpanel'] = 'O utilizador $u deu-lhe acesso a um calendário chamado "$c". Para adicionar este calendário à sua lista de calendários activos, inicie sessão no Roundcube, vá a Calendário, clique no ícone mais na barra lateral e selecione Adicionar calendário partilhado.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Calendários de férias';
$labels['edit_holiday_calendar'] = 'Editar o calendário de feriados';
$labels['religious_holidays'] = 'Feriados religiosos';
$labels['country_holidays'] = 'Férias no campo';
$labels['add'] = 'Adicionar';
$labels['no_more_holidays_allowed'] = 'Não é possível adicionar mais calendários de férias.';
$labels['cannot_load_holidays'] = 'Não é possível carregar as férias a partir do servidor Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Adicionar calendário de aniversários';
$labels['edit_birthday_calendar'] = 'Editar calendário de aniversários';
$labels['contact_birthdays'] = 'Contacto Aniversários';
$labels['contact_birthdays_info'] = 'Este calendário obtém informações sobre aniversários da sua lista de contactos e apresenta-as como eventos de calendário.';
$labels['birthday_event_title'] = 'Aniversário: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Adicionar calendário do Google';
$labels['edit_google_calendar'] = 'Editar o calendário do Google';
$labels['google_id'] = 'ID do calendário Google';
$labels['new_google_calendar'] = 'Novo Calendário Google';
$labels['specify_google_calendar_id'] = 'Introduza o ID do calendário Google';
$labels['calendar_id_format_error'] = 'O ID do calendário do Google deve incluir o carácter @.';
$labels['cannot_load_google_calendar'] = 'O calendário do Google "$c" não pode ser carregado. Certifique-se de que o ID do calendário está correto e de que o calendário foi disponibilizado ao público.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Adicionar calendário CalDAV';
$labels['edit_caldav_calendar'] = 'Editar o calendário CalDAV';
$labels['add_caldav_calendar_information'] = 'Para subscrever um calendário CalDAV, especifique as informações do servidor CalDAV e clique em Procurar calendários.';
$labels['caldav_url'] = 'URL do CalDAV';
$labels['find_calendars'] = 'Encontrar calendários';
$labels['searching_for_calendars'] = 'Procura de calendários, aguarde...';
$labels['select_calendars'] = 'Selecione os calendários a adicionar:';
$labels['read_only_status_yes'] = 'Sim';
$labels['read_only_status_no'] = 'Não';
$labels['subscribed'] = 'Subscrito';

$labels['refresh_calendar'] = 'Atualizar o calendário';
$labels['refresh_every_minute'] = 'Cada minuto';
$labels['refresh_every_5_minutes'] = 'A cada 5 minutos';
$labels['refresh_every_15_minutes'] = 'A cada 15 minutos';
$labels['refresh_every_30_minutes'] = 'A cada 30 minutos';
$labels['refresh_every_60_minutes'] = 'A cada 60 minutos';
$labels['refresh_never'] = 'Nunca';

$labels['cannot_connect_to_caldav_server'] = 'Não é possível ligar ao servidor CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Não é possível ligar ao servidor CalDAV. Verifique o URL do servidor.';
$labels['caldav_client_error_credentials'] = 'Nome de utilizador ou palavra-passe CalDAV incorreto.';
$labels['caldav_client_error_empty'] = 'O servidor não devolveu quaisquer calendários que possam ser adicionados. Verifique o URL do CalDAV.';
$labels['caldav_client_error_server_info'] = 'Informação incorrecta do servidor CalDAV.';
$labels['caldav_client_error_add_calendar'] = 'Não é possível guardar o calendário "$n".';
$labels['caldav_client_error_delete_event'] = 'Não é possível eliminar o evento';
$labels['caldav_client_error_save_event'] = 'Não é possível guardar o evento CalDAV: este pode ter sido movido, eliminado ou o calendário pode não estar disponível.';
$labels['caldav_client_error_calendar_added'] = 'O calendário "$n" já se encontra na sua lista de calendários.';

// previewing event
$labels['remove_recurring_event'] = 'Eliminar um evento recorrente';
$labels['remove_one'] = 'Eliminar apenas esta instância';
$labels['remove_all'] = 'Eliminar todas as instâncias';
$labels['edit_event'] = 'Editar evento';
$labels['event_removed'] = 'Evento eliminado.';
$labels['go_to_url'] = 'Ir para URL';
$labels['confirm_remove_event'] = 'Tem a certeza de que pretende apagar este evento?';

// calendar/agenda grid
$labels['no_events'] = 'Não existem eventos para apresentar.';

// importing events
$labels['select_calendar'] = 'Selecionar calendário';
$labels['select_calendar_for_events'] = 'Selecione o calendário ao qual pretende adicionar os eventos:';
$labels['add_events'] = 'Adicionar eventos';
$labels['add_events_to_calendar'] = 'Adicionar eventos ao calendário';
$labels['events_imported_successfully'] = 'Eventos importados com sucesso';
$labels['events_imported_with_errors'] = 'Não foi possível importar outro evento.';
$labels['error_importing_events'] = 'Ocorreu um erro durante a importação de eventos para o calendário';

// editing events
$labels['calendar'] = 'Calendário';
$labels['new_event'] = 'Novo evento';
$labels['summary'] = 'Resumo';
$labels['recurrence'] = 'Recorrência';
$labels['title'] = 'Título';
$labels['location'] = 'Localização';
$labels['description'] = 'Descrição';
$labels['comment'] = 'Comentário';
$labels['url'] = 'URL';
$labels['name'] = 'Nome';
$labels['start'] = 'Início';
$labels['end'] = 'Fim';
$labels['show_timezones'] = 'Mostrar fusos horários';
$labels['reset_timezones'] = 'Repor os fusos horários para a predefinição';
$labels['all_day'] = 'Todo o dia';
$labels['use_calendar_colors'] = 'Utilizar cores do calendário';
$labels['color'] = 'Cor';
$labels['background'] = 'Antecedentes';
$labels['text'] = 'Texto';
$labels['show_me_as'] = 'Mostrar-me como';
$labels['available'] = 'Disponível';
$labels['busy'] = 'Ocupado';
$labels['visibility'] = 'Visibilidade';
$labels['calendar_default'] = 'Calendário por defeito';
$labels['public'] = 'Público';
$labels['private'] = 'Privado';
$labels['repeat'] = 'Repetir';
$labels['repeat_every'] = 'Repetir cada';
$labels['never'] = 'Nunca';
$labels['daily'] = 'Diário';
$labels['weekly'] = 'Semanal';
$labels['monthly'] = 'Mensal';
$labels['yearly'] = 'Anual';
$labels['days'] = 'dia(s)';
$labels['weeks'] = 'semana(s)';
$labels['months'] = 'mês(es)';
$labels['years'] = 'ano(s)';
$labels['repeat_on'] = 'Repetir';
$labels['repeat_by'] = 'Repetir por';
$labels['day_of_month'] = 'Dia do mês';
$labels['day_of_week'] = 'Dia da semana';
$labels['not_specified'] = 'Não especificado';
$labels['first'] = 'Primeiro';
$labels['second'] = 'Segundo';
$labels['third'] = 'Terceiro';
$labels['fourth'] = 'Quarto';
$labels['fifth'] = 'Quinto';
$labels['second_to_last'] = 'Em penúltimo lugar';
$labels['last'] = 'Último';
$labels['attachment_too_large'] = 'O ficheiro anexo é demasiado grande. O tamanho máximo permitido é $s.';
$labels['bg_color'] = 'Cor de fundo';
$labels['tx_color'] = 'Cor do texto';
$labels['add_notification'] = 'Adicionar notificação';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'Este evento não tem quaisquer notificações.';
$labels['minutes'] = 'minutos';
$labels['hours'] = 'horas';
$labels['link'] = 'Ligação';
$labels['attach_file_from_web'] = 'Anexar ficheiro';
$labels['attachment_url'] = 'URL do anexo';
$labels['invalid_url'] = 'Este URL é inválido.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Duplicar';
$labels['duplicate_suffix'] = '(duplicado)';
$labels['no_notifications'] = 'Este evento não tem quaisquer notificações.';
$labels['this_calendar_is_hidden'] = 'Este calendário está oculto. Os eventos guardados não serão apresentados até que o calendário fique visível.';
$labels['make_calendar_visible'] = 'Torne este calendário visível.';

// recurrence
$labels['recurrence_range'] = 'Intervalo de recorrência';
$labels['repeat_forever'] = 'Repetir para sempre';
$labels['repeat_every_weekday'] = 'Repetir todos os dias da semana';
$labels['last_day'] = 'Último dia';
$labels['every'] = 'Todos';
$labels['weekday'] = 'Dia da semana';
$labels['weekend_day'] = 'Dia de fim de semana';
$labels['day_of_week'] = 'Dia da semana';
$labels['day_of_month'] = 'Dia do mês';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Fim após';
$labels['occurrences'] = 'ocorrência(s)';

// end recurrence on date:
$labels['end_by_date'] = 'Fim por data';
$labels['removed_events_title'] = 'Eventos removidos de séries recorrentes';
$labels['restore'] = 'Restaurar';

// settings page
$labels['setting_view'] = 'Vista inicial do calendário';
$labels['setting_first_day'] = 'A semana começa em';
$labels['setting_agenda_week_span'] = 'Período de tempo da agenda (semanas)';
$labels['setting_slot_duration'] = 'Horário do dia (minutos)';
$labels['setting_scroll_time'] = 'Deslocar-se para a hora';
$labels['setting_calendar'] = 'Calendário predefinido para eventos';
$labels['setting_refresh'] = 'Atualizar calendários';
$labels['setting_week_numbers'] = 'Números da semana num calendário pequeno';
$labels['setting_event_border'] = 'Cores da categoria como limites do evento';
$labels['show_sunrise'] = 'Mostrar o nascer do sol na vista mensal';
$labels['show_sunset'] = 'Mostrar o pôr do sol na vista mensal';
$labels['categories'] = 'Categorias';
$labels['category'] = 'Categoria';
$labels['no_category'] = 'Nenhuma categoria';
$labels['add_category'] = 'Adicionar categoria';
$labels['setting_alarm_sound'] = 'Som de notificação';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambu';
$labels['alarm_sound_bells'] = 'Sinos';
$labels['alarm_sound_chord'] = 'Acorde';
$labels['alarm_sound_circles'] = 'Círculos';
$labels['alarm_sound_glass'] = 'Vidro';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metal';
$labels['alarm_sound_ripple'] = 'Ondulação';
$labels['alarm_sound_startrek'] = 'Caminho das Estrelas';
$labels['alarm_sound_whistle'] = 'Apito';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Reproduzir som';

// alarms
$labels['upcoming_events'] = 'Próximos eventos';
$labels['dismiss'] = 'Rejeitar';
$labels['dismiss_all'] = 'Não aceitar todos';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minutos';
$labels['10_minutes'] = '10 minutos';
$labels['20_minutes'] = '20 minutos';
$labels['30_minutes'] = '30 minutos';
$labels['1_hour'] = '1 hora';
$labels['2_hours'] = '2 horas';
$labels['tomorrow'] = 'Até amanhã';
$labels['next_week'] = 'Até à próxima semana';

// sidebar
$labels['setting_show_xcalendar'] = 'Calendário de espectáculos Agenda';
$labels['todays_agenda'] = 'Agenda de hoje';
$labels['no_events_today'] = 'Não há eventos programados para hoje.';
$labels['sidebar_more'] = 'Mais...';

// event invitation
$labels['itip_title_request'] = 'Está convidado para um evento.';
$labels['itip_title_modified'] = 'Este evento foi modificado.';
$labels['itip_title_reply_accepted'] = '$u aceitou o seu convite';
$labels['itip_title_reply_tentative'] = '$u aceitou provisoriamente o seu convite';
$labels['itip_title_reply_declined'] = '$u recusou o seu convite';
$labels['itip_title_cancel'] = 'Este evento foi cancelado.';

$labels['itip_email_subject_request'] = 'Convite:';
$labels['itip_email_subject_request_modified'] = 'Evento modificado:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Cancelado:';

$labels['itip_popup_event_added'] = 'Evento adicionado ao calendário.';
$labels['itip_popup_event_updated'] = 'Evento atualizado no calendário.';
$labels['itip_popup_event_deleted'] = 'Evento eliminado do calendário.';
$labels['itip_popup_response_sent'] = 'Resposta enviada ao organizador.';

$labels['itip_message_invitation_accepted'] = 'Aceitou este convite.';
$labels['itip_message_invitation_tentative'] = 'Aceitou provisoriamente este convite.';
$labels['itip_message_invitation_declined'] = 'Recusou este convite.';
$labels['itip_message_event_deleted'] = 'O evento foi eliminado do seu calendário.';
$labels['itip_message_event_updated'] = 'O evento no seu calendário foi atualizado.';
$labels['itip_message_event_missing'] = 'Este evento não existe em nenhum dos vossos calendários.';

$labels['itip_accept'] = 'Aceitar';
$labels['itip_tentative'] = 'Tentativa';
$labels['itip_decline'] = 'Declínio';
$labels['itip_change_reponse'] = 'Alterar resposta.';
$labels['itip_add_to_calendar'] = 'Adicionar ao calendário';
$labels['itip_reply_to_sender'] = 'Responder ao remetente';
$labels['itip_dont_import'] = 'Não adicionar ao calendário';
$labels['itip_update_in_calendar'] = 'Atualizar evento no calendário';
$labels['itip_delete_from_calendar'] = 'Eliminar um evento do calendário';

$labels['mail_subject_invitation'] = 'Convite:';
$labels['mail_subject_update'] = 'Convite atualizado:';
$labels['mail_subject_cancel'] = 'Evento cancelado:';

// event email notifications
$labels['mail_title'] = 'Título:';
$labels['mail_start'] = 'Início:';
$labels['mail_end'] = 'Fim:';
$labels['mail_description'] = 'Descrição:';
$labels['mail_location'] = 'Localização:';

// event attendees
$labels['attendees'] = 'Participantes';
$labels['attendee_role'] = 'Papel';
$labels['attendee_role_required'] = 'Necessário';
$labels['attendee_role_optional'] = 'Opcional';
$labels['attendee_role_chair'] = 'Cadeira';
$labels['attendee_role_none'] = 'Não participante';
$labels['organizer'] = 'Organizador';
$labels['attendance'] = 'Presença';
$labels['your_status'] = 'O seu estatuto';
$labels['attendance_going'] = 'Vai?';
$labels['attendee_status_yes'] = 'Sim';
$labels['attendee_status_no'] = 'Não';
$labels['attendee_status_maybe'] = 'Talvez';
$labels['attendee_status_delegated'] = 'Delegado';
$labels['attendee_status_waiting'] = 'Em espera';
$labels['attendee_status_waiting_for_confirmation'] = 'À espera de confirmação';
$labels['attendee_status_info_yes'] = 'Estará presente';
$labels['attendee_status_info_no'] = 'Não vai participar';
$labels['attendee_status_info_maybe'] = 'Poderá estar presente';
$labels['attendee_status_info_delegated'] = 'Presença delegada';
$labels['attendee_role'] = 'Papel';
$labels['attendee_role_info'] = 'Especificar se o participante é obrigado a assistir ou a presidir à reunião.';
$labels['attendee_notify'] = 'Notificar';
$labels['attendee_notify_info'] = 'Se estiver selecionado, será enviado um convite por correio eletrónico ao participante.';
$labels['add_attendee'] = 'Adicionar participante';
$labels['attendee_notify_question'] = 'Enviar e-mails de notificação aos participantes?';
$labels['attendee_notify_textarea'] = 'Adicionar uma mensagem opcional';
$labels['attendee_notify_send'] = 'Enviar';
$labels['attendee_notify_dont'] = 'Não enviar';
$labels['attendee_notify_cancel'] = 'Cancelar';
$labels['new_attendee_not_saved_warning'] = 'Introduziu o endereço de e-mail de um participante, mas não o adicionou à lista de participantes. Deseja continuar a guardar sem este participante?';

// import / export
$labels['import_export'] = 'Importação / Exportação';
$labels['export_to_file'] = 'Exportar para ficheiro';
$labels['export_info'] = 'Clique neste botão para exportar e descarregar os eventos deste calendário no formato iCalendar (ics). Se o calendário contiver muitos eventos, o processo de exportação pode demorar alguns momentos.';
$labels['export_no_events'] = 'Este calendário não tem quaisquer eventos para exportar.';
$labels['import_from_file'] = 'Importar de um ficheiro';
$labels['import_info'] = 'Clique neste botão para importar eventos para este calendário a partir de um ficheiro iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Lembrete de marcação de consulta';
$labels['email_notification_subject'] = 'Lembrete: $t em $d ($z)';
$labels['email_notification_cron_error'] = 'As notificações por correio eletrónico estão desactivadas. Contactar o administrador.';
$labels['alarm_before_event_starts'] = 'Antes do início do evento';
$labels['alarm_after_event_starts'] = 'Após o início do evento';
$labels['alarm_before_event_ends'] = 'Antes do fim do evento';
$labels['alarm_after_event_ends'] = 'Após o fim do evento';
$labels['alarm_at_specific_time'] = 'Num determinado momento';

// search
$labels['specify_at_least_one_calendar'] = 'Especifique pelo menos um calendário para pesquisar.';
$labels['specify_text_to_search'] = 'Escreva o texto a pesquisar.';
$labels['clear_search_results'] = 'Limpo';
$labels['search_outdated_message'] = 'Os resultados da pesquisa podem estar desactualizados. Considere a possibilidade de voltar a efetuar a sua pesquisa.';
$labels['date_range'] = 'Intervalo de datas';
$labels['date_range_subtitle'] = 'Intervalo de datas:';
$labels['search_results'] = 'Resultados encontrados: $n';
$labels['modify_search_options'] = 'Tente modificar as opções de pesquisa.';
$labels['show_shortcuts'] = 'Mostrar atalhos';
$labels['hide_shortcuts'] = 'Ocultar atalhos';
$labels['this_month'] = 'Este mês';
$labels['this_year'] = 'Este ano';
$labels['past_30_days'] = 'Últimos 30 dias';
$labels['next_30_days'] = 'Próximos 30 dias';
$labels['past_6_months'] = 'Últimos 6 meses';
$labels['next_6_months'] = 'Próximos 6 meses';

// calendar grid
$labels['cannot_load_events'] = 'Não é possível carregar eventos para o calendário "$c".';
