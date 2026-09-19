<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'التقويم';
$labels['app_menu_title'] = 'التقويم';

// menu
$labels['create_event'] = 'إنشاء حدث';
$labels['create_event_button'] = 'حدث جديد';
$labels['day'] = 'اليوم';
$labels['week'] = 'الأسبوع';
$labels['month'] = 'الشهر';
$labels['agenda'] = 'جدول الأعمال';

// editing calendars
$labels['calendars'] = 'التقويمات';
$labels['my_calendar'] = 'التقويم الخاص بي';
$labels['create_calendar'] = 'إنشاء تقويم';
$labels['new_calendar'] = 'التقويم الجديد';
$labels['edit_calendar'] = 'تحرير التقويم';
$labels['remove_calendar'] = 'حذف التقويم';
$labels['confirm_remove_calendar'] = 'هل أنت متأكد من رغبتك في حذف هذا التقويم؟';
$labels['calendar_removed'] = 'تم حذف التقويم.';
$labels['notifications'] = 'الإشعارات';
$labels['default_event_visibility'] = 'الرؤية الافتراضية للحدث الافتراضي';
$labels['time_zone'] = 'المنطقة الزمنية للتقويم';
$labels['share_calendar'] = 'مشاركة التقويم';
$labels['permissions'] = 'الأذونات';
$labels['permission_publish_calendar'] = 'نشر التقويم';
$labels['permission_share_calendar'] = 'مشاركة التقويم';
$labels['permission_edit_events'] = 'تحرير الأحداث';
$labels['permission_see_details'] = 'انظر التفاصيل';
$labels['permission_publish_calendar_info'] = 'في حالة تحديده، سيتمكن المستخدم من نشر التقويم.';
$labels['permission_share_calendar_info'] = 'في حالة تحديده، سيتمكن المستخدم من مشاركة التقويم مع مستخدمين آخرين.';
$labels['permission_edit_events_info'] = 'في حالة تحديدها، سيتمكن المستخدم من تحرير أحداث التقويم وحذفها.';
$labels['permission_see_details_info'] = 'في حالة تحديدها، ستتم مشاركة جميع تفاصيل الحدث. إذا لم يتم تحديدها، ستتم مشاركة المعلومات المتاحة/المشغولة فقط.';
$labels['share_send_notification'] = 'الإخطار';
$labels['share_send_notification_info'] = 'في حالة التحقق، سيتم إرسال بريد إلكتروني إلى المستخدم لإعلامه بالتقويم الذي تمت مشاركته حديثاً.';
$labels['new_share_not_saved_warning'] = 'لقد أدخلت عنوان بريد إلكتروني لمشاركة التقويم ولكنك لم تنقر على زر مشاركة لإضافته إلى القائمة المشتركة. هل ترغب في متابعة الحفظ دون مشاركة التقويم مع هذا المستخدم؟';
$labels['share'] = 'شارك';
$labels['shared_marker'] = '(مشترك)';
$labels['owner'] = 'المالك';
$labels['publish_calendar'] = 'نشر التقويم';
$labels['create_link'] = 'إنشاء رابط';
$labels['reset'] = 'إعادة تعيين';
$labels['remove'] = 'إزالة';
$labels['publish_full'] = 'نشر جميع معلومات الحدث.';
$labels['publish_busy'] = 'نشر المعلومات المجانية/المشغولة فقط.';
$labels['sync_no_id'] = 'ستصبح خيارات CalDAV متاحة بعد حفظ التقويم.';
$labels['share_no_id'] = 'ستصبح خيارات مشاركة التقويم متاحة بعد حفظ التقويم.';
$labels['publish_no_id'] = 'ستصبح خيارات نشر التقويم متاحة بعد حفظ التقويم.';
$labels['import_no_id'] = 'ستصبح خيارات الاستيراد والتصدير متاحة بعد حفظ التقويم.';
$labels['unable_to_save_calendar_data'] = 'تعذر حفظ بيانات التقويم.';
$labels['unable_to_retrieve_calendar_data'] = 'تعذر استرداد بيانات التقويم. قد لا يكون التقويم متوفراً بعد الآن.';
$labels['unable_add_shared_calendar'] = 'تعذر إضافة التقويم المشترك. قد لا يكون التقويم متوفراً بعد الآن.';
$labels['itip_organizer_message'] = 'لقد تمت دعوتك إلى هذا الحدث ولكنك لست المنظم. لن يتم إرسال التغييرات التي تجريها على هذا الحدث إلى الحاضرين الآخرين.';
$labels['cannot_share_with_yourself'] = 'لا يمكنك مشاركة التقويم الخاص بك مع نفسك.';
$labels['cannot_share_with_calendar_owner'] = 'لا يمكنك مشاركة هذا التقويم مع مالك التقويم ($e)';
$labels['setting_default_notification_type'] = 'الإشعارات الافتراضية للأحداث الجديدة';
$labels['calendar_name_cannot_be_empty'] = 'لا يمكن أن يكون اسم التقويم فارغاً.';

