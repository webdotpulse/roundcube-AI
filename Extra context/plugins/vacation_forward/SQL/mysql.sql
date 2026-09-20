CREATE TABLE IF NOT EXISTS `vacation_forward_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `sender` VARCHAR(255) NOT NULL DEFAULT '',
    `recipient` VARCHAR(255) NOT NULL DEFAULT '',
    `subject` VARCHAR(500) NOT NULL DEFAULT '',
    `action_type` VARCHAR(32) NOT NULL DEFAULT 'auto_reply',
    `template_used` VARCHAR(128) NOT NULL DEFAULT 'default',
    `status` VARCHAR(32) NOT NULL DEFAULT 'sent',
    `message_id` VARCHAR(255) NOT NULL DEFAULT '',
    `details` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `user_sender_action` (`user_id`, `sender`, `action_type`),
    INDEX `user_created` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
