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
$labels['create_event'] = 'Loo sündmus';
$labels['create_event_button'] = 'Uus sündmus';
$labels['day'] = 'Päev';
$labels['week'] = 'Nädal';
$labels['month'] = 'Kuu';
$labels['agenda'] = 'Päevakord';

// editing calendars
$labels['calendars'] = 'Kalendrid';
$labels['my_calendar'] = 'Minu kalender';
$labels['create_calendar'] = 'Kalendri loomine';
$labels['new_calendar'] = 'Uus kalender';
$labels['edit_calendar'] = 'Kalendri muutmine';
$labels['remove_calendar'] = 'Kustuta kalender';
$labels['confirm_remove_calendar'] = 'Kas olete kindel, et soovite selle kalendri kustutada?';
$labels['calendar_removed'] = 'Kalender kustutatud.';
$labels['notifications'] = 'Teated';
$labels['default_event_visibility'] = 'Sündmuse vaikimisi nähtavus';
$labels['time_zone'] = 'Kalendri ajavöönd';
$labels['share_calendar'] = 'Jaga kalender';
$labels['permissions'] = 'Õigused';
$labels['permission_publish_calendar'] = 'Kalendri avaldamine';
$labels['permission_share_calendar'] = 'Jaga kalender';
$labels['permission_edit_events'] = 'Sündmuste redigeerimine';
$labels['permission_see_details'] = 'Vaata üksikasju';
$labels['permission_publish_calendar_info'] = 'Kui see on märgitud, saab kasutaja kalendrit avaldada.';
$labels['permission_share_calendar_info'] = 'Kui see on märgitud, saab kasutaja jagada kalendrit teiste kasutajatega.';
$labels['permission_edit_events_info'] = 'Kui see on märgitud, saab kasutaja kalendrisündmusi muuta ja kustutada.';
$labels['permission_see_details_info'] = 'Kui see on märgitud, jagatakse kõiki sündmuse üksikasju. Kui see ei ole märgitud, jagatakse ainult vaba/hõivatud teavet.';
$labels['share_send_notification'] = 'Teavita';
$labels['share_send_notification_info'] = 'Kui see on märgitud, saadetakse kasutajale e-kiri, mis teavitab teda uuest jagatud kalendrist.';
$labels['new_share_not_saved_warning'] = 'Sisestasite kalendri jagamiseks e-posti aadressi, kuid ei klõpsanud jagamise nupule, et lisada see jagatud nimekirja. Kas soovite jätkata salvestamist ilma kalendrit selle kasutajaga jagamata?';
$labels['share'] = 'Jaga';
$labels['shared_marker'] = '(Jagatud)';
$labels['owner'] = 'Omanik';
$labels['publish_calendar'] = 'Kalendri avaldamine';
$labels['create_link'] = 'Loo link';
$labels['reset'] = 'Reset';
$labels['remove'] = 'Eemaldage';
$labels['publish_full'] = 'Avaldage kogu ürituse teave.';
$labels['publish_busy'] = 'Avaldage ainult vaba/hõivatud teavet.';
$labels['sync_no_id'] = 'CalDAV-valikud muutuvad kättesaadavaks pärast kalendri salvestamist.';
$labels['share_no_id'] = 'Kalendri jagamisvõimalused muutuvad kättesaadavaks pärast kalendri salvestamist.';
$labels['publish_no_id'] = 'Kalendri avaldamisvõimalused muutuvad kättesaadavaks pärast kalendri salvestamist.';
$labels['import_no_id'] = 'Impordi- ja ekspordivõimalused muutuvad kättesaadavaks pärast kalendri salvestamist.';
$labels['unable_to_save_calendar_data'] = 'Kalendriandmete salvestamine ei ole võimalik.';
$labels['unable_to_retrieve_calendar_data'] = 'Kalendriandmeid ei ole võimalik kätte saada. Kalender ei pruugi enam saadaval olla.';
$labels['unable_add_shared_calendar'] = 'Ei ole võimalik lisada jagatud kalendrit. Kalender ei pruugi enam saadaval olla.';
$labels['itip_organizer_message'] = 'Teid kutsuti sellele üritusele, kuid te ei ole korraldaja. Muudatusi, mida te selles sündmuses teete, ei saadeta teistele osalejatele.';
$labels['cannot_share_with_yourself'] = 'Te ei saa oma kalendrit iseendaga jagada.';
$labels['cannot_share_with_calendar_owner'] = 'Seda kalendrit ei saa jagada kalendri omanikuga ($e)';
$labels['setting_default_notification_type'] = 'Vaikimisi teated uute sündmuste kohta';
$labels['calendar_name_cannot_be_empty'] = 'Kalendri nimi ei tohi olla tühi.';

