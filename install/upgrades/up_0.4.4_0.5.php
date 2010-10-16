<?php
$GLOBALS['cb_db']->query("REPLACE INTO ".$GLOBALS['cb_db']->prefix."config (cf_field,cf_value) VALUES
	('mailsubject_cp', 'Récupération de vos informations d\'identification ! !'),
	('mail_cp', 'Bonjour {--mail_user_name--},\r\n\r\nCe mail vous est envoyé suite à votre demande de récupération d\'informations personnelles sur {--mail_forumname--}. Si cette demande est une erreur, ne tenez pas compte de ce mail.\r\n\r\nLe mot de passe aléatoire qui a été généré est le suivant: {--mail_user_password--}\r\n\r\nVous pouvez valider ce mot de passe simplement en cliquant sur le lien ci-dessous, ou en le copiant dans votre navigateur favori :\r\n{--mail_confirm_link--}\r\n\r\nMerci beaucoup et à bientot sur nos forums.\r\n\r\n{--mail_forum_owner--}'),
	('foruminfobot_dyn', ''),
	('foruminfotop_dyn', ''),
	('website',''),
	('show_posted','yes'),
	('bb_sign_forbidden',''),
	('defaultstyle', 'BlueSkin'),
	('banned_ips','')
	");

$GLOBALS['cb_db']->query("REPLACE INTO ".$GLOBALS['cb_db']->prefix."stats (st_field,st_value) VALUES ('nb_reports','0')");

require_once(CB_PATH.'include/lib/lib.db.php');
execute_sqlfile('install/sql/upgrade_0.4.4_0.5.sql');
?>