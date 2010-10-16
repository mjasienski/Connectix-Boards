-- Messages automatiques --
CREATE TABLE `CB_TABLE_PREFIXautomessages` (
  `am_id` mediumint(8) unsigned NOT NULL auto_increment,
  `am_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `am_message` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  PRIMARY KEY  (`am_id`)
) ENGINE=MyISAM;

-- IPs bannies --
CREATE TABLE `CB_TABLE_PREFIXbanned` (
  `ban_ip` int(11) NOT NULL default 0,
  `ban_expires` int(11) NOT NULL default 0,
  UNIQUE KEY `ban_unique` (`ban_ip`)
) ENGINE=MyISAM;

-- Configuration --
CREATE TABLE `CB_TABLE_PREFIXconfig` (
  `cf_field` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `cf_value` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  UNIQUE KEY `cf_unique` (`cf_field`)
) ENGINE=MyISAM;

-- Membres connectés --
CREATE TABLE `CB_TABLE_PREFIXconnected` (
  `con_ip` int(11) NOT NULL default '0',
  `con_id` int(10) unsigned NOT NULL default '0',
  `con_timestamp` int(11) NOT NULL default '0',
  `con_position` varchar(127) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  PRIMARY KEY (`con_id`)
) ENGINE=MyISAM;

-- Forums --
CREATE TABLE `CB_TABLE_PREFIXforums` (
  `forum_id` smallint(5) unsigned NOT NULL auto_increment,
  `forum_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `forum_order` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`forum_id`),
  KEY `forum_keys` (`forum_order`)
) ENGINE=MyISAM;

-- Groupes d'utilisateurs --
CREATE TABLE `CB_TABLE_PREFIXgroups` (
  `gr_id` smallint(5) unsigned NOT NULL auto_increment,
  `gr_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `gr_status` tinyint(3) unsigned NOT NULL default '0',
  `gr_cond` smallint(6) NOT NULL default '0',
  `gr_color` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `gr_mps` smallint(5) unsigned NOT NULL default '20',
  `gr_hide` TINYINT( 1 ) NOT NULL DEFAULT '0',
  `gr_mod` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `gr_auth_create` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `gr_auth_reply` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `gr_auth_see` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `gr_auth_flood` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY (`gr_id`)
) ENGINE=MyISAM;

-- Log des actions des modérateurs --
CREATE TABLE `CB_TABLE_PREFIXlog` (
  `log_id` mediumint(8) unsigned NOT NULL auto_increment,
  `log_type` tinyint(3) unsigned NOT NULL default '0',
  `log_usermake` mediumint(8) unsigned NOT NULL default '0',
  `log_timestamp` int(10) unsigned NOT NULL default '0',
  `log_rep_user` mediumint(8) unsigned NOT NULL default '0',
  `log_rep_topic` mediumint(8) unsigned NOT NULL default '0',
  `log_rep_msg` mediumint(8) unsigned NOT NULL default '0',
  `log_param` SMALLINT NULL,
  PRIMARY KEY  (`log_id`),
  KEY `log_keys1` (`log_usermake`),
  KEY `log_keys2` (`log_rep_user`),
  KEY `log_keys3` (`log_rep_topic`),
  KEY `log_keys4` (`log_rep_msg`),
  KEY `log_keys5` (`log_param`)
) ENGINE=MyISAM;

-- Messages --
CREATE TABLE `CB_TABLE_PREFIXmessages` (
  `msg_id` mediumint(8) unsigned NOT NULL auto_increment,
  `msg_topicid` mediumint(8) unsigned NOT NULL default '0',
  `msg_userid` mediumint(8) unsigned NOT NULL default '0',
  `msg_guest` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `msg_userip` int(11) NOT NULL default '0',
  `msg_message` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `msg_timestamp` int(10) unsigned NOT NULL default '0',
  `msg_modified` int(10) unsigned NOT NULL default '0',
  `msg_modifieduser` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`msg_id`),
  KEY `msg_keys1` (`msg_topicid`),
  KEY `msg_keys2` (`msg_userid`),
  KEY `msg_keys3` (`msg_userip`),
  KEY `msg_keys4` (`msg_timestamp`)
) ENGINE=MyISAM;

-- Messages personnels --
CREATE TABLE `CB_TABLE_PREFIXmp` (
  `mp_id` mediumint(8) unsigned NOT NULL auto_increment,
  `mp_subj` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `mp_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `mp_read` tinyint(3) unsigned NOT NULL default '0',
  `mp_to` mediumint(8) unsigned NOT NULL default '0',
  `mp_from` mediumint(8) unsigned NOT NULL default '0',
  `mp_to_del` tinyint(3) unsigned NOT NULL default '0',
  `mp_from_del` tinyint(3) unsigned NOT NULL default '0',
  `mp_timestamp` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`mp_id`)
) ENGINE=MyISAM;

