<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Naptár';
$labels['app_menu_title'] = 'Naptár';

// menu
$labels['create_event'] = 'Új esemény';
$labels['create_event_button'] = 'Új esemény';
$labels['day'] = 'Nap';
$labels['week'] = 'Hét';
$labels['month'] = 'Hónap';
$labels['agenda'] = 'Lista';

// editing calendars
$labels['calendars'] = 'Naptárak';
$labels['my_calendar'] = 'Saját naptár';
$labels['create_calendar'] = 'Naptár létrehozása';
$labels['new_calendar'] = 'Új naptár';
$labels['edit_calendar'] = 'Naptár szerkesztése';
$labels['remove_calendar'] = 'Naptár törlése';
$labels['confirm_remove_calendar'] = 'Biztos, hogy törölni szeretné ezt a naptárat?';
$labels['calendar_removed'] = 'Naptár törölve.';
$labels['notifications'] = 'Értesítések';
$labels['default_event_visibility'] = 'Alapértelmezett láthatóság';
$labels['time_zone'] = 'Naptáridőzóna';
$labels['share_calendar'] = 'Naptár megosztása';
$labels['permissions'] = 'Engedélyek';
$labels['permission_publish_calendar'] = 'Naptár közzététele';
$labels['permission_share_calendar'] = 'Naptár megosztása';
$labels['permission_edit_events'] = 'Események szerkesztése';
$labels['permission_see_details'] = 'Részletek megtekintése';
$labels['permission_publish_calendar_info'] = 'Ha be van jelölve, a felhasználó közzéteheti a naptárt.';
$labels['permission_share_calendar_info'] = 'Ha be van jelölve, a felhasználó megoszthatja a naptárt.';
$labels['permission_edit_events_info'] = 'Ha be van jelölve, a felhasználó szerkesztheti és törölheti az eseményeket.';
$labels['permission_see_details_info'] = 'Ha be van jelölve, minden eseményleírás meg lesz osztva. Ha nincs bejelölve, csak a szabad/elfoglalt információ lesz megosztva.';
$labels['share_send_notification'] = 'Értesítés';
$labels['share_send_notification_info'] = 'Ha be van jelölve, a felhasználó e-mailt kap a megosztott eseményről.';
$labels['new_share_not_saved_warning'] = 'Ön megadta az e-mail címet, amellyel meg szeretné osztani a naptárt, de nem kattintott a Megosztás gombra. Szeretné megosztás nélkül menteni a naptárt?';
$labels['share'] = 'Megosztás';
$labels['shared_marker'] = '(Megosztva)';
$labels['owner'] = 'Tulajdonos';
$labels['publish_calendar'] = 'Naptár közzététele';
$labels['create_link'] = 'Link létrehozása';
$labels['reset'] = 'Visszaállítás';
$labels['remove'] = 'Eltávolítás';
$labels['publish_full'] = 'Az esemény minden részletének közzététele.';
$labels['publish_busy'] = 'Csak a szabad/elfoglalt információ közzététele.';
$labels['sync_no_id'] = 'A CalDAV lehetőségek a naptár elmentése után lesznek elérhetők.';
$labels['share_no_id'] = 'A naptár megosztási lehetőségei a naptár elmentése után lesznek elérhetők.';
$labels['publish_no_id'] = 'A naptár közzétételi lehetőségei a naptár elmentése után lesznek elérhetők.';
$labels['import_no_id'] = 'Az importálási és exportálási lehetőségek a naptár mentése után válnak elérhetővé.';
$labels['unable_to_save_calendar_data'] = 'Nem sikerült a naptáradatok elmentése.';
$labels['unable_to_retrieve_calendar_data'] = 'Nem találhatók a naptáradatok. A naptár valószínűleg már nem elérhető.';
$labels['unable_add_shared_calendar'] = 'Nem lehet hozzáadni a megosztott naptárt. A naptár valószínűleg már nem elérhető.';
$labels['itip_organizer_message'] = 'Önt meghívták erre az eseményre, de nem Ön a szervező. Az Ön által az eseményen végrehajtott módosítások nem kerülnek elküldésre a többi résztvevőnek.';
$labels['cannot_share_with_yourself'] = 'A saját naptárát nem oszthatja meg önmagával.';
$labels['cannot_share_with_calendar_owner'] = 'Ezt a naptárat nem oszthatja meg a naptár tulajdonosával ($e)';
$labels['setting_default_notification_type'] = 'Alapértelmezett értesítések új eseményekről';
$labels['calendar_name_cannot_be_empty'] = 'A naptár neve nem lehet üres.';