// caldav server
$labels['sync_calendar'] = 'Kalendri sünkroonimine';
$labels['password_too_short'] = 'Parool peaks olema vähemalt 10 tähemärki pikk';
$labels['read_only'] = 'Ainult lugemine';
$labels['add_connection'] = 'Lisa ühendus';
$labels['show_details'] = 'Näita üksikasju';
$labels['hide_details'] = 'Peida üksikasjad';
$labels['caldav_connection_password'] = 'Parool, mille määrasite selle ühenduse loomisel.';
$labels['yes'] = 'Jah';
$labels['no'] = 'Ei';
$labels['confirm_remove_sync'] = 'Kas olete kindel, et soovite selle ühenduse lõplikult eemaldada?';
$labels['remove_sync_error'] = 'Ühenduse eemaldamisel tekkis viga.';
$labels['create_new_caldav_connection'] = 'Uue CalDav-ühenduse loomine';
$labels['your_caldav_connections'] = 'Teie CalDav ühendused';
$labels['created_at'] = 'Loodud';
$labels['connected_at'] = 'Viimane sünkroonimine';
$labels['never'] = 'Mitte kunagi';
$labels['alternate_url'] = 'Alternatiivne URL';
$labels['caldav_password_info'] = 'Turvalisuse huvides ei salvestata seda salasõna serverisse. Kui olete selle unustanud, kustutage see ühendus ja looge uus.';
$labels['caldav_url_info'] = 'Kasutage seda URL-aadressi rakendustega, mis toetavad kalendri leidmist, näiteks: iOS ja Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Kasutage seda URL-aadressi näiteks rakenduste puhul, mis ei tööta ülaltoodud lühikese url-iga: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'CalDAV-serveri URL on kehtetu.';
$labels['cannot_resolve_caldav_host'] = 'CalDAV-serveri hosti ei õnnestu tuvastada.';
$labels['private_caldav_addresses_not_allowed'] = 'Era- ja reserveeritud CalDAV-aadressid ei ole lubatud.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Lisa jagatud kalender';
$labels['shared_calendars'] = 'Jagatud kalendrid';
$labels['no_shared_calendars'] = 'Ühiseid kalendreid lisada ei saa.';
$labels['add_to_my_calendars'] = 'Lisa minu kalendritesse';
$labels['remove_from_my_calendars'] = 'Eemalda minu kalendritest';
$labels['unshare'] = 'Unshare';
$labels['unshare_warning'] = 'Kui tühistate selle kalendri jagamise, kaob see sellest nimekirjast ja te ei saa seda uuesti lisada.';
$labels['calendar_name'] = 'Kalendri nimi';
$labels['calendar_owner'] = 'Kalendri omanik';
$labels['shared_date'] = 'Jagatud';
$labels['unable_to_share'] = 'Kalendrit ei saa jagada järgmiste kasutajatega, sest nende e-posti kontod asuvad kaugserveris:';

