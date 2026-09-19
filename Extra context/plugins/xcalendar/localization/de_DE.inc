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
$labels['create_event'] = 'Ereignis erstellen';
$labels['create_event_button'] = 'Erstellen';
$labels['day'] = 'Tag';
$labels['week'] = 'Woche';
$labels['month'] = 'Monat';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Kalender';
$labels['my_calendar'] = 'Mein Kalender';
$labels['create_calendar'] = 'Kalender erstellen';
$labels['new_calendar'] = 'Neuer Kalender';
$labels['edit_calendar'] = 'Kalender bearbeiten';
$labels['remove_calendar'] = 'Kalender löschen';
$labels['confirm_remove_calendar'] = 'Sind Sie sicher, dass Sie den Kalender löschen wollen?';
$labels['calendar_removed'] = 'Kalender gelöscht';
$labels['notifications'] = 'Benachrichtigungen';
$labels['default_event_visibility'] = 'Standard Ereignisanzeige';
$labels['time_zone'] = 'Kalender Zeitzone';
$labels['share_calendar'] = 'Kalender teilen';
$labels['permissions'] = 'Berechtigungen';
$labels['permission_publish_calendar'] = 'Kalender veröffentlichen';
$labels['permission_share_calendar'] = 'Kann Kalender teilen';
$labels['permission_edit_events'] = 'Kann Ereignisse bearbeiten';
$labels['permission_see_details'] = 'Details sehen';
$labels['permission_publish_calendar_info'] = 'Wenn ausgewählt kann der Nutzer den Kalender veröffentlichen.';
$labels['permission_share_calendar_info'] = 'Wenn ausgewählt, kann der Nutzer den Kalender mit anderen Nutzern teilen.';
$labels['permission_edit_events_info'] = 'Wenn ausgewählt, kann der Nutzer Kalenderereignisse bearbeiten oder löschen.';
$labels['permission_see_details_info'] = 'Wenn ausgewählt, werden alle Ereignisdetails geteilt. Wenn nicht ausgewählt, wird nur die verfügbar/beschäftigt Information geteilt.';
$labels['share_send_notification'] = 'Benachrichtigen';
$labels['share_send_notification_info'] = 'Wenn ausgewählt, wird eine E-Mail über den neuen geteilten Kalender an den Nutzer gesendet.';
$labels['new_share_not_saved_warning'] = 'Sie haben eine E-Mail zum Teilen des Kalenders erstellt, aber Sie haben nicht den Teilen-Button gedrückt um diesen der Liste der geteilten Kalender hinzuzufügen. Fortfahren ohne den Kalender mit dem ausgewählten Nutzer zu teilen?';
$labels['share'] = 'Teilen';
$labels['shared_marker'] = '(Geteilt)';
$labels['owner'] = 'Besitzer';
$labels['publish_calendar'] = 'Kalender veröffentlichen';
$labels['create_link'] = 'Link erstellen';
$labels['reset'] = 'Zurücksetzen';
$labels['remove'] = 'Entfernen';
$labels['publish_full'] = 'Veröffentlichen von allen Ereignisinformationen.';
$labels['publish_busy'] = 'Nur verfügbar/beschäftigt Information veröffentlichen.';
$labels['sync_no_id'] = 'CalDAV Einstellungen werden verfügbar, sobald Sie den Kalender gespeichert haben..';
$labels['share_no_id'] = 'Kalender teilen Einstellungen werden verfügbar, sobald Sie den Kalender gespeichert haben.';
$labels['publish_no_id'] = 'Kalender veröffentlichen Einstellungen werden verfügbar, sobald Sie den Kalender gespeichert haben.';
$labels['import_no_id'] = 'Die Import- und Exportoptionen werden verfügbar, nachdem Sie den Kalender gespeichert haben.';
$labels['unable_to_save_calendar_data'] = 'Speichern der Kalenderdaten nicht möglich.';
$labels['unable_to_retrieve_calendar_data'] = 'Abrufen der Kalenderdaten nicht möglich. Der Kalender ist vielleicht nicht mehr verfügbar.';
$labels['unable_add_shared_calendar'] = 'Hinzufügen des geteilten Kalenders nicht möglich. Der Kalender ist vielleicht nicht mehr verfügbar.';
$labels['itip_organizer_message'] = 'Sie wurden zu dieser Veranstaltung eingeladen, sind aber nicht der Organisator. Die Änderungen, die Sie an dieser Veranstaltung vornehmen, werden nicht an die anderen Teilnehmer weitergeleitet.';
$labels['cannot_share_with_yourself'] = 'Sie können Ihren eigenen Kalender nicht mit sich selbst teilen.';
$labels['cannot_share_with_calendar_owner'] = 'Sie können diesen Kalender nicht für den Kalenderbesitzer ($e) freigeben.';
$labels['setting_default_notification_type'] = 'Standardbenachrichtigungen für neue Ereignisse';
$labels['calendar_name_cannot_be_empty'] = 'Der Kalendername darf nicht leer sein.';