// caldav server
$labels['sync_calendar'] = 'Naptár szinkronizálása';
$labels['password_too_short'] = 'A jelszónak legalább 10 karakterből kell állnia.';
$labels['read_only'] = 'Csak olvasható';
$labels['add_connection'] = 'Kapcsolat hozzáadása';
$labels['show_details'] = 'Részletek megjelenítése';
$labels['hide_details'] = 'Részletek elrejtése';
$labels['caldav_connection_password'] = 'A kapcsolat létrehozásakor megadott jelszó.';
$labels['yes'] = 'Igen';
$labels['no'] = 'Nem';
$labels['confirm_remove_sync'] = 'Biztos benne, hogy véglegesen el akarja távolítani ezt a kapcsolatot?';
$labels['remove_sync_error'] = 'Hiba történt a kapcsolat eltávolítása közben.';
$labels['create_new_caldav_connection'] = 'Új CalDav kapcsolat létrehozása';
$labels['your_caldav_connections'] = 'CalDav kapcsolatok';
$labels['created_at'] = 'Létrehozva';
$labels['connected_at'] = 'Utolsó szinkronizálás';
$labels['never'] = 'Soha';
$labels['alternate_url'] = 'Alternatív URL';
$labels['caldav_password_info'] = 'Biztonsági okokból a jelszó nincs elmentve a szerveren. Ha elfelejtette a jelszavát, törölje ezt a kapcsolatot, és hozzon létre újat.';
$labels['caldav_url_info'] = 'Használja ezt az URL-t olyan alkalmazásokkal, amelyek támogatják a naptárfelfedezést (pl. iOS és Mac OS naptár, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Használja ezt az URL-t olyan alkalmazásokkal, amelyek nem működnek a fenti rövid URL-lel, például Thunderbird Lightning. (Ez az URL nem használható iOS-szel és OSX Naptárral.)';
$labels['invalid_caldav_url'] = 'Érvénytelen CalDAV-kiszolgáló URL-cím.';
$labels['cannot_resolve_caldav_host'] = 'A CalDAV-kiszolgáló gazdagépét nem sikerült feloldani.';
$labels['private_caldav_addresses_not_allowed'] = 'A magán- és fenntartott CalDAV-címek használata nem megengedett.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Megosztott naptár hozzáadása';
$labels['shared_calendars'] = 'Megosztott naptárak';
$labels['no_shared_calendars'] = 'Nincsenek hozzáadható megosztott naptárak.';
$labels['add_to_my_calendars'] = 'Hozzáadás a naptáraimhoz';
$labels['remove_from_my_calendars'] = 'Törlés a naptáraimból';
$labels['unshare'] = 'Megosztás eltávolítása';
$labels['unshare_warning'] = 'Ha kikapcsolja a naptár megosztását, el fog tűnni ebből a listából, és nem lehet újra hozzáadni.';
$labels['calendar_name'] = 'Naptár neve';
$labels['calendar_owner'] = 'Naptár tulajdonosa';
$labels['shared_date'] = 'Megosztva';
$labels['unable_to_share'] = 'A naptárt nem lehet megosztani a következő felhasználókkal, mert az e-mail fiókjuk távoli szerveren van.';

