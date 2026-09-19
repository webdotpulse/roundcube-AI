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
$labels['create_event'] = 'Nuovo Evento';
$labels['create_event_button'] = 'Nuovo Evento';
$labels['day'] = 'Giorno';
$labels['week'] = 'Settimana';
$labels['month'] = 'Mese';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Calendari';
$labels['my_calendar'] = 'Calendario personale';
$labels['create_calendar'] = 'Crea calendario';
$labels['new_calendar'] = 'Nuovo calendario';
$labels['edit_calendar'] = 'Modifica calendario';
$labels['remove_calendar'] = 'Cancella calendario';
$labels['confirm_remove_calendar'] = 'Sei sicuro di voler cancellare questo calendario?';
$labels['calendar_removed'] = 'Calendario cancellato correttamente.';
$labels['notifications'] = 'Notifiche';
$labels['default_event_visibility'] = 'Visualizzazione di default';
$labels['time_zone'] = 'Fuso orario del calendario';
$labels['share_calendar'] = 'Condividi calendario';
$labels['permissions'] = 'Permessi';
$labels['permission_publish_calendar'] = 'Pubblica calendario';
$labels['permission_share_calendar'] = 'Può condividere il calendario';
$labels['permission_edit_events'] = 'Può modificare gli appuntamenti';
$labels['permission_see_details'] = 'Vedi dettagli';
$labels['permission_publish_calendar_info'] = 'Se selezionato, l\'utente potrà pubblicare il calendario.';
$labels['permission_share_calendar_info'] = 'Se selezionato, l\'utente sarà in grado di condividere il calendario con altri utenti.';
$labels['permission_edit_events_info'] = 'Se selezionato, l\'utente potrà modificare ed eliminare gli eventi del calendario.';
$labels['permission_see_details_info'] = 'Se selezionato, tutti i dettagli dell\'evento verranno condivisi. Se deselezionato, verranno condivise solo le informazioni disponibile/occupato.';
$labels['share_send_notification'] = 'Notifica';
$labels['share_send_notification_info'] = 'Se selezionato, verrà inviata un\'email all\'utente per informarlo del calendario appena condiviso.';
$labels['new_share_not_saved_warning'] = 'Hai digitato un\'email con cui condividere il calendario ma non hai fatto clic sul pulsante Condividi per aggiungerlo all\'elenco condiviso. Continuare a salvare senza condividere il calendario con l\'utente specificato?';
$labels['share'] = 'Condividi';
$labels['shared_marker'] = '(Condiviso)';
$labels['owner'] = 'Proprietario';
$labels['publish_calendar'] = 'Pubblica il calendario';
$labels['create_link'] = 'Crea collegamento';
$labels['reset'] = 'Resetta';
$labels['remove'] = 'Rimuovi';
$labels['publish_full'] = 'Pubblica tutte le informazioni degli eventi.';
$labels['publish_busy'] = 'Pubblica solo le informazioni sulla disponibilità.';
$labels['sync_no_id'] = 'Le opzioni CalDAV diventeranno disponibili dopo aver salvato il calendario.';
$labels['share_no_id'] = 'Le opzioni di condivisione del calendario diventeranno disponibili dopo aver salvato il calendario.';
$labels['publish_no_id'] = 'L\'opzione di pubblicazione sarà attiva dopo aver salvato il calendario.';
$labels['import_no_id'] = 'Le opzioni di importazione ed esportazione saranno disponibili dopo aver salvato il calendario.';
$labels['unable_to_save_calendar_data'] = 'Impossibile salvare i dati nel calendario.';
$labels['unable_to_retrieve_calendar_data'] = 'Impossibile recuperare i dati del calendario. Il calendario potrebbe non essere più disponibile.';
$labels['unable_add_shared_calendar'] = 'Impossibile aggiungere il calendario condiviso. Il calendario potrebbe non essere più disponibile.';
$labels['itip_organizer_message'] = 'Sei stato invitato a questo evento ma non sei l\'organizzatore. Le modifiche apportate a questo evento non verranno inviate agli altri partecipanti.';
$labels['cannot_share_with_yourself'] = 'Non puoi condividere il tuo calendario con te stesso.';
$labels['cannot_share_with_calendar_owner'] = 'Non puoi condividere questo calendario con il proprietario del calendario ($e)';
$labels['setting_default_notification_type'] = 'Notifiche predefinite per i nuovi eventi';
$labels['calendar_name_cannot_be_empty'] = 'Il nome del calendario non può essere vuoto.';

