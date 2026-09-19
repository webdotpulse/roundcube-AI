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
$labels['my_calendar'] = 'Meu calendário';
$labels['create_calendar'] = 'Criar calendário';
$labels['new_calendar'] = 'Novo calendário';
$labels['edit_calendar'] = 'Editar calendário';
$labels['remove_calendar'] = 'Excluir calendário';
$labels['confirm_remove_calendar'] = 'Tem certeza de que deseja excluir este calendário?';
$labels['calendar_removed'] = 'Calendário excluído.';
$labels['notifications'] = 'Notificações';
$labels['default_event_visibility'] = 'Visibilidade padrão do evento';
$labels['time_zone'] = 'Fuso horário do calendário';
$labels['share_calendar'] = 'Compartilhar calendário';
$labels['permissions'] = 'Permissões';
$labels['permission_publish_calendar'] = 'Publicar calendário';
$labels['permission_share_calendar'] = 'Compartilhar calendário';
$labels['permission_edit_events'] = 'Editar eventos';
$labels['permission_see_details'] = 'Ver detalhes';
$labels['permission_publish_calendar_info'] = 'Se marcado, o usuário poderá publicar o calendário.';
$labels['permission_share_calendar_info'] = 'Se marcado, o usuário poderá compartilhar o calendário com outros usuários.';
$labels['permission_edit_events_info'] = 'Se marcado, o usuário poderá editar e excluir eventos do calendário.';
$labels['permission_see_details_info'] = 'Se marcado, todos os detalhes do evento serão compartilhados. Se desmarcada, apenas as informações de disponível/ocupado serão compartilhadas.';
$labels['share_send_notification'] = 'Notificar';
$labels['share_send_notification_info'] = 'Se marcado, um e-mail será enviado ao usuário informando-o sobre o novo calendário compartilhado.';
$labels['new_share_not_saved_warning'] = 'Você digitou um e-mail para compartilhar o calendário, mas não clicou no botão Compartilhar para adicioná-lo à lista compartilhada. Continuar salvando sem compartilhar o calendário com o usuário especificado?';
$labels['share'] = 'Compartilhar';
$labels['shared_marker'] = '(Compartilhado)';
$labels['owner'] = 'Proprietário';
$labels['publish_calendar'] = 'Publicar calendário';
$labels['create_link'] = 'Criar link';
$labels['reset'] = 'Reiniciar';
$labels['remove'] = 'Remover';
$labels['publish_full'] = 'Publicar todas as informações do evento.';
$labels['publish_busy'] = 'Publique apenas as informações de disponibilidade.';
$labels['sync_no_id'] = 'As opções do CalDAV ficarão disponíveis depois que você salvar o calendário.';
$labels['share_no_id'] = 'As opções de compartilhamento de calendário ficarão disponíveis depois que você salvar o calendário.';
$labels['publish_no_id'] = 'As opções de publicação do calendário ficarão disponíveis depois que você salvar o calendário.';
$labels['import_no_id'] = 'As opções de importação e exportação ficarão disponíveis depois que você salvar o calendário.';
$labels['unable_to_save_calendar_data'] = 'Não foi possível salvar os dados do calendário.';
$labels['unable_to_retrieve_calendar_data'] = 'Não foi possível recuperar os dados do calendário. O calendário pode não estar mais disponível.';
$labels['unable_add_shared_calendar'] = 'Não foi possível adicionar o calendário compartilhado. O calendário pode não estar mais disponível.';
$labels['itip_organizer_message'] = 'Você foi convidado para este evento, mas não é o organizador. As alterações feitas neste evento não serão enviadas aos outros participantes.';
$labels['cannot_share_with_yourself'] = 'Você não pode compartilhar seu próprio calendário consigo mesmo.';
$labels['cannot_share_with_calendar_owner'] = 'Você não pode compartilhar este calendário com o proprietário do calendário ($e)';
$labels['setting_default_notification_type'] = 'Notificações padrão para novos eventos';
$labels['calendar_name_cannot_be_empty'] = 'O nome do calendário não pode estar vazio.';