// shared calendar email
$labels['shared_email_subject'] = '$u jagas teiega kalendrit';
$labels['shared_email_body'] = 'Kasutaja $u on andnud teile juurdepääsu kalendrile nimega "$c". Selle kalendri lisamiseks aktiivsete kalendrite nimekirja klõpsake alloleval lingil:';
$labels['shared_email_body_cpanel'] = 'Kasutaja $u on andnud teile juurdepääsu kalendrile nimega "$c". Selle kalendri lisamiseks oma aktiivsete kalendrite nimekirja logige sisse Roundcube\'i, minge rubriiki Kalender, klõpsake külgribal plussikoonil ja valige Lisa jagatud kalender.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Pühade kalendrid';
$labels['edit_holiday_calendar'] = 'Puhkuste kalendri muutmine';
$labels['religious_holidays'] = 'Usulised pühad';
$labels['country_holidays'] = 'Puhkused maal';
$labels['add'] = 'Lisa';
$labels['no_more_holidays_allowed'] = 'Te ei saa enam pühadekalendreid lisada.';
$labels['cannot_load_holidays'] = 'Google\'i serverist ei ole võimalik puhkust laadida.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Lisa sünnipäevakalender';
$labels['edit_birthday_calendar'] = 'Sünnipäevakalendri muutmine';
$labels['contact_birthdays'] = 'Kontakt Sünnipäevad';
$labels['contact_birthdays_info'] = 'See kalender otsib teie kontaktide nimekirjast sünnipäevade andmed ja kuvab need kalendrisündmuste kujul.';
$labels['birthday_event_title'] = 'Sünnipäev: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Google kalendri lisamine';
$labels['edit_google_calendar'] = 'Google\'i kalendri muutmine';
$labels['google_id'] = 'Google\'i kalendri ID';
$labels['new_google_calendar'] = 'Uus Google\'i kalender';
$labels['specify_google_calendar_id'] = 'Palun sisestage Google\'i kalendri ID';
$labels['calendar_id_format_error'] = 'Google\'i kalendri ID peaks sisaldama @-märki.';
$labels['cannot_load_google_calendar'] = 'Google\'i kalendrit "$c" ei saa laadida. Veenduge, et kalendri ID on õige ja et kalender on avalikustatud.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'CalDAV-kalendri lisamine';
$labels['edit_caldav_calendar'] = 'CalDAV-kalendri muutmine';
$labels['add_caldav_calendar_information'] = 'CalDAV-kalendri tellimiseks määrake CalDAV-serveri andmed ja klõpsake nuppu Find calendars.';
$labels['caldav_url'] = 'CalDAV URL';
$labels['find_calendars'] = 'Leia kalendrid';
$labels['searching_for_calendars'] = 'Kalendrite otsimine, palun oodake...';
$labels['select_calendars'] = 'Valige lisatavad kalendrid:';
$labels['read_only_status_yes'] = 'Jah';
$labels['read_only_status_no'] = 'Ei';
$labels['subscribed'] = 'Registreeritud';

$labels['refresh_calendar'] = 'Värskenda kalendrit';
$labels['refresh_every_minute'] = 'Iga minut';
$labels['refresh_every_5_minutes'] = 'Iga 5 minuti tagant';
$labels['refresh_every_15_minutes'] = 'Iga 15 minuti tagant';
$labels['refresh_every_30_minutes'] = 'Iga 30 minuti tagant';
$labels['refresh_every_60_minutes'] = 'Iga 60 minuti tagant';
$labels['refresh_never'] = 'Mitte kunagi';

$labels['cannot_connect_to_caldav_server'] = 'Ei saa ühendust CalDAV serveriga "$n".';
$labels['caldav_client_error_url'] = 'Ei saa ühendust CalDAV-serveriga. Kontrollige serveri URL-i.';
$labels['caldav_client_error_credentials'] = 'Vale CalDAVi kasutajanimi või parool.';
$labels['caldav_client_error_empty'] = 'Server ei tagastanud ühtegi kalendrit, mida saaks lisada. Kontrollige CalDAV-i URL-i.';
$labels['caldav_client_error_server_info'] = 'Vale CalDAV-serveri teave.';
$labels['caldav_client_error_add_calendar'] = 'Ei saa salvestada kalendrit "$n".';
$labels['caldav_client_error_delete_event'] = 'Ei saa sündmust kustutada';
$labels['caldav_client_error_save_event'] = 'CalDAV-sündmust ei saa salvestada: see on tõenäoliselt teisaldatud, kustutatud või kalender pole kättesaadav.';
$labels['caldav_client_error_calendar_added'] = 'Kalender "$n" on juba teie kalendrite nimekirjas.';

