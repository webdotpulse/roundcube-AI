<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Koledar';
$labels['app_menu_title'] = 'Koledar';

// menu
$labels['create_event'] = 'Ustvarjanje dogodka';
$labels['create_event_button'] = 'Nov dogodek';
$labels['day'] = 'Dan';
$labels['week'] = 'Teden';
$labels['month'] = 'Mesec';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Koledarji';
$labels['my_calendar'] = 'Moj koledar';
$labels['create_calendar'] = 'Ustvarjanje koledarja';
$labels['new_calendar'] = 'Nov koledar';
$labels['edit_calendar'] = 'Urejanje koledarja';
$labels['remove_calendar'] = 'Brisanje koledarja';
$labels['confirm_remove_calendar'] = 'Ste prepričani, da želite izbrisati ta koledar?';
$labels['calendar_removed'] = 'Koledar je izbrisan.';
$labels['notifications'] = 'Obvestila';
$labels['default_event_visibility'] = 'Privzeta vidnost dogodka';
$labels['time_zone'] = 'Časovni pas koledarja';
$labels['share_calendar'] = 'Koledar deljenja';
$labels['permissions'] = 'Dovoljenja';
$labels['permission_publish_calendar'] = 'Objava koledarja';
$labels['permission_share_calendar'] = 'Koledar deljenja';
$labels['permission_edit_events'] = 'Urejanje dogodkov';
$labels['permission_see_details'] = 'Poglej podrobnosti';
$labels['permission_publish_calendar_info'] = 'Če je označeno, bo uporabnik lahko objavil koledar.';
$labels['permission_share_calendar_info'] = 'Če je označeno, bo uporabnik lahko koledar delil z drugimi uporabniki.';
$labels['permission_edit_events_info'] = 'Če je označeno, bo uporabnik lahko urejal in brisal dogodke koledarja.';
$labels['permission_see_details_info'] = 'Če je označeno, bodo vse podrobnosti o dogodku v skupni rabi. Če ta možnost ni označena, bodo v skupni rabi samo razpoložljivi/zaposleni podatki.';
$labels['share_send_notification'] = 'Obvestilo';
$labels['share_send_notification_info'] = 'Če je označeno, bo uporabniku poslano e-poštno sporočilo, v katerem bo obveščen o novem koledarju v skupni rabi.';
$labels['new_share_not_saved_warning'] = 'Vnesli ste e-poštno sporočilo, s katerim želite deliti koledar, vendar niste kliknili gumba Share, da bi ga dodali na seznam v skupni rabi. Še naprej shranjujete, ne da bi koledar delili z navedenim uporabnikom?';
$labels['share'] = 'Delite';
$labels['shared_marker'] = '(v skupni rabi)';
$labels['owner'] = 'Lastnik';
$labels['publish_calendar'] = 'Objava koledarja';
$labels['create_link'] = 'Ustvari povezavo';
$labels['reset'] = 'Ponastavitev';
$labels['remove'] = 'Odstranite';
$labels['publish_full'] = 'Objavite vse informacije o dogodku.';
$labels['publish_busy'] = 'Objavite samo informacije o prostih/zaposlenih.';
$labels['sync_no_id'] = 'Možnosti CalDAV bodo na voljo, ko shranite koledar.';
$labels['share_no_id'] = 'Možnosti souporabe koledarja bodo na voljo, ko shranite koledar.';
$labels['publish_no_id'] = 'Možnosti objave koledarja bodo na voljo, ko koledar shranite.';
$labels['import_no_id'] = 'Možnosti uvoza in izvoza bodo na voljo, ko shranite koledar.';
$labels['unable_to_save_calendar_data'] = 'Ni mogoče shraniti koledarskih podatkov.';
$labels['unable_to_retrieve_calendar_data'] = 'Ni mogoče pridobiti podatkov koledarja. Koledar morda ni več na voljo.';
$labels['unable_add_shared_calendar'] = 'Ni mogoče dodati koledarja v skupni rabi. Koledar morda ni več na voljo.';
$labels['itip_organizer_message'] = 'Na ta dogodek ste bili povabljeni, vendar niste organizator. Spremembe, ki jih vnesete v ta dogodek, ne bodo poslane drugim udeležencem.';
$labels['cannot_share_with_yourself'] = 'Lastnega koledarja ne morete deliti sami s seboj.';
$labels['cannot_share_with_calendar_owner'] = 'Tega koledarja ne morete deliti z lastnikom koledarja ($e)';
$labels['setting_default_notification_type'] = 'Privzeta obvestila za nove dogodke';
$labels['calendar_name_cannot_be_empty'] = 'Ime koledarja ne sme biti prazno.';