// caldav server
$labels['sync_calendar'] = 'Kalender synchronisieren';
$labels['password_too_short'] = 'Das Passwort muss mindestens 10 Zeichen enthalten.';
$labels['read_only'] = 'Nur Lesen';
$labels['add_connection'] = 'Verbindung hinzufügen';
$labels['show_details'] = 'Details anzeigen';
$labels['hide_details'] = 'Details verstecken';
$labels['caldav_connection_password'] = 'Das Passwort, dass sie bei Erstellung der Verbindung festgelegt haben.';
$labels['yes'] = 'Ja';
$labels['no'] = 'Nein';
$labels['confirm_remove_sync'] = 'Sind Sie sicher, dass sie die Verbindung dauerhaft entfernen wollen?';
$labels['remove_sync_error'] = 'Ein Fehler ist beim Entfernen der Verbindung aufgetreten.';
$labels['create_new_caldav_connection'] = 'Erstellen einer neuen CalDav Verbindung';
$labels['your_caldav_connections'] = 'Ihre CalDav Verbindungen';
$labels['created_at'] = 'Erstellt';
$labels['connected_at'] = 'Letzte Synchronisation';
$labels['never'] = 'Nie';
$labels['alternate_url'] = 'Alternative URL';
$labels['caldav_password_info'] = 'Aus Sicherheitsgründen wird das Passwort nicht auf dem Server gespeichert. Wenn Sie es vergessen sollten, löschen Sie diese Verbindung und erstellen Sie eine neue.';
$labels['caldav_url_info'] = 'Nutzen Sie diese URL um die Kalendererkennung zu unterstüzten, zum Beispiel mit iOS und OSX Kalender, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Nutzen Sie diese URL mit Anwendungen, die nicht mit der obigen Short-URL zusammenarbieten, zum Beispiel Thunderbird Lightning. (Diese URL will nicht mit iOS und OSX Kalender funktionieren.)';
$labels['invalid_caldav_url'] = 'Ungültige CalDAV-Server-URL.';
$labels['cannot_resolve_caldav_host'] = 'Der Host des CalDAV-Servers kann nicht aufgelöst werden.';
$labels['private_caldav_addresses_not_allowed'] = 'Private und reservierte CalDAV-Adressen sind nicht zulässig.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Gemeinsamen Kalender hinzufügen';
$labels['shared_calendars'] = 'Gemeinsame Kalender';
$labels['no_shared_calendars'] = 'Es gibt keine freigegebenen Kalender, die Sie hinzufügen können.';
$labels['add_to_my_calendars'] = 'Zu meinen Kalendern hinzufügen';
$labels['remove_from_my_calendars'] = 'Aus meinen Kalendern entfernen';
$labels['unshare'] = 'Teilen aufheben';
$labels['unshare_warning'] = 'Wenn Sie diesen Kalender freigeben, wird er aus dieser Liste gelöscht und Sie können ihn nicht erneut hinzufügen.';
$labels['calendar_name'] = 'Kalendername';
$labels['calendar_owner'] = 'Kalenderbesitzer';
$labels['shared_date'] = 'Geteilt';
$labels['unable_to_share'] = 'Der Kalender kann nicht mit den folgenden Nutzern geteilt werden, weil deren E-Mail-Accounts auf einen entfernten Server gespeichert sind:';

