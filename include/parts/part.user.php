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
if (!defined('CB_INC')) exit('Incorrect access attempt !!');

/* On affiche une erreur si le membre n'est pas connecté */
if (!$_SESSION['cb_user']->logged)
	trigger_error(lang('error_mustlogin'),E_USER_ERROR);

$GLOBALS['cb_tpl']->lang_load('userprofile.lang');

require_once(CB_PATH.'include/lib/lib.users.php');

/* Si on édite un compte utilisateur, id à considérer */
$edit_id = null;
if (isset($_GET['editprofile']) && is_numeric($_GET['editprofile'])) {
	if ((int)$_GET['editprofile'] == $_SESSION['cb_user']->userid) $edit_id = $_SESSION['cb_user']->userid;
	elseif ($_SESSION['cb_user']->isModerator()) {
		if (isUser((int)$_GET['editprofile'])) $edit_id = (int)$_GET['editprofile'];
	} else $edit_id = $_SESSION['cb_user']->userid;
}

/* On ne peut pas éditer le profil d'un autre admin ou d'un autre modo ! */
if (!empty($edit_id) && $edit_id != $_SESSION['cb_user']->userid) {
	$r = $GLOBALS['cb_db']->query('SELECT gr_status FROM '.$GLOBALS['cb_db']->prefix.'users LEFT JOIN '.$GLOBALS['cb_db']->prefix.'groups ON gr_id=usr_class WHERE usr_id='.$edit_id);
	if ($d = $GLOBALS['cb_db']->fetch_assoc($r)) {
		if ($d['gr_status']==2 || ($d['gr_status']==1 && !$_SESSION['cb_user']->isAdmin())) 
			trigger_error(lang('usr_edit_cannot'),E_USER_ERROR);
	}
}

/* Formats acceptés pour les avatars ( != des formats supportés par GD). */
$possibleFormats = array(IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG);

/* Différentes time-zones possibles */
$timezones = getTimeZones();

/**
*	Note sur la gestion des avatars :
*	- Les avatars des utilisateurs sont stockés dans le répertoire 'avatars/users'.
*	- Si l'utilisateur insère un lien vers une image:
*		si l'image est trop grande et si gd2 est activé, le script la télécharge, la redimensionne puis l'enregistre sur le ftp
*		si l'image est trop grande mais que gd2 n'est pas activé, l'avatar sera affiché comme une image distante (balise img), avec les attributs nécessaires pour qu'elle soit redimensionnée (width ou height)
*		si l'image a des dimensions correctes, elle est simplement enregistrée sur le ftp
*	- Si l'utilisateur insère un fichier:
*		si l'image est trop grande et si gd2 est activé, le script la télécharge, la redimensionne puis l'enregistre sur le ftp
*		si l'image est trop grande mais que gd2 n'est pas activé, le script génère une erreur spécifiant que l'image est trop grande
*		si l'image a des dimensions correctes, elle est simplement enregistrée sur le ftp
*	- Les avatars changés par la galerie sont copiés dans le répertoire 'avatars/users', ainsi les utilisateurs ne perdent pas leur avatar si l'original de la galerie est supprimé
*/

/* Changement de mot de passe (pas accessible par les modos ou admins) */
if (isset($_POST['passwordchange']) && $edit_id == $_SESSION['cb_user']->userid) {
	if (isset($_POST['password'],$_POST['password1'],$_POST['password2'])) {
		$ret=$GLOBALS['cb_db']->query('SELECT usr_id FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_password=\''.cbHash($_POST['password']).'\' AND usr_id='.$_SESSION['cb_user']->userid);
		if ($GLOBALS['cb_db']->fetch_assoc($ret)) {
			if ($_POST['password1']==$_POST['password2']) {
				if (verifyUserPassword($_POST['password1'])) {
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_password=\''.cbHash($_POST['password1']).'\' WHERE usr_id='.$_SESSION['cb_user']->userid);
					trigger_error(lang('usr_passsuccchanged'),E_USER_NOTICE);
				}
			} else trigger_error(lang('usr_passwrongchanged'),E_USER_WARNING);
		} else trigger_error(lang('usr_badpass'),E_USER_WARNING);
	}
