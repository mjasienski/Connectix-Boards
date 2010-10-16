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
define('CB_INC', 'CB');
require('common.php');

$GLOBALS['cb_tpl']->lang_load('ajax.lang');

/**
* Toutes les fonctions de type AJAX nécessaires à CB se trouvent ici!
*/
switch ($_POST['request']) {
	// Renvoie un message prêt pour édition
	case 'msg_unclean':
		if ($_SESSION['cb_user']->logged) {
			$msgquery = $GLOBALS['cb_db']->query('SELECT 
					msg_id,msg_message,msg_userid,
					topic_id,topic_fromtopicgroup,topic_status
				FROM '.$GLOBALS['cb_db']->prefix.'messages 
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topics ON msg_topicid = topic_id
				WHERE msg_id='.(int)$_POST['value']);
			
			if ($msgdata = $GLOBALS['cb_db']->fetch_assoc($msgquery)) {
				if ($_SESSION['cb_user']->getAuth('reply',$msgdata['topic_fromtopicgroup'])) {
					if ($msgdata['msg_userid']==$_SESSION['cb_user']->userid && $msgdata['topic_status']!=1 || $_SESSION['cb_user']->isMod($msgdata['topic_fromtopicgroup'])) {
						$GLOBALS['cb_tpl']->assign('msg_contents',unclean($msgdata['msg_message']));
						$GLOBALS['cb_tpl']->assign('msg_id',$msgdata['msg_id']);
						$GLOBALS['cb_tpl']->assign('topic_id',$msgdata['topic_id']);
						
						$GLOBALS['cb_tpl']->display('menu_quickedit.php');
					} else echo '0-'.lang('warning').' - '.lang('error_wm_noeditright');
				} else echo '0-'.lang('warning').' - '.lang('error_wm_nowriteright');
			}  else echo '0-'.lang('warning').' - '.lang('error_wm_notopic');
		} else echo '0-'.lang('warning').' - '.lang('error_mustlogin');
		break;
	
	// Edite un message
	case 'msg_edit':
		if ($_SESSION['cb_user']->logged) {
			$msgquery = $GLOBALS['cb_db']->query('SELECT msg_userid, topic_fromtopicgroup, topic_id
				FROM '.$GLOBALS['cb_db']->prefix.'messages 
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topics ON msg_topicid = topic_id
				WHERE msg_id='.(int)$_POST['value']);
			
			if ($msgdata = $GLOBALS['cb_db']->fetch_assoc($msgquery)) {
				if ($_SESSION['cb_user']->getAuth('reply',$msgdata['topic_fromtopicgroup'])) {
					if ($msgdata['msg_userid']==$_SESSION['cb_user']->userid && $msgdata['topic_status']!=1 || $_SESSION['cb_user']->isMod($msgdata['topic_fromtopicgroup'])) {
						if (isset($_POST['message']) && utf8_strlen(trim($_POST['message']))>=1) {
							require(CB_PATH.'include/lib/lib.writing.php');
							$write = array(
								'wmessage' => $_POST['message'],
								'toedit' => (int)$_POST['value'],
								'towrite' => 'editmessage',
								'towriteid' => $msgdata['topic_id'],
								'edit_show' => true
								);
							
							writeMessage($write,false);
							echo '1-'.clean($_POST['message'], STR_MULTILINE + STR_PARSEBB + STR_TODISPLAY);
						} else echo '0-'.lang('warning').' - '.lang('error_wm_nomessage');
					} else echo '0-'.lang('warning').' - '.lang('error_wm_noeditright');
				} else echo '0-'.lang('warning').' - '.lang('error_wm_nowriteright');
			}  else echo '0-'.lang('warning').' - '.lang('error_wm_notopic');
		} else echo '0-'.lang('warning').' - '.lang('error_mustlogin');
		break;
	
	// Enregistrement - Vérification du nom d'utilisateur
	case 'reg_login':
		if (isset($_POST['value'])) {
			require_once(CB_PATH.'include/lib/lib.users.php');
			
			$tocheck = trim(utf8_js_decode($_POST['value']));
			
			if (verifyUserName($tocheck))
				echo '1-'.lang('r_login_ok');
			else
				echo '0-'.$GLOBALS['cb_warn'][0]['str'];
		} else 
			echo '0-'.lang('error_reg_namelength');
		break;
	
	// Enregistrement - Vérification du mot de passe
	case 'reg_password':
		if (isset($_POST['pass1'],$_POST['pass2'])) {
			require_once(CB_PATH.'include/lib/lib.users.php');
			
			if ($_POST['pass1']==$_POST['pass2'] && verifyUserPassword($_POST['pass1']))
				echo '1-'.lang('r_password_ok');
			elseif ($_POST['pass1']!=$_POST['pass2'])
				echo '0-'.lang('error_reg_mispasswords');
			else 
				echo '0-'.$GLOBALS['cb_warn'][0]['str'];
		} else 
			echo '0-'.lang('error_reg_passwordlength');
		break;
	
	// Enregistrement - Vérification de l'adresse mail
	case 'reg_mail':
		if (isset($_POST['value'])) {
			require_once(CB_PATH.'include/lib/lib.users.php');
			
			$tocheck = trim(utf8_js_decode($_POST['value']));
			
			if (verifyUserMail($tocheck))
				echo '1-'.lang('r_mail_ok');
			else
				echo '0-'.$GLOBALS['cb_warn'][0]['str'];
		} else 
			echo '0-'.lang('error_reg_nomail');
		break;
	
	// Enregistrement - Chargement d'un nouveau code captcha
	case 'new_captcha':
		require_once(CB_PATH.'include/lib/lib.images.php');
		echo '1-'.getCaptcha();
		break;
	
	// Mise d'un sujet en favoris
	case 't_bookmark':
		if ($_SESSION['cb_user']->logged) {
			if (((int)$_POST['bookmark']==1 || (int)$_POST['bookmark']==0) && isTopic((int)$_POST['topicid'])) {
				$check_line = $GLOBALS['cb_db']->single_result('SELECT 1 FROM '.$GLOBALS['cb_db']->prefix.'usertopics WHERE ut_userid='.$_SESSION['cb_user']->userid.' AND ut_topicid='.(int)$_POST['topicid']);
				if ($check_line) 
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'usertopics SET ut_bookmark='.(int)$_POST['bookmark'].' WHERE ut_userid='.$_SESSION['cb_user']->userid.' AND ut_topicid='.(int)$_POST['topicid']);
				else 
					$GLOBALS['cb_db']->query('INSERT DELAYED INTO '.$GLOBALS['cb_db']->prefix.'usertopics(ut_userid,ut_topicid,ut_bookmark) VALUES('.$_SESSION['cb_user']->userid.','.(int)$_POST['topicid'].','.(int)$_POST['bookmark'].')');
				
				$GLOBALS['cb_tpl']->lang_load('topic.lang');
				if ((int)$_POST['bookmark']==1)
					echo '1-'.lang('t_bookmarked');
				else
					echo '1-'.lang('t_notbookmarked');
			} else echo '0-'.lang('warning').' - '.lang('error_wrongrequest');
		} else echo '0-'.lang('warning').' - '.lang('error_mustlogin');
		break;
		
	// Suivre un sujet par mail
	case 't_track':
		if ($_SESSION['cb_user']->logged) {
			if (((int)$_POST['track']==1 || (int)$_POST['track']==0) && isTopic((int)$_POST['topicid'])) {
				$check_line = $GLOBALS['cb_db']->single_result('SELECT 1 FROM '.$GLOBALS['cb_db']->prefix.'usertopics WHERE ut_userid='.$_SESSION['cb_user']->userid.' AND ut_topicid='.(int)$_POST['topicid']);
				if ($check_line) 
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'usertopics SET ut_mail='.(int)$_POST['track'].' WHERE ut_userid='.$_SESSION['cb_user']->userid.' AND ut_topicid='.(int)$_POST['topicid']);
				else 
					$GLOBALS['cb_db']->query('INSERT DELAYED INTO '.$GLOBALS['cb_db']->prefix.'usertopics(ut_userid,ut_topicid,ut_mail) VALUES('.$_SESSION['cb_user']->userid.','.(int)$_POST['topicid'].','.(int)$_POST['track'].')');
				
				$GLOBALS['cb_tpl']->lang_load('topic.lang');
				if ((int)$_POST['track']==1)
					echo '1-'.lang('t_tracked');
				else
					echo '1-'.lang('t_nottracked');
			} else echo '0-'.lang('warning').' - '.lang('error_wrongrequest');
		} else echo '0-'.lang('warning').' - '.lang('error_mustlogin');
		break;
	
	// Menu étendu de smileys
	case 'extendedsmilies':
		echo '1-'.getSmileyMenu(clean($_POST['taid'],STR_TODISPLAY),true);
		break;
	
	// Erreur, aucun code de requète correspondant...
	default:
		echo '0-'.lang('warning').' - '.lang('error_wrongrequest');
}
?>