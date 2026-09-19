<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Calendar';
$labels['app_menu_title'] = 'Calendar';

// menu
$labels['create_event'] = 'Create event';
$labels['create_event_button'] = 'New event';
$labels['day'] = 'Day';
$labels['week'] = 'Week';
$labels['month'] = 'Month';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Calendars';
$labels['my_calendar'] = 'My Calendar';
$labels['create_calendar'] = 'Create calendar';
$labels['new_calendar'] = 'New calendar';
$labels['edit_calendar'] = 'Edit calendar';
$labels['remove_calendar'] = 'Delete calendar';
$labels['confirm_remove_calendar'] = 'Are you sure you want to delete this calendar?';
$labels['calendar_removed'] = 'Calendar deleted.';
$labels['notifications'] = 'Notifications';
$labels['default_event_visibility'] = 'Default event visibility';
$labels['time_zone'] = 'Calendar time zone';
$labels['share_calendar'] = 'Share calendar';
$labels['permissions'] = 'Permissions';
$labels['permission_publish_calendar'] = 'Publish calendar';
$labels['permission_share_calendar'] = 'Share calendar';
$labels['permission_edit_events'] = 'Edit events';
$labels['permission_see_details'] = 'See details';
$labels['permission_publish_calendar_info'] = 'If checked, the user will be able to publish the calendar.';
$labels['permission_share_calendar_info'] = 'If checked, the user will be able to share the calendar with other users.';
$labels['permission_edit_events_info'] = 'If checked, the user will be able to edit and delete calendar events.';
$labels['permission_see_details_info'] = 'If checked, all event details will be shared. If unchecked, only the available/busy information will be shared.';
$labels['share_send_notification'] = 'Notify';
$labels['share_send_notification_info'] = 'If checked, an email will be sent to the user informing him or her of the newly shared calendar.';
$labels['new_share_not_saved_warning'] = 'You entered an email address to share the calendar but didn\'t click the Share button to add it to the shared list. Would you like to continue saving without sharing the calendar with this user?';
$labels['share'] = 'Share';
$labels['shared_marker'] = '(Shared)';
$labels['owner'] = 'Owner';
$labels['publish_calendar'] = 'Publish calendar';
$labels['create_link'] = 'Create link';
$labels['reset'] = 'Reset';
$labels['remove'] = 'Remove';
$labels['publish_full'] = 'Publish all the event information.';
$labels['publish_busy'] = 'Publish the free/busy information only.';
$labels['sync_no_id'] = 'CalDAV options will become available after you save the calendar.';
$labels['share_no_id'] = 'Calendar sharing options will become available after you save the calendar.';
$labels['publish_no_id'] = 'Calendar publishing options will become available after you save the calendar.';
$labels['import_no_id'] = 'Import and export options will become available after you save the calendar.';
$labels['unable_to_save_calendar_data'] = 'Unable to save calendar data.';
$labels['unable_to_retrieve_calendar_data'] = 'Unable to retrieve calendar data. The calendar might be no longer available.';
$labels['unable_add_shared_calendar'] = 'Unable add the shared calendar. The calendar might be no longer available.';
$labels['itip_organizer_message'] = 'You were invited to this event but you are not the organizer. The changes you make to this event will not be sent to the other attendees.';
$labels['cannot_share_with_yourself'] = 'You cannot share your own calendar with yourself.';
$labels['cannot_share_with_calendar_owner'] = 'You cannot share this calendar with the calendar owner ($e)'; // $e - email
$labels['setting_default_notification_type'] = 'Default notifications for new events';
$labels['calendar_name_cannot_be_empty'] = 'The calendar name cannot be empty.';

