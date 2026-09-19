<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Kalendārs';
$labels['app_menu_title'] = 'Kalendārs';

// menu
$labels['create_event'] = 'Izveidot notikumu';
$labels['create_event_button'] = 'Jauns notikums';
$labels['day'] = 'Diena';
$labels['week'] = 'Nedēļa';
$labels['month'] = 'Mēnesis';
$labels['agenda'] = 'Darba kārtība';

// editing calendars
$labels['calendars'] = 'Kalendāri';
$labels['my_calendar'] = 'Mans kalendārs';
$labels['create_calendar'] = 'Izveidot kalendāru';
$labels['new_calendar'] = 'Jauns kalendārs';
$labels['edit_calendar'] = 'Rediģēt kalendāru';
$labels['remove_calendar'] = 'Dzēst kalendāru';
$labels['confirm_remove_calendar'] = 'Vai esat pārliecināts, ka vēlaties dzēst šo kalendāru?';
$labels['calendar_removed'] = 'Kalendārs dzēsts.';
$labels['notifications'] = 'Paziņojumi';
$labels['default_event_visibility'] = 'Notikumu redzamība pēc noklusējuma';
$labels['time_zone'] = 'Kalendāra laika josla';
$labels['share_calendar'] = 'Koplietošanas kalendārs';
$labels['permissions'] = 'Atļaujas';
$labels['permission_publish_calendar'] = 'Publicēt kalendāru';
$labels['permission_share_calendar'] = 'Koplietošanas kalendārs';
$labels['permission_edit_events'] = 'Rediģēt notikumus';
$labels['permission_see_details'] = 'Skatīt informāciju';
$labels['permission_publish_calendar_info'] = 'Ja ir atzīmēts, lietotājs varēs publicēt kalendāru.';
$labels['permission_share_calendar_info'] = 'Ja ir atzīmēts, lietotājs varēs kopīgot kalendāru ar citiem lietotājiem.';
$labels['permission_edit_events_info'] = 'Ja ir atzīmēts, lietotājs varēs rediģēt un dzēst kalendāra notikumus.';
$labels['permission_see_details_info'] = 'Ja ir atzīmēts, tiks kopīgota visa notikuma informācija. Ja izvēles rūtiņa nav atzīmēta, tiks kopīgota tikai pieejamā/aizņemtā informācija.';
$labels['share_send_notification'] = 'Paziņot';
$labels['share_send_notification_info'] = 'Ja ir atzīmēts, lietotājam tiks nosūtīts e-pasta vēstule, kurā viņš tiks informēts par jauno koplietojamo kalendāru.';
$labels['new_share_not_saved_warning'] = 'Ievadījāt e-pasta adresi, lai kopīgotu kalendāru, bet nepiespiedāt pogu Kopīgot, lai to pievienotu kopīgotajam sarakstam. Vai vēlaties turpināt saglabāt, nepiedaloties kalendārā ar šo lietotāju?';
$labels['share'] = 'Dalīties';
$labels['shared_marker'] = '(Koplietojams)';
$labels['owner'] = 'Īpašnieks';
$labels['publish_calendar'] = 'Publicēt kalendāru';
$labels['create_link'] = 'Izveidot saiti';
$labels['reset'] = 'Atiestatīt';
$labels['remove'] = 'Noņemt';
$labels['publish_full'] = 'Publicējiet visu pasākuma informāciju.';
$labels['publish_busy'] = 'Publicējiet tikai informāciju par brīvajām/aizņemtajām vietām.';
$labels['sync_no_id'] = 'CalDAV opcijas būs pieejamas pēc kalendāra saglabāšanas.';
$labels['share_no_id'] = 'Kalendāra kopīgošanas opcijas būs pieejamas pēc kalendāra saglabāšanas.';
$labels['publish_no_id'] = 'Kalendāra publicēšanas opcijas būs pieejamas pēc kalendāra saglabāšanas.';
$labels['import_no_id'] = 'Importēšanas un eksportēšanas opcijas būs pieejamas pēc kalendāra saglabāšanas.';
$labels['unable_to_save_calendar_data'] = 'Nevar saglabāt kalendāra datus.';
$labels['unable_to_retrieve_calendar_data'] = 'Nevar iegūt kalendāra datus. Kalendārs, iespējams, vairs nav pieejams.';
$labels['unable_add_shared_calendar'] = 'Nevar pievienot koplietojamo kalendāru. Kalendārs, iespējams, vairs nav pieejams.';
$labels['itip_organizer_message'] = 'Jūs esat uzaicināts uz šo pasākumu, bet neesat tā organizators. Jūsu veiktās izmaiņas šajā pasākumā netiks nosūtītas citiem dalībniekiem.';
$labels['cannot_share_with_yourself'] = 'Jūs nevarat kopīgot savu kalendāru ar sevi.';
$labels['cannot_share_with_calendar_owner'] = 'Jūs nevarat kopīgot šo kalendāru ar kalendāra īpašnieku ($e)';
$labels['setting_default_notification_type'] = 'Noklusējuma paziņojumi par jauniem notikumiem';
$labels['calendar_name_cannot_be_empty'] = 'Kalendāra nosaukums nedrīkst būt tukšs.';

