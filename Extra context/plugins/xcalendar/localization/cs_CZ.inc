<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Kalendář';
$labels['app_menu_title'] = 'Kalendář';

// menu
$labels['create_event'] = 'Nová událost';
$labels['create_event_button'] = 'Nová událost';
$labels['day'] = 'Den';
$labels['week'] = 'Týden';
$labels['month'] = 'Měsíc';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Kalendáře';
$labels['my_calendar'] = 'Můj kalendář';
$labels['create_calendar'] = 'Vytvořit kalendář';
$labels['new_calendar'] = 'Nový kalendář';
$labels['edit_calendar'] = 'Editovat kalendář';
$labels['remove_calendar'] = 'Smazat kalendář';
$labels['confirm_remove_calendar'] = 'Opravdu chcete smazat tento kalendář?';
$labels['calendar_removed'] = 'Kalendář smazán.';
$labels['notifications'] = 'Notifikace';
$labels['default_event_visibility'] = 'Výchozí viditelnost události';
$labels['time_zone'] = 'Časové pásmo kalendáře';
$labels['share_calendar'] = 'Sdílet kalendář';
$labels['permissions'] = 'Oprávnění';
$labels['permission_publish_calendar'] = 'Veřejný kalendář';
$labels['permission_share_calendar'] = 'Sdílený kalendář';
$labels['permission_edit_events'] = 'Upravit událost';
$labels['permission_see_details'] = 'Zobrazit podrobnosti';
$labels['permission_publish_calendar_info'] = 'Pokud je zaškrtnuto, uživatel bude moci kalendář publikovat.';
$labels['permission_share_calendar_info'] = 'Pokud je zaškrtnuto, uživatel bude moci kalendář sdílet s ostatními uživateli.';
$labels['permission_edit_events_info'] = 'Pokud je zaškrtnuto, uživatel bude moci upravovat a mazat události kalendáře.';
$labels['permission_see_details_info'] = 'Pokud je zaškrtnuto, budou sdíleny všechny podrobnosti události. Pokud není zaškrtnuto, budou sdíleny pouze informace o dostupnosti/obsazenosti.';
$labels['share_send_notification'] = 'Oznámení';
$labels['share_send_notification_info'] = 'Pokud je zaškrtnuto, bude uživateli zaslán e-mail s informací o nově sdíleném kalendáři.';
$labels['new_share_not_saved_warning'] = 'Zadali jste e-mail, se kterým chcete kalendář sdílet, ale neklikli jste na tlačítko Sdílet, abyste jej přidali do sdíleného seznamu. Pokračovat v ukládání bez sdílení kalendáře se zadaným uživatelem?';
$labels['share'] = 'Sdílet';
$labels['shared_marker'] = '(Sdílení)';
$labels['owner'] = 'Vlastník';
$labels['publish_calendar'] = 'Veřejný kalendář';
$labels['create_link'] = 'Vytvořit odkaz';
$labels['reset'] = 'Reset';
$labels['remove'] = 'Odstranit';
$labels['publish_full'] = 'Zveřejnit všechny informace o události.';
$labels['publish_busy'] = 'Publikovat pouze informace o volném čase.';
$labels['sync_no_id'] = 'Možnosti CalDAV budou dostupné po uložení kalendáře.';
$labels['share_no_id'] = 'Možnosti sdílení kalendáře budou dostupné po uložení kalendáře.';
$labels['publish_no_id'] = 'Možnosti publikování kalendáře budou dostupné po uložení kalendáře.';
$labels['import_no_id'] = 'Možnosti importu a exportu se zpřístupní po uložení kalendáře.';
$labels['unable_to_save_calendar_data'] = 'Data kalendáře nelze uložit.';
$labels['unable_to_retrieve_calendar_data'] = 'Nelze načíst data kalendáře. Kalendář již nemusí být dostupný.';
$labels['unable_add_shared_calendar'] = 'Nelze přidat sdílený kalendář. Kalendář již není k dispozici.';
$labels['itip_organizer_message'] = 'Na tuto akci jste byli pozváni, ale nejste jejím organizátorem. Změny, které v této události provedete, nebudou zaslány ostatním účastníkům.';
$labels['cannot_share_with_yourself'] = 'Vlastní kalendář nemůžete sdílet sami se sebou.';
$labels['cannot_share_with_calendar_owner'] = 'Tento kalendář nelze sdílet s vlastníkem kalendáře ($e)';
$labels['setting_default_notification_type'] = 'Výchozí upozornění na nové události';
$labels['calendar_name_cannot_be_empty'] = 'Název kalendáře nesmí být prázdný.';

