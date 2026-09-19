CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_alarms_user_type_end_time_evid
    ON xcalendar_alarms (user_id, alarm_type, event_end, alarm_time, event_id);