// caldav server
$labels['sync_calendar'] = 'Sinhronizēt kalendāru';
$labels['password_too_short'] = 'Parolei jābūt vismaz 10 rakstzīmju garai';
$labels['read_only'] = 'Tikai lasīt';
$labels['add_connection'] = 'Pievienot savienojumu';
$labels['show_details'] = 'Rādīt informāciju';
$labels['hide_details'] = 'Paslēpt informāciju';
$labels['caldav_connection_password'] = 'Parole, kas norādīta, izveidojot šo savienojumu.';
$labels['yes'] = 'Jā';
$labels['no'] = 'Nē';
$labels['confirm_remove_sync'] = 'Vai esat pārliecināts, ka vēlaties šo savienojumu noņemt uz visiem laikiem?';
$labels['remove_sync_error'] = 'Savienojuma noņemšanas laikā radās kļūda.';
$labels['create_new_caldav_connection'] = 'Izveidot jaunu CalDav savienojumu';
$labels['your_caldav_connections'] = 'Jūsu CalDav savienojumi';
$labels['created_at'] = 'Izveidots';
$labels['connected_at'] = 'Pēdējā sinhronizācija';
$labels['never'] = 'Nekad';
$labels['alternate_url'] = 'Alternatīvais URL';
$labels['caldav_password_info'] = 'Drošības apsvērumu dēļ šī parole netiek saglabāta serverī. Ja esat to aizmirsis, dzēsiet šo savienojumu un izveidojiet jaunu.';
$labels['caldav_url_info'] = 'Izmantojiet šo URL ar programmām, kas atbalsta kalendāra atklāšanu, piemēram, iOS un Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Izmantojiet šo URL, piemēram, lietojot lietotnes, kas nedarbojas, izmantojot iepriekš minēto īso url: Outlook CalDav sinhronizators, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'Nepareiza CalDAV servera URL adrese.';
$labels['cannot_resolve_caldav_host'] = 'Neizdodas noteikt CalDAV servera uzņēmējdatoru.';
$labels['private_caldav_addresses_not_allowed'] = 'Privātas un rezervētas CalDAV adreses nav atļautas.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Pievienot koplietojamo kalendāru';
$labels['shared_calendars'] = 'Koplietošanas kalendāri';
$labels['no_shared_calendars'] = 'Koplietojamie kalendāri, kurus varat pievienot, nav pieejami.';
$labels['add_to_my_calendars'] = 'Pievienot maniem kalendāriem';
$labels['remove_from_my_calendars'] = 'Dzēst no maniem kalendāriem';
$labels['unshare'] = 'Atcelt kopīgošanu';
$labels['unshare_warning'] = 'Ja šo kalendāru atcelsiet no koplietošanas, tas pazudīs no šī saraksta, un to vairs nevarēsiet pievienot.';
$labels['calendar_name'] = 'Kalendāra nosaukums';
$labels['calendar_owner'] = 'Kalendāra īpašnieks';
$labels['shared_date'] = 'Koplietošanas vietne';
$labels['unable_to_share'] = 'Kalendāru nevar kopīgot ar šādiem lietotājiem, jo viņu e-pasta konti atrodas attālā serverī:';

