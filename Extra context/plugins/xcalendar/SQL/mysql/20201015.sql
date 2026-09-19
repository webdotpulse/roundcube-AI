ALTER TABLE xcalendar_events ADD timezone_start VARCHAR(255) NOT NULL DEFAULT '' AFTER start;
ALTER TABLE xcalendar_events ADD timezone_end VARCHAR(255) NOT NULL DEFAULT '' AFTER timezone_start;
