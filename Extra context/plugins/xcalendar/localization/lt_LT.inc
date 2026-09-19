<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Kalendorius';
$labels['app_menu_title'] = 'Kalendorius';

// menu
$labels['create_event'] = 'Sukurti renginį';
$labels['create_event_button'] = 'Naujas renginys';
$labels['day'] = 'Diena';
$labels['week'] = 'Savaitė';
$labels['month'] = 'Mėnuo';
$labels['agenda'] = 'Darbotvarkė';

// editing calendars
$labels['calendars'] = 'Kalendoriai';
$labels['my_calendar'] = 'Mano kalendorius';
$labels['create_calendar'] = 'Sukurti kalendorių';
$labels['new_calendar'] = 'Naujas kalendorius';
$labels['edit_calendar'] = 'Redaguoti kalendorių';
$labels['remove_calendar'] = 'Ištrinti kalendorių';
$labels['confirm_remove_calendar'] = 'Ar tikrai norite ištrinti šį kalendorių?';
$labels['calendar_removed'] = 'Kalendorius ištrintas.';
$labels['notifications'] = 'Pranešimai';
$labels['default_event_visibility'] = 'Numatytasis įvykio matomumas';
$labels['time_zone'] = 'Kalendoriaus laiko juosta';
$labels['share_calendar'] = 'Dalytis kalendoriumi';
$labels['permissions'] = 'Leidimai';
$labels['permission_publish_calendar'] = 'Paskelbti kalendorių';
$labels['permission_share_calendar'] = 'Dalytis kalendoriumi';
$labels['permission_edit_events'] = 'Redaguoti įvykius';
$labels['permission_see_details'] = 'Žiūrėti daugiau informacijos';
$labels['permission_publish_calendar_info'] = 'Jei pažymėta, naudotojas galės paskelbti kalendorių.';
$labels['permission_share_calendar_info'] = 'Jei pažymėta, naudotojas galės dalytis kalendoriumi su kitais naudotojais.';
$labels['permission_edit_events_info'] = 'Jei pažymėta, naudotojas galės redaguoti ir ištrinti kalendoriaus įvykius.';
$labels['permission_see_details_info'] = 'Jei pažymėta, bus dalijamasi visa įvykio informacija. Jei nepažymėta, bus dalijamasi tik turima/užimta informacija.';
$labels['share_send_notification'] = 'Praneškite';
$labels['share_send_notification_info'] = 'Jei pažymėta, naudotojui bus išsiųstas el. laiškas, kuriame jis bus informuotas apie naujai bendrinamą kalendorių.';
$labels['new_share_not_saved_warning'] = 'Įvedėte el. laišką, su kuriuo norite bendrinti kalendorių, bet nepaspaudėte mygtuko Bendrinti, kad įtrauktumėte jį į bendrinamą sąrašą. Tęsti išsaugojimą nesidalijant kalendoriumi su nurodytu naudotoju?';
$labels['share'] = 'Dalintis';
$labels['shared_marker'] = '(Bendrai)';
$labels['owner'] = 'Savininkas';
$labels['publish_calendar'] = 'Paskelbti kalendorių';
$labels['create_link'] = 'Sukurti nuorodą';
$labels['reset'] = 'Iš naujo nustatyti';
$labels['remove'] = 'Pašalinti';
$labels['publish_full'] = 'Paskelbkite visą informaciją apie renginį.';
$labels['publish_busy'] = 'Skelbkite tik informaciją apie laisvas ir (arba) užimtas vietas.';
$labels['sync_no_id'] = 'CalDAV parinktys bus prieinamos, kai išsaugosite kalendorių.';
$labels['share_no_id'] = 'Kalendoriaus bendrinimo parinktys bus prieinamos, kai kalendorių išsaugosite.';
$labels['publish_no_id'] = 'Kalendoriaus skelbimo parinktys bus prieinamos, kai kalendorių išsaugosite.';
$labels['import_no_id'] = 'Importavimo ir eksportavimo parinktys bus prieinamos po to, kai išsaugosite kalendorių.';
$labels['unable_to_save_calendar_data'] = 'Nepavyksta išsaugoti kalendoriaus duomenų.';
$labels['unable_to_retrieve_calendar_data'] = 'Nepavyksta gauti kalendoriaus duomenų. Kalendorius gali būti nebeprieinamas.';
$labels['unable_add_shared_calendar'] = 'Nepavyksta pridėti bendrojo kalendoriaus. Kalendorius gali būti nebeprieinamas.';
$labels['itip_organizer_message'] = 'Buvote pakviestas į šį renginį, bet nesate jo organizatorius. Jūsų padaryti šio įvykio pakeitimai nebus siunčiami kitiems dalyviams.';
$labels['cannot_share_with_yourself'] = 'Negalite dalytis savo kalendoriumi su savimi.';
$labels['cannot_share_with_calendar_owner'] = 'Jūs negalite dalintis šiuo kalendoriumi su kalendoriaus savininku ($e)';
$labels['setting_default_notification_type'] = 'Numatytuosius pranešimus apie naujus įvykius';
$labels['calendar_name_cannot_be_empty'] = 'Kalendoriaus pavadinimas negali būti tuščias.';

