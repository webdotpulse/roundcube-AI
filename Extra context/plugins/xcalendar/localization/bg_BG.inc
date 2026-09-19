<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Календар';
$labels['app_menu_title'] = 'Календар';

// menu
$labels['create_event'] = 'Създаване на събитие';
$labels['create_event_button'] = 'Ново събитие';
$labels['day'] = 'Ден';
$labels['week'] = 'Седмица';
$labels['month'] = 'Месец';
$labels['agenda'] = 'Програма';

// editing calendars
$labels['calendars'] = 'Календари';
$labels['my_calendar'] = 'Моят календар';
$labels['create_calendar'] = 'Създаване на календар';
$labels['new_calendar'] = 'Нов календар';
$labels['edit_calendar'] = 'Редактиране на календара';
$labels['remove_calendar'] = 'Изтриване на календара';
$labels['confirm_remove_calendar'] = 'Сигурни ли сте, че искате да изтриете този календар?';
$labels['calendar_removed'] = 'Изтрит календар.';
$labels['notifications'] = 'Известия';
$labels['default_event_visibility'] = 'Видимост на събитието по подразбиране';
$labels['time_zone'] = 'Часова зона на календара';
$labels['share_calendar'] = 'Календар за споделяне';
$labels['permissions'] = 'Разрешения';
$labels['permission_publish_calendar'] = 'Публикуване на календар';
$labels['permission_share_calendar'] = 'Календар за споделяне';
$labels['permission_edit_events'] = 'Редактиране на събития';
$labels['permission_see_details'] = 'Вижте подробности';
$labels['permission_publish_calendar_info'] = 'Ако е маркирано, потребителят ще може да публикува календара.';
$labels['permission_share_calendar_info'] = 'Ако е поставена отметка, потребителят ще може да споделя календара с други потребители.';
$labels['permission_edit_events_info'] = 'Ако е маркирано, потребителят ще може да редактира и изтрива събития от календара.';
$labels['permission_see_details_info'] = 'Ако е отбелязано, всички подробности за събитието ще бъдат споделени. Ако отметката не е поставена, ще бъде споделена само наличната/заетата информация.';
$labels['share_send_notification'] = 'Уведомяване на';
$labels['share_send_notification_info'] = 'Ако е маркирано, на потребителя ще бъде изпратен имейл, който го информира за новия споделен календар.';
$labels['new_share_not_saved_warning'] = 'Въвели сте имейл адрес, за да споделите календара, но не сте кликнали върху бутона Споделяне, за да го добавите към списъка за споделяне. Искате ли да продължите да записвате, без да споделяте календара с този потребител?';
$labels['share'] = 'Споделете';
$labels['shared_marker'] = '(Споделено)';
$labels['owner'] = 'Собственик';
$labels['publish_calendar'] = 'Публикуване на календар';
$labels['create_link'] = 'Създаване на връзка';
$labels['reset'] = 'Нулиране на';
$labels['remove'] = 'Премахване на';
$labels['publish_full'] = 'Публикувайте цялата информация за събитието.';
$labels['publish_busy'] = 'Публикувайте само информацията за свободни/заети часове.';
$labels['sync_no_id'] = 'Опциите на CalDAV ще станат достъпни, след като запазите календара.';
$labels['share_no_id'] = 'Опциите за споделяне на календара ще станат достъпни, след като запазите календара.';
$labels['publish_no_id'] = 'Опциите за публикуване на календара ще станат достъпни, след като запазите календара.';
$labels['import_no_id'] = 'Опциите за импортиране и експортиране ще станат достъпни, след като запазите календара.';
$labels['unable_to_save_calendar_data'] = 'Невъзможност за запазване на данни от календара.';
$labels['unable_to_retrieve_calendar_data'] = 'Невъзможно е да се изтеглят данни от календара. Възможно е календарът вече да не е наличен.';
$labels['unable_add_shared_calendar'] = 'Не е възможно да добавите споделения календар. Календарът може би вече не е наличен.';
$labels['itip_organizer_message'] = 'Вие сте поканени на това събитие, но не сте организатор. Промените, които направите в това събитие, няма да бъдат изпратени на останалите участници.';
$labels['cannot_share_with_yourself'] = 'Не можете да споделяте собствения си календар със себе си.';
$labels['cannot_share_with_calendar_owner'] = 'Не можете да споделите този календар със собственика на календара ($e)';
$labels['setting_default_notification_type'] = 'Известия по подразбиране за нови събития';
$labels['calendar_name_cannot_be_empty'] = 'Името на календара не може да бъде празно.';