// caldav server
$labels['sync_calendar'] = 'Sincronizza il calendario';
$labels['password_too_short'] = 'La password deve contenere almeno 10 caratteri';
$labels['read_only'] = 'Sola lettura';
$labels['add_connection'] = 'Aggiungi connessione';
$labels['show_details'] = 'Visualizza dettagli';
$labels['hide_details'] = 'Nascondi dettagli';
$labels['caldav_connection_password'] = 'La password specificata durante la creazione di questa connessione.';
$labels['yes'] = 'Si';
$labels['no'] = 'No';
$labels['confirm_remove_sync'] = 'Sei sicuro di voler rimuovere definitivamente questa connessione?';
$labels['remove_sync_error'] = 'Si è verificato un errore durante la rimozione della connessione.';
$labels['create_new_caldav_connection'] = 'Crea una nuova connessione CalDav';
$labels['your_caldav_connections'] = 'Le tue connessioni CalDav';
$labels['created_at'] = 'Creato';
$labels['connected_at'] = 'Ultima sincronizzazione';
$labels['never'] = 'Mai';
$labels['alternate_url'] = 'URL Alternativo';
$labels['caldav_password_info'] = 'Per motivi di sicurezza questa password non viene salvata sul server. Se l\'hai dimenticata, elimina questa connessione e creane una nuova.';
$labels['caldav_url_info'] = 'Utilizza questo URL con le app che supportano l\'individuazione del calendario, ad esempio: iOS e Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Utilizza questo URL con le app che non funzionano con l\'URL breve sopra, ad esempio: Thunderbird Lightning. (Questo URL non funzionerà con iOS e OSX Calendar.)';
$labels['invalid_caldav_url'] = 'URL del server CalDAV non valido.';
$labels['cannot_resolve_caldav_host'] = 'Impossibile risolvere l\'host del server CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'Non sono ammessi indirizzi CalDAV privati e riservati.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Collega un calendario condiviso';
$labels['shared_calendars'] = 'Calendari condivisi';
$labels['no_shared_calendars'] = 'Non ci sono calendari condivisi a cui puoi collegarti.';
$labels['add_to_my_calendars'] = 'Aggiungi ai miei calendari';
$labels['remove_from_my_calendars'] = 'Rimuovi dai miei calendari';
$labels['unshare'] = 'Rimuovi condivisione';
$labels['unshare_warning'] = 'Se togli la condivisione, questo calendario scomparirà dalla lista e non potrà più essere aggiunto.';
$labels['calendar_name'] = 'Nome calendario';
$labels['calendar_owner'] = 'Proprietario';
$labels['shared_date'] = 'Condiviso il';
$labels['unable_to_share'] = 'L\'indirizzo email scelto non appartiene a questo server pertanto l\'opzione di condivisione non può essere attivata.';

// shared calendar email
$labels['shared_email_subject'] = '$u ha condiviso il suo calendario';
$labels['shared_email_body'] = 'L\'utente $u ha autorizzato l\'accesso al suo calendario "$c." Per aggiungere questo calendario alla tua lista di calendari attivi, clicca il link qui sotto:';
$labels['shared_email_body_cpanel'] = 'L\'utente $u ha autorizzato l\'accesso al suo calendario "$c." Per aggiungere questo calendario alla tua lista di calendari attivi effettua l\'accesso alla Webmail, vai nel Calendario, clicca l\'icona con il "+" sulla barra laterale e scegli "Connetti calendario condiviso".';

