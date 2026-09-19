CREATE TABLE IF NOT EXISTS xcalendar_calendars (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INT UNSIGNED NOT NULL DEFAULT 0,
    type TINYINT(1) NOT NULL DEFAULT 1,
    url VARCHAR(512) NOT NULL DEFAULT '',
    name VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT,
    timezone VARCHAR(255) NOT NULL DEFAULT '',
    bg_color VARCHAR(255) NOT NULL DEFAULT '',
    tx_color VARCHAR(255) NOT NULL DEFAULT '',
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    default_event_visibility VARCHAR(255) NOT NULL DEFAULT 'public',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP NULL DEFAULT NULL,
    removed_at TIMESTAMP NULL DEFAULT NULL
);

CREATE INDEX IF NOT EXISTS user_enabled_removed ON xcalendar_calendars (user_id, enabled, removed_at);
CREATE INDEX IF NOT EXISTS user_type_url ON xcalendar_calendars (user_id, type, url);

CREATE TABLE IF NOT EXISTS xcalendar_calendars_shared (
    email VARCHAR(255) NOT NULL DEFAULT '',
    calendar_id INT UNSIGNED NOT NULL DEFAULT 0,
    permissions VARCHAR(255) NOT NULL DEFAULT '',
    added TINYINT(1) NOT NULL DEFAULT 0,
    add_code VARCHAR(40) NOT NULL DEFAULT '',
    name VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT NOT NULL,
    bg_color VARCHAR(255) NOT NULL DEFAULT '',
    tx_color VARCHAR(255) NOT NULL DEFAULT '',
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (email, calendar_id)
);

CREATE INDEX IF NOT EXISTS add_code ON xcalendar_calendars_shared (add_code);

CREATE TABLE IF NOT EXISTS xcalendar_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INT UNSIGNED NOT NULL DEFAULT 0,
    calendar_id INT UNSIGNED NOT NULL DEFAULT 0,
    uid VARCHAR(255) NOT NULL DEFAULT '',
    title VARCHAR(255) NOT NULL DEFAULT '',
    location VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT NOT NULL,
    url VARCHAR(512) NOT NULL DEFAULT '',
    start TIMESTAMP NULL DEFAULT NULL,
    end TIMESTAMP NULL DEFAULT NULL,
    all_day TINYINT(1) NOT NULL DEFAULT 0,
    repeat_rule VARCHAR(255) NOT NULL DEFAULT '',
    repeat_end TIMESTAMP NULL DEFAULT NULL,
    use_calendar_colors TINYINT(1) NOT NULL DEFAULT 1,
    bg_color VARCHAR(255) NOT NULL DEFAULT '',
    tx_color VARCHAR(255) NOT NULL DEFAULT '',
    busy TINYINT(1) NOT NULL DEFAULT 0,
    visibility VARCHAR(255) NOT NULL DEFAULT 'default',
    priority TINYINT(1) NOT NULL DEFAULT 0,
    category VARCHAR(255) NOT NULL DEFAULT '',
    attachments TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP NULL DEFAULT NULL,
    removed_at TIMESTAMP NULL DEFAULT NULL
);

CREATE INDEX IF NOT EXISTS calendar_start_end ON xcalendar_events (calendar_id, start, end, removed_at, repeat_end);
CREATE INDEX IF NOT EXISTS uid ON xcalendar_events (uid);

CREATE TABLE IF NOT EXISTS xcalendar_events_removed (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    day TIMESTAMP NULL DEFAULT NULL,
    event_id INT UNSIGNED NOT NULL DEFAULT 0,
    removed_at TIMESTAMP NULL DEFAULT NULL,
    removed_by INT UNSIGNED NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS day_event ON xcalendar_events_removed (day, event_id);

CREATE TABLE IF NOT EXISTS xcalendar_events_custom (
    user_id INT UNSIGNED NOT NULL DEFAULT 0,
    event_id INT UNSIGNED NOT NULL DEFAULT 0,
    use_calendar_colors TINYINT(1) NOT NULL DEFAULT 1,
    bg_color VARCHAR(255) NOT NULL DEFAULT '',
    tx_color VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (user_id, event_id)
);

CREATE TABLE IF NOT EXISTS xcalendar_alarms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INT UNSIGNED NOT NULL DEFAULT 0,
    event_id INT UNSIGNED NOT NULL DEFAULT 0,
    event_end TIMESTAMP NULL DEFAULT NULL,
    alarm_type VARCHAR(255) NOT NULL DEFAULT 'popup',
    alarm_number SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    alarm_units VARCHAR(255) NOT NULL DEFAULT 'minutes',
    snooze SMALLINT NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS user_event_end ON xcalendar_alarms (user_id, event_end);

CREATE TABLE IF NOT EXISTS xcalendar_attachments_temp (
    filename VARCHAR(255) NOT NULL DEFAULT '',
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (filename, uploaded_at)
);