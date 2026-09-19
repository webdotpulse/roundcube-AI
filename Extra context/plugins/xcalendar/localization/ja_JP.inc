<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'カレンダー';
$labels['app_menu_title'] = 'カレンダー';

// menu
$labels['create_event'] = 'イベント作成';
$labels['create_event_button'] = '新しいイベント';
$labels['day'] = '日';
$labels['week'] = '週間';
$labels['month'] = '月';
$labels['agenda'] = 'アジェンダ';

// editing calendars
$labels['calendars'] = 'カレンダー';
$labels['my_calendar'] = 'マイカレンダー';
$labels['create_calendar'] = 'カレンダー作成';
$labels['new_calendar'] = '新カレンダー';
$labels['edit_calendar'] = 'カレンダー編集';
$labels['remove_calendar'] = 'カレンダーの削除';
$labels['confirm_remove_calendar'] = '本当にこのカレンダーを削除しますか？';
$labels['calendar_removed'] = 'カレンダーが削除された。';
$labels['notifications'] = 'お知らせ';
$labels['default_event_visibility'] = 'デフォルトのイベントの可視性';
$labels['time_zone'] = 'カレンダーのタイムゾーン';
$labels['share_calendar'] = '共有カレンダー';
$labels['permissions'] = 'アクセス許可';
$labels['permission_publish_calendar'] = 'カレンダーの発行';
$labels['permission_share_calendar'] = '共有カレンダー';
$labels['permission_edit_events'] = 'イベントの編集';
$labels['permission_see_details'] = '詳細を見る';
$labels['permission_publish_calendar_info'] = 'チェックした場合、ユーザーはカレンダーを公開することができます。';
$labels['permission_share_calendar_info'] = 'チェックした場合、ユーザーは他のユーザーとカレンダーを共有することができます。';
$labels['permission_edit_events_info'] = 'チェックした場合、ユーザーはカレンダーイベントを編集および削除できます。';
$labels['permission_see_details_info'] = 'チェックを入れると、すべてのイベントの詳細が共有されます。チェックを外すと、使用可能/使用中の情報のみが共有されます。';
$labels['share_send_notification'] = '通知';
$labels['share_send_notification_info'] = 'チェックした場合、新しく共有されたカレンダーを知らせるメールがユーザーに送信されます。';
$labels['new_share_not_saved_warning'] = 'カレンダーを共有するためにメールアドレスを入力しましたが、共有リストに追加するために共有ボタンをクリックしませんでした。このユーザーとカレンダーを共有せずに保存を続けますか？';
$labels['share'] = 'シェア';
$labels['shared_marker'] = '共有';
$labels['owner'] = 'オーナー';
$labels['publish_calendar'] = 'カレンダーの発行';
$labels['create_link'] = 'リンク作成';
$labels['reset'] = 'リセット';
$labels['remove'] = '削除';
$labels['publish_full'] = 'すべてのイベント情報を公開する。';
$labels['publish_busy'] = 'フリー/ビジーの情報のみを掲載する。';
$labels['sync_no_id'] = 'CalDAVオプションは、カレンダーを保存した後に利用可能になります。';
$labels['share_no_id'] = 'カレンダーの共有オプションは、カレンダーを保存した後に利用できるようになります。';
$labels['publish_no_id'] = 'カレンダーの公開オプションは、カレンダーを保存した後に利用できるようになります。';
$labels['import_no_id'] = 'インポートとエクスポートのオプションは、カレンダーを保存した後に利用できるようになります。';
$labels['unable_to_save_calendar_data'] = 'カレンダーデータを保存できない。';
$labels['unable_to_retrieve_calendar_data'] = 'カレンダーデータを取得できません。カレンダーが利用できなくなった可能性があります。';
$labels['unable_add_shared_calendar'] = '共有カレンダーを追加できません。カレンダーが利用できなくなった可能性があります。';
$labels['itip_organizer_message'] = 'あなたはこのイベントに招待されましたが、主催者ではありません。あなたがこのイベントに加えた変更は、他の参加者には送られません。';
$labels['cannot_share_with_yourself'] = '自分のカレンダーを自分と共有することはできない。';
$labels['cannot_share_with_calendar_owner'] = 'カレンダーの所有者とこのカレンダーを共有することはできません ($e)';
$labels['setting_default_notification_type'] = '新規イベントのデフォルト通知';
$labels['calendar_name_cannot_be_empty'] = 'カレンダー名を空にすることはできません。';

