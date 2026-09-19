<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Kalenteri';
$labels['app_menu_title'] = 'Kalenteri';

// menu
$labels['create_event'] = 'Luo tapahtuma';
$labels['create_event_button'] = 'Uusi tapahtuma';
$labels['day'] = 'Päivä';
$labels['week'] = 'Viikko';
$labels['month'] = 'Kuukausi';
$labels['agenda'] = 'Esityslista';

// editing calendars
$labels['calendars'] = 'Kalenterit';
$labels['my_calendar'] = 'Kalenterini';
$labels['create_calendar'] = 'Luo kalenteri';
$labels['new_calendar'] = 'Uusi kalenteri';
$labels['edit_calendar'] = 'Muokkaa kalenteria';
$labels['remove_calendar'] = 'Poista kalenteri';
$labels['confirm_remove_calendar'] = 'Haluatko varmasti poistaa tämän kalenterin?';
$labels['calendar_removed'] = 'Kalenteri poistettu.';
$labels['notifications'] = 'Ilmoitukset';
$labels['default_event_visibility'] = 'Tapahtuman oletusnäkyvyys';
$labels['time_zone'] = 'Kalenterin aikavyöhyke';
$labels['share_calendar'] = 'Jaa kalenteri';
$labels['permissions'] = 'Luvat';
$labels['permission_publish_calendar'] = 'Julkaise kalenteri';
$labels['permission_share_calendar'] = 'Jaa kalenteri';
$labels['permission_edit_events'] = 'Muokkaa tapahtumia';
$labels['permission_see_details'] = 'Katso lisätietoja';
$labels['permission_publish_calendar_info'] = 'Jos tämä valitaan, käyttäjä voi julkaista kalenterin.';
$labels['permission_share_calendar_info'] = 'Jos tämä valitaan, käyttäjä voi jakaa kalenterin muiden käyttäjien kanssa.';
$labels['permission_edit_events_info'] = 'Jos tämä valitaan, käyttäjä voi muokata ja poistaa kalenteritapahtumia.';
$labels['permission_see_details_info'] = 'Jos valintaruutu on valittuna, kaikki tapahtuman tiedot jaetaan. Jos valintamerkki poistetaan, vain käytettävissä olevat/varatut tiedot jaetaan.';
$labels['share_send_notification'] = 'Ilmoita';
$labels['share_send_notification_info'] = 'Jos valintaruutu on valittuna, käyttäjälle lähetetään sähköpostiviesti, jossa hänelle ilmoitetaan jaetusta kalenterista.';
$labels['new_share_not_saved_warning'] = 'Kirjoitit sähköpostin, jonka kanssa haluat jakaa kalenterin, mutta et napsauttanut Jaa-painiketta lisätäksesi sen jaettuun luetteloon. Jatka tallentamista jakamatta kalenteria määritetyn käyttäjän kanssa?';
$labels['share'] = 'Jaa';
$labels['shared_marker'] = '(Jaettu)';
$labels['owner'] = 'Omistaja';
$labels['publish_calendar'] = 'Julkaise kalenteri';
$labels['create_link'] = 'Luo linkki';
$labels['reset'] = 'Nollaa';
$labels['remove'] = 'Poista';
$labels['publish_full'] = 'Julkaise kaikki tapahtuman tiedot.';
$labels['publish_busy'] = 'Julkaise vain vapaita/kiireisiä koskevat tiedot.';
$labels['sync_no_id'] = 'CalDAV-vaihtoehdot tulevat saataville, kun olet tallentanut kalenterin.';
$labels['share_no_id'] = 'Kalenterin jakamisvaihtoehdot tulevat saataville, kun olet tallentanut kalenterin.';
$labels['publish_no_id'] = 'Kalenterin julkaisuvaihtoehdot tulevat saataville, kun olet tallentanut kalenterin.';
$labels['import_no_id'] = 'Tuonti- ja vientivaihtoehdot tulevat saataville, kun olet tallentanut kalenterin.';
$labels['unable_to_save_calendar_data'] = 'Kalenteritietojen tallentaminen ei onnistu.';
$labels['unable_to_retrieve_calendar_data'] = 'Kalenteritietoja ei pystytä hakemaan. Kalenteri ei ehkä ole enää käytettävissä.';
$labels['unable_add_shared_calendar'] = 'Jaetun kalenterin lisääminen ei onnistu. Kalenteri ei ehkä ole enää käytettävissä.';
$labels['itip_organizer_message'] = 'Sinut on kutsuttu tähän tapahtumaan, mutta et ole järjestäjä. Tähän tapahtumaan tekemiäsi muutoksia ei lähetetä muille osallistujille.';
$labels['cannot_share_with_yourself'] = 'Et voi jakaa omaa kalenteriasi itsellesi.';
$labels['cannot_share_with_calendar_owner'] = 'Et voi jakaa tätä kalenteria kalenterin omistajan kanssa ($e).';
$labels['setting_default_notification_type'] = 'Uusien tapahtumien oletusilmoitukset';
$labels['calendar_name_cannot_be_empty'] = 'Kalenterin nimi ei voi olla tyhjä.';

