<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = '캘린더';
$labels['app_menu_title'] = '캘린더';

// menu
$labels['create_event'] = '이벤트 만들기';
$labels['create_event_button'] = '새로운 이벤트';
$labels['day'] = '일';
$labels['week'] = '주';
$labels['month'] = '월';
$labels['agenda'] = '아젠다';

// editing calendars
$labels['calendars'] = '캘린더';
$labels['my_calendar'] = '내 캘린더';
$labels['create_calendar'] = '캘린더 만들기';
$labels['new_calendar'] = '새 캘린더';
$labels['edit_calendar'] = '캘린더 편집';
$labels['remove_calendar'] = '캘린더 삭제';
$labels['confirm_remove_calendar'] = '이 캘린더를 삭제하시겠습니까?';
$labels['calendar_removed'] = '캘린더가 삭제되었습니다.';
$labels['notifications'] = '알림';
$labels['default_event_visibility'] = '기본 이벤트 가시성';
$labels['time_zone'] = '캘린더 시간대';
$labels['share_calendar'] = '캘린더 공유';
$labels['permissions'] = '권한';
$labels['permission_publish_calendar'] = '캘린더 게시';
$labels['permission_share_calendar'] = '캘린더 공유';
$labels['permission_edit_events'] = '이벤트 편집';
$labels['permission_see_details'] = '자세히 보기';
$labels['permission_publish_calendar_info'] = '이 옵션을 선택하면 사용자가 캘린더를 게시할 수 있습니다.';
$labels['permission_share_calendar_info'] = '이 옵션을 선택하면 사용자가 다른 사용자와 캘린더를 공유할 수 있습니다.';
$labels['permission_edit_events_info'] = '이 옵션을 선택하면 사용자가 캘린더 이벤트를 편집하고 삭제할 수 있습니다.';
$labels['permission_see_details_info'] = '체크하면 모든 이벤트 세부 정보가 공유됩니다. 선택하지 않으면 사용 가능/사용 중 정보만 공유됩니다.';
$labels['share_send_notification'] = '알림';
$labels['share_send_notification_info'] = '이 옵션을 선택하면 사용자에게 새로 공유된 캘린더를 알리는 이메일이 전송됩니다.';
$labels['new_share_not_saved_warning'] = '캘린더를 공유할 이메일 주소를 입력했지만 공유 버튼을 클릭하여 공유 목록에 추가하지 않았습니다. 이 사용자와 캘린더를 공유하지 않고 계속 저장하시겠습니까?';
$labels['share'] = '공유';
$labels['shared_marker'] = '(공유)';
$labels['owner'] = '소유자';
$labels['publish_calendar'] = '캘린더 게시';
$labels['create_link'] = '링크 만들기';
$labels['reset'] = '초기화';
$labels['remove'] = '제거';
$labels['publish_full'] = '모든 이벤트 정보를 게시합니다.';
$labels['publish_busy'] = '무료/바쁜 정보만 게시합니다.';
$labels['sync_no_id'] = '캘린더를 저장하면 CalDAV 옵션을 사용할 수 있게 됩니다.';
$labels['share_no_id'] = '캘린더 공유 옵션은 캘린더를 저장한 후에 사용할 수 있습니다.';
$labels['publish_no_id'] = '캘린더 게시 옵션은 캘린더를 저장한 후에 사용할 수 있습니다.';
$labels['import_no_id'] = '가져오기 및 내보내기 옵션은 캘린더를 저장한 후에 사용할 수 있습니다.';
$labels['unable_to_save_calendar_data'] = '캘린더 데이터를 저장할 수 없습니다.';
$labels['unable_to_retrieve_calendar_data'] = '캘린더 데이터를 검색할 수 없습니다. 캘린더를 더 이상 사용할 수 없을 수 있습니다.';
$labels['unable_add_shared_calendar'] = '공유 캘린더를 추가할 수 없습니다. 캘린더를 더 이상 사용할 수 없을 수 있습니다.';
$labels['itip_organizer_message'] = '이 이벤트에 초대를 받았지만 주최자가 아닙니다. 이 이벤트에 대한 변경 사항은 다른 참석자에게 전송되지 않습니다.';
$labels['cannot_share_with_yourself'] = '자신의 캘린더는 자신과 공유할 수 없습니다.';
$labels['cannot_share_with_calendar_owner'] = '이 캘린더를 캘린더 소유자($e)와 공유할 수 없습니다.';
$labels['setting_default_notification_type'] = '새 이벤트에 대한 기본 알림';
$labels['calendar_name_cannot_be_empty'] = '캘린더 이름은 비워 둘 수 없습니다.';

