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
$labels['create_event'] = 'Gebeurtenis creëren';
$labels['create_event_button'] = 'Nieuw evenement';
$labels['day'] = 'Dag';
$labels['week'] = 'Week';
$labels['month'] = 'Maand';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Kalenders';
$labels['my_calendar'] = 'Mijn kalender';
$labels['create_calendar'] = 'Kalender maken';
$labels['new_calendar'] = 'Nieuwe kalender';
$labels['edit_calendar'] = 'Kalender bewerken';
$labels['remove_calendar'] = 'Kalender verwijderen';
$labels['confirm_remove_calendar'] = 'Weet u zeker dat u deze agenda wilt verwijderen?';
$labels['calendar_removed'] = 'Kalender verwijderd.';
$labels['notifications'] = 'Meldingen';
$labels['default_event_visibility'] = 'Standaard zichtbaarheid van gebeurtenissen';
$labels['time_zone'] = 'Kalender tijdzone';
$labels['share_calendar'] = 'Kalender delen';
$labels['permissions'] = 'Machtigingen';
$labels['permission_publish_calendar'] = 'Kalender publiceren';
$labels['permission_share_calendar'] = 'Kalender delen';
$labels['permission_edit_events'] = 'Gebeurtenissen bewerken';
$labels['permission_see_details'] = 'Zie details';
$labels['permission_publish_calendar_info'] = 'Indien aangevinkt kan de gebruiker de agenda publiceren.';
$labels['permission_share_calendar_info'] = 'Indien aangevinkt, kan de gebruiker de agenda delen met andere gebruikers.';
$labels['permission_edit_events_info'] = 'Indien aangevinkt, kan de gebruiker agenda-evenementen bewerken en verwijderen.';
$labels['permission_see_details_info'] = 'Indien aangevinkt, worden alle details van de gebeurtenis gedeeld. Indien niet aangevinkt, wordt alleen de beschikbare/bezette informatie gedeeld.';
$labels['share_send_notification'] = 'Verwittig';
$labels['share_send_notification_info'] = 'Indien aangevinkt, wordt een e-mail naar de gebruiker gestuurd om hem of haar te informeren over de nieuw gedeelde agenda.';
$labels['new_share_not_saved_warning'] = 'Je hebt een e-mail getypt om de agenda mee te delen, maar hebt niet op de knop Delen geklikt om hem toe te voegen aan de gedeelde lijst. Blijft u opslaan zonder de agenda te delen met de opgegeven gebruiker?';
$labels['share'] = 'Deel';
$labels['shared_marker'] = '(Gedeeld)';
$labels['owner'] = 'Eigenaar';
$labels['publish_calendar'] = 'Kalender publiceren';
$labels['create_link'] = 'Koppeling maken';
$labels['reset'] = 'Reset';
$labels['remove'] = 'Verwijder';
$labels['publish_full'] = 'Publiceer alle informatie over het evenement.';
$labels['publish_busy'] = 'Publiceer alleen de vrije/actieve informatie.';
$labels['sync_no_id'] = 'CalDAV opties worden beschikbaar nadat u de kalender heeft opgeslagen.';
$labels['share_no_id'] = 'Opties voor het delen van agenda\'s worden beschikbaar nadat u de agenda hebt opgeslagen.';
$labels['publish_no_id'] = 'Opties voor het publiceren van kalenders worden beschikbaar nadat u de kalender hebt opgeslagen.';
$labels['import_no_id'] = 'De opties voor importeren en exporteren worden beschikbaar nadat je de agenda hebt opgeslagen.';
$labels['unable_to_save_calendar_data'] = 'Kan kalendergegevens niet opslaan.';
$labels['unable_to_retrieve_calendar_data'] = 'Kan de kalendergegevens niet ophalen. De kalender is misschien niet meer beschikbaar.';
$labels['unable_add_shared_calendar'] = 'Kan de gedeelde kalender niet toevoegen. De agenda is misschien niet meer beschikbaar.';
$labels['itip_organizer_message'] = 'U bent uitgenodigd voor dit evenement, maar u bent niet de organisator. De wijzigingen die u aanbrengt in dit evenement worden niet naar de andere aanwezigen gestuurd.';
$labels['cannot_share_with_yourself'] = 'Je kunt je eigen agenda niet met jezelf delen.';
$labels['cannot_share_with_calendar_owner'] = 'U kunt deze kalender niet delen met de eigenaar van de kalender ($e)';
$labels['setting_default_notification_type'] = 'Standaardmeldingen voor nieuwe gebeurtenissen';
$labels['calendar_name_cannot_be_empty'] = 'De naam van de agenda mag niet leeg zijn.';

