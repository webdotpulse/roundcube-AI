<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Calendar';
$labels['app_menu_title'] = 'Calendar';

// menu
$labels['create_event'] = 'Creați un eveniment';
$labels['create_event_button'] = 'Eveniment nou';
$labels['day'] = 'Ziua';
$labels['week'] = 'Săptămâna';
$labels['month'] = 'Luna';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Calendare';
$labels['my_calendar'] = 'Calendarul meu';
$labels['create_calendar'] = 'Creați un calendar';
$labels['new_calendar'] = 'Calendar nou';
$labels['edit_calendar'] = 'Editați calendarul';
$labels['remove_calendar'] = 'Ștergeți calendarul';
$labels['confirm_remove_calendar'] = 'Sunteți sigur că doriți să ștergeți acest calendar?';
$labels['calendar_removed'] = 'Calendar șters.';
$labels['notifications'] = 'Notificări';
$labels['default_event_visibility'] = 'Vizibilitatea implicită a evenimentului';
$labels['time_zone'] = 'Fusul orar al calendarului';
$labels['share_calendar'] = 'Calendarul de partajare';
$labels['permissions'] = 'Permisiuni';
$labels['permission_publish_calendar'] = 'Publicarea calendarului';
$labels['permission_share_calendar'] = 'Calendarul de partajare';
$labels['permission_edit_events'] = 'Editarea evenimentelor';
$labels['permission_see_details'] = 'Vezi detalii';
$labels['permission_publish_calendar_info'] = 'Dacă este bifată, utilizatorul va putea publica calendarul.';
$labels['permission_share_calendar_info'] = 'Dacă este bifată, utilizatorul va putea partaja calendarul cu alți utilizatori.';
$labels['permission_edit_events_info'] = 'Dacă este bifată, utilizatorul va putea edita și șterge evenimentele din calendar.';
$labels['permission_see_details_info'] = 'Dacă se bifează, toate detaliile evenimentului vor fi partajate. Dacă nu este bifat, vor fi partajate doar informațiile disponibile/ocupate.';
$labels['share_send_notification'] = 'Notificare';
$labels['share_send_notification_info'] = 'Dacă se bifează această opțiune, se va trimite un e-mail utilizatorului pentru a-l informa cu privire la noul calendar partajat.';
$labels['new_share_not_saved_warning'] = 'Ați tastat un e-mail cu care să partajați calendarul, dar nu ați făcut clic pe butonul Partajare pentru a-l adăuga la lista partajată. Continuați salvarea fără a partaja calendarul cu utilizatorul specificat?';
$labels['share'] = 'Share';
$labels['shared_marker'] = '(Partajat)';
$labels['owner'] = 'Proprietar';
$labels['publish_calendar'] = 'Publicarea calendarului';
$labels['create_link'] = 'Creați un link';
$labels['reset'] = 'Resetare';
$labels['remove'] = 'Eliminați';
$labels['publish_full'] = 'Publicați toate informațiile despre eveniment.';
$labels['publish_busy'] = 'Publicați doar informațiile despre liber/ocupat.';
$labels['sync_no_id'] = 'Opțiunile CalDAV vor deveni disponibile după ce salvați calendarul.';
$labels['share_no_id'] = 'Opțiunile de partajare a calendarului vor deveni disponibile după ce salvați calendarul.';
$labels['publish_no_id'] = 'Opțiunile de publicare a calendarului vor deveni disponibile după ce salvați calendarul.';
$labels['import_no_id'] = 'Opțiunile de import și export vor deveni disponibile după ce salvați calendarul.';
$labels['unable_to_save_calendar_data'] = 'Nu se pot salva datele din calendar.';
$labels['unable_to_retrieve_calendar_data'] = 'Nu se poate prelua datele din calendar. Este posibil ca calendarul să nu mai fie disponibil.';
$labels['unable_add_shared_calendar'] = 'Nu se poate adăuga calendarul partajat. Este posibil ca acest calendar să nu mai fie disponibil.';
$labels['itip_organizer_message'] = 'Ați fost invitat la acest eveniment, dar nu sunteți organizatorul. Modificările pe care le faceți la acest eveniment nu vor fi trimise celorlalți participanți.';
$labels['cannot_share_with_yourself'] = 'Nu vă puteți partaja propriul calendar cu dvs.';
$labels['cannot_share_with_calendar_owner'] = 'Nu puteți partaja acest calendar cu proprietarul calendarului ($e)';
$labels['setting_default_notification_type'] = 'Notificări implicite pentru evenimente noi';
$labels['calendar_name_cannot_be_empty'] = 'Numele calendarului nu poate fi gol.';