// caldav server
$labels['sync_calendar'] = 'カレンダーの同期';
$labels['password_too_short'] = 'パスワードは少なくとも10文字以上でなければなりません。';
$labels['read_only'] = '読むだけ';
$labels['add_connection'] = '接続の追加';
$labels['show_details'] = '詳細を表示';
$labels['hide_details'] = '詳細を隠す';
$labels['caldav_connection_password'] = '接続作成時に指定したパスワード。';
$labels['yes'] = 'はい';
$labels['no'] = 'いいえ';
$labels['confirm_remove_sync'] = 'この接続を永久に削除してもよろしいですか？';
$labels['remove_sync_error'] = '接続の削除中にエラーが発生しました。';
$labels['create_new_caldav_connection'] = '新しいCalDav接続を作成する';
$labels['your_caldav_connections'] = 'あなたのCalDav接続';
$labels['created_at'] = '作成';
$labels['connected_at'] = '最後の同期';
$labels['never'] = '決して';
$labels['alternate_url'] = '代替URL';
$labels['caldav_password_info'] = 'セキュリティ上の理由から、このパスワードはサーバーに保存されません。パスワードを忘れた場合は、この接続を削除して新しい接続を作成してください。';
$labels['caldav_url_info'] = 'このURLは、iOSおよびMac OSのカレンダー、Evolution、emClientなど、カレンダーの検出をサポートするアプリで使用します。';
$labels['caldav_alternate_url_info'] = 'このURLは、上記の短いURLでは動作しないアプリで使用してください：Outlook CalDav Synchronizer、Thunderbird Lightning。';
$labels['invalid_caldav_url'] = 'CalDAVサーバーのURLが無効です。';
$labels['cannot_resolve_caldav_host'] = 'CalDAVサーバーのホストを解決できません。';
$labels['private_caldav_addresses_not_allowed'] = 'プライベートおよびリザーブドのCalDAVアドレスは使用できません。';

// adding a shared calendar
$labels['add_shared_calendar'] = '共有カレンダーの追加';
$labels['shared_calendars'] = '共有カレンダー';
$labels['no_shared_calendars'] = '追加できる共有カレンダーはありません。';
$labels['add_to_my_calendars'] = 'カレンダーに追加';
$labels['remove_from_my_calendars'] = 'カレンダーから削除する';
$labels['unshare'] = 'アンシェア';
$labels['unshare_warning'] = 'このカレンダーの共有を解除すると、このリストから消え、再度追加することはできません。';
$labels['calendar_name'] = 'カレンダー名';
$labels['calendar_owner'] = 'カレンダーの所有者';
$labels['shared_date'] = '共有';
$labels['unable_to_share'] = '以下のユーザーのメールアカウントがリモートサーバーにあるため、カレンダーを共有できません：';

// shared calendar email
$labels['shared_email_subject'] = 'uさんがカレンダーを共有しました';
$labels['shared_email_body'] = 'ユーザー$uはあなたに"$c "というカレンダーへのアクセス権を与えました。このカレンダーをアクティブなカレンダーのリストに追加するには、以下のリンクをクリックしてください：';
$labels['shared_email_body_cpanel'] = 'ユーザ $u から、"$c" というカレンダーへのアクセス権が付与されています。このカレンダーをアクティブなカレンダーのリストに追加するには、Roundcube にログインし、[カレンダー] に移動して、サイドバーのプラスアイコンをクリックし、[共有カレンダーの追加] を選択します。';

// adding holiday calendars
$labels['holiday_calendars'] = 'ホリデーカレンダー';
$labels['edit_holiday_calendar'] = '休日カレンダーの編集';
$labels['religious_holidays'] = '祝祭日';
$labels['country_holidays'] = 'カントリーホリデー';
$labels['add'] = '追加';
$labels['no_more_holidays_allowed'] = 'これ以上ホリデーカレンダーを追加することはできません。';
$labels['cannot_load_holidays'] = 'Googleサーバーから休日をロードできません。';

// adding birthday calendar
$labels['add_birthday_calendar'] = '誕生日カレンダーを追加';
$labels['edit_birthday_calendar'] = '誕生日カレンダーの編集';
$labels['contact_birthdays'] = '誕生日の連絡先';
$labels['contact_birthdays_info'] = 'このカレンダーは、連絡先リストから誕生日情報を取得し、カレンダーのイベントとして表示します。';
$labels['birthday_event_title'] = '誕生日： $n';

