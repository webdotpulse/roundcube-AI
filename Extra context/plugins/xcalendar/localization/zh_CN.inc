<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = '日历';
$labels['app_menu_title'] = '日历';

// menu
$labels['create_event'] = '创建活动';
$labels['create_event_button'] = '新活动';
$labels['day'] = '日';
$labels['week'] = '周数';
$labels['month'] = '月份';
$labels['agenda'] = '议程';

// editing calendars
$labels['calendars'] = '日历';
$labels['my_calendar'] = '我的日历';
$labels['create_calendar'] = '创建日历';
$labels['new_calendar'] = '新日历';
$labels['edit_calendar'] = '编辑日历';
$labels['remove_calendar'] = '删除日历';
$labels['confirm_remove_calendar'] = '您确定要删除此日历吗？';
$labels['calendar_removed'] = '日历已删除。';
$labels['notifications'] = '通知';
$labels['default_event_visibility'] = '默认事件可见性';
$labels['time_zone'] = '日历时区';
$labels['share_calendar'] = '共享日历';
$labels['permissions'] = '权限';
$labels['permission_publish_calendar'] = '发布日历';
$labels['permission_share_calendar'] = '共享日历';
$labels['permission_edit_events'] = '编辑事件';
$labels['permission_see_details'] = '查看详情';
$labels['permission_publish_calendar_info'] = '如果选中，用户就可以发布日历。';
$labels['permission_share_calendar_info'] = '如果选中，用户就可以与其他用户共享日历。';
$labels['permission_edit_events_info'] = '如果选中，用户就可以编辑和删除日历事件。';
$labels['permission_see_details_info'] = '如果选中，将共享所有事件详细信息。如果不选中，则只共享可用/繁忙信息。';
$labels['share_send_notification'] = '通知';
$labels['share_send_notification_info'] = '如果选中，将向用户发送一封电子邮件，告知其新共享的日历。';
$labels['new_share_not_saved_warning'] = '您输入了要共享日历的电子邮件地址，但没有单击共享按钮将其添加到共享列表中。您想在不与该用户共享日历的情况下继续保存吗？';
$labels['share'] = '分享';
$labels['shared_marker'] = '（共享）';
$labels['owner'] = '业主';
$labels['publish_calendar'] = '发布日历';
$labels['create_link'] = '创建链接';
$labels['reset'] = '重置';
$labels['remove'] = '移除';
$labels['publish_full'] = '发布所有活动信息。';
$labels['publish_busy'] = '只发布空闲/繁忙信息。';
$labels['sync_no_id'] = '保存日历后，即可使用 CalDAV 选项。';
$labels['share_no_id'] = '日历共享选项将在保存日历后可用。';
$labels['publish_no_id'] = '日历发布选项将在保存日历后可用。';
$labels['import_no_id'] = '保存日历后，即可使用导入和导出选项。';
$labels['unable_to_save_calendar_data'] = '无法保存日历数据。';
$labels['unable_to_retrieve_calendar_data'] = '无法检索日历数据。日历可能已不可用。';
$labels['unable_add_shared_calendar'] = '无法添加共享日历。日历可能已不可用。';
$labels['itip_organizer_message'] = '您受邀参加此活动，但您不是组织者。您对该活动所做的更改不会发送给其他与会者。';
$labels['cannot_share_with_yourself'] = '您不能与自己共享日历。';
$labels['cannot_share_with_calendar_owner'] = '您不能与日历所有者共享此日历 ($e)';
$labels['setting_default_notification_type'] = '新事件的默认通知';
$labels['calendar_name_cannot_be_empty'] = '日历名称不能为空。';