// caldav server
$labels['sync_calendar'] = 'مزامنة التقويم';
$labels['password_too_short'] = 'يجب ألا يقل طول كلمة المرور عن 10 أحرف';
$labels['read_only'] = 'قراءة فقط';
$labels['add_connection'] = 'إضافة اتصال';
$labels['show_details'] = 'عرض التفاصيل';
$labels['hide_details'] = 'إخفاء التفاصيل';
$labels['caldav_connection_password'] = 'كلمة المرور التي حددتها عند إنشاء هذا الاتصال.';
$labels['yes'] = 'نعم';
$labels['no'] = 'لا يوجد';
$labels['confirm_remove_sync'] = 'هل أنت متأكد من رغبتك في إزالة هذا الاتصال بشكل دائم؟';
$labels['remove_sync_error'] = 'حدث خطأ أثناء إزالة الاتصال.';
$labels['create_new_caldav_connection'] = 'إنشاء اتصال CalDav جديد';
$labels['your_caldav_connections'] = 'اتصالات CalDav الخاصة بك';
$labels['created_at'] = 'تم إنشاؤها';
$labels['connected_at'] = 'آخر مزامنة';
$labels['never'] = 'أبداً';
$labels['alternate_url'] = 'عنوان URL البديل';
$labels['caldav_password_info'] = 'لأسباب أمنية لا يتم حفظ كلمة المرور هذه على الخادم. إذا نسيتها، فاحذف هذا الاتصال وأنشئ اتصالاً جديداً.';
$labels['caldav_url_info'] = 'استخدم عنوان URL هذا مع التطبيقات التي تدعم اكتشاف التقويم، على سبيل المثال: تقويم iOS و Mac OS Calendar و Evolution و emClient.';
$labels['caldav_alternate_url_info'] = 'استخدم عنوان URL هذا مع التطبيقات التي لن تعمل مع عنوان url القصير أعلاه، على سبيل المثال: Outlook CalDav Synchronizer، و Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'عنوان URL لخادم CalDAV غير صالح.';
$labels['cannot_resolve_caldav_host'] = 'تعذر تحديد مضيف خادم CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'لا يُسمح باستخدام عناوين CalDAV الخاصة والمحجوزة.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'إضافة تقويم مشترك';
$labels['shared_calendars'] = 'التقويمات المشتركة';
$labels['no_shared_calendars'] = 'لا توجد تقويمات مشتركة يمكنك إضافتها.';
$labels['add_to_my_calendars'] = 'إضافة إلى تقويماتي';
$labels['remove_from_my_calendars'] = 'حذف من تقويماتي';
$labels['unshare'] = 'إلغاء المشاركة';
$labels['unshare_warning'] = 'إذا قمت بإلغاء مشاركة هذا التقويم فسيختفي من هذه القائمة ولن تتمكن من إضافته مرة أخرى.';
$labels['calendar_name'] = 'اسم التقويم';
$labels['calendar_owner'] = 'مالك التقويم';
$labels['shared_date'] = 'مشترك';
$labels['unable_to_share'] = 'لا يمكن مشاركة التقويم مع المستخدمين التاليين لأن حسابات بريدهم الإلكتروني موجودة على خادم بعيد:';

// shared calendar email
$labels['shared_email_subject'] = 'شاركنا معك $u التقويم';
$labels['shared_email_body'] = 'منحك المستخدم $u حق الوصول إلى تقويم يسمى "$c". لإضافة هذا التقويم إلى قائمة التقويمات النشطة، انقر على الرابط أدناه:';
$labels['shared_email_body_cpanel'] = 'منحك المستخدم $u حق الوصول إلى تقويم يسمى "$c". لإضافة هذا التقويم إلى قائمة التقويمات النشطة، قم بتسجيل الدخول إلى Roundcube، وانتقل إلى التقويم، وانقر على أيقونة علامة الجمع على الشريط الجانبي وحدد إضافة تقويم مشترك.';

