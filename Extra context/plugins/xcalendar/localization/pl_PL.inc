<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Kalendarz';
$labels['app_menu_title'] = 'Kalendarz';

// menu
$labels['create_event'] = 'Stwórz wydarzenie';
$labels['create_event_button'] = 'Stwórz';
$labels['day'] = 'Dzień';
$labels['week'] = 'Tydzień';
$labels['month'] = 'Miesiąc';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Kalendarze';
$labels['my_calendar'] = 'Mój Kalendarz';
$labels['create_calendar'] = 'Stwórz kalendarz';
$labels['new_calendar'] = 'Nowy kalendarz';
$labels['edit_calendar'] = 'Edytuj kalendarz';
$labels['remove_calendar'] = 'Usuń kalendarz';
$labels['confirm_remove_calendar'] = 'Na pewno chcesz usunąć ten kalendarz?';
$labels['calendar_removed'] = 'Kalendarz usunięty';
$labels['notifications'] = 'Powiadomienia';
$labels['default_event_visibility'] = 'Domyślna widoczność wydarzenia';
$labels['time_zone'] = 'Strefa czasowa kalendarza';
$labels['share_calendar'] = 'Udostępnij kalendarz';
$labels['permissions'] = 'Pozwolenia';
$labels['permission_publish_calendar'] = 'Publikacja';
$labels['permission_share_calendar'] = 'Udostępnianie';
$labels['permission_edit_events'] = 'Edycja';
$labels['permission_see_details'] = 'Podgląd';
$labels['permission_publish_calendar_info'] = 'Jeżeli ta opcja będzie włączona, użytkownik będzie mógł opublikować kalendarz.';
$labels['permission_share_calendar_info'] = 'Jeżeli ta opcja będzie włączona, użytkownik będzie mógł udostępnić kalendarz innym użytkownikom.';
$labels['permission_edit_events_info'] = 'Jeżeli ta opcja będzie włączona, użytkownik będzie mógł edytować i usuwać wydarzenia.';
$labels['permission_see_details_info'] = 'Jeżeli ta opcja będzie włączona, użytkownik będzie widział wszystkie informacje dotyczące wydarzenia. Jeżeli opcja będzie wyłączona, użytkownik będzie widział wyłącznie informacje o czasie zajętym/wolnym.';
$labels['share_send_notification'] = 'Powiadom';
$labels['share_send_notification_info'] = 'Jeśli zostanie zaznaczone, do użytkownika zostanie wysłany e-mail informujący go o nowo udostępnionym kalendarzu.';
$labels['new_share_not_saved_warning'] = 'Wpisałeś e-mail, aby udostępnić kalendarz, ale nie kliknąłeś przycisku Udostępnij, aby dodać go do udostępnionej listy. Kontynuować zapisywanie bez dzielenia się kalendarzem z określonym użytkownikiem?';
$labels['share'] = 'Udostępnij';
$labels['shared_marker'] = '(Udostępniony)';
$labels['owner'] = 'Właściciel';
$labels['publish_calendar'] = 'Opublikuj kalendarz';
$labels['create_link'] = 'Utwórz łącze';
$labels['reset'] = 'Resetuj';
$labels['remove'] = 'Usuń';
$labels['publish_full'] = 'Opublikuj informacje o wydarzeniach.';
$labels['publish_busy'] = 'Opublikuj tylko informacje o czasie zajętym/wolnym.';
$labels['sync_no_id'] = 'Opcje CalDAV staną się dostępne po zapisaniu kalendarza.';
$labels['share_no_id'] = 'Opcje udostępniania będą widoczne po zapisaniu kalendarza.';
$labels['publish_no_id'] = 'Opcje publikowania będą widoczne po zapisaniu kalendarza.';
$labels['import_no_id'] = 'Opcje importu i eksportu będą dostępne po zapisaniu kalendarza.';
$labels['unable_to_save_calendar_data'] = 'Nie udało się zapisać danych kalendarza.';
$labels['unable_to_retrieve_calendar_data'] = 'Nie można wczytać kalendarza. Ten kalendarz może być już niedostępny.';
$labels['unable_add_shared_calendar'] = 'Nie można dodać udostępnionego kalendarza. Ten kalendarz może być już niedostępny.';
$labels['itip_organizer_message'] = 'Masz zaproszenie na to wydarzenie ale nie jesteś organizatorem. Zamiany, które zrobisz nie zostaną wysłane do innych zaproszonych osób.';
$labels['cannot_share_with_yourself'] = 'Nie możesz udostępnić sobie własnego kalendarza.';
$labels['cannot_share_with_calendar_owner'] = 'Nie możesz udostępnić tego kalendarza jego właścicielowi ($e)';
$labels['setting_default_notification_type'] = 'Domyślne powiadomienia dla nowych zdarzeń';
$labels['calendar_name_cannot_be_empty'] = 'Nazwa kalendarza nie może być pusta.';

