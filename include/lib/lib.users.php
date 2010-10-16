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

/* Différentes time-zones possibles */
function getTimeZones() {
	return array(
		'-12'	=> '(GMT - 12:00 h) Enitwetok, Kwajalien',
		'-11'	=> '(GMT - 11:00 h) Midway Island, Samoa',
		'-10'	=> '(GMT - 10:00 h) Hawaii',
		'-9'	=> '(GMT - 9:00 h) Alaska',
		'-8'	=> '(GMT - 8:00 h) Pacific Time (US &amp; Canada)',
		'-7'	=> '(GMT - 7:00 h) Mountain Time (US &amp; Canada)',
		'-6'	=> '(GMT - 6:00 h) Central Time (US &amp; Canada), Mexico City',
		'-5'	=> '(GMT - 5:00 h) Eastern Time (US &amp; Canada), Bogota, Lima, Quito',
		'-4'	=> '(GMT - 4:00 h) Atlantic Time (Canada), Caracas, La Paz',
		'-3.5'	=> '(GMT - 3:30 h) Newfoundland',
		'-3'	=> '(GMT - 3:00 h) Brazil, Buenos Aires, Georgetown, Falkland Is.',
		'-2'	=> '(GMT - 2:00 h) Mid-Atlantic, Ascention Is., St Helena',
		'-1'	=> '(GMT - 1:00 h) Azores, Cape Verde Islands',
		'0'		=> '(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia',
		'1'		=> '(GMT + 1:00 h) Berlin, Brussels, Copenhagen, Madrid, Paris, Rome',
		'2'		=> '(GMT + 2:00 h) Kaliningrad, South Africa, Warsaw',
		'3'		=> '(GMT + 3:00 h) Baghdad, Riyadh, Moscow, Nairobi',
		'3.5'	=> '(GMT + 3:30 h) Tehran',
		'4'		=> '(GMT + 4:00 h) Adu Dhabi, Baku, Muscat, Tbilisi',
		'4.5'	=> '(GMT + 4:30 h) Kabul',
		'5'		=> '(GMT + 5:00 h) Ekaterinburg, Islamabad, Karachi, Tashkent',
		'5.5'	=> '(GMT + 5:30 h) Bombay, Calcutta, Madras, Nouveaux Delhi',
		'6'		=> '(GMT + 6:00 h) Almaty, Colomba, Dhakra',
		'7'		=> '(GMT + 7:00 h) Bangkok, Hanoi, Jakarta',
		'8'		=> '(GMT + 8:00 h) Beijing, Hong Kong, Perth, Singapore, Taipei',
		'9'		=> '(GMT + 9:00 h) Osaka, Sapporo, Seoul, Tokyo, Yakutsk',
		'9.5'	=> '(GMT + 9:30 h) Adelaide, Darwin',
		'10'	=> '(GMT + 10:00 h) Melbourne, Papua Nouveaux Guinea, Sydney, Vladivostok',
		'11'	=> '(GMT + 11:00 h) Magadan, Nouveaux Caledonia, Solomon Islands',
		'12'	=> '(GMT + 12:00 h) Auckland, Wellington, Fiji, Marshall Island'
		);
}

//// Fonctions de gestion des Groupes ////