// shared calendar email
$labels['shared_email_subject'] = '$u ir kopīgojusi ar jums kalendāru';
$labels['shared_email_body'] = 'Lietotājs $u jums ir piešķīris piekļuvi kalendāram ar nosaukumu "$c". Lai pievienotu šo kalendāru aktīvo kalendāru sarakstam, noklikšķiniet uz saites zemāk:';
$labels['shared_email_body_cpanel'] = 'Lietotājs $u jums ir piešķīris piekļuvi kalendāram ar nosaukumu "$c". Lai pievienotu šo kalendāru aktīvo kalendāru sarakstam, ieejiet Roundcube, atveriet Kalendārs, noklikšķiniet uz plusa ikonas sānjoslā un izvēlieties Pievienot koplietojamo kalendāru.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Svētku kalendāri';
$labels['edit_holiday_calendar'] = 'Rediģēt brīvdienu kalendāru';
$labels['religious_holidays'] = 'Reliģiskie svētki';
$labels['country_holidays'] = 'Lauku brīvdienas';
$labels['add'] = 'Pievienot';
$labels['no_more_holidays_allowed'] = 'Nevarat pievienot vairāk brīvdienu kalendāru.';
$labels['cannot_load_holidays'] = 'Nevar ielādēt brīvdienas no Google servera.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Pievienot Dzimšanas dienu kalendāru';
$labels['edit_birthday_calendar'] = 'Rediģēt Dzimšanas dienu kalendāru';
$labels['contact_birthdays'] = 'Kontaktpersona Dzimšanas dienas';
$labels['contact_birthdays_info'] = 'Šis kalendārs iegūst dzimšanas dienu informāciju no kontaktu saraksta un parāda to kā kalendāra notikumus.';
$labels['birthday_event_title'] = 'Dzimšanas diena: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Pievienot Google kalendāru';
$labels['edit_google_calendar'] = 'Google kalendāra rediģēšana';
$labels['google_id'] = 'Google kalendāra ID';
$labels['new_google_calendar'] = 'Jauns Google kalendārs';
$labels['specify_google_calendar_id'] = 'Lūdzu, ievadiet Google kalendāra ID';
$labels['calendar_id_format_error'] = 'Google kalendāra ID jāietver @ rakstzīme.';
$labels['cannot_load_google_calendar'] = 'Google kalendāru "$c" nevar ielādēt. Pārliecinieties, ka kalendāra ID ir pareizs un ka kalendārs ir publiskots.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Pievienot CalDAV kalendāru';
$labels['edit_caldav_calendar'] = 'Rediģēt CalDAV kalendāru';
$labels['add_caldav_calendar_information'] = 'Lai abonētu CalDAV kalendāru, norādiet CalDAV servera informāciju un noklikšķiniet uz Meklēt kalendārus.';
$labels['caldav_url'] = 'CalDAV URL';
$labels['find_calendars'] = 'Kalendāru meklēšana';
$labels['searching_for_calendars'] = 'Kalendāru meklēšana, lūdzu, uzgaidiet...';
$labels['select_calendars'] = 'Atlasiet kalendārus, kurus vēlaties pievienot:';
$labels['read_only_status_yes'] = 'Jā';
$labels['read_only_status_no'] = 'Nē';
$labels['subscribed'] = 'Abonēts';

$labels['refresh_calendar'] = 'Atjaunināt kalendāru';
$labels['refresh_every_minute'] = 'Katru minūti';
$labels['refresh_every_5_minutes'] = 'Ik pēc 5 minūtēm';
$labels['refresh_every_15_minutes'] = 'Ik pēc 15 minūtēm';
$labels['refresh_every_30_minutes'] = 'Ik pēc 30 minūtēm';
$labels['refresh_every_60_minutes'] = 'Ik pēc 60 minūtēm';
$labels['refresh_never'] = 'Nekad';

$labels['cannot_connect_to_caldav_server'] = 'Nevar izveidot savienojumu ar CalDAV serveri "$n".';
$labels['caldav_client_error_url'] = 'Nevar izveidot savienojumu ar CalDAV serveri. Pārbaudiet servera URL.';
$labels['caldav_client_error_credentials'] = 'Nepareizs CalDAV lietotājvārds vai parole.';
$labels['caldav_client_error_empty'] = 'Serveris neatdeva nevienu kalendāru, ko var pievienot. Pārbaudiet CalDAV URL.';
$labels['caldav_client_error_server_info'] = 'Nepareiza CalDAV servera informācija.';
$labels['caldav_client_error_add_calendar'] = 'Nevar saglabāt kalendāru "$n".';
$labels['caldav_client_error_delete_event'] = 'Nevar dzēst notikumu';
$labels['caldav_client_error_save_event'] = 'CalDAV notikumu nevar saglabāt: iespējams, tas ir pārvietots vai dzēsts, vai arī kalendārs nav pieejams.';
$labels['caldav_client_error_calendar_added'] = 'Kalendārs "$n" jau ir jūsu kalendāru sarakstā.';

