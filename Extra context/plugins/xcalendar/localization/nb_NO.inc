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
$labels['create_event'] = 'Opprett arrangement';
$labels['create_event_button'] = 'Ny hendelse';
$labels['day'] = 'Dag';
$labels['week'] = 'Uke';
$labels['month'] = 'Måned';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Kalendere';
$labels['my_calendar'] = 'Min kalender';
$labels['create_calendar'] = 'Opprett kalender';
$labels['new_calendar'] = 'Ny kalender';
$labels['edit_calendar'] = 'Rediger kalender';
$labels['remove_calendar'] = 'Slett kalender';
$labels['confirm_remove_calendar'] = 'Er du sikker på at du vil slette denne kalenderen?';
$labels['calendar_removed'] = 'Kalender slettet.';
$labels['notifications'] = 'Meldinger';
$labels['default_event_visibility'] = 'Standard hendelsessynlighet';
$labels['time_zone'] = 'Kalendertidssone';
$labels['share_calendar'] = 'Del kalender';
$labels['permissions'] = 'Tillatelser';
$labels['permission_publish_calendar'] = 'Publiser kalender';
$labels['permission_share_calendar'] = 'Del kalender';
$labels['permission_edit_events'] = 'Rediger hendelser';
$labels['permission_see_details'] = 'Se detaljer';
$labels['permission_publish_calendar_info'] = 'Hvis avkrysset, vil brukeren kunne publisere kalenderen.';
$labels['permission_share_calendar_info'] = 'Hvis dette krysset av, vil brukeren kunne dele kalenderen med andre brukere.';
$labels['permission_edit_events_info'] = 'Hvis avkrysset, vil brukeren kunne redigere og slette kalenderhendelser.';
$labels['permission_see_details_info'] = 'Hvis det er krysset av, vil alle hendelsesdetaljer bli delt. Hvis det ikke er merket av, deles bare tilgjengelig/opptatt-informasjonen.';
$labels['share_send_notification'] = 'Varsle';
$labels['share_send_notification_info'] = 'Hvis dette krysses av, sendes en e-post til brukeren for å informere ham eller henne om den nylig delte kalenderen.';
$labels['new_share_not_saved_warning'] = 'Du skrev inn en e-post for å dele kalenderen med, men klikket ikke på Del-knappen for å legge den til i den delte listen. Fortsett å lagre uten å dele kalenderen med den angitte brukeren?';
$labels['share'] = 'Del';
$labels['shared_marker'] = '(Delt)';
$labels['owner'] = 'Eier';
$labels['publish_calendar'] = 'Publiser kalender';
$labels['create_link'] = 'Opprett lenke';
$labels['reset'] = 'Tilbakestill';
$labels['remove'] = 'Fjern';
$labels['publish_full'] = 'Publiser all informasjon om arrangementet.';
$labels['publish_busy'] = 'Publiser kun informasjon om ledig/opptatt.';
$labels['sync_no_id'] = 'CalDAV-alternativene blir tilgjengelige etter at du har lagret kalenderen.';
$labels['share_no_id'] = 'Alternativene for kalenderdeling blir tilgjengelige etter at du har lagret kalenderen.';
$labels['publish_no_id'] = 'Alternativene for kalenderpublisering blir tilgjengelige etter at du har lagret kalenderen.';
$labels['import_no_id'] = 'Import- og eksportalternativene blir tilgjengelige etter at du har lagret kalenderen.';
$labels['unable_to_save_calendar_data'] = 'Kan ikke lagre kalenderdata.';
$labels['unable_to_retrieve_calendar_data'] = 'Kan ikke hente kalenderdata. Kalenderen er kanskje ikke lenger tilgjengelig.';
$labels['unable_add_shared_calendar'] = 'Kan ikke legge til den delte kalenderen. Kalenderen er kanskje ikke lenger tilgjengelig.';
$labels['itip_organizer_message'] = 'Du ble invitert til dette arrangementet, men du er ikke arrangør. Endringene du gjør i dette arrangementet, vil ikke bli sendt til de andre deltakerne.';
$labels['cannot_share_with_yourself'] = 'Du kan ikke dele din egen kalender med deg selv.';
$labels['cannot_share_with_calendar_owner'] = 'Du kan ikke dele denne kalenderen med kalendereieren ($e).';
$labels['setting_default_notification_type'] = 'Standardvarsler for nye hendelser';
$labels['calendar_name_cannot_be_empty'] = 'Kalendernavnet kan ikke være tomt.';