// caldav server
$labels['sync_calendar'] = 'Sinchronizuoti kalendorių';
$labels['password_too_short'] = 'Slaptažodis turi būti ne trumpesnis kaip 10 simbolių.';
$labels['read_only'] = 'Tik skaityti';
$labels['add_connection'] = 'Pridėti ryšį';
$labels['show_details'] = 'Rodyti informaciją';
$labels['hide_details'] = 'Paslėpti informaciją';
$labels['caldav_connection_password'] = 'slaptažodis, kurį nurodėte kurdami šį ryšį.';
$labels['yes'] = 'Taip';
$labels['no'] = 'Ne';
$labels['confirm_remove_sync'] = 'Ar tikrai norite visam laikui pašalinti šį ryšį?';
$labels['remove_sync_error'] = 'Šalinant ryšį įvyko klaida.';
$labels['create_new_caldav_connection'] = 'Sukurti naują "CalDav" ryšį';
$labels['your_caldav_connections'] = 'Jūsų "CalDav" jungtys';
$labels['created_at'] = 'Sukurta';
$labels['connected_at'] = 'Paskutinis sinchronizavimas';
$labels['never'] = 'Niekada';
$labels['alternate_url'] = 'Pakaitinis URL adresas';
$labels['caldav_password_info'] = 'Saugumo sumetimais šis slaptažodis serveryje nesaugomas. Jei jį pamiršote, ištrinkite šį prisijungimą ir sukurkite naują.';
$labels['caldav_url_info'] = 'Šį URL naudokite su programomis, kurios palaiko kalendoriaus atradimą, pavyzdžiui: "iOS" ir "Mac OS Calendar", "Evolution", "emClient".';
$labels['caldav_alternate_url_info'] = 'Šį URL naudokite su programomis, kurios neveikia su pirmiau nurodytu trumpuoju URL, pvz: "Outlook CalDav Synchronizer", "Thunderbird Lightning".';
$labels['invalid_caldav_url'] = 'Neteisingas CalDAV serverio URL adresas.';
$labels['cannot_resolve_caldav_host'] = 'Nepavyksta nustatyti CalDAV serverio hosto.';
$labels['private_caldav_addresses_not_allowed'] = 'Privačios ir rezervuotos CalDAV adresų nuorodos neleidžiamos.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Pridėti bendrinamą kalendorių';
$labels['shared_calendars'] = 'Bendrai naudojami kalendoriai';
$labels['no_shared_calendars'] = 'Bendrai naudojamų kalendorių, kuriuos galite pridėti, nėra.';
$labels['add_to_my_calendars'] = 'Įtraukti į mano kalendorius';
$labels['remove_from_my_calendars'] = 'Pašalinti iš mano kalendorių';
$labels['unshare'] = 'Atšaukti bendrinimą';
$labels['unshare_warning'] = 'Jei panaikinsite šio kalendoriaus bendrinimą, jis išnyks iš šio sąrašo ir negalėsite jo vėl pridėti.';
$labels['calendar_name'] = 'Kalendoriaus pavadinimas';
$labels['calendar_owner'] = 'Kalendoriaus savininkas';
$labels['shared_date'] = 'Bendras';
$labels['unable_to_share'] = 'Kalendoriaus negalima bendrinti su šiais naudotojais, nes jų el. pašto paskyros yra nutolusiame serveryje:';

