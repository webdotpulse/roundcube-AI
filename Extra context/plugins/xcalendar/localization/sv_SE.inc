<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Kalender';
$labels['app_menu_title'] = 'Kalender';

// menu
$labels['create_event'] = 'Skapa evenemang';
$labels['create_event_button'] = 'Nytt evenemang';
$labels['day'] = 'Dag';
$labels['week'] = 'Veckan';
$labels['month'] = 'Månad';
$labels['agenda'] = 'Dagordning';

// editing calendars
$labels['calendars'] = 'Kalendrar';
$labels['my_calendar'] = 'Min kalender';
$labels['create_calendar'] = 'Skapa kalender';
$labels['new_calendar'] = 'Ny kalender';
$labels['edit_calendar'] = 'Redigera kalender';
$labels['remove_calendar'] = 'Ta bort kalender';
$labels['confirm_remove_calendar'] = 'Är du säker på att du vill ta bort den här kalendern?';
$labels['calendar_removed'] = 'Kalender borttagen.';
$labels['notifications'] = 'Meddelanden';
$labels['default_event_visibility'] = 'Standard synlighet för händelser';
$labels['time_zone'] = 'Tidszon för kalender';
$labels['share_calendar'] = 'Dela kalender';
$labels['permissions'] = 'Behörigheter';
$labels['permission_publish_calendar'] = 'Publicera kalender';
$labels['permission_share_calendar'] = 'Dela kalender';
$labels['permission_edit_events'] = 'Redigera händelser';
$labels['permission_see_details'] = 'Se mer information';
$labels['permission_publish_calendar_info'] = 'Om markerad kommer användaren att kunna publicera kalendern.';
$labels['permission_share_calendar_info'] = 'Om den är markerad kan användaren dela kalendern med andra användare.';
$labels['permission_edit_events_info'] = 'Om den är markerad kan användaren redigera och ta bort kalenderhändelser.';
$labels['permission_see_details_info'] = 'Om den är markerad delas all information om händelsen. Om den inte är markerad delas endast information om tillgänglig/upptagen.';
$labels['share_send_notification'] = 'Meddela';
$labels['share_send_notification_info'] = 'Om den är markerad skickas ett e-postmeddelande till användaren med information om den nya delade kalendern.';
$labels['new_share_not_saved_warning'] = 'Du angav en e-postadress för att dela kalendern men klickade inte på knappen Dela för att lägga till den i den delade listan. Vill du fortsätta spara utan att dela kalendern med den här användaren?';
$labels['share'] = 'Aktie';
$labels['shared_marker'] = '(Delad)';
$labels['owner'] = 'Ägare';
$labels['publish_calendar'] = 'Publicera kalender';
$labels['create_link'] = 'Skapa länk';
$labels['reset'] = 'Återställning';
$labels['remove'] = 'Ta bort';
$labels['publish_full'] = 'Publicera all information om evenemanget.';
$labels['publish_busy'] = 'Publicera endast information om lediga tider.';
$labels['sync_no_id'] = 'CalDAV-alternativen blir tillgängliga när du har sparat kalendern.';
$labels['share_no_id'] = 'Alternativ för kalenderdelning blir tillgängliga när du har sparat kalendern.';
$labels['publish_no_id'] = 'Alternativen för kalenderpublicering blir tillgängliga när du har sparat kalendern.';
$labels['import_no_id'] = 'Import- och exportalternativen blir tillgängliga när du har sparat kalendern.';
$labels['unable_to_save_calendar_data'] = 'Det går inte att spara kalenderdata.';
$labels['unable_to_retrieve_calendar_data'] = 'Det gick inte att hämta kalenderdata. Kalendern kanske inte längre är tillgänglig.';
$labels['unable_add_shared_calendar'] = 'Det går inte att lägga till den delade kalendern. Kalendern kanske inte längre är tillgänglig.';
$labels['itip_organizer_message'] = 'Du blev inbjuden till detta evenemang men du är inte arrangören. De ändringar du gör i det här evenemanget skickas inte till de andra deltagarna.';
$labels['cannot_share_with_yourself'] = 'Du kan inte dela din egen kalender med dig själv.';
$labels['cannot_share_with_calendar_owner'] = 'Du kan inte dela den här kalendern med kalenderägaren ($e)';
$labels['setting_default_notification_type'] = 'Standardaviseringar för nya händelser';
$labels['calendar_name_cannot_be_empty'] = 'Kalendernamnet får inte vara tomt.';