// caldav server
$labels['sync_calendar'] = 'Synchronizace kalendáře';
$labels['password_too_short'] = 'Heslo by mělo mít alespoň 10 znaků';
$labels['read_only'] = 'Pouze pro čtení';
$labels['add_connection'] = 'Přidat připojení';
$labels['show_details'] = 'Zobrazit detaily';
$labels['hide_details'] = 'Skrýt detaily';
$labels['caldav_connection_password'] = 'Heslo, které jste zadali při vytváření tohoto připojení.';
$labels['yes'] = 'Ano';
$labels['no'] = 'Ne';
$labels['confirm_remove_sync'] = 'Opravdu chcete toto připojení trvale odebrat?';
$labels['remove_sync_error'] = 'Při odstraňování připojení došlo k chybě.';
$labels['create_new_caldav_connection'] = 'Vytvoři nové CalDav připojení';
$labels['your_caldav_connections'] = 'Vaše připojení CalDav';
$labels['created_at'] = 'Vytvořeno';
$labels['connected_at'] = 'Poslední synchronizace';
$labels['never'] = 'Nikdy';
$labels['alternate_url'] = 'Alternativní URL';
$labels['caldav_password_info'] = 'Z bezpečnostních důvodů toto heslo není uloženo na serveru. Pokud jste jej zapomněli, smažte toto připojení a vytvořte nové.';
$labels['caldav_url_info'] = 'Tuto adresu URL použijte s aplikacemi, které podporují zjišťování kalendáře, například: Kalendář iOS a Mac OS, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Použijte tuto adresu URL s aplikacemi, které nebudou fungovat s krátkou adresou URL výše, například: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'Neplatná adresa URL serveru CalDAV.';
$labels['cannot_resolve_caldav_host'] = 'Nelze zjistit hostitele serveru CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'Soukromé a vyhrazené adresy CalDAV nejsou povoleny.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Přidat sdílený kalendář';
$labels['shared_calendars'] = 'Sdílené kalendáře';
$labels['no_shared_calendars'] = 'Neexistují žádné sdílené kalendáře, které byste mohli přidat.';
$labels['add_to_my_calendars'] = 'Přidat do mých kalendářů';
$labels['remove_from_my_calendars'] = 'Odstranit z mých kalendářů';
$labels['unshare'] = 'Zrušit sdílení';
$labels['unshare_warning'] = 'Pokud zrušíte sdílení tohoto kalendáře, zmizí z tohoto seznamu a nebudete jej moci znovu přidat.';
$labels['calendar_name'] = 'Jméno kalendáře';
$labels['calendar_owner'] = 'Vlastník kalendáře';
$labels['shared_date'] = 'Sdílet';
$labels['unable_to_share'] = 'Kalendář nelze sdílet s následujícími uživateli, protože jejich e-mailové účty nejsou umístěny na místním serveru:';