// adding holiday calendars
$labels['holiday_calendars'] = 'تقويمات العطلات';
$labels['edit_holiday_calendar'] = 'تحرير تقويم العطلات';
$labels['religious_holidays'] = 'الأعياد الدينية';
$labels['country_holidays'] = 'العطلات الريفية';
$labels['add'] = 'إضافة';
$labels['no_more_holidays_allowed'] = 'لا يمكنك إضافة المزيد من تقويمات العطلات.';
$labels['cannot_load_holidays'] = 'لا يمكن تحميل العطلات من خادم Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'إضافة تقويم أعياد الميلاد';
$labels['edit_birthday_calendar'] = 'تحرير تقويم أعياد الميلاد';
$labels['contact_birthdays'] = 'الاتصال بأعياد الميلاد';
$labels['contact_birthdays_info'] = 'يسترجع هذا التقويم معلومات أعياد الميلاد من قائمة جهات الاتصال الخاصة بك ويعرضها كأحداث تقويم.';
$labels['birthday_event_title'] = 'تاريخ الميلاد: $ن';

// adding google calendars
$labels['add_google_calendar'] = 'إضافة تقويم Google';
$labels['edit_google_calendar'] = 'تحرير تقويم Google';
$labels['google_id'] = 'معرّف تقويم Google';
$labels['new_google_calendar'] = 'تقويم Google الجديد';
$labels['specify_google_calendar_id'] = 'الرجاء كتابة معرّف تقويم Google';
$labels['calendar_id_format_error'] = 'يجب أن يتضمن معرّف تقويم Google الحرف @.';
$labels['cannot_load_google_calendar'] = 'لا يمكن تحميل تقويم Google "$c". تأكد من أن معرّف التقويم صحيح وأن التقويم متاح للجمهور.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'إضافة تقويم CalDAV';
$labels['edit_caldav_calendar'] = 'تحرير تقويم CalDAV';
$labels['add_caldav_calendar_information'] = 'للاشتراك في تقويم CalDAV، حدد معلومات خادم CalDAV وانقر فوق البحث عن التقويمات.';
$labels['caldav_url'] = 'عنوان URL الخاص ب CalDAV';
$labels['find_calendars'] = 'البحث عن التقويمات';
$labels['searching_for_calendars'] = 'البحث عن التقاويم، يرجى الانتظار...';
$labels['select_calendars'] = 'حدد التقويمات لإضافتها:';
$labels['read_only_status_yes'] = 'نعم';
$labels['read_only_status_no'] = 'لا يوجد';
$labels['subscribed'] = 'مشترك';

$labels['refresh_calendar'] = 'تحديث التقويم';
$labels['refresh_every_minute'] = 'كل دقيقة';
$labels['refresh_every_5_minutes'] = 'كل 5 دقائق';
$labels['refresh_every_15_minutes'] = 'كل 15 دقيقة';
$labels['refresh_every_30_minutes'] = 'كل 30 دقيقة';
$labels['refresh_every_60_minutes'] = 'كل 60 دقيقة';
$labels['refresh_never'] = 'أبداً';

$labels['cannot_connect_to_caldav_server'] = 'لا يمكن الاتصال بخادم CalDAV "$ n".';
$labels['caldav_client_error_url'] = 'لا يمكن الاتصال بخادم CalDAV. تحقق من عنوان URL الخاص بالخادم.';
$labels['caldav_client_error_credentials'] = 'اسم مستخدم أو كلمة مرور CalDAV غير صحيحة.';
$labels['caldav_client_error_empty'] = 'لم يقم الخادم بإرجاع أي تقويمات يمكن إضافتها. تحقق من عنوان URL الخاص بـ CalDAV.';
$labels['caldav_client_error_server_info'] = 'معلومات خادم CalDAV غير صحيحة.';
$labels['caldav_client_error_add_calendar'] = 'لا يمكن حفظ التقويم "$ n".';
$labels['caldav_client_error_delete_event'] = 'لا يمكن حذف الحدث';
$labels['caldav_client_error_save_event'] = 'يتعذر حفظ حدث CalDAV: ربما تم نقله أو حذفه، أو أن التقويم غير متاح.';
$labels['caldav_client_error_calendar_added'] = 'التقويم "$n" موجود بالفعل في قائمة التقويم الخاصة بك.';

