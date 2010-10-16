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

//// Gestion des fonctions modérateurs ////

/* Fonction qui renvoie le menu de modération (à afficher dans les sujets...). */
function getModMenu($topic_id,$topic_type,$topic_status,$topic_name,$topic_comment,$topicgroup_id) {
	if (!$_SESSION['cb_user']->isMod($topicgroup_id)) return '';

	$GLOBALS['cb_tpl']->assign(array(
		'mm_type' => $topic_type,
		'mm_status' => $topic_status,
		'mm_topicid' => $topic_id,
		'mm_topicname' => $topic_name,
		'mm_topiccomment' => $topic_comment,
		'mm_topicgroupmenu' => showForumMenu('newtg','mod_displacetopic_choose',0,0,0,$topicgroup_id,true),
		'mm_automess' => autoMessages('am_id'),
		'mm_delete_ok' => (($topic_type != 2 && $GLOBALS['cb_cfg']->config['deleteallowed']=='yes') || $_SESSION['cb_user']->isAdmin())
		));

	return $GLOBALS['cb_tpl']->fetch('topic_modmenu.php');
}

/* Gestion des options de modification d'un sujet. */
function manageTopicModOptions ($topic) {
	if (!$_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup']))
		return false;

	$redirect = manage_url('index.php?showtopic='.$topic['topic_id'],'forum-t'.$topic['topic_id'].'.html');

	require_once(CB_PATH.'include/lib/lib.log.php');

	/* Suppression d'un message */
	if (isset($_GET['deletemessage'])) {
		$return=$GLOBALS['cb_db']->query('SELECT msg_topicid,msg_userid,topic_nbreply,topic_fromtopicgroup,topic_name
			FROM '.$GLOBALS['cb_db']->prefix.'messages
			LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topics ON topic_id=msg_topicid
			WHERE msg_id='.(int)$_GET['deletemessage'].' AND topic_id='.$topic['topic_id']);

		if ($fetch=$GLOBALS['cb_db']->fetch_array($return)) {
			if (isset($_GET['confirmdel'])) {
				$nbk = $GLOBALS['cb_db']->single_result('SELECT COUNT(msg_id)
					FROM '.$GLOBALS['cb_db']->prefix.'messages
					WHERE msg_topicid='.$topic['topic_id'].' AND msg_id<'.(int)$_GET['deletemessage']);

				if ($fetch['topic_nbreply'] > 0 && $nbk > 0) {
					if ($fetch['msg_userid'] > 0)
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_nbmess=usr_nbmess-1 WHERE usr_id='.$fetch['msg_userid'].' AND usr_nbmess > 0');

					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_nbreply=topic_nbreply-1 WHERE topic_id='.$fetch['msg_topicid']);
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_nbmess=tg_nbmess-1 WHERE tg_id = '.$fetch['topic_fromtopicgroup']);
					$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_id='.(int)$_GET['deletemessage']);
					$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'src_matches WHERE sm_msgid='.(int)$_GET['deletemessage']);
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value-1 WHERE st_field=\'total_messages\'');

					setLastMessage($topic['topic_id'],(int)$_GET['deletemessage']);
					setLastTopic($fetch['topic_fromtopicgroup']);

					addLog( LOG_DELETEMESS,$fetch['msg_userid'],$topic['topic_id'],'' );

					redirect(manage_url('index.php?showtopic='.$fetch['msg_topicid'],'forum-t'.$fetch['msg_topicid'].','.rewrite_words($fetch['topic_name']).'.html'));
				} else redirect(manage_url('index.php?showtopic='.$fetch['msg_topicid'],'forum-t'.$fetch['msg_topicid'].','.rewrite_words($fetch['topic_name']).'.html'));
			} else message(lang(array('item' => 'mod_conf_del','yes' => manage_url('index.php?showtopic='.$topic['topic_id'].'&amp;deletemessage='.(int)$_GET['deletemessage'].'&amp;confirmdel=1','forum-t'.$topic['topic_id'].'.html?deletemessage='.(int)$_GET['deletemessage'].'&amp;confirmdel=1'),'no' => manage_url('index.php?showtopic='.$topic['topic_id'],'forum-t'.$topic['topic_id'].','.rewrite_words($topic['topic_name']).'.html'))),E_USER_WARNING);
		} else redirect();
	/* Actions sur le sujet - Multi modération */
	} elseif (isset($_POST['mod_submit'])) {
		// Epingler
		if (isset($_POST['select_setpinned']) && $_POST['select_setpinned'] == 'on') {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_type=1 WHERE topic_type=0 AND topic_id='.$topic['topic_id']);
			addLog( LOG_PINTOPIC,'',$topic['topic_id'],'' );
		}
		// Retirer l'épingle
		if (isset($_POST['select_unsetpinned']) && $_POST['select_unsetpinned'] == 'on') {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_type=0 WHERE topic_type=1 AND topic_id='.$topic['topic_id']);
			addLog( LOG_UNPINTOPIC,'',$topic['topic_id'],'' );
		}
		// Fermer le sujet
		if (isset($_POST['select_closetopic']) && $_POST['select_closetopic'] == 'on') {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_status=1 WHERE topic_status=0 AND topic_type!=2 AND topic_id='.$topic['topic_id']);
			addLog( LOG_CLOSETOPIC,'',$topic['topic_id'],'' );
		}
		// Réouvrir le sujet
		if (isset($_POST['select_opentopic']) && $_POST['select_opentopic'] == 'on') {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_status=0 WHERE topic_status=1 AND topic_type!=2 AND topic_id='.$topic['topic_id']);
			addLog( LOG_OPENTOPIC,'',$topic['topic_id'],'' );
		}
		// Retirer le statut d'annonce
		if (isset($_POST['select_removeannounce']) && $_POST['select_removeannounce'] == 'on') {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_type=0,topic_status=1 WHERE topic_id='.$topic['topic_id'].' AND topic_type=2');
			addLog( LOG_UNANNOUNCETOPIC,'',$topic['topic_id'],'' );
		}
		// Changer le titre et le commentaire du sujet
		if (isset($_POST['select_changetitle']) && $_POST['select_changetitle'] == 'on') {
			if (!empty($_POST['newtitle']) && isset($_POST['newcomment'])) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_name=\''.clean($_POST['newtitle']).'\',topic_comment=\''.clean($_POST['newcomment']).'\' WHERE topic_id='.$topic['topic_id']);
				addLog( LOG_CHANGETOPICTITLE,'',$topic['topic_id'],'' );
			}
		}
		// Déplacer le sujet
		if (isset($_POST['select_displacetopic']) && $_POST['select_displacetopic'] == 'on') {
			if (!empty($_POST['newtg']) && preg_match('#^tg_[0-9]+$#',$_POST['newtg']) && isTg(str_replace('tg_','',$_POST['newtg']))) {
				displaceTopic($topic['topic_id'],str_replace('tg_','',$_POST['newtg']),(isset($_POST['leavetrace']) && $_POST['leavetrace']=='on'));
			}
		}
		// Messages automatiques
		if (isset($_POST['select_automessage']) && $_POST['select_automessage'] == 'on') {
			$ret=$GLOBALS['cb_db']->query('SELECT am_message FROM '.$GLOBALS['cb_db']->prefix.'automessages WHERE am_id='.(int)$_POST['am_id']);
			if ($data=$GLOBALS['cb_db']->fetch_assoc($ret)) {
				$write = array(
					'wmessage' => unclean($data['am_message'],STR_REMOVESPECIALCHARS)."\n\n[b][".lang('automessage_warn').'][/b]',
					'towrite' => 'addmessage',
					'towriteid' => $topic['topic_id'],
					'redirect' => 'message'
					);
				require_once(CB_PATH.'include/lib/lib.writing.php');
				writeMessage($write);
			}
		}

		redirect($redirect);
	/* Déplacement de sujets */
	} elseif (isset($_POST['mod_msgs_submit'])) {
		require_once(CB_PATH.'include/lib/lib.structure.php');
		
		// Filtre les ids
		$sel_msgs = array();
		foreach ($_POST['selectmsg'] as $msg)
			$sel_msgs[] = (int)$msg;
		
		// Vérifie que les ids sont valides
		$return = $GLOBALS['cb_db']->query('SELECT msg_id FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_id IN ('.implode(',',$sel_msgs).') AND msg_topicid = '.$topic['topic_id']);
		$val_msgs = array();
		while ($data = $GLOBALS['cb_db']->fetch_assoc($return))
			$val_msgs[] = $data['msg_id'];
		
		sort($val_msgs, SORT_NUMERIC);
		reset($val_msgs);
		$firstmsg = current($val_msgs);
		end($val_msgs);
		$lastmsg = current($val_msgs);
		
		if (count($val_msgs) > 0) {
			if ($_POST['select_displace'] == 'new') {
				if (utf8_strlen(trim($_POST['mod_newtopic'])) > 2) {
					$ttitle = trim($_POST['mod_newtopic']);
					$ttg = (int)substr($_POST['mod_newtopic_tg'],3);
					
					if (isTg($ttg) && $_SESSION['cb_user']->isMod($ttg)) {
						// Récupération de certaines données du premier message
						$return = $GLOBALS['cb_db']->query('SELECT msg_userid, msg_guest FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_id = '.$firstmsg);
						$data = $GLOBALS['cb_db']->fetch_assoc($return);
						
						// Création du sujet
						$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'topics(topic_name,topic_starter,topic_guest,topic_type,topic_status,topic_fromtopicgroup,topic_nbreply,topic_lastmessage)
							VALUES(\''.clean($ttitle).'\','.$data['msg_userid'].',\''.$data['msg_guest'].'\',0,0,'.$ttg.','.(count($val_msgs) - 1).','.$lastmsg.')');
						$newtopicid=$GLOBALS['cb_db']->insert_id();
						
						// Déplacement des messages dans le nouveau sujet
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'messages SET msg_topicid = '.$newtopicid.' WHERE msg_id IN ('.implode(',',$val_msgs).')');
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'src_matches SET sm_topicid = '.$newtopicid.' WHERE sm_msgid IN ('.implode(',',$val_msgs).')');
						
						// Comptes des groupes de sujets
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_nbmess=tg_nbmess-'.count($val_msgs).' WHERE tg_id = '.$topic['topic_fromtopicgroup']);
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_nbmess=tg_nbmess+'.count($val_msgs).' WHERE tg_id = '.$ttg);
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_nbtopics=tg_nbtopics+1 WHERE tg_id = '.$ttg);
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value-1 WHERE st_field=\'total_topics\'');
						
						// Modification du sujet courant
						$deleted = topic_consistency($topic['topic_id']);
						
						setLastTopic($ttg);
						
						if ($deleted)
							trigger_error(lang('mod_t_displace_success_deleted'),E_USER_ERROR);
						else
							trigger_error(lang('mod_t_displace_success'),E_USER_NOTICE);
						
					} else trigger_error(lang('mod_t_displace_destnotmod'),E_USER_WARNING);
				} else trigger_error(lang('mod_t_displace_topicnametooshort'),E_USER_WARNING);
			} elseif ($_POST['select_displace'] == 'existing') {
				if (isTopic((int)$_POST['mod_existingtopic'])) {
					// Id du sujet de destination
					$newtopicid = (int)$_POST['mod_existingtopic'];
					$ttg = $GLOBALS['cb_db']->single_result('SELECT topic_fromtopicgroup FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_id = '.$newtopicid);
					
					if ($_SESSION['cb_user']->isMod($ttg)) {
						// Déplacement des messages dans le sujet de destination
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'messages SET msg_topicid = '.$newtopicid.' WHERE msg_id IN ('.implode(',',$val_msgs).')');
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'src_matches SET sm_topicid = '.$newtopicid.' WHERE sm_msgid IN ('.implode(',',$val_msgs).')');
						
						// Comptes des groupes de sujets
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_nbmess=tg_nbmess-'.count($val_msgs).' WHERE tg_id = '.$topic['topic_fromtopicgroup']);
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_nbmess=tg_nbmess+'.count($val_msgs).' WHERE tg_id = '.$ttg);
						
						// Données qui ont changé pour le sujet courant et le sujet de destination
						$deleted = topic_consistency($topic['topic_id']);
						topic_consistency($newtopicid);
						
						if ($deleted)
							trigger_error(lang('mod_t_displace_success_deleted'),E_USER_ERROR);
						else
							trigger_error(lang('mod_t_displace_success'),E_USER_NOTICE);
						
					} else trigger_error(lang('mod_t_displace_topicnotmod'),E_USER_WARNING);
				} else trigger_error(lang('mod_t_displace_badtopicid'),E_USER_WARNING);
			} else trigger_error(lang('mod_t_displace_noaction'),E_USER_WARNING);
		} else trigger_error(lang('mod_t_displace_nomessage'),E_USER_WARNING);
	}

	return true;
}

