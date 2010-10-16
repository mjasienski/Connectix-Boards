<?php
// Config
function myStripslashes ($s) {
	return str_replace(array('[single_quote_slashed]','[double_quote_slashed]'),array("'",'"'),$s);
}
$top = $GLOBALS['cb_db']->single_result('SELECT cf_value FROM '.$GLOBALS['cb_db']->prefix.'config WHERE cf_field=\'foruminfotop_dyn\'');
$bot = $GLOBALS['cb_db']->single_result('SELECT cf_value FROM '.$GLOBALS['cb_db']->prefix.'config WHERE cf_field=\'foruminfobot_dyn\'');
$GLOBALS['cb_db']->query("REPLACE INTO ".$GLOBALS['cb_db']->prefix."config (cf_field,cf_value) VALUES
	('foruminfotop_dyn','".$GLOBALS['cb_db']->escape(myStripSlashes($top))."'),
	('foruminfobot_dyn','".$GLOBALS['cb_db']->escape(myStripSlashes($bot))."'),
	('postguest','no'),
	('gzip_output','yes'),
	('readornot_sessions','no'),
	('pass_salt','".genValidCode()."'),
	('cookie_path','/'),
	('mail_tt','Bonjour {--mail_user_name--},\r\n\r\nUn ou plusieurs nouveau(x) message(s) a (ont) été posté(s) dans le sujet {--mail_topic_name--} depuis votre dernière visite de celui-ci. Pour y accéder, cliquez sur le lien ci-dessous ou copiez le dans votre navigateur favori:\r\n{--mail_topic_link--}\r\n\r\nCe mail vous est envoyé suite à votre demande de suivre un sujet sur {--mail_forumname--}. Si cette demande est une erreur, rendez-vous sur ce sujet et cliquez sur &#39;Ne plus suivre ce sujet&#39;.\r\n\r\nA bientôt sur nos forums,\r\n{--mail_forum_owner--}')");

// Sql
execute_sqlfile('install/sql/upgrade_0.7.1_0.8.sql');

// Ordre des tg et f
$q = $GLOBALS['cb_db']->query('SELECT tg_id FROM '.$GLOBALS['cb_db']->prefix.'topicgroups LEFT JOIN '.$GLOBALS['cb_db']->prefix.'forums ON forum_id = tg_fromforum ORDER BY forum_order ASC,tg_order ASC');
$i = 1;
while ($r = $GLOBALS['cb_db']->fetch_assoc($q))
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_order='.($i++).' WHERE tg_id='.$r['tg_id']);

$q = $GLOBALS['cb_db']->query('SELECT forum_id FROM '.$GLOBALS['cb_db']->prefix.'forums ORDER BY forum_order ASC');
$i = 1;
while ($r = $GLOBALS['cb_db']->fetch_assoc($q))
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'forums SET forum_order='.($i++).' WHERE forum_id='.$r['forum_id']);

// Messages lus/non-lus
$mid = $GLOBALS['cb_db']->single_result('SELECT MAX(msg_id) FROM '.$GLOBALS['cb_db']->prefix.'messages');
$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_markasread='.$mid);
$GLOBALS['cb_db']->query('TRUNCATE TABLE '.$GLOBALS['cb_db']->prefix.'usertgs');
$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'usertopics WHERE ut_posted=0 AND ut_bookmark=0 AND ut_mail=0');

// Structure
cacheStructure();
resetTgCounts();
?>