// shared calendar email
$labels['shared_email_subject'] = '$u su jumis pasidalijo kalendoriumi';
$labels['shared_email_body'] = 'Naudotojas $u suteikė jums prieigą prie kalendoriaus, pavadinto "$c". Norėdami įtraukti šį kalendorių į aktyvių kalendorių sąrašą, spustelėkite toliau pateiktą nuorodą:';
$labels['shared_email_body_cpanel'] = 'Naudotojas $u suteikė jums prieigą prie kalendoriaus, pavadinto "$c". Norėdami įtraukti šį kalendorių į aktyvių kalendorių sąrašą, prisijunkite prie "Roundcube", eikite į "Calendar", spustelėkite pliuso piktogramą šoninėje juostoje ir pasirinkite "Add shared calendar".';

// adding holiday calendars
$labels['holiday_calendars'] = 'Atostogų kalendoriai';
$labels['edit_holiday_calendar'] = 'Redaguoti atostogų kalendorių';
$labels['religious_holidays'] = 'Religinės šventės';
$labels['country_holidays'] = 'Atostogos šalyje';
$labels['add'] = 'Pridėti';
$labels['no_more_holidays_allowed'] = 'Daugiau atostogų kalendorių pridėti negalima.';
$labels['cannot_load_holidays'] = 'Nepavyksta įkelti švenčių iš "Google" serverio.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Pridėti gimtadienio kalendorių';
$labels['edit_birthday_calendar'] = 'Redaguoti gimtadienio kalendorių';
$labels['contact_birthdays'] = 'Susisiekite su gimtadieniais';
$labels['contact_birthdays_info'] = 'Šis kalendorius iš kontaktų sąrašo gauna informaciją apie gimtadienius ir rodo ją kaip kalendoriaus įvykius.';
$labels['birthday_event_title'] = 'Gimtadienis: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Pridėti "Google" kalendorių';
$labels['edit_google_calendar'] = 'Redaguoti "Google" kalendorių';
$labels['google_id'] = '"Google" kalendoriaus ID';
$labels['new_google_calendar'] = 'Naujas "Google" kalendorius';
$labels['specify_google_calendar_id'] = 'Įveskite "Google" kalendoriaus ID';
$labels['calendar_id_format_error'] = '"Google" kalendoriaus ID turi būti simbolis @.';
$labels['cannot_load_google_calendar'] = '"Google" kalendoriaus "$c" negalima įkelti. Įsitikinkite, kad kalendoriaus ID yra teisingas ir kad kalendorius yra viešai prieinamas.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Pridėti "CalDAV" kalendorių';
$labels['edit_caldav_calendar'] = 'Redaguoti CalDAV kalendorių';
$labels['add_caldav_calendar_information'] = 'Jei norite prenumeruoti CalDAV kalendorių, nurodykite CalDAV serverio informaciją ir spustelėkite Ieškoti kalendorių.';
$labels['caldav_url'] = 'CalDAV URL';
$labels['find_calendars'] = 'Rasti kalendorių';
$labels['searching_for_calendars'] = 'Kalendorių paieška, palaukite...';
$labels['select_calendars'] = 'Pasirinkite kalendorius, kuriuos norite pridėti:';
$labels['read_only_status_yes'] = 'Taip';
$labels['read_only_status_no'] = 'Ne';
$labels['subscribed'] = 'Prenumeruojama';

$labels['refresh_calendar'] = 'Atnaujinti kalendorių';
$labels['refresh_every_minute'] = 'Kiekviena minutė';
$labels['refresh_every_5_minutes'] = 'Kas 5 minutes';
$labels['refresh_every_15_minutes'] = 'Kas 15 minučių';
$labels['refresh_every_30_minutes'] = 'Kas 30 minučių';
$labels['refresh_every_60_minutes'] = 'Kas 60 minučių';
$labels['refresh_never'] = 'Niekada';

$labels['cannot_connect_to_caldav_server'] = 'Nepavyksta prisijungti prie CalDAV serverio "$n".';
$labels['caldav_client_error_url'] = 'Nepavyksta prisijungti prie CalDAV serverio. Patikrinkite serverio URL.';
$labels['caldav_client_error_credentials'] = 'Neteisingas CalDAV vartotojo vardas arba slaptažodis.';
$labels['caldav_client_error_empty'] = 'Serveris negrąžino jokių kalendorių, kuriuos būtų galima pridėti. Patikrinkite CalDAV URL.';
$labels['caldav_client_error_server_info'] = 'Neteisinga CalDAV serverio informacija.';
$labels['caldav_client_error_add_calendar'] = 'Negalima išsaugoti kalendoriaus "$n".';
$labels['caldav_client_error_delete_event'] = 'Negalima ištrinti įvykio';
$labels['caldav_client_error_save_event'] = 'Nepavyksta išsaugoti CalDAV renginio: jis galėjo būti perkelta, ištrinta arba kalendorius yra neprieinamas.';
$labels['caldav_client_error_calendar_added'] = 'Kalendorius "$n" jau yra jūsų kalendorių sąraše.';