// caldav server
$labels['sync_calendar'] = 'Sincronizarea calendarului';
$labels['password_too_short'] = 'Parola trebuie să aibă cel puțin 10 caractere';
$labels['read_only'] = 'Numai pentru citire';
$labels['add_connection'] = 'Adăugați o conexiune';
$labels['show_details'] = 'Arată detalii';
$labels['hide_details'] = 'Ascundeți detaliile';
$labels['caldav_connection_password'] = 'Parola pe care ați specificat-o la crearea acestei conexiuni.';
$labels['yes'] = 'Da';
$labels['no'] = 'Nu';
$labels['confirm_remove_sync'] = 'Sunteți sigur că doriți să eliminați permanent această conexiune?';
$labels['remove_sync_error'] = 'S-a produs o eroare în timpul eliminării conexiunii.';
$labels['create_new_caldav_connection'] = 'Creați o nouă conexiune CalDav';
$labels['your_caldav_connections'] = 'Conexiunile dumneavoastră CalDav';
$labels['created_at'] = 'Creat';
$labels['connected_at'] = 'Ultima sincronizare';
$labels['never'] = 'Niciodată';
$labels['alternate_url'] = 'URL alternativ';
$labels['caldav_password_info'] = 'Din motive de securitate, această parolă nu este salvată pe server. Dacă ați uitat-o, ștergeți această conexiune și creați una nouă.';
$labels['caldav_url_info'] = 'Utilizați acest URL cu aplicațiile care acceptă descoperirea calendarului, de exemplu: iOS și Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Utilizați acest URL cu aplicații care nu funcționează cu url-ul scurt de mai sus, de exemplu: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'Adresă URL a serverului CalDAV nevalidă.';
$labels['cannot_resolve_caldav_host'] = 'Nu se poate identifica gazda serverului CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'Adresele CalDAV private și rezervate nu sunt permise.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Adăugați un calendar partajat';
$labels['shared_calendars'] = 'Calendare partajate';
$labels['no_shared_calendars'] = 'Nu există calendare partajate pe care să le puteți adăuga.';
$labels['add_to_my_calendars'] = 'Adaugă la calendarele mele';
$labels['remove_from_my_calendars'] = 'Elimină din calendarele mele';
$labels['unshare'] = 'Dezafectați';
$labels['unshare_warning'] = 'Dacă anulați partajarea acestui calendar, acesta va dispărea din această listă și nu îl veți mai putea adăuga din nou.';
$labels['calendar_name'] = 'Numele calendarului';
$labels['calendar_owner'] = 'Proprietarul calendarului';
$labels['shared_date'] = 'Partajat';
$labels['unable_to_share'] = 'Calendarul nu poate fi partajat cu următorii utilizatori, deoarece conturile lor de e-mail se află pe un server la distanță:';

