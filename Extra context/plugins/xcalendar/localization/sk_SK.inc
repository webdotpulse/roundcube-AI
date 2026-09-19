<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Kalendár';
$labels['app_menu_title'] = 'Kalendár';

// menu
$labels['create_event'] = 'Vytvoriť udalosť';
$labels['create_event_button'] = 'Nová udalosť';
$labels['day'] = 'Deň';
$labels['week'] = 'Týždeň';
$labels['month'] = 'Mesiac';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Kalendáre';
$labels['my_calendar'] = 'Môj kalendár';
$labels['create_calendar'] = 'Vytvoriť kalendár';
$labels['new_calendar'] = 'Nový kalendár';
$labels['edit_calendar'] = 'Úprava kalendára';
$labels['remove_calendar'] = 'Odstránenie kalendára';
$labels['confirm_remove_calendar'] = 'Ste si istí, že chcete tento kalendár vymazať?';
$labels['calendar_removed'] = 'Kalendár vymazaný.';
$labels['notifications'] = 'Oznámenia';
$labels['default_event_visibility'] = 'Predvolená viditeľnosť udalosti';
$labels['time_zone'] = 'Časové pásmo kalendára';
$labels['share_calendar'] = 'Zdieľať kalendár';
$labels['permissions'] = 'Oprávnenia';
$labels['permission_publish_calendar'] = 'Uverejnenie kalendára';
$labels['permission_share_calendar'] = 'Zdieľať kalendár';
$labels['permission_edit_events'] = 'Úprava udalostí';
$labels['permission_see_details'] = 'Zobraziť podrobnosti';
$labels['permission_publish_calendar_info'] = 'Ak je začiarknuté, používateľ bude môcť kalendár publikovať.';
$labels['permission_share_calendar_info'] = 'Ak je začiarknuté, používateľ bude môcť zdieľať kalendár s ostatnými používateľmi.';
$labels['permission_edit_events_info'] = 'Ak je začiarknuté, používateľ bude môcť upravovať a odstraňovať udalosti kalendára.';
$labels['permission_see_details_info'] = 'Ak je začiarknuté, budú sa zdieľať všetky podrobnosti o udalosti. Ak nie je začiarknuté, zdieľajú sa len dostupné/obsadené informácie.';
$labels['share_send_notification'] = 'Upozornenie';
$labels['share_send_notification_info'] = 'Ak je začiarknuté, používateľovi sa odošle e-mail s informáciou o novom zdieľanom kalendári.';
$labels['new_share_not_saved_warning'] = 'Zadali ste e-mailovú adresu na zdieľanie kalendára, ale neklikli ste na tlačidlo Zdieľať, aby ste ho pridali do zdieľaného zoznamu. Chcete pokračovať v ukladaní bez zdieľania kalendára s týmto používateľom?';
$labels['share'] = 'Zdieľať';
$labels['shared_marker'] = '(Zdieľané)';
$labels['owner'] = 'Vlastník';
$labels['publish_calendar'] = 'Uverejnenie kalendára';
$labels['create_link'] = 'Vytvoriť prepojenie';
$labels['reset'] = 'Obnovenie';
$labels['remove'] = 'Odstránenie stránky';
$labels['publish_full'] = 'Uverejnite všetky informácie o podujatí.';
$labels['publish_busy'] = 'Zverejňujte len informácie o voľných/zaťažených miestach.';
$labels['sync_no_id'] = 'Možnosti CalDAV budú k dispozícii po uložení kalendára.';
$labels['share_no_id'] = 'Možnosti zdieľania kalendára budú k dispozícii po uložení kalendára.';
$labels['publish_no_id'] = 'Možnosti publikovania kalendára budú k dispozícii po uložení kalendára.';
$labels['import_no_id'] = 'Možnosti importu a exportu budú k dispozícii po uložení kalendára.';
$labels['unable_to_save_calendar_data'] = 'Nie je možné uložiť údaje kalendára.';
$labels['unable_to_retrieve_calendar_data'] = 'Nie je možné načítať údaje kalendára. Kalendár už nemusí byť k dispozícii.';
$labels['unable_add_shared_calendar'] = 'Nie je možné pridať zdieľaný kalendár. Kalendár už nemusí byť k dispozícii.';
$labels['itip_organizer_message'] = 'Na toto podujatie ste boli pozvaní, ale nie ste jeho organizátor. Zmeny, ktoré vykonáte v tejto udalosti, nebudú odoslané ostatným účastníkom.';
$labels['cannot_share_with_yourself'] = 'Vlastný kalendár nemôžete zdieľať sami so sebou.';
$labels['cannot_share_with_calendar_owner'] = 'Tento kalendár nemôžete zdieľať s vlastníkom kalendára ($e)';
$labels['setting_default_notification_type'] = 'Predvolené upozornenia na nové udalosti';
$labels['calendar_name_cannot_be_empty'] = 'Názov kalendára nemôže byť prázdny.';

