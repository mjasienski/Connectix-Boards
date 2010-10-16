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

$GLOBALS['cb_tpl']->lang_load('writemessage.lang');
$GLOBALS['cb_tpl']->lang_load('topic.lang');

require_once(CB_PATH.'include/lib/lib.writing.php');

$GLOBALS['cb_javascript'][] = '<script type="text/javascript" src="include/javascripts/cb_ajax.js"></script>';

/* Définition de ce qu'on fait */
$act = '';
$wrtopic = null;
$wrtg = null;
$poll = false;

/* Gestion de l'écriture de messages */
if (isset($_GET['addreply'])) {
	$act = 'addreply';
	$wrtopic=(int)$_GET['addreply'];
	$wrtg = getTopicgroupId($wrtopic);
	
	if ($wrtg == false)
		trigger_error(lang('error_t_noexist'),E_USER_ERROR);
	
	if (!$_SESSION['cb_user']->getAuth('reply',$wrtg))
		trigger_error(lang('error_wm_nowriteright'),E_USER_ERROR);

	if (isClosed($wrtopic)) {
		if ($_SESSION['cb_user']->isMod($wrtg))
			trigger_error(lang('error_wm_topicclosed'),E_USER_NOTICE);
		else
			trigger_error(lang('error_wm_topicclosed'),E_USER_ERROR);
	}
} elseif (isset($_GET['intopic'])) {
	if (!$_SESSION['cb_user']->logged)
		trigger_error(lang('error_wrongrequest'),E_USER_ERROR);

	if (!isset($_GET['editmessage']))
		trigger_error(lang('error_wrongrequest'),E_USER_ERROR);

	$act = 'editmessage';
	$wrtopic=(int)$_GET['intopic'];
	$wrtg = getTopicgroupId($wrtopic);

	$uid = $GLOBALS['cb_db']->single_result('SELECT msg_userid FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_id='.(int)$_GET['editmessage'].' AND msg_topicid='.$wrtopic);

	if ($uid === false)
		trigger_error(lang('error_wrongrequest'),E_USER_ERROR);

	if (!$_SESSION['cb_user']->isMod($wrtg) && $uid != $_SESSION['cb_user']->userid)
		trigger_error(lang('error_wrongrequest',E_USER_ERROR));
}

/* Gestion de l'écriture de nouveaux sujets */
if (isset($_GET['newtopic']) && empty($act)) {
	if ($_SESSION['cb_user']->getAuth('create',(int)$_GET['newtopic'])) {
		$act = 'newtopic';
		$wrtg = (int)$_GET['newtopic'];
	} else
		trigger_error(lang('error_wm_nowriteright'),E_USER_ERROR);
}

/* Gestion de l'écriture de nouveaux sondages */
if (isset($_GET['newpoll']) && empty($act)) {
	if ($_SESSION['cb_user']->getAuth('create',(int)$_GET['newpoll'])) {
		$act = 'newtopic';
		$wrtg = (int)$_GET['newpoll'];
		$poll = true;
	} else
		trigger_error(lang('error_wm_nowriteright'),E_USER_ERROR);
}

/* Si on ne fait rien: erreur */
if (empty($act))
	trigger_error(lang('error_wrongrequest'),E_USER_ERROR);