// shared calendar email
$labels['shared_email_subject'] = '$u s vámi sdílí kalendář';
$labels['shared_email_body'] = 'Uživatel $u vám udělil přístup ke kalendáři s názvem „$c“. Chcete-li tento kalendář přidat do seznamu aktivních kalendářů, klikněte na odkaz níže:';
$labels['shared_email_body_cpanel'] = 'Uživatel $u vám udělil přístup ke kalendáři s názvem „$c“. Chcete-li tento kalendář přidat do seznamu aktivních kalendářů, přihlaste se do Roundcube, přejděte do Kalendáře, klikněte na ikonu plus na postranním panelu a vyberte Přidat sdílený kalendář.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Prázdninové kalendáře';
$labels['edit_holiday_calendar'] = 'Úprava kalendáře dovolených';
$labels['religious_holidays'] = 'Náboženské svátky';
$labels['country_holidays'] = 'Místní svátky';
$labels['add'] = 'Přidat';
$labels['no_more_holidays_allowed'] = 'Nemůžete přidat žádné další kalendáře svátků.';
$labels['cannot_load_holidays'] = 'Nelze načíst svátky ze serveru Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Přidat kalendář narozenin';
$labels['edit_birthday_calendar'] = 'Upravit kalendář narozenin';
$labels['contact_birthdays'] = 'Kontakt Narozeniny';
$labels['contact_birthdays_info'] = 'Tento kalendář načítá informace o narozeninách ze seznamu kontaktů a zobrazuje je jako události kalendáře.';
$labels['birthday_event_title'] = 'Narozeniny: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Přidat Google kalendář';
$labels['edit_google_calendar'] = 'Úprava kalendáře Google';
$labels['google_id'] = 'Google kalendář ID';
$labels['new_google_calendar'] = 'Nový Google kalendář';
$labels['specify_google_calendar_id'] = 'Zadejte prosím ID kalendáře Google';
$labels['calendar_id_format_error'] = 'ID kalendáře Google by mělo obsahovat znak @ .';
$labels['cannot_load_google_calendar'] = 'Kalendář Google "$c" nelze načíst. Zkontrolujte, zda je ID kalendáře správné a zda byl kalendář zpřístupněn veřejnosti.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Přidání kalendáře CalDAV';
$labels['edit_caldav_calendar'] = 'Úprava kalendáře CalDAV';
$labels['add_caldav_calendar_information'] = 'Chcete-li se přihlásit k odběru kalendáře CalDAV, zadejte informace o serveru CalDAV a klikněte na tlačítko Najít kalendáře.';
$labels['caldav_url'] = 'Adresa URL CalDAV';
$labels['find_calendars'] = 'Vyhledávání kalendářů';
$labels['searching_for_calendars'] = 'Vyhledávání kalendářů, počkejte prosím...';
$labels['select_calendars'] = 'Vyberte kalendáře, které chcete přidat:';
$labels['read_only_status_yes'] = 'Ano';
$labels['read_only_status_no'] = 'Ne';
$labels['subscribed'] = 'Předplatné';

$labels['refresh_calendar'] = 'Obnovit kalendář';
$labels['refresh_every_minute'] = 'Každou minutu';
$labels['refresh_every_5_minutes'] = 'Každých 5 minut';
$labels['refresh_every_15_minutes'] = 'Každých 15 minut';
$labels['refresh_every_30_minutes'] = 'Každých 30 minut';
$labels['refresh_every_60_minutes'] = 'Každých 60 minut';
$labels['refresh_never'] = 'Nikdy';

$labels['cannot_connect_to_caldav_server'] = 'Nelze se připojit k serveru CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Nelze se připojit k serveru CalDAV. Zkontrolujte adresu URL serveru.';
$labels['caldav_client_error_credentials'] = 'Nesprávné uživatelské jméno nebo heslo CalDAV.';
$labels['caldav_client_error_empty'] = 'Server nevrátil žádné kalendáře, které by bylo možné přidat. Zkontrolujte adresu URL CalDAV.';
$labels['caldav_client_error_server_info'] = 'Nesprávné informace o serveru CalDAV.';
$labels['caldav_client_error_add_calendar'] = 'Nelze uložit kalendář "$n".';
$labels['caldav_client_error_delete_event'] = 'Nelze odstranit událost';
$labels['caldav_client_error_save_event'] = 'Nelze uložit událost CalDAV: událost mohla být přesunuta, smazána nebo je kalendář nedostupný.';
$labels['caldav_client_error_calendar_added'] = 'Kalendář "$n" je již ve vašem seznamu kalendářů.';