// shared calendar email
$labels['shared_email_subject'] = '$u hat einen Kalender mit Ihnen geteilt';
$labels['shared_email_body'] = 'Der Benutzer $u hat Ihnen Zugriff auf den Kalender "$c" gegeben. Um diesen Kalender zu Ihrer Liste der aktiven Kalender hinzuzufügen, klicken Sie auf den unten stehenden Link:';
$labels['shared_email_body_cpanel'] = 'Der Nutzer $u hat Ihnen Zugang zu dem Kalender "$c" gegeben. Um diesen Kalender zu Ihrer Liste von aktiven Kalendern hinzuzufügen, loggen Sie sich in Roundcube ein, gehen zu Kalender, klicken auf das Plus-Zeichen in der Seitenleiste und wählen Geteilten Kalender hinzufügen.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Feiertagskalender';
$labels['edit_holiday_calendar'] = 'Ferienkalender bearbeiten';
$labels['religious_holidays'] = 'Religiöse Feiertage';
$labels['country_holidays'] = 'Landesfeiertage';
$labels['add'] = 'Hinzufügen';
$labels['no_more_holidays_allowed'] = 'Sie können keine weiteren Ferienkalender hinzufügen.';
$labels['cannot_load_holidays'] = 'Die Ferien können nicht vom Google-Server geladen werden.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Geburtstagskalender hinzufügen';
$labels['edit_birthday_calendar'] = 'Geburtstagskalender bearbeiten';
$labels['contact_birthdays'] = 'Kontakt Geburtstage';
$labels['contact_birthdays_info'] = 'Dieser Kalender ruft Geburtstagsinformationen aus Ihrer Kontaktliste ab und zeigt sie als Kalenderereignisse an.';
$labels['birthday_event_title'] = 'Geburtstag: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Google Kalender hinzufügen';
$labels['edit_google_calendar'] = 'Google-Kalender bearbeiten';
$labels['google_id'] = 'Google Kalender ID';
$labels['new_google_calendar'] = 'Neuer Google Kalender';
$labels['specify_google_calendar_id'] = 'Bitte geben Sie die Google Kalender ID ein';
$labels['calendar_id_format_error'] = 'Die Google Kalender ID sollte das Zeichen @ beinhalten.';
$labels['cannot_load_google_calendar'] = 'Der Google-Kalender "$c" kann nicht geladen werden. Vergewissern Sie sich, dass die Kalender-ID korrekt ist und dass der Kalender für die Öffentlichkeit zugänglich gemacht wurde.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'CalDAV-Kalender hinzufügen';
$labels['edit_caldav_calendar'] = 'CalDAV-Kalender bearbeiten';
$labels['add_caldav_calendar_information'] = 'Um einen CalDAV-Kalender zu abonnieren, geben Sie die CalDAV-Serverinformationen an und klicken auf Kalender suchen.';
$labels['caldav_url'] = 'CalDAV-URL';
$labels['find_calendars'] = 'Kalendarien finden';
$labels['searching_for_calendars'] = 'Suche nach Kalendern, bitte warten...';
$labels['select_calendars'] = 'Wählen Sie die Kalender aus, die Sie hinzufügen möchten:';
$labels['read_only_status_yes'] = 'Ja';
$labels['read_only_status_no'] = 'Nein';
$labels['subscribed'] = 'Abonniert';

$labels['refresh_calendar'] = 'Kalender aktualisieren';
$labels['refresh_every_minute'] = 'Jede Minute';
$labels['refresh_every_5_minutes'] = 'Alle 5 Minuten';
$labels['refresh_every_15_minutes'] = 'Alle 15 Minuten';
$labels['refresh_every_30_minutes'] = 'Alle 30 Minuten';
$labels['refresh_every_60_minutes'] = 'Alle 60 Minuten';
$labels['refresh_never'] = 'Niemals';