// caldav server
$labels['sync_calendar'] = '캘린더 동기화';
$labels['password_too_short'] = '비밀번호는 10자 이상이어야 합니다.';
$labels['read_only'] = '읽기 전용';
$labels['add_connection'] = '연결 추가';
$labels['show_details'] = '세부 정보 보기';
$labels['hide_details'] = '세부 정보 숨기기';
$labels['caldav_connection_password'] = '이 연결을 만들 때 지정한 비밀번호입니다.';
$labels['yes'] = '예';
$labels['no'] = '아니요';
$labels['confirm_remove_sync'] = '이 연결을 영구적으로 제거하시겠습니까?';
$labels['remove_sync_error'] = '연결을 제거하는 동안 오류가 발생했습니다.';
$labels['create_new_caldav_connection'] = '새 CalDav 연결 만들기';
$labels['your_caldav_connections'] = 'CalDav 연결';
$labels['created_at'] = '생성됨';
$labels['connected_at'] = '마지막 동기화';
$labels['never'] = '절대로';
$labels['alternate_url'] = '대체 URL';
$labels['caldav_password_info'] = '보안상의 이유로 이 비밀번호는 서버에 저장되지 않습니다. 비밀번호를 잊어버린 경우 이 연결을 삭제하고 새 연결을 만드세요.';
$labels['caldav_url_info'] = '이 URL은 캘린더 검색을 지원하는 앱(예: iOS 및 Mac OS 캘린더, Evolution, emClient)에서 사용하세요.';
$labels['caldav_alternate_url_info'] = '예를 들어 위의 짧은 URL에서 작동하지 않는 앱에는 이 URL을 사용하세요: Outlook CalDav 동기화 프로그램, 썬더버드 라이트닝.';
$labels['invalid_caldav_url'] = 'CalDAV 서버 URL이 잘못되었습니다.';
$labels['cannot_resolve_caldav_host'] = 'CalDAV 서버 호스트를 확인할 수 없습니다.';
$labels['private_caldav_addresses_not_allowed'] = '비공개 및 예약된 CalDAV 주소는 사용할 수 없습니다.';

// adding a shared calendar
$labels['add_shared_calendar'] = '공유 캘린더 추가';
$labels['shared_calendars'] = '공유 캘린더';
$labels['no_shared_calendars'] = '추가할 수 있는 공유 캘린더가 없습니다.';
$labels['add_to_my_calendars'] = '내 캘린더에 추가';
$labels['remove_from_my_calendars'] = '내 캘린더에서 삭제하기';
$labels['unshare'] = '공유 취소';
$labels['unshare_warning'] = '이 캘린더의 공유를 해제하면 이 목록에서 사라지고 다시 추가할 수 없습니다.';
$labels['calendar_name'] = '캘린더 이름';
$labels['calendar_owner'] = '캘린더 소유자';
$labels['shared_date'] = '공유';
$labels['unable_to_share'] = '이메일 계정이 원격 서버에 있으므로 다음 사용자와 캘린더를 공유할 수 없습니다:';