// caldav server
$labels['sync_calendar'] = 'Sinhronizacija koledarja';
$labels['password_too_short'] = 'Geslo mora biti dolgo vsaj 10 znakov.';
$labels['read_only'] = 'Samo za branje';
$labels['add_connection'] = 'Dodajanje povezave';
$labels['show_details'] = 'Prikaži podrobnosti';
$labels['hide_details'] = 'Skrij podrobnosti';
$labels['caldav_connection_password'] = 'Geslo, ki ste ga določili pri ustvarjanju te povezave.';
$labels['yes'] = 'Da';
$labels['no'] = 'Ne';
$labels['confirm_remove_sync'] = 'Ali ste prepričani, da želite trajno odstraniti to povezavo?';
$labels['remove_sync_error'] = 'Pri odstranjevanju povezave je prišlo do napake.';
$labels['create_new_caldav_connection'] = 'Ustvarite novo povezavo CalDav';
$labels['your_caldav_connections'] = 'Povezave CalDav';
$labels['created_at'] = 'Ustvarjeno';
$labels['connected_at'] = 'Zadnja sinhronizacija';
$labels['never'] = 'Nikoli';
$labels['alternate_url'] = 'Nadomestni naslov URL';
$labels['caldav_password_info'] = 'Zaradi varnostnih razlogov to geslo ni shranjeno v strežniku. Če ste ga pozabili, izbrišite to povezavo in ustvarite novo.';
$labels['caldav_url_info'] = 'Ta URL uporabite z aplikacijami, ki podpirajo odkrivanje koledarja, na primer: Koledar iOS in Mac OS, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Ta URL uporabite za aplikacije, ki ne delujejo z zgornjim kratkim URL-jem, na primer: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'Neveljaven naslov strežnika CalDAV.';
$labels['cannot_resolve_caldav_host'] = 'Gostitelja strežnika CalDAV ni mogoče najti.';
$labels['private_caldav_addresses_not_allowed'] = 'Zasebni in rezervirani naslovi CalDAV niso dovoljeni.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Dodajanje koledarja v skupni rabi';
$labels['shared_calendars'] = 'Skupni koledarji';
$labels['no_shared_calendars'] = 'Skupnih koledarjev ne morete dodati.';
$labels['add_to_my_calendars'] = 'Dodaj v moje koledarje';
$labels['remove_from_my_calendars'] = 'Odstrani iz mojih koledarjev';
$labels['unshare'] = 'Odstranitev souporabe';
$labels['unshare_warning'] = 'Če ta koledar odstranite iz souporabe, bo izginil s tega seznama in ga ne boste mogli znova dodati.';
$labels['calendar_name'] = 'Ime koledarja';
$labels['calendar_owner'] = 'Lastnik koledarja';
$labels['shared_date'] = 'Skupna spletna stran';
$labels['unable_to_share'] = 'Koledarja ni mogoče deliti z naslednjimi uporabniki, ker se njihovi e-poštni računi nahajajo v oddaljenem strežniku:';