// caldav server
$labels['sync_calendar'] = '同步日历';
$labels['password_too_short'] = '密码长度至少应为 10 个字符';
$labels['read_only'] = '只读';
$labels['add_connection'] = '添加连接';
$labels['show_details'] = '显示详细信息';
$labels['hide_details'] = '隐藏详细信息';
$labels['caldav_connection_password'] = '您在创建此连接时指定的密码。';
$labels['yes'] = '是';
$labels['no'] = '没有';
$labels['confirm_remove_sync'] = '您确定要永久删除此连接吗？';
$labels['remove_sync_error'] = '删除连接时出现错误。';
$labels['create_new_caldav_connection'] = '创建新的 CalDav 连接';
$labels['your_caldav_connections'] = '您的 CalDav 连接';
$labels['created_at'] = '创建';
$labels['connected_at'] = '最后一次同步';
$labels['never'] = '从不';
$labels['alternate_url'] = '备用网址';
$labels['caldav_password_info'] = '出于安全考虑，该密码不会保存在服务器上。如果您忘记了密码，请删除此连接并创建一个新连接。';
$labels['caldav_url_info'] = '将此 URL 与支持日历发现的应用程序一起使用，例如：iOS 和 Mac OS Calendar、Evolution、emClient。';
$labels['caldav_alternate_url_info'] = '将此 URL 用于无法使用上述简短 URL 的应用程序，例如：Outlook CalDav Synchronizer、Thunderbird Lightning：Outlook CalDav Synchronizer、Thunderbird Lightning。';
$labels['invalid_caldav_url'] = 'CalDAV 服务器网址无效。';
$labels['cannot_resolve_caldav_host'] = '无法解析 CalDAV 服务器主机。';
$labels['private_caldav_addresses_not_allowed'] = '不允许使用私有和保留的 CalDAV 地址。';

// adding a shared calendar
$labels['add_shared_calendar'] = '添加共享日历';
$labels['shared_calendars'] = '共享日历';
$labels['no_shared_calendars'] = '没有可以添加的共享日历。';
$labels['add_to_my_calendars'] = '添加到我的日历中';
$labels['remove_from_my_calendars'] = '从我的日历中移除';
$labels['unshare'] = '取消共享';
$labels['unshare_warning'] = '如果您取消共享此日历，它将从此列表中消失，您将无法再次添加它。';
$labels['calendar_name'] = '日历名称';
$labels['calendar_owner'] = '日历所有者';
$labels['shared_date'] = '共享';
$labels['unable_to_share'] = '无法与下列用户共享日历，因为他们的电子邮件帐户位于远程服务器上：';

// shared calendar email
$labels['shared_email_subject'] = '$u 已与您共享日历';
$labels['shared_email_body'] = '用户 $u 允许您访问名为"$c "的日历。要将该日历添加到您的活动日历列表中，请单击下面的链接：';
$labels['shared_email_body_cpanel'] = '用户$u允许您访问名为"$c "的日历。要将该日历添加到您的活动日历列表中，请登录到 Roundcube，转到日历，点击侧边栏上的加号图标，然后选择添加共享日历。';

// adding holiday calendars
$labels['holiday_calendars'] = '节日日历';
$labels['edit_holiday_calendar'] = '编辑假日日历';
$labels['religious_holidays'] = '宗教节日';
$labels['country_holidays'] = '乡村假期';
$labels['add'] = '添加';
$labels['no_more_holidays_allowed'] = '您不能再添加任何假日日历。';
$labels['cannot_load_holidays'] = '无法从 Google 服务器加载假期。';

// adding birthday calendar
$labels['add_birthday_calendar'] = '添加生日日历';
$labels['edit_birthday_calendar'] = '编辑生日日历';
$labels['contact_birthdays'] = '联系生日';
$labels['contact_birthdays_info'] = '该日历可从联系人列表中检索生日信息，并将其显示为日历事件。';
$labels['birthday_event_title'] = '生日： $n';

