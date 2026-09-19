ALTER TABLE xcalendar_events ADD INDEX vevent_uid_index (vevent_uid);

CREATE TABLE xcalendar_changes (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    sync_token INT(11) UNSIGNED NOT NULL,
    calendar_id INT(11) UNSIGNED NOT NULL,
    operation TINYINT(1) NOT NULL,
    INDEX xcalendar_changes_calendar_id_sync_token(calendar_id, sync_token),
    CONSTRAINT calendar_id_fk_xcalendar_changes FOREIGN KEY (calendar_id) REFERENCES xcalendar_calendars (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE xcalendar_scheduling_objects (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    principal_uri VARBINARY(255),
    calendar_data MEDIUMBLOB,
    uri VARBINARY(200),
    modified_at INT(11) UNSIGNED,
    etag VARBINARY(32),
    size INT(11) UNSIGNED NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET utf8 COLLATE utf8_unicode_ci;