// shared calendar email
$labels['shared_email_subject'] = '$u je z vami delil koledar';
$labels['shared_email_body'] = 'Uporabnik $u vam je dal dostop do koledarja z imenom "$c". Če želite koledar dodati na seznam aktivnih koledarjev, kliknite spodnjo povezavo:';
$labels['shared_email_body_cpanel'] = 'Uporabnik $u vam je omogočil dostop do koledarja z imenom "$c". Če želite ta koledar dodati na seznam aktivnih koledarjev, se prijavite v Roundcube, pojdite na Koledar, kliknite ikono plusa v stranski vrstici in izberite Dodaj koledar v skupni rabi.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Praznični koledarji';
$labels['edit_holiday_calendar'] = 'Urejanje počitniškega koledarja';
$labels['religious_holidays'] = 'Verski prazniki';
$labels['country_holidays'] = 'Počitnice na podeželju';
$labels['add'] = 'Dodaj';
$labels['no_more_holidays_allowed'] = 'Ne morete več dodajati prazničnih koledarjev.';
$labels['cannot_load_holidays'] = 'Ni mogoče naložiti praznikov iz Googlovega strežnika.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Dodaj koledar rojstnega dne';
$labels['edit_birthday_calendar'] = 'Urejanje koledarja za rojstni dan';
$labels['contact_birthdays'] = 'Kontakt Rojstni dnevi';
$labels['contact_birthdays_info'] = 'Ta koledar iz seznama stikov pridobi podatke o rojstnih dnevih in jih prikaže kot dogodke v koledarju.';
$labels['birthday_event_title'] = 'Rojstni dan: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Dodajanje koledarja Google';
$labels['edit_google_calendar'] = 'Urejanje koledarja Google';
$labels['google_id'] = 'ID koledarja Google';
$labels['new_google_calendar'] = 'Novi Googlov koledar';
$labels['specify_google_calendar_id'] = 'Vnesite ID koledarja Google';
$labels['calendar_id_format_error'] = 'ID koledarja Google mora vsebovati znak @.';
$labels['cannot_load_google_calendar'] = 'Googlovega koledarja "$c" ni mogoče naložiti. Prepričajte se, da je ID koledarja pravilen in da je koledar na voljo javnosti.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Dodajanje koledarja CalDAV';
$labels['edit_caldav_calendar'] = 'Urejanje koledarja CalDAV';
$labels['add_caldav_calendar_information'] = 'Če se želite naročiti na koledar CalDAV, določite podatke o strežniku CalDAV in kliknite Poišči koledarje.';
$labels['caldav_url'] = 'URL CalDAV';
$labels['find_calendars'] = 'Poiščite koledarje';
$labels['searching_for_calendars'] = 'Iščete koledarje, počakajte...';
$labels['select_calendars'] = 'Izberite koledarje, ki jih želite dodati:';
$labels['read_only_status_yes'] = 'Da';
$labels['read_only_status_no'] = 'Ne';
$labels['subscribed'] = 'Naročeno';

$labels['refresh_calendar'] = 'Osvežitev koledarja';
$labels['refresh_every_minute'] = 'Vsako minuto';
$labels['refresh_every_5_minutes'] = 'Vsakih 5 minut';
$labels['refresh_every_15_minutes'] = 'Vsakih 15 minut';
$labels['refresh_every_30_minutes'] = 'Vsakih 30 minut';
$labels['refresh_every_60_minutes'] = 'Vsakih 60 minut';
$labels['refresh_never'] = 'Nikoli';

$labels['cannot_connect_to_caldav_server'] = 'Ne morete se povezati s strežnikom CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Ni se mogoče povezati s strežnikom CalDAV. Preverite URL strežnika.';
$labels['caldav_client_error_credentials'] = 'Nepravilno uporabniško ime ali geslo CalDAV.';
$labels['caldav_client_error_empty'] = 'Strežnik ni vrnil nobenega koledarja, ki bi ga bilo mogoče dodati. Preverite naslov URL CalDAV.';
$labels['caldav_client_error_server_info'] = 'Nepravilne informacije o strežniku CalDAV.';
$labels['caldav_client_error_add_calendar'] = 'Ni mogoče shraniti koledarja "$n".';
$labels['caldav_client_error_delete_event'] = 'Dogodka ni mogoče izbrisati';
$labels['caldav_client_error_save_event'] = 'Dogodka CalDAV ni mogoče shraniti: morda je bil premaknjen, izbrisan ali pa koledar ni na voljo.';
$labels['caldav_client_error_calendar_added'] = 'Koledar "$n" je že na vašem seznamu koledarjev.';