/* Fonction qui met à jour les données d'un sujet en phase avec les messages associés - Spécifique pour le déplacement de sujets */
function topic_consistency ($topicid) {
	require_once(CB_PATH.'include/lib/lib.structure.php');
	$return = $GLOBALS['cb_db']->query('SELECT MIN(msg_id) AS msgmin, MAX(msg_id) AS msgmax, COUNT(*) AS msgcount FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_topicid = '.$topicid);
	$tdata = $GLOBALS['cb_db']->fetch_assoc($return);
	
	if ($tdata['msgcount'] == 0) {
		$ftg = $GLOBALS['cb_db']->single_result('SELECT topic_fromtopicgroup FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_id = '.$topicid);
		$poll = $GLOBALS['cb_db']->single_result('SELECT topic_poll FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_id = '.$topicid);
		
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_id = '.$topicid.' OR topic_displaced='.$topicid);
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_nbtopics=tg_nbtopics-1 WHERE tg_id IN ('.implode(',',getUpperTopicGroupsOfTg($ftg)).')');
		
		if ($poll) {
			$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'pollpossibilities WHERE poss_pollid='.$poll);
			$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'polls WHERE poll_id='.$poll);
		}
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'usertopics WHERE ut_topicid='.$topicid);
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'src_matches WHERE sm_topicid='.$topicid);
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value-1 WHERE st_field=\'total_topics\'');
		
		setLastTopic($ftg);
		
		return true;
	} else {
		$return = $GLOBALS['cb_db']->query('SELECT msg_userid, msg_guest FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_id = '.$tdata['msgmin']);
		$mdata = $GLOBALS['cb_db']->fetch_assoc($return);
	
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET 
									topic_nbreply = '.($tdata['msgcount'] - 1).', 
									topic_lastmessage='.$tdata['msgmax'].', 
									topic_starter = '.$mdata['msg_userid'].', 
									topic_guest = \''.$mdata['msg_guest'].'\' 
								  WHERE topic_id = '.$topicid);
		
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'usertopics SET ut_msgread='.$tdata['msgmax'].' WHERE ut_topicid='.$topicid.' AND ut_msgread > '.$tdata['msgmax']);
		
		return false;
	}
}

