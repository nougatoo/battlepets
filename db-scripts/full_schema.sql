-- 1
CREATE TABLE `realms` (
  `slug` varchar(45) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `locale` varchar(10) DEFAULT NULL,
  `id` int(11) DEFAULT NULL,
  PRIMARY KEY (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2
CREATE TABLE `realms_connected` (
  `slug_parent` varchar(45) NOT NULL,
  `slug_child` varchar(45) NOT NULL,
  `id_parent` varchar(45) DEFAULT NULL,
  `id_child` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`slug_parent`,`slug_child`),
  KEY `child_connected_fk_idx` (`slug_child`),
  CONSTRAINT `child_connected_fk` FOREIGN KEY (`slug_child`) REFERENCES `realms` (`slug`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `parent_connected_fk` FOREIGN KEY (`slug_parent`) REFERENCES `realms` (`slug`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 3
CREATE TABLE `pets` (
  `species_id` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `quality_id` int(11) DEFAULT NULL,
  `creature_id` int(11) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`species_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `auctions_daily_pet` (
  `id` int(11) NOT NULL,
  `species_id` int(11) DEFAULT NULL,
  `realm` varchar(45) DEFAULT NULL,
  `buyout` decimal(11,0) DEFAULT NULL,
  `bid` decimal(11,0) DEFAULT NULL,
  `owner` varchar(45) DEFAULT NULL,
  `time_left` varchar(45) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `species_id_fk_idx` (`species_id`),
  KEY `realm_speed_index` (`realm`) USING BTREE,
  CONSTRAINT `auctions_daily_relam_fk` FOREIGN KEY (`realm`) REFERENCES `realms` (`slug`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `species_id_fk` FOREIGN KEY (`species_id`) REFERENCES `pets` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `auctions_hourly_pet` (
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
  KEY `buyout_idx` (`buyout`),
  KEY `auctions_hourly_realm_fk_idx` (`realm`),
  CONSTRAINT `auctions_hourly_realm_fk` FOREIGN KEY (`realm`) REFERENCES `realms` (`slug`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `species_id_hourly_fk` FOREIGN KEY (`species_id`) REFERENCES `pets` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


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
  KEY `auctions_hourly_pet_stg_realm_fk_idx` (`realm`),
  CONSTRAINT `auctions_hourly_pet_stg_realm_fk` FOREIGN KEY (`realm`) REFERENCES `realms` (`slug`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `species_id_hourly_stg_fk` FOREIGN KEY (`species_id`) REFERENCES `pets` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `market_value_pets` (
  `species_id` int(11) NOT NULL,
  `realm` varchar(45) NOT NULL,
  `date` datetime NOT NULL,
  `market_value` decimal(11,0) DEFAULT NULL,
  PRIMARY KEY (`species_id`,`realm`,`date`),
  KEY `market_value_pets_realm_fk_idx` (`realm`),
  CONSTRAINT `species_id_mv_fk` FOREIGN KEY (`species_id`) REFERENCES `pets` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `market_value_pets_hist` (
  `species_id` int(11) NOT NULL,
  `market_value_hist` decimal(30,10) DEFAULT NULL,
  PRIMARY KEY (`species_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `market_value_pets_hist_median` (
  `species_id` int(11) NOT NULL,
  `market_value_hist_median` decimal(30,10) DEFAULT NULL,
  PRIMARY KEY (`species_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE ALGORITHM=UNDEFINED DEFINER=`nougatoo`@`%` SQL SECURITY DEFINER VIEW `market_value_pets_historical` AS select `market_value_pets`.`species_id` AS `species_id`,avg(`market_value_pets`.`market_value`) AS `market_value_hist` from `market_value_pets` group by `market_value_pets`.`species_id`;