// caldav server
$labels['sync_calendar'] = 'Synchronizacja';
$labels['password_too_short'] = 'Hasło musi mieć przynajmniej 10 znaków';
$labels['read_only'] = 'Tylko do odczytu';
$labels['add_connection'] = 'Dodaj połączenie';
$labels['show_details'] = 'Pokaż detale';
$labels['hide_details'] = 'Ukryj detale';
$labels['caldav_connection_password'] = 'Hasło, które podałeś podczas tworzenia tego połączenia.';
$labels['yes'] = 'Tak';
$labels['no'] = 'Nie';
$labels['confirm_remove_sync'] = 'Jesteś pewien, że chcesz na stałe usunąć to połączenie?';
$labels['remove_sync_error'] = 'Wystąpił błąd podczas usuwania połączenia.';
$labels['create_new_caldav_connection'] = 'Tworzenie nowego połączenia CalDav';
$labels['your_caldav_connections'] = 'Twoje połączenia CalDav';
$labels['created_at'] = 'Stworzone';
$labels['connected_at'] = 'Ostatnia synchronizacja';
$labels['never'] = 'Nigdy';
$labels['alternate_url'] = 'Alternatywny adres URL';
$labels['caldav_password_info'] = 'Ze względów bezpieczeństwa hasło to nie jest zapisywane na serwerze. Jeśli go zapomniałeś, usuń to połączenie i utwórz nowe.';
$labels['caldav_url_info'] = 'Użyj tego adresu URL z aplikacjami, które obsługują wyszukiwanie kalendarza, na przykład: iOS i Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Użyj tego adresu URL w przypadku aplikacji, które nie będą działać z krótkim adresem url powyżej, na przykład: Thunderbird Lightning. (Ten adres URL nie będzie działał z iOS i OSX Calendar).';
$labels['invalid_caldav_url'] = 'Nieprawidłowy adres URL serwera CalDAV.';
$labels['cannot_resolve_caldav_host'] = 'Nie można rozpoznać hosta serwera CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'Nie zezwala się na używanie prywatnych i zastrzeżonych adresów CalDAV.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Dodaj udostępniony kalendarz';
$labels['shared_calendars'] = 'Udostępnione kalendarze';
$labels['no_shared_calendars'] = 'Nie ma udostępnionych kalendarzy, które mogłyby być dodane.';
$labels['add_to_my_calendars'] = 'Dodaj do moich kalendarzy';
$labels['remove_from_my_calendars'] = 'Usuń z moich kalendarzy';
$labels['unshare'] = 'Usuń';
$labels['unshare_warning'] = 'Jeśli usuniesz ten kalendarz zniknie on z tej listy i nie będzie mógł być dodany ponownie.';
$labels['calendar_name'] = 'Nazwa kalendarza';
$labels['calendar_owner'] = 'Właściciel kalendarza';
$labels['shared_date'] = 'Udostępniony';
$labels['unable_to_share'] = 'Następujące adresy znajdują się na innym serwerze i kalendarz nie może być im udostępniony:';

// shared calendar email
$labels['shared_email_subject'] = '$u udostępnił(a) Ci kalendarz';
$labels['shared_email_body'] = 'Użytkownik $u dał Ci dostęp do kalendarza o nazwie "$c." Aby dodać ten kalendarz do twojej listy aktywnych kalendarzy, kliknij na link poniżej:';
$labels['shared_email_body_cpanel'] = 'Użytkownik $u dał Ci dostęp do kalendarza o nazwie "$c." Aby dodać ten kalendarz do twojej listy aktywnych kalendarzy, zaloguj się do Roundcube, otwórz stronę kalendarza, kliknij przycisk "plus" na pasku bocznym i wybierz "Dodaj udostępniony kalendarz".';

