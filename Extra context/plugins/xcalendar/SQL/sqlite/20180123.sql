CREATE TABLE IF NOT EXISTS xcalendar_synced (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INT UNSIGNED NOT NULL DEFAULT 0,
    calendar_id INT UNSIGNED NOT NULL DEFAULT 0,
    name VARCHAR(255) NOT NULL DEFAULT '',
    username VARCHAR(255) NOT NULL DEFAULT '',
    url VARCHAR(255) NOT NULL DEFAULT '',
    password VARCHAR(255) NOT NULL DEFAULT '',
    read_only TINYINT(1) NOT NULL DEFAULT 0,
    connected_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS username ON xcalendar_synced (username);
CREATE INDEX IF NOT EXISTS url ON xcalendar_synced (url);
CREATE INDEX IF NOT EXISTS user_calendar ON xcalendar_synced (user_id, calendar_id);

CREATE TABLE IF NOT EXISTS tmp_xcalendar_events (
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
    vevent BLOB DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP NULL DEFAULT NULL,
    removed_at TIMESTAMP NULL DEFAULT NULL
);

INSERT INTO tmp_xcalendar_events (id, user_id, calendar_id, uid, title, location, description, url, start, end, all_day,
        repeat_rule, repeat_end, use_calendar_colors, bg_color, tx_color, busy, visibility, priority, category,
        attachments, created_at, modified_at, removed_at)
    SELECT id, user_id, calendar_id, uid, title, location, description, url, start, end, all_day, repeat_rule,
        repeat_end, use_calendar_colors, bg_color, tx_color, busy, visibility, priority, category, attachments,
        created_at, modified_at, removed_at FROM xcalendar_events;

DROP TABLE xcalendar_events;

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
    vevent BLOB DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP NULL DEFAULT NULL,
    removed_at TIMESTAMP NULL DEFAULT NULL
);

INSERT INTO xcalendar_events (id, user_id, calendar_id, uid, title, location, description, url, start, end, all_day,
        repeat_rule, repeat_end, use_calendar_colors, bg_color, tx_color, busy, visibility, priority, category,
        attachments, created_at, modified_at, removed_at)
    SELECT id, user_id, calendar_id, uid, title, location, description, url, start, end, all_day, repeat_rule,
        repeat_end, use_calendar_colors, bg_color, tx_color, busy, visibility, priority, category, attachments,
        created_at, modified_at, removed_at FROM tmp_xcalendar_events;

CREATE INDEX IF NOT EXISTS calendar_start_end ON xcalendar_events (calendar_id, start, end, removed_at, repeat_end);
CREATE INDEX IF NOT EXISTS uid ON xcalendar_events (uid);

DROP TABLE tmp_xcalendar_events;