// shared calendar email
$labels['shared_email_subject'] = 'u가 캘린더를 공유했습니다.';
$labels['shared_email_body'] = '사용자 $u가 "$c"라는 캘린더에 대한 액세스 권한을 부여했습니다. 이 캘린더를 활성 캘린더 목록에 추가하려면 아래 링크를 클릭합니다:';
$labels['shared_email_body_cpanel'] = '사용자 $u가 "$c"라는 캘린더에 대한 액세스 권한을 부여했습니다. 이 캘린더를 활성 캘린더 목록에 추가하려면 Roundcube에 로그인하고 캘린더로 이동하여 사이드바에서 더하기 아이콘을 클릭한 다음 공유 캘린더 추가를 선택합니다.';

// adding holiday calendars
$labels['holiday_calendars'] = '휴일 캘린더';
$labels['edit_holiday_calendar'] = '휴일 캘린더 편집';
$labels['religious_holidays'] = '종교 공휴일';
$labels['country_holidays'] = '국가별 공휴일';
$labels['add'] = '추가';
$labels['no_more_holidays_allowed'] = '휴일 캘린더는 더 이상 추가할 수 없습니다.';
$labels['cannot_load_holidays'] = 'Google 서버에서 휴일을 로드할 수 없습니다.';

// adding birthday calendar
$labels['add_birthday_calendar'] = '생일 캘린더 추가';
$labels['edit_birthday_calendar'] = '생일 캘린더 편집';
$labels['contact_birthdays'] = '생일 연락처';
$labels['contact_birthdays_info'] = '이 캘린더는 연락처 목록에서 생일 정보를 검색하여 캘린더 이벤트로 표시합니다.';
$labels['birthday_event_title'] = '생일: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Google 캘린더 추가';
$labels['edit_google_calendar'] = 'Google 캘린더 편집';
$labels['google_id'] = 'Google 캘린더 ID';
$labels['new_google_calendar'] = '새로운 Google 캘린더';
$labels['specify_google_calendar_id'] = 'Google 캘린더 ID를 입력하세요.';
$labels['calendar_id_format_error'] = 'Google 캘린더 ID에는 @ 문자가 포함되어야 합니다.';
$labels['cannot_load_google_calendar'] = 'Google 캘린더 "$c"를 로드할 수 없습니다. 캘린더 ID가 올바른지, 캘린더가 공개적으로 사용 가능하도록 설정되어 있는지 확인하세요.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'CalDAV 캘린더 추가';
$labels['edit_caldav_calendar'] = 'CalDAV 캘린더 편집';
$labels['add_caldav_calendar_information'] = 'CalDAV 캘린더를 구독하려면 CalDAV 서버 정보를 지정하고 캘린더 찾기를 클릭합니다.';
$labels['caldav_url'] = 'CalDAV URL';
$labels['find_calendars'] = '캘린더 찾기';
$labels['searching_for_calendars'] = '캘린더를 검색하는 중입니다... 잠시만 기다려주세요...';
$labels['select_calendars'] = '추가할 캘린더를 선택합니다:';
$labels['read_only_status_yes'] = '예';
$labels['read_only_status_no'] = '아니요';
$labels['subscribed'] = '구독';

$labels['refresh_calendar'] = '캘린더 새로 고침';
$labels['refresh_every_minute'] = '매 순간';
$labels['refresh_every_5_minutes'] = '5분마다';
$labels['refresh_every_15_minutes'] = '15분마다';
$labels['refresh_every_30_minutes'] = '30분마다';
$labels['refresh_every_60_minutes'] = '60분마다';
$labels['refresh_never'] = '절대로';