/* Changement d'adresse mail (pas accessible par les modos ou admins) */
} elseif (isset($_POST['mailchange']) && $edit_id==$_SESSION['cb_user']->userid) {
	if (isset($_POST['changemail']) && !empty($_POST['changemail'])) {
		if (verifyUserMail($_POST['changemail'])) {
			$code='change';
			for ($k=0;$k<24;$k++) $code.=rand(0,9);
			$patterns=array(
				'{--mail_user_name--}'	 =>  $_SESSION['cb_user']->username,
				'{--mail_forumname--}'	 =>  $GLOBALS['cb_cfg']->config['forumname'],
				'{--mail_confirm_link--}'  =>  'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).((utf8_substr(dirname($_SERVER['PHP_SELF']),-1)!=='/')?'/':'').manage_url('index.php?act=validate&hash='.$code,'forum-validate.html?hash='.$code),
				'{--mail_forum_owner--}'   =>  $GLOBALS['cb_cfg']->config['forumowner']
				);
			$mailmsg=str_replace(array_keys($patterns),$patterns,$GLOBALS['cb_cfg']->config['mail_cm']);

			require_once(CB_PATH.'include/lib/lib.mails.php');
			if (sendMail($_POST['changemail'],str_replace('{--mail_forumname--}',$GLOBALS['cb_cfg']->config['forumname'],$GLOBALS['cb_cfg']->config['mailsubject_cm']),$mailmsg)) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_registered=\''.$code.'\',usr_email=\''.clean($_POST['changemail']).'\' WHERE usr_id='.$_SESSION['cb_user']->userid);
				redirect(manage_url('logout.php','forum-logout.html'),lang('usr_changemail_done'),5);
			} else {
				trigger_error(lang('error_sendmail'),E_USER_WARNING);
			}
		}
	}
/* Changement d'avatar par url */
} elseif (isset($_POST['avatarchange'])) {
	if (isset($_POST['avatar'])) {
		require_once(CB_PATH.'include/lib/lib.images.php');
		if (empty($_POST['avatar'])) {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_avatar=\'\' WHERE usr_id='.$edit_id);

			// Ajout de l'action dans le log
			if ($edit_id != $_SESSION['cb_user']->userid) {
				require_once(CB_PATH.'include/lib/lib.log.php');
				addLog (LOG_EDITPROFILE_AVATAR,$edit_id,'','');
			}

			trigger_error(lang('usr_noavatar'),E_USER_NOTICE);
		} elseif ($image = @getimagesize(clean($_POST['avatar'],STR_TODISPLAY))) {
			if (in_array($image[2],$possibleFormats)) {
				deleteAvatar($edit_id);
				$ok = false;
				$temp_file='avatars/temp/user'.$edit_id.'.tmp';
				$real_file='avatars/users/user'.$edit_id.'.'.time().'.'.getExtension($image[2]);
				require_once(CB_PATH.'include/lib/lib.files.php');
				$filesize = remoteFileSize(clean($_POST['avatar']));

				// Redimensionnement par GD puis enregistrement sur le ftp
				if (isGdEnabled() && ($image[0]>$GLOBALS['cb_cfg']->config['maxsize'] || $image[1]>$GLOBALS['cb_cfg']->config['maxsize'])) {
					if (in_array($image[2],getSupportedImages()) && $filesize !== false && $filesize <= 256*1024) {
						if (copy(clean($_POST['avatar']),$temp_file)) {
							resizeImage($temp_file,$image,$real_file,$GLOBALS['cb_cfg']->config['maxsize'],$GLOBALS['cb_cfg']->config['maxsize']);
							unlink($temp_file);
							chmod($real_file,0777);
							$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_avatar=\''.$real_file.'\' WHERE usr_id='.$edit_id);
							$ok = true;
							trigger_error(lang('usr_avatarsuccchanged'),E_USER_NOTICE);
						} else trigger_error(lang('usr_transferterror'),E_USER_WARNING);
					} else trigger_error(lang('usr_badformat'),E_USER_WARNING);
				}
				// Redimensionnement par le html
				elseif ($image[0]>$GLOBALS['cb_cfg']->config['maxsize'] || $image[1]>$GLOBALS['cb_cfg']->config['maxsize']) {
					$att='';
					if ($image[0] <= $image[1]) $att='|h';
					else $att='|w';
					$avatar=clean($_POST['avatar']).$att;
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_avatar=\''.$avatar.'\' WHERE usr_id='.$edit_id);
					$ok = true;
					trigger_error(lang('usr_avatarsuccchanged'),E_USER_NOTICE);
				}
				// Pas besoin de redimensionnement
				elseif (copy(clean($_POST['avatar']),$real_file)) {
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_avatar=\''.$real_file.'\' WHERE usr_id='.$edit_id);
					$ok = true;
					trigger_error(lang('usr_avatarsuccchanged'),E_USER_NOTICE);
				} else trigger_error(lang('usr_transferterror'),E_USER_WARNING);

				// Le changement d'avatar n'a pas eu lieu, on vide le champ 'avatar' de l'utilisateur concerné
				if (!$ok) $GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_avatar=\'\' WHERE usr_id='.$edit_id);

				// Ajout de l'action dans le log
				if ($edit_id != $_SESSION['cb_user']->userid) {
					require_once(CB_PATH.'include/lib/lib.log.php');
					addLog (LOG_EDITPROFILE_AVATAR,$edit_id,'','');
				}
			} else trigger_error(lang('usr_badformat'),E_USER_WARNING);
		} else trigger_error(lang('usr_badpath'),E_USER_WARNING);
	}
