CREATE SEQUENCE IF NOT EXISTS xcalendar_calendars_seq START WITH 1 INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;

CREATE TABLE xcalendar_calendars (
    id INTEGER NOT NULL DEFAULT nextval('xcalendar_calendars_seq'::text),
    user_id INTEGER NOT NULL DEFAULT 0,
    type SMALLINT NOT NULL DEFAULT 1,
    url VARCHAR(512) NOT NULL DEFAULT '',
    name VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT,
    timezone VARCHAR(255) NOT NULL DEFAULT '',
    bg_color VARCHAR(255) NOT NULL DEFAULT '',
    tx_color VARCHAR(255) NOT NULL DEFAULT '',
    enabled SMALLINT NOT NULL DEFAULT 1,
    default_event_visibility VARCHAR(255) NOT NULL DEFAULT 'public',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP NULL DEFAULT NULL,
    removed_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id)
);

CREATE INDEX user_enabled_removed ON xcalendar_calendars (user_id, enabled, removed_at);

-----------------

CREATE TABLE xcalendar_calendars_shared (
    email VARCHAR(255) NOT NULL DEFAULT '',
    calendar_id INTEGER NOT NULL DEFAULT 0,
    permissions VARCHAR(255) NOT NULL DEFAULT '',
    added SMALLINT NOT NULL DEFAULT 0,
    add_code VARCHAR(40) NOT NULL DEFAULT '',
    name VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT NOT NULL,
    bg_color VARCHAR(255) NOT NULL DEFAULT '',
    tx_color VARCHAR(255) NOT NULL DEFAULT '',
    enabled SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (email, calendar_id)
);

CREATE INDEX add_code ON xcalendar_calendars_shared (add_code);

-----------------

CREATE SEQUENCE IF NOT EXISTS xcalendar_events_seq START WITH 1 INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;

CREATE TABLE xcalendar_events (
    id INTEGER NOT NULL DEFAULT nextval('xcalendar_events_seq'::text),
    user_id INTEGER NOT NULL DEFAULT 0,
    calendar_id INTEGER NOT NULL DEFAULT 0,
    uid VARCHAR(255) NOT NULL DEFAULT '',
    title VARCHAR(255) NOT NULL DEFAULT '',
    location VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT NOT NULL,
    url VARCHAR(512) NOT NULL DEFAULT '',
    start TIMESTAMP NULL DEFAULT NULL,
    "end" TIMESTAMP NULL DEFAULT NULL,
    all_day SMALLINT NOT NULL DEFAULT 0,
    repeat_rule VARCHAR(255) NOT NULL DEFAULT '',
    repeat_end TIMESTAMP NULL DEFAULT NULL,
    use_calendar_colors SMALLINT NOT NULL DEFAULT 1,
    bg_color VARCHAR(255) NOT NULL DEFAULT '',
    tx_color VARCHAR(255) NOT NULL DEFAULT '',
    busy SMALLINT NOT NULL DEFAULT 0,
    visibility VARCHAR(255) NOT NULL DEFAULT 'default',
    priority SMALLINT NOT NULL DEFAULT 0,
    category VARCHAR(255) NOT NULL DEFAULT '',
    attachments TEXT NOT NULL,
    vevent TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP NULL DEFAULT NULL,
    removed_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id)
);

CREATE INDEX uid ON xcalendar_events (uid);
CREATE INDEX calendar_start_end_removed_repeat ON xcalendar_events (calendar_id, start, "end", removed_at, repeat_end);

-----------------

CREATE SEQUENCE IF NOT EXISTS xcalendar_events_removed_seq START WITH 1 INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;

CREATE TABLE xcalendar_events_removed (
    id INTEGER NOT NULL DEFAULT nextval('xcalendar_events_removed_seq'::text),
    day TIMESTAMP NULL DEFAULT NULL,
    event_id INTEGER NOT NULL DEFAULT 0,
    removed_at TIMESTAMP NULL DEFAULT NULL,
    removed_by INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE INDEX day_event ON xcalendar_events_removed (day, event_id);

-----------------

CREATE TABLE xcalendar_events_custom (
    user_id INTEGER NOT NULL DEFAULT 0,
    event_id INTEGER NOT NULL DEFAULT 0,
    use_calendar_colors SMALLINT NOT NULL DEFAULT 1,
    bg_color VARCHAR(255) NOT NULL DEFAULT '',
    tx_color VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (user_id, event_id)
);

-----------------

CREATE SEQUENCE IF NOT EXISTS xcalendar_alarms_seq START WITH 1 INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;

CREATE TABLE xcalendar_alarms (
    id INTEGER NOT NULL DEFAULT nextval('xcalendar_alarms_seq'::text),
    user_id INTEGER NOT NULL DEFAULT 0,
    event_id INTEGER NOT NULL DEFAULT 0,
    event_end TIMESTAMP NULL DEFAULT NULL,
    alarm_type VARCHAR(255) NOT NULL DEFAULT 'popup',
    alarm_number SMALLINT NOT NULL DEFAULT 0,
    alarm_units VARCHAR(255) NOT NULL DEFAULT 'minutes',
    snooze SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE INDEX user_event_end ON xcalendar_alarms (user_id, event_end);

-----------------

CREATE TABLE xcalendar_attachments_temp (
    filename VARCHAR(255) NOT NULL DEFAULT '',
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (filename, uploaded_at)
);

-----------------

CREATE SEQUENCE IF NOT EXISTS xcalendar_published_seq START WITH 1 INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;

CREATE TABLE xcalendar_published (
    id INTEGER NOT NULL DEFAULT nextval('xcalendar_published_seq'::text),
    user_id INTEGER NOT NULL DEFAULT 0,
    calendar_id INTEGER NOT NULL DEFAULT 0,
    code VARCHAR(255) NOT NULL DEFAULT '',
    "full" SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

CREATE INDEX code ON xcalendar_published (code);

-----------------

CREATE SEQUENCE IF NOT EXISTS xcalendar_synced_seq START WITH 1 INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;

CREATE TABLE xcalendar_synced (
    id INTEGER NOT NULL DEFAULT nextval('xcalendar_synced_seq'::text),
    user_id INTEGER NOT NULL DEFAULT 0,
    calendar_id INTEGER NOT NULL DEFAULT 0,
    name VARCHAR(255) NOT NULL DEFAULT '',
    username VARCHAR(255) NOT NULL DEFAULT '',
    url VARCHAR(255) NOT NULL DEFAULT '',
    password VARCHAR(255) NOT NULL DEFAULT '',
    read_only SMALLINT NOT NULL DEFAULT 0,
    connected_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

CREATE UNIQUE INDEX username ON xcalendar_synced (username);
CREATE UNIQUE INDEX url ON xcalendar_synced (url);
CREATE INDEX user_calendar ON xcalendar_synced (user_id, calendar_id);