// adding google calendars
$labels['add_google_calendar'] = '添加 Google 日历';
$labels['edit_google_calendar'] = '编辑 Google 日历';
$labels['google_id'] = '谷歌日历 ID';
$labels['new_google_calendar'] = '新谷歌日历';
$labels['specify_google_calendar_id'] = '请输入 Google 日历 ID';
$labels['calendar_id_format_error'] = 'Google 日历 ID 应包括 @ 字符。';
$labels['cannot_load_google_calendar'] = '无法加载 Google 日历"$c"。请确保日历 ID 正确，且日历已向公众开放。';

// adding caldav calendars
$labels['add_caldav_calendar'] = '添加 CalDAV 日历';
$labels['edit_caldav_calendar'] = '编辑 CalDAV 日历';
$labels['add_caldav_calendar_information'] = '要订阅 CalDAV 日历，请指定 CalDAV 服务器信息并单击查找日历。';
$labels['caldav_url'] = 'CalDAV URL';
$labels['find_calendars'] = '查找日历';
$labels['searching_for_calendars'] = '搜索日历，请稍候...';
$labels['select_calendars'] = '选择要添加的日历：';
$labels['read_only_status_yes'] = '是';
$labels['read_only_status_no'] = '没有';
$labels['subscribed'] = '已订阅';

$labels['refresh_calendar'] = '刷新日历';
$labels['refresh_every_minute'] = '每一分钟';
$labels['refresh_every_5_minutes'] = '每 5 分钟';
$labels['refresh_every_15_minutes'] = '每 15 分钟';
$labels['refresh_every_30_minutes'] = '每 30 分钟';
$labels['refresh_every_60_minutes'] = '每 60 分钟';
$labels['refresh_never'] = '从不';

$labels['cannot_connect_to_caldav_server'] = '无法连接 CalDAV 服务器"$n"。';
$labels['caldav_client_error_url'] = '无法连接 CalDAV 服务器。检查服务器 URL。';
$labels['caldav_client_error_credentials'] = 'CalDAV 用户名或密码不正确。';
$labels['caldav_client_error_empty'] = '服务器未返回任何可添加的日历。检查 CalDAV URL。';
$labels['caldav_client_error_server_info'] = 'CalDAV 服务器信息不正确。';
$labels['caldav_client_error_add_calendar'] = '无法保存日历"$n"。';
$labels['caldav_client_error_delete_event'] = '无法删除事件';
$labels['caldav_client_error_save_event'] = '无法保存 CalDAV 事件：该事件可能已被移动、删除，或者日历不可用。';
$labels['caldav_client_error_calendar_added'] = '日历"$n "已在您的日历列表中。';

// previewing event
$labels['remove_recurring_event'] = '删除重复事件';
$labels['remove_one'] = '仅删除该实例';
$labels['remove_all'] = '删除所有实例';
$labels['edit_event'] = '编辑活动';
$labels['event_removed'] = '活动已删除。';
$labels['go_to_url'] = '转到 URL';
$labels['confirm_remove_event'] = '您确定要删除此事件吗？';

// calendar/agenda grid
$labels['no_events'] = '没有要显示的事件。';

// importing events
$labels['select_calendar'] = '选择日历';
$labels['select_calendar_for_events'] = '选择要添加事件的日历：';
$labels['add_events'] = '添加事件';
$labels['add_events_to_calendar'] = '将活动添加到日历';
$labels['events_imported_successfully'] = '成功导入事件';
$labels['events_imported_with_errors'] = '某些其他事件无法导入。';
$labels['error_importing_events'] = '将事件导入日历时出现错误';