// caldav server
$labels['sync_calendar'] = 'Sincronizar calendário';
$labels['password_too_short'] = 'A senha deve ter pelo menos 10 caracteres';
$labels['read_only'] = 'Somente leitura';
$labels['add_connection'] = 'Adicionar conexão';
$labels['show_details'] = 'Mostrar detalhes';
$labels['hide_details'] = 'Ocultar detalhes';
$labels['caldav_connection_password'] = 'A senha que você especificou ao criar esta conexão.';
$labels['yes'] = 'Sim';
$labels['no'] = 'Não';
$labels['confirm_remove_sync'] = 'Tem certeza de que deseja remover esta conexão permanentemente?';
$labels['remove_sync_error'] = 'Ocorreu um erro ao remover a conexão.';
$labels['create_new_caldav_connection'] = 'Criar nova conexão CalDav';
$labels['your_caldav_connections'] = 'Suas conexões CalDav';
$labels['created_at'] = 'Criado';
$labels['connected_at'] = 'Última sincronização';
$labels['never'] = 'Nunca';
$labels['alternate_url'] = 'URL alternativo';
$labels['caldav_password_info'] = 'Por motivos de segurança, esta senha não está salva no servidor. Se você a esqueceu, exclua esta conexão e crie uma nova.';
$labels['caldav_url_info'] = 'Use este URL com aplicativos que suportam a descoberta de calendário, por exemplo: iOS e Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Use este URL com aplicativos que não funcionarão com o URL curto acima, por exemplo: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'URL inválida do servidor CalDAV.';
$labels['cannot_resolve_caldav_host'] = 'Não é possível resolver o host do servidor CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'Endereços CalDAV privados e reservados não são permitidos.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Adicionar calendário compartilhado';
$labels['shared_calendars'] = 'Calendários compartilhados';
$labels['no_shared_calendars'] = 'Não há calendários compartilhados que você possa adicionar.';
$labels['add_to_my_calendars'] = 'Adicionar aos meus calendários';
$labels['remove_from_my_calendars'] = 'Remover dos meus calendários';
$labels['unshare'] = 'Descompartilhar';
$labels['unshare_warning'] = 'Se você cancelar o compartilhamento deste calendário, ele desaparecerá desta lista e você não poderá adicioná-lo novamente.';
$labels['calendar_name'] = 'Nome do calendário';
$labels['calendar_owner'] = 'Proprietário do calendário';
$labels['shared_date'] = 'Compartilhado';
$labels['unable_to_share'] = 'O calendário não pode ser compartilhado com os seguintes usuários porque suas contas de e-mail estão localizadas em um servidor remoto:';