// previewing event
$labels['remove_recurring_event'] = 'Smazat opakující se událost';
$labels['remove_one'] = 'Smazat pouze tuto instanci';
$labels['remove_all'] = 'Smazat všechny instance';
$labels['edit_event'] = 'Upravit událost';
$labels['event_removed'] = 'Smazat událost.';
$labels['go_to_url'] = 'Přejit na URL';
$labels['confirm_remove_event'] = 'Opravdu chcete tuto událost odstranit?';

// calendar/agenda grid
$labels['no_events'] = 'Nejsou žádné události k zobrazení.';

// importing events
$labels['select_calendar'] = 'Vyberte kalendář';
$labels['select_calendar_for_events'] = 'Vyberte kalendář, do kterého chcete přidat události:';
$labels['add_events'] = 'Přidat události';
$labels['add_events_to_calendar'] = 'Přidejte události do kalendáře';
$labels['events_imported_successfully'] = 'Události byly úspěšně importovány';
$labels['events_imported_with_errors'] = 'Některou z událostí nebylo možné importovat.';
$labels['error_importing_events'] = 'Při importu událostí do kalendáře došlo k chybě.';

// editing events
$labels['calendar'] = 'Kalendář';
$labels['new_event'] = 'Nová událost';
$labels['summary'] = 'Souhrn';
$labels['recurrence'] = 'Opakování';
$labels['title'] = 'Název';
$labels['location'] = 'Místo';
$labels['description'] = 'Popis';
$labels['comment'] = 'Komentář (nepovinný))';
$labels['url'] = 'URL';
$labels['name'] = 'Název';
$labels['start'] = 'Začátek';
$labels['end'] = 'Konec';
$labels['show_timezones'] = 'Zobrazit časová pásma';
$labels['reset_timezones'] = 'Resetovat časová pásma';
$labels['all_day'] = 'Celý den';
$labels['use_calendar_colors'] = 'Použít výchozí barvy kalendáře';
$labels['color'] = 'Barva';
$labels['background'] = 'Pozadí';
$labels['text'] = 'Text';
$labels['show_me_as'] = 'Zobrazit mě jako';
$labels['available'] = 'Dostupný';
$labels['busy'] = 'Zaneprázdněný';
$labels['visibility'] = 'Viditelnost';
$labels['calendar_default'] = 'Default';
$labels['public'] = 'Veřejná';
$labels['private'] = 'Soukromá';
$labels['repeat'] = 'Opakovat';
$labels['repeat_every'] = 'Opakovavat každých';
$labels['never'] = 'Nikdy';
$labels['daily'] = 'Denně';
$labels['weekly'] = 'Týdně';
$labels['monthly'] = 'Měsíčně';
$labels['yearly'] = 'Ročně';
$labels['days'] = 'den(ů)';
$labels['weeks'] = 'týden(ů)';
$labels['months'] = 'měsíc(ů)';
$labels['years'] = 'rok(let)';
$labels['repeat_on'] = 'Opakovat v';
$labels['repeat_by'] = 'Opakovat';
$labels['day_of_month'] = 'den v měsíci';
$labels['day_of_week'] = 'den v týdnu';
$labels['not_specified'] = 'Není specifikováno';
$labels['first'] = 'První';
$labels['second'] = 'Druhý';
$labels['third'] = 'Třetí';
$labels['fourth'] = 'Čtvrtý';
$labels['fifth'] = 'Pátý';
$labels['second_to_last'] = 'Předposlední';
$labels['last'] = 'Poslední';
$labels['attachment_too_large'] = 'Příloha je příliš veliká. Maximální povolená velikost je $s.';
$labels['bg_color'] = 'Barva pozadí';
$labels['tx_color'] = 'Barva textu';
$labels['add_notification'] = 'Přidat oznámení';
$labels['popup'] = 'Vyskakovat';
$labels['no_notifications'] = 'Tato událost nemá žádná oznámení.';
$labels['minutes'] = 'minut';
$labels['hours'] = 'hodin';
$labels['link'] = 'Odkaz';
$labels['attach_file_from_web'] = 'Připojte soubor';
$labels['attachment_url'] = 'Adresa URL přílohy';
$labels['invalid_url'] = 'Tato adresa URL je neplatná.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Disk Google';
$labels['duplicate'] = 'Duplikovat';
$labels['duplicate_suffix'] = '(duplikát)';
$labels['no_notifications'] = 'Tato událost nemá žádná oznámení.';
$labels['this_calendar_is_hidden'] = 'Tento kalendář je skrytý. Uložené události se nezobrazí, dokud nebude kalendář viditelný.';
$labels['make_calendar_visible'] = 'Zviditelnit tento kalendář.';