// adding holiday calendars
$labels['holiday_calendars'] = 'Kalendarze świąteczne';
$labels['edit_holiday_calendar'] = 'Edycja kalendarza świąt';
$labels['religious_holidays'] = 'Święta kościelne';
$labels['country_holidays'] = 'Święta narodowe';
$labels['add'] = 'Dodaj';
$labels['no_more_holidays_allowed'] = 'Nie możesz dodać kolejnego kalendarza dni wolnych.';
$labels['cannot_load_holidays'] = 'Nie można załadować świąt z serwera Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Dodaj kalendarz urodzin';
$labels['edit_birthday_calendar'] = 'Edytuj kalendarz urodzin';
$labels['contact_birthdays'] = 'Kontakt Urodziny';
$labels['contact_birthdays_info'] = 'Ten kalendarz pobiera informacje o urodzinach z listy kontaktów i wyświetla je jako wydarzenia kalendarza.';
$labels['birthday_event_title'] = 'Urodziny: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Dodaj kalendarz Google';
$labels['edit_google_calendar'] = 'Edycja kalendarza Google';
$labels['google_id'] = 'ID kalendarza Google';
$labels['new_google_calendar'] = 'Nowy kalendarz Google';
$labels['specify_google_calendar_id'] = 'Proszę wpisać ID kalendarza Google';
$labels['calendar_id_format_error'] = 'ID kalendarza Google powinno zawierać znak @.';
$labels['cannot_load_google_calendar'] = 'Nie można załadować kalendarza Google "$c". Upewnij się, że identyfikator kalendarza jest poprawny i że kalendarz został udostępniony publicznie.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Dodaj kalendarz CalDAV';
$labels['edit_caldav_calendar'] = 'Edycja kalendarza CalDAV';
$labels['add_caldav_calendar_information'] = 'Aby zasubskrybować kalendarz CalDAV, określ informacje o serwerze CalDAV i kliknij przycisk Znajdź kalendarze.';
$labels['caldav_url'] = 'Adres URL CalDAV';
$labels['find_calendars'] = 'Znajdź kalendarze';
$labels['searching_for_calendars'] = 'Wyszukiwanie kalendarzy, proszę czekać...';
$labels['select_calendars'] = 'Wybierz kalendarze do dodania:';
$labels['read_only_status_yes'] = 'Tak';
$labels['read_only_status_no'] = 'Nie';
$labels['subscribed'] = 'Subskrybowane';

$labels['refresh_calendar'] = 'Odśwież kalendarz';
$labels['refresh_every_minute'] = 'Co minutę';
$labels['refresh_every_5_minutes'] = 'Co 5 minut';
$labels['refresh_every_15_minutes'] = 'Co 15 minut';
$labels['refresh_every_30_minutes'] = 'Co 30 minut';
$labels['refresh_every_60_minutes'] = 'Co 60 minut';
$labels['refresh_never'] = 'Nigdy';

$labels['cannot_connect_to_caldav_server'] = 'Nie można połączyć się z serwerem CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Nie można połączyć się z serwerem CalDAV. Sprawdź adres URL serwera.';
$labels['caldav_client_error_credentials'] = 'Nieprawidłowa nazwa użytkownika lub hasło CalDAV.';
$labels['caldav_client_error_empty'] = 'Serwer nie zwrócił żadnych kalendarzy, które można dodać. Sprawdź adres URL CalDAV.';
$labels['caldav_client_error_server_info'] = 'Nieprawidłowe informacje o serwerze CalDAV.';
$labels['caldav_client_error_add_calendar'] = 'Nie można zapisać kalendarza "$n".';
$labels['caldav_client_error_delete_event'] = 'Nie można usunąć zdarzenia';
$labels['caldav_client_error_save_event'] = 'Nie można zapisać wydarzenia CalDAV: mogło ono zostać przeniesione lub usunięte albo kalendarz jest niedostępny.';
$labels['caldav_client_error_calendar_added'] = 'Kalendarz "$n" znajduje się już na liście kalendarzy.';

