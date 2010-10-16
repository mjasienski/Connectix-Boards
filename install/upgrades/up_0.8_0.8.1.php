<?php
$GLOBALS['cb_db']->query("REPLACE INTO ".$GLOBALS['cb_db']->prefix."config (cf_field,cf_value) VALUES
	('mail_mp','Bonjour {--mail_user_name--},\r\n\r\nVous avez recu un (ou plusieurs) nouveau(x) message(s) personnel(s), dont le premier est de la part de {--mail_poster--} sur {--mail_forumname--}.\r\n\r\nPour le(s) consulter, connectez-vous au forum et rendez-vous dans votre panneau de gestion des messages personnels, ou suivez le lien suivant:\r\n{--mail_mp_link--}\r\n\r\nA bientot sur nos forums,\r\n\r\n{--mail_forum_owner--}')");
?>