// previewing event
$labels['remove_recurring_event'] = 'Dzēst atkārtotu notikumu';
$labels['remove_one'] = 'Dzēst tikai šo gadījumu';
$labels['remove_all'] = 'Dzēst visus gadījumus';
$labels['edit_event'] = 'Rediģēt notikumu';
$labels['event_removed'] = 'Pasākums dzēsts.';
$labels['go_to_url'] = 'Pāriet uz URL';
$labels['confirm_remove_event'] = 'Vai esat pārliecināts, ka vēlaties dzēst šo notikumu?';

// calendar/agenda grid
$labels['no_events'] = 'Nav rādāmu notikumu.';

// importing events
$labels['select_calendar'] = 'Izvēlieties kalendāru';
$labels['select_calendar_for_events'] = 'Izvēlieties kalendāru, kuram vēlaties pievienot notikumus:';
$labels['add_events'] = 'Pievienot notikumus';
$labels['add_events_to_calendar'] = 'Pasākumu pievienošana kalendāram';
$labels['events_imported_successfully'] = 'Notikumi importēti veiksmīgi';
$labels['events_imported_with_errors'] = 'Kādu citu notikumu nevarēja importēt.';
$labels['error_importing_events'] = 'Importējot notikumus kalendārā, tika pieļauta kļūda';

// editing events
$labels['calendar'] = 'Kalendārs';
$labels['new_event'] = 'Jauns notikums';
$labels['summary'] = 'Kopsavilkums';
$labels['recurrence'] = 'Atkārtošanās';
$labels['title'] = 'Nosaukums';
$labels['location'] = 'Atrašanās vieta';
$labels['description'] = 'Apraksts';
$labels['comment'] = 'Komentārs';
$labels['url'] = 'URL';
$labels['name'] = 'Nosaukums';
$labels['start'] = 'Sākt';
$labels['end'] = 'Izbeigt';
$labels['show_timezones'] = 'Rādīt laika joslas';
$labels['reset_timezones'] = 'Laika joslu atiestatīšana uz noklusējuma';
$labels['all_day'] = 'Visu dienu';
$labels['use_calendar_colors'] = 'Kalendāra krāsu izmantošana';
$labels['color'] = 'Krāsa';
$labels['background'] = 'Pamatinformācija';
$labels['text'] = 'Teksts';
$labels['show_me_as'] = 'Parādiet mani kā';
$labels['available'] = 'Pieejams';
$labels['busy'] = 'Aizņemts';
$labels['visibility'] = 'Redzamība';
$labels['calendar_default'] = 'Kalendāra noklusējuma iestatījumi';
$labels['public'] = 'Publiski';
$labels['private'] = 'Privāts';
$labels['repeat'] = 'Atkārtojiet';
$labels['repeat_every'] = 'Atkārtojiet katru';
$labels['never'] = 'Nekad';
$labels['daily'] = 'Dienas';
$labels['weekly'] = 'Nedēļas';
$labels['monthly'] = 'Ikmēneša';
$labels['yearly'] = 'Gada';
$labels['days'] = 'diena(-as)';
$labels['weeks'] = 'nedēļa(-as)';
$labels['months'] = 'mēnesis(-i)';
$labels['years'] = 'gads(-i)';
$labels['repeat_on'] = 'Atkārtojiet';
$labels['repeat_by'] = 'Atkārtot ar';
$labels['day_of_month'] = 'Mēneša diena';
$labels['day_of_week'] = 'Nedēļas diena';
$labels['not_specified'] = 'Nav norādīts';
$labels['first'] = 'Pirmais';
$labels['second'] = 'Otrais';
$labels['third'] = 'Trešais';
$labels['fourth'] = 'Ceturtais';
$labels['fifth'] = 'Piektais';
$labels['second_to_last'] = 'Priekšpēdējais';
$labels['last'] = 'Pēdējais';
$labels['attachment_too_large'] = 'Pielikuma fails ir pārāk liels. Maksimālais atļautais izmērs ir $s.';
$labels['bg_color'] = 'Fona krāsa';
$labels['tx_color'] = 'Teksta krāsa';
$labels['add_notification'] = 'Pievienot paziņojumu';
$labels['popup'] = 'Iznirstošie logi';
$labels['no_notifications'] = 'Šim notikumam nav paziņojumu.';
$labels['minutes'] = 'protokols';
$labels['hours'] = 'stundas';
$labels['link'] = 'Saite';
$labels['attach_file_from_web'] = 'Pievienot failu';
$labels['attachment_url'] = 'Pielikuma URL';
$labels['invalid_url'] = 'Šis URL ir nederīgs.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google disks';
$labels['duplicate'] = 'Dublēt';
$labels['duplicate_suffix'] = '(dublikāts)';
$labels['no_notifications'] = 'Šim notikumam nav paziņojumu.';
$labels['this_calendar_is_hidden'] = 'Šis kalendārs ir paslēpts. Saglabātie notikumi netiks parādīti, kamēr kalendārs nebūs redzams.';
$labels['make_calendar_visible'] = 'Padariet šo kalendāru redzamu.';