/* Gestion des options de modification de sondage. */
function managePollOptions ($topic,$pagenumber) {
	if (!$_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup']) && $_SESSION['cb_user']->userid != $topic['topic_starter'])
		return false;

	/* Modifications des possibilités de sondages */
	if (isset($_POST['poll_edit'])) {
		if ($_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup']) || $_SESSION['cb_user']->userid == $topic['topic_starter']) {
			foreach ($_POST as $key => $value) {
				$matches = array();
				if (preg_match('`^poll_poss_([0-9]+)$`',$key,$matches)) {
					if (utf8_strlen(trim($value))>1) {
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'pollpossibilities poss SET poss_name=\''.clean($value,STR_PARSEBB).'\' WHERE poss_pollid='.$topic['poll_id'].' AND poss_id='.$matches[1]);
					} else trigger_error(lang('t_poll_poss_tooshort'),E_USER_WARNING);
				}
			}
			trigger_error(lang('t_poll_changedfields'),E_USER_NOTICE);
		}
	/* Ajout d'une possibilité au sondage */
	} elseif (isset($_POST['poll_addposs'])) {
		if ($_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup']) || $_SESSION['cb_user']->userid == $topic['topic_starter']) {
			if (utf8_strlen(trim($_POST['poll_newposs']))>1) {
				$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'pollpossibilities(poss_pollid,poss_name,poss_votes) VALUES('.$topic['poll_id'].',\''.clean($_POST['poll_newposs'],STR_PARSEBB).'\',0)');
				trigger_error(lang('t_poll_poss_added'),E_USER_NOTICE);
			} else trigger_error(lang('t_poll_poss_tooshort'),E_USER_WARNING);
		}
	/* Suppression d'une possibilité au sondage */
	} elseif (isset($_GET['deleteposs']) && is_numeric($_GET['deleteposs'])) {
		if ($_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup']) || $_SESSION['cb_user']->userid == $topic['topic_starter']) {
			$poss_votes = $GLOBALS['cb_db']->single_result('SELECT poss_votes FROM '.$GLOBALS['cb_db']->prefix.'pollpossibilities WHERE poss_pollid='.$topic['poll_id'].' AND poss_id='.(int)$_GET['deleteposs']);
			$nb_poss = $GLOBALS['cb_db']->single_result('SELECT COUNT(*) FROM '.$GLOBALS['cb_db']->prefix.'pollpossibilities WHERE poss_pollid='.$topic['poll_id']);
			if ($nb_poss>2) {
				$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'pollpossibilities WHERE poss_id='.(int)$_GET['deleteposs']);
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'polls SET poll_totalvotes=poll_totalvotes-'.$poss_votes.' WHERE poll_id='.$topic['poll_id']);
			}
			redirect(manage_url('index.php?showtopic='.$topic['topic_id'].'&page='.$pagenumber.'&editpoll=1','forum-t'.$topic['topic_id'].'-p'.$pagenumber.'-editpoll.html'));
		}
	}
	return true;
}
	
