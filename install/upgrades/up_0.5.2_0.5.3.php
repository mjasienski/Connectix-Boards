<?php
$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."users` ADD `usr_markasread` INT UNSIGNED NOT NULL AFTER `usr_signature`");
$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."users` ADD `usr_lastconnect` INT UNSIGNED NOT NULL AFTER `usr_registertime`");

$GLOBALS['cb_db']->query("UPDATE ".$GLOBALS['cb_db']->prefix."users SET usr_lastconnect=usr_registertime,usr_markasread=".time());

require_once(CB_PATH.'include/lib/lib.db.php');
execute_sqlfile('tables_0.5.2_0.5.3.sql.gz');

$GLOBALS['cb_db']->query("SELECT msg_topicid,msg_userid FROM ".$GLOBALS['cb_db']->prefix."messages");
while ($d = $GLOBALS['cb_db']->fetch_assoc($q)) {
	$GLOBALS['cb_db']->query("REPLACE INTO ".$GLOBALS['cb_db']->prefix."usertopics(ut_userid,ut_topicid,ut_posted) VALUES (".$d['msg_userid'].",".$d['msg_topicid'].",1)");
}

$GLOBALS['cb_db']->query("INSERT INTO `".$GLOBALS['cb_db']->prefix."config` (`cf_field`, `cf_value`) VALUES ('url_rewrite', 'no')");

$GLOBALS['cb_db']->query("DROP TABLE `".$GLOBALS['cb_db']->prefix."messagesread`");

if (is_dir('include/smarty')) deletedir('include/smarty');
?>