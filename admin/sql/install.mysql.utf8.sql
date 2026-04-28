CREATE TABLE IF NOT EXISTS `#__mcpserver_users` (
    `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `joomla_user_id` INT(11) UNSIGNED NOT NULL,
    `token`          VARCHAR(64)      NOT NULL,
    `enabled`        TINYINT(1)       NOT NULL DEFAULT 1,
    `note`           VARCHAR(255)     NOT NULL DEFAULT '',
    `created`        DATETIME         NOT NULL,
    `last_used`      DATETIME             NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_token`          (`token`),
    UNIQUE KEY `idx_joomla_user_id` (`joomla_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