// caldav server
$labels['sync_calendar'] = 'Synka kalender';
$labels['password_too_short'] = 'Lösenordet ska vara minst 10 tecken långt';
$labels['read_only'] = 'Endast läsning';
$labels['add_connection'] = 'Lägg till anslutning';
$labels['show_details'] = 'Visa detaljer';
$labels['hide_details'] = 'Dölj detaljer';
$labels['caldav_connection_password'] = 'Det lösenord som du angav när du skapade anslutningen.';
$labels['yes'] = 'Ja';
$labels['no'] = 'Nej';
$labels['confirm_remove_sync'] = 'Är du säker på att du vill ta bort den här anslutningen permanent?';
$labels['remove_sync_error'] = 'Det uppstod ett fel när anslutningen togs bort.';
$labels['create_new_caldav_connection'] = 'Skapa en ny CalDav-anslutning';
$labels['your_caldav_connections'] = 'Dina CalDav-anslutningar';
$labels['created_at'] = 'Skapad';
$labels['connected_at'] = 'Senaste synkroniseringen';
$labels['never'] = 'Aldrig';
$labels['alternate_url'] = 'Alternativ URL';
$labels['caldav_password_info'] = 'Av säkerhetsskäl sparas inte detta lösenord på servern. Om du har glömt det kan du ta bort den här anslutningen och skapa en ny.';
$labels['caldav_url_info'] = 'Använd den här URL:en med appar som stöder kalenderupptäckt, till exempel: iOS och Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Använd den här webbadressen med appar som inte fungerar med den korta webbadressen ovan, t.ex: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'Ogiltig CalDAV-serveradress.';
$labels['cannot_resolve_caldav_host'] = 'Det går inte att identifiera CalDAV-serverns värd.';
$labels['private_caldav_addresses_not_allowed'] = 'Privata och reserverade CalDAV-adresser är inte tillåtna.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Lägg till delad kalender';
$labels['shared_calendars'] = 'Delade kalendrar';
$labels['no_shared_calendars'] = 'Det finns inga delade kalendrar som du kan lägga till.';
$labels['add_to_my_calendars'] = 'Lägg till i mina kalendrar';
$labels['remove_from_my_calendars'] = 'Ta bort från mina kalendrar';
$labels['unshare'] = 'Dela inte med dig';
$labels['unshare_warning'] = 'Om du tar bort delningen av den här kalendern försvinner den från listan och du kan inte lägga till den igen.';
$labels['calendar_name'] = 'Kalenderns namn';
$labels['calendar_owner'] = 'Ägare till kalendern';
$labels['shared_date'] = 'Delad';
$labels['unable_to_share'] = 'Kalendern kan inte delas med följande användare eftersom deras e-postkonton finns på en fjärrserver:';

// shared calendar email
$labels['shared_email_subject'] = '$u har delat en kalender med dig';
$labels['shared_email_body'] = 'Användaren $u har gett dig tillgång till en kalender som heter "$c". Klicka på länken nedan för att lägga till den här kalendern i din lista över aktiva kalendrar:';
$labels['shared_email_body_cpanel'] = 'Användaren $u har gett dig tillgång till en kalender som heter "$c". För att lägga till den här kalendern i din lista över aktiva kalendrar, logga in på Roundcube, gå till Kalender, klicka på plusikonen i sidofältet och välj Lägg till delad kalender.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Semesterkalendrar';
$labels['edit_holiday_calendar'] = 'Redigera semesterkalender';
$labels['religious_holidays'] = 'Religiösa högtider';
$labels['country_holidays'] = 'Semester på landet';
$labels['add'] = 'Lägg till';
$labels['no_more_holidays_allowed'] = 'Du kan inte lägga till fler semesterkalendrar.';
$labels['cannot_load_holidays'] = 'Det går inte att ladda helgdagar från Google-servern.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Lägg till födelsedagskalender';
$labels['edit_birthday_calendar'] = 'Redigera födelsedagskalender';
$labels['contact_birthdays'] = 'Kontakta Födelsedagar';
$labels['contact_birthdays_info'] = 'Denna kalender hämtar födelsedagsinformation från din kontaktlista och visar den som kalenderhändelser.';
$labels['birthday_event_title'] = 'Födelsedag: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Lägg till Google-kalender';
$labels['edit_google_calendar'] = 'Redigera Google Kalender';
$labels['google_id'] = 'ID för Google-kalender';
$labels['new_google_calendar'] = 'Ny Google Kalender';
$labels['specify_google_calendar_id'] = 'Vänligen skriv in Google kalender-ID';
$labels['calendar_id_format_error'] = 'Google-kalenderns ID ska innehålla @-tecknet.';
$labels['cannot_load_google_calendar'] = 'Google-kalendern "$c" kan inte laddas. Kontrollera att kalenderns ID är korrekt och att kalendern har gjorts tillgänglig för allmänheten.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Lägg till CalDAV-kalender';
$labels['edit_caldav_calendar'] = 'Redigera CalDAV-kalender';
$labels['add_caldav_calendar_information'] = 'Om du vill prenumerera på en CalDAV-kalender anger du CalDAV-serverinformationen och klickar på Sök kalendrar.';
$labels['caldav_url'] = 'CalDAV URL';
$labels['find_calendars'] = 'Hitta kalendrar';
$labels['searching_for_calendars'] = 'Söker efter kalendrar, vänligen vänta...';
$labels['select_calendars'] = 'Välj kalendrar som ska läggas till:';
$labels['read_only_status_yes'] = 'Ja';
$labels['read_only_status_no'] = 'Nej';
$labels['subscribed'] = 'Prenumeration';

