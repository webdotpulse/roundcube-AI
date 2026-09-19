<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Календарь';
$labels['app_menu_title'] = 'Календарь';

// menu
$labels['create_event'] = 'Создать событие';
$labels['create_event_button'] = 'Создать';
$labels['day'] = 'День';
$labels['week'] = 'Неделя';
$labels['month'] = 'Месяц';
$labels['agenda'] = 'Повестка дня';

// editing calendars
$labels['calendars'] = 'Календарь';
$labels['my_calendar'] = 'Мой календарь';
$labels['create_calendar'] = 'Создать календарь';
$labels['new_calendar'] = 'Новый календарь';
$labels['edit_calendar'] = 'Редактировать календарь';
$labels['remove_calendar'] = 'Удалить календарь';
$labels['confirm_remove_calendar'] = 'Вы уверены, что хотите удалить этот календарь?';
$labels['calendar_removed'] = 'Календарь удален.';
$labels['notifications'] = 'Уведомления';
$labels['default_event_visibility'] = 'Видимость события по умолчанию';
$labels['time_zone'] = 'Часовой пояс календаря';
$labels['share_calendar'] = 'Поделиться календарём';
$labels['permissions'] = 'Права доступа';
$labels['permission_publish_calendar'] = 'Опубликовать календарь';
$labels['permission_share_calendar'] = 'Поделиться календарём';
$labels['permission_edit_events'] = 'Редактировать событие';
$labels['permission_see_details'] = 'См. подробности';
$labels['permission_publish_calendar_info'] = 'Если отмечено, пользователь сможет публиковать календарь.';
$labels['permission_share_calendar_info'] = 'Если отмечено, пользователь сможет делиться календарём с другими пользователями.';
$labels['permission_edit_events_info'] = 'Если отмечено, пользователь сможет редактировать и удалять события календаря.';
$labels['permission_see_details_info'] = 'Если отмечено, все детали события будут опубликованы. Если нет, будет доступна только информация о занятости.';
$labels['share_send_notification'] = 'Уведомлять';
$labels['share_send_notification_info'] = 'Если отмечено, пользователю будет отправлено электронное письмо с уведомлением о новом общедоступном календаре.';
$labels['new_share_not_saved_warning'] = 'Вы ввели e-mail адрес, чтобы поделиться календарём, но не нажали кнопку Поделиться. Продолжить сохранение без обмена календарем с указанным пользователем?';
$labels['share'] = 'Поделиться';
$labels['shared_marker'] = '(Поделился)';
$labels['owner'] = 'Владелец';
$labels['publish_calendar'] = 'Опубликовать календарь';
$labels['create_link'] = 'Создать ссылку';
$labels['reset'] = 'Сброс';
$labels['remove'] = 'Удалить';
$labels['publish_full'] = 'Публиковать всю информацию о событии.';
$labels['publish_busy'] = 'Публиковать только информацию о занятости.';
$labels['sync_no_id'] = 'Параметры CalDAV станут доступны после сохранения календаря.';
$labels['share_no_id'] = 'Параметры общего доступа станут доступны после сохранения календаря.';
$labels['publish_no_id'] = 'Параметры публикации станут доступны после сохранения календаря.';
$labels['import_no_id'] = 'Опции импорта и экспорта станут доступны после сохранения календаря.';
$labels['unable_to_save_calendar_data'] = 'Невозможно сохранить данные календаря.';
$labels['unable_to_retrieve_calendar_data'] = 'Невозможно получить данные календаря. Календарь может быть больше недоступен.';
$labels['unable_add_shared_calendar'] = 'Невозможно добавить общий календарь. Календарь может быть больше недоступен.';
$labels['itip_organizer_message'] = 'Вы были приглашены на это мероприятие, но не являетесь его организатором. Изменения, внесенные вами в это событие, не будут отправлены другим участникам.';
$labels['cannot_share_with_yourself'] = 'Вы не можете поделиться собственным календарем с самим собой.';
$labels['cannot_share_with_calendar_owner'] = 'Вы не можете поделиться этим календарем с владельцем календаря ($e)';
$labels['setting_default_notification_type'] = 'Уведомления по умолчанию для новых событий';
$labels['calendar_name_cannot_be_empty'] = 'Имя календаря не может быть пустым.';