// shared calendar email
$labels['shared_email_subject'] = '$u a partajat un calendar cu tine';
$labels['shared_email_body'] = 'Utilizatorul $u v-a dat acces la un calendar numit "$c". Pentru a adăuga acest calendar la lista de calendare active, faceți clic pe link-ul de mai jos:';
$labels['shared_email_body_cpanel'] = 'Utilizatorul $u v-a dat acces la un calendar numit "$c". Pentru a adăuga acest calendar la lista de calendare active, conectați-vă la Roundcube, accesați Calendar, faceți clic pe pictograma plus din bara laterală și selectați Adăugare calendar partajat.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Calendare de vacanță';
$labels['edit_holiday_calendar'] = 'Editați calendarul de vacanță';
$labels['religious_holidays'] = 'Sărbători religioase';
$labels['country_holidays'] = 'Vacanțe la țară';
$labels['add'] = 'Adăugați';
$labels['no_more_holidays_allowed'] = 'Nu mai puteți adăuga alte calendare de vacanță.';
$labels['cannot_load_holidays'] = 'Nu se pot încărca sărbătorile de pe serverul Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Adăugați Calendarul zilei de naștere';
$labels['edit_birthday_calendar'] = 'Editare calendar aniversar';
$labels['contact_birthdays'] = 'Contact Zile de naștere';
$labels['contact_birthdays_info'] = 'Acest calendar recuperează informațiile despre zilele de naștere din lista de contacte și le afișează ca evenimente din calendar.';
$labels['birthday_event_title'] = 'Ziua de naștere: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Adăugați calendarul Google';
$labels['edit_google_calendar'] = 'Editați calendarul Google';
$labels['google_id'] = 'ID-ul calendarului Google';
$labels['new_google_calendar'] = 'Noul Google Calendar';
$labels['specify_google_calendar_id'] = 'Vă rugăm să introduceți ID-ul calendarului Google';
$labels['calendar_id_format_error'] = 'ID-ul calendarului Google trebuie să includă caracterul @.';
$labels['cannot_load_google_calendar'] = 'Calendarul Google "$c" nu poate fi încărcat. Asigurați-vă că ID-ul calendarului este corect și că calendarul a fost pus la dispoziția publicului.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Adăugați calendarul CalDAV';
$labels['edit_caldav_calendar'] = 'Editați calendarul CalDAV';
$labels['add_caldav_calendar_information'] = 'Pentru a vă abona la un calendar CalDAV, specificați informațiile serverului CalDAV și faceți clic pe Find calendars (Găsește calendare).';
$labels['caldav_url'] = 'URL CalDAV';
$labels['find_calendars'] = 'Găsiți calendare';
$labels['searching_for_calendars'] = 'Căutarea calendarelor, vă rugăm să așteptați...';
$labels['select_calendars'] = 'Selectați calendarele pe care doriți să le adăugați:';
$labels['read_only_status_yes'] = 'Da';
$labels['read_only_status_no'] = 'Nu';
$labels['subscribed'] = 'Abonat';

$labels['refresh_calendar'] = 'Actualizați calendarul';
$labels['refresh_every_minute'] = 'Fiecare minut';
$labels['refresh_every_5_minutes'] = 'La fiecare 5 minute';
$labels['refresh_every_15_minutes'] = 'La fiecare 15 minute';
$labels['refresh_every_30_minutes'] = 'La fiecare 30 de minute';
$labels['refresh_every_60_minutes'] = 'La fiecare 60 de minute';
$labels['refresh_never'] = 'Niciodată';

$labels['cannot_connect_to_caldav_server'] = 'Nu se poate conecta la serverul CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Nu se poate conecta la serverul CalDAV. Verificați adresa URL a serverului.';
$labels['caldav_client_error_credentials'] = 'Nume de utilizator sau parolă CalDAV incorectă.';
$labels['caldav_client_error_empty'] = 'Serverul nu a returnat niciun calendar care poate fi adăugat. Verificați URL-ul CalDAV.';
$labels['caldav_client_error_server_info'] = 'Informații incorecte despre serverul CalDAV.';
$labels['caldav_client_error_add_calendar'] = 'Nu se poate salva calendarul "$n".';
$labels['caldav_client_error_delete_event'] = 'Nu se poate șterge evenimentul';
$labels['caldav_client_error_save_event'] = 'Nu se poate salva evenimentul CalDAV: este posibil ca acesta să fi fost mutat, șters sau ca calendarul să nu fie disponibil.';
$labels['caldav_client_error_calendar_added'] = 'Calendarul "$n" se află deja în lista de calendare.';