// shared calendar email
$labels['shared_email_subject'] = '$u megosztott Önnel egy naptárt';
$labels['shared_email_body'] = '$u hozzáférést adott Önnek a "$c" című naptárhoz. Kattintson az alábbi linkre, ha szeretné hozzáadni ezt a naptárt az aktív naptáraihoz.';
$labels['shared_email_body_cpanel'] = '$u hozzáférést adott Önnek a "$c" című naptárhoz. Ha szeretné hozzáadni ezt a naptárt az aktív naptáraihoz, jelentkezzen be a Roundcube-ba, kattintson a Naptárra, majd az oldalsávban a pluszjelre, és válassza ki a "Megosztott naptár hozzáadása" lehetőséget.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Ünnepi naptárak';
$labels['edit_holiday_calendar'] = 'Ünnepi naptár szerkesztése';
$labels['religious_holidays'] = 'Vallási ünnepek';
$labels['country_holidays'] = 'Nemzeti ünnepek';
$labels['add'] = 'Hozzáadás';
$labels['no_more_holidays_allowed'] = 'Nem lehet több ünnepnapot hozzáadni.';
$labels['cannot_load_holidays'] = 'Nem lehet betölteni az ünnepeket a Google szerveréről.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Születésnapi naptár hozzáadása';
$labels['edit_birthday_calendar'] = 'Születésnapi naptár szerkesztése';
$labels['contact_birthdays'] = 'Kapcsolat Születésnapok';
$labels['contact_birthdays_info'] = 'Ez a naptár lekérdezi a születésnapi információkat a névjegyzékből, és naptári eseményként jeleníti meg azokat.';
$labels['birthday_event_title'] = 'Születésnap: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Google naptár hozzáadása';
$labels['edit_google_calendar'] = 'Google naptár szerkesztése';
$labels['google_id'] = 'Google naptár ID';
$labels['new_google_calendar'] = 'Új Google naptár';
$labels['specify_google_calendar_id'] = 'Adja meg a Google naptár ID-ját';
$labels['calendar_id_format_error'] = 'A Google naptár ID-jának tartalmaznia kell a @ karaktert.';
$labels['cannot_load_google_calendar'] = 'A Google naptár "$c" nem tölthető be. Győződjön meg róla, hogy a naptár azonosítója helyes, és hogy a naptár nyilvános.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'CalDAV naptár hozzáadása';
$labels['edit_caldav_calendar'] = 'CalDAV naptár szerkesztése';
$labels['add_caldav_calendar_information'] = 'CalDAV-naptárra való feliratkozáshoz adja meg a CalDAV-kiszolgáló adatait, majd kattintson a Naptárak keresése gombra.';
$labels['caldav_url'] = 'CalDAV URL-cím';
$labels['find_calendars'] = 'Naptárak keresése';
$labels['searching_for_calendars'] = 'Naptárak keresése, kérem várjon...';
$labels['select_calendars'] = 'Válassza ki a hozzáadandó naptárakat:';
$labels['read_only_status_yes'] = 'Igen';
$labels['read_only_status_no'] = 'Nem';
$labels['subscribed'] = 'Előfizetett';

$labels['refresh_calendar'] = 'Naptár frissítése';
$labels['refresh_every_minute'] = 'Minden percben';
$labels['refresh_every_5_minutes'] = '5 percenként';
$labels['refresh_every_15_minutes'] = '15 percenként';
$labels['refresh_every_30_minutes'] = '30 percenként';
$labels['refresh_every_60_minutes'] = '60 percenként';
$labels['refresh_never'] = 'Soha';

$labels['cannot_connect_to_caldav_server'] = 'Nem tud csatlakozni a "$n" CalDAV-kiszolgálóhoz.';
$labels['caldav_client_error_url'] = 'Nem lehet csatlakozni a CalDAV-kiszolgálóhoz. Ellenőrizze a kiszolgáló URL-címét.';
$labels['caldav_client_error_credentials'] = 'Helytelen CalDAV felhasználónév vagy jelszó.';
$labels['caldav_client_error_empty'] = 'A kiszolgáló nem adott vissza hozzáadható naptárakat. Ellenőrizze a CalDAV URL-címet.';
$labels['caldav_client_error_server_info'] = 'Helytelen CalDAV-kiszolgálóadatok.';
$labels['caldav_client_error_add_calendar'] = 'Nem lehet elmenteni a "$n" naptárat.';
$labels['caldav_client_error_delete_event'] = 'Nem lehet törölni az eseményt';
$labels['caldav_client_error_save_event'] = 'A CalDAV-esemény nem menthető: lehet, hogy áthelyezték, törölték, vagy a naptár nem érhető el.';
$labels['caldav_client_error_calendar_added'] = 'A "$n" naptár már szerepel a naptárlistában.';