// adding google calendars
$labels['add_google_calendar'] = 'グーグルカレンダーを追加する';
$labels['edit_google_calendar'] = 'グーグルカレンダーの編集';
$labels['google_id'] = 'グーグルカレンダーID';
$labels['new_google_calendar'] = '新しいグーグルカレンダー';
$labels['specify_google_calendar_id'] = 'GoogleカレンダーのIDを入力してください。';
$labels['calendar_id_format_error'] = 'グーグルカレンダーのIDは@を含む必要があります。';
$labels['cannot_load_google_calendar'] = 'Googleカレンダー「$c」が読み込めません。カレンダーIDが正しいか、カレンダーが公開されているか確認してください。';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'CalDAVカレンダーの追加';
$labels['edit_caldav_calendar'] = 'CalDAVカレンダーの編集';
$labels['add_caldav_calendar_information'] = 'CalDAV カレンダーを購読するには、CalDAV サーバー情報を指定し、[カレンダーの検索] をクリックします。';
$labels['caldav_url'] = 'CalDAV URL';
$labels['find_calendars'] = 'カレンダーを探す';
$labels['searching_for_calendars'] = 'カレンダーを検索しています。';
$labels['select_calendars'] = '追加するカレンダーを選択します：';
$labels['read_only_status_yes'] = 'はい';
$labels['read_only_status_no'] = 'いいえ';
$labels['subscribed'] = '購読済み';

$labels['refresh_calendar'] = 'カレンダーの更新';
$labels['refresh_every_minute'] = '毎分';
$labels['refresh_every_5_minutes'] = '5分ごと';
$labels['refresh_every_15_minutes'] = '15分ごと';
$labels['refresh_every_30_minutes'] = '30分ごと';
$labels['refresh_every_60_minutes'] = '60分ごと';
$labels['refresh_never'] = '決して';

$labels['cannot_connect_to_caldav_server'] = 'CalDAVサーバー"$n "に接続できません。';
$labels['caldav_client_error_url'] = 'CalDAVサーバーに接続できません。サーバーのURLを確認してください。';
$labels['caldav_client_error_credentials'] = 'CalDAVのユーザー名またはパスワードが間違っています。';
$labels['caldav_client_error_empty'] = 'サーバーが追加可能なカレンダーを返しませんでした。CalDAV URLを確認してください。';
$labels['caldav_client_error_server_info'] = 'CalDAV サーバー情報が正しくありません。';
$labels['caldav_client_error_add_calendar'] = 'カレンダー "$n" を保存できません。';
$labels['caldav_client_error_delete_event'] = 'イベントを削除できない';
$labels['caldav_client_error_save_event'] = 'CalDAVイベントを保存できません。イベントが移動または削除されたか、カレンダーが利用できない可能性があります。';
$labels['caldav_client_error_calendar_added'] = 'カレンダー"$n "はすでにカレンダーリストに登録されています。';

// previewing event
$labels['remove_recurring_event'] = '定期イベントの削除';
$labels['remove_one'] = 'このインスタンスだけを削除する';
$labels['remove_all'] = 'すべてのインスタンスを削除する';
$labels['edit_event'] = 'イベントの編集';
$labels['event_removed'] = 'イベントは削除された。';
$labels['go_to_url'] = 'URLへ';
$labels['confirm_remove_event'] = '本当にこのイベントを削除しますか？';

// calendar/agenda grid
$labels['no_events'] = '表示するイベントはない。';

// importing events
$labels['select_calendar'] = 'カレンダーを選択';
$labels['select_calendar_for_events'] = 'イベントを追加したいカレンダーを選択します：';
$labels['add_events'] = 'イベントの追加';
$labels['add_events_to_calendar'] = 'カレンダーにイベントを追加';
$labels['events_imported_successfully'] = 'イベントのインポートに成功';
$labels['events_imported_with_errors'] = '他のイベントがインポートできなかった。';
$labels['error_importing_events'] = 'カレンダーにイベントをインポートする際にエラーが発生しました。';

