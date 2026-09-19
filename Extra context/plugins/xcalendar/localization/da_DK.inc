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
$labels['create_event'] = 'Opret begivenhed';
$labels['create_event_button'] = 'Ny begivenhed';
$labels['day'] = 'Dag';
$labels['week'] = 'Uge';
$labels['month'] = 'Måned';
$labels['agenda'] = 'Dagsorden';

// editing calendars
$labels['calendars'] = 'Kalendere';
$labels['my_calendar'] = 'Min kalender';
$labels['create_calendar'] = 'Opret kalender';
$labels['new_calendar'] = 'Ny kalender';
$labels['edit_calendar'] = 'Rediger kalender';
$labels['remove_calendar'] = 'Slet kalender';
$labels['confirm_remove_calendar'] = 'Er du sikker på, at du vil slette denne kalender?';
$labels['calendar_removed'] = 'Kalender slettet.';
$labels['notifications'] = 'Meddelelser';
$labels['default_event_visibility'] = 'Standard synlighed af hændelser';
$labels['time_zone'] = 'Kalender tidszone';
$labels['share_calendar'] = 'Del kalender';
$labels['permissions'] = 'Tilladelser';
$labels['permission_publish_calendar'] = 'Offentliggøre kalender';
$labels['permission_share_calendar'] = 'Del kalender';
$labels['permission_edit_events'] = 'Rediger begivenheder';
$labels['permission_see_details'] = 'Se detaljer';
$labels['permission_publish_calendar_info'] = 'Hvis dette afkrydsningsfelt er markeret, kan brugeren offentliggøre kalenderen.';
$labels['permission_share_calendar_info'] = 'Hvis dette afkrydses, kan brugeren dele kalenderen med andre brugere.';
$labels['permission_edit_events_info'] = 'Hvis dette felt er markeret, kan brugeren redigere og slette kalenderbegivenheder.';
$labels['permission_see_details_info'] = 'Hvis dette afkrydses, deles alle detaljer om begivenheden. Hvis der ikke er markeret, er det kun de tilgængelige/optaget-informationer, der deles.';
$labels['share_send_notification'] = 'Meddel';
$labels['share_send_notification_info'] = 'Hvis dette afkrydses, sendes der en e-mail til brugeren med besked om den nyligt delte kalender.';
$labels['new_share_not_saved_warning'] = 'Du har skrevet en e-mail, som du vil dele kalenderen med, men du har ikke klikket på knappen Del for at tilføje den til den delte liste. Fortsætte med at gemme uden at dele kalenderen med den angivne bruger?';
$labels['share'] = 'Del';
$labels['shared_marker'] = '(Delte)';
$labels['owner'] = 'Ejer';
$labels['publish_calendar'] = 'Offentliggøre kalender';
$labels['create_link'] = 'Opret link';
$labels['reset'] = 'Nulstil';
$labels['remove'] = 'Fjern';
$labels['publish_full'] = 'Offentliggør alle oplysninger om begivenheden.';
$labels['publish_busy'] = 'Offentliggør kun oplysninger om ledige/optagethed.';
$labels['sync_no_id'] = 'CalDAV-indstillingerne bliver tilgængelige, når du har gemt kalenderen.';
$labels['share_no_id'] = 'Mulighederne for at dele kalenderen bliver tilgængelige, når du har gemt kalenderen.';
$labels['publish_no_id'] = 'Valgmulighederne for udgivelse af kalenderen bliver tilgængelige, når du har gemt kalenderen.';
$labels['import_no_id'] = 'Import- og eksportmulighederne bliver tilgængelige, når du har gemt kalenderen.';
$labels['unable_to_save_calendar_data'] = 'Det er ikke muligt at gemme kalenderdata.';
$labels['unable_to_retrieve_calendar_data'] = 'Det er ikke muligt at hente kalenderdata. Kalenderen er muligvis ikke længere tilgængelig.';
$labels['unable_add_shared_calendar'] = 'Kan ikke tilføje den delte kalender. Kalenderen er muligvis ikke længere tilgængelig.';
$labels['itip_organizer_message'] = 'Du blev inviteret til denne begivenhed, men du er ikke arrangøren. De ændringer, du foretager i denne begivenhed, vil ikke blive sendt til de andre deltagere.';
$labels['cannot_share_with_yourself'] = 'Du kan ikke dele din egen kalender med dig selv.';
$labels['cannot_share_with_calendar_owner'] = 'Du kan ikke dele denne kalender med kalenderens ejer ($e)';
$labels['setting_default_notification_type'] = 'Standardmeddelelser om nye hændelser';
$labels['calendar_name_cannot_be_empty'] = 'Kalendernavnet må ikke være tomt.';