// shared calendar email
$labels['shared_email_subject'] = '$u compartilhou um calendário com você';
$labels['shared_email_body'] = 'O usuário $u deu a você acesso a um calendário chamado "$c." Para adicionar este calendário à sua lista de calendários ativos, clique no link abaixo:';
$labels['shared_email_body_cpanel'] = 'O usuário $u deu a você acesso a um calendário chamado "$c." Para adicionar este calendário à sua lista de calendários ativos, faça login no Roundcube, vá para Calendário, clique no ícone de adição na barra lateral e selecione Adicionar calendário compartilhado.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Calendários de feriados';
$labels['edit_holiday_calendar'] = 'Editar calendário de feriados';
$labels['religious_holidays'] = 'Feriados religiosos';
$labels['country_holidays'] = 'Feriados no país';
$labels['add'] = 'Adicionar';
$labels['no_more_holidays_allowed'] = 'Você não pode adicionar mais calendários de feriados.';
$labels['cannot_load_holidays'] = 'Não é possível carregar feriados do servidor do Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Adicionar calendário de aniversários';
$labels['edit_birthday_calendar'] = 'Editar calendário de aniversários';
$labels['contact_birthdays'] = 'Contato Aniversários';
$labels['contact_birthdays_info'] = 'Esse calendário recupera informações de aniversários da sua lista de contatos e as exibe como eventos de calendário.';
$labels['birthday_event_title'] = 'Aniversário: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Adicionar calendário do Google';
$labels['edit_google_calendar'] = 'Editar o Google Agenda';
$labels['google_id'] = 'ID da agenda do Google';
$labels['new_google_calendar'] = 'Novo Google Agenda';
$labels['specify_google_calendar_id'] = 'Digite o ID da agenda do Google';
$labels['calendar_id_format_error'] = 'O ID do calendário do Google deve incluir o caractere @.';
$labels['cannot_load_google_calendar'] = 'O calendário do Google "$c" não pode ser carregado. Certifique-se de que o ID do calendário esteja correto e que o calendário tenha sido disponibilizado ao público.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Adicionar calendário CalDAV';
$labels['edit_caldav_calendar'] = 'Editar o calendário CalDAV';
$labels['add_caldav_calendar_information'] = 'Para assinar um calendário CalDAV, especifique as informações do servidor CalDAV e clique em Find calendars (Localizar calendários).';
$labels['caldav_url'] = 'URL do CalDAV';
$labels['find_calendars'] = 'Localizar calendários';
$labels['searching_for_calendars'] = 'Procurando por calendários, aguarde...';
$labels['select_calendars'] = 'Selecione os calendários a serem adicionados:';
$labels['read_only_status_yes'] = 'Sim';
$labels['read_only_status_no'] = 'Não';
$labels['subscribed'] = 'Assinatura';

$labels['refresh_calendar'] = 'Atualizar calendário';
$labels['refresh_every_minute'] = 'A cada minuto';
$labels['refresh_every_5_minutes'] = 'A cada 5 minutos';
$labels['refresh_every_15_minutes'] = 'A cada 15 minutos';
$labels['refresh_every_30_minutes'] = 'A cada 30 minutos';
$labels['refresh_every_60_minutes'] = 'A cada 60 minutos';
$labels['refresh_never'] = 'Nunca';

$labels['cannot_connect_to_caldav_server'] = 'Não é possível conectar-se ao servidor CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Não é possível conectar-se ao servidor CalDAV. Verifique o URL do servidor.';
$labels['caldav_client_error_credentials'] = 'Nome de usuário ou senha do CalDAV incorretos.';
$labels['caldav_client_error_empty'] = 'O servidor não retornou nenhum calendário que possa ser adicionado. Verifique o URL do CalDAV.';
$labels['caldav_client_error_server_info'] = 'Informações incorretas do servidor CalDAV.';
$labels['caldav_client_error_add_calendar'] = 'Não é possível salvar o calendário "$n".';
$labels['caldav_client_error_delete_event'] = 'Não é possível excluir o evento';
$labels['caldav_client_error_save_event'] = 'Não é possível salvar o evento CalDAV: ele pode ter sido movido, excluído ou o calendário pode estar indisponível.';
$labels['caldav_client_error_calendar_added'] = 'O calendário "$n" já está em sua lista de calendários.';

// previewing event
$labels['remove_recurring_event'] = 'Excluir evento recorrente';
$labels['remove_one'] = 'Excluir apenas esta instância';
$labels['remove_all'] = 'Excluir todas as instâncias';
$labels['edit_event'] = 'Editar evento';
$labels['event_removed'] = 'Evento excluído.';
$labels['go_to_url'] = 'Ir para URL';
$labels['confirm_remove_event'] = 'Tem certeza de que deseja excluir esse evento?';

// calendar/agenda grid
$labels['no_events'] = 'Não há eventos para exibir.';