/* Fonction qui met un utilisateur à la classe (non-admin) à laquelle il doit appartenir en fonction de ses posts. */
function setUserPostClass ($user_id,$pass_if_admin = false) {
	$return=$GLOBALS['cb_db']->query('SELECT usr_nbmess,gr_cond 
		FROM '.$GLOBALS['cb_db']->prefix.'users 
			LEFT JOIN '.$GLOBALS['cb_db']->prefix.'groups ON gr_id=usr_class 
			WHERE usr_id='.$user_id);
	if ($user_msg=$GLOBALS['cb_db']->fetch_array($return)) {
		if ($pass_if_admin || !isset($user_msg['gr_cond']) || (isset($user_msg['gr_cond']) && $user_msg['gr_cond']>=0)) {
			$ng=$GLOBALS['cb_db']->single_result('SELECT gr_id 
				FROM '.$GLOBALS['cb_db']->prefix.'groups 
				WHERE gr_cond>=0 AND gr_cond<='.$user_msg['usr_nbmess'].' 
					AND gr_id!=0 
				ORDER BY gr_cond DESC LIMIT 1');
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_class='.$ng.' WHERE usr_id='.$user_id);
			return $ng;
		} else return false;
	} else return false;
}

//// Fonctions utiles pour la gestion des utilisateurs ////

/* Fonction qui renvoie où se trouve l'utilisateur. */
function getUserLocation ($location) {
	$f = 0;		$fname = '';
	$tg = 0;	$tgname = '';

	$matches = array();
	if (preg_match('`^index_([0-9]+)$`',$location,$matches)) {
		$position = 'index_f';
		$f = $matches[1];
	} elseif (preg_match('`^index(_[0-9]+)+$`',$location,$matches)) {
		$position = 'index_tg';
		$tg = utf8_substr($matches[1],1);
	} elseif (preg_match('`^index(_[0-9]+)+_t_[0-9]+$`',$location,$matches)) {
		$position = 'index_t';
		$tg = utf8_substr($matches[1],1);
	} elseif (preg_match('`^index(_[0-9]+)+_t_[0-9]+_wm$`',$location,$matches)) {
		$position = 'index_t_wm';
		$tg = utf8_substr($matches[1],1);
	} elseif (preg_match('`^index(_[0-9]+)+_wm$`',$location,$matches)) {
		$position = 'index_tg_wm';
		$tg = utf8_substr($matches[1],1);
	} else $position = $location;

	if ($f != 0)
		$fname = $GLOBALS['cb_str_fnames'][$f];
	if ($tg != 0)
		$tgname = $_SESSION['cb_user']->getAuth('see',$tg) ? $GLOBALS['cb_str_tgnames'][$tg] : lang('topicgroup_noaccess');
	
	return array('position' => $position,'tg' => $tgname,'f' => $fname);
}

/* Fonction qui retourne un tableau avec tous les avatars de la galerie. */
function getGallery () {
	$avatars=array();
	$handle = opendir(CB_PATH.'avatars/gallery/');
	while (false !== ($file = readdir ($handle))) {
		if ($file != '.' && $file != '..' && $file != 'index.html' && $file != 'index.php') {
			$avatars[] = $file;
		}
	}
	closedir($handle);
	return $avatars;
}

//// Création ou suppression d'un membre ////

/* Enregistrement d'un membre */
function registerUser($name,$pass,$email,$class=0,$sendmail=true,$notice=true) {
	// Vérification du nom de l'utilisateur
	if (!verifyUserName($name))
		return false;
	
	// Vérification du mot de passe
	if (!verifyUserPassword($pass))
		return false;
	
	// Vérification de l'adresse mail
	if (!verifyUserMail($email))
		return false;
	
	// On envoie le mail s'il le faut
	$registration = 'TRUE';
	if ($GLOBALS['cb_cfg']->config['enablemail']=='yes' && $sendmail) {
		$registration = genValidCode();
		
		$patterns=array(
			'{--mail_user_name--}'	 =>  clean($name,STR_TODISPLAY),
			'{--mail_user_password--}' =>  clean($pass,STR_TODISPLAY),
			'{--mail_forumname--}'	 =>  $GLOBALS['cb_cfg']->config['forumname'],
			'{--mail_confirm_link--}'  =>  'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).((utf8_substr(dirname($_SERVER['PHP_SELF']),-1)!=='/')?'/':'').manage_url('index.php?act=validate&hash='.$registration,'forum-validate.html?hash='.$registration),
			'{--mail_forum_owner--}'   =>  $GLOBALS['cb_cfg']->config['forumowner']
			);
		$mailmsg = str_replace(array_keys($patterns),$patterns,$GLOBALS['cb_cfg']->config['mail_ci']);

		require_once(CB_PATH.'include/lib/lib.mails.php');
		if (!sendMail(	clean($email),
						str_replace('{--mail_forumname--}',
						$GLOBALS['cb_cfg']->config['forumname'],
						$GLOBALS['cb_cfg']->config['mailsubject_ci']),
						$mailmsg)) {
			trigger_error(lang('error_sendmail'),E_USER_WARNING);
			return false;
		}
	}
	
	// Insersion du membre dans la base de données
	$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'users(usr_name,usr_password,usr_registered,usr_registertime,usr_email,usr_ip) 
		VALUES(\''.clean($name).'\',\''.cbHash($pass).'\',\''.$registration.'\','.time().',\''.clean($email).'\','.ip2long($_SERVER['REMOTE_ADDR']).')');
	$id_user = $GLOBALS['cb_db']->insert_id();
	
	// Classe de l'utilisateur créé
	if ($class==0)
		setUserPostClass($id_user);
	else
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_class='.(int)$class.' WHERE usr_id='.$id_user);
	
	// Finalisation
	if ($registration == 'TRUE')
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value+1 WHERE st_field=\'registered_users\'');
	
	// Remarque de réussite
	if ($notice) {
		if ($GLOBALS['cb_cfg']->config['enablemail']=='yes' && $sendmail)
			trigger_error(lang('reg_success_mail'),E_USER_NOTICE);
		else
			trigger_error(lang('reg_success_nomail'),E_USER_NOTICE);
	}
	
	return true;
}

/* Vérification du nom d'utilisateur */
function verifyUserName($username) {
	// Vérification de la taille du nom
	if (utf8_strlen(trim($username))<=2 || utf8_strlen(trim($username))>=31) {
		trigger_error(lang('error_reg_namelength'),E_USER_WARNING);
		return false;
	}
	
	// Vérification des caractères utilisés dans le nom
	if (preg_match('#[^\w ]+#i',trim($username))) {
		trigger_error(lang('error_reg_badchars'),E_USER_WARNING);
		return false;
	}
	
	// Si ce nom existe déjà
	if ((bool)getUserId($username)) {
		trigger_error(lang('error_reg_alreadytakenname'),E_USER_WARNING);
		return false;
	}
	
	// Toutes les vérifications sont passées, c'est donc OK
	return true;
}

/* Vérification d'un password valide */
function verifyUserPassword ($pass) {
	// Vérification de la longueur du password
	if (utf8_strlen(trim($pass))<3 || utf8_strlen(trim($pass))>20) {
		trigger_error(lang('error_reg_passwordlength'),E_USER_WARNING);
		return false;
	}
	
	// Toutes les vérifications sont passées, c'est donc OK
	return true;
}

/* Vérification de la validité d'une adresse mail */
function verifyUserMail ($email) {
	// Vérification du format de l'adresse mail
	if (!preg_match('#^[^@]+?@.+?\.[a-z]{2,4}$#i',clean($email))) {
		trigger_error(lang('error_reg_nomail'),E_USER_WARNING);
		return false;
	}
	
	// Vérification que l'adresse mail n'est pas utilisée par un autre utilisateur
	if ($GLOBALS['cb_db']->single_result('SELECT 1 FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_email=\''.clean($email).'\'')) {
		trigger_error(lang('error_reg_mail_already_used'),E_USER_WARNING);
		return false;
	}
	
	// Toutes les vérifications sont passées, c'est donc OK
	return true;
}

/* Suppression d'un membre */
function deleteUser ($uid) {
	if (isUser($uid)) {
		/* On supprime ses mp et on le marque comme invité */
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'mp SET mp_to=0,mp_to_del=1 WHERE mp_to='.$uid);
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'mp SET mp_from=0,mp_from_del=1 WHERE mp_from='.$uid);
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'mp WHERE mp_to_del=1 AND mp_from_del=1');

		/* On marque ses messages comme écrits par un invité du même nom ou anonyme */
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'messages SET msg_userid=0'.((isset($_POST['deleteuser_msg']) && $_POST['deleteuser_msg'] == 'guest')?'':',msg_guest=\''.clean($_POST['delete_user']).'\'').' WHERE msg_userid='.$uid);
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'messages SET msg_modifieduser=0,msg_modified=\'\' WHERE msg_modifieduser='.$uid);
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_starter=0'.((isset($_POST['deleteuser_msg']) && $_POST['deleteuser_msg'] == 'guest')?'':',topic_guest=\''.clean($_POST['delete_user']).'\'').' WHERE topic_starter='.$uid);

		/* On supprimes ses entrées relatives dans tous les endroits du forum où il pourrait intervenir */
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'log SET log_rep_user=0 WHERE log_rep_user='.$uid);
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'connected WHERE con_id='.$uid);
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'reports WHERE rep_userid='.$uid);
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value-1 WHERE st_field=\'registered_users\'');

		/* Et finalement, on supprime toutes les données utilisateur */
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'usertopics WHERE ut_userid='.$uid);
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'usertgs WHERE utg_userid='.$uid);
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_id='.$uid);

		trigger_error(str_replace('{name}',clean($_POST['delete_user'],STR_TODISPLAY),lang('user_success_deleted')),E_USER_NOTICE);
		return true;
	}
	trigger_error(lang('error_user_noexist'),E_USER_WARNING);
	return false;
}
?>