// previewing event
$labels['remove_recurring_event'] = 'حذف الحدث المتكرر';
$labels['remove_one'] = 'حذف هذه الحالة فقط';
$labels['remove_all'] = 'حذف جميع المثيلات';
$labels['edit_event'] = 'تحرير الحدث';
$labels['event_removed'] = 'تم حذف الحدث.';
$labels['go_to_url'] = 'انتقل إلى عنوان URL';
$labels['confirm_remove_event'] = 'هل أنت متأكد من رغبتك في حذف هذا الحدث؟';

// calendar/agenda grid
$labels['no_events'] = 'لا توجد أحداث لعرضها.';

// importing events
$labels['select_calendar'] = 'اختر التقويم';
$labels['select_calendar_for_events'] = 'حدد التقويم الذي ترغب في إضافة الأحداث إليه:';
$labels['add_events'] = 'إضافة أحداث';
$labels['add_events_to_calendar'] = 'إضافة أحداث إلى التقويم';
$labels['events_imported_successfully'] = 'تم استيراد الأحداث بنجاح';
$labels['events_imported_with_errors'] = 'تعذر استيراد بعض الأحداث الأخرى.';
$labels['error_importing_events'] = 'كان هناك خطأ أثناء استيراد الأحداث إلى التقويم';

// editing events
$labels['calendar'] = 'التقويم';
$labels['new_event'] = 'حدث جديد';
$labels['summary'] = 'الملخص';
$labels['recurrence'] = 'التكرار';
$labels['title'] = 'العنوان';
$labels['location'] = 'الموقع';
$labels['description'] = 'الوصف';
$labels['comment'] = 'تعليق';
$labels['url'] = 'عنوان URL';
$labels['name'] = 'الاسم';
$labels['start'] = 'ابدأ';
$labels['end'] = 'النهاية';
$labels['show_timezones'] = 'إظهار المناطق الزمنية';
$labels['reset_timezones'] = 'إعادة تعيين المناطق الزمنية إلى الوضع الافتراضي';
$labels['all_day'] = 'طوال اليوم';
$labels['use_calendar_colors'] = 'استخدام ألوان التقويم';
$labels['color'] = 'اللون';
$labels['background'] = 'الخلفية';
$labels['text'] = 'النص';
$labels['show_me_as'] = 'أرني';
$labels['available'] = 'متوفرة';
$labels['busy'] = 'مشغول';
$labels['visibility'] = 'الرؤية';
$labels['calendar_default'] = 'التقويم الافتراضي';
$labels['public'] = 'عام';
$labels['private'] = 'خاص';
$labels['repeat'] = 'التكرار';
$labels['repeat_every'] = 'كرر كل';
$labels['never'] = 'أبداً';
$labels['daily'] = 'يومياً';
$labels['weekly'] = 'أسبوعياً';
$labels['monthly'] = 'شهرياً';
$labels['yearly'] = 'سنويًا';
$labels['days'] = 'يوم (أيام)';
$labels['weeks'] = 'الأسبوع (الأسابيع)';
$labels['months'] = 'شهر (أشهر)';
$labels['years'] = 'السنة (السنوات)';
$labels['repeat_on'] = 'كرر على';
$labels['repeat_by'] = 'التكرار بواسطة';
$labels['day_of_month'] = 'يوم من الشهر';
$labels['day_of_week'] = 'يوم من الأسبوع';
$labels['not_specified'] = 'غير محدد';
$labels['first'] = 'أولاً';
$labels['second'] = 'الثاني';
$labels['third'] = 'الثالث';
$labels['fourth'] = 'رابعاً';
$labels['fifth'] = 'خامساً';
$labels['second_to_last'] = 'الثاني قبل الأخير';
$labels['last'] = 'الأخير';
$labels['attachment_too_large'] = 'الملف المرفق كبير جداً. الحد الأقصى للحجم المسموح به هو $s.';
$labels['bg_color'] = 'لون الخلفية';
$labels['tx_color'] = 'لون النص';
$labels['add_notification'] = 'إضافة إشعار';
$labels['popup'] = 'نافذة منبثقة';
$labels['no_notifications'] = 'لا يحتوي هذا الحدث على أي تنبيهات.';
$labels['minutes'] = 'الدقائق';
$labels['hours'] = 'ساعات';
$labels['link'] = 'الرابط';
$labels['attach_file_from_web'] = 'إرفاق ملف';
$labels['attachment_url'] = 'عنوان URL للمرفق';
$labels['invalid_url'] = 'عنوان URL هذا غير صالح.';
$labels['xdropbox_name'] = 'دروب بوكس';
$labels['xgoogle_drive_name'] = 'جوجل درايف';
$labels['duplicate'] = 'كرِّر';
$labels['duplicate_suffix'] = '(مكرر)';
$labels['no_notifications'] = 'لا يحتوي هذا الحدث على أي تنبيهات.';
$labels['this_calendar_is_hidden'] = 'هذا التقويم مخفي. لن تظهر الأحداث المحفوظة حتى يصبح التقويم مرئيًا.';
$labels['make_calendar_visible'] = 'اجعل هذا التقويم مرئيًا.';