/* Si pas loggé, on s'occupe du nom du posteur et de l'image aléatoire */
$guest_okforpost = false;
if (!$_SESSION['cb_user']->logged) {
	$_SESSION['guest_name'] = (isset($_SESSION['guest_name'])?$_SESSION['guest_name']:'');
	$_SESSION['guest_displayname'] = (isset($_SESSION['guest_displayname'])?$_SESSION['guest_displayname']:'');
	
	$GLOBALS['cb_javascript'][] = '<script type="text/javascript" src="include/javascripts/cb_ajax.js"></script>';
	require_once(CB_PATH.'include/lib/lib.images.php');
	
	$regencaptcha = true;
	if (isset($_POST['prev']) || isset($_POST['confirm']) || isset($_POST['fastreply'])) {
		if ($_POST['captcha'] == $_SESSION['verifnbr']) {
			$regencaptcha = false;
		}
	}
	$GLOBALS['cb_tpl']->assign('captcha_code',getCaptcha($regencaptcha));

	if (isset($_POST['captcha'],$_POST['guestname'])) {
		require_once(CB_PATH.'include/lib/lib.users.php');
		if (verifyUserName($_POST['guestname'])) {
			$_SESSION['guest_name'] = clean($_POST['guestname']);
			$_SESSION['guest_displayname'] = clean($_POST['guestname'],STR_TODISPLAY);
			
			if ($_POST['captcha'] == $_SESSION['verifnbr']) {
				$guest_okforpost = true;
			} else trigger_error(lang('error_reg_mistypenumber'),E_USER_WARNING);
		}
	}
}

/* Si on n'écrit pas dans un topic ou un topicgroup existant: erreur */
if (($act == 'addreply' || $act == 'editmessage') && !isTopic($wrtopic))
	trigger_error(lang('error_wm_notgoodtopic'),E_USER_ERROR);

if ($act == 'newtopic' && !isTg($wrtg))
	trigger_error(lang('error_tg_noexist'),E_USER_ERROR);

/* Vérification du flood */
$flooding = ((time()-$_SESSION['flood']) < $GLOBALS['cb_cfg']->config['floodlimit'] && !$_SESSION['cb_user']->canFlood());
$floodtime = ($GLOBALS['cb_cfg']->config['floodlimit']-time()+$_SESSION['flood']);

