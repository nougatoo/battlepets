-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema battlepets
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema battlepets
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `battlepets` DEFAULT CHARACTER SET utf8 ;
USE `battlepets` ;

-- -----------------------------------------------------
-- Table `battlepets`.`realms`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `battlepets`.`realms` (
  `slug` VARCHAR(45) NOT NULL,
  `name` VARCHAR(50) NULL DEFAULT NULL,
  `locale` VARCHAR(10) NULL DEFAULT NULL,
  PRIMARY KEY (`slug`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `battlepets`.`pets`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `battlepets`.`pets` (
  `species_id` INT(11) NOT NULL,
  `name` VARCHAR(45) NULL DEFAULT NULL,
  `quality_id` INT(11) NULL DEFAULT NULL,
  `creature_id` INT(11) NULL DEFAULT NULL,
  `icon` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`species_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `battlepets`.`auctions_daily_pet`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `battlepets`.`auctions_daily_pet` (
  `id` INT(11) NOT NULL,
  `species_id` INT(11) NULL DEFAULT NULL,
  `realm` VARCHAR(45) NULL DEFAULT NULL,
  `buyout` DECIMAL(11,0) NULL DEFAULT NULL,
  `bid` DECIMAL(11,0) NULL DEFAULT NULL,
  `owner` VARCHAR(45) NULL DEFAULT NULL,
  `time_left` VARCHAR(45) NULL DEFAULT NULL,
  `quantity` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `realm_fk`
    FOREIGN KEY (`realm`)
    REFERENCES `battlepets`.`realms` (`slug`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `species_id_fk`
    FOREIGN KEY (`species_id`)
    REFERENCES `battlepets`.`pets` (`species_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE INDEX `species_id_fk_idx` ON `battlepets`.`auctions_daily_pet` (`species_id` ASC);

CREATE INDEX `realm_fk_idx` ON `battlepets`.`auctions_daily_pet` (`realm` ASC);

CREATE INDEX `realm_speed_index` USING BTREE ON `battlepets`.`auctions_daily_pet` (`realm` ASC);


-- -----------------------------------------------------
-- Table `battlepets`.`auctions_hourly_pet`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `battlepets`.`auctions_hourly_pet` (
  `id` INT(11) NOT NULL,
  `species_id` INT(11) NULL DEFAULT NULL,
  `realm` VARCHAR(45) NULL DEFAULT NULL,
  `buyout` DECIMAL(11,0) NULL DEFAULT NULL,
  `bid` DECIMAL(11,0) NULL DEFAULT NULL,
  `owner` VARCHAR(45) NULL DEFAULT NULL,
  `time_left` VARCHAR(45) NULL DEFAULT NULL,
  `quantity` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `realm_hourly_fk`
    FOREIGN KEY (`realm`)
    REFERENCES `battlepets`.`realms` (`slug`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `species_id_hourly_fk`
    FOREIGN KEY (`species_id`)
    REFERENCES `battlepets`.`pets` (`species_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE INDEX `species_id_hourly_idx` ON `battlepets`.`auctions_hourly_pet` (`species_id` ASC);

CREATE INDEX `realm_hourly_idx` ON `battlepets`.`auctions_hourly_pet` (`realm` ASC);


-- -----------------------------------------------------
-- Table `battlepets`.`auctions_hourly_pet_stg`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `battlepets`.`auctions_hourly_pet_stg` (
  `id` INT(11) NOT NULL,
  `species_id` INT(11) NULL DEFAULT NULL,
  `realm` VARCHAR(45) NULL DEFAULT NULL,
  `buyout` DECIMAL(11,0) NULL DEFAULT NULL,
  `bid` DECIMAL(11,0) NULL DEFAULT NULL,
  `owner` VARCHAR(45) NULL DEFAULT NULL,
  `time_left` VARCHAR(45) NULL DEFAULT NULL,
  `quantity` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `realm_hourly_stg_fk`
    FOREIGN KEY (`realm`)
    REFERENCES `battlepets`.`realms` (`slug`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `species_id_hourly_stg_fk`
    FOREIGN KEY (`species_id`)
    REFERENCES `battlepets`.`pets` (`species_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE INDEX `species_id_hourly_idx` ON `battlepets`.`auctions_hourly_pet_stg` (`species_id` ASC);

CREATE INDEX `realm_hourly_idx` ON `battlepets`.`auctions_hourly_pet_stg` (`realm` ASC);


-- -----------------------------------------------------
-- Table `battlepets`.`market_value_pets`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `battlepets`.`market_value_pets` (
  `species_id` INT(11) NOT NULL,
  `realm` VARCHAR(45) NOT NULL,
  `date` DATETIME NOT NULL,
  `market_value` DECIMAL(11,0) NULL DEFAULT NULL,
  PRIMARY KEY (`species_id`, `realm`, `date`),
  CONSTRAINT `ream_mv_fk`
    FOREIGN KEY (`realm`)
    REFERENCES `battlepets`.`realms` (`slug`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `species_id_mv_fk`
    FOREIGN KEY (`species_id`)
    REFERENCES `battlepets`.`pets` (`species_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE INDEX `ream_mv_fk_idx` ON `battlepets`.`market_value_pets` (`realm` ASC);


-- -----------------------------------------------------
-- Table `battlepets`.`realms_connected`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `battlepets`.`realms_connected` (
  `slug_parent` VARCHAR(45) NOT NULL,
  `slug_child` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`slug_parent`, `slug_child`),
  CONSTRAINT `child_connected_fk`
    FOREIGN KEY (`slug_child`)
    REFERENCES `battlepets`.`realms` (`slug`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `parent_connected_fk`
    FOREIGN KEY (`slug_parent`)
    REFERENCES `battlepets`.`realms` (`slug`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE INDEX `child_connected_fk_idx` ON `battlepets`.`realms_connected` (`slug_child` ASC);

USE `battlepets` ;

-- -----------------------------------------------------
-- Placeholder table for view `battlepets`.`market_value_pets_historical`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `battlepets`.`market_value_pets_historical` (`species_id` INT, `market_value_hist` INT);

-- -----------------------------------------------------
-- View `battlepets`.`market_value_pets_historical`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `battlepets`.`market_value_pets_historical`;
USE `battlepets`;
CREATE  OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `battlepets`.`market_value_pets_historical` AS select `battlepets`.`market_value_pets`.`species_id` AS `species_id`,avg(`battlepets`.`market_value_pets`.`market_value`) AS `market_value_hist` from `battlepets`.`market_value_pets` where ((`battlepets`.`market_value_pets`.`date` >= (curdate() - interval 14 day)) and (`battlepets`.`market_value_pets`.`date` < (curdate() + interval 1 day))) group by `battlepets`.`market_value_pets`.`species_id`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