// caldav server
$labels['sync_calendar'] = 'Sync calendar';
$labels['password_too_short'] = 'The password should be at least 10 characters long';
$labels['read_only'] = 'Read only';
$labels['add_connection'] = 'Add connection';
$labels['show_details'] = 'Show details';
$labels['hide_details'] = 'Hide details';
$labels['caldav_connection_password'] = 'The password you specified when creating this connection.';
$labels['yes'] = 'Yes';
$labels['no'] = 'No';
$labels['confirm_remove_sync'] = 'Are you sure you want to permanently remove this connection?';
$labels['remove_sync_error'] = 'There was an error while removing the connection.';
$labels['create_new_caldav_connection'] = 'Create new CalDav connection';
$labels['your_caldav_connections'] = 'Your CalDav connections';
$labels['created_at'] = 'Created';
$labels['connected_at'] = 'Last sync';
$labels['never'] = 'Never';
$labels['alternate_url'] = 'Alternate URL';
$labels['caldav_password_info'] = 'For security reasons this password is not saved on the server. If you forgot it, delete this connection and create a new one.';
$labels['caldav_url_info'] = 'Use this URL with apps that support calendar discovery, for example: iOS and Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = "Use this URL with apps that won't work with the short url above, for example: Outlook CalDav Synchronizer, Thunderbird Lightning.";
$labels['invalid_caldav_url'] = 'Invalid CalDAV server url.';
$labels['cannot_resolve_caldav_host'] = 'Cannot resolve CalDAV server host.';
$labels['private_caldav_addresses_not_allowed'] = 'Private and reserved CalDAV addresses are not allowed.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Add shared calendar';
$labels['shared_calendars'] = 'Shared calendars';
$labels['no_shared_calendars'] = 'There are no shared calendars you can add.';
$labels['add_to_my_calendars'] = 'Add to my calendars';
$labels['remove_from_my_calendars'] = 'Remove from my calendars';
$labels['unshare'] = 'Unshare';
$labels['unshare_warning'] = 'If you unshare this calendar it will disappear from this list and you will not be able to add it again.';
$labels['calendar_name'] = 'Calendar name';
$labels['calendar_owner'] = 'Calendar owner';
$labels['shared_date'] = 'Shared';
$labels['unable_to_share'] = 'The calendar cannot be shared with the following users because their email accounts are located on a remote server:';

// shared calendar email
$labels['shared_email_subject'] = '$u has shared a calendar with you'; // $u - user
$labels['shared_email_body'] = 'The user $u has given you access to a calendar called "$c." To add this calendar to your list of active calendars, click the link below:';
$labels['shared_email_body_cpanel'] = 'The user $u has given you access to a calendar called "$c." To add this calendar to your list of active calendars, log in to Roundcube, go to Calendar, click the plus icon on the sidebar and select Add shared calendar.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Holiday calendars';
$labels['edit_holiday_calendar'] = 'Edit holiday calendar';
$labels['religious_holidays'] = 'Religious holidays';
$labels['country_holidays'] = 'Country holidays';
$labels['add'] = 'Add';
$labels['no_more_holidays_allowed'] = 'You cannot add any more holiday calendars.';
$labels['cannot_load_holidays'] = 'Cannot load holidays from the Google server.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Add Birthday calendar';
$labels['edit_birthday_calendar'] = 'Edit Birthday calendar';
$labels['contact_birthdays'] = 'Contact Birthdays';
$labels['contact_birthdays_info'] = 'This calendar retrieves birthday information from your contact list and displays it as calendar events.';
$labels['birthday_event_title'] = 'Birthday: $n'; // $n - name

// adding google calendars
$labels['add_google_calendar'] = 'Add Google calendar';
$labels['edit_google_calendar'] = 'Edit Google calendar';
$labels['google_id'] = 'Google calendar ID';
$labels['new_google_calendar'] = 'New Google Calendar';
$labels['specify_google_calendar_id'] = 'Please type the Google calendar ID';
$labels['calendar_id_format_error'] = 'The Google calendar ID should include the @ character.';
$labels['cannot_load_google_calendar'] = 'The Google calendar "$c" cannot be loaded. Make sure that the calendar ID is correct and that the calendar has been made available to public.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Add CalDAV calendar';
$labels['edit_caldav_calendar'] = 'Edit CalDAV calendar';
$labels['add_caldav_calendar_information'] = 'To subscribe to a CalDAV calendar, specify the CalDAV server information and click Find calendars.';
$labels['caldav_url'] = 'CalDAV URL';
$labels['find_calendars'] = 'Find calendars';
$labels['searching_for_calendars'] = 'Searching for calendars, please wait...';
$labels['select_calendars'] = 'Select calendars to add:';
$labels['read_only_status_yes'] = 'Yes';
$labels['read_only_status_no'] = 'No';
$labels['subscribed'] = 'Subscribed';

$labels['refresh_calendar'] = 'Refresh calendar';
$labels['refresh_every_minute'] = 'Every minute';
$labels['refresh_every_5_minutes'] = 'Every 5 minutes';
$labels['refresh_every_15_minutes'] = 'Every 15 minutes';
$labels['refresh_every_30_minutes'] = 'Every 30 minutes';
$labels['refresh_every_60_minutes'] = 'Every 60 minutes';
$labels['refresh_never'] = 'Never';