// previewing event
$labels['remove_recurring_event'] = 'Kustuta korduvsündmus';
$labels['remove_one'] = 'Kustuta ainult see instants';
$labels['remove_all'] = 'Kustuta kõik instantsid';
$labels['edit_event'] = 'Sündmuse muutmine';
$labels['event_removed'] = 'Sündmus kustutatud.';
$labels['go_to_url'] = 'Mine URL-le';
$labels['confirm_remove_event'] = 'Kas olete kindel, et soovite selle sündmuse kustutada?';

// calendar/agenda grid
$labels['no_events'] = 'Ei ole ühtegi sündmust, mida kuvada.';

// importing events
$labels['select_calendar'] = 'Valige kalender';
$labels['select_calendar_for_events'] = 'Valige kalender, kuhu soovite sündmused lisada:';
$labels['add_events'] = 'Lisage sündmused';
$labels['add_events_to_calendar'] = 'Lisa sündmused kalendrisse';
$labels['events_imported_successfully'] = 'Edukalt imporditud sündmused';
$labels['events_imported_with_errors'] = 'Mingit muud sündmust ei saanud importida.';
$labels['error_importing_events'] = 'Sündmuste importimisel kalendrisse tekkis viga';

// editing events
$labels['calendar'] = 'Kalender';
$labels['new_event'] = 'Uus sündmus';
$labels['summary'] = 'Kokkuvõte';
$labels['recurrence'] = 'Taastumine';
$labels['title'] = 'Pealkiri';
$labels['location'] = 'Asukoht';
$labels['description'] = 'Kirjeldus';
$labels['comment'] = 'Kommentaar';
$labels['url'] = 'URL';
$labels['name'] = 'Nimi';
$labels['start'] = 'Start';
$labels['end'] = 'End';
$labels['show_timezones'] = 'Näita ajavööndeid';
$labels['reset_timezones'] = 'Ajavööndite lähtestamine vaikimisi';
$labels['all_day'] = 'Kogu päev';
$labels['use_calendar_colors'] = 'Kasutage kalendri värve';
$labels['color'] = 'Värv';
$labels['background'] = 'Taustaks';
$labels['text'] = 'Tekst';
$labels['show_me_as'] = 'Näita mulle kui';
$labels['available'] = 'Saadaval';
$labels['busy'] = 'Hõivatud';
$labels['visibility'] = 'Nähtavus';
$labels['calendar_default'] = 'Kalendri vaikimisi';
$labels['public'] = 'Avalik';
$labels['private'] = 'Eraldi';
$labels['repeat'] = 'Kordus';
$labels['repeat_every'] = 'Korrake iga';
$labels['never'] = 'Mitte kunagi';
$labels['daily'] = 'Igapäevane';
$labels['weekly'] = 'Nädalane';
$labels['monthly'] = 'Igakuine';
$labels['yearly'] = 'Aastane';
$labels['days'] = 'päev(ad)';
$labels['weeks'] = 'nädal(ad)';
$labels['months'] = 'kuu(d)';
$labels['years'] = 'aasta(d)';
$labels['repeat_on'] = 'Kordus on';
$labels['repeat_by'] = 'Korda poolt';
$labels['day_of_month'] = 'Kuu päev';
$labels['day_of_week'] = 'Nädala päev';
$labels['not_specified'] = 'Ei ole täpsustatud';
$labels['first'] = 'Esimene';
$labels['second'] = 'Teine';
$labels['third'] = 'Kolmas';
$labels['fourth'] = 'Neljas';
$labels['fifth'] = 'Viies';
$labels['second_to_last'] = 'Eelviimane';
$labels['last'] = 'Viimane';
$labels['attachment_too_large'] = 'Manustatav fail on liiga suur. Maksimaalne lubatud suurus on $s.';
$labels['bg_color'] = 'Taustavärv';
$labels['tx_color'] = 'Teksti värvus';
$labels['add_notification'] = 'Teatise lisamine';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'Sellel sündmusel ei ole teateid.';
$labels['minutes'] = 'protokoll';
$labels['hours'] = 'tundi';
$labels['link'] = 'Link';
$labels['attach_file_from_web'] = 'Lisa fail';
$labels['attachment_url'] = 'Manuse URL';
$labels['invalid_url'] = 'See URL on kehtetu.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Dubleeri';
$labels['duplicate_suffix'] = '(duplikaat)';
$labels['no_notifications'] = 'Sellel sündmusel ei ole teateid.';
$labels['this_calendar_is_hidden'] = 'See kalender on peidetud. Salvestatud sündmused kuvatakse alles siis, kui kalender on nähtav.';
$labels['make_calendar_visible'] = 'Tee see kalender nähtavaks.';