// previewing event
$labels['remove_recurring_event'] = 'Ištrinti pasikartojantį įvykį';
$labels['remove_one'] = 'Ištrinti tik šį atvejį';
$labels['remove_all'] = 'Ištrinti visus atvejus';
$labels['edit_event'] = 'Redaguoti įvykį';
$labels['event_removed'] = 'Įvykis ištrintas.';
$labels['go_to_url'] = 'Eiti į URL';
$labels['confirm_remove_event'] = 'Ar tikrai norite ištrinti šį įvykį?';

// calendar/agenda grid
$labels['no_events'] = 'Nėra rodomų įvykių.';

// importing events
$labels['select_calendar'] = 'Pasirinkite kalendorių';
$labels['select_calendar_for_events'] = 'Pasirinkite kalendorių, į kurį norite įtraukti įvykius:';
$labels['add_events'] = 'Pridėti įvykius';
$labels['add_events_to_calendar'] = 'Įtraukti įvykius į kalendorių';
$labels['events_imported_successfully'] = 'Įvykiai sėkmingai importuoti';
$labels['events_imported_with_errors'] = 'Kai kurių kitų įvykių nepavyko importuoti.';
$labels['error_importing_events'] = 'Importuojant įvykius į kalendorių įvyko klaida';

// editing events
$labels['calendar'] = 'Kalendorius';
$labels['new_event'] = 'Naujas renginys';
$labels['summary'] = 'Santrauka';
$labels['recurrence'] = 'Pasikartojimas';
$labels['title'] = 'Pavadinimas';
$labels['location'] = 'Vieta';
$labels['description'] = 'Aprašymas';
$labels['comment'] = 'Komentaras';
$labels['url'] = 'URL';
$labels['name'] = 'Pavadinimas';
$labels['start'] = 'Pradžia';
$labels['end'] = 'Pabaiga';
$labels['show_timezones'] = 'Rodyti laiko juostas';
$labels['reset_timezones'] = 'Iš naujo nustatyti numatytąsias laiko juostas';
$labels['all_day'] = 'Visą dieną';
$labels['use_calendar_colors'] = 'Naudokite kalendoriaus spalvas';
$labels['color'] = 'Spalva';
$labels['background'] = 'Pagrindinė informacija';
$labels['text'] = 'Tekstas';
$labels['show_me_as'] = 'Parodykite man kaip';
$labels['available'] = 'Galima rasti';
$labels['busy'] = 'Užimtas';
$labels['visibility'] = 'Matomumas';
$labels['calendar_default'] = 'Kalendoriaus numatytoji reikšmė';
$labels['public'] = 'Viešoji svetainė';
$labels['private'] = 'Privatus';
$labels['repeat'] = 'Pakartokite';
$labels['repeat_every'] = 'Pakartokite kiekvieną';
$labels['never'] = 'Niekada';
$labels['daily'] = 'Kasdien';
$labels['weekly'] = 'Savaitinis';
$labels['monthly'] = 'Mėnesinis';
$labels['yearly'] = 'Metinis';
$labels['days'] = 'diena (-os)';
$labels['weeks'] = 'savaitė (-ės)';
$labels['months'] = 'mėnuo (-iai)';
$labels['years'] = 'metai (-iai)';
$labels['repeat_on'] = 'Pakartokite';
$labels['repeat_by'] = 'Pakartoti pagal';
$labels['day_of_month'] = 'mėnesio diena';
$labels['day_of_week'] = 'savaitės diena';
$labels['not_specified'] = 'Nenurodyta';
$labels['first'] = 'Pirmasis';
$labels['second'] = 'Antrasis';
$labels['third'] = 'Trečiasis';
$labels['fourth'] = 'Ketvirtoji';
$labels['fifth'] = 'Penktasis';
$labels['second_to_last'] = 'Priešpaskutinis';
$labels['last'] = 'Paskutinis';
$labels['attachment_too_large'] = 'Priedo failas yra per didelis. Didžiausias leistinas dydis yra $s.';
$labels['bg_color'] = 'Fono spalva';
$labels['tx_color'] = 'Teksto spalva';
$labels['add_notification'] = 'Pridėti pranešimą';
$labels['popup'] = 'Iššokantis langas';
$labels['no_notifications'] = 'Šis įvykis neturi jokių pranešimų.';
$labels['minutes'] = 'minutės';
$labels['hours'] = 'valandos';
$labels['link'] = 'Nuoroda';
$labels['attach_file_from_web'] = 'Pridėti failą';
$labels['attachment_url'] = 'Priedo URL';
$labels['invalid_url'] = 'Šis URL adresas negalioja.';
$labels['xdropbox_name'] = '"Dropbox"';
$labels['xgoogle_drive_name'] = '"Google" diskas';
$labels['duplicate'] = 'Dublikuok';
$labels['duplicate_suffix'] = '(dublikatas)';
$labels['no_notifications'] = 'Šis įvykis neturi jokių pranešimų.';
$labels['this_calendar_is_hidden'] = 'Šis kalendorius yra paslėptas. Įrašyti renginiai nebus rodomi, kol kalendorius nebus matomas.';
$labels['make_calendar_visible'] = 'Nustatykite, kad šis kalendorius būtų matomas.';

