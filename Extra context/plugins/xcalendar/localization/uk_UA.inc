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
$labels['create_event'] = 'Створити подію';
$labels['create_event_button'] = 'Нова подія';
$labels['day'] = 'День';
$labels['week'] = 'Тиждень';
$labels['month'] = 'Місяць';
$labels['agenda'] = 'Порядок денний';

// editing calendars
$labels['calendars'] = 'Календарі';
$labels['my_calendar'] = 'Мій календар';
$labels['create_calendar'] = 'Створити календар';
$labels['new_calendar'] = 'Новий календар';
$labels['edit_calendar'] = 'Редагувати календар';
$labels['remove_calendar'] = 'Видалити календар';
$labels['confirm_remove_calendar'] = 'Ви впевнені, що хочете видалити цей календар?';
$labels['calendar_removed'] = 'Календар видалено.';
$labels['notifications'] = 'Сповіщення';
$labels['default_event_visibility'] = 'Видимість подій за замовчуванням';
$labels['time_zone'] = 'Часовий пояс календаря';
$labels['share_calendar'] = 'Поділитися календарем';
$labels['permissions'] = 'Дозволи';
$labels['permission_publish_calendar'] = 'Опублікувати календар';
$labels['permission_share_calendar'] = 'Поділитися календарем';
$labels['permission_edit_events'] = 'Редагувати події';
$labels['permission_see_details'] = 'Дивитися деталі';
$labels['permission_publish_calendar_info'] = 'Якщо встановлено позначку, користувач зможе опублікувати календар.';
$labels['permission_share_calendar_info'] = 'Якщо встановлено позначку, користувач зможе поділитися календарем з іншими користувачами.';
$labels['permission_edit_events_info'] = 'Якщо встановлено позначку, користувач зможе редагувати та видаляти події в календарі.';
$labels['permission_see_details_info'] = 'Якщо встановлено позначку, будуть доступні всі деталі подій. Якщо позначка скасована, буде доступна інформація тільки про доступність/зайнятість.';
$labels['share_send_notification'] = 'Сповіщення';
$labels['share_send_notification_info'] = 'Якщо встановлено позначку, на електронну пошту користувачу буде відправлено сповіщення про новий поділений календар.';
$labels['new_share_not_saved_warning'] = 'Ви ввели електронну адресу для поділу календаря, але не натиснули кнопку "Поділитися", щоб додати її до списку поділених. Продовжити збереження без поділу календаря з вказаним користувачем?';
$labels['share'] = 'Поділитися';
$labels['shared_marker'] = '(Поділений)';
$labels['owner'] = 'Власник';
$labels['publish_calendar'] = 'Опублікувати календар';
$labels['create_link'] = 'Створити посилання';
$labels['reset'] = 'Скинути';
$labels['remove'] = 'Видалити';
$labels['publish_full'] = 'Опублікувати всю інформацію про події.';
$labels['publish_busy'] = 'Опублікувати інформацію про доступність/зайнятість.';
$labels['sync_no_id'] = 'Параметри CalDAV стануть доступними після збереження календаря.';
$labels['share_no_id'] = 'Параметри спільного доступу до календаря стануть доступними після збереження календаря.';
$labels['publish_no_id'] = 'Параметри публікації календаря стануть доступними після збереження календаря.';
$labels['import_no_id'] = 'Опції імпорту та експорту стануть доступними після збереження календаря.';
$labels['unable_to_save_calendar_data'] = 'Неможливо зберегти дані календаря.';
$labels['unable_to_retrieve_calendar_data'] = 'Неможливо отримати дані календаря. Календар може бути недоступним.';
$labels['unable_add_shared_calendar'] = 'Неможливо додати спільний календар. Календар може бути недоступним.';
$labels['itip_organizer_message'] = 'Вас запрошено на цю подію, але ви не є організатором. Зміни, які ви вносите до цієї події, не будуть відправлені іншим учасникам.';
$labels['cannot_share_with_yourself'] = 'Ви не можете поділитися своїм календарем самі з собою.';
$labels['cannot_share_with_calendar_owner'] = 'Ви не можете поділитися цим календарем з власником календаря ($e)';
$labels['setting_default_notification_type'] = 'Тип сповіщень за замовчуванням';
$labels['calendar_name_cannot_be_empty'] = 'Назва календаря не може бути порожньою.';