// recurrence
$labels['recurrence_range'] = 'Korduvusvahemik';
$labels['repeat_forever'] = 'Korda igavesti';
$labels['repeat_every_weekday'] = 'Kordus igal nädalapäeval';
$labels['last_day'] = 'Viimane päev';
$labels['every'] = 'Iga';
$labels['weekday'] = 'Nädalapäevad';
$labels['weekend_day'] = 'Nädalavahetuse päev';
$labels['day_of_week'] = 'Nädala päev';
$labels['day_of_month'] = 'Kuu päev';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Lõpp pärast';
$labels['occurrences'] = 'juhtum(id)';

// end recurrence on date:
$labels['end_by_date'] = 'Lõpp kuupäevaks';
$labels['removed_events_title'] = 'Sündmused, mis on eemaldatud korduvatest sarjadest';
$labels['restore'] = 'Taasta';

// settings page
$labels['setting_view'] = 'Esialgne kalendri vaade';
$labels['setting_first_day'] = 'Nädal algab';
$labels['setting_agenda_week_span'] = 'Päevakorra ajaline kestus (nädalat)';
$labels['setting_slot_duration'] = 'Päevane ajavahe (minutites)';
$labels['setting_scroll_time'] = 'Kerige aega';
$labels['setting_calendar'] = 'Sündmuste vaikimisi kalender';
$labels['setting_refresh'] = 'Kalendrite uuendamine';
$labels['setting_week_numbers'] = 'Nädala numbrid väikeses kalendris';
$labels['setting_event_border'] = 'Kategooria värvid kui sündmuse piirid';
$labels['show_sunrise'] = 'Näita päikesetõusu kuu vaates';
$labels['show_sunset'] = 'Näita päikeseloojangut kuu vaates';
$labels['categories'] = 'Kategooriad';
$labels['category'] = 'Kategooria';
$labels['no_category'] = 'Kategooria puudub';
$labels['add_category'] = 'Lisa kategooria';
$labels['setting_alarm_sound'] = 'Teavituse heli';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambus';
$labels['alarm_sound_bells'] = 'Bells';
$labels['alarm_sound_chord'] = 'Akord';
$labels['alarm_sound_circles'] = 'Ringid';
$labels['alarm_sound_glass'] = 'Klaas';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metall';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Vile';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Mängi heli';

// alarms
$labels['upcoming_events'] = 'Tulevased üritused';
$labels['dismiss'] = 'Lase lahti';
$labels['dismiss_all'] = 'Lase kõik välja';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minutit';
$labels['10_minutes'] = '10 minutit';
$labels['20_minutes'] = '20 minutit';
$labels['30_minutes'] = '30 minutit';
$labels['1_hour'] = '1 tund';
$labels['2_hours'] = '2 tundi';
$labels['tomorrow'] = 'Homseni';
$labels['next_week'] = 'Järgmise nädalani';

