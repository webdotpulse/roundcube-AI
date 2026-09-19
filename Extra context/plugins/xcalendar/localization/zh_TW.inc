<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = '行事曆';
$labels['app_menu_title'] = '行事曆';

// menu
$labels['create_event'] = '建立活動';
$labels['create_event_button'] = '新活動';
$labels['day'] = '日';
$labels['week'] = '週';
$labels['month'] = '月份';
$labels['agenda'] = '議程';

// editing calendars
$labels['calendars'] = '日曆';
$labels['my_calendar'] = '我的行事曆';
$labels['create_calendar'] = '建立行事曆';
$labels['new_calendar'] = '新行事曆';
$labels['edit_calendar'] = '編輯行事曆';
$labels['remove_calendar'] = '刪除行事曆';
$labels['confirm_remove_calendar'] = '您確定要刪除此行事曆嗎？';
$labels['calendar_removed'] = '日曆已刪除。';
$labels['notifications'] = '通知';
$labels['default_event_visibility'] = '預設事件能見度';
$labels['time_zone'] = '日曆時區';
$labels['share_calendar'] = '分享行事曆';
$labels['permissions'] = '權限';
$labels['permission_publish_calendar'] = '發佈行事曆';
$labels['permission_share_calendar'] = '分享行事曆';
$labels['permission_edit_events'] = '編輯活動';
$labels['permission_see_details'] = '詳細資訊';
$labels['permission_publish_calendar_info'] = '如果勾選，使用者將可發佈行事曆。';
$labels['permission_share_calendar_info'] = '如果勾選，使用者將可與其他使用者共用行事曆。';
$labels['permission_edit_events_info'] = '如果勾選，使用者將能夠編輯和刪除行事曆事件。';
$labels['permission_see_details_info'] = '如果勾選，則會分享所有事件詳細資訊。如果未選取，則只會分享可用/繁忙資訊。';
$labels['share_send_notification'] = '通知';
$labels['share_send_notification_info'] = '如果勾選，將發送電子郵件通知使用者新共用的行事曆。';
$labels['new_share_not_saved_warning'] = '您輸入電子郵件地址以分享行事曆，但未按一下分享按鈕以將其新增至分享清單。您要繼續儲存而不與此使用者分享行事曆嗎？';
$labels['share'] = '分享';
$labels['shared_marker'] = '(共用)';
$labels['owner'] = '業主';
$labels['publish_calendar'] = '發佈行事曆';
$labels['create_link'] = '建立連結';
$labels['reset'] = '重設';
$labels['remove'] = '移除';
$labels['publish_full'] = '發佈所有活動資訊。';
$labels['publish_busy'] = '僅發佈空閒/忙碌資訊。';
$labels['sync_no_id'] = 'CalDAV 選項會在您儲存行事曆後變得可用。';
$labels['share_no_id'] = '在您儲存行事曆後，行事曆共用選項就會變得可用。';
$labels['publish_no_id'] = '在您儲存行事曆後，行事曆發佈選項就會變得可用。';
$labels['import_no_id'] = '匯入和匯出選項會在您儲存行事曆後變得可用。';
$labels['unable_to_save_calendar_data'] = '無法儲存行事曆資料。';
$labels['unable_to_retrieve_calendar_data'] = '無法擷取行事曆資料。行事曆可能已不可用。';
$labels['unable_add_shared_calendar'] = '無法新增共用行事曆。行事曆可能已不可用。';
$labels['itip_organizer_message'] = '您受邀參加此活動，但您不是組織者。您對此活動所做的變更不會傳送給其他與會者。';
$labels['cannot_share_with_yourself'] = '您無法與自己共用行事曆。';
$labels['cannot_share_with_calendar_owner'] = '您無法與行事曆擁有者共用此行事曆 ($e)';
$labels['setting_default_notification_type'] = '新事件的預設通知';
$labels['calendar_name_cannot_be_empty'] = '行事曆名稱不能為空。';