// caldav server
$labels['sync_calendar'] = 'Synkroniser kalender';
$labels['password_too_short'] = 'Passordet skal være minst 10 tegn langt';
$labels['read_only'] = 'Kun lesing';
$labels['add_connection'] = 'Legg til tilkobling';
$labels['show_details'] = 'Vis detaljer';
$labels['hide_details'] = 'Skjul detaljer';
$labels['caldav_connection_password'] = 'Passordet du oppga da du opprettet denne tilkoblingen.';
$labels['yes'] = 'Ja';
$labels['no'] = 'Nei';
$labels['confirm_remove_sync'] = 'Er du sikker på at du vil fjerne denne tilkoblingen permanent?';
$labels['remove_sync_error'] = 'Det oppstod en feil under fjerning av tilkoblingen.';
$labels['create_new_caldav_connection'] = 'Opprett ny CalDav-tilkobling';
$labels['your_caldav_connections'] = 'CalDav-forbindelsene dine';
$labels['created_at'] = 'Opprettet';
$labels['connected_at'] = 'Siste synkronisering';
$labels['never'] = 'Aldri';
$labels['alternate_url'] = 'Alternativ URL';
$labels['caldav_password_info'] = 'Av sikkerhetsgrunner lagres ikke dette passordet på serveren. Hvis du har glemt det, må du slette denne tilkoblingen og opprette en ny.';
$labels['caldav_url_info'] = 'Bruk denne nettadressen med apper som støtter kalenderoppdagelse, for eksempel: iOS og Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Bruk denne nettadressen med apper som ikke fungerer med den korte nettadressen ovenfor, for eksempel: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'Ugyldig CalDAV-serveradresse.';
$labels['cannot_resolve_caldav_host'] = 'Kan ikke finne CalDAV-serverens vertsnavn.';
$labels['private_caldav_addresses_not_allowed'] = 'Private og reserverte CalDAV-adresser er ikke tillatt.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Legg til delt kalender';
$labels['shared_calendars'] = 'Delte kalendere';
$labels['no_shared_calendars'] = 'Det er ingen delte kalendere du kan legge til.';
$labels['add_to_my_calendars'] = 'Legg til i kalenderne mine';
$labels['remove_from_my_calendars'] = 'Fjern fra kalenderne mine';
$labels['unshare'] = 'Del ikke';
$labels['unshare_warning'] = 'Hvis du opphever delingen av denne kalenderen, forsvinner den fra listen, og du kan ikke legge den til igjen.';
$labels['calendar_name'] = 'Kalendernavn';
$labels['calendar_owner'] = 'Eier av kalenderen';
$labels['shared_date'] = 'Delt';
$labels['unable_to_share'] = 'Kalenderen kan ikke deles med følgende brukere fordi e-postkontoene deres ligger på en ekstern server:';