// caldav server
$labels['sync_calendar'] = 'Synchronizácia kalendára';
$labels['password_too_short'] = 'Heslo by malo mať aspoň 10 znakov.';
$labels['read_only'] = 'Len na čítanie';
$labels['add_connection'] = 'Pridanie pripojenia';
$labels['show_details'] = 'Zobraziť podrobnosti';
$labels['hide_details'] = 'Skryť podrobnosti';
$labels['caldav_connection_password'] = 'Heslo, ktoré ste zadali pri vytváraní tohto pripojenia.';
$labels['yes'] = 'Áno';
$labels['no'] = 'Nie';
$labels['confirm_remove_sync'] = 'Ste si istí, že chcete toto pripojenie natrvalo odstrániť?';
$labels['remove_sync_error'] = 'Pri odstraňovaní pripojenia došlo k chybe.';
$labels['create_new_caldav_connection'] = 'Vytvorenie nového pripojenia CalDav';
$labels['your_caldav_connections'] = 'Vaše pripojenia CalDav';
$labels['created_at'] = 'Vytvorené';
$labels['connected_at'] = 'Posledná synchronizácia';
$labels['never'] = 'Nikdy';
$labels['alternate_url'] = 'Alternatívna adresa URL';
$labels['caldav_password_info'] = 'Z bezpečnostných dôvodov sa toto heslo na serveri neukladá. Ak ste ho zabudli, odstráňte toto pripojenie a vytvorte nové.';
$labels['caldav_url_info'] = 'Túto adresu URL používajte s aplikáciami, ktoré podporujú zisťovanie kalendára, napríklad: Kalendár iOS a Mac OS, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Túto adresu URL použite napríklad pri aplikáciách, ktoré nebudú fungovať s vyššie uvedenou krátkou adresou url: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'Neplatná adresa URL servera CalDAV.';
$labels['cannot_resolve_caldav_host'] = 'Nepodarilo sa zistiť hostiteľa servera CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'Súkromné a vyhradené adresy CalDAV nie sú povolené.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Pridanie zdieľaného kalendára';
$labels['shared_calendars'] = 'Zdieľané kalendáre';
$labels['no_shared_calendars'] = 'Nie sú k dispozícii žiadne zdieľané kalendáre, ktoré by ste mohli pridať.';
$labels['add_to_my_calendars'] = 'Pridať do mojich kalendárov';
$labels['remove_from_my_calendars'] = 'Odstrániť z mojich kalendárov';
$labels['unshare'] = 'Zrušenie zdieľania';
$labels['unshare_warning'] = 'Ak zrušíte zdieľanie tohto kalendára, zmizne z tohto zoznamu a nebudete ho môcť znova pridať.';
$labels['calendar_name'] = 'Názov kalendára';
$labels['calendar_owner'] = 'Majiteľ kalendára';
$labels['shared_date'] = 'Spoločná stránka';
$labels['unable_to_share'] = 'Kalendár nemožno zdieľať s nasledujúcimi používateľmi, pretože ich e-mailové kontá sa nachádzajú na vzdialenom serveri:';