// caldav server
$labels['sync_calendar'] = 'Синхронизиране на календара';
$labels['password_too_short'] = 'Дължината на паролата трябва да е поне 10 символа.';
$labels['read_only'] = 'Само за четене';
$labels['add_connection'] = 'Добавяне на връзка';
$labels['show_details'] = 'Покажи подробности';
$labels['hide_details'] = 'Скрийте детайлите';
$labels['caldav_connection_password'] = 'Паролата, която сте посочили при създаването на тази връзка.';
$labels['yes'] = 'Да';
$labels['no'] = 'Не';
$labels['confirm_remove_sync'] = 'Сигурни ли сте, че искате да премахнете тази връзка за постоянно?';
$labels['remove_sync_error'] = 'Имаше грешка при премахването на връзката.';
$labels['create_new_caldav_connection'] = 'Създаване на нова връзка CalDav';
$labels['your_caldav_connections'] = 'Вашите връзки с CalDav';
$labels['created_at'] = 'Създаден';
$labels['connected_at'] = 'Последна синхронизация';
$labels['never'] = 'Никога';
$labels['alternate_url'] = 'Алтернативен URL адрес';
$labels['caldav_password_info'] = 'От съображения за сигурност тази парола не се запазва на сървъра. Ако сте я забравили, изтрийте тази връзка и създайте нова.';
$labels['caldav_url_info'] = 'Използвайте този URL адрес с приложения, които поддържат откриване на календари, например: iOS и Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Използвайте този URL адрес за приложения, които няма да работят с краткия URL адрес по-горе, например: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'Невалиден URL адрес на CalDAV сървъра.';
$labels['cannot_resolve_caldav_host'] = 'Не може да се определи хостът на CalDAV сървъра.';
$labels['private_caldav_addresses_not_allowed'] = 'Частните и запазените CalDAV адреси не са разрешени.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Добавяне на споделен календар';
$labels['shared_calendars'] = 'Споделени календари';
$labels['no_shared_calendars'] = 'Няма споделени календари, които можете да добавите.';
$labels['add_to_my_calendars'] = 'Добави към календарите ми';
$labels['remove_from_my_calendars'] = 'Премахни от календарите ми';
$labels['unshare'] = 'Отказ от споделяне';
$labels['unshare_warning'] = 'Ако отмените споделянето на този календар, той ще изчезне от този списък и няма да можете да го добавите отново.';
$labels['calendar_name'] = 'Име на календара';
$labels['calendar_owner'] = 'Собственик на календара';
$labels['shared_date'] = 'Споделен';
$labels['unable_to_share'] = 'Календарът не може да бъде споделен със следните потребители, тъй като техните имейл акаунти се намират на отдалечен сървър:';

// shared calendar email
$labels['shared_email_subject'] = '$u е споделил с вас календар';
$labels['shared_email_body'] = 'Потребителят $u ви е предоставил достъп до календар, наречен "$c". За да добавите този календар към списъка си с активни календари, щракнете върху връзката по-долу:';
$labels['shared_email_body_cpanel'] = 'Потребителят $u ви е предоставил достъп до календар, наречен "$c". За да добавите този календар към списъка си с активни календари, влезте в Roundcube, отидете на Календар, щракнете върху иконата плюс в страничната лента и изберете Добавяне на споделен календар.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Празнични календари';
$labels['edit_holiday_calendar'] = 'Редактиране на празничния календар';
$labels['religious_holidays'] = 'Религиозни празници';
$labels['country_holidays'] = 'Почивки в страната';
$labels['add'] = 'Добавяне на';
$labels['no_more_holidays_allowed'] = 'Не можете да добавяте повече празнични календари.';
$labels['cannot_load_holidays'] = 'Не мога да заредя празници от сървъра на Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Добавяне на календар за рожден ден';
$labels['edit_birthday_calendar'] = 'Редактиране на календара за рожден ден';
$labels['contact_birthdays'] = 'Рожденни дни за контакт';
$labels['contact_birthdays_info'] = 'Този календар извлича информация за рождени дни от списъка ви с контакти и я показва като събития в календара.';
$labels['birthday_event_title'] = 'Рожден ден: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Добавяне на календар на Google';
$labels['edit_google_calendar'] = 'Редактиране на календара на Google';
$labels['google_id'] = 'Идентификатор на календара на Google';
$labels['new_google_calendar'] = 'Нов календар на Google';
$labels['specify_google_calendar_id'] = 'Моля, въведете идентификатора на календара на Google';
$labels['calendar_id_format_error'] = 'Идентификаторът на календара на Google трябва да включва символа @.';
$labels['cannot_load_google_calendar'] = 'Календарът на Google "$c" не може да бъде зареден. Уверете се, че идентификаторът на календара е правилен и че календарът е станал публично достъпен.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Добавяне на календар на CalDAV';
$labels['edit_caldav_calendar'] = 'Редактиране на календара на CalDAV';
$labels['add_caldav_calendar_information'] = 'За да се абонирате за календар на CalDAV, посочете информацията за сървъра на CalDAV и щракнете върху Намери календари.';
$labels['caldav_url'] = 'URL адрес на CalDAV';
$labels['find_calendars'] = 'Намиране на календари';
$labels['searching_for_calendars'] = 'Търся календари, моля, изчакайте...';
$labels['select_calendars'] = 'Изберете календари, които да добавите:';
$labels['read_only_status_yes'] = 'Да';
$labels['read_only_status_no'] = 'Не';
$labels['subscribed'] = 'Абониран';