/* Gestion des variables POST pour le formulaire de messages. */
if ($act == 'addreply') {
	if (isset($_POST['fastreply']) || (!isset($_POST['prev']) && isset($_POST['confirm']))) {
		if (!$flooding) {
			if ($_SESSION['cb_user']->logged || $guest_okforpost) {
				if (isset($_POST['message']) && utf8_strlen(trim($_POST['message']))>=1) {
					$write = array(
						'wmessage' => $_POST['message'],
						'towrite' => 'addmessage',
						'towriteid' => $wrtopic,
						'redirect' => (isset($_POST['redirect'])?$_POST['redirect']:'message')
						);
					if ($_SESSION['cb_user']->isMod($wrtg)) {
						if ($_POST['modoptions']=='n') $write['status']=0;
						elseif ($_POST['modoptions']=='c') $write['status']=1;
					}
					writeMessage($write);
				} else trigger_error(lang('error_wm_nomessage'),E_USER_WARNING);
			}
		} else trigger_error(lang('error_flood').' ('.$floodtime.' sec)',E_USER_WARNING);
	}
}
if ($act == 'newtopic') {
	if (!isset($_POST['prev']) && isset($_POST['confirm'])) {
		if (!$flooding) {
			if ($_SESSION['cb_user']->logged || $guest_okforpost) {
				if (isset($_POST['topictitle']) && utf8_strlen(trim($_POST['topictitle']))>2 && utf8_strlen(trim($_POST['topictitle']))<=60) {
					if (isset($_POST['message'],$_POST['topiccomment']) && utf8_strlen(trim($_POST['message']))>=1) {
						$write = array(
							'wtopictitle' => $_POST['topictitle'],
							'wtopiccomment' => $_POST['topiccomment'],
							'wmessage' => $_POST['message'],
							'towrite' => 'newtopic',
							'towriteid' => $wrtg,
							'redirect' => $_POST['redirect'],
							'poll' => false,
							'type' => 0
							);

						if ($_SESSION['cb_user']->isMod($wrtg)) {
							if ($_POST['modoptions']=='a') $write['type']=2;
							elseif ($_POST['modoptions']=='p') $write['type']=1;
						}

						if ($poll) {
							if (isset($_POST['pollquestion']) && utf8_strlen(trim($_POST['pollquestion']))>2) {
								if (isset($_POST['pollpossibilities']) && utf8_strlen(trim($_POST['pollpossibilities']))>=1) {
									$poss=explode("\n",str_replace("\r",'',$_POST['pollpossibilities']));
									$possibilities=array();
									foreach ($poss as $value) {
										if (!empty($value) && utf8_strlen(trim($value))>=1)
											$possibilities[] = trim($value);
									}
									if (count($possibilities) >= 2) {
										$write['towrite'] = 'newtopic';
										$write['poll'] = true;
										$write['pollpossibilities'] = $possibilities;
										$write['pollquestion'] = $_POST['pollquestion'];
									} else trigger_error(lang('error_wm_nopossibilities'),E_USER_WARNING);
								} else trigger_error(lang('error_wm_nopossibilities'),E_USER_WARNING);
							} else trigger_error(lang('error_wm_pollquestionlength'),E_USER_WARNING);
						}

						if (!$poll || $write['poll'])
							writeMessage($write);
					} else trigger_error(lang('error_wm_nomessage'),E_USER_WARNING);
				} else trigger_error(lang('error_wm_topictitlelength'),E_USER_WARNING);
			}
		} else trigger_error(lang('error_flood').' ('.$floodtime.' sec)',E_USER_WARNING);
	}
}
if ($act == 'editmessage') {
	if (!isset($_POST['prev']) && isset($_POST['confirm'])) {
		if ($_SESSION['cb_user']->logged || $guest_okforpost) {
			if (isset($_POST['message']) && utf8_strlen(trim($_POST['message']))>=1) {
				$write = array(
					'wmessage' => $_POST['message'],
					'toedit' => (int)$_GET['editmessage'],
					'towrite' => 'editmessage',
					'towriteid' => $wrtopic,
					'edit_show' => (($_SESSION['cb_user']->isMod($wrtg) && isset($_POST['mod_edit']) && $_POST['mod_edit']=='no') ? false : true ),
					'redirect' => $_POST['redirect']
					);

				$title = true;
				if ($GLOBALS['cb_cfg']->config['edittopictitle'] == 'yes' && isset($_POST['topictitle'],$_POST['topiccomment']) && utf8_strlen(trim($_POST['topictitle']))>2 && utf8_strlen(trim($_POST['topictitle']))<=60) {
					$nbPre = $GLOBALS['cb_db']->single_result('SELECT COUNT(*) FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_topicid='.$wrtopic.' AND msg_id < '.(int)$_GET['editmessage']);
					if ($nbPre == 0) {
						$title = false;
						if (isset($_POST['topictitle'],$_POST['topiccomment']) && utf8_strlen(trim($_POST['topictitle']))>2 && utf8_strlen(trim($_POST['topictitle']))<=60) {
							$write['wtopictitle'] = $_POST['topictitle'];
							$write['wtopiccomment'] = $_POST['topiccomment'];
							$title = true;
						} else trigger_error(lang('error_wm_topictitlelength'),E_USER_WARNING);
					}
				}

				if ($title)
					writeMessage($write);
			} else trigger_error(lang('error_wm_nomessage'),E_USER_WARNING);
		}
	}
}

/* Initialisation du préremplissage des champs. */
$topictitle		= (isset($_POST['topictitle']))		? htmlspecialchars($_POST['topictitle'],ENT_QUOTES)		: '' ;
$topiccomment	  = (isset($_POST['topiccomment']))	  ? htmlspecialchars($_POST['topiccomment'],ENT_QUOTES)	  : '' ;
$message		   = (isset($_POST['message']))		   ? htmlspecialchars($_POST['message'],ENT_QUOTES)		   : '' ;
$pollquestion	  = (isset($_POST['pollquestion']))	  ? htmlspecialchars($_POST['pollquestion'],ENT_QUOTES)	  : '' ;
$pollpossibilities = (isset($_POST['pollpossibilities'])) ? htmlspecialchars($_POST['pollpossibilities'],ENT_QUOTES) : '' ;