// shared calendar email
$labels['shared_email_subject'] = '$u sa s vami podelil o kalendár';
$labels['shared_email_body'] = 'Používateľ $u vám poskytol prístup ku kalendáru s názvom "$c". Ak chcete pridať tento kalendár do zoznamu aktívnych kalendárov, kliknite na odkaz nižšie:';
$labels['shared_email_body_cpanel'] = 'Používateľ $u vám poskytol prístup ku kalendáru s názvom "$c". Ak chcete pridať tento kalendár do zoznamu aktívnych kalendárov, prihláste sa do Roundcube, prejdite na Kalendár, kliknite na ikonu plus na bočnom paneli a vyberte položku Pridať zdieľaný kalendár.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Dovolenkové kalendáre';
$labels['edit_holiday_calendar'] = 'Úprava kalendára sviatkov';
$labels['religious_holidays'] = 'Náboženské sviatky';
$labels['country_holidays'] = 'Dovolenka na vidieku';
$labels['add'] = 'Pridať';
$labels['no_more_holidays_allowed'] = 'Nemôžete pridávať ďalšie kalendáre sviatkov.';
$labels['cannot_load_holidays'] = 'Nie je možné načítať sviatky zo servera Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Pridať kalendár narodenín';
$labels['edit_birthday_calendar'] = 'Úprava kalendára narodenín';
$labels['contact_birthdays'] = 'Kontakt Narodeniny';
$labels['contact_birthdays_info'] = 'Tento kalendár načíta informácie o narodeninách zo zoznamu kontaktov a zobrazí ich ako udalosti kalendára.';
$labels['birthday_event_title'] = 'Narodeniny: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Pridanie kalendára Google';
$labels['edit_google_calendar'] = 'Úprava kalendára Google';
$labels['google_id'] = 'ID kalendára Google';
$labels['new_google_calendar'] = 'Nový kalendár Google';
$labels['specify_google_calendar_id'] = 'Zadajte ID kalendára Google';
$labels['calendar_id_format_error'] = 'ID kalendára Google by malo obsahovať znak @.';
$labels['cannot_load_google_calendar'] = 'Kalendár Google "$c" nemožno načítať. Skontrolujte, či je ID kalendára správne a či bol kalendár sprístupnený verejnosti.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Pridanie kalendára CalDAV';
$labels['edit_caldav_calendar'] = 'Úprava kalendára CalDAV';
$labels['add_caldav_calendar_information'] = 'Ak sa chcete prihlásiť k odberu kalendára CalDAV, zadajte informácie o serveri CalDAV a kliknite na tlačidlo Nájsť kalendáre.';
$labels['caldav_url'] = 'Adresa URL CalDAV';
$labels['find_calendars'] = 'Vyhľadávanie kalendárov';
$labels['searching_for_calendars'] = 'Vyhľadávanie kalendárov, počkajte prosím...';
$labels['select_calendars'] = 'Vyberte kalendáre, ktoré chcete pridať:';
$labels['read_only_status_yes'] = 'Áno';
$labels['read_only_status_no'] = 'Nie';
$labels['subscribed'] = 'Predplatené';

$labels['refresh_calendar'] = 'Obnoviť kalendár';
$labels['refresh_every_minute'] = 'Každú minútu';
$labels['refresh_every_5_minutes'] = 'Každých 5 minút';
$labels['refresh_every_15_minutes'] = 'Každých 15 minút';
$labels['refresh_every_30_minutes'] = 'Každých 30 minút';
$labels['refresh_every_60_minutes'] = 'Každých 60 minút';
$labels['refresh_never'] = 'Nikdy';

$labels['cannot_connect_to_caldav_server'] = 'Nemožno sa pripojiť k serveru CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Nemožno sa pripojiť k serveru CalDAV. Skontrolujte adresu URL servera.';
$labels['caldav_client_error_credentials'] = 'Nesprávne používateľské meno alebo heslo CalDAV.';
$labels['caldav_client_error_empty'] = 'Server nevrátil žiadne kalendáre, ktoré by bolo možné pridať. Skontrolujte adresu URL CalDAV.';
$labels['caldav_client_error_server_info'] = 'Nesprávne informácie o serveri CalDAV.';
$labels['caldav_client_error_add_calendar'] = 'Nemožno uložiť kalendár "$n".';
$labels['caldav_client_error_delete_event'] = 'Nie je možné odstrániť udalosť';
$labels['caldav_client_error_save_event'] = 'Udalosť CalDAV sa nedá uložiť: mohla byť presunutá, odstránená alebo je kalendár nedostupný.';
$labels['caldav_client_error_calendar_added'] = 'Kalendár "$n" je už v zozname kalendárov.';