// caldav server
$labels['sync_calendar'] = 'Kalender synchroniseren';
$labels['password_too_short'] = 'Het wachtwoord moet minstens 10 tekens lang zijn';
$labels['read_only'] = 'Alleen lezen';
$labels['add_connection'] = 'Verbinding toevoegen';
$labels['show_details'] = 'Details tonen';
$labels['hide_details'] = 'Details verbergen';
$labels['caldav_connection_password'] = 'Het wachtwoord dat u bij het maken van deze verbinding heeft opgegeven.';
$labels['yes'] = 'Ja';
$labels['no'] = 'Geen';
$labels['confirm_remove_sync'] = 'Weet je zeker dat je deze verbinding permanent wilt verwijderen?';
$labels['remove_sync_error'] = 'Er was een fout bij het verwijderen van de verbinding.';
$labels['create_new_caldav_connection'] = 'Nieuwe CalDav-verbinding maken';
$labels['your_caldav_connections'] = 'Uw CalDav verbindingen';
$labels['created_at'] = 'Gemaakt';
$labels['connected_at'] = 'Laatste synchronisatie';
$labels['never'] = 'Nooit';
$labels['alternate_url'] = 'Alternatieve URL';
$labels['caldav_password_info'] = 'Om veiligheidsredenen wordt dit wachtwoord niet opgeslagen op de server. Als u het vergeten bent, verwijdert u deze verbinding en maakt u een nieuwe aan.';
$labels['caldav_url_info'] = 'Gebruik deze URL met programma\'s die het ontdekken van agenda\'s ondersteunen, bijvoorbeeld: iOS en Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Gebruik deze URL met apps die niet werken met de korte url hierboven, bijvoorbeeld: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'Ongeldige CalDAV-server-URL.';
$labels['cannot_resolve_caldav_host'] = 'De host van de CalDAV-server kan niet worden gevonden.';
$labels['private_caldav_addresses_not_allowed'] = 'Privé- en gereserveerde CalDAV-adressen zijn niet toegestaan.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Gedeelde kalender toevoegen';
$labels['shared_calendars'] = 'Gedeelde kalenders';
$labels['no_shared_calendars'] = 'Er zijn geen gedeelde kalenders die u kunt toevoegen.';
$labels['add_to_my_calendars'] = 'Toevoegen aan mijn agenda’s';
$labels['remove_from_my_calendars'] = 'Uit mijn agenda’s verwijderen';
$labels['unshare'] = 'Unshare';
$labels['unshare_warning'] = 'Als u deze agenda weer deelt, verdwijnt hij uit deze lijst en kunt u hem niet meer toevoegen.';
$labels['calendar_name'] = 'Naam kalender';
$labels['calendar_owner'] = 'Kalender eigenaar';
$labels['shared_date'] = 'Gedeelde';
$labels['unable_to_share'] = 'De agenda kan niet worden gedeeld met de volgende gebruikers omdat hun e-mailaccounts zich op een externe server bevinden:';