$labels['cannot_connect_to_caldav_server'] = 'Cannot connect to CalDAV server "$n".'; // $n - calendar name
$labels['caldav_client_error_url'] = 'Cannot connect to the CalDAV server. Check the server URL.';
$labels['caldav_client_error_credentials'] = 'Incorrect CalDAV username or password.';
$labels['caldav_client_error_empty'] = 'The server did not return any calendars that can be added. Check the CalDAV URL.';
$labels['caldav_client_error_server_info'] = 'Incorrect CalDAV server information.';
$labels['caldav_client_error_add_calendar'] = 'Cannot save calendar "$n".'; // $n - calendar name
$labels['caldav_client_error_delete_event'] = 'Cannot delete event';
$labels['caldav_client_error_save_event'] = 'Cannot save CalDAV event: it may have been moved, deleted, or the calendar is unavailable.';
$labels['caldav_client_error_calendar_added'] = 'The calendar "$n" is already in your calendar list.'; // $n - calendar name

// previewing event
$labels['remove_recurring_event'] = 'Delete recurring event';
$labels['remove_one'] = 'Delete only this instance';
$labels['remove_all'] = 'Delete all instances';
$labels['edit_event'] = 'Edit event';
$labels['event_removed'] = 'Event deleted.';
$labels['go_to_url'] = 'Go to URL';
$labels['confirm_remove_event'] = 'Are you sure you want to delete this event?';

// calendar/agenda grid
$labels['no_events'] = 'There are no events to display.';

// importing events
$labels['select_calendar'] = 'Select calendar';
$labels['select_calendar_for_events'] = 'Select the calendar you would like to add the events to:';
$labels['add_events'] = 'Add events';
$labels['add_events_to_calendar'] = 'Add events to calendar';
$labels['events_imported_successfully'] = 'Events imported successfully';
$labels['events_imported_with_errors'] = 'Some other event could not be imported.';
$labels['error_importing_events'] = 'There was an error while importing events into calendar';

// editing events
$labels['calendar'] = 'Calendar';
$labels['new_event'] = 'New event';
$labels['summary'] = 'Summary';
$labels['recurrence'] = 'Recurrence';
$labels['title'] = 'Title';
$labels['location'] = 'Location';
$labels['description'] = 'Description';
$labels['comment'] = 'Comment';
$labels['url'] = 'URL';
$labels['name'] = 'Name';
$labels['start'] = 'Start';
$labels['end'] = 'End';
$labels['show_timezones'] = 'Show timezones';
$labels['reset_timezones'] = 'Reset timezones to default';
$labels['all_day'] = 'All day';
$labels['use_calendar_colors'] = 'Use calendar colors';
$labels['color'] = 'Color';
$labels['background'] = 'Background';
$labels['text'] = 'Text';
$labels['show_me_as'] = 'Show me as';
$labels['available'] = 'Available';
$labels['busy'] = 'Busy';
$labels['visibility'] = 'Visibility';
$labels['calendar_default'] = 'Calendar default';
$labels['public'] = 'Public';
$labels['private'] = 'Private';
$labels['repeat'] = 'Repeat';
$labels['repeat_every'] = 'Repeat every';
$labels['never'] = 'Never';
$labels['daily'] = 'Daily';
$labels['weekly'] = 'Weekly';
$labels['monthly'] = 'Monthly';
$labels['yearly'] = 'Yearly';
$labels['days'] = 'day(s)';
$labels['weeks'] = 'week(s)';
$labels['months'] = 'month(s)';
$labels['years'] = 'year(s)';
$labels['repeat_on'] = 'Repeat on';
$labels['repeat_by'] = 'Repeat by';
$labels['day_of_month'] = 'day of the month';
$labels['day_of_week'] = 'day of the week';
$labels['not_specified'] = 'Not specified';
$labels['first'] = 'First';
$labels['second'] = 'Second';
$labels['third'] = 'Third';
$labels['fourth'] = 'Fourth';
$labels['fifth'] = 'Fifth';
$labels['second_to_last'] = 'Second to last';
$labels['last'] = 'Last';
$labels['attachment_too_large'] = 'The attachment file is too large. The maximum allowed size is $s.'; // $s - size
$labels['bg_color'] = 'Background color';
$labels['tx_color'] = 'Text color';
$labels['add_notification'] = 'Add notification';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'This event doesn\'t have any notifications.';
$labels['minutes'] = 'minutes';
$labels['hours'] = 'hours';
$labels['link'] = 'Link';
$labels['attach_file_from_web'] = 'Attach file';
$labels['attachment_url'] = 'Attachment URL';
$labels['invalid_url'] = 'This URL is invalid.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Duplicate';
$labels['duplicate_suffix'] = '(duplicate)';
$labels['no_notifications'] = 'There are no notifications. Click the button below to add one.';
$labels['this_calendar_is_hidden'] = 'This calendar is hidden. Saved events won\'t be shown until the calendar is visible.';
$labels['make_calendar_visible'] = 'Make this calendar visible.';

