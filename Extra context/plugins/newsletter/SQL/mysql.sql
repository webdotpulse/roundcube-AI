CREATE TABLE IF NOT EXISTS `newsletter_campaigns` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `subject` VARCHAR(500) NOT NULL DEFAULT '',
    `from_email` VARCHAR(255) NOT NULL DEFAULT '',
    `from_name` VARCHAR(255) NOT NULL DEFAULT '',
    `recipients_total` INT UNSIGNED NOT NULL DEFAULT 0,
    `recipients_sent` INT UNSIGNED NOT NULL DEFAULT 0,
    `recipients_failed` INT UNSIGNED NOT NULL DEFAULT 0,
    `status` VARCHAR(32) NOT NULL DEFAULT 'draft',
    `body_html` LONGTEXT NULL,
    `body_text` LONGTEXT NULL,
    `spam_score` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    `started_at` DATETIME NULL,
    `finished_at` DATETIME NULL,
    `log_data` MEDIUMTEXT NULL,
    PRIMARY KEY (`id`),
    INDEX `user_status` (`user_id`, `status`),
    INDEX `created_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `newsletter_suppressions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `email` VARCHAR(255) NOT NULL,
    `reason` VARCHAR(64) NOT NULL DEFAULT 'user_unsubscribe',
    `campaign_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_email` (`user_id`, `email`),
    INDEX `email_idx` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