// sidebar
$labels['setting_show_xcalendar'] = 'Näita kalendri päevakorda';
$labels['todays_agenda'] = 'Tänane päevakord';
$labels['no_events_today'] = 'Tänaseks ei ole sündmusi kavandatud.';
$labels['sidebar_more'] = 'Rohkem...';

// event invitation
$labels['itip_title_request'] = 'Teid on kutsutud üritusele.';
$labels['itip_title_modified'] = 'Seda sündmust on muudetud.';
$labels['itip_title_reply_accepted'] = '$u on teie kutse vastu võtnud';
$labels['itip_title_reply_tentative'] = '$u on teie kutse esialgu vastu võtnud';
$labels['itip_title_reply_declined'] = '$u on teie kutse tagasi lükanud';
$labels['itip_title_cancel'] = 'See üritus on tühistatud.';

$labels['itip_email_subject_request'] = 'Kutse:';
$labels['itip_email_subject_request_modified'] = 'Üritus muudetud:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Tühistatud:';

$labels['itip_popup_event_added'] = 'Sündmus lisatud kalendrisse.';
$labels['itip_popup_event_updated'] = 'Sündmus uuendatud kalendris.';
$labels['itip_popup_event_deleted'] = 'Sündmus kustutati kalendrist.';
$labels['itip_popup_response_sent'] = 'Korraldajale saadetud vastus.';

$labels['itip_message_invitation_accepted'] = 'Te olete selle kutse vastu võtnud.';
$labels['itip_message_invitation_tentative'] = 'Te olete selle kutse esialgu vastu võtnud.';
$labels['itip_message_invitation_declined'] = 'Te olete selle kutse tagasi lükanud.';
$labels['itip_message_event_deleted'] = 'Sündmus on teie kalendrist kustutatud.';
$labels['itip_message_event_updated'] = 'Sündmus teie kalendris on uuendatud.';
$labels['itip_message_event_missing'] = 'Seda sündmust ei ole üheski teie kalendris olemas.';

$labels['itip_accept'] = 'Aktsepteeri';
$labels['itip_tentative'] = 'Esialgne';
$labels['itip_decline'] = 'Langus';
$labels['itip_change_reponse'] = 'Muuda vastus.';
$labels['itip_add_to_calendar'] = 'Lisa kalendrisse';
$labels['itip_reply_to_sender'] = 'Vastus saatjale';
$labels['itip_dont_import'] = 'Ära lisa kalendrisse';
$labels['itip_update_in_calendar'] = 'Sündmuse ajakohastamine kalendris';
$labels['itip_delete_from_calendar'] = 'Sündmuse kustutamine kalendrist';

$labels['mail_subject_invitation'] = 'Kutse:';
$labels['mail_subject_update'] = 'Uuendatud kutse:';
$labels['mail_subject_cancel'] = 'Sündmus on tühistatud:';

// event email notifications
$labels['mail_title'] = 'Pealkiri:';
$labels['mail_start'] = 'Alusta:';
$labels['mail_end'] = 'Lõpp:';
$labels['mail_description'] = 'Kirjeldus:';
$labels['mail_location'] = 'Asukoht:';