// recurrence
$labels['recurrence_range'] = 'Recurrence range';
$labels['repeat_forever'] = 'Repeat forever';
$labels['repeat_every_weekday'] = 'Repeat every weekday';
$labels['last_day'] = 'Last day';
$labels['every'] = 'Every';
$labels['weekday'] = 'Weekday';
$labels['weekend_day'] = 'Weekend day';
$labels['day_of_week'] = 'Day of week';
$labels['day_of_month'] = 'Day of month';

// used together as: End after 5 occurrences
$labels['end_after'] = 'End after';
$labels['occurrences'] = 'occurrence(s)';

// end recurrence on date:
$labels['end_by_date'] = 'End by date';
$labels['removed_events_title'] = 'Events removed from recurring series';
$labels['restore'] = 'Restore';

// settings page
$labels['setting_view'] = 'Initial calendar view';
$labels['setting_first_day'] = 'Week starts on';
$labels['setting_agenda_week_span'] = 'Agenda time span (weeks)';
$labels['setting_slot_duration'] = 'Day time slot (minutes)';
$labels['setting_scroll_time'] = 'Scroll to time';
$labels['setting_calendar'] = 'Default calendar for events';
$labels['setting_refresh'] = 'Refresh calendars';
$labels['setting_week_numbers'] = 'Week numbers in small calendar';
$labels['setting_event_border'] = 'Category colors as event borders';
$labels['setting_show_sunrise'] = 'Show sunrise in month view';
$labels['setting_show_sunset'] = 'Show sunset in month view';
$labels['categories'] = 'Categories';
$labels['category'] = 'Category';
$labels['no_category'] = 'No category';
$labels['add_category'] = 'Add category';
$labels['setting_alarm_sound'] = 'Notification sound';
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
$labels['play_sound'] = 'Play sound';

// alarms
$labels['upcoming_events'] = 'Upcoming events';
$labels['dismiss'] = 'Dismiss';
$labels['dismiss_all'] = 'Dismiss all';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 minutes';
$labels['10_minutes'] = '10 minutes';
$labels['20_minutes'] = '20 minutes';
$labels['30_minutes'] = '30 minutes';
$labels['1_hour'] = '1 hour';
$labels['2_hours'] = '2 hours';
$labels['tomorrow'] = 'Until tomorrow';
$labels['next_week'] = 'Until next week';

// sidebar
$labels['setting_show_xcalendar'] = 'Show Calendar Agenda';
$labels['todays_agenda'] = 'Today\'s Agenda';
$labels['no_events_today'] = 'No events scheduled for today.';
$labels['sidebar_more'] = 'More...';

// event invitation
$labels['itip_title_request'] = 'You are invited to an event.';
$labels['itip_title_modified'] = 'This event has been modified.';
$labels['itip_title_reply_accepted'] = '$u has accepted your invitation'; // $u - user
$labels['itip_title_reply_tentative'] = '$u has tentatively accepted your invitation';
$labels['itip_title_reply_declined'] = '$u has declined your invitation';
$labels['itip_title_cancel'] = 'This event has been canceled.';

$labels['itip_email_subject_request'] = 'Invitation:';
$labels['itip_email_subject_request_modified'] = 'Event modified:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Cancelled:';

$labels['itip_popup_event_added'] = 'Event added to calendar.';
$labels['itip_popup_event_updated'] = 'Event updated in calendar.';
$labels['itip_popup_event_deleted'] = 'Event deleted from calendar.';
$labels['itip_popup_response_sent'] = 'Response sent to the organizer.';

$labels['itip_message_invitation_accepted'] = 'You have accepted this invitation.';
$labels['itip_message_invitation_tentative'] = 'You have tentatively accepted this invitation.';
$labels['itip_message_invitation_declined'] = 'You have declined this invitation.';
$labels['itip_message_event_deleted'] = 'The event has been deleted from your calendar.';
$labels['itip_message_event_updated'] = 'The event in your calendar has been updated.';
$labels['itip_message_event_missing'] = 'This event does not exist in any of your calendars.';

$labels['itip_accept'] = 'Accept';
$labels['itip_tentative'] = 'Tentative';
$labels['itip_decline'] = 'Decline';
$labels['itip_change_reponse'] = 'Change response.';
$labels['itip_add_to_calendar'] = 'Add to calendar';
$labels['itip_reply_to_sender'] = 'Reply to sender';
$labels['itip_dont_import'] = 'Don\'t add to calendar';
$labels['itip_update_in_calendar'] = 'Update event in calendar';
$labels['itip_delete_from_calendar'] = 'Delete event from calendar';

