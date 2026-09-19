CREATE TABLE xcalendar_attendees (
    event_id INTEGER NOT NULL DEFAULT 0,
    calendar_id INTEGER NOT NULL DEFAULT 0,
    email VARCHAR(255) NOT NULL DEFAULT '',
    name VARCHAR(255) NOT NULL DEFAULT '',
    code VARCHAR(40) NOT NULL DEFAULT '',
    organizer SMALLINT NOT NULL DEFAULT 0,
    role SMALLINT NOT NULL DEFAULT 0,
    hidden SMALLINT NOT NULL DEFAULT 0,
    can_see_attendees SMALLINT NOT NULL DEFAULT 1,
    notify SMALLINT NOT NULL DEFAULT 1,
    status SMALLINT NOT NULL DEFAULT 0,
    guests INTEGER NOT NULL DEFAULT 0,
    comment VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (event_id, email)
);

CREATE INDEX attendee_code ON xcalendar_attendees (code);

ALTER TABLE xcalendar_events ADD has_attendees SMALLINT DEFAULT 0;