-- Notes des modérateurs --
CREATE TABLE `CB_TABLE_PREFIXmodnotes` (
	`mn_id` mediumint(8) unsigned NOT NULL auto_increment,
	`mn_modid` mediumint(8) unsigned NULL,
	`mn_userid` mediumint(8) unsigned NULL,
	`mn_date` int(10) unsigned NULL,
	`mn_note` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
	PRIMARY KEY (`mn_id`) ,
	KEY `mn_keys1` (`mn_modid`),
	KEY `mn_keys2` (`mn_userid`)
) ENGINE = MYISAM;

-- Possibilités de réponses aux sondages --
CREATE TABLE `CB_TABLE_PREFIXpollpossibilities` (
  `poss_id` mediumint(8) unsigned NOT NULL auto_increment,
  `poss_pollid` mediumint(8) unsigned NOT NULL default '0',
  `poss_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `poss_votes` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`poss_id`),
  KEY `poss_keys` (`poss_pollid`)
) ENGINE=MyISAM;

-- Sondages --
CREATE TABLE `CB_TABLE_PREFIXpolls` (
  `poll_id` mediumint(8) unsigned NOT NULL auto_increment,
  `poll_question` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `poll_voted` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `poll_totalvotes` mediumint(8) unsigned NOT NULL default '0',
  `poll_white` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`poll_id`)
) ENGINE=MyISAM;

-- Rangs utilisateurs
CREATE TABLE `CB_TABLE_PREFIXranks` (
  `rk_id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `rk_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
  `rk_posts` INT UNSIGNED NOT NULL
) ENGINE = MYISAM ;

-- Messages signalés
CREATE TABLE `CB_TABLE_PREFIXreports` (
  `rep_id` mediumint(8) unsigned NOT NULL auto_increment,
  `rep_desc` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `rep_msgid` mediumint(8) unsigned NOT NULL default '0',
  `rep_userid` mediumint(8) unsigned NOT NULL default '0',
  `rep_timestamp` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`rep_id`),
  KEY `rep_keys1` (`rep_msgid`),
  KEY `rep_keys2` (`rep_userid`)
) ENGINE=MyISAM;

-- Smileys --
CREATE TABLE `CB_TABLE_PREFIXsmileys` (
  `sm_id` mediumint(9) unsigned NOT NULL auto_increment,
  `sm_symbol` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `sm_filename` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `sm_orig_used` enum('oui','non') NOT NULL default 'oui',
  PRIMARY KEY  (`sm_id`)
) ENGINE=MyISAM;

-- Correspondances des mots pour les recherches --
CREATE TABLE `CB_TABLE_PREFIXsrc_matches` (
  `sm_wordid` mediumint(8) unsigned NULL,
  `sm_msgid` mediumint(8) unsigned NULL,
  `sm_topicid` mediumint(8) unsigned NULL,
  KEY `sm_keys1` (`sm_wordid`),
  KEY `sm_keys2` (`sm_msgid`),
  KEY `sm_keys3` (`sm_topicid`)
) ENGINE=MyISAM;

-- Mots pour les recherches --
CREATE TABLE `CB_TABLE_PREFIXsrc_words` (
  `sw_id` mediumint(8) unsigned NOT NULL auto_increment,
  `sw_word` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  PRIMARY KEY  (`sw_id`),
  KEY `sw_keys1` (`sw_word`)
) ENGINE=MyISAM;

-- Statistiques --
CREATE TABLE `CB_TABLE_PREFIXstats` (
  `st_field` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `st_value` mediumint(8) unsigned NOT NULL default '0',
  UNIQUE KEY `st_unique` (`st_field`)
) ENGINE=MyISAM;

-- Groupes de sujets --
CREATE TABLE `CB_TABLE_PREFIXtopicgroups` (
  `tg_id` mediumint(8) unsigned NOT NULL auto_increment,
  `tg_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `tg_comment` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `tg_fromforum` mediumint(8) unsigned NOT NULL default '0',
  `tg_fromtopicgroup` mediumint(8) unsigned NOT NULL default '0',
  `tg_visibility` tinyint(3) unsigned NOT NULL default '0',
  `tg_order` smallint(5) unsigned NOT NULL default '0',
  `tg_lasttopic` mediumint(8) unsigned NOT NULL default '0',
  `tg_nbtopics` mediumint(8) unsigned NOT NULL default '0',
  `tg_nbmess` mediumint(8) unsigned NOT NULL default '0',
  `tg_link` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  PRIMARY KEY  (`tg_id`),
  KEY `tg_keys1` (`tg_order`),
  KEY `tg_keys2` (`tg_fromforum`),
  KEY `tg_keys3` (`tg_fromtopicgroup`),
  KEY `tg_forums` (`tg_visibility`,`tg_fromforum`)
) ENGINE=MyISAM;