// caldav server
$labels['sync_calendar'] = 'Synkroniser kalender';
$labels['password_too_short'] = 'Adgangskoden skal være mindst 10 tegn lang';
$labels['read_only'] = 'Kun læsning';
$labels['add_connection'] = 'Tilføj forbindelse';
$labels['show_details'] = 'Vis detaljer';
$labels['hide_details'] = 'Skjul detaljer';
$labels['caldav_connection_password'] = 'Den adgangskode, du angav, da du oprettede denne forbindelse.';
$labels['yes'] = 'Ja';
$labels['no'] = 'Nej';
$labels['confirm_remove_sync'] = 'Er du sikker på, at du ønsker at fjerne denne forbindelse permanent?';
$labels['remove_sync_error'] = 'Der opstod en fejl under fjernelsen af forbindelsen.';
$labels['create_new_caldav_connection'] = 'Opret en ny CalDav-forbindelse';
$labels['your_caldav_connections'] = 'Dine CalDav-forbindelser';
$labels['created_at'] = 'Oprettet';
$labels['connected_at'] = 'Sidste synkronisering';
$labels['never'] = 'Aldrig';
$labels['alternate_url'] = 'Alternativ URL';
$labels['caldav_password_info'] = 'Af sikkerhedshensyn gemmes denne adgangskode ikke på serveren. Hvis du har glemt det, skal du slette denne forbindelse og oprette en ny.';
$labels['caldav_url_info'] = 'Brug denne URL-adresse med apps, der understøtter kalendervisning, f.eks. iOS og Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Brug denne URL med apps, der ikke fungerer med den korte URL ovenfor, f.eks: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'Ugyldig CalDAV-server-URL.';
$labels['cannot_resolve_caldav_host'] = 'Det er ikke muligt at finde CalDAV-serverens værtsnavn.';
$labels['private_caldav_addresses_not_allowed'] = 'Private og reserverede CalDAV-adresser er ikke tilladt.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Tilføj delt kalender';
$labels['shared_calendars'] = 'Delte kalendere';
$labels['no_shared_calendars'] = 'Der er ingen delte kalendere, du kan tilføje.';
$labels['add_to_my_calendars'] = 'Føj til mine kalendere';
$labels['remove_from_my_calendars'] = 'Fjern fra mine kalendere';
$labels['unshare'] = 'Fjern deling';
$labels['unshare_warning'] = 'Hvis du ophæver delingen af denne kalender, forsvinder den fra denne liste, og du kan ikke tilføje den igen.';
$labels['calendar_name'] = 'Kalendernavn';
$labels['calendar_owner'] = 'Kalender ejer';
$labels['shared_date'] = 'Fælles';
$labels['unable_to_share'] = 'Kalenderen kan ikke deles med følgende brugere, fordi deres e-mail-konti er placeret på en fjernserver:';