/* Gestion des options de modération de masse d'un groupe de sujets. */
function manageTgModOptions ($tgid) {
	if (isset($_POST['tgmod'])) {
		$selected = array_filter($_POST['tgmod'],'ctype_digit');
		if (count($selected) > 0) {
			$log = null;
			require_once(CB_PATH.'include/lib/lib.log.php');
			if (isset($_POST['mod_open'])) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_status=0 WHERE topic_status=1 AND topic_type!=2 AND topic_fromtopicgroup = '.$tgid.' AND topic_id IN ('.implode(',',$selected).')');
				$log = LOG_OPENTOPIC;
			} elseif (isset($_POST['mod_close'])) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_status=1 WHERE topic_status=0 AND topic_type!=2 AND topic_fromtopicgroup = '.$tgid.' AND topic_id IN ('.implode(',',$selected).')');
				$log = LOG_CLOSETOPIC;
			} elseif (isset($_POST['mod_pin'])) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_type=1 WHERE topic_type=0 AND topic_fromtopicgroup = '.$tgid.' AND topic_id IN ('.implode(',',$selected).')');
				$log = LOG_PINTOPIC;
			} elseif (isset($_POST['mod_unpin'])) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_type=0 WHERE topic_type=1 AND topic_fromtopicgroup = '.$tgid.' AND topic_id IN ('.implode(',',$selected).')');
				$log = LOG_UNPINTOPIC;
			} elseif (isset($_POST['mod_disp'],$_POST['mod_displace']) && isTg((int)$_POST['mod_disp'])) {
				foreach ($selected as $tid) displaceTopic($tid,(int)$_POST['mod_disp']);
			} elseif (isset($_POST['mod_delete']) && ($GLOBALS['cb_cfg']->config['deleteallowed']=='yes' || $_SESSION['cb_user']->isAdmin())) {
				$t = $GLOBALS['cb_db']->query('SELECT topic_id,topic_name FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_type!=2 AND topic_fromtopicgroup = '.$tgid.' AND topic_id IN ('.implode(',',$selected).')');
				$topics = '';
				$tids = array();
				while ($tp = $GLOBALS['cb_db']->fetch_assoc($t)) {
					$tids[]=$tp['topic_id'];
					$topics.='- '.$tp['topic_name'].'<br />';
				}
				$url_yes = manage_url('index.php?showtopicgroup='.$tgid.'&amp;','forum-tg'.$tgid.'.html?').'deletetopics=1';
				$_SESSION['cb_deletetopics'] = implode('-',$tids);
				message(lang(array('item' => 'tg_deletetopic_confirm','topics' => $topics,'url_yes' => $url_yes,'url_no' => '')));
			}
			if (!empty($log) && $GLOBALS['cb_db']->affected_rows()) {
				foreach ($selected as $topicmod) addLog($log,'',$topicmod,'');
			}
		}
	}
}

