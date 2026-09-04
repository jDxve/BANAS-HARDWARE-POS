CREATE DATABASE IF NOT EXISTS `banas_hardware_pos`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `banas_hardware_pos`;

CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    VARCHAR(11)  NOT NULL COMMENT 'Login ID, format 000-000-000',
    `name`       VARCHAR(100) NOT NULL,
    `pin_hash`   VARCHAR(255) NOT NULL COMMENT 'password_hash() output, never plaintext',
    `role`       ENUM('admin', 'clerk') NOT NULL DEFAULT 'clerk',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_user_id` (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `inventory` (
    `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_name`        VARCHAR(150)   NOT NULL,
    `brand`                VARCHAR(100)   NOT NULL,
    `description`          VARCHAR(255)   NOT NULL DEFAULT '',
    `category`             ENUM('Nails', 'Cements', 'Roofing Sheets', 'Paint', 'Plywood', 'Steel Bars', 'Other')
                                NOT NULL DEFAULT 'Other',
    `product_price`        DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `stocks`                DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `low_stock_threshold`  DECIMAL(10, 2) NOT NULL DEFAULT 10.00,
    `date`                  DATE NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_inventory_category` (`category`),
    KEY `idx_inventory_product_name` (`product_name`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `sales` (
    `id`            INT UNSIGNED NOT NULL,
    `product_name`  VARCHAR(150)   NOT NULL,
    `brand`         VARCHAR(100)   NOT NULL,
    `description`   VARCHAR(255)   NOT NULL DEFAULT '',
    `stocks`        DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `product_sold`  DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `date`          DATE NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_sales_inventory` FOREIGN KEY (`id`) REFERENCES `inventory` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `month_weeklysales` (
    `id`                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `week`                     TINYINT UNSIGNED NOT NULL,
    `stocks`                   DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `product_sold`             DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `product_sale_percentage`  DECIMAL(5, 2)  NOT NULL DEFAULT 0.00,
    `date_column`              DATE NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_month_weeklysales_week` (`week`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `inventory_history` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_name`  VARCHAR(150)   NOT NULL,
    `brand`         VARCHAR(100)   NOT NULL,
    `description`   VARCHAR(255)   NOT NULL DEFAULT '',
    `product_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `stocks`        DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `date`          DATE NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `transactions_history` (
    `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `transaction_id`     VARCHAR(20)    NOT NULL,
    `total_amount`       DECIMAL(10, 2) NOT NULL,
    `amount_received`    DECIMAL(10, 2) NOT NULL,
    `change_amount`      DECIMAL(10, 2) NOT NULL,
    `status`             ENUM('completed', 'voided') NOT NULL DEFAULT 'completed',
    `voided_by`          VARCHAR(11) NULL COMMENT 'users.user_id of the admin who voided this sale',
    `voided_at`          TIMESTAMP NULL DEFAULT NULL,
    `transaction_date`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_transactions_history_transaction_id` (`transaction_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `transaction_items` (
    `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `transaction_id`       INT UNSIGNED NOT NULL,
    `inventory_id`         INT UNSIGNED NULL,
    `product_name`         VARCHAR(150)   NOT NULL,
    `brand`                VARCHAR(100)   NOT NULL,
    `description`          VARCHAR(255)   NOT NULL DEFAULT '',
    `quantity`             DECIMAL(10, 2) NOT NULL,
    `unit_price`           DECIMAL(10, 2) NOT NULL,
    `line_total`           DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_transaction_items_transaction` (`transaction_id`),
    CONSTRAINT `fk_transaction_items_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions_history` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_transaction_items_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
