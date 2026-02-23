-- MySQL schema for the SyncExample project
-- Run: mysql -u <user> -p <database> < sql/mysql.sql

CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT          NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
    `email`      VARCHAR(255) NOT NULL               COMMENT 'Unique e-mail address',
    `name`       VARCHAR(100)     NULL               COMMENT 'Display name',
    `status`     VARCHAR(20)  NOT NULL DEFAULT 'active' COMMENT 'Account status',
    `created_at` DATETIME     NOT NULL               COMMENT 'Record creation timestamp',
    `updated_at` DATETIME         NULL               COMMENT 'Last update timestamp',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Application users';

CREATE TABLE IF NOT EXISTS `posts` (
    `id`         INT          NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
    `user_id`    INT          NOT NULL               COMMENT 'Author user ID',
    `title`      VARCHAR(255) NOT NULL               COMMENT 'Post title',
    `content`    TEXT             NULL               COMMENT 'Post body',
    `published`  TINYINT(1)   NOT NULL DEFAULT 0    COMMENT 'Published flag',
    `created_at` DATETIME     NOT NULL               COMMENT 'Record creation timestamp',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User published posts';

CREATE TABLE IF NOT EXISTS `comments` (
    `id`         INT          NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
    `post_id`    INT          NOT NULL               COMMENT 'Parent post ID',
    `user_id`    INT          NOT NULL               COMMENT 'Author user ID',
    `body`       TEXT         NOT NULL               COMMENT 'Comment text',
    `created_at` DATETIME     NOT NULL               COMMENT 'Record creation timestamp',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Post comments';
