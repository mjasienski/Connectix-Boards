-- Smileys --
CREATE TABLE `CB_TABLE_PREFIXsmileys` (
  `sm_id` mediumint(9) unsigned NOT NULL auto_increment,
  `sm_symbol` varchar(25) NOT NULL default '',
  `sm_filename` varchar(100) NOT NULL default '',
  `sm_orig_used` enum('oui','non') NOT NULL default 'oui',
  PRIMARY KEY  (`sm_id`)
) ENGINE=MyISAM;

-- Banques de smileys --
CREATE TABLE `CB_TABLE_PREFIXsmileysbanks` (
  `bank_id` smallint(5) unsigned NOT NULL auto_increment,
  `bank_name` text NOT NULL,
  `bank_author` text,
  PRIMARY KEY  (`bank_id`)
) ENGINE=MyISAM;

-- Composants de smileys --
CREATE TABLE `CB_TABLE_PREFIXsmileyscomponents` (
  `comp_id` smallint(5) unsigned NOT NULL auto_increment,
  `comp_bank` smallint(5) unsigned NOT NULL default '0',
  `comp_layer` tinyint(3) unsigned NOT NULL default '0',
  `comp_file` text NOT NULL,
  PRIMARY KEY  (`comp_id`)
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
  PRIMARY KEY  (`log_id`),
  KEY `log_keys1` (`log_usermake`),
  KEY `log_keys2` (`log_rep_user`),
  KEY `log_keys3` (`log_rep_topic`),
  KEY `log_keys4` (`log_rep_msg`)
) ENGINE=MyISAM;