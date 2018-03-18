CREATE TABLE `battle_pets`.`pets` (
  `species_id` INT NOT NULL,
  `name` VARCHAR(45) NULL,
  `quality_id` INT NULL,
  `creature_id` INT NULL,
  PRIMARY KEY (`species_id`));

ALTER TABLE `battle_pets`.`pets` 
ADD COLUMN `icon` VARCHAR(100) NULL AFTER `creature_id`;
  
CREATE TABLE `realms` (
  `slug` varchar(45) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `locale` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `battle_pets`.`auctions_daily_pet` (
  `id` INT NOT NULL,
  `species_id` INT NULL,
  `realm` VARCHAR(45) NULL,
  `buyout` INT UNSIGNED NULL,
  `bid` INT UNSIGNED NULL,
  `owner` VARCHAR(45) NULL,
  `time_left` VARCHAR(45) NULL,
  `quantity` INT NULL,
  PRIMARY KEY (`id`));

  
CREATE TABLE `battle_pets`.`market_value_pets` (
  `species_id` INT NOT NULL,
  `realm` VARCHAR(45) NOT NULL,
  `date` DATETIME NULL,
  `market_value` INT UNSIGNED NULL,
	PRIMARY KEY (`species_id`, `realm`));

	
CREATE TABLE `battle_pets`.`realms_connected` (
  `slug_parent` VARCHAR(45) NOT NULL,
  `slug_child` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`slug_parent`, `slug_child`));

CREATE TABLE `battle_pets`.`auctions_hourly_pet` (
  `id` INT NOT NULL,
  `species_id` INT NULL,
  `realm` VARCHAR(45) NULL,
  `buyout` INT UNSIGNED NULL,
  `bid` INT UNSIGNED NULL,
  `owner` VARCHAR(45) NULL,
  `time_left` VARCHAR(45) NULL,
  `quantity` INT NULL,
  PRIMARY KEY (`id`));
  
USE `battle_pets`;
CREATE  OR REPLACE VIEW `market_value_pets_historical` AS
SELECT species_id, AVG(market_value) as market_value_hist FROM market_value_pets 
WHERE DATE >= CURDATE() - INTERVAL  14 DAY 
      AND DATE  < CURDATE() + INTERVAL  1 DAY
GROUP BY species_id;

-- Staging table
CREATE TABLE `auctions_hourly_pet_stg` (
   `id` int(11) NOT NULL,
   `species_id` int(11) DEFAULT NULL,
   `realm` varchar(45) DEFAULT NULL,
   `buyout` decimal(11,0) DEFAULT NULL,
   `bid` decimal(11,0) DEFAULT NULL,
   `owner` varchar(45) DEFAULT NULL,
   `time_left` varchar(45) DEFAULT NULL,
   `quantity` int(11) DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `species_id_hourly_idx` (`species_id`),
   KEY `realm_hourly_idx` (`realm`),
   CONSTRAINT `realm_hourly_stg_fk` FOREIGN KEY (`realm`) REFERENCES `realms` (`slug`) ON DELETE NO ACTION ON UPDATE NO ACTION,
   CONSTRAINT `species_id_hourly_stg_fk` FOREIGN KEY (`species_id`) REFERENCES `pets` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8