$labels['cannot_connect_to_caldav_server'] = 'CalDAV 서버 "$n"에 연결할 수 없습니다.';
$labels['caldav_client_error_url'] = 'CalDAV 서버에 연결할 수 없습니다. 서버 URL을 확인하세요.';
$labels['caldav_client_error_credentials'] = 'CalDAV 사용자 이름 또는 비밀번호가 잘못되었습니다.';
$labels['caldav_client_error_empty'] = '서버에서 추가할 수 있는 캘린더를 반환하지 않았습니다. CalDAV URL을 확인하세요.';
$labels['caldav_client_error_server_info'] = '잘못된 CalDAV 서버 정보입니다.';
$labels['caldav_client_error_add_calendar'] = '캘린더 "$n"을 저장할 수 없습니다.';
$labels['caldav_client_error_delete_event'] = '이벤트를 삭제할 수 없습니다.';
$labels['caldav_client_error_save_event'] = 'CalDAV 일정을 저장할 수 없습니다. 일정이 이동되었거나 삭제되었거나, 캘린더에 액세스할 수 없는 상태일 수 있습니다.';
$labels['caldav_client_error_calendar_added'] = '캘린더 "$n"이 이미 캘린더 목록에 있습니다.';

// previewing event
$labels['remove_recurring_event'] = '반복 이벤트 삭제';
$labels['remove_one'] = '이 인스턴스만 삭제';
$labels['remove_all'] = '모든 인스턴스 삭제';
$labels['edit_event'] = '이벤트 편집';
$labels['event_removed'] = '이벤트가 삭제되었습니다.';
$labels['go_to_url'] = 'URL로 이동';
$labels['confirm_remove_event'] = '이 이벤트를 삭제하시겠습니까?';

// calendar/agenda grid
$labels['no_events'] = '표시할 이벤트가 없습니다.';

// importing events
$labels['select_calendar'] = '캘린더 선택';
$labels['select_calendar_for_events'] = '이벤트를 추가할 캘린더를 선택합니다:';
$labels['add_events'] = '이벤트 추가';
$labels['add_events_to_calendar'] = '캘린더에 이벤트 추가';
$labels['events_imported_successfully'] = '이벤트 가져오기에 성공했습니다.';
$labels['events_imported_with_errors'] = '다른 이벤트를 가져올 수 없습니다.';
$labels['error_importing_events'] = '캘린더로 이벤트를 가져오는 동안 오류가 발생했습니다.';

// editing events
$labels['calendar'] = '캘린더';
$labels['new_event'] = '새로운 이벤트';
$labels['summary'] = '요약';
$labels['recurrence'] = '재발';
$labels['title'] = '제목';
$labels['location'] = '위치';
$labels['description'] = '설명';
$labels['comment'] = '댓글';
$labels['url'] = 'URL';
$labels['name'] = '이름';
$labels['start'] = '시작';
$labels['end'] = '종료';
$labels['show_timezones'] = '시간대 표시';
$labels['reset_timezones'] = '표준 시간대를 기본값으로 재설정';
$labels['all_day'] = '하루 종일';
$labels['use_calendar_colors'] = '캘린더 색상 사용';
$labels['color'] = '색상';
$labels['background'] = '배경';
$labels['text'] = '텍스트';
$labels['show_me_as'] = '다른 이름으로 표시';
$labels['available'] = '사용 가능';
$labels['busy'] = '바쁘다';
$labels['visibility'] = '가시성';
$labels['calendar_default'] = '캘린더 기본값';
$labels['public'] = '공개';
$labels['private'] = '비공개';
$labels['repeat'] = '반복';
$labels['repeat_every'] = '모든';
$labels['never'] = '절대로';
$labels['daily'] = '매일';
$labels['weekly'] = '주간';
$labels['monthly'] = '월간';
$labels['yearly'] = '연간';
$labels['days'] = '일(일)';
$labels['weeks'] = '주(주)';
$labels['months'] = '개월(들)';
$labels['years'] = '연도';
$labels['repeat_on'] = '다음에서 반복';
$labels['repeat_by'] = '반복 기준';
$labels['day_of_month'] = '월의 요일';
$labels['day_of_week'] = '요일';
$labels['not_specified'] = '지정되지 않음';
$labels['first'] = '먼저';
$labels['second'] = '둘째';
$labels['third'] = '셋째';
$labels['fourth'] = '넷째';
$labels['fifth'] = '다섯째';
$labels['second_to_last'] = '두 번째부터 마지막까지';
$labels['last'] = '마지막';
$labels['attachment_too_large'] = '첨부 파일이 너무 큽니다. 허용되는 최대 크기는 $s입니다.';
$labels['bg_color'] = '배경색';
$labels['tx_color'] = '텍스트 색상';
$labels['add_notification'] = '알림 추가';
$labels['popup'] = '팝업';
$labels['no_notifications'] = '이 이벤트에는 알림이 없습니다.';
$labels['minutes'] = '분';
$labels['hours'] = '시간';
$labels['link'] = '링크';
$labels['attach_file_from_web'] = '파일 첨부';
$labels['attachment_url'] = '첨부 파일 URL';
$labels['invalid_url'] = '이 URL은 유효하지 않습니다.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google 드라이브';
$labels['duplicate'] = '복제';
$labels['duplicate_suffix'] = '(복제본)';
$labels['no_notifications'] = '이 이벤트에는 알림이 없습니다.';
$labels['this_calendar_is_hidden'] = '이 캘린더는 숨겨져 있습니다. 캘린더가 표시될 때까지 저장된 일정은 나타나지 않습니다.';
$labels['make_calendar_visible'] = '이 달력을 공개로 설정하세요.';