// editing events
$labels['calendar'] = 'カレンダー';
$labels['new_event'] = '新しいイベント';
$labels['summary'] = '概要';
$labels['recurrence'] = '再発';
$labels['title'] = 'タイトル';
$labels['location'] = '所在地';
$labels['description'] = '説明';
$labels['comment'] = 'コメント';
$labels['url'] = 'URL';
$labels['name'] = '名称';
$labels['start'] = 'スタート';
$labels['end'] = '終了';
$labels['show_timezones'] = 'タイムゾーンを表示する';
$labels['reset_timezones'] = 'タイムゾーンをデフォルトに戻す';
$labels['all_day'] = '終日';
$labels['use_calendar_colors'] = 'カレンダーの色を使う';
$labels['color'] = 'カラー';
$labels['background'] = '背景';
$labels['text'] = 'テキスト';
$labels['show_me_as'] = 'として表示';
$labels['available'] = '利用可能';
$labels['busy'] = '忙しい';
$labels['visibility'] = '視認性';
$labels['calendar_default'] = 'カレンダーのデフォルト';
$labels['public'] = 'パブリック';
$labels['private'] = 'プライベート';
$labels['repeat'] = 'リピート';
$labels['repeat_every'] = '毎回繰り返す';
$labels['never'] = '決して';
$labels['daily'] = '毎日';
$labels['weekly'] = 'ウィークリー';
$labels['monthly'] = '毎月';
$labels['yearly'] = '年間';
$labels['days'] = '日';
$labels['weeks'] = '週間';
$labels['months'] = '月';
$labels['years'] = '年';
$labels['repeat_on'] = 'を繰り返す。';
$labels['repeat_by'] = 'リピート';
$labels['day_of_month'] = '月';
$labels['day_of_week'] = '曜日';
$labels['not_specified'] = '特になし';
$labels['first'] = '最初';
$labels['second'] = 'セカンド';
$labels['third'] = 'サード';
$labels['fourth'] = '第四';
$labels['fifth'] = 'フィフス';
$labels['second_to_last'] = '最後から2番目';
$labels['last'] = '最後';
$labels['attachment_too_large'] = '添付ファイルが大きすぎます。最大許容サイズは$sです。';
$labels['bg_color'] = '背景色';
$labels['tx_color'] = '文字色';
$labels['add_notification'] = '通知を追加する';
$labels['popup'] = 'ポップアップ';
$labels['no_notifications'] = 'このイベントには何の通知もない。';
$labels['minutes'] = '議事録';
$labels['hours'] = '時間';
$labels['link'] = 'リンク';
$labels['attach_file_from_web'] = '添付ファイル';
$labels['attachment_url'] = '添付URL';
$labels['invalid_url'] = 'このURLは無効です。';
$labels['xdropbox_name'] = 'ドロップボックス';
$labels['xgoogle_drive_name'] = 'グーグルドライブ';
$labels['duplicate'] = '複製する';
$labels['duplicate_suffix'] = '(複製)';
$labels['no_notifications'] = 'このイベントには何の通知もない。';
$labels['this_calendar_is_hidden'] = 'このカレンダーは非表示になっています。カレンダーが表示されるまで、保存された予定は表示されません。';
$labels['make_calendar_visible'] = 'このカレンダーを表示するように設定してください。';

// recurrence
$labels['recurrence_range'] = '再発範囲';
$labels['repeat_forever'] = '永遠に繰り返す';
$labels['repeat_every_weekday'] = '平日毎日繰り返す';
$labels['last_day'] = '最終日';
$labels['every'] = 'すべての';
$labels['weekday'] = '平日';
$labels['weekend_day'] = '週末';
$labels['day_of_week'] = '曜日';
$labels['day_of_month'] = '月';

// used together as: End after 5 occurrences
$labels['end_after'] = '終了';
$labels['occurrences'] = '発生';

// end recurrence on date:
$labels['end_by_date'] = '終了日';
$labels['removed_events_title'] = '定期シリーズから削除されたイベント';
$labels['restore'] = 'リストア';