// caldav server
$labels['sync_calendar'] = 'Synkronoi kalenteri';
$labels['password_too_short'] = 'Salasanan on oltava vähintään 10 merkkiä pitkä';
$labels['read_only'] = 'Vain luku';
$labels['add_connection'] = 'Lisää yhteys';
$labels['show_details'] = 'Näytä tiedot';
$labels['hide_details'] = 'Piilota tiedot';
$labels['caldav_connection_password'] = 'Salasana, jonka määrittelit yhteyden luomisen yhteydessä.';
$labels['yes'] = 'Kyllä';
$labels['no'] = 'Ei';
$labels['confirm_remove_sync'] = 'Haluatko varmasti poistaa tämän yhteyden pysyvästi?';
$labels['remove_sync_error'] = 'Yhteyttä poistettaessa tapahtui virhe.';
$labels['create_new_caldav_connection'] = 'Luo uusi CalDav-yhteys';
$labels['your_caldav_connections'] = 'CalDav-yhteydet';
$labels['created_at'] = 'Luotu';
$labels['connected_at'] = 'Viimeisin synkronointi';
$labels['never'] = 'Ei koskaan';
$labels['alternate_url'] = 'Vaihtoehtoinen URL-osoite';
$labels['caldav_password_info'] = 'Turvallisuussyistä tätä salasanaa ei tallenneta palvelimelle. Jos olet unohtanut sen, poista tämä yhteys ja luo uusi.';
$labels['caldav_url_info'] = 'Käytä tätä URL-osoitetta sovelluksissa, jotka tukevat kalenterin löytämistä, esimerkiksi iOS- ja Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Käytä tätä URL-osoitetta esimerkiksi sovelluksissa, jotka eivät toimi yllä olevan lyhyen url-osoitteen kanssa: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'CalDAV-palvelimen URL-osoite on virheellinen.';
$labels['cannot_resolve_caldav_host'] = 'CalDAV-palvelimen isäntää ei voida tunnistaa.';
$labels['private_caldav_addresses_not_allowed'] = 'Yksityisiä ja varattuja CalDAV-osoitteita ei sallita.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Lisää jaettu kalenteri';
$labels['shared_calendars'] = 'Jaetut kalenterit';
$labels['no_shared_calendars'] = 'Jaettuja kalentereita ei voi lisätä.';
$labels['add_to_my_calendars'] = 'Lisää kalentereihini';
$labels['remove_from_my_calendars'] = 'Poista kalentereistani';
$labels['unshare'] = 'Unshare';
$labels['unshare_warning'] = 'Jos poistat kalenterin jakamisen, se katoaa tästä luettelosta, etkä voi lisätä sitä uudelleen.';
$labels['calendar_name'] = 'Kalenterin nimi';
$labels['calendar_owner'] = 'Kalenterin omistaja';
$labels['shared_date'] = 'Jaettu';
$labels['unable_to_share'] = 'Kalenteria ei voi jakaa seuraavien käyttäjien kanssa, koska heidän sähköpostitilinsä sijaitsevat etäpalvelimella:';

