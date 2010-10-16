-- Correspondances des mots pour les recherches --
CREATE TABLE `CB_TABLE_PREFIXsrc_matches` (
  `sm_wordid` mediumint(8) unsigned NOT NULL,
  `sm_msgid` mediumint(8) unsigned NOT NULL,
  `sm_topicid` mediumint(8) unsigned NOT NULL,
  KEY `sm_keys1` (`sm_wordid`),
  KEY `sm_keys2` (`sm_msgid`),
  KEY `sm_keys3` (`sm_topicid`)
) ENGINE=MyISAM;

-- Mots pour les recherches --
CREATE TABLE `CB_TABLE_PREFIXsrc_words` (
  `sw_id` mediumint(8) unsigned NOT NULL auto_increment,
  `sw_word` varchar(100) NOT NULL,
  PRIMARY KEY  (`sw_id`),
  KEY `sw_keys1` (`sw_word`)
) ENGINE=MyISAM;