// settings page
$labels['setting_view'] = 'カレンダーの初期表示';
$labels['setting_first_day'] = '週明け';
$labels['setting_agenda_week_span'] = 'アジェンダの期間（週）';
$labels['setting_slot_duration'] = '日の時間枠（分）';
$labels['setting_scroll_time'] = '時間までスクロール';
$labels['setting_calendar'] = 'イベント用デフォルトカレンダー';
$labels['setting_refresh'] = 'カレンダーの更新';
$labels['setting_week_numbers'] = '小カレンダーの週番号';
$labels['setting_event_border'] = 'カテゴリー・カラーをイベントの境界線に';
$labels['show_sunrise'] = '月ビューで日の出を表示';
$labels['show_sunset'] = '月表示で日没を表示';
$labels['categories'] = 'カテゴリー';
$labels['category'] = 'カテゴリー';
$labels['no_category'] = 'カテゴリーなし';
$labels['add_category'] = 'カテゴリーを追加する';
$labels['setting_alarm_sound'] = '通知音';
$labels['alarm_sound_aurora'] = 'オーロラ';
$labels['alarm_sound_bamboo'] = 'バンブー';
$labels['alarm_sound_bells'] = 'ベル';
$labels['alarm_sound_chord'] = 'コード';
$labels['alarm_sound_circles'] = 'サークル';
$labels['alarm_sound_glass'] = 'ガラス';
$labels['alarm_sound_maramba'] = 'マランバ';
$labels['alarm_sound_metal'] = 'メタル';
$labels['alarm_sound_ripple'] = 'リップル';
$labels['alarm_sound_startrek'] = 'スタートレック';
$labels['alarm_sound_whistle'] = 'ホイッスル';
$labels['alarm_sound_yoohoo'] = 'ヤッホー';
$labels['play_sound'] = 'サウンドを再生する';

// alarms
$labels['upcoming_events'] = '今後のイベント';
$labels['dismiss'] = '解散';
$labels['dismiss_all'] = 'すべて却下';
$labels['snooze'] = 'スヌーズ';
$labels['5_minutes'] = '5分';
$labels['10_minutes'] = '10分';
$labels['20_minutes'] = '20分';
$labels['30_minutes'] = '30分';
$labels['1_hour'] = '1時間';
$labels['2_hours'] = '2時間';
$labels['tomorrow'] = '明日まで';
$labels['next_week'] = '来週まで';

// sidebar
$labels['setting_show_xcalendar'] = 'カレンダー・アジェンダを表示';
$labels['todays_agenda'] = '本日のアジェンダ';
$labels['no_events_today'] = '今日はイベントの予定はない。';
$labels['sidebar_more'] = 'もっと...';

// event invitation
$labels['itip_title_request'] = 'あなたはイベントに招待されている。';
$labels['itip_title_modified'] = 'このイベントは変更されました。';
$labels['itip_title_reply_accepted'] = 'uはあなたの招待を受け入れた';
$labels['itip_title_reply_tentative'] = 'uはあなたの招待を仮承諾しました';
$labels['itip_title_reply_declined'] = 'uはご招待をお断りしました';
$labels['itip_title_cancel'] = 'このイベントは中止となりました。';

$labels['itip_email_subject_request'] = '招待状';
$labels['itip_email_subject_request_modified'] = 'イベントは変更された：';
$labels['itip_email_subject_reply'] = 'Re：';
$labels['itip_email_subject_cancel'] = '中止：';

$labels['itip_popup_event_added'] = 'カレンダーにイベントを追加しました。';
$labels['itip_popup_event_updated'] = 'イベントがカレンダーに更新されました。';
$labels['itip_popup_event_deleted'] = 'カレンダーからイベントが削除されました。';
$labels['itip_popup_response_sent'] = '主催者に返信。';

$labels['itip_message_invitation_accepted'] = 'あなたはこの招待を受け入れた。';
$labels['itip_message_invitation_tentative'] = 'あなたはこの招待を仮承諾した。';
$labels['itip_message_invitation_declined'] = 'あなたはこの招待を断った。';
$labels['itip_message_event_deleted'] = 'イベントはカレンダーから削除されました。';
$labels['itip_message_event_updated'] = 'カレンダーのイベントが更新されました。';
$labels['itip_message_event_missing'] = 'このイベントはどのカレンダーにも存在しません。';

$labels['itip_accept'] = '受け入れる';
$labels['itip_tentative'] = '暫定';
$labels['itip_decline'] = '減少';
$labels['itip_change_reponse'] = '対応を変える。';
$labels['itip_add_to_calendar'] = 'カレンダーに追加';
$labels['itip_reply_to_sender'] = '送信者に返信';
$labels['itip_dont_import'] = 'カレンダーに追加しない';
$labels['itip_update_in_calendar'] = 'カレンダーのイベントを更新する';
$labels['itip_delete_from_calendar'] = 'カレンダーからイベントを削除する';

$labels['mail_subject_invitation'] = '招待状';
$labels['mail_subject_update'] = '招待状を更新した：';
$labels['mail_subject_cancel'] = 'イベント中止';

// event email notifications
$labels['mail_title'] = 'タイトル';
$labels['mail_start'] = 'スタート：';
$labels['mail_end'] = '終了：';
$labels['mail_description'] = '説明';
$labels['mail_location'] = '場所は？';