// previewing event
$labels['remove_recurring_event'] = 'Ștergeți evenimentul recurent';
$labels['remove_one'] = 'Ștergeți numai această instanță';
$labels['remove_all'] = 'Ștergeți toate instanțele';
$labels['edit_event'] = 'Editați evenimentul';
$labels['event_removed'] = 'Eveniment șters.';
$labels['go_to_url'] = 'Mergeți la URL';
$labels['confirm_remove_event'] = 'Sunteți sigur că doriți să ștergeți acest eveniment?';

// calendar/agenda grid
$labels['no_events'] = 'Nu există evenimente de afișat.';

// importing events
$labels['select_calendar'] = 'Selectați calendarul';
$labels['select_calendar_for_events'] = 'Selectați calendarul în care doriți să adăugați evenimentele:';
$labels['add_events'] = 'Adăugați evenimente';
$labels['add_events_to_calendar'] = 'Adăugați evenimente în calendar';
$labels['events_imported_successfully'] = 'Evenimente importate cu succes';
$labels['events_imported_with_errors'] = 'Un alt eveniment nu a putut fi importat.';
$labels['error_importing_events'] = 'S-a produs o eroare în timpul importului de evenimente în calendar';

// editing events
$labels['calendar'] = 'Calendar';
$labels['new_event'] = 'Eveniment nou';
$labels['summary'] = 'Rezumat';
$labels['recurrence'] = 'Recurență';
$labels['title'] = 'Titlu';
$labels['location'] = 'Locație';
$labels['description'] = 'Descriere';
$labels['comment'] = 'Comentariu';
$labels['url'] = 'URL';
$labels['name'] = 'Nume';
$labels['start'] = 'Start';
$labels['end'] = 'Sfârșitul';
$labels['show_timezones'] = 'Afișați fusurile orare';
$labels['reset_timezones'] = 'Resetați fusurile orare la cele implicite';
$labels['all_day'] = 'Toată ziua';
$labels['use_calendar_colors'] = 'Utilizați culorile calendarului';
$labels['color'] = 'Culoare';
$labels['background'] = 'Fond';
$labels['text'] = 'Text';
$labels['show_me_as'] = 'Arată-mi ca';
$labels['available'] = 'Disponibil';
$labels['busy'] = 'Ocupat';
$labels['visibility'] = 'Vizibilitate';
$labels['calendar_default'] = 'Calendar implicit';
$labels['public'] = 'Public';
$labels['private'] = 'Privat';
$labels['repeat'] = 'Repetați';
$labels['repeat_every'] = 'Se repetă la fiecare';
$labels['never'] = 'Niciodată';
$labels['daily'] = 'Daily';
$labels['weekly'] = 'Săptămânal';
$labels['monthly'] = 'Lunar';
$labels['yearly'] = 'Anual';
$labels['days'] = 'zi (zile)';
$labels['weeks'] = 'săptămână(e)';
$labels['months'] = 'luna (lunile)';
$labels['years'] = 'an(i)';
$labels['repeat_on'] = 'Repetați pe';
$labels['repeat_by'] = 'Repetați de';
$labels['day_of_month'] = 'ziua lunii';
$labels['day_of_week'] = 'ziua săptămânii';
$labels['not_specified'] = 'Nespecificat';
$labels['first'] = 'Prima';
$labels['second'] = 'Al doilea';
$labels['third'] = 'Al treilea';
$labels['fourth'] = 'Al patrulea';
$labels['fifth'] = 'Al cincilea';
$labels['second_to_last'] = 'Penultimul';
$labels['last'] = 'Ultima';
$labels['attachment_too_large'] = 'Fișierul atașat este prea mare. Dimensiunea maximă permisă este $s.';
$labels['bg_color'] = 'Culoarea de fundal';
$labels['tx_color'] = 'Culoarea textului';
$labels['add_notification'] = 'Adăugați o notificare';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'Acest eveniment nu are nicio notificare.';
$labels['minutes'] = 'minute';
$labels['hours'] = 'ore';
$labels['link'] = 'Link';
$labels['attach_file_from_web'] = 'Atașați fișierul';
$labels['attachment_url'] = 'Atașament URL';
$labels['invalid_url'] = 'Acest URL nu este valid.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Duplică';
$labels['duplicate_suffix'] = '(duplicat)';
$labels['no_notifications'] = 'Acest eveniment nu are nicio notificare.';
$labels['this_calendar_is_hidden'] = 'Acest calendar este ascuns. Evenimentele salvate nu vor fi afișate până când calendarul nu va fi vizibil.';
$labels['make_calendar_visible'] = 'Setează acest calendar ca fiind vizibil.';

