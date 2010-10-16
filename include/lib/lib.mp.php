<?php
/**
*	Connectix Boards 1.0, free interactive php bulletin boards.
*	Copyright (C) 2005-2010  Jasienski Martin.
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 3 of the License, or
*	(at your option) any later version.
*
*	This program is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU General Public License for more details.
*
*	You can find a copy of the GNU General Public License at 
*	<http://www.connectix-boards.org/license.txt>.
*/

//// Fonctions relatives aux messages personnels ////

// Envoi d'un mp
function sendMp($from,$to,$subject,$message) {
	// L'envoyeur peut-il stocker un message en plus?
	if (!canHaveNewMp($from,getTotMp($from),$_SESSION['cb_user']->gr_mps)) {
		trigger_error(lang('error_mp_mefull'),E_USER_WARNING);
		return false;
	}
	
	// Est-ce un automessage?
	if ($to==$from) {
		trigger_error(lang('error_user_automess'),E_USER_WARNING);
		return false;
	}
	
	// Le destinataire peut-il stocker un message en plus?
	if (!canHaveNewMp($to,getTotMp($to))) {
		trigger_error(lang('error_mp_tofull'),E_USER_WARNING);
		return false;
	}
	
	// Le sujet est-il vide?
	if (utf8_strlen(trim($subject))==0) {
		trigger_error(lang('error_subj_noexist'),E_USER_WARNING);
		return false;
	}
	
	// Le message est-il vide?
	if (utf8_strlen(trim($message))==0) {
		trigger_error(lang('error_mess_noexist'),E_USER_WARNING);
		return false;
	}
	
	// Ecriture du message dans la bdd
	$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'mp(mp_subj,mp_content,mp_read,mp_to,mp_from,mp_to_del,mp_from_del,mp_timestamp) VALUES(\''.clean($subject).'\',\''.clean($message,STR_MULTILINE + STR_PARSEBB).'\',0,'.$to.','.$from.',0,0,'.time().')');
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_nbmp=usr_nbmp+1,usr_mpadv=1 WHERE usr_id='.$to);
	
	// On vérifie si le destinataire veut recevoir un mail pour l'avertir du nouveau MP
	$rq = $GLOBALS['cb_db']->query('SELECT usr_name,usr_email,usr_pref_mailmp FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_id='.$to);
	$dt = $GLOBALS['cb_db']->fetch_assoc($rq);
	if ($dt['usr_pref_mailmp']) {
		$patterns=array(
			'{--mail_poster--}'		 =>  getUserName($from),
			'{--mail_user_name--}'   =>  $dt['usr_name'],
			'{--mail_forumname--}'   =>  $GLOBALS['cb_cfg']->config['forumname'],
			'{--mail_forum_owner--}' =>  $GLOBALS['cb_cfg']->config['forumowner'],
			'{--mail_mp_link--}' 	 =>  'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/'.manage_url('index.php?act=mp&sub=1','forum-mp-inbox.html')
			);
		$mailsubj = str_replace('{--mail_forumname--}',$GLOBALS['cb_cfg']->config['forumname'],$GLOBALS['cb_cfg']->config['mailsubject_mp']);
		$mailmsg = str_replace(array_keys($patterns),$patterns,$GLOBALS['cb_cfg']->config['mail_mp']);

		require_once(CB_PATH.'include/lib/lib.mails.php');
		ob_start();
		sendMail($dt['usr_email'],$mailsubj,$mailmsg);
		ob_end_clean();
	}

	return true;
}

/* Fonction qui renvoie le nombre de messages total de l'utilisateur $id. */
function getTotMp($id) {
	return $GLOBALS['cb_db']->single_result('SELECT COUNT(*) FROM '.$GLOBALS['cb_db']->prefix.'mp WHERE (mp_to='.(int)$id.' AND mp_to_del=0) OR (mp_from='.(int)$id.' AND mp_from_del=0)');
}

/* Fonction qui renvoie si l'utilisateur $id_user peut avoir encore des mp ou si sa boite est pleine. */
function canHaveNewMp ($id_user,$totnbmp,$maxmp = false) {
	if ($maxmp===false) {
		$maxmp = $GLOBALS['cb_db']->single_result('SELECT gr_mps FROM '.$GLOBALS['cb_db']->prefix.'users LEFT JOIN '.$GLOBALS['cb_db']->prefix.'groups ON gr_id=usr_class WHERE usr_id='.(int)$id_user);
		if ($maxmp===false) return false;
	}
	return $totnbmp<$maxmp;
}
?>