// caldav server
$labels['sync_calendar'] = '同步行事曆';
$labels['password_too_short'] = '密碼長度至少要有 10 個字元';
$labels['read_only'] = '僅讀';
$labels['add_connection'] = '新增連接';
$labels['show_details'] = '顯示詳細資訊';
$labels['hide_details'] = '隱藏詳細資訊';
$labels['caldav_connection_password'] = '您在建立此連線時指定的密碼。';
$labels['yes'] = '是';
$labels['no'] = '沒有';
$labels['confirm_remove_sync'] = '您確定要永久移除此連線嗎？';
$labels['remove_sync_error'] = '移除連線時發生錯誤。';
$labels['create_new_caldav_connection'] = '建立新的 CalDav 連線';
$labels['your_caldav_connections'] = '您的 CalDav 連線';
$labels['created_at'] = '創建';
$labels['connected_at'] = '最後同步';
$labels['never'] = '從不';
$labels['alternate_url'] = '替代 URL';
$labels['caldav_password_info'] = '基於安全理由，此密碼不會儲存在伺服器上。如果您忘記了，請刪除此連線，然後再建立一個新連線。';
$labels['caldav_url_info'] = '將此 URL 用於支援行事曆發現的應用程式，例如：iOS 和 Mac OS Calendar、Evolution、emClient。';
$labels['caldav_alternate_url_info'] = '將此 URL 用於無法使用上述簡短 URL 的應用程式，例如：Outlook CalDav Synchronizer、Thunderbird Lightning：Outlook CalDav Synchronizer、Thunderbird Lightning。';
$labels['invalid_caldav_url'] = 'CalDAV 伺服器網址無效。';
$labels['cannot_resolve_caldav_host'] = '無法解析 CalDAV 伺服器主機。';
$labels['private_caldav_addresses_not_allowed'] = '不允許使用私有及保留的 CalDAV 位址。';

// adding a shared calendar
$labels['add_shared_calendar'] = '新增共用行事曆';
$labels['shared_calendars'] = '共用行事曆';
$labels['no_shared_calendars'] = '沒有可以新增的共用行事曆。';
$labels['add_to_my_calendars'] = '加入我的行事曆';
$labels['remove_from_my_calendars'] = '從我的行事曆中移除';
$labels['unshare'] = '取消分享';
$labels['unshare_warning'] = '如果您取消共用此行事曆，它將從此清單中消失，您將無法再次新增它。';
$labels['calendar_name'] = '日曆名稱';
$labels['calendar_owner'] = '日曆擁有者';
$labels['shared_date'] = '共享';
$labels['unable_to_share'] = '由於下列使用者的電子郵件帳號位於遠端伺服器，因此無法與他們共用行事曆：';

// shared calendar email
$labels['shared_email_subject'] = '$u 已與您共用行事曆';
$labels['shared_email_body'] = '使用者 $u 已允許您存取名為 "$c" 的行事曆。若要將此行事曆新增至您的活動行事曆清單，請點選以下連結：';
$labels['shared_email_body_cpanel'] = '用戶$u已允許您訪問一個名為"$c "的日曆。若要將此行事曆新增至您的活動行事曆清單，請登入 Roundcube，前往行事曆，按一下側邊欄上的加號圖示，然後選擇新增共用行事曆。';

// adding holiday calendars
$labels['holiday_calendars'] = '假日日曆';
$labels['edit_holiday_calendar'] = '編輯假日行事曆';
$labels['religious_holidays'] = '宗教節日';
$labels['country_holidays'] = '國家假期';
$labels['add'] = '新增';
$labels['no_more_holidays_allowed'] = '您無法再新增任何假日行事曆。';
$labels['cannot_load_holidays'] = '無法從 Google 伺服器載入假期。';

// adding birthday calendar
$labels['add_birthday_calendar'] = '新增生日行事曆';
$labels['edit_birthday_calendar'] = '編輯生日行事曆';
$labels['contact_birthdays'] = '聯絡生日';
$labels['contact_birthdays_info'] = '此行事曆會從連絡人清單中擷取生日資訊，並顯示為行事曆事件。';
$labels['birthday_event_title'] = '生日： $n';