// previewing event
$labels['remove_recurring_event'] = 'Odstránenie opakujúcej sa udalosti';
$labels['remove_one'] = 'Odstrániť iba túto inštanciu';
$labels['remove_all'] = 'Odstránenie všetkých inštancií';
$labels['edit_event'] = 'Upraviť udalosť';
$labels['event_removed'] = 'Udalosť vymazaná.';
$labels['go_to_url'] = 'Prejsť na adresu URL';
$labels['confirm_remove_event'] = 'Ste si istí, že chcete túto udalosť odstrániť?';

// calendar/agenda grid
$labels['no_events'] = 'Nie sú žiadne udalosti, ktoré by sa mali zobraziť.';

// importing events
$labels['select_calendar'] = 'Vyberte kalendár';
$labels['select_calendar_for_events'] = 'Vyberte kalendár, do ktorého chcete pridať udalosti:';
$labels['add_events'] = 'Pridanie udalostí';
$labels['add_events_to_calendar'] = 'Pridanie podujatí do kalendára';
$labels['events_imported_successfully'] = 'Udalosti boli úspešne importované';
$labels['events_imported_with_errors'] = 'Niektoré iné udalosti nebolo možné importovať.';
$labels['error_importing_events'] = 'Pri importovaní udalostí do kalendára došlo k chybe';

// editing events
$labels['calendar'] = 'Kalendár';
$labels['new_event'] = 'Nová udalosť';
$labels['summary'] = 'Zhrnutie';
$labels['recurrence'] = 'Opakovanie';
$labels['title'] = 'Názov';
$labels['location'] = 'Umiestnenie';
$labels['description'] = 'Popis';
$labels['comment'] = 'Komentár';
$labels['url'] = 'ADRESA URL';
$labels['name'] = 'Názov';
$labels['start'] = 'Štart';
$labels['end'] = 'Koniec';
$labels['show_timezones'] = 'Zobraziť časové pásma';
$labels['reset_timezones'] = 'Obnovenie predvolených časových pásiem';
$labels['all_day'] = 'Celý deň';
$labels['use_calendar_colors'] = 'Používanie farieb kalendára';
$labels['color'] = 'Farba';
$labels['background'] = 'Pozadie';
$labels['text'] = 'Text';
$labels['show_me_as'] = 'Ukážte mi ako';
$labels['available'] = 'Dostupné na';
$labels['busy'] = 'Zaneprázdnený';
$labels['visibility'] = 'Viditeľnosť';
$labels['calendar_default'] = 'Predvolené nastavenie kalendára';
$labels['public'] = 'Verejnosť';
$labels['private'] = 'Súkromná stránka';
$labels['repeat'] = 'Opakujte';
$labels['repeat_every'] = 'Opakujte každý';
$labels['never'] = 'Nikdy';
$labels['daily'] = 'Denne';
$labels['weekly'] = 'Týždeň';
$labels['monthly'] = 'Mesačne';
$labels['yearly'] = 'Ročný';
$labels['days'] = 'deň(y)';
$labels['weeks'] = 'týždeň(y)';
$labels['months'] = 'mesiac(y)';
$labels['years'] = 'rok(y)';
$labels['repeat_on'] = 'Opakujte na';
$labels['repeat_by'] = 'Opakovanie podľa';
$labels['day_of_month'] = 'Deň v mesiaci';
$labels['day_of_week'] = 'Deň v týždni';
$labels['not_specified'] = 'Nie je špecifikované';
$labels['first'] = 'Prvá stránka';
$labels['second'] = 'Druhý';
$labels['third'] = 'Tretia stránka';
$labels['fourth'] = 'Štvrtý';
$labels['fifth'] = 'Piata stránka';
$labels['second_to_last'] = 'Predposledný';
$labels['last'] = 'Posledný';
$labels['attachment_too_large'] = 'Súbor prílohy je príliš veľký. Maximálna povolená veľkosť je $s.';
$labels['bg_color'] = 'Farba pozadia';
$labels['tx_color'] = 'Farba textu';
$labels['add_notification'] = 'Pridanie oznámenia';
$labels['popup'] = 'Vyskakovacie okno';
$labels['no_notifications'] = 'Táto udalosť nemá žiadne upozornenia.';
$labels['minutes'] = 'zápisnice';
$labels['hours'] = 'hodiny';
$labels['link'] = 'Odkaz';
$labels['attach_file_from_web'] = 'Pripojte súbor';
$labels['attachment_url'] = 'Adresa URL prílohy';
$labels['invalid_url'] = 'Táto adresa URL je neplatná.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Disk Google';
$labels['duplicate'] = 'Duplikovať';
$labels['duplicate_suffix'] = '(duplikát)';
$labels['no_notifications'] = 'Táto udalosť nemá žiadne upozornenia.';
$labels['this_calendar_is_hidden'] = 'Tento kalendár je skrytý. Uložené udalosti sa nezobrazia, kým kalendár nebude viditeľný.';
$labels['make_calendar_visible'] = 'Nastavte tento kalendár ako viditeľný.';