// shared calendar email
$labels['shared_email_subject'] = '$u heeft een agenda met u gedeeld';
$labels['shared_email_body'] = 'De gebruiker $u heeft u toegang gegeven tot een kalender genaamd "$c". Om deze agenda toe te voegen aan uw lijst van actieve kalenders, klikt u op de onderstaande link:';
$labels['shared_email_body_cpanel'] = 'De gebruiker $u heeft u toegang gegeven tot een kalender genaamd "$c". Om deze agenda toe te voegen aan uw lijst met actieve kalenders, logt u in op Roundcube, gaat u naar Agenda, klikt u op het plus-pictogram in de zijbalk en selecteert u Gedeelde agenda toevoegen.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Vakantiekalenders';
$labels['edit_holiday_calendar'] = 'Vakantiekalender bewerken';
$labels['religious_holidays'] = 'Religieuze feestdagen';
$labels['country_holidays'] = 'Vakantie op het platteland';
$labels['add'] = 'Toevoegen';
$labels['no_more_holidays_allowed'] = 'U kunt geen vakantiekalenders meer toevoegen.';
$labels['cannot_load_holidays'] = 'Kan geen vakantie laden van de Google-server.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Verjaardagskalender toevoegen';
$labels['edit_birthday_calendar'] = 'Verjaardagskalender bewerken';
$labels['contact_birthdays'] = 'Contact Verjaardagen';
$labels['contact_birthdays_info'] = 'Deze kalender haalt verjaardagsinformatie op uit je lijst met contactpersonen en geeft deze weer als kalendergebeurtenissen.';
$labels['birthday_event_title'] = 'Verjaardag: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Google kalender toevoegen';
$labels['edit_google_calendar'] = 'Google-kalender bewerken';
$labels['google_id'] = 'Google kalender ID';
$labels['new_google_calendar'] = 'Nieuwe Google Agenda';
$labels['specify_google_calendar_id'] = 'Typ de Google kalender ID';
$labels['calendar_id_format_error'] = 'De Google agenda ID moet het @ teken bevatten.';
$labels['cannot_load_google_calendar'] = 'De Google-agenda "$c" kan niet worden geladen. Controleer of de agenda-ID correct is en of de agenda openbaar is gemaakt.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'CalDAV-agenda toevoegen';
$labels['edit_caldav_calendar'] = 'CalDAV-agenda bewerken';
$labels['add_caldav_calendar_information'] = 'Om je te abonneren op een CalDAV-agenda, geef je de informatie van de CalDAV-server op en klik je op Agenda\'s zoeken.';
$labels['caldav_url'] = 'CalDAV URL';
$labels['find_calendars'] = 'Kalenders zoeken';
$labels['searching_for_calendars'] = 'Zoeken naar kalenders, even geduld aub...';
$labels['select_calendars'] = 'Selecteer kalenders om toe te voegen:';
$labels['read_only_status_yes'] = 'Ja';
$labels['read_only_status_no'] = 'Geen';
$labels['subscribed'] = 'Ingeschreven';

$labels['refresh_calendar'] = 'Vernieuw kalender';
$labels['refresh_every_minute'] = 'Elke minuut';
$labels['refresh_every_5_minutes'] = 'Elke 5 minuten';
$labels['refresh_every_15_minutes'] = 'Elke 15 minuten';
$labels['refresh_every_30_minutes'] = 'Elke 30 minuten';
$labels['refresh_every_60_minutes'] = 'Elke 60 minuten';
$labels['refresh_never'] = 'Nooit';

$labels['cannot_connect_to_caldav_server'] = 'Kan geen verbinding maken met CalDAV-server "$n".';
$labels['caldav_client_error_url'] = 'Kan geen verbinding maken met de CalDAV-server. Controleer de URL van de server.';
$labels['caldav_client_error_credentials'] = 'Onjuiste CalDAV gebruikersnaam of wachtwoord.';
$labels['caldav_client_error_empty'] = 'De server heeft geen kalenders teruggestuurd die kunnen worden toegevoegd. Controleer de CalDAV URL.';
$labels['caldav_client_error_server_info'] = 'Onjuiste CalDAV-serverinformatie.';
$labels['caldav_client_error_add_calendar'] = 'Kan agenda "$n" niet opslaan.';
$labels['caldav_client_error_delete_event'] = 'Kan gebeurtenis niet verwijderen';
$labels['caldav_client_error_save_event'] = 'Het CalDAV-evenement kan niet worden opgeslagen: het is mogelijk verplaatst of verwijderd, of de agenda is niet beschikbaar.';
$labels['caldav_client_error_calendar_added'] = 'De agenda "$n" staat al in je agendalijst.';