// shared calendar email
$labels['shared_email_subject'] = '$u on jakanut kalenterin kanssasi';
$labels['shared_email_body'] = 'Käyttäjä $u on antanut sinulle pääsyn kalenteriin nimeltä "$c". Voit lisätä tämän kalenterin aktiivisten kalenterien luetteloon napsauttamalla alla olevaa linkkiä:';
$labels['shared_email_body_cpanel'] = 'Käyttäjä $u on antanut sinulle pääsyn kalenteriin nimeltä "$c". Voit lisätä tämän kalenterin aktiivisten kalenterien luetteloon kirjautumalla sisään Roundcubeen, siirtymällä kohtaan Kalenteri, napsauttamalla sivupalkin plus-kuvaketta ja valitsemalla Lisää jaettu kalenteri.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Lomakalenterit';
$labels['edit_holiday_calendar'] = 'Muokkaa lomakalenteria';
$labels['religious_holidays'] = 'Uskonnolliset vapaapäivät';
$labels['country_holidays'] = 'Maalaislomat';
$labels['add'] = 'Lisää';
$labels['no_more_holidays_allowed'] = 'Et voi lisätä enää yhtään lomakalenteria.';
$labels['cannot_load_holidays'] = 'Lomia ei voi ladata Googlen palvelimelta.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Lisää syntymäpäiväkalenteri';
$labels['edit_birthday_calendar'] = 'Muokkaa syntymäpäiväkalenteria';
$labels['contact_birthdays'] = 'Yhteystiedot Syntymäpäivät';
$labels['contact_birthdays_info'] = 'Tämä kalenteri hakee syntymäpäivätiedot yhteystietoluettelostasi ja näyttää ne kalenteritapahtumina.';
$labels['birthday_event_title'] = 'Syntymäpäivä: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Lisää Google-kalenteri';
$labels['edit_google_calendar'] = 'Muokkaa Google-kalenteria';
$labels['google_id'] = 'Google-kalenterin tunnus';
$labels['new_google_calendar'] = 'Uusi Google-kalenteri';
$labels['specify_google_calendar_id'] = 'Kirjoita Google-kalenterin tunnus';
$labels['calendar_id_format_error'] = 'Googlen kalenterin tunnuksen tulee sisältää @-merkki.';
$labels['cannot_load_google_calendar'] = 'Google-kalenteria "$c" ei voida ladata. Varmista, että kalenterin tunnus on oikein ja että kalenteri on asetettu julkisesti saataville.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Lisää CalDAV-kalenteri';
$labels['edit_caldav_calendar'] = 'Muokkaa CalDAV-kalenteria';
$labels['add_caldav_calendar_information'] = 'Jos haluat tilata CalDAV-kalenterin, määritä CalDAV-palvelimen tiedot ja valitse Etsi kalentereita.';
$labels['caldav_url'] = 'CalDAV URL-osoite';
$labels['find_calendars'] = 'Etsi kalenterit';
$labels['searching_for_calendars'] = 'Kalentereita etsitään, odota...';
$labels['select_calendars'] = 'Valitse lisättävät kalenterit:';
$labels['read_only_status_yes'] = 'Kyllä';
$labels['read_only_status_no'] = 'Ei';
$labels['subscribed'] = 'Tilattu';

$labels['refresh_calendar'] = 'Päivitä kalenteri';
$labels['refresh_every_minute'] = 'Joka minuutti';
$labels['refresh_every_5_minutes'] = '5 minuutin välein';
$labels['refresh_every_15_minutes'] = '15 minuutin välein';
$labels['refresh_every_30_minutes'] = '30 minuutin välein';
$labels['refresh_every_60_minutes'] = '60 minuutin välein';
$labels['refresh_never'] = 'Ei koskaan';

$labels['cannot_connect_to_caldav_server'] = 'Yhteyttä CalDAV-palvelimeen "$n" ei voida muodostaa.';
$labels['caldav_client_error_url'] = 'Yhteyttä CalDAV-palvelimeen ei voida muodostaa. Tarkista palvelimen URL-osoite.';
$labels['caldav_client_error_credentials'] = 'Väärä CalDAV-käyttäjänimi tai salasana.';
$labels['caldav_client_error_empty'] = 'Palvelin ei palauttanut yhtään lisättävää kalenteria. Tarkista CalDAV URL-osoite.';
$labels['caldav_client_error_server_info'] = 'Virheelliset CalDAV-palvelimen tiedot.';
$labels['caldav_client_error_add_calendar'] = 'Kalenteria "$n" ei voida tallentaa.';
$labels['caldav_client_error_delete_event'] = 'Tapahtumaa ei voi poistaa';
$labels['caldav_client_error_save_event'] = 'CalDAV-tapahtumaa ei voi tallentaa: se on mahdollisesti siirretty tai poistettu, tai kalenteri ei ole käytettävissä.';
$labels['caldav_client_error_calendar_added'] = 'Kalenteri "$n" on jo kalenteriluettelossasi.';