// recurrence
$labels['recurrence_range'] = 'Rozsah opakovania';
$labels['repeat_forever'] = 'Opakujte navždy';
$labels['repeat_every_weekday'] = 'Opakujte každý pracovný deň';
$labels['last_day'] = 'Posledný deň';
$labels['every'] = 'Každý';
$labels['weekday'] = 'Víkendový deň';
$labels['weekend_day'] = 'Víkendový deň';
$labels['day_of_week'] = 'Deň v týždni';
$labels['day_of_month'] = 'Deň v mesiaci';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Koniec po';
$labels['occurrences'] = 'výskyt(y)';

// end recurrence on date:
$labels['end_by_date'] = 'Ukončenie do dátumu';
$labels['removed_events_title'] = 'Udalosti odstránené z opakujúcich sa sérií';
$labels['restore'] = 'Obnovenie';

// settings page
$labels['setting_view'] = 'Počiatočné zobrazenie kalendára';
$labels['setting_first_day'] = 'Týždeň sa začína';
$labels['setting_agenda_week_span'] = 'Časové rozpätie programu (týždne)';
$labels['setting_slot_duration'] = 'Časový interval dňa (v minútach)';
$labels['setting_scroll_time'] = 'Prejdite na čas';
$labels['setting_calendar'] = 'Predvolený kalendár pre udalosti';
$labels['setting_refresh'] = 'Obnovenie kalendárov';
$labels['setting_week_numbers'] = 'Čísla týždňov v malom kalendári';
$labels['setting_event_border'] = 'Farby kategórie ako okraje udalosti';
$labels['show_sunrise'] = 'Zobrazenie východu slnka v mesačnom zobrazení';
$labels['show_sunset'] = 'Zobraziť západ slnka v zobrazení mesiaca';
$labels['categories'] = 'Kategórie';
$labels['category'] = 'Kategória';
$labels['no_category'] = 'Žiadna kategória';
$labels['add_category'] = 'Pridať kategóriu';
$labels['setting_alarm_sound'] = 'Zvuk oznámenia';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambus';
$labels['alarm_sound_bells'] = 'Zvony';
$labels['alarm_sound_chord'] = 'Akord';
$labels['alarm_sound_circles'] = 'Kruhy';
$labels['alarm_sound_glass'] = 'Sklo';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Kov';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Píšťalka';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Prehrávanie zvuku';

// alarms
$labels['upcoming_events'] = 'Nadchádzajúce podujatia';
$labels['dismiss'] = 'Odmietnuť';
$labels['dismiss_all'] = 'Odmietnuť všetky';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minút';
$labels['10_minutes'] = '10 minút';
$labels['20_minutes'] = '20 minút';
$labels['30_minutes'] = '30 minút';
$labels['1_hour'] = '1 hodina';
$labels['2_hours'] = '2 hodiny';
$labels['tomorrow'] = 'Do zajtra';
$labels['next_week'] = 'Do budúceho týždňa';