// editing events
$labels['calendar'] = '日历';
$labels['new_event'] = '新活动';
$labels['summary'] = '摘要';
$labels['recurrence'] = '复发';
$labels['title'] = '标题';
$labels['location'] = '地点';
$labels['description'] = '说明';
$labels['comment'] = '评论';
$labels['url'] = '网址';
$labels['name'] = '名称';
$labels['start'] = '开始';
$labels['end'] = '结束';
$labels['show_timezones'] = '显示时区';
$labels['reset_timezones'] = '将时区重置为默认时区';
$labels['all_day'] = '全天';
$labels['use_calendar_colors'] = '使用日历颜色';
$labels['color'] = '颜色';
$labels['background'] = '背景介绍';
$labels['text'] = '文本';
$labels['show_me_as'] = '让我看看';
$labels['available'] = '可用';
$labels['busy'] = '繁忙';
$labels['visibility'] = '可见性';
$labels['calendar_default'] = '日历默认值';
$labels['public'] = '公众';
$labels['private'] = '私人';
$labels['repeat'] = '重复';
$labels['repeat_every'] = '每';
$labels['never'] = '从不';
$labels['daily'] = '每日';
$labels['weekly'] = '每周';
$labels['monthly'] = '每月';
$labels['yearly'] = '每年';
$labels['days'] = '天数';
$labels['weeks'] = '周数';
$labels['months'] = '个月';
$labels['years'] = '年份';
$labels['repeat_on'] = '重复';
$labels['repeat_by'] = '重复';
$labels['day_of_month'] = '月日';
$labels['day_of_week'] = '星期';
$labels['not_specified'] = '未说明';
$labels['first'] = '第一';
$labels['second'] = '第二';
$labels['third'] = '第三届';
$labels['fourth'] = '第四届';
$labels['fifth'] = '第五届';
$labels['second_to_last'] = '倒数第二';
$labels['last'] = '最后';
$labels['attachment_too_large'] = '附件文件太大。允许的最大大小为 $s。';
$labels['bg_color'] = '背景颜色';
$labels['tx_color'] = '文字颜色';
$labels['add_notification'] = '添加通知';
$labels['popup'] = '弹出窗口';
$labels['no_notifications'] = '该事件没有任何通知。';
$labels['minutes'] = '分钟';
$labels['hours'] = '时数';
$labels['link'] = '链接';
$labels['attach_file_from_web'] = '附加文件';
$labels['attachment_url'] = '附件 URL';
$labels['invalid_url'] = '此 URL 无效。';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = '谷歌硬盘';
$labels['duplicate'] = '复制';
$labels['duplicate_suffix'] = '(副本)';
$labels['no_notifications'] = '该事件没有任何通知。';
$labels['this_calendar_is_hidden'] = '此日历处于隐藏状态。已保存的事件在日历显示出来之前不会显示。';
$labels['make_calendar_visible'] = '将此日历设为可见。';

// recurrence
$labels['recurrence_range'] = '复发范围';
$labels['repeat_forever'] = '永远重复';
$labels['repeat_every_weekday'] = '每个工作日重复';
$labels['last_day'] = '最后一天';
$labels['every'] = '每一个';
$labels['weekday'] = '平日';
$labels['weekend_day'] = '周末';
$labels['day_of_week'] = '星期';
$labels['day_of_month'] = '月日';

// used together as: End after 5 occurrences
$labels['end_after'] = '之后结束';
$labels['occurrences'] = '发生次数';

// end recurrence on date:
$labels['end_by_date'] = '结束日期';
$labels['removed_events_title'] = '从循环系列中删除的事件';
$labels['restore'] = '恢复';

// settings page
$labels['setting_view'] = '初始日历视图';
$labels['setting_first_day'] = '本周开始于';
$labels['setting_agenda_week_span'] = '议程时间跨度（周）';
$labels['setting_slot_duration'] = '日时段（分钟）';
$labels['setting_scroll_time'] = '滚动到时间';
$labels['setting_calendar'] = '活动的默认日历';
$labels['setting_refresh'] = '刷新日历';
$labels['setting_week_numbers'] = '小日历中的周数';
$labels['setting_event_border'] = '将类别颜色作为活动边框';
$labels['show_sunrise'] = '在月份视图中显示日出';
$labels['show_sunset'] = '在月份视图中显示日落';
$labels['categories'] = '类别';
$labels['category'] = '类别';
$labels['no_category'] = '无类别';
$labels['add_category'] = '添加类别';
$labels['setting_alarm_sound'] = '通知声音';
$labels['alarm_sound_aurora'] = '极光';
$labels['alarm_sound_bamboo'] = '竹子';
$labels['alarm_sound_bells'] = '钟声';
$labels['alarm_sound_chord'] = '和弦';
$labels['alarm_sound_circles'] = '圆圈';
$labels['alarm_sound_glass'] = '玻璃';
$labels['alarm_sound_maramba'] = '马兰巴';
$labels['alarm_sound_metal'] = '金属';
$labels['alarm_sound_ripple'] = '波纹';
$labels['alarm_sound_startrek'] = '星际迷航';
$labels['alarm_sound_whistle'] = '哨子';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = '播放声音';