// previewing event
$labels['remove_recurring_event'] = 'Ismétlődő esemény törlése';
$labels['remove_one'] = 'Egy törlése';
$labels['remove_all'] = 'Az összes törlése';
$labels['edit_event'] = 'Esemény szerkesztése';
$labels['event_removed'] = 'Az esemény törölve.';
$labels['go_to_url'] = 'URL felkeresése';
$labels['confirm_remove_event'] = 'Biztos, hogy törölni szeretné ezt az eseményt?';

// calendar/agenda grid
$labels['no_events'] = 'Nincsenek megjeleníthető események.';

// importing events
$labels['select_calendar'] = 'Naptár kiválasztása';
$labels['select_calendar_for_events'] = 'Válassza ki a naptárt, amelyhez hozzá szeretné adni az eseményeket:';
$labels['add_events'] = 'Események hozzáadása';
$labels['add_events_to_calendar'] = 'Események hozzáadása naptárhoz';
$labels['events_imported_successfully'] = 'Az események importálása kész.';
$labels['events_imported_with_errors'] = 'Néhány egyéb eseményt nem sikerült importálni.';
$labels['error_importing_events'] = 'Hiba történt az események naptárba importálása közben';

// editing events
$labels['calendar'] = 'Naptár';
$labels['new_event'] = 'Új esemény';
$labels['summary'] = 'Összefoglalás';
$labels['recurrence'] = 'Ismétlődés';
$labels['title'] = 'Cím';
$labels['location'] = 'Helyszín';
$labels['description'] = 'Leírás';
$labels['comment'] = 'Megjegyzés (nem kötelező)';
$labels['url'] = 'URL';
$labels['name'] = 'Név';
$labels['start'] = 'Kezdés';
$labels['end'] = 'Vége';
$labels['show_timezones'] = 'Időzónák megjelenítése';
$labels['reset_timezones'] = 'Időzónák alapértelmezettre visszaállítása';
$labels['all_day'] = 'Egész nap';
$labels['use_calendar_colors'] = 'Naptár színei';
$labels['color'] = 'Szín';
$labels['background'] = 'Háttér';
$labels['text'] = 'Szöveg';
$labels['show_me_as'] = 'Megjelenítés mint';
$labels['available'] = 'Szabad';
$labels['busy'] = 'Elfoglalt';
$labels['visibility'] = 'Láthatóság';
$labels['calendar_default'] = 'Alapértelmezett naptár';
$labels['public'] = 'Nyilvános';
$labels['private'] = 'Privát';
$labels['repeat'] = 'Ismétlődés';
$labels['repeat_every'] = 'Ismétlődés minden';
$labels['never'] = 'Soha';
$labels['daily'] = 'Naponta';
$labels['weekly'] = 'Hetente';
$labels['monthly'] = 'Havonta';
$labels['yearly'] = 'Évente';
$labels['days'] = 'naponta';
$labels['weeks'] = 'hetente';
$labels['months'] = 'havonta';
$labels['years'] = 'évente';
$labels['repeat_on'] = 'Ismétlés ezen a napon';
$labels['repeat_by'] = 'Ismétlés rendszeressége';
$labels['day_of_month'] = 'day of the month';
$labels['day_of_week'] = 'day of the week';
$labels['not_specified'] = 'Nincs megadva';
$labels['first'] = 'Első';
$labels['second'] = 'Második';
$labels['third'] = 'Harmadik';
$labels['fourth'] = 'Negyedik';
$labels['fifth'] = 'Ötödik';
$labels['second_to_last'] = 'Utolsó előtti';
$labels['last'] = 'Utolsó';
$labels['attachment_too_large'] = 'A csatolmány túl nagy. A legnagyobb megengedett méret $s.';
$labels['bg_color'] = 'Háttérszín';
$labels['tx_color'] = 'Szöveg színe';
$labels['add_notification'] = 'Értesítés beállítása';
$labels['popup'] = 'Felugró ablak';
$labels['no_notifications'] = 'Ehhez az eseményhez nincs beállítva értesítés.';
$labels['minutes'] = 'perc';
$labels['hours'] = 'óra';
$labels['link'] = 'Link';
$labels['attach_file_from_web'] = 'Fájl csatolása';
$labels['attachment_url'] = 'Csatolási URL';
$labels['invalid_url'] = 'Ez az URL érvénytelen.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Duplikálj';
$labels['duplicate_suffix'] = '(másolat)';
$labels['no_notifications'] = 'Ehhez az eseményhez nincs beállítva értesítés.';
$labels['this_calendar_is_hidden'] = 'Ez a naptár el van rejtve. A mentett események addig nem jelennek meg, amíg a naptár láthatóvá nem válik.';
$labels['make_calendar_visible'] = 'Tedd láthatóvá ezt a naptárat.';

