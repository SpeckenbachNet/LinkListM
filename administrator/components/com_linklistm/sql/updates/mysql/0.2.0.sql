-- 0.2.0: multiple categories per menu item, new front-end layout, bug fixes.
-- Check-out columns were used by the backend but missing in the 0.1 install script.
ALTER TABLE `#__linklistm_links` ADD COLUMN `checked_out` INT UNSIGNED NULL /** CAN FAIL **/;
ALTER TABLE `#__linklistm_links` ADD COLUMN `checked_out_time` DATETIME NULL /** CAN FAIL **/;