// importing events
$labels['select_calendar'] = 'Selecionar calendário';
$labels['select_calendar_for_events'] = 'Selecione o calendário ao qual gostaria de adicionar os eventos:';
$labels['add_events'] = 'Adicionar eventos';
$labels['add_events_to_calendar'] = 'Adicionar eventos ao calendário';
$labels['events_imported_successfully'] = 'Eventos importados com sucesso';
$labels['events_imported_with_errors'] = 'Algum outro evento não pôde ser importado.';
$labels['error_importing_events'] = 'Ocorreu um erro ao importar eventos para o calendário';

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
$labels['start'] = 'Iniciar';
$labels['end'] = 'Fim';
$labels['show_timezones'] = 'Mostrar fusos horários';
$labels['reset_timezones'] = 'Redefinir fusos horários para o padrão';
$labels['all_day'] = 'Dia inteiro';
$labels['use_calendar_colors'] = 'Usar cores de calendário';
$labels['color'] = 'Cor';
$labels['background'] = 'Background';
$labels['text'] = 'Texto';
$labels['show_me_as'] = 'Mostrar como';
$labels['available'] = 'Disponível';
$labels['busy'] = 'Ocupado';
$labels['visibility'] = 'Visibilidade';
$labels['calendar_default'] = 'Padrão do calendário';
$labels['public'] = 'Público';
$labels['private'] = 'Privado';
$labels['repeat'] = 'Repetir';
$labels['repeat_every'] = 'Repetir a cada';
$labels['never'] = 'Nunca';
$labels['daily'] = 'Diariamente';
$labels['weekly'] = 'Semanalmente';
$labels['monthly'] = 'Mensal';
$labels['yearly'] = 'Anualmente';
$labels['days'] = 'dia(s)';
$labels['weeks'] = 'week(s)';
$labels['months'] = 'mês(es)';
$labels['years'] = 'ano(s)';
$labels['repeat_on'] = 'Repetir em';
$labels['repeat_by'] = 'Repetir por';
$labels['day_of_month'] = 'dia do mês';
$labels['day_of_week'] = 'dia da semana';
$labels['not_specified'] = 'Não especificado';
$labels['first'] = 'Primeiro';
$labels['second'] = 'Segundo';
$labels['third'] = 'Terceiro';
$labels['fourth'] = 'Quarto';
$labels['fifth'] = 'Quinto';
$labels['second_to_last'] = 'O penúltimo';
$labels['last'] = 'Último';
$labels['attachment_too_large'] = 'O arquivo anexo é muito grande. O tamanho máximo permitido é $s.';
$labels['bg_color'] = 'Cor de fundo';
$labels['tx_color'] = 'Cor do texto';
$labels['add_notification'] = 'Adicionar notificação';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'Este evento não possui notificações.';
$labels['minutes'] = 'minutos';
$labels['hours'] = 'horas';
$labels['link'] = 'Link';
$labels['attach_file_from_web'] = 'Anexar arquivo';
$labels['attachment_url'] = 'URL do anexo';
$labels['invalid_url'] = 'Este URL é inválido.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Duplicar';
$labels['duplicate_suffix'] = '(duplicata)';
$labels['no_notifications'] = 'Este evento não possui notificações.';
$labels['this_calendar_is_hidden'] = 'Este calendário está oculto. Os eventos salvos não serão exibidos até que o calendário fique visível.';
$labels['make_calendar_visible'] = 'Torne este calendário visível.';

// recurrence
$labels['recurrence_range'] = 'Intervalo de recorrência';
$labels['repeat_forever'] = 'Repetir para sempre';
$labels['repeat_every_weekday'] = 'Repetir todos os dias da semana';
$labels['last_day'] = 'Último dia';
$labels['every'] = 'Cada';
$labels['weekday'] = 'Dia da semana';
$labels['weekend_day'] = 'Dia de fim de semana';
$labels['day_of_week'] = 'dia da semana';
$labels['day_of_month'] = 'dia do mês';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Fim após';
$labels['occurrences'] = 'ocorrências';

// end recurrence on date:
$labels['end_by_date'] = 'Data de término';
$labels['removed_events_title'] = 'Eventos removidos de séries recorrentes';
$labels['restore'] = 'Restaurar';