/* Changement d'avatar par upload de fichier */
} elseif (isset($_POST['avatarchangefile'])) {
	if ($image=@getimagesize($_FILES['imagefile']['tmp_name'])) {
		require_once(CB_PATH.'include/lib/lib.images.php');
		if (in_array($image[2],$possibleFormats)) {
			deleteAvatar($edit_id);
			$ok = false;
			$temp_file='avatars/temp/user'.$edit_id.'.tmp';
			$real_file='avatars/users/user'.$edit_id.'.'.time().'.'.getExtension($image[2]);
			
			// Redimensionnement par GD
			if (isGdEnabled() && ($image[0]>$GLOBALS['cb_cfg']->config['maxsize'] || $image[1]>$GLOBALS['cb_cfg']->config['maxsize'])) {
				if (in_array($image[2],getSupportedImages()) && $_FILES['imagefile']['size'] <= 256*1024) {
					if (@move_uploaded_file($_FILES['imagefile']['tmp_name'],$temp_file)) {
						resizeImage($temp_file,$image,$real_file,$GLOBALS['cb_cfg']->config['maxsize'],$GLOBALS['cb_cfg']->config['maxsize']);
						unlink($temp_file);
						chmod($real_file,0777);

						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_avatar=\''.$real_file.'\' WHERE usr_id='.$edit_id);
						$ok = true;
						trigger_error(lang('usr_avatarsuccchanged'),E_USER_NOTICE);
					} else trigger_error(lang('usr_transferterror'),E_USER_WARNING);
				} else trigger_error(lang('usr_badformat'),E_USER_WARNING);
			// L'image est trop grande et gd n'est pas activé -> erreur
			} elseif ($image[0]>$GLOBALS['cb_cfg']->config['maxsize'] || $image[1]>$GLOBALS['cb_cfg']->config['maxsize']) {
				trigger_error(lang('usr_imagetoobig'),E_USER_WARNING);
			// On prend l'image telle quelle
			} else {
				if (@move_uploaded_file($_FILES['imagefile']['tmp_name'],$real_file)) {
					chmod($real_file,0777);
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_avatar=\''.$real_file.'\' WHERE usr_id='.$edit_id);
					$ok = true;
					trigger_error(lang('usr_avatarsuccchanged'),E_USER_NOTICE);
				} else trigger_error(lang('usr_transferterror'),E_USER_WARNING);
			}

			// Le changement d'avatar n'a pas eu lieu
			if (!$ok) $GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_avatar=\'\' WHERE usr_id='.$edit_id);

			// Ajout de l'action dans le log
			if ($edit_id != $_SESSION['cb_user']->userid) {
				require_once(CB_PATH.'include/lib/lib.log.php');
				addLog (LOG_EDITPROFILE_AVATAR,$edit_id,'','');
			}
		} else trigger_error(lang('usr_badformat'),E_USER_WARNING);
	} else trigger_error(lang('usr_badpath'),E_USER_WARNING);