// shared calendar email
$labels['shared_email_subject'] = '$u har delt en kalender med dig';
$labels['shared_email_body'] = 'Brugeren $u har givet dig adgang til en kalender med navnet "$c". Hvis du vil tilføje denne kalender til din liste over aktive kalendere, skal du klikke på linket nedenfor for at tilføje den til din liste over aktive kalendere:';
$labels['shared_email_body_cpanel'] = 'Brugeren $u har givet dig adgang til en kalender med navnet "$c". Hvis du vil tilføje denne kalender til din liste over aktive kalendere, skal du logge ind på Roundcube, gå til Kalender, klikke på plusikonet på sidebjælken og vælge Tilføj delt kalender.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Ferie-kalendere';
$labels['edit_holiday_calendar'] = 'Rediger feriekalender';
$labels['religious_holidays'] = 'Religiøse helligdage';
$labels['country_holidays'] = 'Ferier på landet';
$labels['add'] = 'Tilføj';
$labels['no_more_holidays_allowed'] = 'Du kan ikke tilføje flere feriekalendere.';
$labels['cannot_load_holidays'] = 'Kan ikke indlæse helligdage fra Google-serveren.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Tilføj fødselsdagskalender';
$labels['edit_birthday_calendar'] = 'Rediger fødselsdagskalender';
$labels['contact_birthdays'] = 'Kontakt fødselsdage';
$labels['contact_birthdays_info'] = 'Denne kalender henter fødselsdagsoplysninger fra din kontaktliste og viser dem som kalenderbegivenheder.';
$labels['birthday_event_title'] = 'Fødselsdag: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Tilføj Google-kalender';
$labels['edit_google_calendar'] = 'Rediger Google-kalender';
$labels['google_id'] = 'Google kalender-ID';
$labels['new_google_calendar'] = 'Ny Google-kalender';
$labels['specify_google_calendar_id'] = 'Indtast venligst Google kalender-ID';
$labels['calendar_id_format_error'] = 'Google-kalender-id\'et skal indeholde @-tegnet.';
$labels['cannot_load_google_calendar'] = 'Google-kalenderen "$c" kan ikke indlæses. Sørg for, at kalender-id\'et er korrekt, og at kalenderen er gjort tilgængelig for offentligheden.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Tilføj CalDAV-kalender';
$labels['edit_caldav_calendar'] = 'Rediger CalDAV-kalender';
$labels['add_caldav_calendar_information'] = 'For at abonnere på en CalDAV-kalender skal du angive CalDAV-serveroplysningerne og klikke på Find kalendere.';
$labels['caldav_url'] = 'CalDAV-URL';
$labels['find_calendars'] = 'Find kalendere';
$labels['searching_for_calendars'] = 'Søger efter kalendere, vent venligst...';
$labels['select_calendars'] = 'Vælg kalendere, der skal tilføjes:';
$labels['read_only_status_yes'] = 'Ja';
$labels['read_only_status_no'] = 'Nej';
$labels['subscribed'] = 'Abonneret';

$labels['refresh_calendar'] = 'Opdater kalender';
$labels['refresh_every_minute'] = 'Hvert eneste minut';
$labels['refresh_every_5_minutes'] = 'Hvert 5. minut';
$labels['refresh_every_15_minutes'] = 'Hvert 15. minut';
$labels['refresh_every_30_minutes'] = 'Hvert 30. minut';
$labels['refresh_every_60_minutes'] = 'Hvert 60. minut';
$labels['refresh_never'] = 'Aldrig';

$labels['cannot_connect_to_caldav_server'] = 'Kan ikke oprette forbindelse til CalDAV-serveren "$n".';
$labels['caldav_client_error_url'] = 'Kan ikke oprette forbindelse til CalDAV-serveren. Tjek serverens URL.';
$labels['caldav_client_error_credentials'] = 'Forkert CalDAV-brugernavn eller -adgangskode.';
$labels['caldav_client_error_empty'] = 'Serveren returnerede ikke nogen kalendere, der kan tilføjes. Tjek CalDAV-URL\'en.';
$labels['caldav_client_error_server_info'] = 'Forkerte CalDAV-serveroplysninger.';
$labels['caldav_client_error_add_calendar'] = 'Kan ikke gemme kalenderen "$n".';
$labels['caldav_client_error_delete_event'] = 'Kan ikke slette begivenhed';
$labels['caldav_client_error_save_event'] = 'Det er ikke muligt at gemme CalDAV-begivenheden: Den er muligvis blevet flyttet eller slettet, eller kalenderen er ikke tilgængelig.';
$labels['caldav_client_error_calendar_added'] = 'Kalenderen "$n" er allerede på din kalenderliste.';