// alarms
$labels['upcoming_events'] = '即将举行的活动';
$labels['dismiss'] = '解散';
$labels['dismiss_all'] = '全部驳回';
$labels['snooze'] = '打盹';
$labels['5_minutes'] = '5 分钟';
$labels['10_minutes'] = '10 分钟';
$labels['20_minutes'] = '20 分钟';
$labels['30_minutes'] = '30 分钟';
$labels['1_hour'] = '1 小时';
$labels['2_hours'] = '2 小时';
$labels['tomorrow'] = '直到明天';
$labels['next_week'] = '下周再见';

// sidebar
$labels['setting_show_xcalendar'] = '展会日程 议程';
$labels['todays_agenda'] = '今天的议程';
$labels['no_events_today'] = '今天没有活动安排。';
$labels['sidebar_more'] = '更多';

// event invitation
$labels['itip_title_request'] = '我们邀请您参加一个活动。';
$labels['itip_title_modified'] = '此活动已修改。';
$labels['itip_title_reply_accepted'] = '$u 已接受您的邀请';
$labels['itip_title_reply_tentative'] = '$u 已初步接受您的邀请';
$labels['itip_title_reply_declined'] = '$u 已拒绝您的邀请';
$labels['itip_title_cancel'] = '该活动已取消。';

$labels['itip_email_subject_request'] = '邀请函';
$labels['itip_email_subject_request_modified'] = '活动已修改：';
$labels['itip_email_subject_reply'] = '关于';
$labels['itip_email_subject_cancel'] = '取消：';

$labels['itip_popup_event_added'] = '活动已添加到日历中。';
$labels['itip_popup_event_updated'] = '日历中的活动已更新。';
$labels['itip_popup_event_deleted'] = '从日历中删除事件。';
$labels['itip_popup_response_sent'] = '答复已发送给组织者。';

$labels['itip_message_invitation_accepted'] = '您已经接受了这一邀请。';
$labels['itip_message_invitation_tentative'] = '您已初步接受了这一邀请。';
$labels['itip_message_invitation_declined'] = '您拒绝了这一邀请。';
$labels['itip_message_event_deleted'] = '事件已从您的日历中删除。';
$labels['itip_message_event_updated'] = '您日历中的事件已更新。';
$labels['itip_message_event_missing'] = '您的日历中没有此活动。';

$labels['itip_accept'] = '接受';
$labels['itip_tentative'] = '暂定';
$labels['itip_decline'] = '下降';
$labels['itip_change_reponse'] = '更改响应。';
$labels['itip_add_to_calendar'] = '添加到日历';
$labels['itip_reply_to_sender'] = '回复发件人';
$labels['itip_dont_import'] = '不要添加到日历';
$labels['itip_update_in_calendar'] = '更新日历中的事件';
$labels['itip_delete_from_calendar'] = '从日历中删除事件';

$labels['mail_subject_invitation'] = '邀请函';
$labels['mail_subject_update'] = '更新邀请函：';
$labels['mail_subject_cancel'] = '活动取消：';

// event email notifications
$labels['mail_title'] = '标题';
$labels['mail_start'] = '开始';
$labels['mail_end'] = '结束：';
$labels['mail_description'] = '说明';
$labels['mail_location'] = '地点：';