// recurrence
$labels['recurrence_range'] = 'Intervalul de recurență';
$labels['repeat_forever'] = 'Repetă pentru totdeauna';
$labels['repeat_every_weekday'] = 'Se repetă în fiecare zi a săptămânii';
$labels['last_day'] = 'Ultima zi';
$labels['every'] = 'Fiecare';
$labels['weekday'] = 'În timpul săptămânii';
$labels['weekend_day'] = 'Zi de weekend';
$labels['day_of_week'] = 'ziua săptămânii';
$labels['day_of_month'] = 'ziua lunii';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Sfârșitul după';
$labels['occurrences'] = 'apariții';

// end recurrence on date:
$labels['end_by_date'] = 'Sfârșitul până la data';
$labels['removed_events_title'] = 'Evenimente eliminate din seriile recurente';
$labels['restore'] = 'Restaurați';

// settings page
$labels['setting_view'] = 'Vizualizarea inițială a calendarului';
$labels['setting_first_day'] = 'Săptămâna începe pe';
$labels['setting_agenda_week_span'] = 'Perioada de timp a agendei (săptămâni)';
$labels['setting_slot_duration'] = 'Intervalul orar al zilei (minute)';
$labels['setting_scroll_time'] = 'Derulați la timp';
$labels['setting_calendar'] = 'Calendar implicit pentru evenimente';
$labels['setting_refresh'] = 'Actualizarea calendarelor';
$labels['setting_week_numbers'] = 'Numerele săptămânilor în calendarul mic';
$labels['setting_event_border'] = 'Culori de categorie ca margini de eveniment';
$labels['show_sunrise'] = 'Afișați răsăritul soarelui în vizualizarea lunii';
$labels['show_sunset'] = 'Afișați apusul soarelui în vizualizarea lunii';
$labels['categories'] = 'Categorii';
$labels['category'] = 'Categoria';
$labels['no_category'] = 'Fără categorie';
$labels['add_category'] = 'Adăugați categoria';
$labels['setting_alarm_sound'] = 'Sunet de notificare';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambus';
$labels['alarm_sound_bells'] = 'Clopote';
$labels['alarm_sound_chord'] = 'Acorduri';
$labels['alarm_sound_circles'] = 'Cercuri';
$labels['alarm_sound_glass'] = 'Sticlă';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metal';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Fluier';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Redare sunet';

// alarms
$labels['upcoming_events'] = 'Evenimente viitoare';
$labels['dismiss'] = 'Respingeți';
$labels['dismiss_all'] = 'Respingeți toate';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minute';
$labels['10_minutes'] = '10 minute';
$labels['20_minutes'] = '20 de minute';
$labels['30_minutes'] = '30 de minute';
$labels['1_hour'] = '1 oră';
$labels['2_hours'] = '2 ore';
$labels['tomorrow'] = 'Până mâine';
$labels['next_week'] = 'Până săptămâna viitoare';