// caldav server
$labels['sync_calendar'] = 'Синхронизировать календарь';
$labels['password_too_short'] = 'Пароль должен быть не менее 10 символов';
$labels['read_only'] = 'Только чтение';
$labels['add_connection'] = 'Добавить соединение';
$labels['show_details'] = 'Показать детали';
$labels['hide_details'] = 'Спрятать детали';
$labels['caldav_connection_password'] = 'Пароль, который вы указали при создании этого соединения.';
$labels['yes'] = 'Да';
$labels['no'] = 'Нет';
$labels['confirm_remove_sync'] = 'Вы уверены, что хотите навсегда удалить это соединение?';
$labels['remove_sync_error'] = 'При удалении соединения произошла ошибка.';
$labels['create_new_caldav_connection'] = 'Создать новое CalDav соединение';
$labels['your_caldav_connections'] = 'Ваше CalDav соединение';
$labels['created_at'] = 'Создано';
$labels['connected_at'] = 'Последняя синхронизация';
$labels['never'] = 'Никогда';
$labels['alternate_url'] = 'Альтернативный URL';
$labels['caldav_password_info'] = 'По соображениям безопасности этот пароль не сохраняется на сервере. Если вы забыли его, удалите это соединение и создайте новое.';
$labels['caldav_url_info'] = 'Используйте этот URL с приложениями, которые поддерживают обнаружение календаря, например: Календарь iOS и Mac OS, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Используйте этот URL с приложениями, которые не работают с коротким URL-адресом выше, например: Thunderbird Lightning. (Этот URL не будет работать с iOS и OSX Calendar.)';
$labels['invalid_caldav_url'] = 'Неверный URL-адрес сервера CalDAV.';
$labels['cannot_resolve_caldav_host'] = 'Не удается определить хост-компьютер сервера CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'Использование частных и зарезервированных адресов CalDAV запрещено.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Добавить общий календарь';
$labels['shared_calendars'] = 'Общие календари';
$labels['no_shared_calendars'] = 'Нет общих календарей, которые вы можете добавить.';
$labels['add_to_my_calendars'] = 'Добавить в мои календари';
$labels['remove_from_my_calendars'] = 'Удалить из моих календарей';
$labels['unshare'] = 'Удалить из открытого доступа';
$labels['unshare_warning'] = 'Если вы отмените данный календарь, он исчезнет из этого списка, и вы не сможете добавить его снова.';
$labels['calendar_name'] = 'Название календаря';
$labels['calendar_owner'] = 'Владелец календаря';
$labels['shared_date'] = 'Поделиться';
$labels['unable_to_share'] = 'Невозможно предоставить доступ к календарю следующим пользователям, поскольку их учетные записи электронной почты расположены на удаленном сервере:';

// shared calendar email
$labels['shared_email_subject'] = '$u поделился с вами календарем';
$labels['shared_email_body'] = 'Пользователь $u дал вам доступ к календарю под названием "$c." Чтобы добавить этот календарь в список активных календарей, нажмите на ссылку ниже:';
$labels['shared_email_body_cpanel'] = 'Пользователь $u дал вам доступ к календарю под названием "$c." Чтобы добавить этот календарь в список активных календарей, войдите в Roundcube, перейдите в «Календарь», щелкните значок «плюс» на боковой панели и выберите «Добавить общий календарь».';