// previewing event
$labels['remove_recurring_event'] = 'Slet tilbagevendende begivenhed';
$labels['remove_one'] = 'Slet kun denne instans';
$labels['remove_all'] = 'Slet alle forekomster';
$labels['edit_event'] = 'Rediger begivenhed';
$labels['event_removed'] = 'Begivenhed slettet.';
$labels['go_to_url'] = 'Gå til URL';
$labels['confirm_remove_event'] = 'Er du sikker på, at du vil slette denne begivenhed?';

// calendar/agenda grid
$labels['no_events'] = 'Der er ingen begivenheder, der skal vises.';

// importing events
$labels['select_calendar'] = 'Vælg kalender';
$labels['select_calendar_for_events'] = 'Vælg den kalender, du vil tilføje begivenhederne til:';
$labels['add_events'] = 'Tilføj begivenheder';
$labels['add_events_to_calendar'] = 'Tilføj begivenheder til kalenderen';
$labels['events_imported_successfully'] = 'Begivenheder importeret med succes';
$labels['events_imported_with_errors'] = 'En anden begivenhed kunne ikke importeres.';
$labels['error_importing_events'] = 'Der opstod en fejl under import af begivenheder til kalenderen';

// editing events
$labels['calendar'] = 'Kalender';
$labels['new_event'] = 'Ny begivenhed';
$labels['summary'] = 'Resumé';
$labels['recurrence'] = 'Genoptagelse';
$labels['title'] = 'Titel';
$labels['location'] = 'Placering';
$labels['description'] = 'Beskrivelse';
$labels['comment'] = 'Kommentar';
$labels['url'] = 'URL';
$labels['name'] = 'Navn';
$labels['start'] = 'Start';
$labels['end'] = 'Slut';
$labels['show_timezones'] = 'Vis tidszoner';
$labels['reset_timezones'] = 'Nulstil tidszoner til standard';
$labels['all_day'] = 'Hele dagen';
$labels['use_calendar_colors'] = 'Brug kalenderfarver';
$labels['color'] = 'Farve';
$labels['background'] = 'Baggrund';
$labels['text'] = 'Tekst';
$labels['show_me_as'] = 'Vis mig som';
$labels['available'] = 'Tilgængelig';
$labels['busy'] = 'Travl';
$labels['visibility'] = 'Synlighed';
$labels['calendar_default'] = 'Standard kalender';
$labels['public'] = 'Offentlig';
$labels['private'] = 'Privat';
$labels['repeat'] = 'Gentag';
$labels['repeat_every'] = 'Gentag hver';
$labels['never'] = 'Aldrig';
$labels['daily'] = 'Dagligt';
$labels['weekly'] = 'Ugentligt';
$labels['monthly'] = 'Månedlig';
$labels['yearly'] = 'Årligt';
$labels['days'] = 'dag(e)';
$labels['weeks'] = 'uge(r)';
$labels['months'] = 'måned(er)';
$labels['years'] = 'år(e)';
$labels['repeat_on'] = 'Gentag den';
$labels['repeat_by'] = 'Gentag ved';
$labels['day_of_month'] = 'dag i måneden';
$labels['day_of_week'] = 'ugedag';
$labels['not_specified'] = 'Ikke specificeret';
$labels['first'] = 'Første';
$labels['second'] = 'Anden';
$labels['third'] = 'Tredje';
$labels['fourth'] = 'Fjerde';
$labels['fifth'] = 'Femte';
$labels['second_to_last'] = 'Næstsidst i rækken';
$labels['last'] = 'Sidst';
$labels['attachment_too_large'] = 'Den vedhæftede fil er for stor. Den maksimalt tilladte størrelse er $s.';
$labels['bg_color'] = 'Baggrundsfarve';
$labels['tx_color'] = 'Tekstfarve';
$labels['add_notification'] = 'Tilføj meddelelse';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'Denne begivenhed har ikke nogen meddelelser.';
$labels['minutes'] = 'minutter';
$labels['hours'] = 'timer';
$labels['link'] = 'Link';
$labels['attach_file_from_web'] = 'Vedhæft fil';
$labels['attachment_url'] = 'Vedhæftet fil URL';
$labels['invalid_url'] = 'Denne URL er ugyldig.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drev';
$labels['duplicate'] = 'Dupliker';
$labels['duplicate_suffix'] = '(duplikat)';
$labels['no_notifications'] = 'Denne begivenhed har ikke nogen meddelelser.';
$labels['this_calendar_is_hidden'] = 'Denne kalender er skjult. Gemte begivenheder vises først, når kalenderen er synlig.';
$labels['make_calendar_visible'] = 'Gør denne kalender synlig.';

