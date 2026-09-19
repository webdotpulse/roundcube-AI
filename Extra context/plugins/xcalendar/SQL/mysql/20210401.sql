-- major changes to the alarm schema

ALTER TABLE xcalendar_alarms ADD alarm_position TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE xcalendar_alarms ADD alarm_time TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE xcalendar_alarms ADD absolute_datetime TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE xcalendar_alarms DROP COLUMN alarm_type;
ALTER TABLE xcalendar_alarms ADD alarm_type TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE xcalendar_alarms ADD processing_started TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE xcalendar_alarms DROP INDEX user_id;
ALTER TABLE xcalendar_alarms ADD INDEX xcalendar_alarms_time_type_end_user(alarm_time, alarm_type, event_end, user_id);

-- events

ALTER TABLE xcalendar_events ADD vevent_uid VARCHAR(255) NOT NULL DEFAULT '';

-- foreign keys that enable easy removal of all user records

ALTER TABLE xcalendar_alarms ADD CONSTRAINT event_id_fk_xcalendar_alarms
  FOREIGN KEY (event_id) REFERENCES xcalendar_events(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE xcalendar_attendees ADD CONSTRAINT event_id_fk_xcalendar_attendees
  FOREIGN KEY (event_id) REFERENCES xcalendar_events(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE xcalendar_calendars ADD CONSTRAINT user_id_fk_xcalendar_calendars
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE xcalendar_calendars_shared ADD CONSTRAINT calendar_id_fk_xcalendar_calendars_shared
  FOREIGN KEY (calendar_id) REFERENCES xcalendar_calendars(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE xcalendar_events ADD CONSTRAINT calendar_id_fk_xcalendar_events
  FOREIGN KEY (calendar_id) REFERENCES xcalendar_calendars(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE xcalendar_events_custom ADD CONSTRAINT event_id_fk_xcalendar_events_custom
  FOREIGN KEY (event_id) REFERENCES xcalendar_events(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE xcalendar_events_removed ADD CONSTRAINT event_id_fk_xcalendar_events_removed
  FOREIGN KEY (event_id) REFERENCES xcalendar_events(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE xcalendar_published ADD CONSTRAINT calendar_id_fk_xcalendar_published
  FOREIGN KEY (calendar_id) REFERENCES xcalendar_calendars(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE xcalendar_synced ADD CONSTRAINT calendar_id_fk_xcalendar_synced
  FOREIGN KEY (calendar_id) REFERENCES xcalendar_calendars(id) ON DELETE CASCADE ON UPDATE CASCADE;