// recurrence
$labels['recurrence_range'] = 'Ismétlődési tartomány';
$labels['repeat_forever'] = 'Ismételd örökké';
$labels['repeat_every_weekday'] = 'Ismétlés minden hétköznap';
$labels['last_day'] = 'Utolsó nap';
$labels['every'] = 'Minden';
$labels['weekday'] = 'Hétköznap';
$labels['weekend_day'] = 'Hétvégi nap';
$labels['day_of_week'] = 'day of the week';
$labels['day_of_month'] = 'day of the month';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Vége után';
$labels['occurrences'] = 'előfordulás történt';

// end recurrence on date:
$labels['end_by_date'] = 'Dátum szerinti vége';
$labels['removed_events_title'] = 'Az ismétlődő sorozatokból eltávolított események';
$labels['restore'] = 'Visszaállítása';

// settings page
$labels['setting_view'] = 'Alapértelmezett nézet';
$labels['setting_first_day'] = 'A hét első napja';
$labels['setting_agenda_week_span'] = 'Napirend időtartama (hetekben)';
$labels['setting_slot_duration'] = 'Időtartam (perc)';
$labels['setting_scroll_time'] = 'Görgetés az időre';
$labels['setting_calendar'] = 'Alapértelmezett naptár eseményekhez';
$labels['setting_refresh'] = 'Naptárak frissítése';
$labels['setting_week_numbers'] = 'Hetek száma kis naptárnézetben';
$labels['setting_event_border'] = 'Kategóriaszínek beállítása események körvonalszíneként';
$labels['show_sunrise'] = 'Napkelte megjelenítése havi nézetben';
$labels['show_sunset'] = 'napnyugta megjelenítése havi nézetben';
$labels['categories'] = 'Kategóriák';
$labels['category'] = 'Kategória';
$labels['no_category'] = 'Nincs kategória';
$labels['add_category'] = 'Új kategória';
$labels['setting_alarm_sound'] = 'Értesítés hangjelzése';
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
$labels['play_sound'] = 'Lejátszás';

// alarms
$labels['upcoming_events'] = 'Közelgő események';
$labels['dismiss'] = 'Elhalasztás';
$labels['dismiss_all'] = 'Mindet elhalaszt';
$labels['snooze'] = 'Szundi';
$labels['5_minutes'] = '5 perc';
$labels['10_minutes'] = '10 perc';
$labels['20_minutes'] = '20 perc';
$labels['30_minutes'] = '30 perc';
$labels['1_hour'] = '1 óra';
$labels['2_hours'] = '2 óra';
$labels['tomorrow'] = 'Holnapig';
$labels['next_week'] = 'Jövő hétig';