// previewing event
$labels['remove_recurring_event'] = 'Poista toistuva tapahtuma';
$labels['remove_one'] = 'Poista vain tämä instanssi';
$labels['remove_all'] = 'Poista kaikki instanssit';
$labels['edit_event'] = 'Muokkaa tapahtumaa';
$labels['event_removed'] = 'Tapahtuma poistettu.';
$labels['go_to_url'] = 'Siirry URL-osoitteeseen';
$labels['confirm_remove_event'] = 'Haluatko varmasti poistaa tämän tapahtuman?';

// calendar/agenda grid
$labels['no_events'] = 'Näytettäviä tapahtumia ei ole.';

// importing events
$labels['select_calendar'] = 'Valitse kalenteri';
$labels['select_calendar_for_events'] = 'Valitse kalenteri, johon haluat lisätä tapahtumat:';
$labels['add_events'] = 'Lisää tapahtumia';
$labels['add_events_to_calendar'] = 'Lisää tapahtumia kalenteriin';
$labels['events_imported_successfully'] = 'Tapahtumat tuotu onnistuneesti';
$labels['events_imported_with_errors'] = 'Jotakin muuta tapahtumaa ei voitu tuoda.';
$labels['error_importing_events'] = 'Tapahtumien tuonnissa kalenteriin tapahtui virhe.';

// editing events
$labels['calendar'] = 'Kalenteri';
$labels['new_event'] = 'Uusi tapahtuma';
$labels['summary'] = 'Yhteenveto';
$labels['recurrence'] = 'Toistuminen';
$labels['title'] = 'Otsikko';
$labels['location'] = 'Sijainti';
$labels['description'] = 'Kuvaus';
$labels['comment'] = 'Kommentti';
$labels['url'] = 'URL';
$labels['name'] = 'Nimi';
$labels['start'] = 'Aloita';
$labels['end'] = 'End';
$labels['show_timezones'] = 'Näytä aikavyöhykkeet';
$labels['reset_timezones'] = 'Nollaa aikavyöhykkeet oletusasetuksiin';
$labels['all_day'] = 'Koko päivä';
$labels['use_calendar_colors'] = 'Käytä kalenterin värejä';
$labels['color'] = 'Väri';
$labels['background'] = 'Tausta';
$labels['text'] = 'Teksti';
$labels['show_me_as'] = 'Näytä minulle';
$labels['available'] = 'Saatavilla';
$labels['busy'] = 'Kiireinen';
$labels['visibility'] = 'Näkyvyys';
$labels['calendar_default'] = 'Kalenterin oletusarvo';
$labels['public'] = 'Julkinen';
$labels['private'] = 'Yksityinen';
$labels['repeat'] = 'Toista';
$labels['repeat_every'] = 'Toista joka';
$labels['never'] = 'Ei koskaan';
$labels['daily'] = 'Päivittäin';
$labels['weekly'] = 'Viikoittain';
$labels['monthly'] = 'Kuukausittain';
$labels['yearly'] = 'Vuosittain';
$labels['days'] = 'päivä(t)';
$labels['weeks'] = 'viikko(t)';
$labels['months'] = 'kuukausi(t)';
$labels['years'] = 'vuosi(t)';
$labels['repeat_on'] = 'Toista';
$labels['repeat_by'] = 'Toista by';
$labels['day_of_month'] = 'kuukauden päivä';
$labels['day_of_week'] = 'viikonpäivä';
$labels['not_specified'] = 'Ei määritelty';
$labels['first'] = 'Ensimmäinen';
$labels['second'] = 'Toinen';
$labels['third'] = 'Kolmas';
$labels['fourth'] = 'Neljäs';
$labels['fifth'] = 'Viides';
$labels['second_to_last'] = 'Toiseksi viimeinen';
$labels['last'] = 'Viimeinen';
$labels['attachment_too_large'] = 'Liitetiedosto on liian suuri. Suurin sallittu koko on $s.';
$labels['bg_color'] = 'Taustan väri';
$labels['tx_color'] = 'Tekstin väri';
$labels['add_notification'] = 'Lisää ilmoitus';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'Tällä tapahtumalla ei ole ilmoituksia.';
$labels['minutes'] = 'minuuttia';
$labels['hours'] = 'tuntia';
$labels['link'] = 'Linkki';
$labels['attach_file_from_web'] = 'Liitä tiedosto';
$labels['attachment_url'] = 'Liitteen URL-osoite';
$labels['invalid_url'] = 'Tämä URL-osoite on virheellinen.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Kahdenna';
$labels['duplicate_suffix'] = '(kopio)';
$labels['no_notifications'] = 'Tällä tapahtumalla ei ole ilmoituksia.';
$labels['this_calendar_is_hidden'] = 'Tämä kalenteri on piilotettu. Tallennetut tapahtumat näkyvät vasta, kun kalenteri on näkyvissä.';
$labels['make_calendar_visible'] = 'Aseta tämä kalenteri näkyväksi.';

