ALTER TABLE xcalendar_attendees ADD user_id INT UNSIGNED NOT NULL DEFAULT 0 AFTER calendar_id;
ALTER TABLE xcalendar_attendees ADD INDEX user_id_status(user_id, status);