// recurrence
$labels['recurrence_range'] = 'Atkārtošanās diapazons';
$labels['repeat_forever'] = 'Atkārtojiet uz visiem laikiem';
$labels['repeat_every_weekday'] = 'Atkārtojiet katru darbdienu';
$labels['last_day'] = 'Pēdējā diena';
$labels['every'] = 'Katrs';
$labels['weekday'] = 'Nedēļas diena';
$labels['weekend_day'] = 'Nedēļas nogales diena';
$labels['day_of_week'] = 'Nedēļas diena';
$labels['day_of_month'] = 'Mēneša diena';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Beigas pēc';
$labels['occurrences'] = 'notikums(-i)';

// end recurrence on date:
$labels['end_by_date'] = 'Beidzas līdz datumam';
$labels['removed_events_title'] = 'Notikumi, kas izņemti no atkārtotām sērijām';
$labels['restore'] = 'Atjaunot';

// settings page
$labels['setting_view'] = 'Sākotnējais kalendāra skats';
$labels['setting_first_day'] = 'Nedēļas sākums';
$labels['setting_agenda_week_span'] = 'Darba kārtības ilgums (nedēļas)';
$labels['setting_slot_duration'] = 'Dienas laika intervāls (minūtes)';
$labels['setting_scroll_time'] = 'Ritiniet uz laiku';
$labels['setting_calendar'] = 'Noklusējuma notikumu kalendārs';
$labels['setting_refresh'] = 'Atjaunināt kalendārus';
$labels['setting_week_numbers'] = 'Nedēļu numuri mazajā kalendārā';
$labels['setting_event_border'] = 'Kategorijas krāsas kā notikuma robežas';
$labels['show_sunrise'] = 'Mēneša skatā parādīt saullēktu';
$labels['show_sunset'] = 'Mēneša skatā parādīt saulrietu';
$labels['categories'] = 'Kategorijas';
$labels['category'] = 'Kategorija';
$labels['no_category'] = 'Nav kategorijas';
$labels['add_category'] = 'Pievienot kategoriju';
$labels['setting_alarm_sound'] = 'Paziņojuma skaņa';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambusa';
$labels['alarm_sound_bells'] = 'Zvani';
$labels['alarm_sound_chord'] = 'Akords';
$labels['alarm_sound_circles'] = 'Apļi';
$labels['alarm_sound_glass'] = 'Stikls';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metāls';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Zvaigžņu ceļš';
$labels['alarm_sound_whistle'] = 'Svilpīte';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Atskaņot skaņu';

// alarms
$labels['upcoming_events'] = 'Gaidāmie pasākumi';
$labels['dismiss'] = 'Atbrīvot';
$labels['dismiss_all'] = 'Atbrīvot visus';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minūtes';
$labels['10_minutes'] = '10 minūtes';
$labels['20_minutes'] = '20 minūtes';
$labels['30_minutes'] = '30 minūtes';
$labels['1_hour'] = '1 stunda';
$labels['2_hours'] = '2 stundas';
$labels['tomorrow'] = 'Līdz rītdienai';
$labels['next_week'] = 'Līdz nākamajai nedēļai';

