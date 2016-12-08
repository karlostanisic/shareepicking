CREATE DATABASE shareepicking COLLATE utf8_unicode_ci;

CREATE TABLE `shareepicking`.`users` ( 
	`userID` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT , 
	`userName` VARCHAR(255) NOT NULL , 
	`name` VARCHAR(255) NULL , 
	`surname` VARCHAR(255) NULL , 
	`birthDate` DATETIME NULL , 
	`city` VARCHAR(255) NULL , 
	`signupDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
	`password` VARCHAR(255) NOT NULL ,
	`lastMassagesCheck` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,	
	`lastFriendsCheck` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,	
	`lastNotificationsCheck` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,	
	PRIMARY KEY (`userID`), 
	INDEX `userName` (`userName`), 
	INDEX `realName` (`name`), 
	INDEX `surname` (`surname`), 
	INDEX `password` (`password`)
) 
ENGINE = InnoDB;

CREATE TABLE `shareepicking`.`messages` ( 
	`messageID` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT , 
	`senderID` INT(12) UNSIGNED NOT NULL , 
	`receiverID` INT(12) UNSIGNED NOT NULL , 
	`subject` TEXT NULL , 
	`text` TEXT NULL , 
	`messageDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
	PRIMARY KEY (`messageID`), 
	INDEX `senderID` (`senderID`), 
	INDEX `receiverID` (`receiverID`), 
	INDEX `messageDate` (messageDate)
) 
ENGINE = InnoDB;

CREATE TABLE `shareepicking`.`friends` ( 
	`friendsID` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT , 
	`user1ID` INT(12) UNSIGNED NOT NULL , 
	`user2ID` INT(12) UNSIGNED NOT NULL , 
	`friendsDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
	PRIMARY KEY (`friendsID`), 
	INDEX `user1ID` (`user1ID`), 
	INDEX `user2ID` (`user2ID`)
) 
ENGINE = InnoDB;

CREATE TABLE `shareepicking`.`friend_requests` ( 
	`friendRequestID` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT , 
	`senderID` INT(12) UNSIGNED NOT NULL , 
	`receiverID` INT(12) UNSIGNED NOT NULL , 
	`requestDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
	`status` INT(3) UNSIGNED NOT NULL DEFAULT '0' , 
	`statusChangeDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`friendRequestID`), 
	INDEX `senderID` (`senderID`), 
	INDEX `receiverID` (`receiverID`)
) 
ENGINE = InnoDB;

CREATE TABLE `shareepicking`.`images` ( 
	`imageID` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT , 
	`userID` INT(12) UNSIGNED NOT NULL , 
	`name` VARCHAR(16) NOT NULL , 
	`caption` TEXT NULL ,
	`imageDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`imageID`), 
	INDEX `userID` (`userID`)
) 
ENGINE = InnoDB;

CREATE TABLE `shareepicking`.`image_likes` ( 
	`likeID` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT , 
	`imageID` INT(16) UNSIGNED NOT NULL , 
	`userID` INT(16) UNSIGNED NOT NULL , 
	`likeDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
	PRIMARY KEY (`likeID`), 
	INDEX `imageID` (`imageID`)
) 
ENGINE = InnoDB;

CREATE TABLE `shareepicking`.`images_comments` ( 
	`commentID` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT , 
	`imageID` INT(16) UNSIGNED NOT NULL , 
	`userID` INT(12) UNSIGNED NOT NULL , 
	`text` TEXT NULL , 
	`commentDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
	PRIMARY KEY (`commentID`), 
	INDEX `imageID` (`imageID`)
) 
ENGINE = InnoDB;

CREATE TABLE `shareepicking`.`albums` ( 
	`albumID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT , 
	`userID` INT(12) UNSIGNED NOT NULL , 
	`name` VARCHAR(255) NULL , 
	`description` TEXT NULL , 
	`albumDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
	PRIMARY KEY (`albumID`), 
	INDEX `userID` (`userID`)
) 
ENGINE = InnoDB;

CREATE TABLE `shareepicking`.`album_images` ( 
	`ID` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT , 
	`albumID` INT(10) UNSIGNED NOT NULL , 
	`imageID` INT(16) UNSIGNED NOT NULL , 
	`dateAdded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	PRIMARY KEY (`ID`), 
	INDEX `albumeID` (`albumeID`),
	INDEX `imageID` (`imageID`)
) 
ENGINE = InnoDB;

CREATE TABLE `shareepicking`.`activities` ( 
	`activityID` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT , 
	`type` INT(4) UNSIGNED NOT NULL , 
	`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
	`data1` INT(16) UNSIGNED NULL , 
	`data2` INT(16) UNSIGNED NULL , 
	`data3` INT(16) UNSIGNED NULL , 
	`data4` INT(16) UNSIGNED NULL , 
	PRIMARY KEY (`activityID`)
) ENGINE = InnoDB;