$labels['refresh_calendar'] = 'Обновяване на календара';
$labels['refresh_every_minute'] = 'Всяка минута';
$labels['refresh_every_5_minutes'] = 'На всеки 5 минути';
$labels['refresh_every_15_minutes'] = 'На всеки 15 минути';
$labels['refresh_every_30_minutes'] = 'На всеки 30 минути';
$labels['refresh_every_60_minutes'] = 'На всеки 60 минути';
$labels['refresh_never'] = 'Никога';

$labels['cannot_connect_to_caldav_server'] = 'Не може да се свърже със сървъра CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Не може да се свърже със сървъра на CalDAV. Проверете URL адреса на сървъра.';
$labels['caldav_client_error_credentials'] = 'Неправилно потребителско име или парола на CalDAV.';
$labels['caldav_client_error_empty'] = 'Сървърът не е върнал никакви календари, които могат да бъдат добавени. Проверете URL адреса на CalDAV.';
$labels['caldav_client_error_server_info'] = 'Неправилна информация за сървъра на CalDAV.';
$labels['caldav_client_error_add_calendar'] = 'Не можете да запазите календара "$n".';
$labels['caldav_client_error_delete_event'] = 'Не мога да изтрия събитие';
$labels['caldav_client_error_save_event'] = 'Не може да се запише събитие в CalDAV: вероятно е било преместено, изтрито или календарът не е достъпен.';
$labels['caldav_client_error_calendar_added'] = 'Календарът "$n" вече е в списъка ви с календари.';

// previewing event
$labels['remove_recurring_event'] = 'Изтриване на повтарящо се събитие';
$labels['remove_one'] = 'Изтриване само на тази инстанция';
$labels['remove_all'] = 'Изтриване на всички екземпляри';
$labels['edit_event'] = 'Редактиране на събитие';
$labels['event_removed'] = 'Изтрито събитие.';
$labels['go_to_url'] = 'Отидете на URL адрес';
$labels['confirm_remove_event'] = 'Сигурни ли сте, че искате да изтриете това събитие?';

// calendar/agenda grid
$labels['no_events'] = 'Няма събития за показване.';

// importing events
$labels['select_calendar'] = 'Изберете календар';
$labels['select_calendar_for_events'] = 'Изберете календара, към който искате да добавите събитията:';
$labels['add_events'] = 'Добавяне на събития';
$labels['add_events_to_calendar'] = 'Добавяне на събития в календара';
$labels['events_imported_successfully'] = 'Успешно импортирани събития';
$labels['events_imported_with_errors'] = 'Някои други събития не могат да бъдат импортирани.';
$labels['error_importing_events'] = 'Имаше грешка при импортирането на събития в календара';