// recurrence
$labels['recurrence_range'] = 'Pasikartojimo intervalas';
$labels['repeat_forever'] = 'Kartokite amžinai';
$labels['repeat_every_weekday'] = 'Kartoti kiekvieną darbo dieną';
$labels['last_day'] = 'Paskutinė diena';
$labels['every'] = 'Kiekvienas';
$labels['weekday'] = 'Savaitgalis';
$labels['weekend_day'] = 'Savaitgalio diena';
$labels['day_of_week'] = 'savaitės diena';
$labels['day_of_month'] = 'mėnesio diena';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Pabaiga po';
$labels['occurrences'] = 'įvykiai';

// end recurrence on date:
$labels['end_by_date'] = 'Pabaiga iki datos';
$labels['removed_events_title'] = 'Iš pasikartojančių serijų pašalinti įvykiai';
$labels['restore'] = 'Atkurti';

// settings page
$labels['setting_view'] = 'Pradinis kalendoriaus rodinys';
$labels['setting_first_day'] = 'Savaitės pradžia';
$labels['setting_agenda_week_span'] = 'Darbotvarkės trukmė (savaitėmis)';
$labels['setting_slot_duration'] = 'Dienos laiko intervalas (minutėmis)';
$labels['setting_scroll_time'] = 'Slinkite prie laiko';
$labels['setting_calendar'] = 'Numatytasis įvykių kalendorius';
$labels['setting_refresh'] = 'Atnaujinti kalendorius';
$labels['setting_week_numbers'] = 'Savaitės numeriai mažajame kalendoriuje';
$labels['setting_event_border'] = 'Kategorijos spalvos kaip įvykio ribos';
$labels['show_sunrise'] = 'Rodyti saulėtekio rodinį mėnesio rodinyje';
$labels['show_sunset'] = 'Rodyti saulėlydį mėnesio rodinyje';
$labels['categories'] = 'Kategorijos';
$labels['category'] = 'Kategorija';
$labels['no_category'] = 'Nėra kategorijos';
$labels['add_category'] = 'Pridėti kategoriją';
$labels['setting_alarm_sound'] = 'Pranešimo garsas';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambuko';
$labels['alarm_sound_bells'] = 'Varpai';
$labels['alarm_sound_chord'] = 'Akordas';
$labels['alarm_sound_circles'] = 'Apskritimai';
$labels['alarm_sound_glass'] = 'Stiklas';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metalas';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Žvaigždžių kelias';
$labels['alarm_sound_whistle'] = 'Švilpukas';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Groti garsą';

// alarms
$labels['upcoming_events'] = 'Būsimi renginiai';
$labels['dismiss'] = 'Atmesti';
$labels['dismiss_all'] = 'Atleisti visus';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minutės';
$labels['10_minutes'] = '10 minučių';
$labels['20_minutes'] = '20 minučių';
$labels['30_minutes'] = '30 minučių';
$labels['1_hour'] = '1 val.';
$labels['2_hours'] = '2 valandos';
$labels['tomorrow'] = 'Iki rytojaus';
$labels['next_week'] = 'Iki kitos savaitės';