// Gére la suppression de topics de masse
function manageMassDelete ($tgid) {
	if ($_SESSION['cb_user']->isMod($tgid) && !empty($_SESSION['cb_deletetopics']) && $_GET['deletetopics']==1) {
		require_once(CB_PATH.'include/lib/lib.log.php');
		$topics = array_filter(explode('-',$_SESSION['cb_deletetopics']));
		foreach ($topics as $tdel) {
			deleteTopic($tdel);
			addLog(LOG_DELETETOPIC,'','','');
		}
		trigger_error(lang('tg_deletetopics_success'),E_USER_NOTICE);
	}
	$_SESSION['cb_deletetopics'] = null;
	unset($_SESSION['cb_deletetopics']);
}

// Renvoie le menu de messages automatiques
function autoMessages($fieldname) {
	$menu ='<select id="'.$fieldname.'" name="'.$fieldname.'" style="width:300px;">';
	$ret=$GLOBALS['cb_db']->query('SELECT am_id,am_name FROM '.$GLOBALS['cb_db']->prefix.'automessages ORDER BY am_id ASC');
	$first=true;
	while ($data=$GLOBALS['cb_db']->fetch_assoc($ret)) {
		$menu.='<option value="'.$data['am_id'].'">'.$data['am_name'].'</option>';
		$first=false;
	}
	$menu.='</select>';
	if ($first) return false;
	return $menu;
}