// previewing event
$labels['remove_recurring_event'] = 'Brisanje ponavljajočega se dogodka';
$labels['remove_one'] = 'Izbriši samo ta primerek';
$labels['remove_all'] = 'Izbriši vse primere';
$labels['edit_event'] = 'Urejanje dogodka';
$labels['event_removed'] = 'Dogodek je bil izbrisan.';
$labels['go_to_url'] = 'Pojdi na URL';
$labels['confirm_remove_event'] = 'Ste prepričani, da želite izbrisati ta dogodek?';

// calendar/agenda grid
$labels['no_events'] = 'Ni dogodkov, ki bi jih bilo treba prikazati.';

// importing events
$labels['select_calendar'] = 'Izberite koledar';
$labels['select_calendar_for_events'] = 'Izberite koledar, v katerega želite dodati dogodke:';
$labels['add_events'] = 'Dodajanje dogodkov';
$labels['add_events_to_calendar'] = 'Dodajanje dogodkov v koledar';
$labels['events_imported_successfully'] = 'Uspešno uvoženi dogodki';
$labels['events_imported_with_errors'] = 'Nekega drugega dogodka ni bilo mogoče uvoziti.';
$labels['error_importing_events'] = 'Pri uvozu dogodkov v koledar je prišlo do napake';

// editing events
$labels['calendar'] = 'Koledar';
$labels['new_event'] = 'Nov dogodek';
$labels['summary'] = 'Povzetek';
$labels['recurrence'] = 'Ponovni pojav';
$labels['title'] = 'Naslov';
$labels['location'] = 'Lokacija';
$labels['description'] = 'Opis';
$labels['comment'] = 'Komentar:';
$labels['url'] = 'URL';
$labels['name'] = 'Ime';
$labels['start'] = 'Začetek';
$labels['end'] = 'Konec';
$labels['show_timezones'] = 'Prikaži časovne pasove';
$labels['reset_timezones'] = 'Ponastavitev privzetih časovnih območij';
$labels['all_day'] = 'Ves dan';
$labels['use_calendar_colors'] = 'Uporaba barv koledarja';
$labels['color'] = 'Barva';
$labels['background'] = 'Ozadje';
$labels['text'] = 'Besedilo';
$labels['show_me_as'] = 'Pokaži mi kot';
$labels['available'] = 'Na voljo';
$labels['busy'] = 'Zaposleni';
$labels['visibility'] = 'Vidljivost';
$labels['calendar_default'] = 'Privzeto za koledar';
$labels['public'] = 'Javna stran';
$labels['private'] = 'Zasebno';
$labels['repeat'] = 'Ponovite';
$labels['repeat_every'] = 'Ponovite vsak';
$labels['never'] = 'Nikoli';
$labels['daily'] = 'Dnevno';
$labels['weekly'] = 'Tedensko';
$labels['monthly'] = 'Mesečno';
$labels['yearly'] = 'Letno';
$labels['days'] = 'dan(i)';
$labels['weeks'] = 'teden(i)';
$labels['months'] = 'mesec(i)';
$labels['years'] = 'leto(a)';
$labels['repeat_on'] = 'Ponovite na';
$labels['repeat_by'] = 'Ponovite z';
$labels['day_of_month'] = 'dan v mesecu';
$labels['day_of_week'] = 'dan v tednu';
$labels['not_specified'] = 'Ni določeno';
$labels['first'] = 'Prvi';
$labels['second'] = 'Drugi';
$labels['third'] = 'Tretji';
$labels['fourth'] = 'Četrti';
$labels['fifth'] = 'Peti';
$labels['second_to_last'] = 'Predzadnji';
$labels['last'] = 'Zadnji';
$labels['attachment_too_large'] = 'Datoteka priponke je prevelika. Največja dovoljena velikost je $s.';
$labels['bg_color'] = 'Barva ozadja';
$labels['tx_color'] = 'Barva besedila';
$labels['add_notification'] = 'Dodajanje obvestila';
$labels['popup'] = 'Izskočno okno';
$labels['no_notifications'] = 'Ta dogodek nima nobenih obvestil.';
$labels['minutes'] = 'minute';
$labels['hours'] = 'ure';
$labels['link'] = 'Povezava';
$labels['attach_file_from_web'] = 'Priložite datoteko';
$labels['attachment_url'] = 'URL naslov priloge';
$labels['invalid_url'] = 'Ta URL je neveljaven.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Podvoji';
$labels['duplicate_suffix'] = '(kopija)';
$labels['no_notifications'] = 'Ta dogodek nima nobenih obvestil.';
$labels['this_calendar_is_hidden'] = 'Ta koledar je skrit. Shranjeni dogodki se ne bodo prikazali, dokler koledar ne bo viden.';
$labels['make_calendar_visible'] = 'Nastavi ta koledar kot viden.';