// recurrence
$labels['recurrence_range'] = '반복 범위';
$labels['repeat_forever'] = '영원히 반복';
$labels['repeat_every_weekday'] = '평일마다 반복';
$labels['last_day'] = '마지막 날';
$labels['every'] = '모든';
$labels['weekday'] = '평일';
$labels['weekend_day'] = '주말 일';
$labels['day_of_week'] = '요일';
$labels['day_of_month'] = '월의 요일';

// used together as: End after 5 occurrences
$labels['end_after'] = '이후 종료';
$labels['occurrences'] = '발생';

// end recurrence on date:
$labels['end_by_date'] = '종료 날짜';
$labels['removed_events_title'] = '반복 시리즈에서 이벤트 제거';
$labels['restore'] = '복원';

// settings page
$labels['setting_view'] = '초기 캘린더 보기';
$labels['setting_first_day'] = '주 시작일';
$labels['setting_agenda_week_span'] = '의제 기간(주)';
$labels['setting_slot_duration'] = '주간 시간대(분)';
$labels['setting_scroll_time'] = '시간으로 스크롤';
$labels['setting_calendar'] = '이벤트의 기본 캘린더';
$labels['setting_refresh'] = '캘린더 새로 고침';
$labels['setting_week_numbers'] = '작은 달력의 주 번호';
$labels['setting_event_border'] = '이벤트 테두리로 카테고리 색상 사용';
$labels['show_sunrise'] = '월별 보기에서 일출 표시';
$labels['show_sunset'] = '월별 보기에서 일몰 표시';
$labels['categories'] = '카테고리';
$labels['category'] = '카테고리';
$labels['no_category'] = '카테고리 없음';
$labels['add_category'] = '카테고리 추가';
$labels['setting_alarm_sound'] = '알림 소리';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bamboo';
$labels['alarm_sound_bells'] = '벨';
$labels['alarm_sound_chord'] = '코드';
$labels['alarm_sound_circles'] = '서클';
$labels['alarm_sound_glass'] = '유리';
$labels['alarm_sound_maramba'] = '마람바';
$labels['alarm_sound_metal'] = '금속';
$labels['alarm_sound_ripple'] = '리플';
$labels['alarm_sound_startrek'] = '스타트렉';
$labels['alarm_sound_whistle'] = '휘파람';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = '사운드 재생';

// alarms
$labels['upcoming_events'] = '예정된 이벤트';
$labels['dismiss'] = '해지';
$labels['dismiss_all'] = '모두 해제';
$labels['snooze'] = '스누즈';
$labels['5_minutes'] = '5분';
$labels['10_minutes'] = '10분';
$labels['20_minutes'] = '20분';
$labels['30_minutes'] = '30분';
$labels['1_hour'] = '1시간';
$labels['2_hours'] = '2시간';
$labels['tomorrow'] = '내일까지';
$labels['next_week'] = '다음 주까지';