// adding holiday calendars
$labels['holiday_calendars'] = 'Calendari delle vacanze';
$labels['edit_holiday_calendar'] = 'Modifica del calendario delle vacanze';
$labels['religious_holidays'] = 'Feste religiose';
$labels['country_holidays'] = 'Feste nazionali';
$labels['add'] = 'Aggiungi';
$labels['no_more_holidays_allowed'] = 'Non puoi aggiungere altri calendari per le ferie.';
$labels['cannot_load_holidays'] = 'Impossibile caricare le vacanze dal server di Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Aggiungere il calendario dei compleanni';
$labels['edit_birthday_calendar'] = 'Modifica del calendario dei compleanni';
$labels['contact_birthdays'] = 'Contatto Compleanni';
$labels['contact_birthdays_info'] = 'Questo calendario recupera le informazioni sui compleanni dall\'elenco dei contatti e le visualizza come eventi del calendario.';
$labels['birthday_event_title'] = 'Compleanno: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Aggiungi Google calendar';
$labels['edit_google_calendar'] = 'Modifica del calendario di Google';
$labels['google_id'] = 'Google calendar ID';
$labels['new_google_calendar'] = 'Nuovo Google Calendar';
$labels['specify_google_calendar_id'] = 'Si prega di inserire il calendar ID di Google';
$labels['calendar_id_format_error'] = 'Google calendar ID dovrebbe includere il carattere @.';
$labels['cannot_load_google_calendar'] = 'Il calendario Google "$c" non può essere caricato. Assicurarsi che l\'ID del calendario sia corretto e che il calendario sia stato reso pubblico.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Aggiungere il calendario CalDAV';
$labels['edit_caldav_calendar'] = 'Modifica del calendario CalDAV';
$labels['add_caldav_calendar_information'] = 'Per iscriversi a un calendario CalDAV, specificare le informazioni del server CalDAV e fare clic su Trova calendari.';
$labels['caldav_url'] = 'URL CalDAV';
$labels['find_calendars'] = 'Trova i calendari';
$labels['searching_for_calendars'] = 'Ricerca dei calendari, attendere...';
$labels['select_calendars'] = 'Selezionare i calendari da aggiungere:';
$labels['read_only_status_yes'] = 'Sì';
$labels['read_only_status_no'] = 'No';
$labels['subscribed'] = 'Abbonati';

$labels['refresh_calendar'] = 'Aggiorna il calendario';
$labels['refresh_every_minute'] = 'Ogni minuto';
$labels['refresh_every_5_minutes'] = 'Ogni 5 minuti';
$labels['refresh_every_15_minutes'] = 'Ogni 15 minuti';
$labels['refresh_every_30_minutes'] = 'Ogni 30 minuti';
$labels['refresh_every_60_minutes'] = 'Ogni 60 minuti';
$labels['refresh_never'] = 'Mai';

$labels['cannot_connect_to_caldav_server'] = 'Impossibile connettersi al server CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Impossibile connettersi al server CalDAV. Controllare l\'URL del server.';
$labels['caldav_client_error_credentials'] = 'Nome utente o password CalDAV non corretti.';
$labels['caldav_client_error_empty'] = 'Il server non ha restituito alcun calendario che possa essere aggiunto. Controllare l\'URL CalDAV.';
$labels['caldav_client_error_server_info'] = 'Informazioni sul server CalDAV non corrette.';
$labels['caldav_client_error_add_calendar'] = 'Impossibile salvare il calendario "$n".';
$labels['caldav_client_error_delete_event'] = 'Impossibile eliminare l\'evento';
$labels['caldav_client_error_save_event'] = 'Impossibile salvare l\'evento CalDAV: potrebbe essere stato spostato o eliminato, oppure il calendario non è disponibile.';
$labels['caldav_client_error_calendar_added'] = 'Il calendario "$n" è già presente nell\'elenco dei calendari.';

