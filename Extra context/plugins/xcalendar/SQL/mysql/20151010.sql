CREATE TABLE IF NOT EXISTS xcalendar_calendars (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL DEFAULT 0,
    type TINYINT(1) NOT NULL DEFAULT 1,
    url VARCHAR(180) NOT NULL DEFAULT '',
    name VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT,
    timezone VARCHAR(255) NOT NULL DEFAULT '',
    bg_color VARCHAR(255) NOT NULL DEFAULT '',
    tx_color VARCHAR(255) NOT NULL DEFAULT '',
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    default_event_visibility ENUM('public', 'private') NOT NULL DEFAULT 'public',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP NULL DEFAULT NULL,
    removed_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX user_id(user_id, enabled, removed_at),
    INDEX user_id_2(user_id, type, url)
) ENGINE = InnoDB DEFAULT CHARSET utf8 COLLATE utf8_unicode_ci;

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
    PRIMARY KEY (email, calendar_id),
    INDEX add_code(add_code)
) ENGINE = InnoDB DEFAULT CHARSET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS xcalendar_events (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
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
    visibility ENUM('default', 'public', 'private') NOT NULL DEFAULT 'default',
    priority TINYINT(1) NOT NULL DEFAULT 0,
    category VARCHAR(255) NOT NULL DEFAULT '',
    attachments TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP NULL DEFAULT NULL,
    removed_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX calendar_id(calendar_id, start, end, removed_at, repeat_end),
    INDEX uid(uid)
) ENGINE = InnoDB DEFAULT CHARSET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS xcalendar_events_removed (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    day TIMESTAMP NULL DEFAULT NULL,
    event_id INT UNSIGNED NOT NULL DEFAULT 0,
    removed_at TIMESTAMP NULL DEFAULT NULL,
    removed_by INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    INDEX `day`(day, event_id)
) ENGINE = InnoDB DEFAULT CHARSET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS xcalendar_events_custom (
    user_id INT UNSIGNED NOT NULL DEFAULT 0,
    event_id INT UNSIGNED NOT NULL DEFAULT 0,
    use_calendar_colors TINYINT(1) NOT NULL DEFAULT 1,
    bg_color VARCHAR(255) NOT NULL DEFAULT '',
    tx_color VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (user_id, event_id)
) ENGINE = InnoDB DEFAULT CHARSET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS xcalendar_alarms (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL DEFAULT 0,
    event_id INT UNSIGNED NOT NULL DEFAULT 0,
    event_end TIMESTAMP NULL DEFAULT NULL,
    alarm_type ENUM('popup', 'email') NOT NULL DEFAULT 'popup',
    alarm_number SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    alarm_units ENUM('minutes', 'hours', 'days', 'weeks') NOT NULL DEFAULT 'minutes',
    snooze SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    INDEX user_id(user_id, event_end)
) ENGINE = InnoDB DEFAULT CHARSET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS xcalendar_attachments_temp (
    filename VARCHAR(255) NOT NULL DEFAULT '',
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (filename, uploaded_at)
) ENGINE = InnoDB DEFAULT CHARSET utf8 COLLATE utf8_unicode_ci;