// sidebar
$labels['setting_show_xcalendar'] = 'Zobraziť kalendár Agenda';
$labels['todays_agenda'] = 'Dnešný program';
$labels['no_events_today'] = 'Na dnes nie sú naplánované žiadne podujatia.';
$labels['sidebar_more'] = 'Viac...';

// event invitation
$labels['itip_title_request'] = 'Ste pozvaní na podujatie.';
$labels['itip_title_modified'] = 'Táto udalosť bola upravená.';
$labels['itip_title_reply_accepted'] = '$u prijalo vaše pozvanie';
$labels['itip_title_reply_tentative'] = '$u predbežne prijalo vaše pozvanie';
$labels['itip_title_reply_declined'] = '$u odmietol vaše pozvanie';
$labels['itip_title_cancel'] = 'Toto podujatie bolo zrušené.';

$labels['itip_email_subject_request'] = 'Pozvánka:';
$labels['itip_email_subject_request_modified'] = 'Udalosť upravená:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Zrušené:';

$labels['itip_popup_event_added'] = 'Udalosť pridaná do kalendára.';
$labels['itip_popup_event_updated'] = 'Udalosť aktualizovaná v kalendári.';
$labels['itip_popup_event_deleted'] = 'Udalosť odstránená z kalendára.';
$labels['itip_popup_response_sent'] = 'Odpoveď zaslaná organizátorovi.';

$labels['itip_message_invitation_accepted'] = 'Toto pozvanie ste prijali.';
$labels['itip_message_invitation_tentative'] = 'Toto pozvanie ste predbežne prijali.';
$labels['itip_message_invitation_declined'] = 'Toto pozvanie ste odmietli.';
$labels['itip_message_event_deleted'] = 'Udalosť bola z vášho kalendára vymazaná.';
$labels['itip_message_event_updated'] = 'Udalosť vo vašom kalendári bola aktualizovaná.';
$labels['itip_message_event_missing'] = 'Táto udalosť neexistuje v žiadnom z vašich kalendárov.';

$labels['itip_accept'] = 'Prijať';
$labels['itip_tentative'] = 'Predbežný';
$labels['itip_decline'] = 'Pokles';
$labels['itip_change_reponse'] = 'Reakcia na zmenu.';
$labels['itip_add_to_calendar'] = 'Pridať do kalendára';
$labels['itip_reply_to_sender'] = 'Odpovedať odosielateľovi';
$labels['itip_dont_import'] = 'Nepridávať do kalendára';
$labels['itip_update_in_calendar'] = 'Aktualizácia udalosti v kalendári';
$labels['itip_delete_from_calendar'] = 'Odstránenie udalosti z kalendára';

$labels['mail_subject_invitation'] = 'Pozvánka:';
$labels['mail_subject_update'] = 'Aktualizovaná pozvánka:';
$labels['mail_subject_cancel'] = 'Podujatie zrušené:';

// event email notifications
$labels['mail_title'] = 'Názov:';
$labels['mail_start'] = 'Začnite:';
$labels['mail_end'] = 'Koniec:';
$labels['mail_description'] = 'Popis: V súčasnosti sa nachádza na území Slovenskej republiky:';
$labels['mail_location'] = 'Umiestnenie:';