// recurrence
$labels['recurrence_range'] = 'Rozsah opakování';
$labels['repeat_forever'] = 'Opakujte navždy';
$labels['repeat_every_weekday'] = 'Opakujte každý všední den';
$labels['last_day'] = 'Poslední den';
$labels['every'] = 'Každý';
$labels['weekday'] = 'Všední den';
$labels['weekend_day'] = 'Víkendový den';
$labels['day_of_week'] = 'den v týdnu';
$labels['day_of_month'] = 'den v měsíci';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Konec po';
$labels['occurrences'] = 'výskytů';

// end recurrence on date:
$labels['end_by_date'] = 'Ukončení k datu';
$labels['removed_events_title'] = 'Události odstraněny z opakujících se sérií';
$labels['restore'] = 'Obnovit';

// settings page
$labels['setting_view'] = 'Počáteční zobrazení kalendáře';
$labels['setting_first_day'] = 'Týden začíná';
$labels['setting_agenda_week_span'] = 'Časové rozpětí (týdny)';
$labels['setting_slot_duration'] = 'Denní časový úsek (minuty)';
$labels['setting_scroll_time'] = 'Přejít na čas';
$labels['setting_calendar'] = 'Výchozí kalendář pro události';
$labels['setting_refresh'] = 'Obnovení kalendářů';
$labels['setting_week_numbers'] = 'Čísla týdnů v mini kalendáři';
$labels['setting_event_border'] = 'Barvy kategorií jako ohraničení událostí';
$labels['show_sunrise'] = 'Zobrazit východ slunce v zobrazení měsíce';
$labels['show_sunset'] = 'Zobrazit západ slunce v zobrazení měsíce';
$labels['categories'] = 'Kategorie';
$labels['category'] = 'Kategorie';
$labels['no_category'] = 'Žádná kategorie';
$labels['add_category'] = 'Přidat kategorii';
$labels['setting_alarm_sound'] = 'Zvuk upozornění';
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
$labels['play_sound'] = 'Přehrát zvuk';

// alarms
$labels['upcoming_events'] = 'Připravované akce';
$labels['dismiss'] = 'Zavřít';
$labels['dismiss_all'] = 'Zavřít vše';
$labels['snooze'] = 'Odložit';
$labels['5_minutes'] = '5 minut';
$labels['10_minutes'] = '10 minut';
$labels['20_minutes'] = '20 minut';
$labels['30_minutes'] = '30 minut';
$labels['1_hour'] = '1 hodina';
$labels['2_hours'] = '2 hodiny';
$labels['tomorrow'] = 'Do zítřka';
$labels['next_week'] = 'Do příštího týdne';