// adding holiday calendars
$labels['holiday_calendars'] = 'Праздничные календари';
$labels['edit_holiday_calendar'] = 'Редактирование календаря праздников';
$labels['religious_holidays'] = 'Религиозные праздники';
$labels['country_holidays'] = 'Государственнные праздники';
$labels['add'] = 'Добавить';
$labels['no_more_holidays_allowed'] = 'Вы не можете добавить больше праздничных календарей.';
$labels['cannot_load_holidays'] = 'Невозможно загрузить праздники с сервера Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Добавить календарь дней рождения';
$labels['edit_birthday_calendar'] = 'Редактировать календарь дней рождения';
$labels['contact_birthdays'] = 'Контактные дни рождения';
$labels['contact_birthdays_info'] = 'Этот календарь извлекает информацию о днях рождения из списка контактов и отображает ее в виде событий календаря.';
$labels['birthday_event_title'] = 'День рождения: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Добавить Google календарь';
$labels['edit_google_calendar'] = 'Редактирование календаря Google';
$labels['google_id'] = 'Google календарь ID';
$labels['new_google_calendar'] = 'Новый Google календарь';
$labels['specify_google_calendar_id'] = 'Пожалуйста, введите идентификатор календаря Google';
$labels['calendar_id_format_error'] = 'Идентификатор календаря Google должен содержать символ @.';
$labels['cannot_load_google_calendar'] = 'Календарь Google "$c" не может быть загружен. Убедитесь, что идентификатор календаря верен и что календарь был общедоступен.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Добавить календарь CalDAV';
$labels['edit_caldav_calendar'] = 'Редактирование календаря CalDAV';
$labels['add_caldav_calendar_information'] = 'Чтобы подписаться на календарь CalDAV, укажите информацию о сервере CalDAV и нажмите кнопку Найти календари.';
$labels['caldav_url'] = 'URL-адрес CalDAV';
$labels['find_calendars'] = 'Найти календари';
$labels['searching_for_calendars'] = 'Поиск календарей, пожалуйста, подождите...';
$labels['select_calendars'] = 'Выберите календари для добавления:';
$labels['read_only_status_yes'] = 'Да';
$labels['read_only_status_no'] = 'Нет';
$labels['subscribed'] = 'Подписка';

$labels['refresh_calendar'] = 'Обновить календарь';
$labels['refresh_every_minute'] = 'Каждую минуту';
$labels['refresh_every_5_minutes'] = 'Каждые 5 минут';
$labels['refresh_every_15_minutes'] = 'Каждые 15 минут';
$labels['refresh_every_30_minutes'] = 'Каждые 30 минут';
$labels['refresh_every_60_minutes'] = 'Каждые 60 минут';
$labels['refresh_never'] = 'Никогда';

$labels['cannot_connect_to_caldav_server'] = 'Невозможно подключиться к серверу CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Невозможно подключиться к серверу CalDAV. Проверьте URL-адрес сервера.';
$labels['caldav_client_error_credentials'] = 'Неверное имя пользователя или пароль CalDAV.';
$labels['caldav_client_error_empty'] = 'Сервер не вернул ни одного календаря, который можно было бы добавить. Проверьте URL-адрес CalDAV.';
$labels['caldav_client_error_server_info'] = 'Неверная информация о сервере CalDAV.';
$labels['caldav_client_error_add_calendar'] = 'Невозможно сохранить календарь "$n".';
$labels['caldav_client_error_delete_event'] = 'Невозможно удалить событие';
$labels['caldav_client_error_save_event'] = 'Не удается сохранить событие CalDAV: возможно, оно было перемещено, удалено или календарь недоступен.';
$labels['caldav_client_error_calendar_added'] = 'Календарь "$n" уже есть в вашем списке календарей.';

// previewing event
$labels['remove_recurring_event'] = 'Удалить повторяющееся событие';
$labels['remove_one'] = 'Удалить только этот экземпляр';
$labels['remove_all'] = 'Удалить все экземпляры';
$labels['edit_event'] = 'Изменить событие';
$labels['event_removed'] = 'Событие удалено.';
$labels['go_to_url'] = 'Перейти к URL';
$labels['confirm_remove_event'] = 'Вы уверены, что хотите удалить это событие?';

// calendar/agenda grid
$labels['no_events'] = 'Нет событий для отображения.';

// importing events
$labels['select_calendar'] = 'Выбрать календарь';
$labels['select_calendar_for_events'] = 'Выберите календарь, в который вы хотите добавить события:';
$labels['add_events'] = 'Добавить событие';
$labels['add_events_to_calendar'] = 'Добавить событие в календарь';
$labels['events_imported_successfully'] = 'События успешно импортированы';
$labels['events_imported_with_errors'] = 'Не удалось импортировать некоторые события.';
$labels['error_importing_events'] = 'Произошла ошибка при импорте событий в календарь';