/* Fonction qui remet à jour le dernier sujet d'un groupe de sujets. */
function setLastTopic ($topicgroup) {
	$lm = $GLOBALS['cb_db']->single_result('SELECT MAX(topic_lastmessage) FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_status!=2 AND topic_fromtopicgroup='.$topicgroup);
	$lm = ($lm == false || $lm == null)?0:(int)$lm;
	$lt = 0;
	if ($lm != 0) {
		$pt = $GLOBALS['cb_db']->single_result('SELECT msg_topicid FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_id='.$lm);
		if ($pt > 0) {
			$lt = $pt;
		}
	}
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_lasttopic='.$lt.' WHERE tg_id='.$topicgroup);
}

/* Fonction qui remet à jour le dernier message d'un sujet. */
function setLastMessage ($topic,$supprmess = 0) {
	$maxid = $GLOBALS['cb_db']->single_result('SELECT MAX(msg_id) FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_topicid='.$topic);
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_lastmessage='.$maxid.' WHERE topic_id='.$topic);
	// Si on supprime le dernier message
	if ($supprmess > $maxid)
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'usertopics SET ut_msgread='.$maxid.' WHERE ut_topicid='.$topic.' AND ut_msgread > '.$maxid);
}

/* Fonction qui supprime un topic. */
function deleteTopic($id) {
	$return=$GLOBALS['cb_db']->query('SELECT msg_id,msg_userid,topic_fromtopicgroup,topic_poll
		FROM '.$GLOBALS['cb_db']->prefix.'messages
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topics ON topic_id=msg_topicid
		WHERE msg_topicid='.$id);
	$deletedreplies=0;
	$deletedreports=0;
	$ftg=null;
	while ($data=$GLOBALS['cb_db']->fetch_array($return)) {
		$ftg=$data['topic_fromtopicgroup'];
		if ($data['topic_poll'] != 0 && $deletedreplies == 0) {
			$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'pollpossibilities WHERE poss_pollid='.$data['topic_poll']);
			$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'polls WHERE poll_id='.$data['topic_poll']);
		}
		if ($data['msg_userid'] > 0)
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_nbmess=usr_nbmess-1 WHERE usr_id='.$data['msg_userid'].' AND usr_nbmess > 0');
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'reports WHERE rep_msgid='.$data['msg_id']);
		if ($GLOBALS['cb_db']->affected_rows() > 0)
			$deletedreports += $GLOBALS['cb_db']->affected_rows();
		$deletedreplies++;
	}
	if ($deletedreports > 0)
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value-'.$deletedreports.' WHERE st_field=\'nb_reports\'');
	$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_topicid='.$id);
	$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_id='.$id.' OR topic_displaced='.$id);
	$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'usertopics WHERE ut_topicid='.$id);
	$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'src_matches WHERE sm_topicid='.$id);
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value-1 WHERE st_field=\'total_topics\'');
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value-'.$deletedreplies.' WHERE st_field=\'total_messages\'');
	if (!empty($ftg)) {
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_nbmess=tg_nbmess-'.$deletedreplies.',tg_nbtopics=tg_nbtopics-1 WHERE tg_id='.$ftg);
		setLastTopic($ftg);
	}
}

