-- RBAC: Distributor, Dealer, Admin roles (run once; app also auto-migrates via Manage Roles page)
-- Backup database before running.

ALTER TABLE `tbl_roles`
  ADD COLUMN IF NOT EXISTS `slug` VARCHAR(50) NULL AFTER `name`,
  ADD COLUMN IF NOT EXISTS `panel` ENUM('admin','distributor','dealer') NOT NULL DEFAULT 'admin' AFTER `slug`,
  ADD COLUMN IF NOT EXISTS `dashboard_url` VARCHAR(255) NULL AFTER `panel`,
  ADD COLUMN IF NOT EXISTS `is_system` TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE `tbl_task_manager`
  ADD COLUMN IF NOT EXISTS `panel` ENUM('admin','distributor','dealer') NOT NULL DEFAULT 'admin' AFTER `url`,
  ADD COLUMN IF NOT EXISTS `route_key` VARCHAR(255) NULL AFTER `panel`;

ALTER TABLE `tbl_admin` ADD COLUMN IF NOT EXISTS `role_id` INT UNSIGNED NULL, ADD INDEX IF NOT EXISTS (`role_id`);
ALTER TABLE `tbl_distributors` ADD COLUMN IF NOT EXISTS `role_id` INT UNSIGNED NULL, ADD INDEX IF NOT EXISTS (`role_id`);
ALTER TABLE `tbl_dealers` ADD COLUMN IF NOT EXISTS `role_id` INT UNSIGNED NULL, ADD INDEX IF NOT EXISTS (`role_id`);