// adding google calendars
$labels['add_google_calendar'] = '新增 Google 行事曆';
$labels['edit_google_calendar'] = '編輯 Google 行事曆';
$labels['google_id'] = 'Google 行事曆 ID';
$labels['new_google_calendar'] = '新的 Google 行事曆';
$labels['specify_google_calendar_id'] = '請輸入 Google 行事曆 ID';
$labels['calendar_id_format_error'] = 'Google 行事曆 ID 應該包含 @ 字元。';
$labels['cannot_load_google_calendar'] = '無法載入 Google 行事曆「$c」。請確定行事曆 ID 正確，且行事曆已公開。';

// adding caldav calendars
$labels['add_caldav_calendar'] = '新增 CalDAV 行事曆';
$labels['edit_caldav_calendar'] = '編輯 CalDAV 行事曆';
$labels['add_caldav_calendar_information'] = '若要訂閱 CalDAV 行事曆，請指定 CalDAV 伺服器資訊，然後按一下尋找行事曆。';
$labels['caldav_url'] = 'CalDAV URL';
$labels['find_calendars'] = '尋找日曆';
$labels['searching_for_calendars'] = '正在搜尋日曆，請稍候...';
$labels['select_calendars'] = '選取要新增的行事曆：';
$labels['read_only_status_yes'] = '是';
$labels['read_only_status_no'] = '沒有';
$labels['subscribed'] = '已訂閱';

$labels['refresh_calendar'] = '刷新行事曆';
$labels['refresh_every_minute'] = '每分鐘';
$labels['refresh_every_5_minutes'] = '每 5 分鐘';
$labels['refresh_every_15_minutes'] = '每 15 分鐘';
$labels['refresh_every_30_minutes'] = '每 30 分鐘';
$labels['refresh_every_60_minutes'] = '每 60 分鐘';
$labels['refresh_never'] = '從不';

$labels['cannot_connect_to_caldav_server'] = '無法連線至 CalDAV 伺服器 "$n"。';
$labels['caldav_client_error_url'] = '無法連線至 CalDAV 伺服器。檢查伺服器 URL。';
$labels['caldav_client_error_credentials'] = 'CalDAV 使用者名稱或密碼不正確。';
$labels['caldav_client_error_empty'] = '伺服器沒有傳回任何可以新增的行事曆。檢查 CalDAV URL。';
$labels['caldav_client_error_server_info'] = 'CalDAV 伺服器資訊不正確。';
$labels['caldav_client_error_add_calendar'] = '無法儲存行事曆 "$n"。';
$labels['caldav_client_error_delete_event'] = '無法刪除事件';
$labels['caldav_client_error_save_event'] = '無法儲存 CalDAV 活動：該活動可能已被移動、刪除，或日曆無法存取。';
$labels['caldav_client_error_calendar_added'] = '行事曆「$n」已在您的行事曆清單中。';

// previewing event
$labels['remove_recurring_event'] = '刪除經常性事件';
$labels['remove_one'] = '僅刪除此實例';
$labels['remove_all'] = '刪除所有實例';
$labels['edit_event'] = '編輯活動';
$labels['event_removed'] = '活動已刪除。';
$labels['go_to_url'] = '前往 URL';
$labels['confirm_remove_event'] = '您確定要刪除此事件嗎？';

// calendar/agenda grid
$labels['no_events'] = '沒有事件要顯示。';

// importing events
$labels['select_calendar'] = '選擇行事曆';
$labels['select_calendar_for_events'] = '選取您要新增事件的行事曆：';
$labels['add_events'] = '新增活動';
$labels['add_events_to_calendar'] = '將活動新增至行事曆';
$labels['events_imported_successfully'] = '事件已成功匯入';
$labels['events_imported_with_errors'] = '某些其他事件無法匯入。';
$labels['error_importing_events'] = '將事件匯入行事曆時發生錯誤';