// settings page
$labels['setting_view'] = 'Visualização inicial do calendário';
$labels['setting_first_day'] = 'A semana começa em';
$labels['setting_agenda_week_span'] = 'Intervalo de tempo da agenda (semanas)';
$labels['setting_slot_duration'] = 'Intervalo do dia (minutos)';
$labels['setting_scroll_time'] = 'Rolar até o horário';
$labels['setting_calendar'] = 'Calendário padrão para eventos';
$labels['setting_refresh'] = 'Atualizar calendários';
$labels['setting_week_numbers'] = 'Números da semana no calendário pequeno';
$labels['setting_event_border'] = 'Categorias de cores como bordas de eventos';
$labels['show_sunrise'] = 'Mostrar o nascer do sol na visualização do mês';
$labels['show_sunset'] = 'Mostrar pôr do sol na visualização do mês';
$labels['categories'] = 'Categorias';
$labels['category'] = 'Categoria';
$labels['no_category'] = 'Sem categoria';
$labels['add_category'] = 'Adicionar categoria';
$labels['setting_alarm_sound'] = 'Som de notificação';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambu';
$labels['alarm_sound_bells'] = 'Sinos';
$labels['alarm_sound_chord'] = 'Acorde';
$labels['alarm_sound_circles'] = 'Círculos';
$labels['alarm_sound_glass'] = 'Copo';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metal';
$labels['alarm_sound_ripple'] = 'Ondulação';
$labels['alarm_sound_startrek'] = 'Jornada nas Estrelas';
$labels['alarm_sound_whistle'] = 'Apito';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Tocar som';

// alarms
$labels['upcoming_events'] = 'Próximos eventos';
$labels['dismiss'] = 'Dispensar';
$labels['dismiss_all'] = 'Dispensar todos';
$labels['snooze'] = 'Soneca';
$labels['5_minutes'] = '5 minutos';
$labels['10_minutes'] = '10 minutos';
$labels['20_minutes'] = '20 minutos';
$labels['30_minutes'] = '30 minutos';
$labels['1_hour'] = '1 hora';
$labels['2_hours'] = '2 horas';
$labels['tomorrow'] = 'Até amanhã';
$labels['next_week'] = 'Até a próxima semana';

// sidebar
$labels['setting_show_xcalendar'] = 'Mostrar Agenda do Calendário';
$labels['todays_agenda'] = 'Agenda de hoje';
$labels['no_events_today'] = 'Nenhum evento agendado para hoje.';
$labels['sidebar_more'] = 'Mais...';

// event invitation
$labels['itip_title_request'] = 'Você foi convidado para um evento.';
$labels['itip_title_modified'] = 'Este evento foi modificado.';
$labels['itip_title_reply_accepted'] = '$u aceitou seu convite';
$labels['itip_title_reply_tentative'] = '$u aceitou provisoriamente seu convite';
$labels['itip_title_reply_declined'] = '$u recusou seu convite';
$labels['itip_title_cancel'] = 'Este evento foi cancelado.';

$labels['itip_email_subject_request'] = 'Convite:';
$labels['itip_email_subject_request_modified'] = 'Evento modificado:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Cancelado:';

$labels['itip_popup_event_added'] = 'Evento adicionado ao calendário.';
$labels['itip_popup_event_updated'] = 'Evento atualizado no calendário.';
$labels['itip_popup_event_deleted'] = 'Evento excluído do calendário.';
$labels['itip_popup_response_sent'] = 'Resposta enviada ao organizador.';

$labels['itip_message_invitation_accepted'] = 'Você aceitou este convite.';
$labels['itip_message_invitation_tentative'] = 'Você aceitou provisoriamente este convite.';
$labels['itip_message_invitation_declined'] = 'Você recusou este convite.';
$labels['itip_message_event_deleted'] = 'O evento foi excluído do seu calendário.';
$labels['itip_message_event_updated'] = 'O evento em seu calendário foi atualizado.';
$labels['itip_message_event_missing'] = 'Este evento não existe em nenhum de seus calendários.';

