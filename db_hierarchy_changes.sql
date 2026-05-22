-- Database schema for new Distributor / Dealer / User hierarchy

ALTER TABLE `tbl_users`
  ADD COLUMN `dealer_id` INT UNSIGNED DEFAULT NULL,
  ADD COLUMN `distributor_id` INT UNSIGNED DEFAULT NULL,
  ADD COLUMN `user_role` ENUM('user','dealer','distributor','admin') NOT NULL DEFAULT 'user',
  ADD INDEX (`dealer_id`),
  ADD INDEX (`distributor_id`);

CREATE TABLE IF NOT EXISTS `tbl_distributors` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `wallet` DECIMAL(16,2) NOT NULL DEFAULT 0.00,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `login_session` VARCHAR(255) DEFAULT NULL,
  `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.50,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_dealers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `distributor_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `wallet` DECIMAL(16,2) NOT NULL DEFAULT 0.00,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `login_session` VARCHAR(255) DEFAULT NULL,
  `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 2.00,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`distributor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_commission_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_user_id` INT UNSIGNED DEFAULT NULL,
  `dealer_id` INT UNSIGNED DEFAULT NULL,
  `distributor_id` INT UNSIGNED DEFAULT NULL,
  `commission_type` ENUM('dealer','distributor') NOT NULL,
  `amount` DECIMAL(16,2) NOT NULL DEFAULT 0.00,
  `rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `game_id` INT UNSIGNED DEFAULT NULL,
  `game_type` VARCHAR(100) DEFAULT NULL,
  `bet_id` INT UNSIGNED DEFAULT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`dealer_id`),
  INDEX (`distributor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_wallet_transfers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `from_type` VARCHAR(50) NOT NULL,
  `from_id` INT UNSIGNED NOT NULL,
  `to_type` VARCHAR(50) NOT NULL,
  `to_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(16,2) NOT NULL DEFAULT 0.00,
  `transfer_type` VARCHAR(100) NOT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`from_type`, `from_id`),
  INDEX (`to_type`, `to_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