// recurrence
$labels['recurrence_range'] = 'Toistuvuusalue';
$labels['repeat_forever'] = 'Toista ikuisesti';
$labels['repeat_every_weekday'] = 'Toista joka arkipäivä';
$labels['last_day'] = 'Viimeinen päivä';
$labels['every'] = 'Jokainen';
$labels['weekday'] = 'Arkipäivä';
$labels['weekend_day'] = 'Viikonloppupäivä';
$labels['day_of_week'] = 'viikonpäivä';
$labels['day_of_month'] = 'kuukauden päivä';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Loppu jälkeen';
$labels['occurrences'] = 'tapahtumat';

// end recurrence on date:
$labels['end_by_date'] = 'Loppupäivämäärä';
$labels['removed_events_title'] = 'Toistuvista sarjoista poistetut tapahtumat';
$labels['restore'] = 'Palauta';

// settings page
$labels['setting_view'] = 'Alkuperäinen kalenterinäkymä';
$labels['setting_first_day'] = 'Viikko alkaa';
$labels['setting_agenda_week_span'] = 'Asialistan kesto (viikkoa)';
$labels['setting_slot_duration'] = 'Päivän aikaväli (minuuttia)';
$labels['setting_scroll_time'] = 'Vieritä aikaa';
$labels['setting_calendar'] = 'Tapahtumien oletuskalenteri';
$labels['setting_refresh'] = 'Päivitä kalenterit';
$labels['setting_week_numbers'] = 'Viikon numerot pienessä kalenterissa';
$labels['setting_event_border'] = 'Luokan värit tapahtuman rajoina';
$labels['show_sunrise'] = 'Näytä auringonnousu kuukauden näkymässä';
$labels['show_sunset'] = 'Näytä auringonlasku kuukausinäkymässä';
$labels['categories'] = 'Kategoriat';
$labels['category'] = 'Luokka';
$labels['no_category'] = 'Ei luokkaa';
$labels['add_category'] = 'Lisää luokka';
$labels['setting_alarm_sound'] = 'Ilmoitusääni';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambu';
$labels['alarm_sound_bells'] = 'Bells';
$labels['alarm_sound_chord'] = 'Sointu';
$labels['alarm_sound_circles'] = 'Ympyrät';
$labels['alarm_sound_glass'] = 'Lasi';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metalli';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Pilli';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Toista ääni';

// alarms
$labels['upcoming_events'] = 'Tulevat tapahtumat';
$labels['dismiss'] = 'Vapauttakaa';
$labels['dismiss_all'] = 'Poistakaa kaikki';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minuuttia';
$labels['10_minutes'] = '10 minuuttia';
$labels['20_minutes'] = '20 minuuttia';
$labels['30_minutes'] = '30 minuuttia';
$labels['1_hour'] = '1 tunti';
$labels['2_hours'] = '2 tuntia';
$labels['tomorrow'] = 'Huomiseen asti';
$labels['next_week'] = 'Ensi viikkoon asti';