// previewing event
$labels['remove_recurring_event'] = 'Cancella l\'evento ricorrente';
$labels['remove_one'] = 'Cancella sola questa istanza';
$labels['remove_all'] = 'Cancella tutte le istanze';
$labels['edit_event'] = 'Modifica evento';
$labels['event_removed'] = 'Evento eliminato.';
$labels['go_to_url'] = 'Vai alla URL';
$labels['confirm_remove_event'] = 'È sicuro di voler cancellare questo evento?';

// calendar/agenda grid
$labels['no_events'] = 'Non ci sono eventi da mostrare.';

// importing events
$labels['select_calendar'] = 'Seleziona il calendario';
$labels['select_calendar_for_events'] = 'Seleziona il calendario nel quale vuoi creare l\'evento:';
$labels['add_events'] = 'Aggiungi Evento';
$labels['add_events_to_calendar'] = 'Aggiungi evento al calendario';
$labels['events_imported_successfully'] = 'Tutti gli eventi sono stati importati correttamente.';
$labels['events_imported_with_errors'] = 'Alcuni eventi sono stati importati ma si sono verificati degli errori.';
$labels['error_importing_events'] = 'Si è verificato un errore durante l\'importazione degli eventi nel calendario';

// editing events
$labels['calendar'] = 'Calendario';
$labels['new_event'] = 'Nuovo evento';
$labels['summary'] = 'Sommario';
$labels['recurrence'] = 'Ricorrenza';
$labels['title'] = 'Titolo';
$labels['location'] = 'Luogo';
$labels['description'] = 'Descrizione';
$labels['comment'] = 'Commento';
$labels['url'] = 'URL';
$labels['name'] = 'Nome';
$labels['start'] = 'Inizio';
$labels['end'] = 'Fine';
$labels['show_timezones'] = 'Mostra fusi orari';
$labels['reset_timezones'] = 'Ripristina i fusi orari predefiniti';
$labels['all_day'] = 'Tutto il giorno';
$labels['use_calendar_colors'] = 'Usa colore corrente';
$labels['color'] = 'Colore';
$labels['background'] = 'Sfondo';
$labels['text'] = 'Testo';
$labels['show_me_as'] = 'Visualizza come';
$labels['available'] = 'Disponibile';
$labels['busy'] = 'Occupato';
$labels['visibility'] = 'Visibile';
$labels['calendar_default'] = 'Impostazione di default';
$labels['public'] = 'Pubblico';
$labels['private'] = 'Privato';
$labels['repeat'] = 'Ripeti';
$labels['repeat_every'] = 'Ripeti ogni';
$labels['never'] = 'Mai';
$labels['daily'] = 'Ogni giorno';
$labels['weekly'] = 'Ogni settimana';
$labels['monthly'] = 'Ogni mese';
$labels['yearly'] = 'Ogni anno';
$labels['days'] = 'giorno(i)';
$labels['weeks'] = 'settimana(e)';
$labels['months'] = 'mese(i)';
$labels['years'] = 'anno(i)';
$labels['repeat_on'] = 'Ripeti il';
$labels['repeat_by'] = 'Ripeti da';
$labels['day_of_month'] = 'giorno del mese';
$labels['day_of_week'] = 'giorno della settimana';
$labels['not_specified'] = 'Non specificato';
$labels['first'] = 'Primo';
$labels['second'] = 'Secondo';
$labels['third'] = 'Terzo';
$labels['fourth'] = 'Quarto';
$labels['fifth'] = 'Quinto';
$labels['second_to_last'] = 'Penultimo posto';
$labels['last'] = 'Ultimo';
$labels['attachment_too_large'] = 'L\'allegato è troppo grande. La dimensione massima è $s.';
$labels['bg_color'] = 'Colore di sfondo';
$labels['tx_color'] = 'Colore del testo';
$labels['add_notification'] = 'Aggiungi notifica';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'Questo evento non ha notifiche.';
$labels['minutes'] = 'minuti';
$labels['hours'] = 'ore';
$labels['link'] = 'Collegamento';
$labels['attach_file_from_web'] = 'Allegare il file';
$labels['attachment_url'] = 'URL allegato';
$labels['invalid_url'] = 'Questo URL non è valido.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Duplica';
$labels['duplicate_suffix'] = '(duplicato)';
$labels['no_notifications'] = 'Questo evento non ha notifiche.';
$labels['this_calendar_is_hidden'] = 'Questo calendario è nascosto. Gli eventi salvati non verranno visualizzati finché il calendario non sarà visibile.';
$labels['make_calendar_visible'] = 'Rendi visibile questo calendario.';