// previewing event
$labels['remove_recurring_event'] = 'Terugkerende gebeurtenis verwijderen';
$labels['remove_one'] = 'Alleen deze instantie verwijderen';
$labels['remove_all'] = 'Alle instanties verwijderen';
$labels['edit_event'] = 'Bewerk evenement';
$labels['event_removed'] = 'Gebeurtenis verwijderd.';
$labels['go_to_url'] = 'Ga naar URL';
$labels['confirm_remove_event'] = 'Weet je zeker dat je deze gebeurtenis wilt verwijderen?';

// calendar/agenda grid
$labels['no_events'] = 'Er zijn geen gebeurtenissen om weer te geven.';

// importing events
$labels['select_calendar'] = 'Selecteer kalender';
$labels['select_calendar_for_events'] = 'Selecteer de kalender waaraan u de gebeurtenissen wilt toevoegen:';
$labels['add_events'] = 'Gebeurtenissen toevoegen';
$labels['add_events_to_calendar'] = 'Evenementen toevoegen aan de kalender';
$labels['events_imported_successfully'] = 'Gebeurtenissen succesvol geïmporteerd';
$labels['events_imported_with_errors'] = 'Een andere gebeurtenis kon niet worden geïmporteerd.';
$labels['error_importing_events'] = 'Er was een fout bij het importeren van gebeurtenissen in de kalender';

// editing events
$labels['calendar'] = 'Kalender';
$labels['new_event'] = 'Nieuw evenement';
$labels['summary'] = 'Samenvatting';
$labels['recurrence'] = 'Herhaling';
$labels['title'] = 'Titel';
$labels['location'] = 'Locatie';
$labels['description'] = 'Beschrijving';
$labels['comment'] = 'Commentaar';
$labels['url'] = 'URL';
$labels['name'] = 'Naam';
$labels['start'] = 'Start';
$labels['end'] = 'Einde';
$labels['show_timezones'] = 'Toon tijdzones';
$labels['reset_timezones'] = 'Tijdzones terugzetten naar standaard';
$labels['all_day'] = 'De hele dag';
$labels['use_calendar_colors'] = 'Gebruik kalenderkleuren';
$labels['color'] = 'Kleur';
$labels['background'] = 'Achtergrond';
$labels['text'] = 'Tekst';
$labels['show_me_as'] = 'Laat me zien als';
$labels['available'] = 'Beschikbaar';
$labels['busy'] = 'Druk';
$labels['visibility'] = 'Zichtbaarheid';
$labels['calendar_default'] = 'Standaard kalender';
$labels['public'] = 'Publiek';
$labels['private'] = 'Privé';
$labels['repeat'] = 'Herhaal';
$labels['repeat_every'] = 'Herhaal elke';
$labels['never'] = 'Nooit';
$labels['daily'] = 'Dagelijks';
$labels['weekly'] = 'Wekelijks';
$labels['monthly'] = 'Maandelijks';
$labels['yearly'] = 'Jaarlijks';
$labels['days'] = 'dag(en)';
$labels['weeks'] = 'week(en)';
$labels['months'] = 'maand(en)';
$labels['years'] = 'jaar';
$labels['repeat_on'] = 'Herhaal op';
$labels['repeat_by'] = 'Herhaal door';
$labels['day_of_month'] = 'dag van de maand';
$labels['day_of_week'] = 'dag van de week';
$labels['not_specified'] = 'Niet gespecificeerd';
$labels['first'] = 'Eerst';
$labels['second'] = 'Tweede';
$labels['third'] = 'Derde';
$labels['fourth'] = 'Vierde';
$labels['fifth'] = 'Vijfde';
$labels['second_to_last'] = 'Op een na laatste';
$labels['last'] = 'Laatste';
$labels['attachment_too_large'] = 'Het bestand met de bijlage is te groot. De maximaal toegestane grootte is $s.';
$labels['bg_color'] = 'Achtergrondkleur';
$labels['tx_color'] = 'Tekstkleur';
$labels['add_notification'] = 'Kennisgeving toevoegen';
$labels['popup'] = 'Pop-up';
$labels['no_notifications'] = 'Deze gebeurtenis heeft geen meldingen.';
$labels['minutes'] = 'minuten';
$labels['hours'] = 'uren';
$labels['link'] = 'Link';
$labels['attach_file_from_web'] = 'Bestand bijvoegen';
$labels['attachment_url'] = 'Bijlage URL';
$labels['invalid_url'] = 'Deze URL is ongeldig.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Dupliceer';
$labels['duplicate_suffix'] = '(duplicaat)';
$labels['no_notifications'] = 'Deze gebeurtenis heeft geen meldingen.';
$labels['this_calendar_is_hidden'] = 'Deze agenda is verborgen. Opgeslagen afspraken worden pas weergegeven als de agenda zichtbaar is.';
$labels['make_calendar_visible'] = 'Maak deze kalender zichtbaar.';

