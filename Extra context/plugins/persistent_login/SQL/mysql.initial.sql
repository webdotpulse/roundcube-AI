-- Roundcube Persistent Login Plugin
-- Schema for MySQL / MariaDB

CREATE TABLE IF NOT EXISTS `persistent_logins` (
    `series` VARCHAR(64) NOT NULL,
    `token_hash` VARCHAR(128) NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `user_name` VARCHAR(128) NOT NULL,
    `user_pass` TEXT NOT NULL,
    `host` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL DEFAULT '',
    `user_agent` VARCHAR(500) NOT NULL DEFAULT '',
    `created` DATETIME NOT NULL,
    `last_used` DATETIME NOT NULL,
    `expires` DATETIME NOT NULL,
    PRIMARY KEY (`series`),
    INDEX `idx_persistent_user_id` (`user_id`),
    INDEX `idx_persistent_expires` (`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