// recurrence
$labels['recurrence_range'] = 'Intervallo di ricorrenza';
$labels['repeat_forever'] = 'Ripetere per sempre';
$labels['repeat_every_weekday'] = 'Ripetere ogni giorno della settimana';
$labels['last_day'] = 'Ultimo giorno';
$labels['every'] = 'Ogni';
$labels['weekday'] = 'Giorno feriale';
$labels['weekend_day'] = 'Giorno del fine settimana';
$labels['day_of_week'] = 'giorno della settimana';
$labels['day_of_month'] = 'giorno del mese';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Fine dopo';
$labels['occurrences'] = 'Occorrenze';

// end recurrence on date:
$labels['end_by_date'] = 'Fine per data';
$labels['removed_events_title'] = 'Eventi rimossi dalle serie ricorrenti';
$labels['restore'] = 'Ripristina';

// settings page
$labels['setting_view'] = 'Visualizzazione iniziale';
$labels['setting_first_day'] = 'Giorno in cui inizia la settimana';
$labels['setting_agenda_week_span'] = 'Durata dell\'agenda (settimane)';
$labels['setting_slot_duration'] = 'Durata fascia oraria (min)';
$labels['setting_scroll_time'] = 'Ora di inizio giornata';
$labels['setting_calendar'] = 'Calendario di default per gli eventi';
$labels['setting_refresh'] = 'Aggiornare i calendari';
$labels['setting_week_numbers'] = 'Mostra il numero della settimana nel calendario piccolo';
$labels['setting_event_border'] = 'Colora il bordo degli eventi con il colore della categoria';
$labels['show_sunrise'] = 'Mostra alba';
$labels['show_sunset'] = 'Mostra tramonto';
$labels['categories'] = 'Categorie';
$labels['category'] = 'Categoria';
$labels['no_category'] = 'Nessuna categoria';
$labels['add_category'] = 'Aggiungi categoria';
$labels['setting_alarm_sound'] = 'Suono di notifica';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bamboo';
$labels['alarm_sound_bells'] = 'Bells';
$labels['alarm_sound_chord'] = 'Chord';
$labels['alarm_sound_circles'] = 'Circles';
$labels['alarm_sound_glass'] = 'Glass';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metal';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Whistle';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Riproduci suono';

// alarms
$labels['upcoming_events'] = 'Prossimi eventi';
$labels['dismiss'] = 'Annulla';
$labels['dismiss_all'] = 'Annulla tutti';
$labels['snooze'] = 'Posticipa';
$labels['5_minutes'] = '5 minuti';
$labels['10_minutes'] = '10 minuti';
$labels['20_minutes'] = '20 minuti';
$labels['30_minutes'] = '30 minuti';
$labels['1_hour'] = '1 ora';
$labels['2_hours'] = '2 ore';
$labels['tomorrow'] = 'Rimanda a domani';
$labels['next_week'] = 'Rimanda alla prossima settimana';

