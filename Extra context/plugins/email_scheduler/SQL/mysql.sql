CREATE TABLE IF NOT EXISTS `email_scheduler_queue` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `message_id` VARCHAR(255) NOT NULL DEFAULT '',
    `subject` VARCHAR(500) NOT NULL DEFAULT '',
    `recipients` TEXT NOT NULL,
    `status` VARCHAR(32) NOT NULL DEFAULT 'delayed',
    `headers` MEDIUMTEXT NULL,
    `body` LONGTEXT NULL,
    `parameters` MEDIUMTEXT NULL,
    `send_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL,
    `sent_at` DATETIME NULL,
    `error` TEXT NULL,
    PRIMARY KEY (`id`),
    INDEX `user_status` (`user_id`, `status`),
    INDEX `status_send_at` (`status`, `send_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
