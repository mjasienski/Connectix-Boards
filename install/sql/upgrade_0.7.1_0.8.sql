-- Notes des modérateurs --
CREATE TABLE `CB_TABLE_PREFIXmodnotes` (
	`mn_id` mediumint(8) unsigned NOT NULL auto_increment,
	`mn_modid` mediumint(8) unsigned NOT NULL,
	`mn_userid` mediumint(8) unsigned NOT NULL,
	`mn_date` int(10) unsigned NOT NULL,
	`mn_note` text NOT NULL ,
	PRIMARY KEY (`mn_id`) ,
	KEY `mn_keys1` (`mn_modid`),
	KEY `mn_keys2` (`mn_userid`)
) ENGINE = MYISAM;

ALTER TABLE `CB_TABLE_PREFIXmessages` ADD `msg_guest` VARCHAR( 255 ) NULL AFTER `msg_userid` ;
ALTER IGNORE TABLE `CB_TABLE_PREFIXmessages` ADD INDEX ( `msg_timestamp` ) ;

ALTER TABLE `CB_TABLE_PREFIXtopics` ADD `topic_guest` VARCHAR( 255 ) NULL AFTER `topic_starter` ;
ALTER IGNORE TABLE `CB_TABLE_PREFIXtopics` ADD INDEX ( `topic_type` , `topic_lastmessage` ) ;

ALTER TABLE `CB_TABLE_PREFIXgroups` ADD `gr_hide` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `gr_mps` ;
ALTER TABLE `CB_TABLE_PREFIXgroups` ADD `gr_mod` TEXT NOT NULL AFTER `gr_hide` ;

ALTER TABLE `CB_TABLE_PREFIXlog` ADD `log_param` SMALLINT NOT NULL ;
ALTER IGNORE TABLE `CB_TABLE_PREFIXlog` ADD INDEX ( `log_param` ) ;

ALTER TABLE `CB_TABLE_PREFIXusers` ADD `usr_reputation` SMALLINT NOT NULL DEFAULT '0' AFTER `usr_email` ;
ALTER TABLE `CB_TABLE_PREFIXusers` ADD `usr_lastaction` INT UNSIGNED NOT NULL AFTER `usr_lastconnect` ;
ALTER TABLE `CB_TABLE_PREFIXusers` ADD `usr_birthdate` DATE NOT NULL AFTER `usr_presentation`;
ALTER TABLE `CB_TABLE_PREFIXusers` ADD `usr_gender` TINYINT( 2 ) NOT NULL AFTER `usr_birthdate`;
ALTER TABLE `CB_TABLE_PREFIXusers` ADD `usr_realname` VARCHAR( 255 ) NOT NULL AFTER `usr_gender`;