// recurrence
$labels['recurrence_range'] = 'Område for tilbagevenden';
$labels['repeat_forever'] = 'Gentag for evigt';
$labels['repeat_every_weekday'] = 'Gentag hver ugedag';
$labels['last_day'] = 'Sidste dag';
$labels['every'] = 'Hver eneste';
$labels['weekday'] = 'Ugedag';
$labels['weekend_day'] = 'Weekenddag';
$labels['day_of_week'] = 'ugedag';
$labels['day_of_month'] = 'dag i måneden';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Slut efter';
$labels['occurrences'] = 'forekomster';

// end recurrence on date:
$labels['end_by_date'] = 'Slutdato';
$labels['removed_events_title'] = 'Begivenheder fjernet fra tilbagevendende serier';
$labels['restore'] = 'Gendan';

// settings page
$labels['setting_view'] = 'Oprindelig kalendervisning';
$labels['setting_first_day'] = 'Ugen starter den';
$labels['setting_agenda_week_span'] = 'Agendaens varighed (uger)';
$labels['setting_slot_duration'] = 'Dagens tidsrum (minutter)';
$labels['setting_scroll_time'] = 'Rul til tid';
$labels['setting_calendar'] = 'Standardkalender for begivenheder';
$labels['setting_refresh'] = 'Opdater kalendere';
$labels['setting_week_numbers'] = 'Ugenumre i den lille kalender';
$labels['setting_event_border'] = 'Kategorifarver som begivenhedsgrænser';
$labels['show_sunrise'] = 'Vis solopgang i månedsvisningen';
$labels['show_sunset'] = 'Vis solnedgang i månedsvisningen';
$labels['categories'] = 'Kategorier';
$labels['category'] = 'Kategori';
$labels['no_category'] = 'Ingen kategori';
$labels['add_category'] = 'Tilføj kategori';
$labels['setting_alarm_sound'] = 'Notifikationslyd';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambus';
$labels['alarm_sound_bells'] = 'Klokker';
$labels['alarm_sound_chord'] = 'Akkord';
$labels['alarm_sound_circles'] = 'Cirkler';
$labels['alarm_sound_glass'] = 'Glas';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metal';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Fløjte';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Afspil lyd';

// alarms
$labels['upcoming_events'] = 'Kommende begivenheder';
$labels['dismiss'] = 'Afvis';
$labels['dismiss_all'] = 'Afvis alle';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minutter';
$labels['10_minutes'] = '10 minutter';
$labels['20_minutes'] = '20 minutter';
$labels['30_minutes'] = '30 minutter';
$labels['1_hour'] = '1 time';
$labels['2_hours'] = '2 timer';
$labels['tomorrow'] = 'Indtil i morgen';
$labels['next_week'] = 'Indtil næste uge';