// sidebar
$labels['setting_show_xcalendar'] = 'Mostra gli appuntamenti calendarizzati';
$labels['todays_agenda'] = 'Appuntamenti di Oggi';
$labels['no_events_today'] = 'Nessun appuntamento per oggi.';
$labels['sidebar_more'] = 'Visualizza tutto...';

// event invitation
$labels['itip_title_request'] = 'Sei invitato ad un evento.';
$labels['itip_title_modified'] = 'Questo evento è stato modificato.';
$labels['itip_title_reply_accepted'] = '$u ha accettato il tuo invito';
$labels['itip_title_reply_tentative'] = '$u ha provvisoriamente accettato il tuo invito';
$labels['itip_title_reply_declined'] = '$u ha rifiutato il tuo invito';
$labels['itip_title_cancel'] = 'Questo evento è stato annullato.';

$labels['itip_email_subject_request'] = 'Invito:';
$labels['itip_email_subject_request_modified'] = 'Evento modificato:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Annullato:';

$labels['itip_popup_event_added'] = 'Evento aggiunto al calendario.';
$labels['itip_popup_event_updated'] = 'Evento aggiornato nel calendario.';
$labels['itip_popup_event_deleted'] = 'Evento cancellato dal calendario.';
$labels['itip_popup_response_sent'] = 'Risposta inviata all\'organizzatore.';

$labels['itip_message_invitation_accepted'] = 'Hai accettato questo invito.';
$labels['itip_message_invitation_tentative'] = 'Hai provvisoriamente accettato questo invito.';
$labels['itip_message_invitation_declined'] = 'Hai rifiutato questo invito.';
$labels['itip_message_event_deleted'] = 'L\'evento è stato cancellato dal tuo calendario.';
$labels['itip_message_event_updated'] = 'L\'evento nel tuo calendario è stato aggiornato.';
$labels['itip_message_event_missing'] = 'Questo evento non esiste in nessuno dei tuoi calendari.';

$labels['itip_accept'] = 'Accetta';
$labels['itip_tentative'] = 'Provvisorio';
$labels['itip_decline'] = 'Rifiuta';
$labels['itip_change_reponse'] = 'Cambia risposta.';
$labels['itip_add_to_calendar'] = 'Aggiungi al calendario';
$labels['itip_reply_to_sender'] = 'Rispondi al mittente';
$labels['itip_dont_import'] = 'Non aggiungere al calendario';
$labels['itip_update_in_calendar'] = 'Aggiorna l\'evento nel calendario';
$labels['itip_delete_from_calendar'] = 'Elimina l\'evento dal calendario';

$labels['mail_subject_invitation'] = 'Invito:';
$labels['mail_subject_update'] = 'Invito aggiornato:';
$labels['mail_subject_cancel'] = 'Evento annullato:';

// event email notifications
$labels['mail_title'] = 'Titolo:';
$labels['mail_start'] = 'Inizio:';
$labels['mail_end'] = 'Fine:';
$labels['mail_description'] = 'Descrizione:';
$labels['mail_location'] = 'Posizione:';