// editing events
$labels['calendar'] = '行事曆';
$labels['new_event'] = '新活動';
$labels['summary'] = '摘要';
$labels['recurrence'] = '復發';
$labels['title'] = '標題';
$labels['location'] = '地點';
$labels['description'] = '說明';
$labels['comment'] = '評論';
$labels['url'] = '網址';
$labels['name'] = '名稱';
$labels['start'] = '開始';
$labels['end'] = '結束';
$labels['show_timezones'] = '顯示時區';
$labels['reset_timezones'] = '將時區重設為預設值';
$labels['all_day'] = '全天';
$labels['use_calendar_colors'] = '使用行事曆顏色';
$labels['color'] = '顏色';
$labels['background'] = '背景資料';
$labels['text'] = '正文';
$labels['show_me_as'] = '顯示我為';
$labels['available'] = '可用';
$labels['busy'] = '繁忙';
$labels['visibility'] = '能見度';
$labels['calendar_default'] = '日曆預設值';
$labels['public'] = '公眾';
$labels['private'] = '私人';
$labels['repeat'] = '重複';
$labels['repeat_every'] = '重複每';
$labels['never'] = '從不';
$labels['daily'] = '每日';
$labels['weekly'] = '每週';
$labels['monthly'] = '每月';
$labels['yearly'] = '每年';
$labels['days'] = '天';
$labels['weeks'] = '週';
$labels['months'] = '月';
$labels['years'] = '年份';
$labels['repeat_on'] = '重複';
$labels['repeat_by'] = '重複';
$labels['day_of_month'] = '月日';
$labels['day_of_week'] = '星期';
$labels['not_specified'] = '未指定';
$labels['first'] = '第一';
$labels['second'] = '第二';
$labels['third'] = '第三次';
$labels['fourth'] = '第四頁';
$labels['fifth'] = '第五屆';
$labels['second_to_last'] = '倒數第二';
$labels['last'] = '最後一次';
$labels['attachment_too_large'] = '附件檔案太大。允許的最大大小為 $s。';
$labels['bg_color'] = '背景顏色';
$labels['tx_color'] = '文字顏色';
$labels['add_notification'] = '新增通知';
$labels['popup'] = '快顯';
$labels['no_notifications'] = '此事件沒有任何通知。';
$labels['minutes'] = '分鐘';
$labels['hours'] = '時數';
$labels['link'] = '連結';
$labels['attach_file_from_web'] = '附加檔案';
$labels['attachment_url'] = '附件 URL';
$labels['invalid_url'] = '此 URL 無效。';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = '複製';
$labels['duplicate_suffix'] = '(副本)';
$labels['no_notifications'] = '此事件沒有任何通知。';
$labels['this_calendar_is_hidden'] = '此行事曆目前處於隱藏狀態。已儲存的活動將不會顯示，直到該行事曆設為可見為止。';
$labels['make_calendar_visible'] = '將此日曆設為可見。';

// recurrence
$labels['recurrence_range'] = '重現範圍';
$labels['repeat_forever'] = '永遠重複';
$labels['repeat_every_weekday'] = '每個平日重複';
$labels['last_day'] = '最後一天';
$labels['every'] = '每個';
$labels['weekday'] = '平日';
$labels['weekend_day'] = '週末日';
$labels['day_of_week'] = '星期';
$labels['day_of_month'] = '月日';

// used together as: End after 5 occurrences
$labels['end_after'] = '結束後';
$labels['occurrences'] = '發生';

// end recurrence on date:
$labels['end_by_date'] = '依日期結束';
$labels['removed_events_title'] = '從經常性系列中移除的事件';
$labels['restore'] = '還原';

// settings page
$labels['setting_view'] = '初始行事曆檢視';
$labels['setting_first_day'] = '週始於';
$labels['setting_agenda_week_span'] = '議程時間跨（週）';
$labels['setting_slot_duration'] = '日間時段（分鐘）';
$labels['setting_scroll_time'] = '捲動到時間';
$labels['setting_calendar'] = '預設活動行事曆';
$labels['setting_refresh'] = '刷新日曆';
$labels['setting_week_numbers'] = '小日曆中的週數';
$labels['setting_event_border'] = '分類顏色作為事件邊框';
$labels['show_sunrise'] = '在月份檢視中顯示日出';
$labels['show_sunset'] = '在月份檢視中顯示日落';
$labels['categories'] = '類別';
$labels['category'] = '類別';
$labels['no_category'] = '無類別';
$labels['add_category'] = '新增類別';
$labels['setting_alarm_sound'] = '通知聲音';
$labels['alarm_sound_aurora'] = '極光';
$labels['alarm_sound_bamboo'] = '竹子';
$labels['alarm_sound_bells'] = '鈴聲';
$labels['alarm_sound_chord'] = '和弦';
$labels['alarm_sound_circles'] = '圓圈';
$labels['alarm_sound_glass'] = '玻璃';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = '金屬';
$labels['alarm_sound_ripple'] = '漣漪';
$labels['alarm_sound_startrek'] = '星際迷航';
$labels['alarm_sound_whistle'] = '口哨';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = '播放聲音';