// recurrence
$labels['recurrence_range'] = 'Herhalingsbereik';
$labels['repeat_forever'] = 'Voor altijd herhalen';
$labels['repeat_every_weekday'] = 'Herhaal elke weekdag';
$labels['last_day'] = 'Laatste dag';
$labels['every'] = 'Elke';
$labels['weekday'] = 'Weekdag';
$labels['weekend_day'] = 'Weekend dag';
$labels['day_of_week'] = 'dag van de week';
$labels['day_of_month'] = 'dag van de maand';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Einde na';
$labels['occurrences'] = 'voorvallen';

// end recurrence on date:
$labels['end_by_date'] = 'Einde per datum';
$labels['removed_events_title'] = 'Uit terugkerende reeksen verwijderde evenementen';
$labels['restore'] = 'Herstel';

// settings page
$labels['setting_view'] = 'Eerste kalenderweergave';
$labels['setting_first_day'] = 'De week begint op';
$labels['setting_agenda_week_span'] = 'Duur van de agenda (weken)';
$labels['setting_slot_duration'] = 'Dagdeel (minuten)';
$labels['setting_scroll_time'] = 'Scroll naar tijd';
$labels['setting_calendar'] = 'Standaard kalender voor evenementen';
$labels['setting_refresh'] = 'Vernieuw kalenders';
$labels['setting_week_numbers'] = 'Weeknummers in kleine kalender';
$labels['setting_event_border'] = 'Categoriekleuren als evenementgrenzen';
$labels['show_sunrise'] = 'Toon zonsopgang in maandweergave';
$labels['show_sunset'] = 'Toon zonsondergang in maandweergave';
$labels['categories'] = 'Categorieën';
$labels['category'] = 'Categorie';
$labels['no_category'] = 'Geen categorie';
$labels['add_category'] = 'Categorie toevoegen';
$labels['setting_alarm_sound'] = 'Meldingsgeluid';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bamboe';
$labels['alarm_sound_bells'] = 'Klokken';
$labels['alarm_sound_chord'] = 'Akkoord';
$labels['alarm_sound_circles'] = 'Cirkels';
$labels['alarm_sound_glass'] = 'Glas';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metaal';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Fluit';
$labels['alarm_sound_yoohoo'] = 'Joehoe';
$labels['play_sound'] = 'Geluid afspelen';

// alarms
$labels['upcoming_events'] = 'Komende evenementen';
$labels['dismiss'] = 'Ontsla';
$labels['dismiss_all'] = 'Ontsla alle';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minuten.';
$labels['10_minutes'] = '10 minuten.';
$labels['20_minutes'] = '20 minuten';
$labels['30_minutes'] = '30 minuten';
$labels['1_hour'] = '1 uur.';
$labels['2_hours'] = '2 uur.';
$labels['tomorrow'] = 'Tot morgen.';
$labels['next_week'] = 'Tot volgende week.';

// sidebar
$labels['setting_show_xcalendar'] = 'Toon Kalender Agenda';
$labels['todays_agenda'] = 'Agenda van vandaag';
$labels['no_events_today'] = 'Geen evenementen gepland voor vandaag.';
$labels['sidebar_more'] = 'Meer...';