// sidebar
$labels['setting_show_xcalendar'] = 'Afișare Calendar Agenda';
$labels['todays_agenda'] = 'Agenda de astăzi';
$labels['no_events_today'] = 'Nu sunt evenimente programate pentru astăzi.';
$labels['sidebar_more'] = 'Mai mult...';

// event invitation
$labels['itip_title_request'] = 'Sunteți invitat la un eveniment.';
$labels['itip_title_modified'] = 'Acest eveniment a fost modificat.';
$labels['itip_title_reply_accepted'] = '$u a acceptat invitația dvs.';
$labels['itip_title_reply_tentative'] = '$u a acceptat provizoriu invitația dvs.';
$labels['itip_title_reply_declined'] = '$u a refuzat invitația dvs.';
$labels['itip_title_cancel'] = 'Acest eveniment a fost anulat.';

$labels['itip_email_subject_request'] = 'Invitație:';
$labels['itip_email_subject_request_modified'] = 'Eveniment modificat:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Anulat:';

$labels['itip_popup_event_added'] = 'Eveniment adăugat în calendar.';
$labels['itip_popup_event_updated'] = 'Eveniment actualizat în calendar.';
$labels['itip_popup_event_deleted'] = 'Eveniment șters din calendar.';
$labels['itip_popup_response_sent'] = 'Răspuns trimis organizatorului.';

$labels['itip_message_invitation_accepted'] = 'Ați acceptat această invitație.';
$labels['itip_message_invitation_tentative'] = 'Ați acceptat provizoriu această invitație.';
$labels['itip_message_invitation_declined'] = 'Ați refuzat această invitație.';
$labels['itip_message_event_deleted'] = 'Evenimentul a fost șters din calendarul dumneavoastră.';
$labels['itip_message_event_updated'] = 'Evenimentul din calendarul dvs. a fost actualizat.';
$labels['itip_message_event_missing'] = 'Acest eveniment nu există în niciunul dintre calendarele dumneavoastră.';

$labels['itip_accept'] = 'Acceptă';
$labels['itip_tentative'] = 'Tentativă';
$labels['itip_decline'] = 'Declin';
$labels['itip_change_reponse'] = 'Răspuns de modificare.';
$labels['itip_add_to_calendar'] = 'Adăugați la calendar';
$labels['itip_reply_to_sender'] = 'Răspundeți la expeditor';
$labels['itip_dont_import'] = 'Nu adăugați la calendar';
$labels['itip_update_in_calendar'] = 'Actualizarea evenimentului în calendar';
$labels['itip_delete_from_calendar'] = 'Ștergeți evenimentul din calendar';

$labels['mail_subject_invitation'] = 'Invitație:';
$labels['mail_subject_update'] = 'Invitație actualizată:';
$labels['mail_subject_cancel'] = 'Eveniment anulat:';

// event email notifications
$labels['mail_title'] = 'Titlu:';
$labels['mail_start'] = 'Începeți:';
$labels['mail_end'] = 'Sfârșit:';
$labels['mail_description'] = 'Descriere:';
$labels['mail_location'] = 'Locație:';

