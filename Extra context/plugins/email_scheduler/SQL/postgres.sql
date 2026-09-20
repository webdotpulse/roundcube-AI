CREATE TABLE IF NOT EXISTS email_scheduler_queue (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL DEFAULT 0,
    message_id VARCHAR(255) NOT NULL DEFAULT '',
    subject VARCHAR(500) NOT NULL DEFAULT '',
    recipients TEXT NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'delayed',
    headers TEXT,
    body TEXT,
    parameters TEXT,
    send_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP,
    error TEXT
);

CREATE INDEX IF NOT EXISTS email_scheduler_user_status ON email_scheduler_queue (user_id, status);
CREATE INDEX IF NOT EXISTS email_scheduler_status_send ON email_scheduler_queue (status, send_at);