$labels['itip_accept'] = 'Aceitar';
$labels['itip_tentative'] = 'Provisório';
$labels['itip_decline'] = 'Recusar';
$labels['itip_change_reponse'] = 'Alterar resposta.';
$labels['itip_add_to_calendar'] = 'Adicionar ao calendário';
$labels['itip_reply_to_sender'] = 'Responder ao remetente';
$labels['itip_dont_import'] = 'Não adicionar ao calendário';
$labels['itip_update_in_calendar'] = 'Atualizar evento no calendário';
$labels['itip_delete_from_calendar'] = 'Excluir evento do calendário';

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
$labels['attendee_role'] = 'Função';
$labels['attendee_role_required'] = 'Obrigatório';
$labels['attendee_role_optional'] = 'Opcional';
$labels['attendee_role_chair'] = 'Cadeira';
$labels['attendee_role_none'] = 'Não participante';
$labels['organizer'] = 'Organizador';
$labels['attendance'] = 'Atendimento';
$labels['your_status'] = 'Seu status';
$labels['attendance_going'] = 'Vai?';
$labels['attendee_status_yes'] = 'Sim';
$labels['attendee_status_no'] = 'Não';
$labels['attendee_status_maybe'] = 'Talvez';
$labels['attendee_status_delegated'] = 'Delegado';
$labels['attendee_status_waiting'] = 'Aguardando';
$labels['attendee_status_waiting_for_confirmation'] = 'Aguardando confirmação';
$labels['attendee_status_info_yes'] = 'Irá participar';
$labels['attendee_status_info_no'] = 'Não participará';
$labels['attendee_status_info_maybe'] = 'Pode comparecer';
$labels['attendee_status_info_delegated'] = 'Presença delegada';
$labels['attendee_role'] = 'Função';
$labels['attendee_role_info'] = 'Especifique se o participante deve participar ou presidir a reunião.';
$labels['attendee_notify'] = 'Notificar';
$labels['attendee_notify_info'] = 'Se marcado, um e-mail de convite será enviado ao participante.';
$labels['add_attendee'] = 'Adicionar participante';
$labels['attendee_notify_question'] = 'Enviar e-mails de notificação aos participantes?';
$labels['attendee_notify_textarea'] = 'Adicionar uma mensagem opcional';
$labels['attendee_notify_send'] = 'Enviar';
$labels['attendee_notify_dont'] = 'Não enviar';
$labels['attendee_notify_cancel'] = 'Cancelar';
$labels['new_attendee_not_saved_warning'] = 'Você inseriu o endereço de e-mail de um participante, mas não o adicionou à lista de participantes. Deseja continuar salvando sem esse participante?';

// import / export
$labels['import_export'] = 'Importar / Exportar';
$labels['export_to_file'] = 'Exportar para arquivo';
$labels['export_info'] = 'Clique neste botão para exportar e baixar os eventos deste calendário no formato iCalendar (ics). Se o calendário contiver muitos eventos, o processo de exportação pode demorar alguns minutos.';
$labels['export_no_events'] = 'Este calendário não tem eventos para exportar.';
$labels['import_from_file'] = 'Importar do arquivo';
$labels['import_info'] = 'Clique neste botão para importar eventos para este calendário de um arquivo iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Lembrete de compromisso';
$labels['email_notification_subject'] = 'Lembrete: $t em $d ($z)';
$labels['email_notification_cron_error'] = 'As notificações por e-mail estão desabilitadas. Contate o administrador.';
$labels['alarm_before_event_starts'] = 'Antes do início do evento';
$labels['alarm_after_event_starts'] = 'Após o início do evento';
$labels['alarm_before_event_ends'] = 'Antes do evento terminar';
$labels['alarm_after_event_ends'] = 'Após o evento terminar';
$labels['alarm_at_specific_time'] = 'Em um horário específico';

// search
$labels['specify_at_least_one_calendar'] = 'Especifique pelo menos um calendário para pesquisar.';
$labels['specify_text_to_search'] = 'Digite o texto a ser pesquisado.';
$labels['clear_search_results'] = 'Limpo';
$labels['search_outdated_message'] = 'Os resultados da pesquisa podem estar desatualizados. Considere a possibilidade de executar sua pesquisa novamente.';
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