// sidebar
$labels['setting_show_xcalendar'] = 'Rādīt kalendāra darba kārtību';
$labels['todays_agenda'] = 'Šodienas darba kārtība';
$labels['no_events_today'] = 'Šodien nav plānoti nekādi pasākumi.';
$labels['sidebar_more'] = 'Vairāk...';

// event invitation
$labels['itip_title_request'] = 'Jūs esat aicināts uz pasākumu.';
$labels['itip_title_modified'] = 'Šis notikums ir mainīts.';
$labels['itip_title_reply_accepted'] = '$u ir pieņēmis jūsu ielūgumu';
$labels['itip_title_reply_tentative'] = '$u ir provizoriski pieņēmis jūsu ielūgumu';
$labels['itip_title_reply_declined'] = '$u ir noraidījis jūsu ielūgumu';
$labels['itip_title_cancel'] = 'Šis pasākums ir atcelts.';

$labels['itip_email_subject_request'] = 'Ielūgums:';
$labels['itip_email_subject_request_modified'] = 'Notikums tika mainīts:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Atcelts:';

$labels['itip_popup_event_added'] = 'Pasākums pievienots kalendāram.';
$labels['itip_popup_event_updated'] = 'Notikums atjaunināts kalendārā.';
$labels['itip_popup_event_deleted'] = 'Notikums dzēsts no kalendāra.';
$labels['itip_popup_response_sent'] = 'Organizatoram nosūtīta atbilde.';

$labels['itip_message_invitation_accepted'] = 'Jūs esat pieņēmuši šo ielūgumu.';
$labels['itip_message_invitation_tentative'] = 'Jūs provizoriski esat pieņēmis šo ielūgumu.';
$labels['itip_message_invitation_declined'] = 'Jūs esat atteicies no šī ielūguma.';
$labels['itip_message_event_deleted'] = 'Notikums ir dzēsts no jūsu kalendāra.';
$labels['itip_message_event_updated'] = 'Notikums jūsu kalendārā ir atjaunināts.';
$labels['itip_message_event_missing'] = 'Šis notikums nav iekļauts nevienā no jūsu kalendāriem.';

$labels['itip_accept'] = 'Pieņemt';
$labels['itip_tentative'] = 'Provizorisks';
$labels['itip_decline'] = 'Samazinājums';
$labels['itip_change_reponse'] = 'Reakcija uz izmaiņām.';
$labels['itip_add_to_calendar'] = 'Pievienot kalendāram';
$labels['itip_reply_to_sender'] = 'Atbildēt sūtītājam';
$labels['itip_dont_import'] = 'Nepievienot kalendāru';
$labels['itip_update_in_calendar'] = 'Atjaunināt notikumu kalendārā';
$labels['itip_delete_from_calendar'] = 'Pasākuma dzēšana no kalendāra';

$labels['mail_subject_invitation'] = 'Ielūgums:';
$labels['mail_subject_update'] = 'Atjaunināts uzaicinājums:';
$labels['mail_subject_cancel'] = 'Pasākums atcelts:';

// event email notifications
$labels['mail_title'] = 'Nosaukums:';
$labels['mail_start'] = 'Sākums:';
$labels['mail_end'] = 'Nobeigums:';
$labels['mail_description'] = 'Apraksts:';
$labels['mail_location'] = 'Atrašanās vieta:';