// previewing event
$labels['remove_recurring_event'] = 'Usuń wydarzenie cykliczne';
$labels['remove_one'] = 'Usuń tylko to powtórzenie';
$labels['remove_all'] = 'Usuń wszystkie powtórzenia';
$labels['edit_event'] = 'Edytuj wydarzenie';
$labels['event_removed'] = 'Wydarzenie usunięte.';
$labels['go_to_url'] = 'Przejdź do URLu';
$labels['confirm_remove_event'] = 'Czy na pewno chcesz usunąć to wydarzenie?';

// calendar/agenda grid
$labels['no_events'] = 'Brak wydarzeń do wyświetlenia.';

// importing events
$labels['select_calendar'] = 'Wybierz kalendarz';
$labels['select_calendar_for_events'] = 'Wybierz kalendarz, do którego chcesz dodać wydarzenia:';
$labels['add_events'] = 'Dodaj wydarzenia';
$labels['add_events_to_calendar'] = 'Dodaj wydarzenia do kalendarza';
$labels['events_imported_successfully'] = 'Wydarzenia zostały zaimportowane';
$labels['events_imported_with_errors'] = 'Niektórych wydarzeń nie udało się zaimportować.';
$labels['error_importing_events'] = 'Wystąpił błąd przy importowaniu zdarzeń do kalendarza';

// editing events
$labels['calendar'] = 'Kalendarz';
$labels['new_event'] = 'Nowe wydarzenie';
$labels['summary'] = 'Podsumowanie';
$labels['recurrence'] = 'Cykl';
$labels['title'] = 'Tytuł';
$labels['location'] = 'Lokalizacja';
$labels['description'] = 'Opis';
$labels['comment'] = 'Komentarz (nieobowiązkowo)';
$labels['url'] = 'URL';
$labels['name'] = 'Nazwa';
$labels['start'] = 'Początek';
$labels['end'] = 'Koniec';
$labels['show_timezones'] = 'Pokaż strefy czasowe';
$labels['reset_timezones'] = 'Przywróć domyślne strefy czasowe';
$labels['all_day'] = 'Cały dzień';
$labels['use_calendar_colors'] = 'Użyj kolorów kalendarza';
$labels['color'] = 'Kolor';
$labels['background'] = 'Tło';
$labels['text'] = 'Tekst';
$labels['show_me_as'] = 'Pokaż mnie jako';
$labels['available'] = 'Dostępny';
$labels['busy'] = 'Zajęty';
$labels['visibility'] = 'Widoczność';
$labels['calendar_default'] = 'Domyślne ustawienia kalendarza';
$labels['public'] = 'Publiczne';
$labels['private'] = 'Prywatne';
$labels['repeat'] = 'Powtórz';
$labels['repeat_every'] = 'Powtórz co';
$labels['never'] = 'Nigdy';
$labels['daily'] = 'Codziennie';
$labels['weekly'] = 'Co tydzień';
$labels['monthly'] = 'Co miesiąc';
$labels['yearly'] = 'Co roku';
$labels['days'] = 'dni';
$labels['weeks'] = 'tygodnie(i)';
$labels['months'] = 'miesiące(cy)';
$labels['years'] = 'lat(a)';
$labels['repeat_on'] = 'Powtarzaj';
$labels['repeat_by'] = 'Powtarzaj';
$labels['day_of_month'] = 'dnia miesiąca';
$labels['day_of_week'] = 'dnia tygodnia';
$labels['not_specified'] = 'Nie określono';
$labels['first'] = 'Pierwszy';
$labels['second'] = 'Drugi';
$labels['third'] = 'Trzeci';
$labels['fourth'] = 'Czwarty';
$labels['fifth'] = 'Piąty';
$labels['second_to_last'] = 'Przedostatni';
$labels['last'] = 'Ostatni';
$labels['attachment_too_large'] = 'Plik w załączniku jest zbyt duży. Maksymalny dozwolony rozmiar to $s.';
$labels['bg_color'] = 'Kolor tła';
$labels['tx_color'] = 'kolor tekstu';
$labels['add_notification'] = 'Dodaj powiadomienie';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'To wydarzenie nie ma żadnych powiadomień.';
$labels['minutes'] = 'minut';
$labels['hours'] = 'godzin';
$labels['link'] = 'Link';
$labels['attach_file_from_web'] = 'Załącz plik';
$labels['attachment_url'] = 'Adres URL załącznika';
$labels['invalid_url'] = 'Ten adres URL jest nieprawidłowy.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Dysk Google';
$labels['duplicate'] = 'Duplikuj';
$labels['duplicate_suffix'] = '(duplikat)';
$labels['no_notifications'] = 'To wydarzenie nie ma żadnych powiadomień.';
$labels['this_calendar_is_hidden'] = 'Ten kalendarz jest ukryty. Zapisane wydarzenia nie będą wyświetlane, dopóki kalendarz nie zostanie wyświetlony.';
$labels['make_calendar_visible'] = 'Ustaw ten kalendarz jako widoczny.';

