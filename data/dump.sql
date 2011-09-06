-- CREATE SCHEMA `basiccrm` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;

-- table for subscription

CREATE  TABLE `subscriptions` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(250) NULL ,
  PRIMARY KEY (`id`) );

-- table for companies

CREATE  TABLE `companies` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(250) NULL ,
  `subscription_id` INT NULL ,
  `is_activated` TINYINT(1) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `company_subscription` (`subscription_id` ASC) ,
  CONSTRAINT `company_subscription`
    FOREIGN KEY (`subscription_id` )
    REFERENCES `subscriptions` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE);

-- table for users

CREATE  TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(250) NULL DEFAULT '' ,
  `email` VARCHAR(250) NULL ,
  `password_hash` VARCHAR(40) NULL ,
  `is_admin` TINYINT(1) NULL ,
  `company_id` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `user_company` (`company_id` ASC) ,
  CONSTRAINT `user_company`
    FOREIGN KEY (`company_id` )
    REFERENCES `companies` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE);

-- adding sample subscriptions manually

INSERT INTO `subscriptions` (`id`, `name`) VALUES (1, 'Standard');
INSERT INTO `subscriptions` (`id`, `name`) VALUES (2, 'Pro');

-- table for sessions

CREATE  TABLE `sessions` (
  `id` VARCHAR(32) NOT NULL ,
  `modified` TIMESTAMP NULL ,
  `user_id` INT(11) NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  INDEX `session_user` (`user_id` ASC) ,
  CONSTRAINT `session_user`
    FOREIGN KEY (`user_id` )
    REFERENCES `users` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE);

-- adding subscriptions basic functionality

ALTER TABLE `subscriptions`
  ADD COLUMN `users_limit` INT(11) NULL  AFTER `name` ,
  ADD COLUMN `clients_limit` INT(11) NULL  AFTER `users_limit` ;

UPDATE `subscriptions` SET `users_limit`=3, `clients_limit`=1000 WHERE `id`='1';
UPDATE `subscriptions` SET `users_limit`=30, `clients_limit`=30000 WHERE `id`='2';