// caldav server
$labels['sync_calendar'] = 'Синхронізація календаря';
$labels['password_too_short'] = 'Пароль повинен бути щонайменше 10 символів';
$labels['read_only'] = 'Тільки для читання';
$labels['add_connection'] = 'Додати з\'єднання';
$labels['show_details'] = 'Показати деталі';
$labels['hide_details'] = 'Сховати деталі';
$labels['caldav_connection_password'] = 'Пароль, який ви вказали при створенні цього з\'єднання.';
$labels['yes'] = 'Так';
$labels['no'] = 'Ні';
$labels['confirm_remove_sync'] = 'Ви впевнені, що хочете остаточно видалити це з\'єднання?';
$labels['remove_sync_error'] = 'Під час видалення з\'єднання виникла помилка.';
$labels['create_new_caldav_connection'] = 'Створити нове з\'єднання CalDav';
$labels['your_caldav_connections'] = 'Ваші з\'єднання CalDav';
$labels['created_at'] = 'Створено';
$labels['connected_at'] = 'Остання синхронізація';
$labels['never'] = 'Ніколи';
$labels['alternate_url'] = 'Альтернативна URL-адреса';
$labels['caldav_password_info'] = 'З міркувань безпеки цей пароль не зберігається на сервері. Якщо ви його забули, видаліть це з\'єднання і створіть нове.';
$labels['caldav_url_info'] = 'Використовуйте цю URL-адресу з додатками, які підтримують пошук календарів, наприклад: календар iOS і Mac OS, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Використовуйте цю URL-адресу з додатками, які не працюють з короткою URL-адресою вище, наприклад: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'Неправильна URL-адреса сервера CalDAV.';
$labels['cannot_resolve_caldav_host'] = 'Не вдається визначити ім\'я хоста сервера CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'Приватні та зарезервовані адреси CalDAV заборонені.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Додати спільний календар';
$labels['shared_calendars'] = 'Спільні календарі';
$labels['no_shared_calendars'] = 'Немає спільних календарів, які можна додати.';
$labels['add_to_my_calendars'] = 'Додати до моїх календарів';
$labels['remove_from_my_calendars'] = 'Видалити з моїх календарів';
$labels['unshare'] = 'Скасувати спільний доступ';
$labels['unshare_warning'] = 'Якщо ви скасуєте спільний доступ до цього календаря, він зникне з цього списку, і ви не зможете додати його знову.';
$labels['calendar_name'] = 'Назва календаря';
$labels['calendar_owner'] = 'Власник календаря';
$labels['shared_date'] = 'Спільний доступ';
$labels['unable_to_share'] = 'Календар не може бути спільно доступним для наступних користувачів через те, що їхні електронні адреси знаходяться на віддаленому сервері:';

// shared calendar email
$labels['shared_email_subject'] = '$u поділився календарем з вами';
$labels['shared_email_body'] = 'Користувач $u дав вам доступ до календаря під назвою "$c." Щоб додати цей календар до списку активних календарів, натисніть на посилання нижче:';
$labels['shared_email_body_cpanel'] = 'Користувач $u надав вам доступ до календаря під назвою "$c." Щоб додати цей календар до списку активних календарів, скористайтеся наступними параметрами:';

// adding holiday calendars
$labels['holiday_calendars'] = 'Календарі відпусток';
$labels['edit_holiday_calendar'] = 'Редагування календаря свят';
$labels['religious_holidays'] = 'Релігійні свята';
$labels['country_holidays'] = 'Національні свята';
$labels['add'] = 'Додати';
$labels['no_more_holidays_allowed'] = 'Ви не можете додати більше календарів відпусток.';
$labels['cannot_load_holidays'] = 'Не вдається завантажити свята з сервера Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Додати календар днів народження';
$labels['edit_birthday_calendar'] = 'Редагувати календар днів народження';
$labels['contact_birthdays'] = 'Зв\'язатися з Днями народження';
$labels['contact_birthdays_info'] = 'Цей календар отримує інформацію про день народження з вашого списку контактів і відображає її як події календаря.';
$labels['birthday_event_title'] = 'День народження: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Додати календар Google';
$labels['edit_google_calendar'] = 'Редагування календаря Google';
$labels['google_id'] = 'Ідентифікатор календаря Google';
$labels['new_google_calendar'] = 'Новий календар Google';
$labels['specify_google_calendar_id'] = 'Будь ласка, введіть ідентифікатор календаря Google';
$labels['calendar_id_format_error'] = 'Ідентифікатор календаря Google повинен містити символ @.';
$labels['cannot_load_google_calendar'] = 'Не вдається завантажити календар Google "$c". Переконайтеся, що ідентифікатор календаря правильний і що календар доступний для загального користування.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Додати календар CalDAV';
$labels['edit_caldav_calendar'] = 'Редагування календаря CalDAV';
$labels['add_caldav_calendar_information'] = 'Щоб підписатися на календар CalDAV, вкажіть інформацію про сервер CalDAV і натисніть кнопку Знайти календарі.';
$labels['caldav_url'] = 'URL-адреса CalDAV';
$labels['find_calendars'] = 'Знайти календарі';
$labels['searching_for_calendars'] = 'Пошук календарів, будь ласка, зачекайте...';
$labels['select_calendars'] = 'Виберіть календарі для додавання:';
$labels['read_only_status_yes'] = 'Так.';
$labels['read_only_status_no'] = 'Ні.';
$labels['subscribed'] = 'Підписано';

