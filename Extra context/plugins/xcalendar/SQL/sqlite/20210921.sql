CREATE INDEX vevent_uid_index ON xcalendar_events (vevent_uid);

CREATE TABLE xcalendar_changes (
    id integer primary key asc NOT NULL,
    uri text,
    sync_token integer NOT NULL,
    calendar_id integer NOT NULL,
    operation integer NOT NULL
);

CREATE INDEX xcalendar_changes_calendar_id_sync_token ON xcalendar_changes (calendar_id, sync_token);

CREATE TABLE xcalendar_scheduling_objects (
    id integer primary key asc NOT NULL,
    principal_uri text NOT NULL,
    calendar_data blob,
    uri text NOT NULL,
    modified_at integer,
    etag text NOT NULL,
    size integer NOT NULL
);