/* Changement d'avatar par la galerie */
} elseif (isset($_GET['gallery'])) {
	if (file_exists('avatars/gallery/'.basename(clean($_GET['gallery'])))) {
		require_once(CB_PATH.'include/lib/lib.images.php');
		deleteAvatar($edit_id);
		$image = getimagesize('avatars/gallery/'.basename(clean($_GET['gallery'])));
		$path_name = 'avatars/users/user'.$edit_id.'.'.getExtension($image[2]);
		if (copy('avatars/gallery/'.basename(clean($_GET['gallery'])),$path_name)) {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_avatar=\''.$path_name.'\' WHERE usr_id='.$edit_id);
			trigger_error(lang('usr_avatarsuccchanged'),E_USER_NOTICE);
		} else {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_avatar=\'\' WHERE usr_id='.$edit_id);
			trigger_error(lang('usr_transferterror'),E_USER_WARNING);
		}
		// Ajout de l'action dans le log
		if ($edit_id != $_SESSION['cb_user']->userid) {
			require_once(CB_PATH.'include/lib/lib.log.php');
			addLog (LOG_EDITPROFILE_AVATAR,$edit_id,'','');
		}
	} else trigger_error(lang('usr_badpath'),E_USER_WARNING);
/* Changement de signature */
} elseif (isset($_POST['signaturechange'])) {
	if (isset($_POST['signature'])) {
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_signature=\''.clean($_POST['signature'],STR_SIGNATURE + STR_MULTILINE + STR_PARSEBB).'\' WHERE usr_id='.$edit_id);

		// Ajout de l'action dans le log
		if ($edit_id != $_SESSION['cb_user']->userid) {
			require_once(CB_PATH.'include/lib/lib.log.php');
			addLog (LOG_EDITPROFILE_SIGNATURE,$edit_id,'','');
		}

		trigger_error(lang('usr_signsuccchanged'),E_USER_NOTICE);
	}