$labels['refresh_calendar'] = 'Оновити календар';
$labels['refresh_every_minute'] = 'Щохвилини';
$labels['refresh_every_5_minutes'] = 'Кожні 5 хвилин';
$labels['refresh_every_15_minutes'] = 'Кожні 15 хвилин';
$labels['refresh_every_30_minutes'] = 'Кожні 30 хвилин';
$labels['refresh_every_60_minutes'] = 'Кожні 60 хвилин';
$labels['refresh_never'] = 'Ніколи.';

$labels['cannot_connect_to_caldav_server'] = 'Не вдається з\'єднатися з сервером CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Не вдається підключитися до сервера CalDAV. Перевірте URL-адресу сервера.';
$labels['caldav_client_error_credentials'] = 'Неправильне ім\'я користувача або пароль CalDAV.';
$labels['caldav_client_error_empty'] = 'Сервер не повернув жодного календаря, який можна було б додати. Перевірте URL-адресу CalDAV.';
$labels['caldav_client_error_server_info'] = 'Неправильна інформація про сервер CalDAV.';
$labels['caldav_client_error_add_calendar'] = 'Не вдається зберегти календар "$n".';
$labels['caldav_client_error_delete_event'] = 'Не вдається видалити подію';
$labels['caldav_client_error_save_event'] = 'Неможливо зберегти подію CalDAV: можливо, її було переміщено або видалено, або календар недоступний.';
$labels['caldav_client_error_calendar_added'] = 'Календар "$n" вже є у вашому списку календарів.';

// previewing event
$labels['remove_recurring_event'] = 'Видалити повторюючуся подію';
$labels['remove_one'] = 'Видалити лише цей екземпляр';
$labels['remove_all'] = 'Видалити всі екземпляри';
$labels['edit_event'] = 'Редагувати подію';
$labels['event_removed'] = 'Подію видалено.';
$labels['go_to_url'] = 'Перейти за посиланням';
$labels['confirm_remove_event'] = 'Ви впевнені, що хочете видалити цю подію?';

// calendar/agenda grid
$labels['no_events'] = 'Немає подій для відображення.';

// importing events
$labels['select_calendar'] = 'Виберіть календар';
$labels['select_calendar_for_events'] = 'Виберіть календар, до якого ви хочете додати події:';
$labels['add_events'] = 'Додати події';
$labels['add_events_to_calendar'] = 'Додати події до календаря';
$labels['events_imported_successfully'] = 'Події успішно імпортовано';
$labels['events_imported_with_errors'] = 'Деякі інші події не вдалося імпортувати.';
$labels['error_importing_events'] = 'Виникла помилка під час імпорту подій до календаря';