// recurrence
$labels['recurrence_range'] = 'Območje ponovitve';
$labels['repeat_forever'] = 'Ponovite za vedno';
$labels['repeat_every_weekday'] = 'Ponovite vsak delovni dan';
$labels['last_day'] = 'Zadnji dan';
$labels['every'] = 'Vsak';
$labels['weekday'] = 'Dnevni program ob delavnikih';
$labels['weekend_day'] = 'Dan ob koncu tedna';
$labels['day_of_week'] = 'dan v tednu';
$labels['day_of_month'] = 'dan v mesecu';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Konec po';
$labels['occurrences'] = 'dogodki';

// end recurrence on date:
$labels['end_by_date'] = 'Konec do datuma';
$labels['removed_events_title'] = 'Dogodki, odstranjeni iz ponavljajočih se serij';
$labels['restore'] = 'Obnovitev';

// settings page
$labels['setting_view'] = 'Začetni pogled koledarja';
$labels['setting_first_day'] = 'Teden se začne na';
$labels['setting_agenda_week_span'] = 'Časovni razpon dnevnega reda (v tednih)';
$labels['setting_slot_duration'] = 'Časovni interval dneva (v minutah)';
$labels['setting_scroll_time'] = 'Pomaknite se na čas';
$labels['setting_calendar'] = 'Privzet koledar za dogodke';
$labels['setting_refresh'] = 'Osvežitev koledarjev';
$labels['setting_week_numbers'] = 'Številke tednov v malem koledarju';
$labels['setting_event_border'] = 'Barve kategorije kot meje dogodka';
$labels['show_sunrise'] = 'Prikaži sončni vzhod v pogledu meseca';
$labels['show_sunset'] = 'Prikaži sončni zahod v pogledu meseca';
$labels['categories'] = 'Kategorije';
$labels['category'] = 'Kategorija';
$labels['no_category'] = 'Brez kategorije';
$labels['add_category'] = 'Dodajanje kategorije';
$labels['setting_alarm_sound'] = 'Zvok obvestila';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambus';
$labels['alarm_sound_bells'] = 'Zvonovi';
$labels['alarm_sound_chord'] = 'Akord';
$labels['alarm_sound_circles'] = 'Krogi';
$labels['alarm_sound_glass'] = 'Steklo';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Kovina';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Zvezdne steze';
$labels['alarm_sound_whistle'] = 'Piščalka';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Predvajanje zvoka';

// alarms
$labels['upcoming_events'] = 'Prihajajoči dogodki';
$labels['dismiss'] = 'Zavrnite';
$labels['dismiss_all'] = 'Zavrnite vse';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minut';
$labels['10_minutes'] = '10 minut';
$labels['20_minutes'] = '20 minut';
$labels['30_minutes'] = '30 minut';
$labels['1_hour'] = '1 ura';
$labels['2_hours'] = '2 uri';
$labels['tomorrow'] = 'Do jutri';
$labels['next_week'] = 'Do naslednjega tedna';