$labels['refresh_calendar'] = 'Uppdatera kalendern';
$labels['refresh_every_minute'] = 'Varje minut';
$labels['refresh_every_5_minutes'] = 'Var 5:e minut';
$labels['refresh_every_15_minutes'] = 'Var 15:e minut';
$labels['refresh_every_30_minutes'] = 'Var 30:e minut';
$labels['refresh_every_60_minutes'] = 'Var 60:e minut';
$labels['refresh_never'] = 'Aldrig';

$labels['cannot_connect_to_caldav_server'] = 'Det går inte att ansluta till CalDAV-servern "$n".';
$labels['caldav_client_error_url'] = 'Det går inte att ansluta till CalDAV-servern. Kontrollera serverns URL.';
$labels['caldav_client_error_credentials'] = 'Felaktigt CalDAV-användarnamn eller lösenord.';
$labels['caldav_client_error_empty'] = 'Servern returnerade inte några kalendrar som kan läggas till. Kontrollera CalDAV-URL:en.';
$labels['caldav_client_error_server_info'] = 'Felaktig CalDAV-serverinformation.';
$labels['caldav_client_error_add_calendar'] = 'Det går inte att spara kalender "$n".';
$labels['caldav_client_error_delete_event'] = 'Det går inte att ta bort händelsen';
$labels['caldav_client_error_save_event'] = 'Det går inte att spara CalDAV-händelsen: den kan ha flyttats, raderats eller så är kalendern inte tillgänglig.';
$labels['caldav_client_error_calendar_added'] = 'Kalendern "$n" finns redan i din kalenderlista.';

// previewing event
$labels['remove_recurring_event'] = 'Ta bort återkommande händelse';
$labels['remove_one'] = 'Radera endast denna instans';
$labels['remove_all'] = 'Ta bort alla instanser';
$labels['edit_event'] = 'Redigera händelse';
$labels['event_removed'] = 'Händelsen raderad.';
$labels['go_to_url'] = 'Gå till URL';
$labels['confirm_remove_event'] = 'Är du säker på att du vill ta bort den här händelsen?';

// calendar/agenda grid
$labels['no_events'] = 'Det finns inga händelser att visa.';

// importing events
$labels['select_calendar'] = 'Välj kalender';
$labels['select_calendar_for_events'] = 'Välj den kalender som du vill lägga till händelserna i:';
$labels['add_events'] = 'Lägg till händelser';
$labels['add_events_to_calendar'] = 'Lägg till händelser i kalendern';
$labels['events_imported_successfully'] = 'Händelser importerade framgångsrikt';
$labels['events_imported_with_errors'] = 'Någon annan händelse kunde inte importeras.';
$labels['error_importing_events'] = 'Det uppstod ett fel vid import av händelser till kalendern';