// event attendees
$labels['attendees'] = 'Partecipanti';
$labels['attendee_role'] = 'Ruolo';
$labels['attendee_role_required'] = 'Richiesto';
$labels['attendee_role_optional'] = 'Opzionale';
$labels['attendee_role_chair'] = 'Presidente';
$labels['attendee_role_none'] = 'Non partecipante';
$labels['organizer'] = 'Organizzatore';
$labels['attendance'] = 'Presenza';
$labels['your_status'] = 'Il tuo stato';
$labels['attendance_going'] = 'Partecipi?';
$labels['attendee_status_yes'] = 'Si';
$labels['attendee_status_no'] = 'No';
$labels['attendee_status_maybe'] = 'Forse';
$labels['attendee_status_delegated'] = 'Delegato';
$labels['attendee_status_waiting'] = 'In attesa';
$labels['attendee_status_waiting_for_confirmation'] = 'In attesa di conferma';
$labels['attendee_status_info_yes'] = 'Parteciperà';
$labels['attendee_status_info_no'] = 'Non parteciperà';
$labels['attendee_status_info_maybe'] = 'Potrebbe partecipare';
$labels['attendee_status_info_delegated'] = 'Partecipazione delegata';
$labels['attendee_role'] = 'Ruolo';
$labels['attendee_role_info'] = 'Specificare se il partecipante deve partecipare o presiedere alla riunione.';
$labels['attendee_notify'] = 'Notifica';
$labels['attendee_notify_info'] = 'Se selezionato, verrà inviata un\'email di invito al partecipante.';
$labels['add_attendee'] = 'Aggiungi partecipante';
$labels['attendee_notify_question'] = 'Inviare email di notifica ai partecipanti?';
$labels['attendee_notify_textarea'] = 'Aggiungi un messaggio facoltativo';
$labels['attendee_notify_send'] = 'Inviare';
$labels['attendee_notify_dont'] = 'Non inviare';
$labels['attendee_notify_cancel'] = 'Annulla';
$labels['new_attendee_not_saved_warning'] = 'Hai inserito l\'indirizzo e-mail di un partecipante ma non l\'hai aggiunto all\'elenco dei partecipanti. Vuoi procedere con il salvataggio senza questo partecipante?';

// import / export
$labels['import_export'] = 'Importa / Esporta';
$labels['export_to_file'] = 'Esporta su file';
$labels['export_info'] = 'Fare clic su questo pulsante per esportare e scaricare gli eventi di questo calendario nel formato iCalendar (ics). Se il calendario contiene molti eventi, il processo di esportazione potrebbe richiedere alcuni istanti.';
$labels['export_no_events'] = 'Questo calendario non ha eventi da esportare.';
$labels['import_from_file'] = 'Importa da file';
$labels['import_info'] = 'Fai clic su questo pulsante per importare eventi in questo calendario da un file iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Promemoria dell\'appuntamento';
$labels['email_notification_subject'] = 'Promemoria: $t alle $d ($z)';
$labels['email_notification_cron_error'] = 'Le notifiche email sono disabilitate. Contattare l\'amministratore.';
$labels['alarm_before_event_starts'] = 'Prima dell\'inizio dell\'evento';
$labels['alarm_after_event_starts'] = 'Dopo l\'inizio dell\'evento';
$labels['alarm_before_event_ends'] = 'Prima della fine dell\'evento';
$labels['alarm_after_event_ends'] = 'Dopo la fine dell\'evento';
$labels['alarm_at_specific_time'] = 'In un momento specifico';

// search
$labels['specify_at_least_one_calendar'] = 'Specificare almeno un calendario da ricercare.';
$labels['specify_text_to_search'] = 'Digitare il testo da cercare.';
$labels['clear_search_results'] = 'Libero';
$labels['search_outdated_message'] = 'I risultati della ricerca potrebbero essere obsoleti. Considerate la possibilità di eseguire nuovamente la ricerca.';
$labels['date_range'] = 'Intervallo di date';
$labels['date_range_subtitle'] = 'Intervallo di date:';
$labels['search_results'] = 'Risultati trovati: $n';
$labels['modify_search_options'] = 'Provare a modificare le opzioni di ricerca.';
$labels['show_shortcuts'] = 'Mostra le scorciatoie';
$labels['hide_shortcuts'] = 'Nascondere le scorciatoie';
$labels['this_month'] = 'Questo mese';
$labels['this_year'] = 'Quest\'anno';
$labels['past_30_days'] = 'Ultimi 30 giorni';
$labels['next_30_days'] = 'I prossimi 30 giorni';
$labels['past_6_months'] = 'Ultimi 6 mesi';
$labels['next_6_months'] = 'I prossimi 6 mesi';

// calendar grid
$labels['cannot_load_events'] = 'Impossibile caricare gli eventi per il calendario "$c".';