// editing events
$labels['calendar'] = 'Календарь';
$labels['new_event'] = 'Новое событие';
$labels['summary'] = 'Основное';
$labels['recurrence'] = 'Повторение';
$labels['title'] = 'Заглавие';
$labels['location'] = 'Местоположение';
$labels['description'] = 'Описание';
$labels['comment'] = 'Комментарий';
$labels['url'] = 'URL';
$labels['name'] = 'Имя';
$labels['start'] = 'Начало';
$labels['end'] = 'Окончание';
$labels['show_timezones'] = 'Показать часовые пояса';
$labels['reset_timezones'] = 'Обнулить часовые пояса';
$labels['all_day'] = 'Весь день';
$labels['use_calendar_colors'] = 'Использовать цвета календаря';
$labels['color'] = 'Цвет';
$labels['background'] = 'Фон';
$labels['text'] = 'Текст';
$labels['show_me_as'] = 'Показать как';
$labels['available'] = 'Доступен';
$labels['busy'] = 'Занят';
$labels['visibility'] = 'Видимость';
$labels['calendar_default'] = 'Календарь по умолчанию';
$labels['public'] = 'Общественный';
$labels['private'] = 'Частный';
$labels['repeat'] = 'Повторение';
$labels['repeat_every'] = 'Повторите каждые';
$labels['never'] = 'Никогда';
$labels['daily'] = 'Ежедневно';
$labels['weekly'] = 'Еженедельно';
$labels['monthly'] = 'Ежемесячно';
$labels['yearly'] = 'Ежегодно';
$labels['days'] = 'дни';
$labels['weeks'] = 'недели';
$labels['months'] = 'месяц(ы)';
$labels['years'] = 'год(ы)';
$labels['repeat_on'] = 'Повторять с';
$labels['repeat_by'] = 'Повторять по';
$labels['day_of_month'] = 'день месяца';
$labels['day_of_week'] = 'день недели';
$labels['not_specified'] = 'Не указано';
$labels['first'] = 'Первый';
$labels['second'] = 'Второй';
$labels['third'] = 'Третий';
$labels['fourth'] = 'Четвертый';
$labels['fifth'] = 'Пятый';
$labels['second_to_last'] = 'Предпоследний';
$labels['last'] = 'Последний';
$labels['attachment_too_large'] = 'Файл вложения слишком велик. Максимально допустимый размер $s.';
$labels['bg_color'] = 'Цвет фона';
$labels['tx_color'] = 'Цвет текста';
$labels['add_notification'] = 'Добавить уведомление';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'Это событие не\имеет никаких уведомлений.';
$labels['minutes'] = 'минуты';
$labels['hours'] = 'часы';
$labels['link'] = 'Ссылка';
$labels['attach_file_from_web'] = 'Прикрепить файл';
$labels['attachment_url'] = 'URL-адрес вложения';
$labels['invalid_url'] = 'Этот URL-адрес недействителен.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Диск';
$labels['duplicate'] = 'Дублировать';
$labels['duplicate_suffix'] = '(дубликат)';
$labels['no_notifications'] = 'Это событие не\имеет никаких уведомлений.';
$labels['this_calendar_is_hidden'] = 'Этот календарь скрыт. Сохраненные события не будут отображаться, пока календарь не станет видимым.';
$labels['make_calendar_visible'] = 'Сделайте этот календарь видимым.';

// recurrence
$labels['recurrence_range'] = 'Диапазон рецидивов';
$labels['repeat_forever'] = 'Повторять вечно';
$labels['repeat_every_weekday'] = 'Повторяйте каждый будний день';
$labels['last_day'] = 'Последний день';
$labels['every'] = 'Каждый';
$labels['weekday'] = 'Будни';
$labels['weekend_day'] = 'День выходного дня';
$labels['day_of_week'] = 'день недели';
$labels['day_of_month'] = 'день месяца';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Конец после';
$labels['occurrences'] = 'вхождение';

// end recurrence on date:
$labels['end_by_date'] = 'Окончание по дате';
$labels['removed_events_title'] = 'События, удаленные из повторяющихся серий';
$labels['restore'] = 'Восстановить';