// sidebar
$labels['setting_show_xcalendar'] = 'Zobrazit dnešní kalendář';
$labels['todays_agenda'] = 'Dnešní agenda';
$labels['no_events_today'] = 'Žádné události pro dnešní den.';
$labels['sidebar_more'] = 'Více...';

// event invitation
$labels['itip_title_request'] = 'Jste pozváni na akci.';
$labels['itip_title_modified'] = 'Tato událost byla upravena.';
$labels['itip_title_reply_accepted'] = '$u přijal vaše pozvání';
$labels['itip_title_reply_tentative'] = '$u předběžně přijal vaše pozvání';
$labels['itip_title_reply_declined'] = '$u odmítl vaše pozvání';
$labels['itip_title_cancel'] = 'Tato akce byla zrušena.';

$labels['itip_email_subject_request'] = 'Pozvánka:';
$labels['itip_email_subject_request_modified'] = 'Událost upravena:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Zrušeno:';

$labels['itip_popup_event_added'] = 'Událost přidána do kalendáře.';
$labels['itip_popup_event_updated'] = 'Událost aktualizována v kalendáři.';
$labels['itip_popup_event_deleted'] = 'Událost odstraněna z kalendáře.';
$labels['itip_popup_response_sent'] = 'Odpověď zaslaná organizátorovi.';

$labels['itip_message_invitation_accepted'] = 'Toto pozvání jste přijali.';
$labels['itip_message_invitation_tentative'] = 'Toto pozvání jste předběžně přijali.';
$labels['itip_message_invitation_declined'] = 'Toto pozvání jste odmítli.';
$labels['itip_message_event_deleted'] = 'Událost byla z vašeho kalendáře odstraněna.';
$labels['itip_message_event_updated'] = 'Událost ve vašem kalendáři byla aktualizována.';
$labels['itip_message_event_missing'] = 'Tato událost neexistuje v žádném z vašich kalendářů.';

$labels['itip_accept'] = 'Přijmout';
$labels['itip_tentative'] = 'Předběžný';
$labels['itip_decline'] = 'Pokles';
$labels['itip_change_reponse'] = 'Reakce na změnu.';
$labels['itip_add_to_calendar'] = 'Přidat do kalendáře';
$labels['itip_reply_to_sender'] = 'Odpovědět odesílateli';
$labels['itip_dont_import'] = 'Nepřidávat do kalendáře';
$labels['itip_update_in_calendar'] = 'Aktualizace události v kalendáři';
$labels['itip_delete_from_calendar'] = 'Odstranění události z kalendáře';

$labels['mail_subject_invitation'] = 'Pozvánka:';
$labels['mail_subject_update'] = 'Aktualizováno pozvání:';
$labels['mail_subject_cancel'] = 'Událost zrušena:';

// event email notifications
$labels['mail_title'] = 'Název:';
$labels['mail_start'] = 'Začátek:';
$labels['mail_end'] = 'Konec:';
$labels['mail_description'] = 'Popis:';
$labels['mail_location'] = 'Místo:';