$labels['cannot_connect_to_caldav_server'] = 'Verbindung zum CalDAV-Server "$n" nicht möglich.';
$labels['caldav_client_error_url'] = 'Es kann keine Verbindung zum CalDAV-Server hergestellt werden. Überprüfen Sie die Server-URL.';
$labels['caldav_client_error_credentials'] = 'Falscher CalDAV-Benutzername oder falsches Passwort.';
$labels['caldav_client_error_empty'] = 'Der Server hat keine Kalender zurückgegeben, die hinzugefügt werden können. Überprüfen Sie die CalDAV-URL.';
$labels['caldav_client_error_server_info'] = 'Falsche CalDAV-Serverinformationen.';
$labels['caldav_client_error_add_calendar'] = 'Kalender "$n" kann nicht gespeichert werden.';
$labels['caldav_client_error_delete_event'] = 'Ereignis kann nicht gelöscht werden';
$labels['caldav_client_error_save_event'] = 'CalDAV-Termin kann nicht gespeichert werden: Er wurde möglicherweise verschoben oder gelöscht, oder der Kalender ist nicht verfügbar.';
$labels['caldav_client_error_calendar_added'] = 'Der Kalender "$n" befindet sich bereits in Ihrer Kalenderliste.';

// previewing event
$labels['remove_recurring_event'] = 'Wiederholendes Ereignis löschen';
$labels['remove_one'] = 'Dieses Ereignis';
$labels['remove_all'] = 'Alle Ereignisse';
$labels['edit_event'] = 'Ereignis bearbeiten';
$labels['event_removed'] = 'Ereignis gelöscht.';
$labels['go_to_url'] = 'Gehe zu URL';
$labels['confirm_remove_event'] = 'Sind Sie sicher, dass Sie dieses Ereignis löschen möchten?';

// calendar/agenda grid
$labels['no_events'] = 'Es gibt keine Ereignisse zum Anzeigen.';

// importing events
$labels['select_calendar'] = 'Kalender wählen';
$labels['select_calendar_for_events'] = 'Wählen Sie den Kalender aus, zu dem Sie die Ereignisse hinzufügen möchten:';
$labels['add_events'] = 'Ereignisse hinzufügen';
$labels['add_events_to_calendar'] = 'Ereignisse zum Kalender hinzufügen';
$labels['events_imported_successfully'] = 'Ereignisse erfolgreich importiert';
$labels['events_imported_with_errors'] = 'Einige andere Ereignisse konnte nicht importiert werden.';
$labels['error_importing_events'] = 'Beim Importieren von Ereignissen im Kalender ist ein Fehler aufgetreten';

// editing events
$labels['calendar'] = 'Kalender';
$labels['new_event'] = 'Neues Event';
$labels['summary'] = 'Zusammenfassung';
$labels['recurrence'] = 'Wiederholung';
$labels['title'] = 'Titel';
$labels['location'] = 'Ort';
$labels['description'] = 'Beschreibung';
$labels['comment'] = 'Kommentar';
$labels['url'] = 'URL';
$labels['name'] = 'Name';
$labels['start'] = 'Start';
$labels['end'] = 'Ende';
$labels['show_timezones'] = 'Zeitzonen anzeigen';
$labels['reset_timezones'] = 'Zeitzonen auf Standard zurücksetzen';
$labels['all_day'] = 'Ganztägig';
$labels['use_calendar_colors'] = 'Verwende Kalenderfarben';
$labels['color'] = 'Farbe';
$labels['background'] = 'Hintergrund';
$labels['text'] = 'Text';
$labels['show_me_as'] = 'Zeigen als';
$labels['available'] = 'Verfügbar';
$labels['busy'] = 'Beschäftigt';
$labels['visibility'] = 'Sichtbarkeit';
$labels['calendar_default'] = 'Kalenderstandard';
$labels['public'] = 'Öffentlich';
$labels['private'] = 'Privat';
$labels['repeat'] = 'Wiederholen';
$labels['repeat_every'] = 'Wiederholen alle';
$labels['never'] = 'Nie';
$labels['daily'] = 'Täglich';
$labels['weekly'] = 'Wöchentlich';
$labels['monthly'] = 'Monatlich';
$labels['yearly'] = 'Jährlich';
$labels['days'] = 'Tage';
$labels['weeks'] = 'Wochen';
$labels['months'] = 'Monat(e)';
$labels['years'] = 'Jahr(e)';
$labels['repeat_on'] = 'Wiederholen am';
$labels['repeat_by'] = 'Wiederholen nach';
$labels['day_of_month'] = 'Tag des Monats';
$labels['day_of_week'] = 'Tag der Woche';
$labels['not_specified'] = 'Keine Angabe';
$labels['first'] = 'Ersten';
$labels['second'] = 'Zweite';
$labels['third'] = 'Dritte';
$labels['fourth'] = 'Vierte';
$labels['fifth'] = 'Fünfte';
$labels['second_to_last'] = 'Vorletzter Platz';
$labels['last'] = 'Letzter';
$labels['attachment_too_large'] = 'Die Dateianhang ist zu groß. Die maximal zulässige Größe ist $s.';
$labels['bg_color'] = 'Hintergrundfarbe';
$labels['tx_color'] = 'Textfarbe';
$labels['add_notification'] = 'Benachrichtigung hinzufügen';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'Dieses Ereignis hat keine Benachrichtigungen.';
$labels['minutes'] = 'Minuten';
$labels['hours'] = 'Stunden';
$labels['link'] = 'Link';
$labels['attach_file_from_web'] = 'Datei anhängen';
$labels['attachment_url'] = 'URL der Anlage';
$labels['invalid_url'] = 'Diese URL ist ungültig.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Duplizieren';
$labels['duplicate_suffix'] = '(Duplikat)';
$labels['no_notifications'] = 'Dieses Ereignis hat keine Benachrichtigungen.';
$labels['this_calendar_is_hidden'] = 'Dieser Kalender ist ausgeblendet. Gespeicherte Termine werden erst angezeigt, wenn der Kalender sichtbar ist.';
$labels['make_calendar_visible'] = 'Machen Sie diesen Kalender sichtbar.';