// editing events
$labels['calendar'] = 'Календар';
$labels['new_event'] = 'Нова подія';
$labels['summary'] = 'Заголовок';
$labels['recurrence'] = 'Повторення';
$labels['title'] = 'Заголовок';
$labels['location'] = 'Місце розташування';
$labels['description'] = 'Опис';
$labels['comment'] = 'Коментар';
$labels['url'] = 'URL-адреса';
$labels['name'] = 'Ім\'я';
$labels['start'] = 'Початок';
$labels['end'] = 'Завершення';
$labels['show_timezones'] = 'Показувати часові пояси';
$labels['reset_timezones'] = 'Скинути часові пояси на типові';
$labels['all_day'] = 'Увесь день';
$labels['use_calendar_colors'] = 'Використовувати кольори календаря';
$labels['color'] = 'Колір';
$labels['background'] = 'Фон';
$labels['text'] = 'Текст';
$labels['show_me_as'] = 'Показувати як';
$labels['available'] = 'Доступний';
$labels['busy'] = 'Зайнятий';
$labels['visibility'] = 'Видимість';
$labels['calendar_default'] = 'Типовий календар';
$labels['public'] = 'Публічний';
$labels['private'] = 'Приватний';
$labels['repeat'] = 'Повторювати';
$labels['repeat_every'] = 'Повторювати кожен';
$labels['never'] = 'Ніколи';
$labels['daily'] = 'Щоденно';
$labels['weekly'] = 'Щотижнево';
$labels['monthly'] = 'Щомісяця';
$labels['yearly'] = 'Щорічно';
$labels['days'] = 'дні(в)';
$labels['weeks'] = 'тижні(в)';
$labels['months'] = 'місяці(в)';
$labels['years'] = 'роки(в)';
$labels['repeat_on'] = 'Повторювати в';
$labels['repeat_by'] = 'Повторювати за';
$labels['day_of_month'] = 'день місяця';
$labels['day_of_week'] = 'день тижня';
$labels['not_specified'] = 'Не вказано';
$labels['first'] = 'Перший';
$labels['second'] = 'Другий';
$labels['third'] = 'Третій';
$labels['fourth'] = 'Четвертий';
$labels['fifth'] = 'П\'ятий';
$labels['second_to_last'] = 'Передостанній';
$labels['last'] = 'Останній';
$labels['attachment_too_large'] = 'Файл вкладення занадто великий. Максимально допустимий розмір - $s.';
$labels['bg_color'] = 'Колір фону';
$labels['tx_color'] = 'Колір тексту';
$labels['add_notification'] = 'Додати сповіщення';
$labels['popup'] = 'Спливаюче вікно';
$labels['no_notifications'] = 'У цій події немає сповіщень.';
$labels['minutes'] = 'хвилин(и)';
$labels['hours'] = 'годин(и)';
$labels['link'] = 'Посилання';
$labels['attach_file_from_web'] = 'Прикріпити файл';
$labels['attachment_url'] = 'URL-адреса вкладення';
$labels['invalid_url'] = 'Ця URL-адреса є недійсною.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Диск';
$labels['duplicate'] = 'Дублювати';
$labels['duplicate_suffix'] = '(дублікат)';
$labels['no_notifications'] = 'У цій події немає сповіщень.';
$labels['this_calendar_is_hidden'] = 'Цей календар приховано. Збережені події не відображатимуться, доки календар не стане видимим.';
$labels['make_calendar_visible'] = 'Зробити цей календар видимим.';

// recurrence
$labels['recurrence_range'] = 'Діапазон повторюваності';
$labels['repeat_forever'] = 'Повторювати вічно';
$labels['repeat_every_weekday'] = 'Повторюйте кожен будній день';
$labels['last_day'] = 'Останній день';
$labels['every'] = 'Кожен';
$labels['weekday'] = 'Будний день';
$labels['weekend_day'] = 'Вихідний день';
$labels['day_of_week'] = 'день тижня';
$labels['day_of_month'] = 'день місяця';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Закінчення після';
$labels['occurrences'] = 'випадків';

// end recurrence on date:
$labels['end_by_date'] = 'Закінчення за датою';
$labels['removed_events_title'] = 'Події, видалені з повторюваної серії';
$labels['restore'] = 'Відновити';

// settings page
$labels['setting_view'] = 'Початковий вигляд календаря';
$labels['setting_first_day'] = 'Тиждень починається з';
$labels['setting_agenda_week_span'] = 'Часовий діапазон порядку денного (тижні)';
$labels['setting_slot_duration'] = 'Часовий слот дня (хвилини)';
$labels['setting_scroll_time'] = 'Прокручувати до часу';
$labels['setting_calendar'] = 'Типовий календар для подій';
$labels['setting_refresh'] = 'Оновити календарі';
$labels['setting_week_numbers'] = 'Номери тижнів у малому календарі';
$labels['setting_event_border'] = 'Кольори категорій як межі подій';
$labels['show_sunrise'] = 'Показувати схід сонця у виді місяця';
$labels['show_sunset'] = 'Показувати захід сонця у виді місяця';
$labels['categories'] = 'Категорії';
$labels['category'] = 'Категорія';
$labels['no_category'] = 'Без категорії';
$labels['add_category'] = 'Додати категорію';
$labels['setting_alarm_sound'] = 'Звук сповіщення';
$labels['alarm_sound_aurora'] = 'Аврора';
$labels['alarm_sound_bamboo'] = 'Бамбук';
$labels['alarm_sound_bells'] = 'Дзвоники';
$labels['alarm_sound_chord'] = 'Акорд';
$labels['alarm_sound_circles'] = 'Кола';
$labels['alarm_sound_glass'] = 'Скло';
$labels['alarm_sound_maramba'] = 'Марамба';
$labels['alarm_sound_metal'] = 'Метал';
$labels['alarm_sound_ripple'] = 'Хвилі';
$labels['alarm_sound_startrek'] = 'Зоряний шлях';
$labels['alarm_sound_whistle'] = 'Свист';
$labels['alarm_sound_yoohoo'] = 'Юху';
$labels['play_sound'] = 'Відтворити звук';

