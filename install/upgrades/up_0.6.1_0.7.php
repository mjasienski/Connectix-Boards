<?php
$GLOBALS['cb_db']->query("REPLACE INTO ".$GLOBALS['cb_db']->prefix."config (cf_field,cf_value) VALUES
	('enabletopictrack','yes'),
	('displayconnected','yes'),
	('displayfastredirect','yes'),
	('mail_mp','Bonjour {--mail_user_name--},\r\n\r\nVous avez recu un nouveau message personnel de la part de {--mail_poster--} sur {--mail_forumname--}.\r\n\r\nPour le consulter, connectez-vous au forum et rendez-vous dans votre panneau de gestion des messages personnels.\r\n\r\nA bientot sur nos forums,\r\n\r\n{--mail_forum_owner--}'),
	('mailsubject_mp','Vous avez recu un nouveau MP sur {--mail_forumname--}!')");

$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."topics` ADD `topic_poll` MEDIUMINT UNSIGNED NULL DEFAULT NULL AFTER `topic_status`");
$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."topics` ADD INDEX ( `topic_poll` )");
$r = $GLOBALS['cb_db']->query("SELECT poll_topicid,poll_id FROM ".$GLOBALS['cb_db']->prefix."polls");
while ($p = $GLOBALS['cb_db']->fetch_assoc($r)) {
	$GLOBALS['cb_db']->query("UPDATE ".$GLOBALS['cb_db']->prefix."topics SET topic_poll=".$p['poll_id']." WHERE topic_id=".$p['poll_topicid']);
}
$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."polls` DROP `poll_topicid`");

$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."users` ADD `usr_pref_mailmp` TINYINT( 1 ) NOT NULL DEFAULT '0'");
$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."topicgroups` ADD INDEX ( `tg_visibility` , `tg_fromforum` )");

$GLOBALS['cb_db']->query("DROP TABLE `".$GLOBALS['cb_db']->prefix."smileysbanks`");
$GLOBALS['cb_db']->query("DROP TABLE `".$GLOBALS['cb_db']->prefix."smileyscomponents`");

$notices[] = 'Vous pouvez supprimer le dossier "smileys/banks" et tout ce qu\'il contient. Il est devenu inutile.';
?>