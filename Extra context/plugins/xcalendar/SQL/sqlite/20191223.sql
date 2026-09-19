ALTER TABLE xcalendar_attendees ADD user_id INT UNSIGNED NOT NULL DEFAULT 0;
CREATE INDEX IF NOT EXISTS user_id_status ON xcalendar_attendees (user_id, status);