// alarms
$labels['upcoming_events'] = '即將舉行的活動';
$labels['dismiss'] = '解散';
$labels['dismiss_all'] = '全部解散';
$labels['snooze'] = '打盹';
$labels['5_minutes'] = '5 分鐘';
$labels['10_minutes'] = '10 分鐘';
$labels['20_minutes'] = '20 分鐘';
$labels['30_minutes'] = '30 分鐘';
$labels['1_hour'] = '1 小時';
$labels['2_hours'] = '2 小時';
$labels['tomorrow'] = '直到明天';
$labels['next_week'] = '直到下週';

// sidebar
$labels['setting_show_xcalendar'] = '顯示日曆議程';
$labels['todays_agenda'] = '今日議程';
$labels['no_events_today'] = '今天沒有安排任何活動。';
$labels['sidebar_more'] = '更多...';

// event invitation
$labels['itip_title_request'] = '邀請您參加活動。';
$labels['itip_title_modified'] = '此事件已修改。';
$labels['itip_title_reply_accepted'] = '$u 已接受您的邀請';
$labels['itip_title_reply_tentative'] = '$u 已初步接受您的邀請';
$labels['itip_title_reply_declined'] = '$u 已拒絕您的邀請';
$labels['itip_title_cancel'] = '此活動已取消。';

$labels['itip_email_subject_request'] = '邀請函：';
$labels['itip_email_subject_request_modified'] = '活動已修改：';
$labels['itip_email_subject_reply'] = '關於';
$labels['itip_email_subject_cancel'] = '已取消：';

$labels['itip_popup_event_added'] = '活動已加入行事曆。';
$labels['itip_popup_event_updated'] = '活動已在行事曆中更新。';
$labels['itip_popup_event_deleted'] = '從行事曆中刪除事件。';
$labels['itip_popup_response_sent'] = '回覆寄給主辦單位。';

$labels['itip_message_invitation_accepted'] = '您已接受此邀請。';
$labels['itip_message_invitation_tentative'] = '您已初步接受此邀請。';
$labels['itip_message_invitation_declined'] = '您拒絕了這次邀請。';
$labels['itip_message_event_deleted'] = '事件已從您的行事曆中刪除。';
$labels['itip_message_event_updated'] = '您行事曆中的事件已更新。';
$labels['itip_message_event_missing'] = '您的任何行事曆中都沒有此活動。';

$labels['itip_accept'] = '接受';
$labels['itip_tentative'] = '暫定';
$labels['itip_decline'] = '下降';
$labels['itip_change_reponse'] = '變更回應。';
$labels['itip_add_to_calendar'] = '新增至行事曆';
$labels['itip_reply_to_sender'] = '回覆寄件者';
$labels['itip_dont_import'] = '不要新增至行事曆';
$labels['itip_update_in_calendar'] = '更新行事曆中的事件';
$labels['itip_delete_from_calendar'] = '從行事曆中刪除事件';

$labels['mail_subject_invitation'] = '邀請函：';
$labels['mail_subject_update'] = '已更新的邀請函：';
$labels['mail_subject_cancel'] = '活動取消：';

// event email notifications
$labels['mail_title'] = '標題：';
$labels['mail_start'] = '開始';
$labels['mail_end'] = '結束：';
$labels['mail_description'] = '說明：';
$labels['mail_location'] = '地點：';