// sidebar
$labels['setting_show_xcalendar'] = '캘린더 일정 표시';
$labels['todays_agenda'] = '오늘의 의제';
$labels['no_events_today'] = '오늘은 예정된 이벤트가 없습니다.';
$labels['sidebar_more'] = '더 보기...';

// event invitation
$labels['itip_title_request'] = '이벤트에 초대되었습니다.';
$labels['itip_title_modified'] = '이 이벤트가 수정되었습니다.';
$labels['itip_title_reply_accepted'] = 'u가 초대를 수락했습니다.';
$labels['itip_title_reply_tentative'] = 'u가 초대를 잠정적으로 수락했습니다.';
$labels['itip_title_reply_declined'] = 'u가 초대를 거부했습니다.';
$labels['itip_title_cancel'] = '이 이벤트는 취소되었습니다.';

$labels['itip_email_subject_request'] = '초대:';
$labels['itip_email_subject_request_modified'] = '이벤트가 수정되었습니다:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = '취소되었습니다:';

$labels['itip_popup_event_added'] = '캘린더에 이벤트가 추가되었습니다.';
$labels['itip_popup_event_updated'] = '캘린더에서 이벤트가 업데이트되었습니다.';
$labels['itip_popup_event_deleted'] = '캘린더에서 이벤트가 삭제되었습니다.';
$labels['itip_popup_response_sent'] = '주최자에게 응답이 전송됩니다.';

$labels['itip_message_invitation_accepted'] = '이 초대를 수락하셨습니다.';
$labels['itip_message_invitation_tentative'] = '이 초대를 잠정적으로 수락했습니다.';
$labels['itip_message_invitation_declined'] = '이 초대를 거절하셨습니다.';
$labels['itip_message_event_deleted'] = '이벤트가 캘린더에서 삭제되었습니다.';
$labels['itip_message_event_updated'] = '캘린더의 이벤트가 업데이트되었습니다.';
$labels['itip_message_event_missing'] = '이 이벤트는 회원님의 캘린더에 존재하지 않습니다.';

$labels['itip_accept'] = '수락';
$labels['itip_tentative'] = '잠정';
$labels['itip_decline'] = '거절';
$labels['itip_change_reponse'] = '응답을 변경합니다.';
$labels['itip_add_to_calendar'] = '캘린더에 추가';
$labels['itip_reply_to_sender'] = '발신자에게 답장하기';
$labels['itip_dont_import'] = '캘린더에 추가하지 않음';
$labels['itip_update_in_calendar'] = '캘린더에서 이벤트 업데이트';
$labels['itip_delete_from_calendar'] = '캘린더에서 이벤트 삭제';

$labels['mail_subject_invitation'] = '초대:';
$labels['mail_subject_update'] = '업데이트된 초대장:';
$labels['mail_subject_cancel'] = '이벤트가 취소되었습니다:';

// event email notifications
$labels['mail_title'] = '제목:';
$labels['mail_start'] = '시작:';
$labels['mail_end'] = '끝:';
$labels['mail_description'] = '설명:';
$labels['mail_location'] = '위치:';