/* Changement des infos générales */
} elseif ( isset($_POST['changeinfos']) ) {
	if ( isset($_POST['msn'],$_POST['icq'],$_POST['aim'],$_POST['yahoo'],$_POST['publicemail'],$_POST['place'],$_POST['presentation'],$_POST['website'],$_POST['allowmm'],$_POST['mailmp']) ) {
		$day = null;
		$month = null;
		$year = null;
		if (!empty($_POST['birthdate']) && preg_match('#^[0-9]{2}-[0-9]{2}-[0-9]{4}$#',$_POST['birthdate']))
			list ($day,$month,$year) = explode('-',$_POST['birthdate']);
		
		$GLOBALS['cb_db']->query('
			UPDATE '.$GLOBALS['cb_db']->prefix.'users
			SET usr_realname=\''.clean($_POST['realname']).'\',
				usr_gender='.(($_POST['gender']=='male')?1:(($_POST['gender']=='female')?2:0)).',
				usr_birthdate=\''.clean($year).'-'.clean($month).'-'.clean($day).'\',
				usr_msn=\''.clean($_POST['msn']).'\',
				usr_icq=\''.clean($_POST['icq']).'\',
				usr_aim=\''.clean($_POST['aim']).'\',
				usr_yahoo=\''.clean($_POST['yahoo']).'\',
				usr_publicemail=\''.(($_POST['publicemail']=='yes')?'1':'0').'\',
				usr_place=\''.clean($_POST['place']).'\',
				usr_presentation=\''.clean($_POST['presentation'],STR_PARSEBB + STR_MULTILINE).'\',
				usr_website=\''.clean($_POST['website']).'\',
				usr_pref_allowmassmail='.($_POST['allowmm']=='yes'?1:0).',
				usr_pref_mailmp='.($_POST['mailmp']=='yes'?1:0).'
			WHERE usr_id='.$edit_id);

		// Ajout de l'action dans le log
		if ($edit_id != $_SESSION['cb_user']->userid) {
			require_once(CB_PATH.'include/lib/lib.log.php');
			addLog (LOG_EDITPROFILE_GENERAL,$edit_id,'','');
		}

		trigger_error(lang('usr_infossuccchanged'),E_USER_NOTICE);
	}
/* Changement des paramètres d'affichage (pas accessible par les modos ou admins) */
} elseif (isset($_POST['changeparams']) && $edit_id==$_SESSION['cb_user']->userid) {
	if ( isset($_POST['p_usrs'],$_POST['p_topics'],$_POST['p_msgs'],$_POST['p_res'],$_POST['p_skin'],$_POST['p_lang'],$_POST['p_timezone']) ) {
		if (is_numeric($_POST['p_usrs']) && is_numeric($_POST['p_topics']) && is_numeric($_POST['p_msgs']) && is_numeric($_POST['p_res']) && isLang($_POST['p_lang']) && isSkin($_POST['p_skin'])) {
			if ((int)$_POST['p_usrs']>=5 && (int)$_POST['p_usrs']<=50 && (int)$_POST['p_topics']>=5 && (int)$_POST['p_topics']<=50 && (int)$_POST['p_msgs']>=5 && (int)$_POST['p_msgs']<=50 && (int)$_POST['p_res']>=5 && (int)$_POST['p_res']<=50 && in_array($_POST['p_timezone'],array_keys($timezones))) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users
					SET usr_pref_msgs='.(int)$_POST['p_msgs'].',
						usr_pref_usrs='.(int)$_POST['p_usrs'].',
						usr_pref_topics='.(int)$_POST['p_topics'].',
						usr_pref_res='.(int)$_POST['p_res'].',
						usr_pref_lang=\''.clean($_POST['p_lang']).'\',
						usr_pref_skin=\''.clean($_POST['p_skin']).'\',
						usr_pref_timezone=\''.$_POST['p_timezone'].'\',
						usr_pref_ctsummer='.((int)(isset($_POST['p_ctsummer']) && $_POST['p_ctsummer']=='on')).'
					WHERE usr_id='.$_SESSION['cb_user']->userid);
				$_SESSION['cb_user']->setVars();
				redirect(manage_url('index.php?act=user&editprofile='.$_SESSION['cb_user']->userid.'&page=6','forum-profile'.$_SESSION['cb_user']->userid.'-params.html'));
			}
		}
	}
}

$GLOBALS['cb_tpl']->assign('u_formaction','http://'.htmlspecialchars($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));
$GLOBALS['cb_addressbar'][] = lang('usr_userprofile');
$GLOBALS['cb_pagename'][] = lang('userprofile');