// alarms
$labels['upcoming_events'] = 'Майбутні події';
$labels['dismiss'] = 'Відхилити';
$labels['dismiss_all'] = 'Відхилити всі';
$labels['snooze'] = 'Спустити на деякий час';
$labels['5_minutes'] = '5 хвилин';
$labels['10_minutes'] = '10 хвилин';
$labels['20_minutes'] = '20 хвилин';
$labels['30_minutes'] = '30 хвилин';
$labels['1_hour'] = '1 година';
$labels['2_hours'] = '2 години';
$labels['tomorrow'] = 'До завтра';
$labels['next_week'] = 'До наступного тижня';

// sidebar
$labels['setting_show_xcalendar'] = 'Показувати порядок денний календаря';
$labels['todays_agenda'] = 'Порядок денний на сьогодні';
$labels['no_events_today'] = 'На сьогодні немає запланованих подій.';
$labels['sidebar_more'] = 'Додатково...';

// event invitation
$labels['itip_title_request'] = 'Вас запрошено на подію.';
$labels['itip_title_modified'] = 'Ця подія була змінена.';
$labels['itip_title_reply_accepted'] = '$u прийняв ваше запрошення';
$labels['itip_title_reply_tentative'] = '$u тимчасово прийняв ваше запрошення';
$labels['itip_title_reply_declined'] = '$u відхилив ваше запрошення';
$labels['itip_title_cancel'] = 'Ця подія скасована.';

$labels['itip_email_subject_request'] = 'Запрошення:';
$labels['itip_email_subject_request_modified'] = 'Подія змінена:';
$labels['itip_email_subject_reply'] = 'Відповідь:';
$labels['itip_email_subject_cancel'] = 'Скасовано:';

$labels['itip_popup_event_added'] = 'Подія додана до календаря.';
$labels['itip_popup_event_updated'] = 'Подія оновлена в календарі.';
$labels['itip_popup_event_deleted'] = 'Подія видалена з календаря.';
$labels['itip_popup_response_sent'] = 'Відповідь надіслана організатору.';

$labels['itip_message_invitation_accepted'] = 'Ви прийняли це запрошення.';
$labels['itip_message_invitation_tentative'] = 'Ви тимчасово прийняли це запрошення.';
$labels['itip_message_invitation_declined'] = 'Ви відхилили це запрошення.';
$labels['itip_message_event_deleted'] = 'Подію видалено з вашого календаря.';
$labels['itip_message_event_updated'] = 'Подія в вашому календарі оновлена.';
$labels['itip_message_event_missing'] = 'Ця подія не існує в жодному з ваших календарів.';

$labels['itip_accept'] = 'Прийняти';
$labels['itip_tentative'] = 'Тимчасово';
$labels['itip_decline'] = 'Відхилити';
$labels['itip_change_reponse'] = 'Змінити відповідь.';
$labels['itip_add_to_calendar'] = 'Додати до календаря';
$labels['itip_reply_to_sender'] = 'Відповісти відправнику';
$labels['itip_dont_import'] = 'Не додавати до календаря';
$labels['itip_update_in_calendar'] = 'Оновити подію в календарі';
$labels['itip_delete_from_calendar'] = 'Видалити подію з календаря';

$labels['mail_subject_invitation'] = 'Запрошення:';
$labels['mail_subject_update'] = 'Оновлене запрошення:';
$labels['mail_subject_cancel'] = 'Подія скасована:';

// event email notifications
$labels['mail_title'] = 'Заголовок:';
$labels['mail_start'] = 'Початок:';
$labels['mail_end'] = 'Завершення:';
$labels['mail_description'] = 'Опис:';
$labels['mail_location'] = 'Місце розташування:';