$labels['mail_subject_invitation'] = 'Invitation:';
$labels['mail_subject_update'] = 'Updated invitation:';
$labels['mail_subject_cancel'] = 'Event canceled:';

// event email notifications
$labels['mail_title'] = 'Title:';
$labels['mail_start'] = 'Start:';
$labels['mail_end'] = 'End:';
$labels['mail_description'] = 'Description:';
$labels['mail_location'] = 'Location:';

// event attendees
$labels['attendees'] = 'Attendees';
$labels['attendee_role'] = 'Role';
$labels['attendee_role_required'] = 'Required';
$labels['attendee_role_optional'] = 'Optional';
$labels['attendee_role_chair'] = 'Chair';
$labels['attendee_role_none'] = 'Non-participant';
$labels['organizer'] = 'Organizer';
$labels['attendance'] = 'Attendance';
$labels['your_status'] = 'Your status';
$labels['attendance_going'] = 'Going?';
$labels['attendee_status_yes'] = 'Yes';
$labels['attendee_status_no'] = 'No';
$labels['attendee_status_maybe'] = 'Maybe';
$labels['attendee_status_delegated'] = 'Delegated';
$labels['attendee_status_waiting'] = 'Waiting';
$labels['attendee_status_waiting_for_confirmation'] = 'Waiting for confirmation';
$labels['attendee_status_info_yes'] = 'Will attend';
$labels['attendee_status_info_no'] = 'Will not attend';
$labels['attendee_status_info_maybe'] = 'Might attend';
$labels['attendee_status_info_delegated'] = 'Delegated attendance';
$labels['attendee_role'] = 'Role';
$labels['attendee_role_info'] = 'Specify whether the attendee is required to attend or chair the meeting.';
$labels['attendee_notify'] = 'Notify';
$labels['attendee_notify_info'] = 'If checked, an invitation email will be sent to the attendee.';
$labels['add_attendee'] = 'Add Attendee';
$labels['attendee_notify_question'] = 'Send notification emails to attendees?';
$labels['attendee_notify_textarea'] = 'Add an optional message';
$labels['attendee_notify_send'] = 'Send';
$labels['attendee_notify_dont'] = 'Don\'t send';
$labels['attendee_notify_cancel'] = 'Cancel';
$labels['new_attendee_not_saved_warning'] = 'You entered an attendee email address but didn\'t add it to the attendee list. Continue saving without this attendee?';

// import / export
$labels['import_export'] = 'Import / Export';
$labels['export_to_file'] = 'Export to file';
$labels['export_info'] = 'Click this button to export and download this calendar\'s events in the iCalendar (ics) format. If the calendar contains a lot of events, the export process might take a few moments.';
$labels['export_no_events'] = 'This calendar does not have any events to export.';
$labels['import_from_file'] = 'Import from file';
$labels['import_info'] = 'Click this button to import events to this calendar from an iCalendar (ics) file.';

// email notifications
$labels['appointment_reminder'] = 'Appointment reminder';
$labels['email_notification_subject'] = 'Reminder: $t at $d ($z)'; // $t - title, $d - datetime, $z - timezone
$labels['email_notification_cron_error'] = 'Email notifications are disabled. Contact the administrator.';
$labels['alarm_before_event_starts'] = 'Before event starts';
$labels['alarm_after_event_starts'] = 'After event starts';
$labels['alarm_before_event_ends'] = 'Before event ends';
$labels['alarm_after_event_ends'] = 'After event ends';
$labels['alarm_at_specific_time'] = 'At a specific time';

// search
$labels['specify_at_least_one_calendar'] = 'Specify at least one calendar to search.';
$labels['specify_text_to_search'] = 'Type the text to search for.';
$labels['clear_search_results'] = 'Clear';
$labels['search_outdated_message'] = 'Search results may be outdated. Consider re-running your search.';
$labels['date_range'] = 'Date Range';
$labels['date_range_subtitle'] = 'Date range:';
$labels['search_results'] = 'Results Found: $n';
$labels['modify_search_options'] = 'Try modifying the search options.';
$labels['show_shortcuts'] = 'Show shortcuts';
$labels['hide_shortcuts'] = 'Hide shortcuts';
$labels['this_month'] = 'This month';
$labels['this_year'] = 'This year';
$labels['past_30_days'] = 'Past 30 days';
$labels['next_30_days'] = 'Next 30 days';
$labels['past_6_months'] = 'Past 6 months';
$labels['next_6_months'] = 'Next 6 months';

// calendar grid
$labels['cannot_load_events'] = 'Cannot load events for calendar "$c".';