// shared calendar email
$labels['shared_email_subject'] = '$u har delt en kalender med deg';
$labels['shared_email_body'] = 'Brukeren $u har gitt deg tilgang til en kalender kalt "$c". Hvis du vil legge til denne kalenderen i listen over aktive kalendere, klikker du på lenken nedenfor:';
$labels['shared_email_body_cpanel'] = 'Brukeren $u har gitt deg tilgang til en kalender kalt "$c". For å legge til denne kalenderen i listen over aktive kalendere, logg inn i Roundcube, gå til Kalender, klikk på plussikonet på sidepanelet og velg Legg til delt kalender.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Kalendere for høytider';
$labels['edit_holiday_calendar'] = 'Rediger feriekalender';
$labels['religious_holidays'] = 'Religiøse helligdager';
$labels['country_holidays'] = 'Landsferie';
$labels['add'] = 'Legg til';
$labels['no_more_holidays_allowed'] = 'Du kan ikke legge til flere feriekalendere.';
$labels['cannot_load_holidays'] = 'Kan ikke laste inn helligdager fra Google-serveren.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Legg til bursdagskalender';
$labels['edit_birthday_calendar'] = 'Rediger bursdagskalender';
$labels['contact_birthdays'] = 'Kontakt Fødselsdager';
$labels['contact_birthdays_info'] = 'Denne kalenderen henter bursdagsinformasjon fra kontaktlisten din og viser den som kalenderhendelser.';
$labels['birthday_event_title'] = 'Fødselsdag: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Legg til Google-kalender';
$labels['edit_google_calendar'] = 'Rediger Google-kalenderen';
$labels['google_id'] = 'Google kalender-ID';
$labels['new_google_calendar'] = 'Ny Google-kalender';
$labels['specify_google_calendar_id'] = 'Vennligst skriv inn Google-kalender-ID-en';
$labels['calendar_id_format_error'] = 'Google-kalender-ID-en skal inneholde @-tegnet.';
$labels['cannot_load_google_calendar'] = 'Google-kalenderen "$c" kan ikke lastes inn. Kontroller at kalender-ID-en er riktig, og at kalenderen er gjort tilgjengelig for publikum.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Legg til CalDAV-kalender';
$labels['edit_caldav_calendar'] = 'Rediger CalDAV-kalenderen';
$labels['add_caldav_calendar_information'] = 'Hvis du vil abonnere på en CalDAV-kalender, angir du CalDAV-serverinformasjonen og klikker på Finn kalendere.';
$labels['caldav_url'] = 'CalDAV URL';
$labels['find_calendars'] = 'Finn kalendere';
$labels['searching_for_calendars'] = 'Søker etter kalendere, vennligst vent...';
$labels['select_calendars'] = 'Velg kalendere du vil legge til:';
$labels['read_only_status_yes'] = 'Ja';
$labels['read_only_status_no'] = 'Nei';
$labels['subscribed'] = 'Abonnert';

$labels['refresh_calendar'] = 'Oppdater kalenderen';
$labels['refresh_every_minute'] = 'Hvert minutt';
$labels['refresh_every_5_minutes'] = 'Hvert 5. minutt';
$labels['refresh_every_15_minutes'] = 'Hvert 15. minutt';
$labels['refresh_every_30_minutes'] = 'Hvert 30. minutt';
$labels['refresh_every_60_minutes'] = 'Hvert 60. minutt';
$labels['refresh_never'] = 'Aldri';

$labels['cannot_connect_to_caldav_server'] = 'Kan ikke koble til CalDAV-serveren "$n".';
$labels['caldav_client_error_url'] = 'Kan ikke koble til CalDAV-serveren. Kontroller serverens URL-adresse.';
$labels['caldav_client_error_credentials'] = 'Feil CalDAV-brukernavn eller -passord.';
$labels['caldav_client_error_empty'] = 'Serveren returnerte ingen kalendere som kan legges til. Kontroller CalDAV-URL-en.';
$labels['caldav_client_error_server_info'] = 'Feil CalDAV-serverinformasjon.';
$labels['caldav_client_error_add_calendar'] = 'Kan ikke lagre kalenderen "$n".';
$labels['caldav_client_error_delete_event'] = 'Kan ikke slette hendelsen';
$labels['caldav_client_error_save_event'] = 'Kan ikke lagre CalDAV-hendelsen: Den kan ha blitt flyttet eller slettet, eller kalenderen er ikke tilgjengelig.';
$labels['caldav_client_error_calendar_added'] = 'Kalenderen "$n" finnes allerede i kalenderlisten din.';