// editing events
$labels['calendar'] = 'Kalender';
$labels['new_event'] = 'Nytt evenemang';
$labels['summary'] = 'Sammanfattning';
$labels['recurrence'] = 'Återkommande';
$labels['title'] = 'Titel';
$labels['location'] = 'Plats';
$labels['description'] = 'Beskrivning';
$labels['comment'] = 'Kommentar';
$labels['url'] = 'URL';
$labels['name'] = 'Namn';
$labels['start'] = 'Start';
$labels['end'] = 'Slut';
$labels['show_timezones'] = 'Visa tidszoner';
$labels['reset_timezones'] = 'Återställ tidszoner till standard';
$labels['all_day'] = 'Hela dagen';
$labels['use_calendar_colors'] = 'Använd kalenderfärger';
$labels['color'] = 'Färg';
$labels['background'] = 'Bakgrund';
$labels['text'] = 'Text';
$labels['show_me_as'] = 'Visa mig som';
$labels['available'] = 'Tillgänglig';
$labels['busy'] = 'Upptagen';
$labels['visibility'] = 'Synlighet';
$labels['calendar_default'] = 'Kalender som standard';
$labels['public'] = 'Allmänheten';
$labels['private'] = 'Privat';
$labels['repeat'] = 'Upprepa';
$labels['repeat_every'] = 'Upprepa varje';
$labels['never'] = 'Aldrig';
$labels['daily'] = 'Dagligen';
$labels['weekly'] = 'Veckovis';
$labels['monthly'] = 'Månadsvis';
$labels['yearly'] = 'Årligen';
$labels['days'] = 'dag(ar)';
$labels['weeks'] = 'vecka(n)';
$labels['months'] = 'månad(ar)';
$labels['years'] = 'år(en)';
$labels['repeat_on'] = 'Upprepa på';
$labels['repeat_by'] = 'Upprepas av';
$labels['day_of_month'] = 'Dag i månaden';
$labels['day_of_week'] = 'Veckodag';
$labels['not_specified'] = 'Ej specificerat';
$labels['first'] = 'Första';
$labels['second'] = 'Andra';
$labels['third'] = 'Tredje';
$labels['fourth'] = 'Fjärde';
$labels['fifth'] = 'Femte';
$labels['second_to_last'] = 'Näst sista plats';
$labels['last'] = 'Sista';
$labels['attachment_too_large'] = 'Den bifogade filen är för stor. Den maximalt tillåtna storleken är $s.';
$labels['bg_color'] = 'Bakgrundsfärg';
$labels['tx_color'] = 'Textfärg';
$labels['add_notification'] = 'Lägg till meddelande';
$labels['popup'] = 'Popup-fönster';
$labels['no_notifications'] = 'Denna händelse har inga meddelanden.';
$labels['minutes'] = 'Protokoll';
$labels['hours'] = 'timmar';
$labels['link'] = 'Länk';
$labels['attach_file_from_web'] = 'Bifoga fil';
$labels['attachment_url'] = 'URL för bifogad fil';
$labels['invalid_url'] = 'Denna URL är ogiltig.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Duplicera';
$labels['duplicate_suffix'] = '(duplikat)';
$labels['no_notifications'] = 'Denna händelse har inga meddelanden.';
$labels['this_calendar_is_hidden'] = 'Den här kalendern är dold. Sparade händelser visas inte förrän kalendern är synlig.';
$labels['make_calendar_visible'] = 'Gör den här kalendern synlig.';

// recurrence
$labels['recurrence_range'] = 'Återkomstintervall';
$labels['repeat_forever'] = 'Upprepa för alltid';
$labels['repeat_every_weekday'] = 'Upprepa varje veckodag';
$labels['last_day'] = 'Sista dagen';
$labels['every'] = 'Varje';
$labels['weekday'] = 'Veckodag';
$labels['weekend_day'] = 'Helgdag';
$labels['day_of_week'] = 'Veckodag';
$labels['day_of_month'] = 'Dag i månaden';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Slut efter';
$labels['occurrences'] = 'förekomst(er)';

// end recurrence on date:
$labels['end_by_date'] = 'Slutdatum';
$labels['removed_events_title'] = 'Evenemang som tagits bort från återkommande serier';
$labels['restore'] = 'Återställ';

// settings page
$labels['setting_view'] = 'Inledande kalendervy';
$labels['setting_first_day'] = 'Veckan börjar den';
$labels['setting_agenda_week_span'] = 'Tidsperiod för agendan (veckor)';
$labels['setting_slot_duration'] = 'Tidsperiod för dagen (minuter)';
$labels['setting_scroll_time'] = 'Bläddra till tid';
$labels['setting_calendar'] = 'Standardkalender för evenemang';
$labels['setting_refresh'] = 'Uppdatera kalendrar';
$labels['setting_week_numbers'] = 'Veckonummer i liten kalender';
$labels['setting_event_border'] = 'Kategorifärger som gränser för evenemang';
$labels['show_sunrise'] = 'Visa soluppgång i månadsvy';
$labels['show_sunset'] = 'Visa solnedgång i månadsvyn';
$labels['categories'] = 'Kategorier';
$labels['category'] = 'Kategori';
$labels['no_category'] = 'Ingen kategori';
$labels['add_category'] = 'Lägg till kategori';
$labels['setting_alarm_sound'] = 'Ljud vid avisering';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambu';
$labels['alarm_sound_bells'] = 'Klockor';
$labels['alarm_sound_chord'] = 'Ackord';
$labels['alarm_sound_circles'] = 'Cirklar';
$labels['alarm_sound_glass'] = 'Glas';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metall';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Visselpipa';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Spela upp ljud';