// sidebar
$labels['setting_show_xcalendar'] = 'Vis kalender Agenda';
$labels['todays_agenda'] = 'Dagens dagsorden';
$labels['no_events_today'] = 'Der er ingen arrangementer planlagt i dag.';
$labels['sidebar_more'] = 'Mere...';

// event invitation
$labels['itip_title_request'] = 'Du er inviteret til en begivenhed.';
$labels['itip_title_modified'] = 'Denne begivenhed er blevet ændret.';
$labels['itip_title_reply_accepted'] = '$u har accepteret din invitation';
$labels['itip_title_reply_tentative'] = '$u har foreløbig accepteret din invitation';
$labels['itip_title_reply_declined'] = '$u har afvist din invitation';
$labels['itip_title_cancel'] = 'Denne begivenhed er blevet aflyst.';

$labels['itip_email_subject_request'] = 'Indbydelse:';
$labels['itip_email_subject_request_modified'] = 'Begivenhed ændret:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Annulleret:';

$labels['itip_popup_event_added'] = 'Begivenhed tilføjet til kalenderen.';
$labels['itip_popup_event_updated'] = 'Begivenheden er opdateret i kalenderen.';
$labels['itip_popup_event_deleted'] = 'Begivenhed slettet fra kalenderen.';
$labels['itip_popup_response_sent'] = 'Svar sendt til arrangøren.';

$labels['itip_message_invitation_accepted'] = 'Du har accepteret denne invitation.';
$labels['itip_message_invitation_tentative'] = 'Du har foreløbig accepteret denne invitation.';
$labels['itip_message_invitation_declined'] = 'Du har afslået denne invitation.';
$labels['itip_message_event_deleted'] = 'Arrangementet er blevet slettet fra din kalender.';
$labels['itip_message_event_updated'] = 'Arrangementet i din kalender er blevet opdateret.';
$labels['itip_message_event_missing'] = 'Denne begivenhed findes ikke i nogen af dine kalendere.';

$labels['itip_accept'] = 'Accepter';
$labels['itip_tentative'] = 'Foreløbig';
$labels['itip_decline'] = 'Nedgang';
$labels['itip_change_reponse'] = 'Ændre svar.';
$labels['itip_add_to_calendar'] = 'Tilføj til kalender';
$labels['itip_reply_to_sender'] = 'Svar til afsender';
$labels['itip_dont_import'] = 'Må ikke tilføjes til kalenderen';
$labels['itip_update_in_calendar'] = 'Opdatering af begivenhed i kalenderen';
$labels['itip_delete_from_calendar'] = 'Slet begivenhed fra kalenderen';

$labels['mail_subject_invitation'] = 'Indbydelse:';
$labels['mail_subject_update'] = 'Opdateret invitation:';
$labels['mail_subject_cancel'] = 'Arrangement aflyst:';

// event email notifications
$labels['mail_title'] = 'Titel:';
$labels['mail_start'] = 'Start:';
$labels['mail_end'] = 'Slut:';
$labels['mail_description'] = 'Beskrivelse:';
$labels['mail_location'] = 'Beliggenhed:';