// previewing event
$labels['remove_recurring_event'] = 'Slett tilbakevendende hendelse';
$labels['remove_one'] = 'Slett bare denne forekomsten';
$labels['remove_all'] = 'Slett alle forekomster';
$labels['edit_event'] = 'Rediger hendelse';
$labels['event_removed'] = 'Hendelse slettet.';
$labels['go_to_url'] = 'Gå til URL';
$labels['confirm_remove_event'] = 'Er du sikker på at du vil slette denne hendelsen?';

// calendar/agenda grid
$labels['no_events'] = 'Det er ingen hendelser å vise.';

// importing events
$labels['select_calendar'] = 'Velg kalender';
$labels['select_calendar_for_events'] = 'Velg kalenderen du vil legge til arrangementene i:';
$labels['add_events'] = 'Legg til hendelser';
$labels['add_events_to_calendar'] = 'Legg til arrangementer i kalenderen';
$labels['events_imported_successfully'] = 'Begivenheter importert med suksess';
$labels['events_imported_with_errors'] = 'Noen andre hendelser kunne ikke importeres.';
$labels['error_importing_events'] = 'Det oppstod en feil under import av hendelser til kalenderen.';

// editing events
$labels['calendar'] = 'Kalender';
$labels['new_event'] = 'Ny hendelse';
$labels['summary'] = 'Sammendrag';
$labels['recurrence'] = 'Gjentakelse';
$labels['title'] = 'Tittel';
$labels['location'] = 'Beliggenhet';
$labels['description'] = 'Beskrivelse';
$labels['comment'] = 'Kommentar';
$labels['url'] = 'URL';
$labels['name'] = 'Navn';
$labels['start'] = 'Start';
$labels['end'] = 'Slutt';
$labels['show_timezones'] = 'Vis tidssoner';
$labels['reset_timezones'] = 'Tilbakestill tidssoner til standard';
$labels['all_day'] = 'Hele dagen';
$labels['use_calendar_colors'] = 'Bruk kalenderfarger';
$labels['color'] = 'Farge';
$labels['background'] = 'Bakgrunn';
$labels['text'] = 'Tekst';
$labels['show_me_as'] = 'Vis meg som';
$labels['available'] = 'Tilgjengelig';
$labels['busy'] = 'Opptatt';
$labels['visibility'] = 'Synlighet';
$labels['calendar_default'] = 'Standard kalender';
$labels['public'] = 'Offentlig';
$labels['private'] = 'Privat';
$labels['repeat'] = 'Gjenta';
$labels['repeat_every'] = 'Gjenta hver';
$labels['never'] = 'Aldri';
$labels['daily'] = 'Daglig';
$labels['weekly'] = 'Ukentlig';
$labels['monthly'] = 'Månedlig';
$labels['yearly'] = 'Årlig';
$labels['days'] = 'dag(er)';
$labels['weeks'] = 'uke(r)';
$labels['months'] = 'måned(er)';
$labels['years'] = 'år';
$labels['repeat_on'] = 'Gjenta på';
$labels['repeat_by'] = 'Gjenta av';
$labels['day_of_month'] = 'dag i måneden';
$labels['day_of_week'] = 'ukedag';
$labels['not_specified'] = 'Ikke spesifisert';
$labels['first'] = 'For det første';
$labels['second'] = 'For det andre';
$labels['third'] = 'For det tredje';
$labels['fourth'] = 'Fjerde';
$labels['fifth'] = 'For det femte';
$labels['second_to_last'] = 'Nest sist';
$labels['last'] = 'Siste';
$labels['attachment_too_large'] = 'Vedleggsfilen er for stor. Maksimal tillatt størrelse er $s.';
$labels['bg_color'] = 'Bakgrunnsfarge';
$labels['tx_color'] = 'Tekstfarge';
$labels['add_notification'] = 'Legg til melding';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'Denne hendelsen har ingen varsler.';
$labels['minutes'] = 'minutter';
$labels['hours'] = 'timer';
$labels['link'] = 'Lenke';
$labels['attach_file_from_web'] = 'Legg ved fil';
$labels['attachment_url'] = 'URL for vedlegg';
$labels['invalid_url'] = 'Denne URL-en er ugyldig.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Disk';
$labels['duplicate'] = 'Dupliser';
$labels['duplicate_suffix'] = '(duplikat)';
$labels['no_notifications'] = 'Denne hendelsen har ingen varsler.';
$labels['this_calendar_is_hidden'] = 'Denne kalenderen er skjult. Lagrede hendelser vises ikke før kalenderen er synlig.';
$labels['make_calendar_visible'] = 'Gjør denne kalenderen synlig.';