$contents=null;
if (isset($_GET['showprofile'])) {
	$_SESSION['cb_user']->connected('index_userpr_show');
	$GLOBALS['cb_addressbar'][] = lang('usr_showprofile');
	$GLOBALS['cb_pagename'][] = lang('usr_showprofile');
	
	if (!isUser((int)$_GET['showprofile']))
		trigger_error(lang('error_user_noexist'),E_USER_ERROR);
	
	$return=$GLOBALS['cb_db']->query('SELECT usr_name,usr_id,IF(usr_publicemail,usr_email,\'\') AS usr_email_ok,usr_gender,usr_birthdate,usr_realname,usr_lastconnect,usr_website,usr_registertime,usr_nbmess,usr_class,gr_name,gr_status,usr_website,usr_msn,usr_icq,usr_aim,usr_yahoo,usr_place,usr_presentation,usr_avatar,usr_signature,con_timestamp,con_position
		FROM '.$GLOBALS['cb_db']->prefix.'users
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'groups ON gr_id=usr_class
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'connected ON con_id=usr_id AND con_timestamp>'.(time()-($GLOBALS['cb_cfg']->config['connectedlimit']*60)).'
		WHERE usr_id='.(int)$_GET['showprofile']);
	$userpr= $GLOBALS['cb_db']->fetch_assoc($return);
	if (isset($userpr['con_position'])) {
		$pos = getUserLocation ($userpr['con_position']);
	}
	$GLOBALS['cb_tpl']->assign(array(
		'u_id'				=> (int)$_GET['showprofile'],
		'u_name' 			=> $userpr['usr_name'],
		'u_gender' 			=> $userpr['usr_gender'],
		'u_birthdate' 		=> getMyDate($userpr['usr_birthdate']),
		'u_realname' 		=> $userpr['usr_realname'],
		'u_status'			=> $userpr['gr_status'],
		'u_mplink' 			=> manage_url('index.php?act=mp&amp;sub=3&amp;mpto='.$userpr['usr_id'],'forum-mp-write.html?mpto='.$userpr['usr_id']),
		'u_www' 			=> ((!empty($userpr['usr_website']))?((!preg_match('`^(http|ftp)://(.+?)$`',$userpr['usr_website']))?'http://':'').$userpr['usr_website']:''),
		'u_regtime' 		=> dateFormat($userpr['usr_registertime']),
		'u_regtimestamp' 	=> $userpr['usr_registertime'],
		'u_lastconnect' 	=> dateFormat($userpr['usr_lastconnect']),
		'u_lastaction'		=> ((isset($userpr['con_position']))?'ttl_'.$pos['position']:''),
		'u_lastaction_f'	=> ((isset($userpr['con_position']))?$pos['f']:''),
		'u_lastaction_tg'	=> ((isset($userpr['con_position']))?$pos['tg']:''),
		'u_lastaction_time'	=> ((isset($userpr['con_position']))?dateFormat($userpr['con_timestamp']):''),
		'u_nbposts' 		=> $userpr['usr_nbmess'],
		'u_class' 			=> $userpr['gr_name'],
		'u_classimage' 		=> ((file_exists('skins/'.$_SESSION['cb_user']->getPreferredSkin().'/class'.$userpr['usr_class'].'.jpg'))?'<img src="skins/'.$_SESSION['cb_user']->getPreferredSkin().'/class'.$userpr['usr_class'].'.jpg" alt="'.$userpr['usr_class'].'" width="140" />':''),
		'u_mail' 			=> $userpr['usr_email_ok'],
		'u_msn' 			=> ((!empty($userpr['usr_msn']))?$userpr['usr_msn']:''),
		'u_icq' 			=> ((!empty($userpr['usr_icq']))?$userpr['usr_icq']:''),
		'u_aim' 			=> ((!empty($userpr['usr_aim']))?$userpr['usr_aim']:''),
		'u_yahoo' 			=> ((!empty($userpr['usr_yahoo']))?$userpr['usr_yahoo']:''),
		'u_place' 			=> ((!empty($userpr['usr_place']))?$userpr['usr_place']:''),
		'u_pres' 			=> ((!empty($userpr['usr_presentation']))?$userpr['usr_presentation']:''),
		'u_avatar' 			=> ((!empty($userpr['usr_avatar']))?getAvatar($userpr['usr_avatar']):''),
		'u_sign' 			=> ((!empty($userpr['usr_signature']))?$userpr['usr_signature']:''),
		'u_mod'				=> ((($_SESSION['cb_user']->isModerator() && $userpr['gr_status']==0) || ($_SESSION['cb_user']->isAdmin() && $userpr['gr_status']<=1))?true:false),
		'u_subpart'			=> 'showprofile'
		));
} elseif (!empty($edit_id)) {
	$_SESSION['cb_user']->connected('index_userpr_edit');
	$GLOBALS['cb_addressbar'][] = lang('usr_editprofile');
	$GLOBALS['cb_pagename'][] = lang('usr_editprofile');
	
	$pagenumber=(isset($_GET['page']) && $_GET['page']<=6 && $_GET['page']>0)?(int)$_GET['page']:1;
	if ($edit_id != $_SESSION['cb_user']->userid && isset($_GET['page']) && ($_GET['page']==2 || $_GET['page']==3 || $_GET['page']==6)) $pagenumber = 1;

	$u_menu = array(
		'title' => 'usr_command',
		'currentpage' => $pagenumber,
		'url' => manage_url('index.php?act=user&amp;editprofile='.$edit_id.'&amp;page=[num_page]','forum-profile'.$edit_id.'-[num_page].html'),
		'items' => array(
			array('id' => manage_url(1,'general') , 'cid' => 1, 'title' => 'usr_part_general'),
			array('id' => manage_url(4,'avatar') , 'cid' => 4, 'title' => 'usr_part_avatar'),
			array('id' => manage_url(5,'signature') , 'cid' => 5, 'title' => 'usr_part_signature')
			)
		);
	if ($edit_id == $_SESSION['cb_user']->userid) {
		$u_menu['items'][] = array('id' => manage_url(2,'changemail') , 'cid' => 2, 'title' => 'usr_part_changemail');
		$u_menu['items'][] = array('id' => manage_url(3,'changepass') , 'cid' => 3, 'title' => 'usr_part_changepass');
		$u_menu['items'][] = array('id' => manage_url(6,'params') , 'cid' => 6, 'title' => 'usr_part_params');
	}
	$GLOBALS['cb_tpl']->assign('u_menu',$u_menu);
	$GLOBALS['cb_tpl']->assign('u_subpart','editprofile');

	if ($edit_id != $_SESSION['cb_user']->userid)
		trigger_error(str_replace('{name}',getUserName($edit_id),lang('usr_editing_other')),E_USER_NOTICE);

	if ($pagenumber==1) { // Informations générales
		$GLOBALS['cb_tpl']->assign('u_title','usr_part_general');
		$return= $GLOBALS['cb_db']->query('SELECT * FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_id='.$edit_id);
		$userpr= $GLOBALS['cb_db']->fetch_assoc($return);
		$GLOBALS['cb_tpl']->assign(array(
			'u_name_link' => '<a href="'.manage_url('index.php?act=user&amp;showprofile='.$userpr['usr_id'],'forum-m'.$userpr['usr_id'].','.rewrite_words($userpr['usr_name']).'.html').'">'.$userpr['usr_name'].'</a>',
			'u_posts_number' => $userpr['usr_nbmess'],
			'u_gender' => $userpr['usr_gender'],
			'u_birthdate' => getMyDate($userpr['usr_birthdate']),
			'u_realname' => $userpr['usr_realname'],
			'u_msn' => $userpr['usr_msn'],
			'u_icq' => $userpr['usr_icq'],
			'u_aim' => $userpr['usr_aim'],
			'u_yahoo' => $userpr['usr_yahoo'],
			'u_mail' => $userpr['usr_email'],
			'u_pmail_yes_checked' => (($userpr['usr_publicemail']=='1')?'selected="selected"':''),
			'u_pmail_no_checked' => (($userpr['usr_publicemail']=='0')?'selected="selected"':''),
			'u_allowmm_yes_checked' => (($userpr['usr_pref_allowmassmail']=='1')?'selected="selected"':''),
			'u_allowmm_no_checked' => (($userpr['usr_pref_allowmassmail']=='0')?'selected="selected"':''),
			'u_mailmp_yes_checked' => (($userpr['usr_pref_mailmp']=='1')?'selected="selected"':''),
			'u_mailmp_no_checked' => (($userpr['usr_pref_mailmp']=='0')?'selected="selected"':''),
			'u_place' => $userpr['usr_place'],
			'u_pres' => unClean($userpr['usr_presentation']),
			'u_website' => $userpr['usr_website'],
			'u_contents' => 'general'
			));
	} elseif ($pagenumber==2 && $edit_id==$_SESSION['cb_user']->userid) { // Changement d'adresse mail
		$GLOBALS['cb_tpl']->assign('u_title','usr_part_changemail');
		$GLOBALS['cb_tpl']->assign('u_contents','changemail');
	} elseif ($pagenumber==3 && $edit_id==$_SESSION['cb_user']->userid) { // Changement de mot de passe
		$GLOBALS['cb_tpl']->assign('u_title','usr_part_changepass');
		$GLOBALS['cb_tpl']->assign('u_contents','changepass');
	} elseif ($pagenumber==4) { // Changement d'avatar
		$avat = $GLOBALS['cb_db']->single_result('SELECT usr_avatar FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_id='.$edit_id);
		$GLOBALS['cb_tpl']->assign('u_title','usr_part_avatar');
		$GLOBALS['cb_tpl']->assign('u_avatar_link',((!empty($avat))?getAvatar($avat):''));
		$GLOBALS['cb_tpl']->assign('u_avatar_gallery',getGallery());
		$GLOBALS['cb_tpl']->assign('u_contents','changeavatar');
	} elseif ($pagenumber==5) { // Changement de signature
		$sign = $GLOBALS['cb_db']->single_result('SELECT usr_signature FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_id='.$edit_id);
		$GLOBALS['cb_tpl']->assign('u_title','usr_part_signature');
		$GLOBALS['cb_tpl']->assign('u_signature_link',((!empty($sign))?$sign:''));
		$GLOBALS['cb_tpl']->assign('u_sign_bb_forbidden',array_filter(explode('|',$GLOBALS['cb_cfg']->config['bb_sign_forbidden'])));
		$GLOBALS['cb_tpl']->assign('u_sign',unclean($sign));
		$GLOBALS['cb_tpl']->assign('u_contents','changesign');
	} elseif ($pagenumber==6 && $edit_id==$_SESSION['cb_user']->userid) { // Changement des paramètres d'affichage
		$GLOBALS['cb_tpl']->assign('u_title','usr_part_params');
		$GLOBALS['cb_tpl']->assign('u_params_usrs',$_SESSION['cb_user']->usr_pref_usrs);
		$GLOBALS['cb_tpl']->assign('u_params_topics',$_SESSION['cb_user']->usr_pref_topics);
		$GLOBALS['cb_tpl']->assign('u_params_msgs',$_SESSION['cb_user']->usr_pref_msgs);
		$GLOBALS['cb_tpl']->assign('u_params_res',$_SESSION['cb_user']->usr_pref_res);
		$GLOBALS['cb_tpl']->assign('u_timezone',$timezones);
		$GLOBALS['cb_tpl']->assign('u_params_timezone',$_SESSION['cb_user']->usr_pref_timezone);
		$GLOBALS['cb_tpl']->assign('u_params_ctsummer',$_SESSION['cb_user']->usr_pref_ctsummer);
		$GLOBALS['cb_tpl']->assign('u_params_skin',skinMenu('p_skin',$_SESSION['cb_user']->getPreferredSkin()));
		$GLOBALS['cb_tpl']->assign('u_params_lang',langMenu('p_lang',$_SESSION['cb_user']->getPreferredLang()));
		$GLOBALS['cb_tpl']->assign('u_contents','changeparams');
	}
}
$GLOBALS['cb_tpl']->assign('g_part','part_userprofile.php');
?>