// event attendees
$labels['attendees'] = '出席者';
$labels['attendee_role'] = '役割';
$labels['attendee_role_required'] = '必須';
$labels['attendee_role_optional'] = 'オプション';
$labels['attendee_role_chair'] = '椅子';
$labels['attendee_role_none'] = '不参加';
$labels['organizer'] = 'オーガナイザー';
$labels['attendance'] = '出席';
$labels['your_status'] = 'あなたのステータス';
$labels['attendance_going'] = '行くのか？';
$labels['attendee_status_yes'] = 'はい';
$labels['attendee_status_no'] = 'いいえ';
$labels['attendee_status_maybe'] = 'たぶん';
$labels['attendee_status_delegated'] = '委任された';
$labels['attendee_status_waiting'] = '待機中';
$labels['attendee_status_waiting_for_confirmation'] = '確認待ち';
$labels['attendee_status_info_yes'] = '出席予定';
$labels['attendee_status_info_no'] = '不参加';
$labels['attendee_status_info_maybe'] = '出席する可能性がある';
$labels['attendee_status_info_delegated'] = '委任出席';
$labels['attendee_role'] = '役割';
$labels['attendee_role_info'] = '出席者が会議に出席する必要があるか、または議長を務める必要があるかを明記する。';
$labels['attendee_notify'] = '通知';
$labels['attendee_notify_info'] = 'チェックを入れると、招待メールが参加者に送信されます。';
$labels['add_attendee'] = '出席者の追加';
$labels['attendee_notify_question'] = '出席者に通知メールを送る？';
$labels['attendee_notify_textarea'] = '任意のメッセージを追加する';
$labels['attendee_notify_send'] = '送信';
$labels['attendee_notify_dont'] = '送るな';
$labels['attendee_notify_cancel'] = 'キャンセル';
$labels['new_attendee_not_saved_warning'] = '参加者のメールアドレスを入力しましたが、参加者リストには追加されていません。この参加者を除外して保存を続行しますか？';

// import / export
$labels['import_export'] = '輸出入';
$labels['export_to_file'] = 'ファイルへのエクスポート';
$labels['export_info'] = 'このボタンをクリックすると、カレンダーのイベントがiCalendar（ics）形式でエクスポートされ、ダウンロードされます。カレンダーのイベントが多い場合、エクスポート処理に時間がかかることがあります。';
$labels['export_no_events'] = 'このカレンダーにはエクスポートするイベントがありません。';
$labels['import_from_file'] = 'ファイルからのインポート';
$labels['import_info'] = 'iCalendar（ics）ファイルからこのカレンダーにイベントをインポートするには、このボタンをクリックします。';

// email notifications
$labels['appointment_reminder'] = '予約リマインダー';
$labels['email_notification_subject'] = 'リマインダー： $t at $d ($z)';
$labels['email_notification_cron_error'] = 'メール通知が無効になっています。管理者に連絡してください。';
$labels['alarm_before_event_starts'] = 'イベント開始前';
$labels['alarm_after_event_starts'] = 'イベント開始後';
$labels['alarm_before_event_ends'] = 'イベント終了前';
$labels['alarm_after_event_ends'] = 'イベント終了後';
$labels['alarm_at_specific_time'] = '特定の時間に';

// search
$labels['specify_at_least_one_calendar'] = '検索するカレンダーを1つ以上指定してください。';
$labels['specify_text_to_search'] = '検索するテキストを入力する。';
$labels['clear_search_results'] = 'クリア';
$labels['search_outdated_message'] = '検索結果が古い可能性があります。再検索をご検討ください。';
$labels['date_range'] = '日付範囲';
$labels['date_range_subtitle'] = '日付の範囲';
$labels['search_results'] = '検索結果： $n';
$labels['modify_search_options'] = '検索オプションを変更してみてください。';
$labels['show_shortcuts'] = 'ショートカットを表示する';
$labels['hide_shortcuts'] = 'ショートカットを隠す';
$labels['this_month'] = '今月';
$labels['this_year'] = '今年';
$labels['past_30_days'] = '過去30日間';
$labels['next_30_days'] = '次の30日';
$labels['past_6_months'] = '過去6ヶ月';
$labels['next_6_months'] = '今後6ヶ月';

// calendar grid
$labels['cannot_load_events'] = 'カレンダー "$c" のイベントをロードできません。';