// sidebar
$labels['setting_show_xcalendar'] = 'Lista megjelenítése';
$labels['todays_agenda'] = 'Mai lista';
$labels['no_events_today'] = 'Mára nincs ütemezett esemény.';
$labels['sidebar_more'] = 'Tovább...';

// event invitation
$labels['itip_title_request'] = 'Önt meghívtuk egy eseményre.';
$labels['itip_title_modified'] = 'Ezt az eseményt módosították.';
$labels['itip_title_reply_accepted'] = '$u elfogadta meghívását';
$labels['itip_title_reply_tentative'] = '$u ideiglenesen elfogadta meghívását';
$labels['itip_title_reply_declined'] = '$u visszautasította a meghívást';
$labels['itip_title_cancel'] = 'Ez az esemény elmarad.';

$labels['itip_email_subject_request'] = 'Meghívó:';
$labels['itip_email_subject_request_modified'] = 'Esemény módosítva:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Törölték:';

$labels['itip_popup_event_added'] = 'Esemény hozzáadva a naptárhoz.';
$labels['itip_popup_event_updated'] = 'Esemény frissítve a naptárban.';
$labels['itip_popup_event_deleted'] = 'Esemény törölve a naptárból.';
$labels['itip_popup_response_sent'] = 'A szervezőnek küldött válasz.';

$labels['itip_message_invitation_accepted'] = 'Ön elfogadta ezt a meghívást.';
$labels['itip_message_invitation_tentative'] = 'Előzetesen elfogadta ezt a meghívást.';
$labels['itip_message_invitation_declined'] = 'Ön visszautasította ezt a meghívást.';
$labels['itip_message_event_deleted'] = 'Az eseményt törölték a naptárából.';
$labels['itip_message_event_updated'] = 'A naptárában lévő eseményt frissítettük.';
$labels['itip_message_event_missing'] = 'Ez az esemény nem szerepel egyik naptárában sem.';

$labels['itip_accept'] = 'Elfogadom';
$labels['itip_tentative'] = 'Tentatív';
$labels['itip_decline'] = 'Hanyatlás';
$labels['itip_change_reponse'] = 'Változtatási válasz.';
$labels['itip_add_to_calendar'] = 'Hozzáadás a naptárhoz';
$labels['itip_reply_to_sender'] = 'Válasz a feladónak';
$labels['itip_dont_import'] = 'Ne adja hozzá a naptárhoz';
$labels['itip_update_in_calendar'] = 'Esemény frissítése a naptárban';
$labels['itip_delete_from_calendar'] = 'Esemény törlése a naptárból';

$labels['mail_subject_invitation'] = 'Meghívó:';
$labels['mail_subject_update'] = 'Frissített meghívó:';
$labels['mail_subject_cancel'] = 'Az esemény elmarad:';

// event email notifications
$labels['mail_title'] = 'Cím:';
$labels['mail_start'] = 'Kezdés:';
$labels['mail_end'] = 'Vége:';
$labels['mail_description'] = 'Leírás:';
$labels['mail_location'] = 'Helyszín:';

