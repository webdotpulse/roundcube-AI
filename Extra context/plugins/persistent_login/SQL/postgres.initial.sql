-- Roundcube Persistent Login Plugin
-- Schema for PostgreSQL

CREATE TABLE IF NOT EXISTS persistent_logins (
    series VARCHAR(64) NOT NULL,
    token_hash VARCHAR(128) NOT NULL,
    user_id INTEGER NOT NULL,
    user_name VARCHAR(128) NOT NULL,
    user_pass TEXT NOT NULL,
    host VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL DEFAULT '',
    user_agent VARCHAR(500) NOT NULL DEFAULT '',
    created TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    last_used TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    expires TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY (series)
);

CREATE INDEX IF NOT EXISTS idx_persistent_user_id ON persistent_logins (user_id);
CREATE INDEX IF NOT EXISTS idx_persistent_expires ON persistent_logins (expires);