// recurrence
$labels['recurrence_range'] = 'Intervall for gjentakelse';
$labels['repeat_forever'] = 'Gjenta for alltid';
$labels['repeat_every_weekday'] = 'Gjenta hver ukedag';
$labels['last_day'] = 'Siste dag';
$labels['every'] = 'Hver';
$labels['weekday'] = 'Ukedag';
$labels['weekend_day'] = 'Helgedag';
$labels['day_of_week'] = 'ukedag';
$labels['day_of_month'] = 'dag i måneden';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Slutt etter';
$labels['occurrences'] = 'hendelser';

// end recurrence on date:
$labels['end_by_date'] = 'Avslutt innen dato';
$labels['removed_events_title'] = 'Hendelser fjernet fra tilbakevendende serier';
$labels['restore'] = 'Gjenopprette';

// settings page
$labels['setting_view'] = 'Innledende kalendervisning';
$labels['setting_first_day'] = 'Uken starter den';
$labels['setting_agenda_week_span'] = 'Tidsperiode for agendaen (uker)';
$labels['setting_slot_duration'] = 'Tidsrom på dagen (minutter)';
$labels['setting_scroll_time'] = 'Bla til tid';
$labels['setting_calendar'] = 'Standard kalender for arrangementer';
$labels['setting_refresh'] = 'Oppdater kalendere';
$labels['setting_week_numbers'] = 'Ukenummer i liten kalender';
$labels['setting_event_border'] = 'Kategorifarger som hendelsesgrenser';
$labels['show_sunrise'] = 'Vis soloppgang i månedsvisning';
$labels['show_sunset'] = 'Vis solnedgang i månedsvisning';
$labels['categories'] = 'Kategorier';
$labels['category'] = 'Kategori';
$labels['no_category'] = 'Ingen kategori';
$labels['add_category'] = 'Legg til kategori';
$labels['setting_alarm_sound'] = 'Varslingslyd';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambus';
$labels['alarm_sound_bells'] = 'Klokker';
$labels['alarm_sound_chord'] = 'Akkord';
$labels['alarm_sound_circles'] = 'Sirkler';
$labels['alarm_sound_glass'] = 'Glass';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metall';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Fløyte';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Spill av lyd';

// alarms
$labels['upcoming_events'] = 'Kommende arrangementer';
$labels['dismiss'] = 'Avvise';
$labels['dismiss_all'] = 'Avvise alle';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minutter';
$labels['10_minutes'] = '10 minutter';
$labels['20_minutes'] = '20 minutter';
$labels['30_minutes'] = '30 minutter';
$labels['1_hour'] = '1 time';
$labels['2_hours'] = '2 timer';
$labels['tomorrow'] = 'Inntil i morgen';
$labels['next_week'] = 'Fram til neste uke';

// sidebar
$labels['setting_show_xcalendar'] = 'Vis kalender Agenda';
$labels['todays_agenda'] = 'Dagens agenda';
$labels['no_events_today'] = 'Ingen arrangementer planlagt i dag.';
$labels['sidebar_more'] = 'Mer...';

