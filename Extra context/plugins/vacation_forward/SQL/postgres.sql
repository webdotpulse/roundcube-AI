CREATE TABLE IF NOT EXISTS vacation_forward_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL DEFAULT 0,
    sender VARCHAR(255) NOT NULL DEFAULT '',
    recipient VARCHAR(255) NOT NULL DEFAULT '',
    subject VARCHAR(500) NOT NULL DEFAULT '',
    action_type VARCHAR(32) NOT NULL DEFAULT 'auto_reply',
    template_used VARCHAR(128) NOT NULL DEFAULT 'default',
    status VARCHAR(32) NOT NULL DEFAULT 'sent',
    message_id VARCHAR(255) NOT NULL DEFAULT '',
    details TEXT,
    created_at TIMESTAMP NOT NULL
);

CREATE INDEX IF NOT EXISTS vacation_forward_user_sender ON vacation_forward_logs (user_id, sender, action_type);
CREATE INDEX IF NOT EXISTS vacation_forward_user_created ON vacation_forward_logs (user_id, created_at);