-- Sujets --
CREATE TABLE `CB_TABLE_PREFIXtopics` (
  `topic_id` mediumint(8) unsigned NOT NULL auto_increment,
  `topic_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `topic_comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `topic_starter` mediumint(8) unsigned NOT NULL default '0',
  `topic_guest` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `topic_views` mediumint(8) unsigned NOT NULL default '0',
  `topic_type` tinyint(3) unsigned NOT NULL default '0',
  `topic_status` tinyint(3) unsigned NOT NULL default '0',
  `topic_poll` mediumint(8) unsigned NULL,
  `topic_fromtopicgroup` mediumint(8) unsigned NOT NULL default '0',
  `topic_displaced` mediumint(8) unsigned NOT NULL default '0',
  `topic_lastmessage` mediumint(8) unsigned NOT NULL default '0',
  `topic_nbreply` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`topic_id`),
  KEY `topic_keys1` (`topic_type`,`topic_lastmessage`),
  KEY `topic_keys2` (`topic_lastmessage`),
  KEY `topic_keys3` (`topic_fromtopicgroup`),
  KEY `topic_keys4` (`topic_poll`)
) ENGINE=MyISAM;

-- Utilisateurs --
CREATE TABLE `CB_TABLE_PREFIXusers` (
  `usr_id` mediumint(8) unsigned NOT NULL auto_increment,
  `usr_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_password` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_changepass` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_changepass_c` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_registered` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_registertime` int(10) unsigned NOT NULL default '0',
  `usr_lastconnect` int(10) unsigned NOT NULL default '0',
  `usr_lastaction` int(10) UNSIGNED NOT NULL default '0',
  `usr_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_reputation` SMALLINT NOT NULL default '0',
  `usr_msn` varchar(255) NOT NULL default '',
  `usr_icq` varchar(255) NOT NULL default '',
  `usr_aim` varchar(255) NOT NULL default '',
  `usr_yahoo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_publicemail` tinyint(3) unsigned NOT NULL default '0',
  `usr_place` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_presentation` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_birthdate` DATE NULL,
  `usr_gender` TINYINT( 2 ) NOT NULL default '0',
  `usr_realname` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_website` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_class` smallint(5) unsigned NOT NULL default '0',
  `usr_mod` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_punished` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_nbmess` mediumint(8) unsigned NOT NULL default '0',
  `usr_nbmp` smallint(5) unsigned NOT NULL default '0',
  `usr_mpadv` smallint(5) unsigned NOT NULL default '0',
  `usr_ip` int(11) NOT NULL default '0',
  `usr_avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_signature` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_markasread` int(10) unsigned NOT NULL default '0',
  `usr_pref_usrs` tinyint(3) unsigned NOT NULL default '20',
  `usr_pref_topics` tinyint(3) unsigned NOT NULL default '20',
  `usr_pref_msgs` tinyint(3) unsigned NOT NULL default '15',
  `usr_pref_res` tinyint(3) unsigned NOT NULL default '15',
  `usr_pref_lang` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_pref_skin` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_pref_ctsummer` tinyint(1) NOT NULL default '0',
  `usr_pref_timezone` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `usr_pref_allowmassmail` tinyint(1) NOT NULL default '1',
  `usr_pref_mailmp` tinyint( 1 ) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`),
  KEY `usr_keys` (`usr_name`)
) ENGINE=MyISAM;

-- Infos utilisateurs-groupes de sujets --
CREATE TABLE `CB_TABLE_PREFIXusertgs` (
  `utg_userid` mediumint(8) unsigned NOT NULL default '0',
  `utg_tgid` mediumint(8) unsigned NOT NULL default '0',
  `utg_markasread` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`utg_userid`,`utg_tgid`)
) ENGINE=MyISAM;

-- Infos utilisateurs-sujets --
CREATE TABLE `CB_TABLE_PREFIXusertopics` (
  `ut_userid` mediumint(8) unsigned NOT NULL default '0',
  `ut_topicid` mediumint(8) unsigned NOT NULL default '0',
  `ut_msgread` mediumint(8) unsigned NOT NULL default '0',
  `ut_posted` tinyint(1) NOT NULL default '0',
  `ut_bookmark` tinyint(1) NOT NULL default '0',
  `ut_mail` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ut_userid`,`ut_topicid`)
) ENGINE=MyISAM;