// sidebar
$labels['setting_show_xcalendar'] = 'Rodyti kalendoriaus darbotvarkę';
$labels['todays_agenda'] = 'Šiandienos darbotvarkė';
$labels['no_events_today'] = 'Šiandien neplanuojama jokių renginių.';
$labels['sidebar_more'] = 'Daugiau...';

// event invitation
$labels['itip_title_request'] = 'Esate kviečiami į renginį.';
$labels['itip_title_modified'] = 'Šis įvykis buvo pakeistas.';
$labels['itip_title_reply_accepted'] = '$u priėmė jūsų kvietimą';
$labels['itip_title_reply_tentative'] = '$u preliminariai priėmė jūsų kvietimą';
$labels['itip_title_reply_declined'] = '$u atsisakė jūsų kvietimo';
$labels['itip_title_cancel'] = 'Šis renginys atšauktas.';

$labels['itip_email_subject_request'] = 'Kvietimas:';
$labels['itip_email_subject_request_modified'] = 'Įvykis pakeistas:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Atšaukta:';

$labels['itip_popup_event_added'] = 'Į kalendorių įtrauktas įvykis.';
$labels['itip_popup_event_updated'] = 'Įvykis atnaujintas kalendoriuje.';
$labels['itip_popup_event_deleted'] = 'Įvykis ištrintas iš kalendoriaus.';
$labels['itip_popup_response_sent'] = 'Organizatoriui išsiųstas atsakymas.';

$labels['itip_message_invitation_accepted'] = 'Jūs priėmėte šį kvietimą.';
$labels['itip_message_invitation_tentative'] = 'Jūs preliminariai priėmėte šį kvietimą.';
$labels['itip_message_invitation_declined'] = 'Jūs atsisakėte šio kvietimo.';
$labels['itip_message_event_deleted'] = 'Įvykis ištrintas iš jūsų kalendoriaus.';
$labels['itip_message_event_updated'] = 'Jūsų kalendoriuje esantis įvykis buvo atnaujintas.';
$labels['itip_message_event_missing'] = 'Šio įvykio nėra nė viename jūsų kalendoriuje.';

$labels['itip_accept'] = 'Priimti';
$labels['itip_tentative'] = 'Preliminarus';
$labels['itip_decline'] = 'Sumažėjimas';
$labels['itip_change_reponse'] = 'Pakeisti atsakymą.';
$labels['itip_add_to_calendar'] = 'Įtraukti į kalendorių';
$labels['itip_reply_to_sender'] = 'Atsakyti siuntėjui';
$labels['itip_dont_import'] = 'Neįtraukti į kalendorių';
$labels['itip_update_in_calendar'] = 'Atnaujinti įvykį kalendoriuje';
$labels['itip_delete_from_calendar'] = 'Ištrinti įvykį iš kalendoriaus';

$labels['mail_subject_invitation'] = 'Kvietimas:';
$labels['mail_subject_update'] = 'Atnaujintas kvietimas:';
$labels['mail_subject_cancel'] = 'Renginys atšauktas:';

// event email notifications
$labels['mail_title'] = 'Pavadinimas:';
$labels['mail_start'] = 'Pradžia:';
$labels['mail_end'] = 'Pabaiga:';
$labels['mail_description'] = 'Aprašymas:';
$labels['mail_location'] = 'Vieta:';