// recurrence
$labels['recurrence_range'] = 'نطاق التكرار';
$labels['repeat_forever'] = 'كرر للأبد';
$labels['repeat_every_weekday'] = 'كرر كل يوم من أيام الأسبوع';
$labels['last_day'] = 'اليوم الأخير';
$labels['every'] = 'كل';
$labels['weekday'] = 'أيام الأسبوع';
$labels['weekend_day'] = 'يوم عطلة نهاية الأسبوع';
$labels['day_of_week'] = 'يوم من الأسبوع';
$labels['day_of_month'] = 'يوم من الشهر';

// used together as: End after 5 occurrences
$labels['end_after'] = 'النهاية بعد';
$labels['occurrences'] = 'حدوث (حالات)';

// end recurrence on date:
$labels['end_by_date'] = 'النهاية بالتاريخ';
$labels['removed_events_title'] = 'الأحداث التي تمت إزالتها من السلسلة المتكررة';
$labels['restore'] = 'الاستعادة';

// settings page
$labels['setting_view'] = 'عرض التقويم الأولي';
$labels['setting_first_day'] = 'يبدأ الأسبوع في';
$labels['setting_agenda_week_span'] = 'الفترة الزمنية لجدول الأعمال (بالأسابيع)';
$labels['setting_slot_duration'] = 'الفترة الزمنية اليومية (بالدقائق)';
$labels['setting_scroll_time'] = 'انتقل إلى الوقت';
$labels['setting_calendar'] = 'التقويم الافتراضي للأحداث';
$labels['setting_refresh'] = 'تحديث التقويمات';
$labels['setting_week_numbers'] = 'أرقام الأسبوع في التقويم الصغير';
$labels['setting_event_border'] = 'ألوان الفئة كحدود للحدث';
$labels['show_sunrise'] = 'عرض شروق الشمس في عرض الشهر';
$labels['show_sunset'] = 'عرض غروب الشمس في عرض الشهر';
$labels['categories'] = 'الفئات';
$labels['category'] = 'الفئة';
$labels['no_category'] = 'لا توجد فئة';
$labels['add_category'] = 'إضافة فئة';
$labels['setting_alarm_sound'] = 'صوت الإشعار';
$labels['alarm_sound_aurora'] = 'أورورا';
$labels['alarm_sound_bamboo'] = 'خيزران';
$labels['alarm_sound_bells'] = 'الأجراس';
$labels['alarm_sound_chord'] = 'الوتر';
$labels['alarm_sound_circles'] = 'الدوائر';
$labels['alarm_sound_glass'] = 'زجاج';
$labels['alarm_sound_maramba'] = 'مارامبا';
$labels['alarm_sound_metal'] = 'معدن';
$labels['alarm_sound_ripple'] = 'تموج';
$labels['alarm_sound_startrek'] = 'ستار تريك';
$labels['alarm_sound_whistle'] = 'صافرة';
$labels['alarm_sound_yoohoo'] = 'يوهو';
$labels['play_sound'] = 'تشغيل الصوت';

// alarms
$labels['upcoming_events'] = 'الفعاليات القادمة';
$labels['dismiss'] = 'استبعاد';
$labels['dismiss_all'] = 'استبعاد الكل';
$labels['snooze'] = 'غفوة';
$labels['5_minutes'] = '5 دقائق';
$labels['10_minutes'] = '10 دقائق';
$labels['20_minutes'] = '20 دقيقة';
$labels['30_minutes'] = '30 دقيقة';
$labels['1_hour'] = '1 ساعة';
$labels['2_hours'] = '2 ساعة';
$labels['tomorrow'] = 'حتى الغد';
$labels['next_week'] = 'حتى الأسبوع القادم';