// editing events
$labels['calendar'] = 'Календар';
$labels['new_event'] = 'Ново събитие';
$labels['summary'] = 'Резюме';
$labels['recurrence'] = 'Повтаряне на заболяването';
$labels['title'] = 'Заглавие';
$labels['location'] = 'Местоположение';
$labels['description'] = 'Описание';
$labels['comment'] = 'Коментар:';
$labels['url'] = 'URL';
$labels['name'] = 'Име';
$labels['start'] = 'Начало';
$labels['end'] = 'Край на';
$labels['show_timezones'] = 'Показване на часовите зони';
$labels['reset_timezones'] = 'Възстановяване на часовите зони по подразбиране';
$labels['all_day'] = 'През целия ден';
$labels['use_calendar_colors'] = 'Използване на цветовете на календара';
$labels['color'] = 'Цвят';
$labels['background'] = 'Фон';
$labels['text'] = 'Текст';
$labels['show_me_as'] = 'Покажи ми се като';
$labels['available'] = 'Наличен';
$labels['busy'] = 'Зает';
$labels['visibility'] = 'Видимост';
$labels['calendar_default'] = 'Календар по подразбиране';
$labels['public'] = 'Публичен';
$labels['private'] = 'Частна';
$labels['repeat'] = 'Повторете';
$labels['repeat_every'] = 'Повтаряйте всеки';
$labels['never'] = 'Никога';
$labels['daily'] = 'Daily';
$labels['weekly'] = 'Седмичен';
$labels['monthly'] = 'Месечно';
$labels['yearly'] = 'Годишен';
$labels['days'] = 'ден(и)';
$labels['weeks'] = 'седмица(и)';
$labels['months'] = 'месец(и)';
$labels['years'] = 'година(и)';
$labels['repeat_on'] = 'Повтаряне на';
$labels['repeat_by'] = 'Повтаряйте от';
$labels['day_of_month'] = 'Ден от месеца';
$labels['day_of_week'] = 'Ден от седмицата';
$labels['not_specified'] = 'Не е посочено';
$labels['first'] = 'Първо';
$labels['second'] = 'Втори';
$labels['third'] = 'Трети';
$labels['fourth'] = 'Четвърти';
$labels['fifth'] = 'Пети';
$labels['second_to_last'] = 'На предпоследно място';
$labels['last'] = 'Последно';
$labels['attachment_too_large'] = 'Файлът на прикачения файл е твърде голям. Максимално допустимият размер е $s.';
$labels['bg_color'] = 'Фонов цвят';
$labels['tx_color'] = 'Цвят на текста';
$labels['add_notification'] = 'Добавяне на известие';
$labels['popup'] = 'Изскачащи прозорци';
$labels['no_notifications'] = 'Това събитие няма известия.';
$labels['minutes'] = 'минути';
$labels['hours'] = 'часове';
$labels['link'] = 'Връзка';
$labels['attach_file_from_web'] = 'Прикачете файл';
$labels['attachment_url'] = 'URL адрес на прикачения файл';
$labels['invalid_url'] = 'Този URL адрес е невалиден.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Диск на Google';
$labels['duplicate'] = 'Дублирай';
$labels['duplicate_suffix'] = '(дубликат)';
$labels['no_notifications'] = 'Това събитие няма известия.';
$labels['this_calendar_is_hidden'] = 'Този календар е скрит. Запазените събития няма да се показват, докато календарът не стане видим.';
$labels['make_calendar_visible'] = 'Направете този календар видим.';

// recurrence
$labels['recurrence_range'] = 'Обхват на повторение';
$labels['repeat_forever'] = 'Повтаряйте завинаги';
$labels['repeat_every_weekday'] = 'Повтаряйте всеки делничен ден';
$labels['last_day'] = 'Последен ден';
$labels['every'] = 'Всеки';
$labels['weekday'] = 'Седмичен ден';
$labels['weekend_day'] = 'Ден на уикенда';
$labels['day_of_week'] = 'Ден от седмицата';
$labels['day_of_month'] = 'Ден от месеца';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Край след';
$labels['occurrences'] = 'възникване(я)';

// end recurrence on date:
$labels['end_by_date'] = 'Дата на приключване';
$labels['removed_events_title'] = 'Премахване на събития от повтарящи се серии';
$labels['restore'] = 'Възстановяване на';

