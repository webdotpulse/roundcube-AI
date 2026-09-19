CREATE INDEX vevent_uid_ix ON xcalendar_events (vevent_uid);

CREATE SEQUENCE IF NOT EXISTS xcalendar_changes_seq START WITH 1 INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;
CREATE TABLE xcalendar_changes (
    id INTEGER NOT NULL DEFAULT nextval('xcalendar_changes_seq'::text),
    uri VARCHAR(200) NOT NULL,
    sync_token INTEGER NOT NULL,
    calendar_id INTEGER NOT NULL,
    operation SMALLINT NOT NULL DEFAULT 0
);

CREATE INDEX xcalendar_changes_calendar_id_sync_token_ix ON xcalendar_changes USING btree (calendar_id, sync_token);
ALTER TABLE ONLY xcalendar_changes ADD CONSTRAINT xcalendar_changes_pkey PRIMARY KEY (id);
ALTER TABLE xcalendar_changes ADD CONSTRAINT calendar_id_fk_xcalendar_calendars_changes
  FOREIGN KEY (calendar_id) REFERENCES xcalendar_calendars(id) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE SEQUENCE IF NOT EXISTS xcalendar_scheduling_objects_seq START WITH 1 INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;
CREATE TABLE xcalendar_scheduling_objects (
    id INTEGER NOT NULL DEFAULT nextval('xcalendar_scheduling_objects_seq'::text),
    principal_uri VARCHAR(255),
    calendar_data BYTEA,
    uri VARCHAR(200),
    modified_at INTEGER,
    etag VARCHAR(32),
    size INTEGER NOT NULL
);