// sidebar
$labels['setting_show_xcalendar'] = 'Näytä kalenteri Agenda';
$labels['todays_agenda'] = 'Tämän päivän asialista';
$labels['no_events_today'] = 'Tänään ei ole suunniteltuja tapahtumia.';
$labels['sidebar_more'] = 'Lisää...';

// event invitation
$labels['itip_title_request'] = 'Sinut on kutsuttu tapahtumaan.';
$labels['itip_title_modified'] = 'Tätä tapahtumaa on muutettu.';
$labels['itip_title_reply_accepted'] = '$u on hyväksynyt kutsusi';
$labels['itip_title_reply_tentative'] = '$u on alustavasti hyväksynyt kutsunne';
$labels['itip_title_reply_declined'] = '$u on kieltäytynyt kutsustasi';
$labels['itip_title_cancel'] = 'Tämä tapahtuma on peruttu.';

$labels['itip_email_subject_request'] = 'Kutsu:';
$labels['itip_email_subject_request_modified'] = 'Tapahtuma muutettu:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Peruttu:';

$labels['itip_popup_event_added'] = 'Tapahtuma lisätty kalenteriin.';
$labels['itip_popup_event_updated'] = 'Tapahtuma päivitetty kalenteriin.';
$labels['itip_popup_event_deleted'] = 'Tapahtuma poistettu kalenterista.';
$labels['itip_popup_response_sent'] = 'Järjestäjälle lähetetty vastaus.';

$labels['itip_message_invitation_accepted'] = 'Olet hyväksynyt tämän kutsun.';
$labels['itip_message_invitation_tentative'] = 'Olet alustavasti hyväksynyt tämän kutsun.';
$labels['itip_message_invitation_declined'] = 'Olet kieltäytynyt tästä kutsusta.';
$labels['itip_message_event_deleted'] = 'Tapahtuma on poistettu kalenteristasi.';
$labels['itip_message_event_updated'] = 'Kalenterissasi oleva tapahtuma on päivitetty.';
$labels['itip_message_event_missing'] = 'Tätä tapahtumaa ei ole missään kalenterissasi.';

$labels['itip_accept'] = 'Hyväksy';
$labels['itip_tentative'] = 'Alustava';
$labels['itip_decline'] = 'Lasku';
$labels['itip_change_reponse'] = 'Vaihda vastaus.';
$labels['itip_add_to_calendar'] = 'Lisää kalenteriin';
$labels['itip_reply_to_sender'] = 'Vastaa lähettäjälle';
$labels['itip_dont_import'] = 'Älä lisää kalenteriin';
$labels['itip_update_in_calendar'] = 'Päivitä tapahtuma kalenteriin';
$labels['itip_delete_from_calendar'] = 'Poista tapahtuma kalenterista';

$labels['mail_subject_invitation'] = 'Kutsu:';
$labels['mail_subject_update'] = 'Päivitetty kutsu:';
$labels['mail_subject_cancel'] = 'Tapahtuma peruttu:';

// event email notifications
$labels['mail_title'] = 'Otsikko:';
$labels['mail_start'] = 'Aloita:';
$labels['mail_end'] = 'Loppu:';
$labels['mail_description'] = 'Kuvaus:';
$labels['mail_location'] = 'Sijainti:';