// event invitation
$labels['itip_title_request'] = 'Je bent uitgenodigd voor een evenement.';
$labels['itip_title_modified'] = 'Dit evenement is gewijzigd.';
$labels['itip_title_reply_accepted'] = '$u heeft uw uitnodiging geaccepteerd';
$labels['itip_title_reply_tentative'] = '$u heeft uw uitnodiging voorlopig geaccepteerd';
$labels['itip_title_reply_declined'] = '$u heeft uw uitnodiging afgewezen';
$labels['itip_title_cancel'] = 'Dit evenement is geannuleerd.';

$labels['itip_email_subject_request'] = 'Uitnodiging:';
$labels['itip_email_subject_request_modified'] = 'Gebeurtenis gewijzigd:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Geannuleerd:';

$labels['itip_popup_event_added'] = 'Evenement toegevoegd aan kalender.';
$labels['itip_popup_event_updated'] = 'Evenement bijgewerkt in kalender.';
$labels['itip_popup_event_deleted'] = 'Gebeurtenis verwijderd uit de kalender.';
$labels['itip_popup_response_sent'] = 'Antwoord gestuurd naar de organisator.';

$labels['itip_message_invitation_accepted'] = 'Je hebt deze uitnodiging geaccepteerd.';
$labels['itip_message_invitation_tentative'] = 'Je hebt deze uitnodiging voorlopig geaccepteerd.';
$labels['itip_message_invitation_declined'] = 'Je hebt deze uitnodiging afgeslagen.';
$labels['itip_message_event_deleted'] = 'Het evenement is verwijderd uit uw agenda.';
$labels['itip_message_event_updated'] = 'Het evenement in uw agenda is bijgewerkt.';
$labels['itip_message_event_missing'] = 'Dit evenement staat in geen enkele agenda.';

$labels['itip_accept'] = 'Accepteer';
$labels['itip_tentative'] = 'Voorlopig';
$labels['itip_decline'] = 'Daling';
$labels['itip_change_reponse'] = 'Reactie op verandering.';
$labels['itip_add_to_calendar'] = 'Toevoegen aan kalender';
$labels['itip_reply_to_sender'] = 'Antwoord aan de afzender';
$labels['itip_dont_import'] = 'Niet toevoegen aan kalender';
$labels['itip_update_in_calendar'] = 'Gebeurtenis in kalender bijwerken';
$labels['itip_delete_from_calendar'] = 'Gebeurtenis uit agenda verwijderen';

$labels['mail_subject_invitation'] = 'Uitnodiging:';
$labels['mail_subject_update'] = 'Bijgewerkte uitnodiging:';
$labels['mail_subject_cancel'] = 'Geannuleerd:';

// event email notifications
$labels['mail_title'] = 'Titel:';
$labels['mail_start'] = 'Begin:';
$labels['mail_end'] = 'Einde:';
$labels['mail_description'] = 'Beschrijving:';
$labels['mail_location'] = 'Locatie:';