// recurrence
$labels['recurrence_range'] = 'Zakres nawrotów';
$labels['repeat_forever'] = 'Powtarzać w nieskończoność';
$labels['repeat_every_weekday'] = 'Powtarzaj w każdy dzień tygodnia';
$labels['last_day'] = 'Ostatni dzień';
$labels['every'] = 'Każdy';
$labels['weekday'] = 'Dzień powszedni';
$labels['weekend_day'] = 'Dzień weekendowy';
$labels['day_of_week'] = 'dnia tygodnia';
$labels['day_of_month'] = 'dnia miesiąca';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Koniec po';
$labels['occurrences'] = 'wystąpieniach';

// end recurrence on date:
$labels['end_by_date'] = 'Data zakończenia';
$labels['removed_events_title'] = 'Wydarzenia usunięte z cyklu';
$labels['restore'] = 'Przywróć';

// settings page
$labels['setting_view'] = 'Wstępny widok kalendarza';
$labels['setting_first_day'] = 'Początek tygodnia w';
$labels['setting_agenda_week_span'] = 'Przedział czasowy agendy (tygodnie)';
$labels['setting_slot_duration'] = 'Trwanie jednego pola (min)';
$labels['setting_scroll_time'] = 'Przewiń do momentu';
$labels['setting_calendar'] = 'Domyślny kalendarz na zdarzenia';
$labels['setting_refresh'] = 'Odświeżanie kalendarzy';
$labels['setting_week_numbers'] = 'Numeracja tygodni w małym kalendarzu';
$labels['setting_event_border'] = 'Kolory kategorii jako granice zdarzeń';
$labels['show_sunrise'] = 'Pokaż wschód słońca w widoku miesięcznym';
$labels['show_sunset'] = 'Pokaż zachód słońca w widoku miesięcznym.';
$labels['categories'] = 'Kategorie';
$labels['category'] = 'Kategoria';
$labels['no_category'] = 'Bez kategorii';
$labels['add_category'] = 'Dodaj kategorię';
$labels['setting_alarm_sound'] = 'Dźwięk powiadomienia';
$labels['alarm_sound_aurora'] = 'Zorza polarna';
$labels['alarm_sound_bamboo'] = 'Bambus';
$labels['alarm_sound_bells'] = 'Dzwoneczki';
$labels['alarm_sound_chord'] = 'Akord';
$labels['alarm_sound_circles'] = 'Kółka';
$labels['alarm_sound_glass'] = 'Szkło';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metal';
$labels['alarm_sound_ripple'] = 'Plusk';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Gwizdanie';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Odtwórz dźwięk';

// alarms
$labels['upcoming_events'] = 'Nadchodzące wydarzenia';
$labels['dismiss'] = 'Odwołaj';
$labels['dismiss_all'] = 'Odwołaj wszystkie';
$labels['snooze'] = 'Drzemka';
$labels['5_minutes'] = '5 minut';
$labels['10_minutes'] = '10 minut';
$labels['20_minutes'] = '20 minut';
$labels['30_minutes'] = '30 minut';
$labels['1_hour'] = '1 godzina';
$labels['2_hours'] = '2 godziny';
$labels['tomorrow'] = 'Do jutra';
$labels['next_week'] = 'Do następnego tygodnia';