// event attendees
$labels['attendees'] = 'Учасники';
$labels['attendee_role'] = 'Роль';
$labels['attendee_role_required'] = 'Обов\'язковий';
$labels['attendee_role_optional'] = 'Необов\'язковий';
$labels['attendee_role_chair'] = 'Голова';
$labels['attendee_role_none'] = 'Неучасник';
$labels['organizer'] = 'Організатор';
$labels['attendance'] = 'Участь';
$labels['your_status'] = 'Ваш статус';
$labels['attendance_going'] = 'Підтверджуєте участь?';
$labels['attendee_status_yes'] = 'Так';
$labels['attendee_status_no'] = 'Ні';
$labels['attendee_status_maybe'] = 'Можливо';
$labels['attendee_status_delegated'] = 'Делеговано';
$labels['attendee_status_waiting'] = 'Очікування';
$labels['attendee_status_waiting_for_confirmation'] = 'Очікування підтвердження';
$labels['attendee_status_info_yes'] = 'Братиме участь';
$labels['attendee_status_info_no'] = 'Не братиме участь';
$labels['attendee_status_info_maybe'] = 'Можливо візьме участь';
$labels['attendee_status_info_delegated'] = 'Делегована участь';
$labels['attendee_role'] = 'Роль';
$labels['attendee_role_info'] = 'Вкажіть, чи обов\'язково брати участь учасникові, або чи він є головою зустрічі.';
$labels['attendee_notify'] = 'Повідомити';
$labels['attendee_notify_info'] = 'Якщо встановлено позначку, лист-запрошення буде відправлено учасникові.';
$labels['add_attendee'] = 'Додати учасника';
$labels['attendee_notify_question'] = 'Відправити листи-повідомлення учасникам?';
$labels['attendee_notify_textarea'] = 'Додати необов\'язкове повідомлення';
$labels['attendee_notify_send'] = 'Відправити';
$labels['attendee_notify_dont'] = 'Не відправляти';
$labels['attendee_notify_cancel'] = 'Скасувати';
$labels['new_attendee_not_saved_warning'] = 'Ви ввели адресу електронної пошти учасника, але не додали його до списку учасників. Продовжити збереження без цього учасника?';

// import / export
$labels['import_export'] = 'Імпорт / Експорт';
$labels['export_to_file'] = 'Експортувати в файл';
$labels['export_info'] = 'Натисніть цю кнопку, щоб експортувати та завантажити події цього календаря у форматі iCalendar (ics). Якщо в календарі є багато подій, процес експорту може зайняти деякий час.';
$labels['export_no_events'] = 'В цьому календарі немає подій для експорту.';
$labels['import_from_file'] = 'Імпортувати з файлу';
$labels['import_info'] = 'Натисніть цю кнопку, щоб імпортувати події в цей календар з файлу у форматі iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Нагадування про зустріч';
$labels['email_notification_subject'] = 'Нагадування: $t о $d ($z)';
$labels['email_notification_cron_error'] = 'Електронні повідомлення вимкнені. Зверніться до адміністратора.';
$labels['alarm_before_event_starts'] = 'Перед початком події';
$labels['alarm_after_event_starts'] = 'Після початку події';
$labels['alarm_before_event_ends'] = 'Перед завершенням події';
$labels['alarm_after_event_ends'] = 'Після завершення події';
$labels['alarm_at_specific_time'] = 'В певний час';

// search
$labels['specify_at_least_one_calendar'] = 'Вкажіть принаймні один календар для пошуку.';
$labels['specify_text_to_search'] = 'Введіть текст для пошуку.';
$labels['clear_search_results'] = 'Очистити';
$labels['search_outdated_message'] = 'Результати пошуку можуть бути застарілими. Розгляньте можливість повторного виконання пошуку.';
$labels['date_range'] = 'Діапазон дат';
$labels['date_range_subtitle'] = 'Діапазон дат:';
$labels['search_results'] = 'Знайдено результатів: $n';
$labels['modify_search_options'] = 'Спробуйте змінити параметри пошуку.';
$labels['show_shortcuts'] = 'Показати ярлики';
$labels['hide_shortcuts'] = 'Сховати ярлики';
$labels['this_month'] = 'Цього місяця';
$labels['this_year'] = 'Цього року';
$labels['past_30_days'] = 'Останні 30 днів';
$labels['next_30_days'] = 'Наступні 30 днів';
$labels['past_6_months'] = 'Останні 6 місяців';
$labels['next_6_months'] = 'Наступні 6 місяців';

// calendar grid
$labels['cannot_load_events'] = 'Не вдається завантажити події для календаря "$c".';