// sidebar
$labels['setting_show_xcalendar'] = 'عرض جدول أعمال المعرض التقويم';
$labels['todays_agenda'] = 'جدول أعمال اليوم';
$labels['no_events_today'] = 'لا توجد أحداث مجدولة لهذا اليوم.';
$labels['sidebar_more'] = 'المزيد...';

// event invitation
$labels['itip_title_request'] = 'أنت مدعو إلى حدث.';
$labels['itip_title_modified'] = 'تم تعديل هذا الحدث.';
$labels['itip_title_reply_accepted'] = 'لقد قبلت $u دعوتك';
$labels['itip_title_reply_tentative'] = 'لقد قبلت $$u دعوتك مبدئياً';
$labels['itip_title_reply_declined'] = 'لقد رفضت $u دعوتك';
$labels['itip_title_cancel'] = 'تم إلغاء هذه الفعالية.';

$labels['itip_email_subject_request'] = 'دعوة:';
$labels['itip_email_subject_request_modified'] = 'تم تعديل الحدث:';
$labels['itip_email_subject_reply'] = 'رد:';
$labels['itip_email_subject_cancel'] = 'تم الإلغاء';

$labels['itip_popup_event_added'] = 'تمت إضافة الحدث إلى التقويم.';
$labels['itip_popup_event_updated'] = 'تم تحديث الحدث في التقويم.';
$labels['itip_popup_event_deleted'] = 'تم حذف الحدث من التقويم.';
$labels['itip_popup_response_sent'] = 'تم إرسال الرد إلى الجهة المنظمة.';

$labels['itip_message_invitation_accepted'] = 'لقد قبلت هذه الدعوة.';
$labels['itip_message_invitation_tentative'] = 'لقد قبلت هذه الدعوة مبدئياً.';
$labels['itip_message_invitation_declined'] = 'لقد رفضت هذه الدعوة.';
$labels['itip_message_event_deleted'] = 'تم حذف الحدث من التقويم الخاص بك.';
$labels['itip_message_event_updated'] = 'تم تحديث الحدث في التقويم الخاص بك.';
$labels['itip_message_event_missing'] = 'هذا الحدث غير موجود في أي من التقويمات الخاصة بك.';

$labels['itip_accept'] = 'قبول';
$labels['itip_tentative'] = 'مبدئي';
$labels['itip_decline'] = 'الانخفاض';
$labels['itip_change_reponse'] = 'تغيير الاستجابة.';
$labels['itip_add_to_calendar'] = 'إضافة إلى التقويم';
$labels['itip_reply_to_sender'] = 'الرد على المرسل';
$labels['itip_dont_import'] = 'لا تضيف إلى التقويم';
$labels['itip_update_in_calendar'] = 'تحديث الحدث في التقويم';
$labels['itip_delete_from_calendar'] = 'حذف حدث من التقويم';

$labels['mail_subject_invitation'] = 'دعوة:';
$labels['mail_subject_update'] = 'دعوة محدثة:';
$labels['mail_subject_cancel'] = 'تم إلغاء الحدث:';

// event email notifications
$labels['mail_title'] = 'العنوان:';
$labels['mail_start'] = 'ابدأ:';
$labels['mail_end'] = 'انتهى:';
$labels['mail_description'] = 'الوصف:';
$labels['mail_location'] = 'الموقع:';