// event attendees
$labels['attendees'] = 'Aanwezigen';
$labels['attendee_role'] = 'Rol';
$labels['attendee_role_required'] = 'Vereist';
$labels['attendee_role_optional'] = 'Optioneel';
$labels['attendee_role_chair'] = 'Stoel';
$labels['attendee_role_none'] = 'Niet-deelnemer';
$labels['organizer'] = 'Organisator';
$labels['attendance'] = 'Aanwezigheid';
$labels['your_status'] = 'Uw status';
$labels['attendance_going'] = 'Ga je?';
$labels['attendee_status_yes'] = 'Ja';
$labels['attendee_status_no'] = 'Geen';
$labels['attendee_status_maybe'] = 'Misschien';
$labels['attendee_status_delegated'] = 'Gedelegeerd';
$labels['attendee_status_waiting'] = 'Wachten';
$labels['attendee_status_waiting_for_confirmation'] = 'Wachten op bevestiging';
$labels['attendee_status_info_yes'] = 'Zal aanwezig zijn';
$labels['attendee_status_info_no'] = 'Zal niet aanwezig zijn';
$labels['attendee_status_info_maybe'] = 'Zou kunnen bijwonen';
$labels['attendee_status_info_delegated'] = 'Gedelegeerde aanwezigheid';
$labels['attendee_role'] = 'Rol';
$labels['attendee_role_info'] = 'Geef aan of de genodigde de vergadering moet bijwonen of voorzitten.';
$labels['attendee_notify'] = 'Verwittig';
$labels['attendee_notify_info'] = 'Indien aangevinkt, wordt een uitnodigingse-mail naar de deelnemer gestuurd.';
$labels['add_attendee'] = 'Deelnemer toevoegen';
$labels['attendee_notify_question'] = 'Notificatiemails sturen naar deelnemers?';
$labels['attendee_notify_textarea'] = 'Voeg een optioneel bericht toe';
$labels['attendee_notify_send'] = 'Stuur';
$labels['attendee_notify_dont'] = 'Stuur geen';
$labels['attendee_notify_cancel'] = 'Annuleren';
$labels['new_attendee_not_saved_warning'] = 'Je hebt het e-mailadres van een deelnemer ingevoerd, maar deze niet aan de deelnemerslijst toegevoegd. Wil je doorgaan met opslaan zonder deze deelnemer?';

// import / export
$labels['import_export'] = 'Import / Export';
$labels['export_to_file'] = 'Exporteren naar bestand';
$labels['export_info'] = 'Klik op deze knop om de gebeurtenissen van deze kalender te exporteren en te downloaden in het formaat iCalendar (ics). Als de agenda veel gebeurtenissen bevat, kan het exporteren enkele ogenblikken duren.';
$labels['export_no_events'] = 'Deze kalender heeft geen evenementen om te exporteren.';
$labels['import_from_file'] = 'Importeren uit bestand';
$labels['import_info'] = 'Klik op deze knop om gebeurtenissen uit een iCalendar (ics)-bestand in deze agenda te importeren.';

// email notifications
$labels['appointment_reminder'] = 'Herinnering aan afspraak';
$labels['email_notification_subject'] = 'Herinnering: $t bij $d ($z)';
$labels['email_notification_cron_error'] = 'E-mailmeldingen zijn uitgeschakeld. Neem contact op met de beheerder.';
$labels['alarm_before_event_starts'] = 'Voordat het evenement begint';
$labels['alarm_after_event_starts'] = 'Na aanvang van het evenement';
$labels['alarm_before_event_ends'] = 'Voordat het evenement eindigt';
$labels['alarm_after_event_ends'] = 'Na afloop van het evenement';
$labels['alarm_at_specific_time'] = 'Op een bepaald tijdstip';

// search
$labels['specify_at_least_one_calendar'] = 'Geef minstens één kalender op om in te zoeken.';
$labels['specify_text_to_search'] = 'Typ de tekst waarnaar u wilt zoeken.';
$labels['clear_search_results'] = 'Duidelijk';
$labels['search_outdated_message'] = 'De zoekresultaten kunnen verouderd zijn. Overweeg om uw zoekopdracht opnieuw uit te voeren.';
$labels['date_range'] = 'Datumbereik';
$labels['date_range_subtitle'] = 'Datumbereik:';
$labels['search_results'] = 'Gevonden resultaten: $n';
$labels['modify_search_options'] = 'Probeer de zoekopties aan te passen.';
$labels['show_shortcuts'] = 'Snelkoppelingen weergeven';
$labels['hide_shortcuts'] = 'Snelkoppelingen verbergen';
$labels['this_month'] = 'Deze maand';
$labels['this_year'] = 'Dit jaar';
$labels['past_30_days'] = 'Afgelopen 30 dagen';
$labels['next_30_days'] = 'Volgende 30 dagen';
$labels['past_6_months'] = 'Afgelopen 6 maanden';
$labels['next_6_months'] = 'Volgende 6 maanden';

// calendar grid
$labels['cannot_load_events'] = 'Kan gebeurtenissen voor kalender "$c" niet laden.';