// recurrence
$labels['recurrence_range'] = 'Wiederholungshäufigkeit';
$labels['repeat_forever'] = 'Ewig wiederholen';
$labels['repeat_every_weekday'] = 'Wiederholung an jedem Wochentag';
$labels['last_day'] = 'Letzter Tag';
$labels['every'] = 'Alle';
$labels['weekday'] = 'Wochentag';
$labels['weekend_day'] = 'Tag des Wochenendes';
$labels['day_of_week'] = 'Tag der Woche';
$labels['day_of_month'] = 'Tag des Monats';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Ende nach';
$labels['occurrences'] = 'Wiederholungen';

// end recurrence on date:
$labels['end_by_date'] = 'Ende bis Datum';
$labels['removed_events_title'] = 'Ereignisse aus der wiederkehrenden Serie entfernt.';
$labels['restore'] = 'Wiederherstellen';

// settings page
$labels['setting_view'] = 'Kalenderansicht';
$labels['setting_first_day'] = 'Woche startet am';
$labels['setting_agenda_week_span'] = 'Agenda-Zeitspanne (Wochen)';
$labels['setting_slot_duration'] = 'Zeitdauer (Min)';
$labels['setting_scroll_time'] = 'Springe zur Zeit';
$labels['setting_calendar'] = 'Standard Kalender für Ereignisse';
$labels['setting_refresh'] = 'Kalendarien aktualisieren';
$labels['setting_week_numbers'] = 'Wochennummern im kleinen Kalender';
$labels['setting_event_border'] = 'Kategoriefarben als Ereignisgrenzen';
$labels['show_sunrise'] = 'Sonnenaufgang in Monatsansicht anzeigen';
$labels['show_sunset'] = 'Sonnenuntergang in der Monatsansicht anzeigen';
$labels['categories'] = 'Kategorien';
$labels['category'] = 'Kategorie';
$labels['no_category'] = 'Keine Kategorie';
$labels['add_category'] = 'Kategorie hinzufügen';
$labels['setting_alarm_sound'] = 'Benachrichtigungston';
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
$labels['play_sound'] = 'Sound abspielen';

// alarms
$labels['upcoming_events'] = 'Kommende Ereignisse';
$labels['dismiss'] = 'Verwerfen';
$labels['dismiss_all'] = 'Alle Verwerfen';
$labels['snooze'] = 'Erneut wiederholen';
$labels['5_minutes'] = '5 Minuten';
$labels['10_minutes'] = '10 Minuten';
$labels['20_minutes'] = '20 Minuten';
$labels['30_minutes'] = '30 Minuten';
$labels['1_hour'] = '1 Stunde';
$labels['2_hours'] = '2 Stunden';
$labels['tomorrow'] = 'Bis Morgen';
$labels['next_week'] = 'Bis nächste Woche';