// event attendees
$labels['attendees'] = 'Deltagere';
$labels['attendee_role'] = 'Rolle';
$labels['attendee_role_required'] = 'Påkrævet';
$labels['attendee_role_optional'] = 'Valgfrit';
$labels['attendee_role_chair'] = 'Stol';
$labels['attendee_role_none'] = 'Ikke-deltagende';
$labels['organizer'] = 'Arrangør';
$labels['attendance'] = 'Deltagelse';
$labels['your_status'] = 'Din status';
$labels['attendance_going'] = 'Går du?';
$labels['attendee_status_yes'] = 'Ja';
$labels['attendee_status_no'] = 'Nej';
$labels['attendee_status_maybe'] = 'Måske';
$labels['attendee_status_delegated'] = 'Delegeret';
$labels['attendee_status_waiting'] = 'Venter';
$labels['attendee_status_waiting_for_confirmation'] = 'Venter på bekræftelse';
$labels['attendee_status_info_yes'] = 'Vil deltage';
$labels['attendee_status_info_no'] = 'Vil ikke deltage';
$labels['attendee_status_info_maybe'] = 'Kunne deltage';
$labels['attendee_status_info_delegated'] = 'Delegeret tilstedeværelse';
$labels['attendee_role'] = 'Rolle';
$labels['attendee_role_info'] = 'Angiv, om deltageren skal deltage i eller lede mødet.';
$labels['attendee_notify'] = 'Meddel';
$labels['attendee_notify_info'] = 'Hvis dette afkrydses, sendes der en e-mail med en invitation til deltageren.';
$labels['add_attendee'] = 'Tilføj deltager';
$labels['attendee_notify_question'] = 'Sende e-mails med meddelelser til deltagerne?';
$labels['attendee_notify_textarea'] = 'Tilføj en valgfri meddelelse';
$labels['attendee_notify_send'] = 'Send';
$labels['attendee_notify_dont'] = 'Du må ikke sende';
$labels['attendee_notify_cancel'] = 'Annuller';
$labels['new_attendee_not_saved_warning'] = 'Du har indtastet en deltagers e-mailadresse, men ikke føjet vedkommende til deltagerlisten. Vil du fortsætte med at gemme uden denne deltager?';

// import / export
$labels['import_export'] = 'Import/eksport';
$labels['export_to_file'] = 'Eksporter til fil';
$labels['export_info'] = 'Klik på denne knap for at eksportere og downloade denne kalenders begivenheder i iCalendar-formatet (ics). Hvis kalenderen indeholder mange begivenheder, kan eksportprocessen tage et par øjeblikke.';
$labels['export_no_events'] = 'Denne kalender har ingen begivenheder, der skal eksporteres.';
$labels['import_from_file'] = 'Import fra fil';
$labels['import_info'] = 'Klik på denne knap for at importere begivenheder til denne kalender fra en iCalendar (ics)-fil.';

// email notifications
$labels['appointment_reminder'] = 'Påmindelse om aftaler';
$labels['email_notification_subject'] = 'Påmindelse: $t på $d ($z)';
$labels['email_notification_cron_error'] = 'E-mail-meddelelser er deaktiveret. Kontakt administratoren.';
$labels['alarm_before_event_starts'] = 'Før arrangementet starter';
$labels['alarm_after_event_starts'] = 'Efter arrangementets start';
$labels['alarm_before_event_ends'] = 'Før begivenheden slutter';
$labels['alarm_after_event_ends'] = 'Efter arrangementets afslutning';
$labels['alarm_at_specific_time'] = 'På et bestemt tidspunkt';

// search
$labels['specify_at_least_one_calendar'] = 'Angiv mindst én kalender, der skal søges i.';
$labels['specify_text_to_search'] = 'Skriv den tekst, du vil søge efter.';
$labels['clear_search_results'] = 'Klar';
$labels['search_outdated_message'] = 'Søgeresultaterne kan være forældede. Overvej at køre din søgning igen.';
$labels['date_range'] = 'Datointerval';
$labels['date_range_subtitle'] = 'Datointerval:';
$labels['search_results'] = 'Fundne resultater: $n';
$labels['modify_search_options'] = 'Prøv at ændre søgeindstillingerne.';
$labels['show_shortcuts'] = 'Vis genveje';
$labels['hide_shortcuts'] = 'Skjul genveje';
$labels['this_month'] = 'I denne måned';
$labels['this_year'] = 'I år';
$labels['past_30_days'] = 'De seneste 30 dage';
$labels['next_30_days'] = 'De næste 30 dage';
$labels['past_6_months'] = 'De seneste 6 måneder';
$labels['next_6_months'] = 'De næste 6 måneder';

// calendar grid
$labels['cannot_load_events'] = 'Kan ikke indlæse begivenheder for kalenderen "$c".';
