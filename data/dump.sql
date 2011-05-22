CREATE SCHEMA `basiccrm` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;

-- table for users

CREATE  TABLE `basiccrm`.`users` (
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
    REFERENCES `basiccrm`.`companies` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

-- table for companies

CREATE  TABLE `basiccrm`.`companies` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(250) NULL ,
  `subscription_id` INT NULL ,
  `is_activated` TINYINT(1) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `company_subscription` (`subscription_id` ASC) ,
  CONSTRAINT `company_subscription`
    FOREIGN KEY (`subscription_id` )
    REFERENCES `basiccrm`.`subscriptions` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

-- table for subscription

CREATE  TABLE `basiccrm`.`subscriptions` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(250) NULL ,
  PRIMARY KEY (`id`) );


