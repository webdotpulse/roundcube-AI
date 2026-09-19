-- major changes to the alarm schema

CREATE TABLE xcalendar_alarms_tmp (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INT UNSIGNED NOT NULL DEFAULT 0,
    event_id INT UNSIGNED NOT NULL DEFAULT 0,
    event_end TIMESTAMP NULL DEFAULT NULL,
    alarm_type TINYINT(1) NOT NULL DEFAULT 0,
    alarm_number SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    alarm_units VARCHAR(255) NOT NULL DEFAULT 'minutes',
    snooze SMALLINT NOT NULL DEFAULT 0,
    alarm_position TINYINT(1) NOT NULL DEFAULT 0,
    alarm_time TIMESTAMP NULL DEFAULT NULL,
    absolute_datetime TIMESTAMP NULL DEFAULT NULL
);

INSERT INTO xcalendar_alarms_tmp (id, user_id, event_id, event_end, alarm_number, alarm_units, snooze)
    SELECT id, user_id, event_id, event_end, alarm_number, alarm_units, snooze FROM xcalendar_alarms;

DROP TABLE xcalendar_alarms;
ALTER TABLE xcalendar_alarms_tmp RENAME TO xcalendar_alarms;

DROP INDEX IF EXISTS user_event_end;
CREATE INDEX IF NOT EXISTS xcalendar_alarms_time_type_end_user ON xcalendar_alarms (alarm_time, alarm_type, event_end, user_id);

-- events

ALTER TABLE xcalendar_events ADD vevent_uid VARCHAR(255) NOT NULL DEFAULT "";

-- not adding foreign keys like in the other databases because rc doesn't use foreign keys in sqlite