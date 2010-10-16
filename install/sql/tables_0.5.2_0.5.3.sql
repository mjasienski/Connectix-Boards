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