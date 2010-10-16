<?php
require_once(CB_PATH.'include/lib/lib.search.php');

$_SESSION['dump_lasttopic'] = (isset($_SESSION['dump_lasttopic']))?$_SESSION['dump_lasttopic']:-1;
$_SESSION['dump_lastpost'] = (isset($_SESSION['dump_lastpost']))?$_SESSION['dump_lastpost']:-1;
$_SESSION['maxpost'] = (isset($_SESSION['maxpost']))?$_SESSION['maxpost']:$GLOBALS['cb_db']->single_result("SELECT MAX(msg_id) FROM ".$GLOBALS['cb_db']->prefix."messages");
$_SESSION['maxtopic'] = (isset($_SESSION['maxtopic']))?$_SESSION['maxtopic']:$GLOBALS['cb_db']->single_result("SELECT MAX(topic_id) FROM ".$GLOBALS['cb_db']->prefix."topics");

$nbPosts = 500; // Taille d'un batch de posts
$nbTopics = 1000; // Taille d'un batch de topics

if ($_SESSION['dump_lasttopic'] == -1) {
	require_once(CB_PATH.'include/lib/lib.db.php');
	execute_sqlfile('install/sql/tables_0.5.5_0.6.sql');

	$GLOBALS['cb_db']->query("REPLACE INTO ".$GLOBALS['cb_db']->prefix."config (cf_field,cf_value) VALUES
		('mailsubject_tt', '{--mail_forumname--} : Nouveau message dans le sujet {--mail_topic_name--}'),
		('mail_tt', 'Bonjour {--mail_user_name--},\r\n\r\n{--mail_poster--} a posté un nouveau message dans {--mail_topic_name--}. Pour accéder à ce message, cliquez sur le lien ci-dessous ou copiez le dans votre navigateur favori:\r\n{--mail_topic_link--}\r\n\r\nCe mail vous est envoyé suite à votre demande de suivre un sujet sur {--mail_forumname--}. Si cette demande est une erreur, rendez-vous sur ce sujet et cliquez sur &#039;Ne plus suivre ce sujet&#039;.\r\n\r\nA bientot sur nos forums,\r\n\r\n{--mail_forum_owner--}'),
		('floodlimit', '30'),
		('edittopictitle', 'no')
		");

	$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."topics` ADD INDEX ( `topic_type` )");
	$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."topics` ADD INDEX ( `topic_lastmessage` )");
	$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."topics` ADD INDEX ( `topic_fromtopicgroup` )");
	$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."users` ADD INDEX ( `usr_name` )");
	$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."messages` ADD INDEX ( `msg_userip` )");
	$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."connected` ADD INDEX ( `con_timestamp` )");
	$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."connected` ADD INDEX ( `con_position` )");

	if (!$GLOBALS['cb_db']->single_result("SHOW COLUMNS FROM ".$GLOBALS['cb_db']->prefix."usertopics LIKE 'ut_mail'"))
		$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."usertopics` ADD `ut_mail` TINYINT( 1 ) NOT NULL DEFAULT '0'");

	$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."users` ADD `usr_pref_timezone` VARCHAR( 5 ) NOT NULL");
	$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."users` ADD `usr_pref_ctsummer` TINYINT( 1 ) NOT NULL DEFAULT '0'");
	$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."users` ADD `usr_pref_allowmassmail` TINYINT( 1 ) NOT NULL DEFAULT '1'");
}

if ($_SESSION['dump_lasttopic'] < $_SESSION['maxtopic']) {
	$rt = $GLOBALS['cb_db']->query("SELECT topic_id,topic_name,topic_comment
		FROM ".$GLOBALS['cb_db']->prefix."topics
		WHERE topic_id > ".$_SESSION['dump_lasttopic']." AND topic_id <= ".$_SESSION['maxtopic']."
		ORDER BY topic_id ASC
		LIMIT 0,".$nbTopics);

	$ct = 0;
	while($dt = $GLOBALS['cb_db']->fetch_assoc ($rt)) {
		parseMessageSearch($dt['topic_name'].' '.$dt['topic_comment'],$dt['topic_id']);
		$_SESSION['dump_lasttopic'] = $dt['topic_id'];
		$ct++;
	}

	$notices[] = 'Avancement de la mise à jour: '.number_format(10*$_SESSION['dump_lasttopic']/$_SESSION['maxtopic']).' % ...';
	$GLOBALS['skip'] = true;
} elseif ($_SESSION['dump_lastpost'] < $_SESSION['maxpost']) {
	$rp = $GLOBALS['cb_db']->query("SELECT msg_id,msg_topicid,msg_message
		FROM ".$GLOBALS['cb_db']->prefix."messages
		WHERE msg_id > ".$_SESSION['dump_lastpost']." AND msg_id <= ".$_SESSION['maxpost']."
		ORDER BY msg_id ASC
		LIMIT 0,".$nbPosts);

	$cp = 0;
	while($dp = $GLOBALS['cb_db']->fetch_assoc ($rp)) {
		parseMessageSearch($dp['msg_message'],$dp['msg_topicid'],$dp['msg_id']);
		$_SESSION['dump_lastpost'] = $dp['msg_id'];
		$cp++;
	}
	$notices[] = 'Avancement de la mise à jour: '.number_format(10+90*$_SESSION['dump_lastpost']/$_SESSION['maxpost']).' % ...';
	$GLOBALS['skip'] = true;
} else {
	$notices[] = 'Veuillez supprimer manuellement le fichier "writed.php".';
	$notices[] = 'Veuillez supprimer manuellement le dossier "parts" et tout ce qu\'il contient.';
}
?>