// settings page
$labels['setting_view'] = 'Първоначален изглед на календара';
$labels['setting_first_day'] = 'Седмицата започва на';
$labels['setting_agenda_week_span'] = 'Времеви диапазон на програмата (седмици)';
$labels['setting_slot_duration'] = 'Времеви интервал за деня (минути)';
$labels['setting_scroll_time'] = 'Превъртете към времето';
$labels['setting_calendar'] = 'Календар по подразбиране за събития';
$labels['setting_refresh'] = 'Обновяване на календари';
$labels['setting_week_numbers'] = 'Номера на седмицата в малък календар';
$labels['setting_event_border'] = 'Цветове на категориите като граници на събитията';
$labels['show_sunrise'] = 'Показване на изгрева на слънцето в месечен изглед';
$labels['show_sunset'] = 'Показване на залеза в изглед на месеца';
$labels['categories'] = 'Категории';
$labels['category'] = 'Категория';
$labels['no_category'] = 'Няма категория';
$labels['add_category'] = 'Добавяне на категория';
$labels['setting_alarm_sound'] = 'Звук на известието';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Бамбук';
$labels['alarm_sound_bells'] = 'Камбани';
$labels['alarm_sound_chord'] = 'Акорди';
$labels['alarm_sound_circles'] = 'Кръгове';
$labels['alarm_sound_glass'] = 'Стъкло';
$labels['alarm_sound_maramba'] = 'Марамба';
$labels['alarm_sound_metal'] = 'Метал';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Стар Трек';
$labels['alarm_sound_whistle'] = 'Свирка';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Възпроизвеждане на звук';

// alarms
$labels['upcoming_events'] = 'Предстоящи събития';
$labels['dismiss'] = 'Отхвърляне на';
$labels['dismiss_all'] = 'Отхвърляне на всички';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 минути';
$labels['10_minutes'] = '10 минути';
$labels['20_minutes'] = '20 минути';
$labels['30_minutes'] = '30 минути';
$labels['1_hour'] = '1 час';
$labels['2_hours'] = '2 часа';
$labels['tomorrow'] = 'До утре';
$labels['next_week'] = 'До следващата седмица';

// sidebar
$labels['setting_show_xcalendar'] = 'Показване на календара';
$labels['todays_agenda'] = 'Дневен ред за днес';
$labels['no_events_today'] = 'Няма планирани събития за днес.';
$labels['sidebar_more'] = 'Още...';

// event invitation
$labels['itip_title_request'] = 'Поканени сте на събитие.';
$labels['itip_title_modified'] = 'Това събитие е променено.';
$labels['itip_title_reply_accepted'] = '$u е приел поканата ви';
$labels['itip_title_reply_tentative'] = '$u е приел поканата ви предварително';
$labels['itip_title_reply_declined'] = '$u е отказал поканата ви';
$labels['itip_title_cancel'] = 'Това събитие е отменено.';

$labels['itip_email_subject_request'] = 'Покана:';
$labels['itip_email_subject_request_modified'] = 'Събитието е променено:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Отменя се:';

$labels['itip_popup_event_added'] = 'Събитието е добавено в календара.';
$labels['itip_popup_event_updated'] = 'Събитието е актуализирано в календара.';
$labels['itip_popup_event_deleted'] = 'Събитието е изтрито от календара.';
$labels['itip_popup_response_sent'] = 'Отговор, изпратен на организатора.';

$labels['itip_message_invitation_accepted'] = 'Приели сте тази покана.';
$labels['itip_message_invitation_tentative'] = 'Приехте тази покана предварително.';
$labels['itip_message_invitation_declined'] = 'Отказахте тази покана.';
$labels['itip_message_event_deleted'] = 'Събитието е изтрито от вашия календар.';
$labels['itip_message_event_updated'] = 'Събитието в календара ви е актуализирано.';
$labels['itip_message_event_missing'] = 'Това събитие не съществува в нито един от вашите календари.';

$labels['itip_accept'] = 'Приемане на';
$labels['itip_tentative'] = 'Предварителен';
$labels['itip_decline'] = 'Спад';
$labels['itip_change_reponse'] = 'Промяна на отговора.';
$labels['itip_add_to_calendar'] = 'Добавяне към календара';
$labels['itip_reply_to_sender'] = 'Отговор на подателя';
$labels['itip_dont_import'] = 'Не добавяйте в календара';
$labels['itip_update_in_calendar'] = 'Актуализиране на събитието в календара';
$labels['itip_delete_from_calendar'] = 'Изтриване на събитие от календара';

$labels['mail_subject_invitation'] = 'Покана:';
$labels['mail_subject_update'] = 'Актуализирана покана:';
$labels['mail_subject_cancel'] = 'Събитието е отменено:';

// event email notifications
$labels['mail_title'] = 'Заглавие:';
$labels['mail_start'] = 'Започнете:';
$labels['mail_end'] = 'Край:';
$labels['mail_description'] = 'Описание:';
$labels['mail_location'] = 'Местоположение:';