// event attendees
$labels['attendees'] = 'Osalejad';
$labels['attendee_role'] = 'Rolli';
$labels['attendee_role_required'] = 'Nõutav';
$labels['attendee_role_optional'] = 'Valikuline';
$labels['attendee_role_chair'] = 'Tooli';
$labels['attendee_role_none'] = 'Mitteosalev';
$labels['organizer'] = 'Korraldaja';
$labels['attendance'] = 'Osalemine';
$labels['your_status'] = 'Teie staatus';
$labels['attendance_going'] = 'Lähed?';
$labels['attendee_status_yes'] = 'Jah';
$labels['attendee_status_no'] = 'Ei';
$labels['attendee_status_maybe'] = 'Võib-olla';
$labels['attendee_status_delegated'] = 'Delegeeritud';
$labels['attendee_status_waiting'] = 'Oodates';
$labels['attendee_status_waiting_for_confirmation'] = 'Ootab kinnitust';
$labels['attendee_status_info_yes'] = 'Osaleb';
$labels['attendee_status_info_no'] = 'Ei osale';
$labels['attendee_status_info_maybe'] = 'Võib osaleda';
$labels['attendee_status_info_delegated'] = 'Delegeeritud osalemine';
$labels['attendee_role'] = 'Rolli';
$labels['attendee_role_info'] = 'Täpsustage, kas osaleja peab osalema koosolekul või juhatama seda.';
$labels['attendee_notify'] = 'Teavita';
$labels['attendee_notify_info'] = 'Kui see on märgitud, saadetakse osalejale kutse e-kiri.';
$labels['add_attendee'] = 'Lisa osaleja';
$labels['attendee_notify_question'] = 'Saate osalejatele teavitusmeile?';
$labels['attendee_notify_textarea'] = 'Lisage vabatahtlik sõnum';
$labels['attendee_notify_send'] = 'Saada';
$labels['attendee_notify_dont'] = 'Ärge saatke';
$labels['attendee_notify_cancel'] = 'Tühista';
$labels['new_attendee_not_saved_warning'] = 'Sisestasite osaleja e-posti aadressi, kuid ei lisanud teda osalejate nimekirja. Kas soovite salvestada ilma selle osalejata?';

// import / export
$labels['import_export'] = 'Import / eksport';
$labels['export_to_file'] = 'Eksportida faili';
$labels['export_info'] = 'Klõpsake seda nuppu, et eksportida ja laadida alla selle kalendri sündmused iCalendri (ics) formaadis. Kui kalender sisaldab palju sündmusi, võib ekspordiprotsess võtta mõne hetke.';
$labels['export_no_events'] = 'Selles kalendris ei ole ühtegi eksporditavat sündmust.';
$labels['import_from_file'] = 'Import failist';
$labels['import_info'] = 'Klõpsake seda nuppu, et importida sündmusi sellesse kalendrisse iCalendar (ics) failist.';

// email notifications
$labels['appointment_reminder'] = 'Kohtumise meeldetuletus';
$labels['email_notification_subject'] = 'Meeldetuletus: $t kell $d ($z)';
$labels['email_notification_cron_error'] = 'E-posti teated on välja lülitatud. Võtke ühendust administraatoriga.';
$labels['alarm_before_event_starts'] = 'Enne ürituse algust';
$labels['alarm_after_event_starts'] = 'Pärast ürituse algust';
$labels['alarm_before_event_ends'] = 'Enne ürituse lõppu';
$labels['alarm_after_event_ends'] = 'Pärast sündmuse lõppu';
$labels['alarm_at_specific_time'] = 'Konkreetsel ajal';

// search
$labels['specify_at_least_one_calendar'] = 'Määrake vähemalt üks otsitav kalender.';
$labels['specify_text_to_search'] = 'Sisestage tekst, mida soovite otsida.';
$labels['clear_search_results'] = 'Selge';
$labels['search_outdated_message'] = 'Otsingutulemused võivad olla vananenud. Kaaluge otsingu uuesti käivitamist.';
$labels['date_range'] = 'Kuupäeva vahemik';
$labels['date_range_subtitle'] = 'Kuupäevade vahemik:';
$labels['search_results'] = 'Leitud tulemused: $n';
$labels['modify_search_options'] = 'Proovige muuta otsinguvalikuid.';
$labels['show_shortcuts'] = 'Näita otseteed';
$labels['hide_shortcuts'] = 'Peida otseteed';
$labels['this_month'] = 'Sel kuul';
$labels['this_year'] = 'Sel aastal';
$labels['past_30_days'] = 'Viimased 30 päeva';
$labels['next_30_days'] = 'Järgmised 30 päeva';
$labels['past_6_months'] = 'Viimased 6 kuud';
$labels['next_6_months'] = 'Järgmised 6 kuud';

// calendar grid
$labels['cannot_load_events'] = 'Ei saa laadida sündmusi kalendrile "$c".';