// sidebar
$labels['setting_show_xcalendar'] = 'Pokaż Agendę Kalendarza';
$labels['todays_agenda'] = 'Dzisiejsza Agenda';
$labels['no_events_today'] = 'Na dzisiaj nie zaplanowano żadnych wydarzeń.';
$labels['sidebar_more'] = 'Więcej...';

// event invitation
$labels['itip_title_request'] = 'Masz zaproszenie na wydarzenie.';
$labels['itip_title_modified'] = 'To wydarzenie zostało zmienione.';
$labels['itip_title_reply_accepted'] = '$u zaakceptował twoje zaproszenie';
$labels['itip_title_reply_tentative'] = '$u wstępnie zaakceptował twoje zaproszenie';
$labels['itip_title_reply_declined'] = '$u odrzucił twoje zaproszenie';
$labels['itip_title_cancel'] = 'To wydarzenie zostało odwołane.';

$labels['itip_email_subject_request'] = 'Zaproszenie:';
$labels['itip_email_subject_request_modified'] = 'Wydarzenie zmienione:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Odwołane';

$labels['itip_popup_event_added'] = 'Wydarzenie dodane do kalendarza.';
$labels['itip_popup_event_updated'] = 'Wydarzenie zostało zaktualizowane w kalendarzu.';
$labels['itip_popup_event_deleted'] = 'Wydarzenie usunięte z kalendarza.';
$labels['itip_popup_response_sent'] = 'Odpowiedź wysłana do organizatora.';

$labels['itip_message_invitation_accepted'] = 'Przyjąłeś to zaproszenie.';
$labels['itip_message_invitation_tentative'] = 'Wstępnie zaakceptowałeś to zaproszenie.';
$labels['itip_message_invitation_declined'] = 'Odrzuciłeś to zaproszenie.';
$labels['itip_message_event_deleted'] = 'Wydarzenie zostało usunięte z twojego kalendarza.';
$labels['itip_message_event_updated'] = 'Wydarzenie w twoim kalendarzu zostało zaktualizowane.';
$labels['itip_message_event_missing'] = 'To wydarzenie nie istnieje w żadnym z twoich kalendarzy.';

$labels['itip_accept'] = 'Akceptuj';
$labels['itip_tentative'] = 'Wstępnie tak';
$labels['itip_decline'] = 'Odrzuć';
$labels['itip_change_reponse'] = 'Zmień odpowiedź.';
$labels['itip_add_to_calendar'] = 'Dodaj do kalendarza';
$labels['itip_reply_to_sender'] = 'Odpowiedz nadawcy';
$labels['itip_dont_import'] = 'Nie dodawaj do kalendarza';
$labels['itip_update_in_calendar'] = 'Zaktualizuj wydarzenie w kalendarzu';
$labels['itip_delete_from_calendar'] = 'Usuń wydarzenie z kalendarza';

$labels['mail_subject_invitation'] = 'Zaproszenie:';
$labels['mail_subject_update'] = 'Zaproszenie zaktualizowane:';
$labels['mail_subject_cancel'] = 'Wydarzenie odwołane:';

// event email notifications
$labels['mail_title'] = 'Tytuł:';
$labels['mail_start'] = 'Początek:';
$labels['mail_end'] = 'Koniec:';
$labels['mail_description'] = 'Opis:';
$labels['mail_location'] = 'Lokalizacja:';