// sidebar
$labels['setting_show_xcalendar'] = 'Zeige Kalender Agenda';
$labels['todays_agenda'] = 'Heutige Agenda';
$labels['no_events_today'] = 'Keine Ereignisse für heute geplant.';
$labels['sidebar_more'] = 'Mehr...';

// event invitation
$labels['itip_title_request'] = 'Sie sind zu einem Ereignis eingeladen.';
$labels['itip_title_modified'] = 'Dieses Ereignis wurde geändert.';
$labels['itip_title_reply_accepted'] = '$u hat Ihre Einladung angenommen';
$labels['itip_title_reply_tentative'] = '$u hat Ihre Einladung vorläufig angenommen';
$labels['itip_title_reply_declined'] = '$u hat Ihre Einladung abgelehnt';
$labels['itip_title_cancel'] = 'Dieses Ereignis ist abgesagt worden.';

$labels['itip_email_subject_request'] = 'Einladung:';
$labels['itip_email_subject_request_modified'] = 'Ereignis geändert:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Abgesagt:';

$labels['itip_popup_event_added'] = 'Ereignis zum Kalender hinzugefügt.';
$labels['itip_popup_event_updated'] = 'Ereignis im Kalender aktualisiert.';
$labels['itip_popup_event_deleted'] = 'Ereignis aus dem Kalender gelöscht.';
$labels['itip_popup_response_sent'] = 'Antwort an den Organisator gesendet.';

$labels['itip_message_invitation_accepted'] = 'Sie haben diese Einladung angenommen';
$labels['itip_message_invitation_tentative'] = 'Sie haben diese Einladung vorläufig angenommen.';
$labels['itip_message_invitation_declined'] = 'Sie haben diese Einladung abgelehnt.';
$labels['itip_message_event_deleted'] = 'Das Ereignis wurde aus Ihrem Kalender gelöscht.';
$labels['itip_message_event_updated'] = 'Das Ereignis in Ihrem Kalender wurde aktualisiert.';
$labels['itip_message_event_missing'] = 'Dieses Ereignis ist in keinem Ihrer Kalender vorhanden.';

$labels['itip_accept'] = 'Akzeptieren';
$labels['itip_tentative'] = 'Vorläufig akzeptieren';
$labels['itip_decline'] = 'Absagen';
$labels['itip_change_reponse'] = 'Antwort ändern.';
$labels['itip_add_to_calendar'] = 'Zum Kalender hinzufügen';
$labels['itip_reply_to_sender'] = 'Antwort an Absender';
$labels['itip_dont_import'] = 'Nicht zum Kalender hinzufügen';
$labels['itip_update_in_calendar'] = 'Ereignis im Kalender aktualisieren';
$labels['itip_delete_from_calendar'] = 'Termin aus dem Kalender löschen';

$labels['mail_subject_invitation'] = 'Einladung:';
$labels['mail_subject_update'] = 'Aktualisierte Einladung:';
$labels['mail_subject_cancel'] = 'Ereignis abgesagt:';

// event email notifications
$labels['mail_title'] = 'Titel:';
$labels['mail_start'] = 'Start:';
$labels['mail_end'] = 'Ende:';
$labels['mail_description'] = 'Beschreibung:';
$labels['mail_location'] = 'Ort:';