// event attendees
$labels['attendees'] = 'Účastníci';
$labels['attendee_role'] = 'Role';
$labels['attendee_role_required'] = 'Povinné';
$labels['attendee_role_optional'] = 'Volitelné';
$labels['attendee_role_chair'] = 'Předseda';
$labels['attendee_role_none'] = 'Neúčastník';
$labels['organizer'] = 'Organizátor';
$labels['attendance'] = 'Účast';
$labels['your_status'] = 'Váš status';
$labels['attendance_going'] = 'Jdeš?';
$labels['attendee_status_yes'] = 'Ano';
$labels['attendee_status_no'] = 'Ne';
$labels['attendee_status_maybe'] = 'Možná';
$labels['attendee_status_delegated'] = 'Delegováno';
$labels['attendee_status_waiting'] = 'Čekání';
$labels['attendee_status_waiting_for_confirmation'] = 'Čekání na potvrzení';
$labels['attendee_status_info_yes'] = 'Zúčastním se';
$labels['attendee_status_info_no'] = 'Nezúčastním se';
$labels['attendee_status_info_maybe'] = 'Možná se zúčastním';
$labels['attendee_status_info_delegated'] = 'Delegovaná účast';
$labels['attendee_role'] = 'Role';
$labels['attendee_role_info'] = 'Určete, zda se má účastník zúčastnit nebo předsedat schůzi.';
$labels['attendee_notify'] = 'Upozornit';
$labels['attendee_notify_info'] = 'Pokud je zaškrtnuto, bude účastníkovi zaslán e-mail s pozvánkou.';
$labels['add_attendee'] = 'Přidat účastníka';
$labels['attendee_notify_question'] = 'Posílat e-maily s upozorněním účastníkům?';
$labels['attendee_notify_textarea'] = 'Přidat volitelnou zprávu';
$labels['attendee_notify_send'] = 'Odeslat';
$labels['attendee_notify_dont'] = 'Neodesílat';
$labels['attendee_notify_cancel'] = 'Zrušit';
$labels['new_attendee_not_saved_warning'] = 'Zadali jste e-mailovou adresu účastníka, ale nepřidali jste ji do seznamu účastníků. Chcete pokračovat v ukládání bez tohoto účastníka?';

// import / export
$labels['import_export'] = 'Import / Export';
$labels['export_to_file'] = 'Export do souboru';
$labels['export_info'] = 'Klepnutím na toto tlačítko exportujete a stáhnete události tohoto kalendáře ve formátu iCalendar (ics). Pokud kalendář obsahuje mnoho událostí, může proces exportu chvíli trvat.';
$labels['export_no_events'] = 'Tento kalendář neobsahuje žádné události k exportu.';
$labels['import_from_file'] = 'Importovat ze souboru';
$labels['import_info'] = 'Klepnutím na tlačítko importujete události do kalendáře ze souboru iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Připomenutí události';
$labels['email_notification_subject'] = 'Připomenutí: $t v $d ($z)';
$labels['email_notification_cron_error'] = 'E-mailová upozornění jsou zakázána. Kontaktujte administrátora.';
$labels['alarm_before_event_starts'] = 'Před začátkem události';
$labels['alarm_after_event_starts'] = 'Po spuštění události';
$labels['alarm_before_event_ends'] = 'Před koncem události';
$labels['alarm_after_event_ends'] = 'Po skončení události';
$labels['alarm_at_specific_time'] = 'V určitou dobu';

// search
$labels['specify_at_least_one_calendar'] = 'Zadejte alespoň jeden kalendář, ve kterém chcete vyhledávat.';
$labels['specify_text_to_search'] = 'Zadejte text, který chcete vyhledat.';
$labels['clear_search_results'] = 'Přehledně';
$labels['search_outdated_message'] = 'Výsledky vyhledávání mohou být zastaralé. Zvažte opětovné spuštění vyhledávání.';
$labels['date_range'] = 'Rozsah dat';
$labels['date_range_subtitle'] = 'Rozsah dat:';
$labels['search_results'] = 'Nalezené výsledky: $n';
$labels['modify_search_options'] = 'Zkuste upravit možnosti vyhledávání.';
$labels['show_shortcuts'] = 'Zobrazit zkratky';
$labels['hide_shortcuts'] = 'Skrytí zkratek';
$labels['this_month'] = 'Tento měsíc';
$labels['this_year'] = 'V letošním roce';
$labels['past_30_days'] = 'Posledních 30 dní';
$labels['next_30_days'] = 'Příštích 30 dní';
$labels['past_6_months'] = 'Posledních 6 měsíců';
$labels['next_6_months'] = 'Následujících 6 měsíců';

// calendar grid
$labels['cannot_load_events'] = 'Nelze načíst události pro kalendář "$c".';