// event invitation
$labels['itip_title_request'] = 'Du er invitert til et arrangement.';
$labels['itip_title_modified'] = 'Denne hendelsen har blitt endret.';
$labels['itip_title_reply_accepted'] = '$u har akseptert invitasjonen din';
$labels['itip_title_reply_tentative'] = '$u har foreløpig akseptert invitasjonen din';
$labels['itip_title_reply_declined'] = '$u har avslått invitasjonen din';
$labels['itip_title_cancel'] = 'Dette arrangementet er avlyst.';

$labels['itip_email_subject_request'] = 'Invitasjon:';
$labels['itip_email_subject_request_modified'] = 'Hendelse endret:';
$labels['itip_email_subject_reply'] = 'Vedr:';
$labels['itip_email_subject_cancel'] = 'Kansellert:';

$labels['itip_popup_event_added'] = 'Arrangement lagt til i kalenderen.';
$labels['itip_popup_event_updated'] = 'Arrangementet er oppdatert i kalenderen.';
$labels['itip_popup_event_deleted'] = 'Arrangement slettet fra kalenderen.';
$labels['itip_popup_response_sent'] = 'Svar sendt til arrangøren.';

$labels['itip_message_invitation_accepted'] = 'Du har akseptert denne invitasjonen.';
$labels['itip_message_invitation_tentative'] = 'Du har foreløpig akseptert denne invitasjonen.';
$labels['itip_message_invitation_declined'] = 'Du har avslått denne invitasjonen.';
$labels['itip_message_event_deleted'] = 'Arrangementet er slettet fra kalenderen din.';
$labels['itip_message_event_updated'] = 'Arrangementet i kalenderen din er oppdatert.';
$labels['itip_message_event_missing'] = 'Denne hendelsen finnes ikke i noen av kalenderne dine.';

$labels['itip_accept'] = 'Godta';
$labels['itip_tentative'] = 'Foreløpig';
$labels['itip_decline'] = 'Nedgang';
$labels['itip_change_reponse'] = 'Endre svar.';
$labels['itip_add_to_calendar'] = 'Legg til i kalenderen';
$labels['itip_reply_to_sender'] = 'Svar til avsender';
$labels['itip_dont_import'] = 'Ikke legg til i kalenderen';
$labels['itip_update_in_calendar'] = 'Oppdater hendelse i kalenderen';
$labels['itip_delete_from_calendar'] = 'Slett hendelse fra kalenderen';

$labels['mail_subject_invitation'] = 'Invitasjon:';
$labels['mail_subject_update'] = 'Oppdatert invitasjon:';
$labels['mail_subject_cancel'] = 'Arrangementet er avlyst:';

// event email notifications
$labels['mail_title'] = 'Tittel:';
$labels['mail_start'] = 'Start:';
$labels['mail_end'] = 'Slutt:';
$labels['mail_description'] = 'Beskrivelse:';
$labels['mail_location'] = 'Beliggenhet:';

