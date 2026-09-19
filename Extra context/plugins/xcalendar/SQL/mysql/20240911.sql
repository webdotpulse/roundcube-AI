
ALTER TABLE `xcalendar_alarms` ROW_FORMAT=DYNAMIC;
ALTER TABLE `xcalendar_attachments_temp` ROW_FORMAT=DYNAMIC;
ALTER TABLE `xcalendar_attendees` ROW_FORMAT=DYNAMIC;
ALTER TABLE `xcalendar_calendars` ROW_FORMAT=DYNAMIC;
ALTER TABLE `xcalendar_calendars_shared` ROW_FORMAT=DYNAMIC;
ALTER TABLE `xcalendar_changes` ROW_FORMAT=DYNAMIC;
ALTER TABLE `xcalendar_events` ROW_FORMAT=DYNAMIC;
ALTER TABLE `xcalendar_events_custom` ROW_FORMAT=DYNAMIC;
ALTER TABLE `xcalendar_events_removed` ROW_FORMAT=DYNAMIC;
ALTER TABLE `xcalendar_published` ROW_FORMAT=DYNAMIC;
ALTER TABLE `xcalendar_scheduling_objects` ROW_FORMAT=DYNAMIC;
ALTER TABLE `xcalendar_synced` ROW_FORMAT=DYNAMIC;

ALTER TABLE `xcalendar_alarms` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `xcalendar_attachments_temp` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `xcalendar_attendees` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `xcalendar_calendars` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `xcalendar_calendars_shared` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `xcalendar_changes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `xcalendar_events` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `xcalendar_events_custom` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `xcalendar_events_removed` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `xcalendar_published` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `xcalendar_scheduling_objects` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `xcalendar_synced` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