// settings page
$labels['setting_view'] = 'Начальный просмотр календаря';
$labels['setting_first_day'] = 'Неделя начинается с';
$labels['setting_agenda_week_span'] = 'Срок выполнения повестки дня (недели)';
$labels['setting_slot_duration'] = 'Продолжительность(мин)';
$labels['setting_scroll_time'] = 'Выделит время';
$labels['setting_calendar'] = 'Календарь по умолчанию для событий';
$labels['setting_refresh'] = 'Обновление календарей';
$labels['setting_week_numbers'] = 'Номера недель в маленьком календаре';
$labels['setting_event_border'] = 'Цвета категорий как границы событий';
$labels['show_sunrise'] = 'Показать время восходя солнца в месяц';
$labels['show_sunset'] = 'Показать время захода солнца в месяц';
$labels['categories'] = 'Категории';
$labels['category'] = 'Категория';
$labels['no_category'] = 'Без категории';
$labels['add_category'] = 'Добавить категорию';
$labels['setting_alarm_sound'] = 'Звуковое оповещение';
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
$labels['play_sound'] = 'Воспроизведение звука';

// alarms
$labels['upcoming_events'] = 'Предстоящие события';
$labels['dismiss'] = 'Отклонить';
$labels['dismiss_all'] = 'Отклонить все';
$labels['snooze'] = 'Отложить';
$labels['5_minutes'] = '5 минут';
$labels['10_minutes'] = '10 минут';
$labels['20_minutes'] = '20 минут';
$labels['30_minutes'] = '30 минут';
$labels['1_hour'] = '1 час';
$labels['2_hours'] = '2 часа';
$labels['tomorrow'] = 'До завтра';
$labels['next_week'] = 'До следующей недели';

// sidebar
$labels['setting_show_xcalendar'] = 'Показать календарь мероприятий';
$labels['todays_agenda'] = 'Сегодняшние мероприятия';
$labels['no_events_today'] = 'Нет запланированных событий на сегодня.';
$labels['sidebar_more'] = 'Ещё...';

// event invitation
$labels['itip_title_request'] = 'Вы приглашены на мероприятие.';
$labels['itip_title_modified'] = 'Это событие было изменено.';
$labels['itip_title_reply_accepted'] = '$u принял ваше приглашение';
$labels['itip_title_reply_tentative'] = '$u предварительно принял ваше приглашение';
$labels['itip_title_reply_declined'] = '$u отклонил ваше приглашение';
$labels['itip_title_cancel'] = 'Это мероприятие было отменено.';

$labels['itip_email_subject_request'] = 'Приглашение:';
$labels['itip_email_subject_request_modified'] = 'Событие изменено:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Отменено:';

$labels['itip_popup_event_added'] = 'Событие добавлено в календарь.';
$labels['itip_popup_event_updated'] = 'Событие обновлено в календаре.';
$labels['itip_popup_event_deleted'] = 'Событие удалено из календаря.';
$labels['itip_popup_response_sent'] = 'Ответ отправлен организатору.';

$labels['itip_message_invitation_accepted'] = 'Вы приняли это приглашение.';
$labels['itip_message_invitation_tentative'] = 'Вы предварительно приняли это приглашение.';
$labels['itip_message_invitation_declined'] = 'Вы отклонили это приглашение.';
$labels['itip_message_event_deleted'] = 'Событие было удалено из вашего календаря.';
$labels['itip_message_event_updated'] = 'Событие в вашем календаре было обновлено.';
$labels['itip_message_event_missing'] = 'Этого события нет ни в одном из ваших календарей.';

$labels['itip_accept'] = 'Принять';
$labels['itip_tentative'] = 'Предварительный';
$labels['itip_decline'] = 'Снижение';
$labels['itip_change_reponse'] = 'Реакция на изменение.';
$labels['itip_add_to_calendar'] = 'Добавить в календарь';
$labels['itip_reply_to_sender'] = 'Ответить отправителю';
$labels['itip_dont_import'] = 'Не добавлять в календарь';
$labels['itip_update_in_calendar'] = 'Обновление события в календаре';
$labels['itip_delete_from_calendar'] = 'Удаление события из календаря';

$labels['mail_subject_invitation'] = 'Приглашение:';
$labels['mail_subject_update'] = 'Обновленное приглашение:';
$labels['mail_subject_cancel'] = 'Событие отменено:';

// event email notifications
$labels['mail_title'] = 'Заглавие:';
$labels['mail_start'] = 'Начало:';
$labels['mail_end'] = 'Окончание:';
$labels['mail_description'] = 'Описание:';
$labels['mail_location'] = 'Расположение:';