// event attendees
$labels['attendees'] = 'Deltakere';
$labels['attendee_role'] = 'Rolle';
$labels['attendee_role_required'] = 'Nødvendig';
$labels['attendee_role_optional'] = 'Valgfritt';
$labels['attendee_role_chair'] = 'Styreformann';
$labels['attendee_role_none'] = 'Ikke-deltaker';
$labels['organizer'] = 'Arrangør';
$labels['attendance'] = 'Deltakelse';
$labels['your_status'] = 'Din status';
$labels['attendance_going'] = 'Går du?';
$labels['attendee_status_yes'] = 'Ja';
$labels['attendee_status_no'] = 'Nei';
$labels['attendee_status_maybe'] = 'Kanskje';
$labels['attendee_status_delegated'] = 'Delegert';
$labels['attendee_status_waiting'] = 'Venter';
$labels['attendee_status_waiting_for_confirmation'] = 'Venter på bekreftelse';
$labels['attendee_status_info_yes'] = 'Vil delta';
$labels['attendee_status_info_no'] = 'Vil ikke delta';
$labels['attendee_status_info_maybe'] = 'Kan delta';
$labels['attendee_status_info_delegated'] = 'Delegert deltakelse';
$labels['attendee_role'] = 'Rolle';
$labels['attendee_role_info'] = 'Angi om deltakeren skal delta i eller lede møtet.';
$labels['attendee_notify'] = 'Varsle';
$labels['attendee_notify_info'] = 'Hvis det er krysset av, sendes en e-post med invitasjon til deltakeren.';
$labels['add_attendee'] = 'Legg til deltaker';
$labels['attendee_notify_question'] = 'Sende e-postmeldinger til deltakerne?';
$labels['attendee_notify_textarea'] = 'Legg til en valgfri melding';
$labels['attendee_notify_send'] = 'Send';
$labels['attendee_notify_dont'] = 'Ikke send';
$labels['attendee_notify_cancel'] = 'Avbryt';
$labels['new_attendee_not_saved_warning'] = 'Du har angitt e-postadressen til en deltaker, men ikke lagt den til i deltakerlisten. Vil du fortsette å lagre uten denne deltakeren?';

// import / export
$labels['import_export'] = 'Import/eksport';
$labels['export_to_file'] = 'Eksporter til fil';
$labels['export_info'] = 'Klikk på denne knappen for å eksportere og laste ned hendelsene i denne kalenderen i iCalendar-format (ics). Hvis kalenderen inneholder mange arrangementer, kan eksportprosessen ta noen øyeblikk.';
$labels['export_no_events'] = 'Denne kalenderen har ingen arrangementer å eksportere.';
$labels['import_from_file'] = 'Import fra fil';
$labels['import_info'] = 'Klikk på denne knappen for å importere hendelser til denne kalenderen fra en iCalendar (ics)-fil.';

// email notifications
$labels['appointment_reminder'] = 'Påminnelse om avtale';
$labels['email_notification_subject'] = 'Påminnelse: $t ved $d ($z)';
$labels['email_notification_cron_error'] = 'E-postvarsler er deaktivert. Kontakt administratoren.';
$labels['alarm_before_event_starts'] = 'Før arrangementet starter';
$labels['alarm_after_event_starts'] = 'Etter at arrangementet har startet';
$labels['alarm_before_event_ends'] = 'Før arrangementet avsluttes';
$labels['alarm_after_event_ends'] = 'Etter at arrangementet er avsluttet';
$labels['alarm_at_specific_time'] = 'På et bestemt tidspunkt';

// search
$labels['specify_at_least_one_calendar'] = 'Angi minst én kalender du vil søke i.';
$labels['specify_text_to_search'] = 'Skriv inn teksten du vil søke etter.';
$labels['clear_search_results'] = 'Klar';
$labels['search_outdated_message'] = 'Søkeresultatene kan være utdaterte. Vurder å kjøre søket på nytt.';
$labels['date_range'] = 'Datointervall';
$labels['date_range_subtitle'] = 'Datointervall:';
$labels['search_results'] = 'Resultater funnet: $n';
$labels['modify_search_options'] = 'Prøv å endre søkealternativene.';
$labels['show_shortcuts'] = 'Vis snarveier';
$labels['hide_shortcuts'] = 'Skjul snarveier';
$labels['this_month'] = 'Denne måneden';
$labels['this_year'] = 'I år';
$labels['past_30_days'] = 'De siste 30 dagene';
$labels['next_30_days'] = 'De neste 30 dagene';
$labels['past_6_months'] = 'De siste 6 månedene';
$labels['next_6_months'] = 'De neste 6 månedene';

// calendar grid
$labels['cannot_load_events'] = 'Kan ikke laste inn hendelser for kalenderen "$c".';