// alarms
$labels['upcoming_events'] = 'Kommande evenemang';
$labels['dismiss'] = 'Avvisa';
$labels['dismiss_all'] = 'Avvisa alla';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minuter';
$labels['10_minutes'] = '10 minuter';
$labels['20_minutes'] = '20 minuter';
$labels['30_minutes'] = '30 minuter';
$labels['1_hour'] = '1 timme';
$labels['2_hours'] = '2 timmar';
$labels['tomorrow'] = 'Tills i morgon';
$labels['next_week'] = 'Till nästa vecka';

// sidebar
$labels['setting_show_xcalendar'] = 'Visa kalender Agenda';
$labels['todays_agenda'] = 'Dagens agenda';
$labels['no_events_today'] = 'Inga evenemang planerade för idag.';
$labels['sidebar_more'] = 'Mer...';

// event invitation
$labels['itip_title_request'] = 'Du är inbjuden till ett evenemang.';
$labels['itip_title_modified'] = 'Detta evenemang har ändrats.';
$labels['itip_title_reply_accepted'] = '$u har accepterat din inbjudan';
$labels['itip_title_reply_tentative'] = '$u har preliminärt accepterat din inbjudan';
$labels['itip_title_reply_declined'] = '$u har avböjt din inbjudan';
$labels['itip_title_cancel'] = 'Detta evenemang har ställts in.';

$labels['itip_email_subject_request'] = 'Inbjudan:';
$labels['itip_email_subject_request_modified'] = 'Händelsen ändrad:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Inställd:';

$labels['itip_popup_event_added'] = 'Händelsen läggs till i kalendern.';
$labels['itip_popup_event_updated'] = 'Händelsen uppdateras i kalendern.';
$labels['itip_popup_event_deleted'] = 'Händelse borttagen från kalendern.';
$labels['itip_popup_response_sent'] = 'Svar skickat till arrangören.';

$labels['itip_message_invitation_accepted'] = 'Du har accepterat denna inbjudan.';
$labels['itip_message_invitation_tentative'] = 'Du har preliminärt accepterat denna inbjudan.';
$labels['itip_message_invitation_declined'] = 'Du har tackat nej till denna inbjudan.';
$labels['itip_message_event_deleted'] = 'Evenemanget har tagits bort från din kalender.';
$labels['itip_message_event_updated'] = 'Händelsen i din kalender har uppdaterats.';
$labels['itip_message_event_missing'] = 'Detta evenemang finns inte med i någon av era kalendrar.';

$labels['itip_accept'] = 'Acceptera';
$labels['itip_tentative'] = 'Preliminär';
$labels['itip_decline'] = 'Nedgång';
$labels['itip_change_reponse'] = 'Ändra svar.';
$labels['itip_add_to_calendar'] = 'Lägg till i kalender';
$labels['itip_reply_to_sender'] = 'Svara till avsändaren';
$labels['itip_dont_import'] = 'Lägg inte till i kalendern';
$labels['itip_update_in_calendar'] = 'Uppdatera händelse i kalender';
$labels['itip_delete_from_calendar'] = 'Ta bort händelse från kalender';

$labels['mail_subject_invitation'] = 'Inbjudan:';
$labels['mail_subject_update'] = 'Uppdaterad inbjudan:';
$labels['mail_subject_cancel'] = 'Händelsen är inställd:';

// event email notifications
$labels['mail_title'] = 'Titel:';
$labels['mail_start'] = 'Börja:';
$labels['mail_end'] = 'Slut:';
$labels['mail_description'] = 'Beskrivning:';
$labels['mail_location'] = 'Plats:';