// event attendees
$labels['attendees'] = 'Účastníci';
$labels['attendee_role'] = 'Úloha';
$labels['attendee_role_required'] = 'Požadované';
$labels['attendee_role_optional'] = 'Voliteľné';
$labels['attendee_role_chair'] = 'Stolička';
$labels['attendee_role_none'] = 'Neúčastník';
$labels['organizer'] = 'Organizátor';
$labels['attendance'] = 'Dochádzka';
$labels['your_status'] = 'Váš stav';
$labels['attendance_going'] = 'Idete?';
$labels['attendee_status_yes'] = 'Áno';
$labels['attendee_status_no'] = 'Nie';
$labels['attendee_status_maybe'] = 'Možno';
$labels['attendee_status_delegated'] = 'Delegované';
$labels['attendee_status_waiting'] = 'Čakanie';
$labels['attendee_status_waiting_for_confirmation'] = 'Čakanie na potvrdenie';
$labels['attendee_status_info_yes'] = 'Zúčastní sa';
$labels['attendee_status_info_no'] = 'Nezúčastní sa';
$labels['attendee_status_info_maybe'] = 'Môže sa zúčastniť';
$labels['attendee_status_info_delegated'] = 'Delegovaná účasť';
$labels['attendee_role'] = 'Úloha';
$labels['attendee_role_info'] = 'Uveďte, či je účastník povinný zúčastniť sa na zasadnutí alebo mu predsedať.';
$labels['attendee_notify'] = 'Upozornenie';
$labels['attendee_notify_info'] = 'Ak je začiarknuté, účastníkovi bude zaslaná e-mailová pozvánka.';
$labels['add_attendee'] = 'Pridať účastníka';
$labels['attendee_notify_question'] = 'Odoslanie notifikačných e-mailov účastníkom?';
$labels['attendee_notify_textarea'] = 'Pridanie voliteľnej správy';
$labels['attendee_notify_send'] = 'Odoslať';
$labels['attendee_notify_dont'] = 'Neposielajte';
$labels['attendee_notify_cancel'] = 'Zrušiť';
$labels['new_attendee_not_saved_warning'] = 'Zadali ste e-mailovú adresu účastníka, ale nepridali ste ju do zoznamu účastníkov. Chcete pokračovať v ukladaní bez tohto účastníka?';

// import / export
$labels['import_export'] = 'Import / export';
$labels['export_to_file'] = 'Export do súboru';
$labels['export_info'] = 'Kliknutím na toto tlačidlo môžete exportovať a prevziať udalosti tohto kalendára vo formáte iCalendar (ics). Ak kalendár obsahuje veľa udalostí, proces exportu môže trvať niekoľko okamihov.';
$labels['export_no_events'] = 'Tento kalendár nemá žiadne udalosti na export.';
$labels['import_from_file'] = 'Import zo súboru';
$labels['import_info'] = 'Kliknutím na toto tlačidlo môžete do tohto kalendára importovať udalosti zo súboru iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Pripomienka termínu';
$labels['email_notification_subject'] = 'Pripomienka: $t na $d ($z)';
$labels['email_notification_cron_error'] = 'E-mailové oznámenia sú vypnuté. Kontaktujte správcu.';
$labels['alarm_before_event_starts'] = 'Pred začiatkom podujatia';
$labels['alarm_after_event_starts'] = 'Po začatí podujatia';
$labels['alarm_before_event_ends'] = 'Pred koncom podujatia';
$labels['alarm_after_event_ends'] = 'Po skončení podujatia';
$labels['alarm_at_specific_time'] = 'V určitom čase';

// search
$labels['specify_at_least_one_calendar'] = 'Zadajte aspoň jeden kalendár, v ktorom chcete vyhľadávať.';
$labels['specify_text_to_search'] = 'Zadajte text, ktorý chcete vyhľadať.';
$labels['clear_search_results'] = 'Jasné';
$labels['search_outdated_message'] = 'Výsledky vyhľadávania môžu byť neaktuálne. Zvážte opätovné spustenie vyhľadávania.';
$labels['date_range'] = 'Rozsah dátumov';
$labels['date_range_subtitle'] = 'Rozsah dátumov:';
$labels['search_results'] = 'Nájdené výsledky: $n';
$labels['modify_search_options'] = 'Skúste upraviť možnosti vyhľadávania.';
$labels['show_shortcuts'] = 'Zobraziť skratky';
$labels['hide_shortcuts'] = 'Skrytie skratiek';
$labels['this_month'] = 'Tento mesiac';
$labels['this_year'] = 'Tento rok';
$labels['past_30_days'] = 'Posledných 30 dní';
$labels['next_30_days'] = 'Nasledujúcich 30 dní';
$labels['past_6_months'] = 'Posledných 6 mesiacov';
$labels['next_6_months'] = 'Nasledujúcich 6 mesiacov';

// calendar grid
$labels['cannot_load_events'] = 'Nie je možné načítať udalosti pre kalendár "$c".';