// event attendees
$labels['attendees'] = 'Teilnehmer';
$labels['attendee_role'] = 'Rolle';
$labels['attendee_role_required'] = 'Erforderlich';
$labels['attendee_role_optional'] = 'Optional';
$labels['attendee_role_chair'] = 'Moderator';
$labels['attendee_role_none'] = 'Nicht-Teilnehmer';
$labels['organizer'] = 'Organisator';
$labels['attendance'] = 'Teilnahme';
$labels['your_status'] = 'Ihr Status';
$labels['attendance_going'] = 'Nehmen Sie teil?';
$labels['attendee_status_yes'] = 'Ja';
$labels['attendee_status_no'] = 'Nein';
$labels['attendee_status_maybe'] = 'Vielleicht';
$labels['attendee_status_delegated'] = 'Vertretung';
$labels['attendee_status_waiting'] = 'Warten';
$labels['attendee_status_waiting_for_confirmation'] = 'Warten auf Bestätigung';
$labels['attendee_status_info_yes'] = 'Wird teilnehmen';
$labels['attendee_status_info_no'] = 'Wird nicht teilnehmen';
$labels['attendee_status_info_maybe'] = 'Wird vielleicht teilnehmen';
$labels['attendee_status_info_delegated'] = 'Teilnahme durch Vertreter';
$labels['attendee_role'] = 'Rolle';
$labels['attendee_role_info'] = 'Legen Sie fest, ob der Teilnehmer teilnehmen oder die Besprechnung moderierern soll.';
$labels['attendee_notify'] = 'Benachrichtigen';
$labels['attendee_notify_info'] = 'Wenn ausgewählt, wird eine Einladungsmail an den Teilnehmer gesendet.';
$labels['add_attendee'] = 'Teilnehmer hinzufügen';
$labels['attendee_notify_question'] = 'Benachrichtigungsmail an die Teilnehmer senden?';
$labels['attendee_notify_textarea'] = 'Optionale Nachricht hinzufügen';
$labels['attendee_notify_send'] = 'Senden';
$labels['attendee_notify_dont'] = 'Nicht senden';
$labels['attendee_notify_cancel'] = 'Abbrechen';
$labels['new_attendee_not_saved_warning'] = 'Sie haben die E-Mail-Adresse eines Teilnehmers eingegeben, diesen aber nicht zur Teilnehmerliste hinzugefügt. Möchten Sie ohne diesen Teilnehmer speichern?';

// import / export
$labels['import_export'] = 'Import / Export';
$labels['export_to_file'] = 'Export als Datei';
$labels['export_info'] = 'Durch Betätigen des Buttons werden die Ereignisse aus dem Kalender in das iCalender (ics) Format exportiert und herunter geladen. Durch die große Anzahl an Ereignisse kann der Export einige Zeit dauern.';
$labels['export_no_events'] = 'Dieser Kalender enthält keine Ereignisse zum Exportieren.';
$labels['import_from_file'] = 'Aus Datei importieren';
$labels['import_info'] = 'Durch Betätigen des Button werden die Ereignisse aus einer iCalender (ics) Datei in den Kalender importiert.';

// email notifications
$labels['appointment_reminder'] = 'Terminerinnerung';
$labels['email_notification_subject'] = 'Erinnerung: $t um $d ($z)';
$labels['email_notification_cron_error'] = 'E-Mail-Benachrichtigungen sind deaktiviert. Kontaktieren Sie den Administrator.';
$labels['alarm_before_event_starts'] = 'Vor Beginn der Veranstaltung';
$labels['alarm_after_event_starts'] = 'Nach Beginn der Veranstaltung';
$labels['alarm_before_event_ends'] = 'Vor Ende der Veranstaltung';
$labels['alarm_after_event_ends'] = 'Nach Veranstaltungsende';
$labels['alarm_at_specific_time'] = 'Zu einem bestimmten Zeitpunkt';

// search
$labels['specify_at_least_one_calendar'] = 'Geben Sie mindestens einen zu durchsuchenden Kalender an.';
$labels['specify_text_to_search'] = 'Geben Sie den Text ein, nach dem Sie suchen möchten.';
$labels['clear_search_results'] = 'Klar';
$labels['search_outdated_message'] = 'Die Suchergebnisse sind möglicherweise veraltet. Erwägen Sie, Ihre Suche zu wiederholen.';
$labels['date_range'] = 'Datumsbereich';
$labels['date_range_subtitle'] = 'Datumsbereich:';
$labels['search_results'] = 'Gefundene Ergebnisse: $n';
$labels['modify_search_options'] = 'Versuchen Sie, die Suchoptionen zu ändern.';
$labels['show_shortcuts'] = 'Abkürzungen anzeigen';
$labels['hide_shortcuts'] = 'Shortcuts ausblenden';
$labels['this_month'] = 'Diesen Monat';
$labels['this_year'] = 'Dieses Jahr';
$labels['past_30_days'] = 'Letzte 30 Tage';
$labels['next_30_days'] = 'Nächste 30 Tage';
$labels['past_6_months'] = 'Letzte 6 Monate';
$labels['next_6_months'] = 'Nächste 6 Monate';

// calendar grid
$labels['cannot_load_events'] = 'Ereignisse für den Kalender "$c" können nicht geladen werden.';