// event attendees
$labels['attendees'] = '與會者';
$labels['attendee_role'] = '角色';
$labels['attendee_role_required'] = '必須';
$labels['attendee_role_optional'] = '選購';
$labels['attendee_role_chair'] = '椅子';
$labels['attendee_role_none'] = '非參與者';
$labels['organizer'] = '組織者';
$labels['attendance'] = '出席率';
$labels['your_status'] = '您的狀態';
$labels['attendance_going'] = '去嗎？';
$labels['attendee_status_yes'] = '是';
$labels['attendee_status_no'] = '沒有';
$labels['attendee_status_maybe'] = '也許';
$labels['attendee_status_delegated'] = '委託';
$labels['attendee_status_waiting'] = '等待';
$labels['attendee_status_waiting_for_confirmation'] = '等待確認';
$labels['attendee_status_info_yes'] = '將出席';
$labels['attendee_status_info_no'] = '不會出席';
$labels['attendee_status_info_maybe'] = '可能出席';
$labels['attendee_status_info_delegated'] = '委託出席';
$labels['attendee_role'] = '角色';
$labels['attendee_role_info'] = '指定出席者是否必須出席或主持會議。';
$labels['attendee_notify'] = '通知';
$labels['attendee_notify_info'] = '如果勾選，則會向與會者發送邀請電子郵件。';
$labels['add_attendee'] = '新增出席者';
$labels['attendee_notify_question'] = '傳送通知電子郵件給出席者？';
$labels['attendee_notify_textarea'] = '新增可選訊息';
$labels['attendee_notify_send'] = '發送';
$labels['attendee_notify_dont'] = '不要發送';
$labels['attendee_notify_cancel'] = '取消';
$labels['new_attendee_not_saved_warning'] = '您已輸入與會者的電子郵件地址，但尚未將其加入與會者名單。是否要跳過此與會者並繼續儲存？';

// import / export
$labels['import_export'] = '進口/出口';
$labels['export_to_file'] = '匯出至檔案';
$labels['export_info'] = '按一下此按鈕，將此行事曆的事件匯出並下載為 iCalendar (ics) 格式。如果行事曆包含大量事件，匯出過程可能需要花費一些時間。';
$labels['export_no_events'] = '此行事曆沒有任何要匯出的事件。';
$labels['import_from_file'] = '從檔案匯入';
$labels['import_info'] = '按一下此按鈕，可從 iCalendar (ics) 檔案匯入事件到此行事曆。';

// email notifications
$labels['appointment_reminder'] = '預約提醒';
$labels['email_notification_subject'] = '提醒： $t 在 $d ($z)';
$labels['email_notification_cron_error'] = '電子郵件通知已停用。請聯絡管理員。';
$labels['alarm_before_event_starts'] = '活動開始前';
$labels['alarm_after_event_starts'] = '活動開始後';
$labels['alarm_before_event_ends'] = '活動結束前';
$labels['alarm_after_event_ends'] = '活動結束後';
$labels['alarm_at_specific_time'] = '在特定時間';

// search
$labels['specify_at_least_one_calendar'] = '指定至少一個要搜尋的行事曆。';
$labels['specify_text_to_search'] = '輸入要搜尋的文字。';
$labels['clear_search_results'] = '清除';
$labels['search_outdated_message'] = '搜尋結果可能已經過期。請考慮重新執行搜尋。';
$labels['date_range'] = '日期範圍';
$labels['date_range_subtitle'] = '日期範圍：';
$labels['search_results'] = '找到的結果: $n';
$labels['modify_search_options'] = '嘗試修改搜尋選項。';
$labels['show_shortcuts'] = '顯示捷徑';
$labels['hide_shortcuts'] = '隱藏捷徑';
$labels['this_month'] = '本月';
$labels['this_year'] = '今年';
$labels['past_30_days'] = '過去 30 天';
$labels['next_30_days'] = '未來 30 天';
$labels['past_6_months'] = '過去 6 個月';
$labels['next_6_months'] = '未來 6 個月';

// calendar grid
$labels['cannot_load_events'] = '無法載入行事曆 "$c" 的事件。';