// sidebar
$labels['setting_show_xcalendar'] = 'Prikaži koledar Agenda';
$labels['todays_agenda'] = 'Današnji dnevni red';
$labels['no_events_today'] = 'Za danes ni načrtovanih dogodkov.';
$labels['sidebar_more'] = 'Več...';

// event invitation
$labels['itip_title_request'] = 'Povabljeni ste na dogodek.';
$labels['itip_title_modified'] = 'Ta dogodek je bil spremenjen.';
$labels['itip_title_reply_accepted'] = '$u je sprejel vaše povabilo';
$labels['itip_title_reply_tentative'] = '$u je začasno sprejel vaše povabilo';
$labels['itip_title_reply_declined'] = '$u je zavrnil vaše povabilo';
$labels['itip_title_cancel'] = 'Ta dogodek je bil odpovedan.';

$labels['itip_email_subject_request'] = 'Vabilo:';
$labels['itip_email_subject_request_modified'] = 'Dogodek je bil spremenjen:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Odpovedano:';

$labels['itip_popup_event_added'] = 'Dogodek dodan v koledar.';
$labels['itip_popup_event_updated'] = 'Dogodek je posodobljen v koledarju.';
$labels['itip_popup_event_deleted'] = 'Dogodek je izbrisan iz koledarja.';
$labels['itip_popup_response_sent'] = 'Odgovor, poslan organizatorju.';

$labels['itip_message_invitation_accepted'] = 'Sprejeli ste to povabilo.';
$labels['itip_message_invitation_tentative'] = 'Vabilo ste začasno sprejeli.';
$labels['itip_message_invitation_declined'] = 'Povabilo ste zavrnili.';
$labels['itip_message_event_deleted'] = 'Dogodek je bil izbrisan iz vašega koledarja.';
$labels['itip_message_event_updated'] = 'Dogodek v vašem koledarju je bil posodobljen.';
$labels['itip_message_event_missing'] = 'Ta dogodek ni v nobenem od vaših koledarjev.';

$labels['itip_accept'] = 'Sprejmite';
$labels['itip_tentative'] = 'Okvirna spletna stran';
$labels['itip_decline'] = 'Upadanje';
$labels['itip_change_reponse'] = 'Odziv na spremembo.';
$labels['itip_add_to_calendar'] = 'Dodaj v koledar';
$labels['itip_reply_to_sender'] = 'Odgovori pošiljatelju';
$labels['itip_dont_import'] = 'Ne dodajte v koledar';
$labels['itip_update_in_calendar'] = 'Posodobitev dogodka v koledarju';
$labels['itip_delete_from_calendar'] = 'Brisanje dogodka iz koledarja';

$labels['mail_subject_invitation'] = 'Vabilo:';
$labels['mail_subject_update'] = 'Posodobljeno povabilo:';
$labels['mail_subject_cancel'] = 'Dogodek odpovedan:';

// event email notifications
$labels['mail_title'] = 'Naslov:';
$labels['mail_start'] = 'Začetek:';
$labels['mail_end'] = 'Konec:';
$labels['mail_description'] = 'Opis:';
$labels['mail_location'] = 'Lokacija:';