/* Préremplissage du message si nécessaire. */
if ($act == 'addreply') {
	if (isset($_GET['quotemessage']) && !isset($_POST['message'])) {
		$return=$GLOBALS['cb_db']->query('SELECT msg_userid,msg_timestamp,msg_message,usr_name,msg_guest
			FROM '.$GLOBALS['cb_db']->prefix.'messages
			LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id = msg_userid
			WHERE msg_id='.(int)$_GET['quotemessage'].' AND msg_topicid='.$wrtopic);
		if ($fetch = $GLOBALS['cb_db']->fetch_assoc($return)) {
			$message = '[quote='.(($fetch['msg_userid'])?$fetch['usr_name']:((!empty($fetch['msg_guest']))?$fetch['msg_guest']:lang('guest'))).' @ '.dateFormat($fetch['msg_timestamp']).']'.unclean($fetch['msg_message']).'[/quote]';
		}
	}
}
if ($act == 'editmessage' && !isset($_POST['message'])) {
	$message = unclean($GLOBALS['cb_db']->single_result('SELECT msg_message FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_id='.(int)$_GET['editmessage'].' AND msg_topicid='.$wrtopic));
}

/* Informations pour les personnes non-connectées */
if (!$_SESSION['cb_user']->logged) {
	$GLOBALS['cb_tpl']->assign('wm_guestname',$_SESSION['guest_displayname']);
	$GLOBALS['cb_tpl']->assign('captcha_typed',((isset($_POST['captcha']) && $_POST['captcha'] == $_SESSION['verifnbr'])?htmlspecialchars($_POST['captcha'],ENT_QUOTES):''));
}

/* Affichage du formulaire. */
$title='';
$action='';
if  ($act == 'editmessage') { $title='wm_editing'; $action='edit'; }
elseif ($act == 'addreply') { $title='wm_writing'; $action='write'; }
elseif ($act == 'newtopic') { $title='wm_newtopic'; $action='new'; }

$GLOBALS['cb_tpl']->assign('wm_action',$action);
$GLOBALS['cb_tpl']->assign('wm_title',$title);

$GLOBALS['cb_addressbar'] = array_merge($GLOBALS['cb_addressbar'],getTgPath($wrtg));

/* On s'occupe de l'affichage des 10 derniers messages. */
$ttype=0;
$tstatus=0;
if ($act == 'addreply') {
	$returndb=$GLOBALS['cb_db']->query('SELECT tg_name,topic_name,topic_type,topic_status,msg_id,msg_userid,msg_guest,msg_timestamp,msg_message,msg_modified,msg_modifieduser,normusers.usr_name AS messusername,editusers.usr_name AS modifusername
		FROM '.$GLOBALS['cb_db']->prefix.'messages
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users AS normusers ON normusers.usr_id = msg_userid
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topics ON topic_id = msg_topicid
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topicgroups ON tg_id = topic_fromtopicgroup
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users AS editusers ON editusers.usr_id = msg_modifieduser
		WHERE '.$GLOBALS['cb_db']->prefix.'messages.msg_topicid='.$wrtopic.'
		ORDER BY msg_timestamp DESC
		LIMIT 10');

	$abstract=array();
	$first=true;
	while ($topic=$GLOBALS['cb_db']->fetch_assoc($returndb)) {
		if ($first) {
			$_SESSION['cb_user']->connected('index_'.getTgPathIds($wrtg).'_t_'.$wrtopic.'_wm');

			$GLOBALS['cb_addressbar'][] = '<a href="'.manage_url('index.php?showtopicgroup='.$wrtg,'forum-tg'.$wrtg.','.rewrite_words($topic['tg_name']).'.html').'">'.$topic['tg_name'].'</a>';
			$GLOBALS['cb_addressbar'][] = '<a href="'.manage_url('index.php?showtopic='.$wrtopic,'forum-t'.$wrtopic.','.rewrite_words($topic['topic_name']).'.html').'">'.$topic['topic_name'].'</a>';
			$GLOBALS['cb_pagename'][] = $topic['topic_name'];
			$GLOBALS['cb_pagename'][] = (($act == 'editmessage')?lang('wm_editing'):lang('wm_writing'));
			
			$ttype=$topic['topic_type'];
			$tstatus=$topic['topic_status'];
		}
		$a = array(
			'wm_lm_msglink' => '<a name="'.$topic['msg_id'].'"></a>',
			'wm_lm_userlink' => getUserLink($topic['msg_userid'],$topic['messusername'],$topic['msg_guest']),
			'wm_lm_time' => dateFormat($topic['msg_timestamp']),
			'wm_lm_message' => $topic['msg_message']
			);
		if ($topic['msg_modified']>0) {
			$a['wm_lm_modif_userlink'] = '<a href="'.manage_url('index.php?act=user&amp;showprofile='.$topic['msg_modifieduser'],'forum-m'.$topic['msg_modifieduser'].','.rewrite_words($topic['modifusername']).'.html').'">'.$topic['modifusername'].'</a>';
			$a['wm_lm_modif_date1'] = dateFormat($topic['msg_modified'],2);
			$a['wm_lm_modif_date2'] = dateFormat($topic['msg_modified'],3);
		}
		$abstract[] = $a;
		$first=false;
	}
	$GLOBALS['cb_tpl']->assign_ref('wm_lastmessages',$abstract);
} else {
	$GLOBALS['cb_addressbar'][] = '<a href="'.manage_url('index.php?showtopicgroup='.$wrtg,'forum-tg'.$wrtg.','.rewrite_words($GLOBALS['cb_str_tgnames'][$wrtg]).'.html').'">'.$GLOBALS['cb_str_tgnames'][$wrtg].'</a>';
	$GLOBALS['cb_pagename'][] = $GLOBALS['cb_str_tgnames'][$wrtg];
	$GLOBALS['cb_pagename'][] = lang($poll?'wm_newpoll':'wm_newtopic');

	$_SESSION['cb_user']->connected('index_'.getTgPathIds($wrtg).'_wm');
}

/* Prévisualisation. */
if (isset($_POST['prev']) && !empty($_POST['message']))
	$GLOBALS['cb_tpl']->assign('wm_prev_message',clean($_POST['message'],STR_TODISPLAY + STR_MULTILINE + STR_PARSEBB));

/* On compose le formulaire... */
$GLOBALS['cb_tpl']->assign('wm_formaction','http://'.htmlspecialchars($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));
$GLOBALS['cb_tpl']->assign('wm_w_edittopictitle',false);

if ($act == 'newtopic') {
	$GLOBALS['cb_tpl']->assign('wm_w_topictitle',$topictitle);
	$GLOBALS['cb_tpl']->assign('wm_w_topiccomment',$topiccomment);
	$GLOBALS['cb_tpl']->assign('wm_newtopic',true);
} else $GLOBALS['cb_tpl']->assign('wm_newtopic',false);

$GLOBALS['cb_tpl']->assign('wm_w_message',$message);
$GLOBALS['cb_tpl']->assign('wm_w_selectredirect',array(
	'name' => 'redirect',
	'style' => 300,
	'items' => array(
		array('name' => 'message','selected' => false,'value' => '','lang' => 'wm_currentmessage'),
		array('name' => 'tg_'.$wrtg,'selected' => false,'value' => implode(' - ',getTgPath($wrtg,false)).' - '.$GLOBALS['cb_str_tgnames'][$wrtg],'lang' => ''),
		array('name' => 'home','selected' => false,'value' => $GLOBALS['cb_cfg']->config['forumname'],'lang' => '')
		)
	));

$GLOBALS['cb_tpl']->assign('wm_w_prevurl','http://'.htmlspecialchars($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).'&amp;prev=on');

if ($poll) {
	$GLOBALS['cb_tpl']->assign('wm_w_submitmessage','wm_submitpoll');
	$GLOBALS['cb_tpl']->assign('wm_w_cancelurl',manage_url('index.php?showtopicgroup='.$wrtg,'forum-tg'.$wrtg.'.html'));
} elseif ($act == 'newtopic') {
	$GLOBALS['cb_tpl']->assign('wm_w_submitmessage','wm_submittopic');
	$GLOBALS['cb_tpl']->assign('wm_w_cancelurl',manage_url('index.php?showtopicgroup='.$wrtg,'forum-tg'.$wrtg.'.html'));
} elseif ($act == 'editmessage') {
	$q = $GLOBALS['cb_db']->query('SELECT topic_name,topic_comment FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_id='.$wrtopic);
	$tpc = $GLOBALS['cb_db']->fetch_assoc($q);
	// Edition du titre et commentaire pour l'initiateur du sujet
	if ($GLOBALS['cb_cfg']->config['edittopictitle'] == 'yes') {
		$nbPre = $GLOBALS['cb_db']->single_result('SELECT COUNT(*) FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_topicid='.$wrtopic.' AND msg_id < '.(int)$_GET['editmessage']);
		if ($nbPre == 0) {
			$GLOBALS['cb_tpl']->assign('wm_w_edittopictitle',true);
			$GLOBALS['cb_tpl']->assign('wm_w_topictitle',$tpc['topic_name']);
			$GLOBALS['cb_tpl']->assign('wm_w_topiccomment',$tpc['topic_comment']);
		}
	}
	$GLOBALS['cb_addressbar'][] = '<a href="'.manage_url('index.php?showtopic='.$wrtopic,'forum-t'.$wrtopic.','.rewrite_words($tpc['topic_name']).'.html').'">'.$tpc['topic_name'].'</a>';
	$GLOBALS['cb_tpl']->assign('wm_w_submitmessage','wm_submitmodifpost');
	$GLOBALS['cb_tpl']->assign('wm_w_cancelurl',manage_url('index.php?showtopic='.$wrtopic,'forum-t'.$wrtopic.'.html'));
} else {
	$GLOBALS['cb_tpl']->assign('wm_w_submitmessage','wm_postmessage');
	$GLOBALS['cb_tpl']->assign('wm_w_cancelurl',manage_url('index.php?showtopic='.$wrtopic,'forum-t'.$wrtopic.'.html'));
}

$GLOBALS['cb_addressbar'][] = lang($title);

/* On s'occupe des options modérateurs. */
$GLOBALS['cb_tpl']->assign('wm_needmodoptions',($_SESSION['cb_user']->isMod($wrtg)));
if ($_SESSION['cb_user']->isMod($wrtg)) {
	$items = array();
	if ($ttype!=2)
		$items[] = array('name' => 'n','selected' => false,'value' => '','lang' => 'wm_normaltopic');
	if ($act == 'newtopic')
		$items[] = array('name' => 'p','selected' => false,'value' => '','lang' => 'pinned');

	$items[] = array('name' => (($act == 'newtopic')?'a':'c'),'selected' => (($tstatus==1)?true:false),'value' => '','lang' => (($act == 'newtopic')?'announcement':'closetopic'));

	$GLOBALS['cb_tpl']->assign('wm_w_modoptions_menu',array(
		'name' => 'modoptions',
		'style' => 200,
		'items' => $items
		));
}

/* On s'occupe des sondages, s'il y a lieu. */
$GLOBALS['cb_tpl']->assign('wm_newpoll',$poll);
if ($poll) {
	$GLOBALS['cb_tpl']->assign('wm_w_pollquestion',$pollquestion);
	$GLOBALS['cb_tpl']->assign('wm_w_pollpossibilities',$pollpossibilities);
}

/* On affiche tout... */
$GLOBALS['cb_tpl']->assign('g_part','part_writemessage.php');
?>