// event attendees
$labels['attendees'] = 'Résztvevők';
$labels['attendee_role'] = 'Szerepkör';
$labels['attendee_role_required'] = 'Kötelező';
$labels['attendee_role_optional'] = 'Nem kötelező';
$labels['attendee_role_chair'] = 'Elnök';
$labels['attendee_role_none'] = 'Non-participant';
$labels['organizer'] = 'Szervező';
$labels['attendance'] = 'Attendance';
$labels['your_status'] = 'Az Ön státusza';
$labels['attendance_going'] = 'Mész?';
$labels['attendee_status_yes'] = 'Igen';
$labels['attendee_status_no'] = 'Nem';
$labels['attendee_status_maybe'] = 'Talán';
$labels['attendee_status_delegated'] = 'Delegált';
$labels['attendee_status_waiting'] = 'Várakozik';
$labels['attendee_status_waiting_for_confirmation'] = 'Megerősítésre vár';
$labels['attendee_status_info_yes'] = 'Részt vesz az eseményen';
$labels['attendee_status_info_no'] = 'Nem vesz részt az eseményen';
$labels['attendee_status_info_maybe'] = 'Talán részt vesz az eseményen';
$labels['attendee_status_info_delegated'] = 'Delegált megjelenés';
$labels['attendee_role'] = 'Szerepkör';
$labels['attendee_role_info'] = 'A résztvevőnek kötelező megjelennie, vagy elnökölnie kell a megbeszélést?';
$labels['attendee_notify'] = 'Értesítés';
$labels['attendee_notify_info'] = 'Ha be van jelölve, a résztvevő meghívó e-mailt kap.';
$labels['add_attendee'] = 'Résztvevő hozzáadása';
$labels['attendee_notify_question'] = 'Küld e-mail értesítést a résztvevőknek?';
$labels['attendee_notify_textarea'] = 'Üzenet hozzáadása (nem kötelező)';
$labels['attendee_notify_send'] = 'Küldés';
$labels['attendee_notify_dont'] = 'Ne küldje el';
$labels['attendee_notify_cancel'] = 'Mégsem';
$labels['new_attendee_not_saved_warning'] = 'Megadott egy résztvevő e-mail-címét, de nem vette fel a résztvevők listájára. Folytatja a mentést ennek a résztvevőnek a kihagyásával?';

// import / export
$labels['import_export'] = 'Import / Export';
$labels['export_to_file'] = 'Exportálás fájlba';
$labels['export_info'] = 'Erre a gombra kattintva a naptár eseményeit iCalendar (ics) formátumban exportálhatja és töltheti le. Ha a naptár sok eseményt tartalmaz, az exportálás néhány pillanatig eltarthat.';
$labels['export_no_events'] = 'Ebben a naptárban nincsenek exportálandó események.';
$labels['import_from_file'] = 'Importálás fájlból';
$labels['import_info'] = 'Erre a gombra kattintva importálhat eseményeket ebbe a naptárba egy iCalendar (ics) fájlból.';

// email notifications
$labels['appointment_reminder'] = 'Időpont emlékeztető';
$labels['email_notification_subject'] = 'Emlékeztető: $t este $d ($z)';
$labels['email_notification_cron_error'] = 'Email notifications are disabled. Contact the administrator.';
$labels['alarm_before_event_starts'] = 'A rendezvény kezdete előtt';
$labels['alarm_after_event_starts'] = 'A rendezvény kezdete után';
$labels['alarm_before_event_ends'] = 'Az esemény vége előtt';
$labels['alarm_after_event_ends'] = 'Az esemény vége után';
$labels['alarm_at_specific_time'] = 'Egy adott időpontban';

// search
$labels['specify_at_least_one_calendar'] = 'Határozzon meg legalább egy naptárat a kereséshez.';
$labels['specify_text_to_search'] = 'Adja meg a keresendő szöveget.';
$labels['clear_search_results'] = 'Törlés';
$labels['search_outdated_message'] = 'A keresési eredmények elavultak lehetnek. Fontolja meg a keresés újbóli végrehajtását.';
$labels['date_range'] = 'Dátumtartomány';
$labels['date_range_subtitle'] = 'Dátumtartomány:';
$labels['search_results'] = 'Talált eredmények: $n';
$labels['modify_search_options'] = 'Próbálja meg módosítani a keresési lehetőségeket.';
$labels['show_shortcuts'] = 'Gyorsbillentyűk megjelenítése';
$labels['hide_shortcuts'] = 'Gyorsbillentyűk elrejtése';
$labels['this_month'] = 'Ebben a hónapban';
$labels['this_year'] = 'Ebben az évben';
$labels['past_30_days'] = 'Elmúlt 30 nap';
$labels['next_30_days'] = 'Következő 30 nap';
$labels['past_6_months'] = 'Elmúlt 6 hónap';
$labels['next_6_months'] = 'Következő 6 hónap';

// calendar grid
$labels['cannot_load_events'] = 'Nem lehet betölteni a "$c" naptár eseményeit.';