// event attendees
$labels['attendees'] = 'Участники';
$labels['attendee_role'] = 'Роль';
$labels['attendee_role_required'] = 'Обязательно';
$labels['attendee_role_optional'] = 'Опционально';
$labels['attendee_role_chair'] = 'Руководитель';
$labels['attendee_role_none'] = 'Не участник';
$labels['organizer'] = 'Организатор';
$labels['attendance'] = 'Участие';
$labels['your_status'] = 'Ваш статус';
$labels['attendance_going'] = 'Уходишь?';
$labels['attendee_status_yes'] = 'Да';
$labels['attendee_status_no'] = 'Нет';
$labels['attendee_status_maybe'] = 'Может быть';
$labels['attendee_status_delegated'] = 'Делегировано';
$labels['attendee_status_waiting'] = 'Ожидание';
$labels['attendee_status_waiting_for_confirmation'] = 'Ожидание подтверждения';
$labels['attendee_status_info_yes'] = 'Участвует';
$labels['attendee_status_info_no'] = 'Не участвует';
$labels['attendee_status_info_maybe'] = 'Может быть участвует';
$labels['attendee_status_info_delegated'] = 'Делегировал участие';
$labels['attendee_role'] = 'Роль';
$labels['attendee_role_info'] = 'Укажите должен ли данный участник присутствовать или руководить мероприятием.';
$labels['attendee_notify'] = 'Уведомить';
$labels['attendee_notify_info'] = 'Если отмечено, участнику будет отправлено уведомление по email о приглашении.';
$labels['add_attendee'] = 'Добавить Участника';
$labels['attendee_notify_question'] = 'Отправить уведомления по email участникам?';
$labels['attendee_notify_textarea'] = 'Дополните опциональным сообщением';
$labels['attendee_notify_send'] = 'Отправить';
$labels['attendee_notify_dont'] = 'Не отправлять';
$labels['attendee_notify_cancel'] = 'Отменить';
$labels['new_attendee_not_saved_warning'] = 'Вы ввели адрес электронной почты участника, но не добавили его в список участников. Продолжить сохранение без этого участника?';

// import / export
$labels['import_export'] = 'Импорт / Экспорт';
$labels['export_to_file'] = 'Экспортировать в файл';
$labels['export_info'] = 'Нажмите на эту кнопку, чтобы экспортировать события этого календаря в файл в формате iCalendar (ics). Если календарь содержит много событий, этот процесс может занять некоторое время.';
$labels['export_no_events'] = 'В данном календаре отсутствуют события для экспорта.';
$labels['import_from_file'] = 'Импортировать из файла';
$labels['import_info'] = 'Нажмите на эту кнопку, чтобы импортировать в этот календарь события из файла в формате iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Напоминание о назначении';
$labels['email_notification_subject'] = 'Напоминание: $t в $d ($z)';
$labels['email_notification_cron_error'] = 'Уведомления по электронной почте отключены. Обратитесь к администратору.';
$labels['alarm_before_event_starts'] = 'Перед началом события';
$labels['alarm_after_event_starts'] = 'После начала события';
$labels['alarm_before_event_ends'] = 'Перед окончанием события';
$labels['alarm_after_event_ends'] = 'После завершения события';
$labels['alarm_at_specific_time'] = 'В определенное время';

// search
$labels['specify_at_least_one_calendar'] = 'Укажите хотя бы один календарь для поиска.';
$labels['specify_text_to_search'] = 'Введите текст для поиска.';
$labels['clear_search_results'] = 'Очистить';
$labels['search_outdated_message'] = 'Результаты поиска могут быть устаревшими. Подумайте о повторном запуске поиска.';
$labels['date_range'] = 'Диапазон дат';
$labels['date_range_subtitle'] = 'Диапазон дат:';
$labels['search_results'] = 'Найдено результатов: $n';
$labels['modify_search_options'] = 'Попробуйте изменить параметры поиска.';
$labels['show_shortcuts'] = 'Показать ярлыки';
$labels['hide_shortcuts'] = 'Скрыть ярлыки';
$labels['this_month'] = 'В этом месяце';
$labels['this_year'] = 'В этом году';
$labels['past_30_days'] = 'Последние 30 дней';
$labels['next_30_days'] = 'Следующие 30 дней';
$labels['past_6_months'] = 'Последние 6 месяцев';
$labels['next_6_months'] = 'Следующие 6 месяцев';

// calendar grid
$labels['cannot_load_events'] = 'Невозможно загрузить события для календаря "$c".';