// event attendees
$labels['attendees'] = '与会者';
$labels['attendee_role'] = '角色';
$labels['attendee_role_required'] = '需要';
$labels['attendee_role_optional'] = '可选';
$labels['attendee_role_chair'] = '主席';
$labels['attendee_role_none'] = '非参与者';
$labels['organizer'] = '组织者';
$labels['attendance'] = '出席情况';
$labels['your_status'] = '您的状态';
$labels['attendance_going'] = '去哪里？';
$labels['attendee_status_yes'] = '是';
$labels['attendee_status_no'] = '没有';
$labels['attendee_status_maybe'] = '也许';
$labels['attendee_status_delegated'] = '授权';
$labels['attendee_status_waiting'] = '等待';
$labels['attendee_status_waiting_for_confirmation'] = '等待确认';
$labels['attendee_status_info_yes'] = '将出席';
$labels['attendee_status_info_no'] = '不参加';
$labels['attendee_status_info_maybe'] = '可能参加';
$labels['attendee_status_info_delegated'] = '委托出席';
$labels['attendee_role'] = '角色';
$labels['attendee_role_info'] = '说明是否要求与会者出席或主持会议。';
$labels['attendee_notify'] = '通知';
$labels['attendee_notify_info'] = '如果选中，将向与会者发送邀请电子邮件。';
$labels['add_attendee'] = '添加与会者';
$labels['attendee_notify_question'] = '向与会者发送通知邮件？';
$labels['attendee_notify_textarea'] = '添加可选信息';
$labels['attendee_notify_send'] = '发送';
$labels['attendee_notify_dont'] = '不要发送';
$labels['attendee_notify_cancel'] = '取消';
$labels['new_attendee_not_saved_warning'] = '您输入了参会者的电子邮件地址，但未将其添加到参会者名单中。是否要跳过此参会者并继续保存？';

// import / export
$labels['import_export'] = '进口/出口';
$labels['export_to_file'] = '导出到文件';
$labels['export_info'] = '单击此按钮可导出并下载 iCalendar (ics) 格式的日历事件。如果日历包含大量事件，导出过程可能需要一些时间。';
$labels['export_no_events'] = '此日历没有任何要导出的事件。';
$labels['import_from_file'] = '从文件导入';
$labels['import_info'] = '单击此按钮可将事件从 iCalendar (ics) 文件导入此日历。';

// email notifications
$labels['appointment_reminder'] = '预约提醒';
$labels['email_notification_subject'] = '提醒： $t at $d ($z)';
$labels['email_notification_cron_error'] = '电子邮件通知已禁用。请联系管理员。';
$labels['alarm_before_event_starts'] = '活动开始前';
$labels['alarm_after_event_starts'] = '活动开始后';
$labels['alarm_before_event_ends'] = '活动结束前';
$labels['alarm_after_event_ends'] = '活动结束后';
$labels['alarm_at_specific_time'] = '在特定时间';

// search
$labels['specify_at_least_one_calendar'] = '指定至少一个要搜索的日历。';
$labels['specify_text_to_search'] = '键入要搜索的文本。';
$labels['clear_search_results'] = '清晰';
$labels['search_outdated_message'] = '搜索结果可能已经过时。请考虑重新进行搜索。';
$labels['date_range'] = '日期范围';
$labels['date_range_subtitle'] = '日期范围：';
$labels['search_results'] = '找到的结果： $n';
$labels['modify_search_options'] = '尝试修改搜索选项。';
$labels['show_shortcuts'] = '显示快捷方式';
$labels['hide_shortcuts'] = '隐藏快捷方式';
$labels['this_month'] = '本月';
$labels['this_year'] = '今年';
$labels['past_30_days'] = '过去 30 天';
$labels['next_30_days'] = '未来 30 天';
$labels['past_6_months'] = '过去 6 个月';
$labels['next_6_months'] = '未来 6 个月';

// calendar grid
$labels['cannot_load_events'] = '无法为日历"$c "加载事件。';
