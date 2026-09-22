-- Roundcube Persistent Login Plugin
-- Schema for SQLite

CREATE TABLE IF NOT EXISTS persistent_logins (
    series VARCHAR(64) PRIMARY KEY,
    token_hash VARCHAR(128) NOT NULL,
    user_id INTEGER NOT NULL,
    user_name VARCHAR(128) NOT NULL,
    user_pass TEXT NOT NULL,
    host VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL DEFAULT '',
    user_agent VARCHAR(500) NOT NULL DEFAULT '',
    created DATETIME NOT NULL,
    last_used DATETIME NOT NULL,
    expires DATETIME NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_persistent_user_id ON persistent_logins (user_id);
CREATE INDEX IF NOT EXISTS idx_persistent_expires ON persistent_logins (expires);