// event attendees
$labels['attendees'] = 'Udeleženci';
$labels['attendee_role'] = 'Vloga';
$labels['attendee_role_required'] = 'Zahtevano';
$labels['attendee_role_optional'] = 'Izbirno';
$labels['attendee_role_chair'] = 'Stol';
$labels['attendee_role_none'] = 'Neudeleženci';
$labels['organizer'] = 'Organizator';
$labels['attendance'] = 'Obisk';
$labels['your_status'] = 'Vaš status';
$labels['attendance_going'] = 'Greš?';
$labels['attendee_status_yes'] = 'Da';
$labels['attendee_status_no'] = 'Ne';
$labels['attendee_status_maybe'] = 'Morda';
$labels['attendee_status_delegated'] = 'Delegacija';
$labels['attendee_status_waiting'] = 'Čakanje';
$labels['attendee_status_waiting_for_confirmation'] = 'Čakamo na potrditev';
$labels['attendee_status_info_yes'] = 'Se bo udeležil';
$labels['attendee_status_info_no'] = 'Ne bo se udeležil';
$labels['attendee_status_info_maybe'] = 'Morda se bo udeležil';
$labels['attendee_status_info_delegated'] = 'Delegirana udeležba';
$labels['attendee_role'] = 'Vloga';
$labels['attendee_role_info'] = 'Navedite, ali se mora udeleženec udeležiti sestanka ali mu predsedovati.';
$labels['attendee_notify'] = 'Obvestilo';
$labels['attendee_notify_info'] = 'Če je označeno, bo udeležencu poslano e-poštno sporočilo z vabilom.';
$labels['add_attendee'] = 'Dodajte udeleženca';
$labels['attendee_notify_question'] = 'Pošiljanje e-poštnih sporočil z obvestili udeležencem?';
$labels['attendee_notify_textarea'] = 'Dodajte neobvezno sporočilo';
$labels['attendee_notify_send'] = 'Pošlji';
$labels['attendee_notify_dont'] = 'Ne pošiljajte';
$labels['attendee_notify_cancel'] = 'Prekliči';
$labels['new_attendee_not_saved_warning'] = 'Vnesli ste e-poštni naslov udeleženca, vendar ga niste dodali na seznam udeležencev. Želite nadaljevati s shranjevanjem brez tega udeleženca?';

// import / export
$labels['import_export'] = 'Uvoz/izvoz';
$labels['export_to_file'] = 'Izvoz v datoteko';
$labels['export_info'] = 'Kliknite ta gumb, če želite izvoziti in prenesti dogodke tega koledarja v obliki iCalendar (ics). Če koledar vsebuje veliko dogodkov, lahko postopek izvoza traja nekaj trenutkov.';
$labels['export_no_events'] = 'Ta koledar nima nobenih dogodkov za izvoz.';
$labels['import_from_file'] = 'Uvoz iz datoteke';
$labels['import_info'] = 'Kliknite ta gumb, če želite v ta koledar uvoziti dogodke iz datoteke iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Opomnik za imenovanje';
$labels['email_notification_subject'] = 'Opomnik: $t na $d ($z)';
$labels['email_notification_cron_error'] = 'E-poštna obvestila so onemogočena. Obrnite se na skrbnika.';
$labels['alarm_before_event_starts'] = 'Pred začetkom dogodka';
$labels['alarm_after_event_starts'] = 'Po začetku dogodka';
$labels['alarm_before_event_ends'] = 'Pred koncem dogodka';
$labels['alarm_after_event_ends'] = 'Po koncu dogodka';
$labels['alarm_at_specific_time'] = 'Ob določenem času';

// search
$labels['specify_at_least_one_calendar'] = 'Navedite vsaj en koledar za iskanje.';
$labels['specify_text_to_search'] = 'Vnesite besedilo, ki ga želite poiskati.';
$labels['clear_search_results'] = 'Jasno';
$labels['search_outdated_message'] = 'Rezultati iskanja so lahko zastareli. Razmislite o ponovnem iskanju.';
$labels['date_range'] = 'Razpon datumov';
$labels['date_range_subtitle'] = 'Razpon datumov:';
$labels['search_results'] = 'Najdeni rezultati: $n';
$labels['modify_search_options'] = 'Poskusite spremeniti možnosti iskanja.';
$labels['show_shortcuts'] = 'Prikaži bližnjice';
$labels['hide_shortcuts'] = 'Skrijte bližnjice';
$labels['this_month'] = 'Ta mesec';
$labels['this_year'] = 'Letos';
$labels['past_30_days'] = 'Zadnjih 30 dni';
$labels['next_30_days'] = 'Naslednjih 30 dni';
$labels['past_6_months'] = 'Zadnjih 6 mesecev';
$labels['next_6_months'] = 'Naslednjih 6 mesecev';

// calendar grid
$labels['cannot_load_events'] = 'Ni mogoče naložiti dogodkov za koledar "$c".';