/* Fonction qui déplace un sujet */
function displaceTopic($tid,$ntgid,$leavetrace = false) {
	$ret=$GLOBALS['cb_db']->query('SELECT topic_id,topic_name,topic_comment,topic_starter,topic_fromtopicgroup,topic_lastmessage,topic_nbreply FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_id='.$tid);
	if ($a=$GLOBALS['cb_db']->fetch_assoc($ret)) {
		if ($leavetrace)
			$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'topics(topic_name,topic_comment,topic_starter,topic_fromtopicgroup,topic_lastmessage,topic_status,topic_displaced) VALUES(\''.$a['topic_name'].'\',\''.$a['topic_comment'].'\','.$a['topic_starter'].','.$a['topic_fromtopicgroup'].','.$a['topic_lastmessage'].',2,'.$a['topic_id'].')');
		
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_status=2 AND topic_displaced='.$tid.' AND topic_fromtopicgroup='.$ntgid);
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_fromtopicgroup='.$ntgid.' WHERE topic_id='.$tid);

		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups
			SET tg_nbtopics=tg_nbtopics+1,
			tg_nbmess=tg_nbmess+'.($a['topic_nbreply']+1).'
			WHERE tg_id='.$ntgid);
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups
			SET tg_nbtopics=tg_nbtopics-1,
			tg_nbmess=tg_nbmess-'.($a['topic_nbreply']+1).'
			WHERE tg_id='.$a['topic_fromtopicgroup']);

		setLastTopic($ntgid);
		setLastTopic($a['topic_fromtopicgroup']);

		addLog( LOG_DISPLACETOPIC,'',$tid,'' );
	}
}

/* Fonction qui supprime un groupe de topics. */
function deleteTopicgroup($id) {
	$r = $GLOBALS['cb_db']->query('SELECT topic_id FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_fromtopicgroup='.$id);
	while ($topic = $GLOBALS['cb_db']->fetch_array($r))
		deleteTopic($topic['topic_id']);
	if (isset($GLOBALS['cb_str_ptg'][$id])) {
		foreach ($GLOBALS['cb_str_ptg'][$id] as $tg)
			deleteTopicgroup($tg);
	}
	$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'topicgroups WHERE tg_id='.$id);
}

/* Fonction qui supprime un forum. */
function deleteForum ($id) {
	foreach ($GLOBALS['cb_str_pf'][$id] as $tgid)
		deleteTopicGroup($tgid);
	$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'forums WHERE forum_id='.$id);
}
?>