// event attendees
$labels['attendees'] = 'Dalībnieki';
$labels['attendee_role'] = 'Loma';
$labels['attendee_role_required'] = 'Nepieciešams';
$labels['attendee_role_optional'] = 'Pēc izvēles';
$labels['attendee_role_chair'] = 'Krēsls';
$labels['attendee_role_none'] = 'Neiesaistītās personas';
$labels['organizer'] = 'Organizators';
$labels['attendance'] = 'Apmeklējums';
$labels['your_status'] = 'Jūsu statuss';
$labels['attendance_going'] = 'Dodies?';
$labels['attendee_status_yes'] = 'Jā';
$labels['attendee_status_no'] = 'Nē';
$labels['attendee_status_maybe'] = 'Varbūt';
$labels['attendee_status_delegated'] = 'Deleģētā';
$labels['attendee_status_waiting'] = 'Gaidīšana';
$labels['attendee_status_waiting_for_confirmation'] = 'Gaidām apstiprinājumu';
$labels['attendee_status_info_yes'] = 'Apmeklēs';
$labels['attendee_status_info_no'] = 'Neapmeklēs';
$labels['attendee_status_info_maybe'] = 'Varētu apmeklēt';
$labels['attendee_status_info_delegated'] = 'Deleģētais apmeklējums';
$labels['attendee_role'] = 'Loma';
$labels['attendee_role_info'] = 'Norādiet, vai dalībniekam ir jāpiedalās sanāksmē vai jāvada sanāksme.';
$labels['attendee_notify'] = 'Paziņot';
$labels['attendee_notify_info'] = 'Ja ir atzīmēts, dalībniekam tiks nosūtīts uzaicinājuma e-pasts.';
$labels['add_attendee'] = 'Pievienot dalībnieku';
$labels['attendee_notify_question'] = 'Nosūtīt paziņojuma e-pasta vēstules dalībniekiem?';
$labels['attendee_notify_textarea'] = 'Pievienot neobligātu ziņojumu';
$labels['attendee_notify_send'] = 'Sūtīt';
$labels['attendee_notify_dont'] = 'Nesūtiet';
$labels['attendee_notify_cancel'] = 'Atcelt';
$labels['new_attendee_not_saved_warning'] = 'Jūs ievadījāt dalībnieka e-pasta adresi, bet neesat to pievienojis dalībnieku sarakstam. Vai vēlaties turpināt saglabāšanu bez šī dalībnieka?';

// import / export
$labels['import_export'] = 'Imports / eksports';
$labels['export_to_file'] = 'Eksportēt uz failu';
$labels['export_info'] = 'Noklikšķiniet uz šīs pogas, lai eksportētu un lejupielādētu šī kalendāra notikumus iCalendar (ics) formātā. Ja kalendārā ir daudz notikumu, eksportēšanas process var aizņemt dažus mirkļus.';
$labels['export_no_events'] = 'Šajā kalendārā nav eksportējamu notikumu.';
$labels['import_from_file'] = 'Importēt no faila';
$labels['import_info'] = 'Noklikšķiniet uz šīs pogas, lai importētu notikumus šajā kalendārā no iCalendar (ics) faila.';

// email notifications
$labels['appointment_reminder'] = 'Tikšanās atgādinājums';
$labels['email_notification_subject'] = 'Atgādinājums: $t pie $d ($z)';
$labels['email_notification_cron_error'] = 'E-pasta paziņojumi ir atspējoti. Sazinieties ar administratoru.';
$labels['alarm_before_event_starts'] = 'Pirms pasākuma sākuma';
$labels['alarm_after_event_starts'] = 'Pēc pasākuma sākuma';
$labels['alarm_before_event_ends'] = 'Pirms pasākuma beigām';
$labels['alarm_after_event_ends'] = 'Pēc pasākuma beigām';
$labels['alarm_at_specific_time'] = 'Konkrētā laikā';

// search
$labels['specify_at_least_one_calendar'] = 'Norādiet vismaz vienu kalendāru, kurā meklēt.';
$labels['specify_text_to_search'] = 'Ievadiet meklēšanai vajadzīgo tekstu.';
$labels['clear_search_results'] = 'Skaidrs';
$labels['search_outdated_message'] = 'Meklēšanas rezultāti var būt novecojuši. Apsveriet atkārtotas meklēšanas iespēju.';
$labels['date_range'] = 'Datuma diapazons';
$labels['date_range_subtitle'] = 'Datumu diapazons:';
$labels['search_results'] = 'Atrastie rezultāti: $n';
$labels['modify_search_options'] = 'Mēģiniet mainīt meklēšanas opcijas.';
$labels['show_shortcuts'] = 'Rādīt īsceļus';
$labels['hide_shortcuts'] = 'Slēpt īsceļus';
$labels['this_month'] = 'Šomēnes';
$labels['this_year'] = 'Šogad';
$labels['past_30_days'] = 'Pēdējās 30 dienas';
$labels['next_30_days'] = 'Nākamās 30 dienas';
$labels['past_6_months'] = 'Pēdējie 6 mēneši';
$labels['next_6_months'] = 'Nākamie 6 mēneši';

// calendar grid
$labels['cannot_load_events'] = 'Nevar ielādēt notikumus kalendāram "$c".';
