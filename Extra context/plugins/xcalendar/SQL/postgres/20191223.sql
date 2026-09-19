ALTER TABLE xcalendar_attendees ADD user_id INT NOT NULL DEFAULT 0;
CREATE INDEX user_id_status ON xcalendar_attendees (user_id, status);