// event attendees
$labels['attendees'] = 'الحضور';
$labels['attendee_role'] = 'الدور';
$labels['attendee_role_required'] = 'مطلوب';
$labels['attendee_role_optional'] = 'اختياري';
$labels['attendee_role_chair'] = 'الكرسي';
$labels['attendee_role_none'] = 'غير مشارك';
$labels['organizer'] = 'المنظم';
$labels['attendance'] = 'الحضور';
$labels['your_status'] = 'حالتك';
$labels['attendance_going'] = 'هل ستذهب؟';
$labels['attendee_status_yes'] = 'نعم';
$labels['attendee_status_no'] = 'لا يوجد';
$labels['attendee_status_maybe'] = 'ربما';
$labels['attendee_status_delegated'] = 'المفوض';
$labels['attendee_status_waiting'] = 'الانتظار';
$labels['attendee_status_waiting_for_confirmation'] = 'في انتظار التأكيد';
$labels['attendee_status_info_yes'] = 'سيحضر';
$labels['attendee_status_info_no'] = 'لن يحضر';
$labels['attendee_status_info_maybe'] = 'قد يحضر';
$labels['attendee_status_info_delegated'] = 'الحضور المنتدب';
$labels['attendee_role'] = 'الدور';
$labels['attendee_role_info'] = 'حدد ما إذا كان مطلوبًا من الحاضر حضور الاجتماع أو رئاسته.';
$labels['attendee_notify'] = 'الإخطار';
$labels['attendee_notify_info'] = 'إذا تم التحقق، سيتم إرسال رسالة دعوة عبر البريد الإلكتروني إلى الحاضر.';
$labels['add_attendee'] = 'إضافة حاضر';
$labels['attendee_notify_question'] = 'إرسال إشعار بالبريد الإلكتروني إلى الحاضرين؟';
$labels['attendee_notify_textarea'] = 'إضافة رسالة اختيارية';
$labels['attendee_notify_send'] = 'إرسال';
$labels['attendee_notify_dont'] = 'لا ترسل';
$labels['attendee_notify_cancel'] = 'إلغاء';
$labels['new_attendee_not_saved_warning'] = 'لقد أدخلت عنوان بريد إلكتروني لأحد الحاضرين ولكنك لم تضفه إلى قائمة الحاضرين. هل تريد متابعة الحفظ بدون هذا الحاضر؟';

// import / export
$labels['import_export'] = 'الاستيراد/التصدير';
$labels['export_to_file'] = 'التصدير إلى ملف';
$labels['export_info'] = 'انقر على هذا الزر لتصدير وتنزيل أحداث هذا التقويم بتنسيق iCalendar (ics). إذا كان التقويم يحتوي على الكثير من الأحداث، فقد تستغرق عملية التصدير بضع لحظات.';
$labels['export_no_events'] = 'لا يحتوي هذا التقويم على أي أحداث للتصدير.';
$labels['import_from_file'] = 'الاستيراد من ملف';
$labels['import_info'] = 'انقر فوق هذا الزر لاستيراد الأحداث إلى هذا التقويم من ملف iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'تذكير بالموعد';
$labels['email_notification_subject'] = 'تذكير: $ t عند $ d ($ z)';
$labels['email_notification_cron_error'] = 'تم تعطيل إشعارات البريد الإلكتروني. اتصل بالمسؤول.';
$labels['alarm_before_event_starts'] = 'قبل بدء الفعالية';
$labels['alarm_after_event_starts'] = 'بعد بدء الفعالية';
$labels['alarm_before_event_ends'] = 'قبل انتهاء الفعالية';
$labels['alarm_after_event_ends'] = 'بعد انتهاء الحدث';
$labels['alarm_at_specific_time'] = 'في وقت محدد';

// search
$labels['specify_at_least_one_calendar'] = 'حدد تقويم واحد على الأقل للبحث فيه.';
$labels['specify_text_to_search'] = 'اكتب النص المراد البحث عنه.';
$labels['clear_search_results'] = 'واضح';
$labels['search_outdated_message'] = 'قد تكون نتائج البحث قديمة. فكر في إعادة إجراء البحث مرة أخرى.';
$labels['date_range'] = 'نطاق التاريخ';
$labels['date_range_subtitle'] = 'نطاق التاريخ:';
$labels['search_results'] = 'النتائج التي تم العثور عليها: $ن';
$labels['modify_search_options'] = 'حاول تعديل خيارات البحث.';
$labels['show_shortcuts'] = 'إظهار الاختصارات';
$labels['hide_shortcuts'] = 'إخفاء الاختصارات';
$labels['this_month'] = 'هذا الشهر';
$labels['this_year'] = 'هذا العام';
$labels['past_30_days'] = 'الثلاثين يوماً الماضية';
$labels['next_30_days'] = 'الثلاثين يوماً القادمة';
$labels['past_6_months'] = 'الأشهر الستة الماضية';
$labels['next_6_months'] = 'الأشهر الستة المقبلة';

// calendar grid
$labels['cannot_load_events'] = 'لا يمكن تحميل أحداث للتقويم "$ c".';