// event attendees
$labels['attendees'] = 'Присъстващи';
$labels['attendee_role'] = 'Роля';
$labels['attendee_role_required'] = 'Изисква се';
$labels['attendee_role_optional'] = 'По избор';
$labels['attendee_role_chair'] = 'Стол';
$labels['attendee_role_none'] = 'Неучастващи';
$labels['organizer'] = 'Организатор';
$labels['attendance'] = 'Посещаемост';
$labels['your_status'] = 'Вашият статус';
$labels['attendance_going'] = 'Отивате?';
$labels['attendee_status_yes'] = 'Да';
$labels['attendee_status_no'] = 'Не';
$labels['attendee_status_maybe'] = 'Може би';
$labels['attendee_status_delegated'] = 'Делегиран';
$labels['attendee_status_waiting'] = 'В очакване';
$labels['attendee_status_waiting_for_confirmation'] = 'В очакване на потвърждение';
$labels['attendee_status_info_yes'] = 'Ще присъства';
$labels['attendee_status_info_no'] = 'Няма да присъства';
$labels['attendee_status_info_maybe'] = 'Може да присъства';
$labels['attendee_status_info_delegated'] = 'Делегирано присъствие';
$labels['attendee_role'] = 'Роля';
$labels['attendee_role_info'] = 'Посочете дали от участника се изисква да присъства на заседанието или да го ръководи.';
$labels['attendee_notify'] = 'Уведомяване на';
$labels['attendee_notify_info'] = 'Ако е отбелязано, на участника ще бъде изпратен имейл с покана.';
$labels['add_attendee'] = 'Добавяне на участник';
$labels['attendee_notify_question'] = 'Изпращане на имейли с известия до участниците?';
$labels['attendee_notify_textarea'] = 'Добавяне на незадължително съобщение';
$labels['attendee_notify_send'] = 'Изпрати';
$labels['attendee_notify_dont'] = 'Не изпращайте';
$labels['attendee_notify_cancel'] = 'Отмяна на';
$labels['new_attendee_not_saved_warning'] = 'Въведохте имейл адрес на участник, но не го добавихте в списъка с участници. Да продължите със запазването без този участник?';

// import / export
$labels['import_export'] = 'Внос / износ';
$labels['export_to_file'] = 'Експортиране към файл';
$labels['export_info'] = 'Щракнете върху този бутон, за да експортирате и изтеглите събитията от този календар във формат iCalendar (ics). Ако календарът съдържа много събития, процесът на експортиране може да отнеме няколко минути.';
$labels['export_no_events'] = 'В този календар няма събития за експортиране.';
$labels['import_from_file'] = 'Импортиране от файл';
$labels['import_info'] = 'Щракнете върху този бутон, за да импортирате събития в този календар от файл iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Напомняне за назначение';
$labels['email_notification_subject'] = 'Напомняне: $t в $d ($z)';
$labels['email_notification_cron_error'] = 'Имейл известията са деактивирани. Свържете се с администратора.';
$labels['alarm_before_event_starts'] = 'Преди началото на събитието';
$labels['alarm_after_event_starts'] = 'След началото на събитието';
$labels['alarm_before_event_ends'] = 'Преди края на събитието';
$labels['alarm_after_event_ends'] = 'След края на събитието';
$labels['alarm_at_specific_time'] = 'В определено време';

// search
$labels['specify_at_least_one_calendar'] = 'Посочете поне един календар, в който да търсите.';
$labels['specify_text_to_search'] = 'Въведете текста, който искате да търсите.';
$labels['clear_search_results'] = 'Clear';
$labels['search_outdated_message'] = 'Резултатите от търсенето може да са неактуални. Помислете за повторно търсене.';
$labels['date_range'] = 'Обхват на датата';
$labels['date_range_subtitle'] = 'Диапазон на датите:';
$labels['search_results'] = 'Намерени резултати: $n';
$labels['modify_search_options'] = 'Опитайте да промените опциите за търсене.';
$labels['show_shortcuts'] = 'Показване на бързи клавиши';
$labels['hide_shortcuts'] = 'Скриване на бързите клавиши';
$labels['this_month'] = 'Този месец';
$labels['this_year'] = 'Тази година';
$labels['past_30_days'] = 'Последните 30 дни';
$labels['next_30_days'] = 'Следващите 30 дни';
$labels['past_6_months'] = 'Последните 6 месеца';
$labels['next_6_months'] = 'Следващите 6 месеца';

// calendar grid
$labels['cannot_load_events'] = 'Не може да зареди събития за календара "$c".';