// event attendees
$labels['attendees'] = 'Deltagare';
$labels['attendee_role'] = 'Roll';
$labels['attendee_role_required'] = 'Krävs';
$labels['attendee_role_optional'] = 'Valfritt';
$labels['attendee_role_chair'] = 'Ordförande';
$labels['attendee_role_none'] = 'Icke-deltagare';
$labels['organizer'] = 'Arrangör';
$labels['attendance'] = 'Närvaro';
$labels['your_status'] = 'Din status';
$labels['attendance_going'] = 'Ska du gå?';
$labels['attendee_status_yes'] = 'Ja';
$labels['attendee_status_no'] = 'Nej';
$labels['attendee_status_maybe'] = 'Kanske';
$labels['attendee_status_delegated'] = 'Delegerad';
$labels['attendee_status_waiting'] = 'Väntar på';
$labels['attendee_status_waiting_for_confirmation'] = 'Väntar på bekräftelse';
$labels['attendee_status_info_yes'] = 'Kommer att delta';
$labels['attendee_status_info_no'] = 'Kommer inte att delta';
$labels['attendee_status_info_maybe'] = 'Kan komma att delta';
$labels['attendee_status_info_delegated'] = 'Delegerad närvaro';
$labels['attendee_role'] = 'Roll';
$labels['attendee_role_info'] = 'Ange om deltagaren ska närvara vid eller leda mötet.';
$labels['attendee_notify'] = 'Meddela';
$labels['attendee_notify_info'] = 'Om den är markerad skickas ett e-postmeddelande med inbjudan till deltagaren.';
$labels['add_attendee'] = 'Lägg till deltagare';
$labels['attendee_notify_question'] = 'Skicka e-postmeddelanden till deltagarna?';
$labels['attendee_notify_textarea'] = 'Lägg till ett valfritt meddelande';
$labels['attendee_notify_send'] = 'Skicka';
$labels['attendee_notify_dont'] = 'Skicka inte';
$labels['attendee_notify_cancel'] = 'Avbryt';
$labels['new_attendee_not_saved_warning'] = 'Du har angett en e-postadress för en deltagare men inte lagt till den i deltagarlistan. Vill du spara ändå utan denna deltagare?';

// import / export
$labels['import_export'] = 'Import / Export';
$labels['export_to_file'] = 'Exportera till fil';
$labels['export_info'] = 'Klicka på den här knappen för att exportera och ladda ner kalenderns händelser i iCalendar (ics)-format. Om kalendern innehåller många händelser kan exportprocessen ta några ögonblick.';
$labels['export_no_events'] = 'Den här kalendern har inga händelser att exportera.';
$labels['import_from_file'] = 'Importera från fil';
$labels['import_info'] = 'Klicka på den här knappen för att importera händelser till den här kalendern från en iCalendar (ics)-fil.';

// email notifications
$labels['appointment_reminder'] = 'Påminnelse om avtalad tid';
$labels['email_notification_subject'] = 'Påminnelse: $t på $d ($z)';
$labels['email_notification_cron_error'] = 'E-postmeddelanden är inaktiverade. Kontakta administratören.';
$labels['alarm_before_event_starts'] = 'Innan evenemanget startar';
$labels['alarm_after_event_starts'] = 'Efter evenemangets start';
$labels['alarm_before_event_ends'] = 'Innan evenemanget avslutas';
$labels['alarm_after_event_ends'] = 'Efter evenemangets slut';
$labels['alarm_at_specific_time'] = 'Vid en viss tidpunkt';

// search
$labels['specify_at_least_one_calendar'] = 'Ange minst en kalender att söka i.';
$labels['specify_text_to_search'] = 'Skriv in den text du vill söka efter.';
$labels['clear_search_results'] = 'Klart';
$labels['search_outdated_message'] = 'Sökresultaten kan vara föråldrade. Överväg att göra om din sökning.';
$labels['date_range'] = 'Datumintervall';
$labels['date_range_subtitle'] = 'Datumintervall:';
$labels['search_results'] = 'Resultat hittades: $n';
$labels['modify_search_options'] = 'Försök att ändra sökalternativen.';
$labels['show_shortcuts'] = 'Visa genvägar';
$labels['hide_shortcuts'] = 'Dölj genvägar';
$labels['this_month'] = 'Denna månad';
$labels['this_year'] = 'Detta år';
$labels['past_30_days'] = 'Senaste 30 dagarna';
$labels['next_30_days'] = 'De närmaste 30 dagarna';
$labels['past_6_months'] = 'Senaste 6 månaderna';
$labels['next_6_months'] = 'Nästa 6 månader';

// calendar grid
$labels['cannot_load_events'] = 'Det går inte att ladda händelser för kalender "$c".';