// event attendees
$labels['attendees'] = '참석자';
$labels['attendee_role'] = '역할';
$labels['attendee_role_required'] = '필수';
$labels['attendee_role_optional'] = '선택 사항';
$labels['attendee_role_chair'] = '의자';
$labels['attendee_role_none'] = '비참가자';
$labels['organizer'] = '주최자';
$labels['attendance'] = '출석';
$labels['your_status'] = '내 상태';
$labels['attendance_going'] = '가나요?';
$labels['attendee_status_yes'] = '예';
$labels['attendee_status_no'] = '아니요';
$labels['attendee_status_maybe'] = '어쩌면';
$labels['attendee_status_delegated'] = '위임';
$labels['attendee_status_waiting'] = '대기 중';
$labels['attendee_status_waiting_for_confirmation'] = '확인 대기 중';
$labels['attendee_status_info_yes'] = '참석 예정';
$labels['attendee_status_info_no'] = '참석하지 않음';
$labels['attendee_status_info_maybe'] = '참석 가능';
$labels['attendee_status_info_delegated'] = '위임된 참석';
$labels['attendee_role'] = '역할';
$labels['attendee_role_info'] = '참석자가 회의에 참석해야 하는지 또는 의장을 맡아야 하는지 지정합니다.';
$labels['attendee_notify'] = '알림';
$labels['attendee_notify_info'] = '이 옵션을 선택하면 참석자에게 초대 이메일이 전송됩니다.';
$labels['add_attendee'] = '참석자 추가';
$labels['attendee_notify_question'] = '참석자에게 알림 이메일을 보내시겠습니까?';
$labels['attendee_notify_textarea'] = '선택적 메시지 추가';
$labels['attendee_notify_send'] = '보내기';
$labels['attendee_notify_dont'] = '보내지 않음';
$labels['attendee_notify_cancel'] = '취소';
$labels['new_attendee_not_saved_warning'] = '참가자의 이메일 주소를 입력하셨지만 참가자 목록에 추가하지 않으셨습니다. 이 참가자를 제외하고 계속 저장하시겠습니까?';

// import / export
$labels['import_export'] = '가져오기/내보내기';
$labels['export_to_file'] = '파일로 내보내기';
$labels['export_info'] = '이 버튼을 클릭하면 이 캘린더의 이벤트를 iCalendar(ics) 형식으로 내보내고 다운로드할 수 있습니다. 캘린더에 많은 이벤트가 포함되어 있는 경우 내보내기 프로세스에 잠시 시간이 걸릴 수 있습니다.';
$labels['export_no_events'] = '이 캘린더에는 내보낼 이벤트가 없습니다.';
$labels['import_from_file'] = '파일에서 가져오기';
$labels['import_info'] = '이 버튼을 클릭하면 iCalendar(ics) 파일에서 이 캘린더로 이벤트를 가져올 수 있습니다.';

// email notifications
$labels['appointment_reminder'] = '약속 알림';
$labels['email_notification_subject'] = '알림: $t에서 $d($z)';
$labels['email_notification_cron_error'] = '이메일 알림이 비활성화되어 있습니다. 관리자에게 문의하세요.';
$labels['alarm_before_event_starts'] = '이벤트 시작 전';
$labels['alarm_after_event_starts'] = '이벤트 시작 후';
$labels['alarm_before_event_ends'] = '이벤트 종료 전';
$labels['alarm_after_event_ends'] = '이벤트 종료 후';
$labels['alarm_at_specific_time'] = '특정 시간에';

// search
$labels['specify_at_least_one_calendar'] = '검색할 캘린더를 하나 이상 지정하세요.';
$labels['specify_text_to_search'] = '검색할 텍스트를 입력합니다.';
$labels['clear_search_results'] = '지우기';
$labels['search_outdated_message'] = '검색 결과가 오래되었을 수 있습니다. 검색을 다시 실행해 보세요.';
$labels['date_range'] = '날짜 범위';
$labels['date_range_subtitle'] = '날짜 범위:';
$labels['search_results'] = '찾은 결과: $n';
$labels['modify_search_options'] = '검색 옵션을 수정해 보세요.';
$labels['show_shortcuts'] = '바로 가기 표시';
$labels['hide_shortcuts'] = '바로 가기 숨기기';
$labels['this_month'] = '이번 달';
$labels['this_year'] = '올해';
$labels['past_30_days'] = '지난 30일';
$labels['next_30_days'] = '향후 30일';
$labels['past_6_months'] = '지난 6개월';
$labels['next_6_months'] = '향후 6개월';

// calendar grid
$labels['cannot_load_events'] = '캘린더 "$c"에 대한 이벤트를 로드할 수 없습니다.';