// event attendees
$labels['attendees'] = 'Uczestnicy';
$labels['attendee_role'] = 'Rola';
$labels['attendee_role_required'] = 'Wymagany';
$labels['attendee_role_optional'] = 'Nieobowiązkowy';
$labels['attendee_role_chair'] = 'Przewodniczący';
$labels['attendee_role_none'] = 'Nieuczestniczący';
$labels['organizer'] = 'Organizator';
$labels['attendance'] = 'Obecność';
$labels['your_status'] = 'Twój status';
$labels['attendance_going'] = 'Przyjąć?';
$labels['attendee_status_yes'] = 'Tak';
$labels['attendee_status_no'] = 'Nie';
$labels['attendee_status_maybe'] = 'Może';
$labels['attendee_status_delegated'] = 'Delegowany';
$labels['attendee_status_waiting'] = 'Oczekujący';
$labels['attendee_status_waiting_for_confirmation'] = 'Oczekujący na potwierdzenie';
$labels['attendee_status_info_yes'] = 'Będzie uczestniczyć';
$labels['attendee_status_info_no'] = 'Nie będzie uczestniczyć';
$labels['attendee_status_info_maybe'] = 'Może uczestniczyć';
$labels['attendee_status_info_delegated'] = 'Obecność delegowana';
$labels['attendee_role'] = 'Rola';
$labels['attendee_role_info'] = 'Należy określić, czy uczestnik ma obowiązek wziąć udział w spotkaniu, czy też mu przewodniczyć.';
$labels['attendee_notify'] = 'Powiadom';
$labels['attendee_notify_info'] = 'Jeśli zostanie zaznaczone, do uczestnika zostanie wysłany e-mail z zaproszeniem.';
$labels['add_attendee'] = 'Dodaj uczestnika';
$labels['attendee_notify_question'] = 'Powiadomić uczestników przez email?';
$labels['attendee_notify_textarea'] = 'Dodaj opcjonalny komunikat';
$labels['attendee_notify_send'] = 'Wyślij';
$labels['attendee_notify_dont'] = 'Nie wysyłaj';
$labels['attendee_notify_cancel'] = 'Anuluj';
$labels['new_attendee_not_saved_warning'] = 'Wpisałeś adres e-mail uczestnika, ale nie dodałeś go do listy uczestników. Czy chcesz zapisać zmiany bez tego uczestnika?';

// import / export
$labels['import_export'] = 'Import / Eksport';
$labels['export_to_file'] = 'Eksportuj do pliku';
$labels['export_info'] = 'Kliknij ten przycisk, aby wyeksportować i pobrać wydarzenia z tego kalendarza w formacie iCalendar (ics). Jeśli w kalendarzu znajduje się wiele wydarzeń, proces eksportowania może potrwać kilka chwil.';
$labels['export_no_events'] = 'Ten kalendarz nie ma żadnych wydarzeń do wyeksportowania.';
$labels['import_from_file'] = 'Importuj z pliku';
$labels['import_info'] = 'Kliknij ten przycisk, aby zaimportować zdarzenia do tego kalendarza z pliku iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Przypomnienie o wizycie';
$labels['email_notification_subject'] = 'Przypomnienie: $t o $d ($z)';
$labels['email_notification_cron_error'] = 'Powiadomienia e-mail są wyłączone. Skontaktuj się z administratorem.';
$labels['alarm_before_event_starts'] = 'Przed rozpoczęciem wydarzenia';
$labels['alarm_after_event_starts'] = 'Po rozpoczęciu wydarzenia';
$labels['alarm_before_event_ends'] = 'Przed zakończeniem wydarzenia';
$labels['alarm_after_event_ends'] = 'Po zakończeniu wydarzenia';
$labels['alarm_at_specific_time'] = 'W konkretnym czasie';

// search
$labels['specify_at_least_one_calendar'] = 'Określ co najmniej jeden kalendarz do przeszukania.';
$labels['specify_text_to_search'] = 'Wpisz tekst do wyszukania.';
$labels['clear_search_results'] = 'Wyczyść';
$labels['search_outdated_message'] = 'Wyniki wyszukiwania mogą być nieaktualne. Rozważ ponowne przeprowadzenie wyszukiwania.';
$labels['date_range'] = 'Zakres dat';
$labels['date_range_subtitle'] = 'Zakres dat:';
$labels['search_results'] = 'Znalezione wyniki: $n';
$labels['modify_search_options'] = 'Spróbuj zmodyfikować opcje wyszukiwania.';
$labels['show_shortcuts'] = 'Pokaż skróty';
$labels['hide_shortcuts'] = 'Ukryj skróty';
$labels['this_month'] = 'W tym miesiącu';
$labels['this_year'] = 'W tym roku';
$labels['past_30_days'] = 'Ostatnie 30 dni';
$labels['next_30_days'] = 'Następne 30 dni';
$labels['past_6_months'] = 'Ostatnie 6 miesięcy';
$labels['next_6_months'] = 'Następne 6 miesięcy';

// calendar grid
$labels['cannot_load_events'] = 'Nie można załadować wydarzeń dla kalendarza "$c".';