// event attendees
$labels['attendees'] = 'Participanți';
$labels['attendee_role'] = 'Rol';
$labels['attendee_role_required'] = 'Necesar';
$labels['attendee_role_optional'] = 'Opțional';
$labels['attendee_role_chair'] = 'Scaun';
$labels['attendee_role_none'] = 'Neparticipant';
$labels['organizer'] = 'Organizator';
$labels['attendance'] = 'Prezența';
$labels['your_status'] = 'Statutul dumneavoastră';
$labels['attendance_going'] = 'Pleci?';
$labels['attendee_status_yes'] = 'Da';
$labels['attendee_status_no'] = 'Nu';
$labels['attendee_status_maybe'] = 'Poate';
$labels['attendee_status_delegated'] = 'Delegat';
$labels['attendee_status_waiting'] = 'Așteptare';
$labels['attendee_status_waiting_for_confirmation'] = 'Așteptând confirmarea';
$labels['attendee_status_info_yes'] = 'Va participa';
$labels['attendee_status_info_no'] = 'Nu va participa';
$labels['attendee_status_info_maybe'] = 'Ar putea participa';
$labels['attendee_status_info_delegated'] = 'Prezența delegată';
$labels['attendee_role'] = 'Rol';
$labels['attendee_role_info'] = 'Precizați dacă participantul este obligat să participe sau să prezideze ședința.';
$labels['attendee_notify'] = 'Notificare';
$labels['attendee_notify_info'] = 'Dacă se bifează această opțiune, se va trimite un e-mail de invitație participantului.';
$labels['add_attendee'] = 'Adăugați un participant';
$labels['attendee_notify_question'] = 'Trimiteți e-mailuri de notificare participanților?';
$labels['attendee_notify_textarea'] = 'Adăugați un mesaj opțional';
$labels['attendee_notify_send'] = 'Trimiteți';
$labels['attendee_notify_dont'] = 'Nu trimiteți';
$labels['attendee_notify_cancel'] = 'Anulează';
$labels['new_attendee_not_saved_warning'] = 'Ați introdus adresa de e-mail a unui participant, dar nu l-ați adăugat pe lista participanților. Doriți să salvați fără acest participant?';

// import / export
$labels['import_export'] = 'Import / Export';
$labels['export_to_file'] = 'Export în fișier';
$labels['export_info'] = 'Faceți clic pe acest buton pentru a exporta și descărca evenimentele din acest calendar în formatul iCalendar (ics). În cazul în care calendarul conține multe evenimente, procesul de export poate dura câteva momente.';
$labels['export_no_events'] = 'Acest calendar nu are niciun eveniment de exportat.';
$labels['import_from_file'] = 'Import din fișier';
$labels['import_info'] = 'Faceți clic pe acest buton pentru a importa evenimente în acest calendar dintr-un fișier iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Reamintire de programare';
$labels['email_notification_subject'] = 'Reminder: $t la $d ($z)';
$labels['email_notification_cron_error'] = 'Notificările prin e-mail sunt dezactivate. Contactați administratorul.';
$labels['alarm_before_event_starts'] = 'Înainte de începerea evenimentului';
$labels['alarm_after_event_starts'] = 'După începerea evenimentului';
$labels['alarm_before_event_ends'] = 'Înainte ca evenimentul să se încheie';
$labels['alarm_after_event_ends'] = 'După încheierea evenimentului';
$labels['alarm_at_specific_time'] = 'La un moment dat';

// search
$labels['specify_at_least_one_calendar'] = 'Specificați cel puțin un calendar în care să se efectueze căutarea.';
$labels['specify_text_to_search'] = 'Introduceți textul care trebuie căutat.';
$labels['clear_search_results'] = 'Clar';
$labels['search_outdated_message'] = 'Rezultatele căutării pot fi depășite. Luați în considerare posibilitatea de a relua căutarea.';
$labels['date_range'] = 'Interval de date';
$labels['date_range_subtitle'] = 'Intervalul de date:';
$labels['search_results'] = 'Rezultate găsite: $n';
$labels['modify_search_options'] = 'Încercați să modificați opțiunile de căutare.';
$labels['show_shortcuts'] = 'Afișați comenzi rapide';
$labels['hide_shortcuts'] = 'Ascundeți comenzile rapide';
$labels['this_month'] = 'Luna aceasta';
$labels['this_year'] = 'În acest an';
$labels['past_30_days'] = 'Ultimele 30 de zile';
$labels['next_30_days'] = 'Următoarele 30 de zile';
$labels['past_6_months'] = 'Ultimele 6 luni';
$labels['next_6_months'] = 'Următoarele 6 luni';

// calendar grid
$labels['cannot_load_events'] = 'Nu se pot încărca evenimente pentru calendarul "$c".';