// event attendees
$labels['attendees'] = 'Dalyviai';
$labels['attendee_role'] = 'Vaidmuo';
$labels['attendee_role_required'] = 'Reikalinga';
$labels['attendee_role_optional'] = 'Pasirinktinai';
$labels['attendee_role_chair'] = 'Kėdė';
$labels['attendee_role_none'] = 'Nedalyvaujantys';
$labels['organizer'] = 'Organizatorius';
$labels['attendance'] = 'Dalyvavimas';
$labels['your_status'] = 'Jūsų statusas';
$labels['attendance_going'] = 'Keliaujate?';
$labels['attendee_status_yes'] = 'Taip';
$labels['attendee_status_no'] = 'Ne';
$labels['attendee_status_maybe'] = 'Galbūt';
$labels['attendee_status_delegated'] = 'Deleguota';
$labels['attendee_status_waiting'] = 'Laukimas';
$labels['attendee_status_waiting_for_confirmation'] = 'Laukiama patvirtinimo';
$labels['attendee_status_info_yes'] = 'Dalyvaus';
$labels['attendee_status_info_no'] = 'Nedalyvaus';
$labels['attendee_status_info_maybe'] = 'Gali dalyvauti';
$labels['attendee_status_info_delegated'] = 'Deleguotas dalyvavimas';
$labels['attendee_role'] = 'Vaidmuo';
$labels['attendee_role_info'] = 'Nurodykite, ar dalyvis privalo dalyvauti posėdyje, ar jam pirmininkauti.';
$labels['attendee_notify'] = 'Praneškite';
$labels['attendee_notify_info'] = 'Jei pažymėta, dalyviui bus išsiųstas kvietimo el. laiškas.';
$labels['add_attendee'] = 'Pridėti dalyvį';
$labels['attendee_notify_question'] = 'Siųsti pranešimų el. laiškus dalyviams?';
$labels['attendee_notify_textarea'] = 'Pridėti neprivalomą pranešimą';
$labels['attendee_notify_send'] = 'Siųsti';
$labels['attendee_notify_dont'] = 'Nesiųskite';
$labels['attendee_notify_cancel'] = 'Atšaukti';
$labels['new_attendee_not_saved_warning'] = 'Įvedėte dalyvio el. pašto adresą, bet jo neįtraukėte į dalyvių sąrašą. Ar norite tęsti išsaugojimą be šio dalyvio?';

// import / export
$labels['import_export'] = 'Importas / eksportas';
$labels['export_to_file'] = 'Eksportuoti į failą';
$labels['export_info'] = 'Spustelėkite šį mygtuką, jei norite eksportuoti ir atsisiųsti šio kalendoriaus įvykius "iCalendar" (ics) formatu. Jei kalendoriuje yra daug įvykių, eksporto procesas gali užtrukti kelias akimirkas.';
$labels['export_no_events'] = 'Šiame kalendoriuje nėra eksportuojamų įvykių.';
$labels['import_from_file'] = 'Importas iš failo';
$labels['import_info'] = 'Spustelėkite šį mygtuką, norėdami importuoti įvykius į šį kalendorių iš "iCalendar" (ics) failo.';

// email notifications
$labels['appointment_reminder'] = 'Priminimas apie paskyrimą';
$labels['email_notification_subject'] = 'Priminimas: $t prie $d ($z)';
$labels['email_notification_cron_error'] = 'Pranešimai el. paštu yra išjungti. Kreipkitės į administratorių.';
$labels['alarm_before_event_starts'] = 'Prieš prasidedant renginiui';
$labels['alarm_after_event_starts'] = 'Prasidėjus renginiui';
$labels['alarm_before_event_ends'] = 'Prieš renginio pabaigą';
$labels['alarm_after_event_ends'] = 'Pasibaigus renginiui';
$labels['alarm_at_specific_time'] = 'Tam tikru metu';

// search
$labels['specify_at_least_one_calendar'] = 'Nurodykite bent vieną ieškomą kalendorių.';
$labels['specify_text_to_search'] = 'Įveskite tekstą, kurio norite ieškoti.';
$labels['clear_search_results'] = 'Skaidrus';
$labels['search_outdated_message'] = 'Paieškos rezultatai gali būti pasenę. Apsvarstykite galimybę pakartoti paiešką.';
$labels['date_range'] = 'Datų intervalas';
$labels['date_range_subtitle'] = 'Datų intervalas:';
$labels['search_results'] = 'Rasti rezultatai: $n';
$labels['modify_search_options'] = 'Pabandykite pakeisti paieškos parinktis.';
$labels['show_shortcuts'] = 'Rodyti sparčiuosius klavišus';
$labels['hide_shortcuts'] = 'Paslėpti sparčiuosius klavišus';
$labels['this_month'] = 'Šį mėnesį';
$labels['this_year'] = 'Šiais metais';
$labels['past_30_days'] = 'Praėjusios 30 dienų';
$labels['next_30_days'] = 'Artimiausios 30 dienų';
$labels['past_6_months'] = 'Pastarieji 6 mėnesiai';
$labels['next_6_months'] = 'Artimiausi 6 mėnesiai';

// calendar grid
$labels['cannot_load_events'] = 'Negalima įkelti kalendoriaus "$c" įvykių.';