// event attendees
$labels['attendees'] = 'Osallistujat';
$labels['attendee_role'] = 'Rooli';
$labels['attendee_role_required'] = 'Vaadittu';
$labels['attendee_role_optional'] = 'Valinnainen';
$labels['attendee_role_chair'] = 'Puheenjohtaja';
$labels['attendee_role_none'] = 'Muu kuin osallistuja';
$labels['organizer'] = 'Järjestäjä';
$labels['attendance'] = 'Osallistuminen';
$labels['your_status'] = 'Tilasi';
$labels['attendance_going'] = 'Menossa?';
$labels['attendee_status_yes'] = 'Kyllä';
$labels['attendee_status_no'] = 'Ei';
$labels['attendee_status_maybe'] = 'Ehkä';
$labels['attendee_status_delegated'] = 'Delegoitu';
$labels['attendee_status_waiting'] = 'Odottelu';
$labels['attendee_status_waiting_for_confirmation'] = 'Vahvistusta odotetaan';
$labels['attendee_status_info_yes'] = 'Osallistuu';
$labels['attendee_status_info_no'] = 'Ei osallistu';
$labels['attendee_status_info_maybe'] = 'Saattaa osallistua';
$labels['attendee_status_info_delegated'] = 'Valtuutettu läsnäolo';
$labels['attendee_role'] = 'Rooli';
$labels['attendee_role_info'] = 'Ilmoita, onko osallistujan osallistuttava kokoukseen vai toimittava sen puheenjohtajana.';
$labels['attendee_notify'] = 'Ilmoita';
$labels['attendee_notify_info'] = 'Jos valintaruutu on valittuna, osallistujalle lähetetään kutsusähköposti.';
$labels['add_attendee'] = 'Lisää osallistuja';
$labels['attendee_notify_question'] = 'Lähetä ilmoitussähköposteja osallistujille?';
$labels['attendee_notify_textarea'] = 'Lisää valinnainen viesti';
$labels['attendee_notify_send'] = 'Lähetä';
$labels['attendee_notify_dont'] = 'Älä lähetä';
$labels['attendee_notify_cancel'] = 'Peruuta';
$labels['new_attendee_not_saved_warning'] = 'Syötit osallistujan sähköpostiosoitteen, mutta et lisännyt sitä osallistujaluetteloon. Haluatko jatkaa tallennusta ilman tätä osallistujaa?';

// import / export
$labels['import_export'] = 'Tuonti / vienti';
$labels['export_to_file'] = 'Vie tiedostoon';
$labels['export_info'] = 'Napsauta tätä painiketta viedäksesi ja ladataksesi tämän kalenterin tapahtumat iCalendar (ics) -muodossa. Jos kalenterissa on paljon tapahtumia, vienti saattaa kestää hetken.';
$labels['export_no_events'] = 'Tässä kalenterissa ei ole vietäviä tapahtumia.';
$labels['import_from_file'] = 'Tuo tiedostosta';
$labels['import_info'] = 'Napsauta tätä painiketta, jos haluat tuoda tapahtumia tähän kalenteriin iCalendar (ics) -tiedostosta.';

// email notifications
$labels['appointment_reminder'] = 'Ajanvarauksen muistutus';
$labels['email_notification_subject'] = 'Muistutus: $t klo $d ($z)';
$labels['email_notification_cron_error'] = 'Sähköposti-ilmoitukset on poistettu käytöstä. Ota yhteys ylläpitäjään.';
$labels['alarm_before_event_starts'] = 'Ennen tapahtuman alkua';
$labels['alarm_after_event_starts'] = 'Tapahtuman alkamisen jälkeen';
$labels['alarm_before_event_ends'] = 'Ennen tapahtuman päättymistä';
$labels['alarm_after_event_ends'] = 'Tapahtuman päätyttyä';
$labels['alarm_at_specific_time'] = 'Tiettynä ajankohtana';

// search
$labels['specify_at_least_one_calendar'] = 'Määritä vähintään yksi haettava kalenteri.';
$labels['specify_text_to_search'] = 'Kirjoita etsittävä teksti.';
$labels['clear_search_results'] = 'Kirkas';
$labels['search_outdated_message'] = 'Hakutulokset voivat olla vanhentuneita. Harkitse haun suorittamista uudelleen.';
$labels['date_range'] = 'Päivämääräalue';
$labels['date_range_subtitle'] = 'Päivämääräalue:';
$labels['search_results'] = 'Löydetyt tulokset: $n';
$labels['modify_search_options'] = 'Yritä muuttaa hakuvaihtoehtoja.';
$labels['show_shortcuts'] = 'Näytä pikanäppäimet';
$labels['hide_shortcuts'] = 'Piilota pikanäppäimet';
$labels['this_month'] = 'Tässä kuussa';
$labels['this_year'] = 'Tänä vuonna';
$labels['past_30_days'] = 'Viimeiset 30 päivää';
$labels['next_30_days'] = 'Seuraavat 30 päivää';
$labels['past_6_months'] = 'Viimeiset 6 kuukautta';
$labels['next_6_months'] = 'Seuraavat 6 kuukautta';

// calendar grid
$labels['cannot_load_events'] = 'Kalenterin "$c" tapahtumia ei voida ladata.';
