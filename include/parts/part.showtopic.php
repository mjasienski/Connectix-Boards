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

$GLOBALS['cb_tpl']->lang_load('topic.lang');
$GLOBALS['cb_javascript'][] = '<script type="text/javascript" src="include/javascripts/cb_ajax.js"></script>';

/* Récupération de toutes les informations relatives au sujet */
$return = $GLOBALS['cb_db']->query('SELECT
		topic_nbreply,topic_type,topic_comment,topic_name,topic_id,topic_fromtopicgroup,topic_lastmessage,topic_status,topic_starter,
		poll_id,poll_question,poll_voted,poll_totalvotes,poll_white
		'.(($_SESSION['cb_user']->logged)?',ut_msgread,utg_markasread,ut_mail,ut_bookmark':'').'
	FROM '.$GLOBALS['cb_db']->prefix.'topics
	LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'polls ON poll_id = topic_poll
	'.(($_SESSION['cb_user']->logged)?'
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'usertgs ON utg_userid='.$_SESSION['cb_user']->userid.' AND utg_tgid = topic_fromtopicgroup
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'usertopics ON ut_userid = '.$_SESSION['cb_user']->userid.' AND ut_topicid = '.(int)$_GET['showtopic']
	:'').'
	WHERE topic_id='.(int)$_GET['showtopic']);

/* Erreur, si le sujet n'existe pas */
if (!$topic = $GLOBALS['cb_db']->fetch_assoc($return))
	trigger_error(lang('error_t_noexist'),E_USER_ERROR);

/* Sujet déplacé, on ne peut théoriquement pas arriver ici, ce n'est qu'une trace. */
if ($topic['topic_status']==2)
	trigger_error(lang('error_t_noexist'),E_USER_ERROR);

/* Erreur, si l'utilisateur n'a pas l'autorisation de visionner le sujet */
if (!$_SESSION['cb_user']->getAuth('see',$topic['topic_fromtopicgroup']))
	trigger_error(lang('error_permerror'),E_USER_ERROR);

/* Numéro de la page */
$pagenumber = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

/* Erreur, si l'utilisateur veut voir une page qui n'existe pas */
if ($pagenumber < 1 || $pagenumber > ceil(($topic['topic_nbreply']+1)/$_SESSION['cb_user']->usr_pref_msgs)) {
	$cnt = $GLOBALS['cb_db']->single_result('SELECT COUNT(*)-1 FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_topicid='.$topic['topic_id']);
	if ($cnt != $topic['topic_nbreply'] && $cnt >= 0) {
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_nbreply='.$cnt.' WHERE topic_id='.$topic['topic_id']);
		header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		exit();
	}
	trigger_error(lang('error_t_nopage'),E_USER_ERROR);
}

/* Redirection si demande du premier post non-lu. */
if (isset($_GET['showtopic'],$_GET['gotofirstunreadpost']) && $_GET['gotofirstunreadpost']==1 && $_SESSION['cb_user']->logged) {
	$q = $GLOBALS['cb_db']->query('SELECT COUNT(*) AS cnt,MIN(msg_id) AS mid FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_topicid='.$topic['topic_id'].' AND msg_id > '.max((int)$_SESSION['cb_user']->mark_as_read,(isset($topic['utg_markasread'])?(int)$topic['utg_markasread']:0),(isset($topic['ut_msgread'])?(int)$topic['ut_msgread']:0)));
	$d = $GLOBALS['cb_db']->fetch_assoc($q);

	/* Redirection */
	$nbpage = ceil((($topic['topic_nbreply'] - $d['cnt'] + 2)/$_SESSION['cb_user']->usr_pref_msgs));
	if ($d['cnt'] == 0) $nbpage = ceil((($topic['topic_nbreply'] + 1)/$_SESSION['cb_user']->usr_pref_msgs));
	$msgid = (($d['mid'])?$d['mid']:$topic['topic_lastmessage']);
	redirect(manage_url('index.php?showtopic='.$topic['topic_id'].'&page='.$nbpage.((isset($_GET['hl']))?'&hl='.$_GET['hl']:'').'#'.$msgid,'forum-t'.$topic['topic_id'].'-p'.$nbpage.','.rewrite_words($topic['topic_name']).'.html'.((isset($_GET['hl']))?'&hl='.$_GET['hl']:'').'#'.$msgid));
}
/* Redirection si demande d'un post spécifique */
if (isset($_GET['showtopic'],$_GET['message'])) {
	$nb = $GLOBALS['cb_db']->single_result('SELECT COUNT(*) FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_id<'.(int)$_GET['message'].' AND msg_topicid='.$topic['topic_id']);
	redirect(manage_url('index.php?showtopic='.$topic['topic_id'].'&page='.ceil(($nb+1)/$_SESSION['cb_user']->usr_pref_msgs).'#'.(int)$_GET['message'],'forum-t'.$topic['topic_id'].'-p'.ceil(($nb+1)/$_SESSION['cb_user']->usr_pref_msgs).','.rewrite_words($topic['topic_name']).'.html#'.(int)$_GET['message']));
}

/* Gestion des variables POST et GET de modération, inclusion du fichier de langue des modérateurs */
if ($_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup'])) {
	$GLOBALS['cb_tpl']->lang_load('moderators.lang');
	require_once(CB_PATH.'include/lib/lib.moderators.php');
	manageTopicModOptions ($topic);
	if (isset($_GET['deletetopic']) && $_GET['deletetopic'] == 1) {
		if ($GLOBALS['cb_cfg']->config['deleteallowed']=='yes' || $_SESSION['cb_user']->isAdmin()) {
			$url_yes = manage_url('index.php?showtopicgroup='.$topic['topic_fromtopicgroup'].'&amp;','forum-tg'.$topic['topic_fromtopicgroup'].'.html?').'deletetopics=1';
			$url_no = manage_url('index.php?showtopic='.$topic['topic_id'].'&amp;page='.$pagenumber,'forum-t'.$topic['topic_id'].'-p'.$pagenumber.','.rewrite_words($topic['topic_name']).'.html');
			$_SESSION['cb_deletetopics'] = $topic['topic_id'];
			$GLOBALS['cb_tpl']->lang_load('ftg.lang');
			message(lang(array('item' => 'tg_deletetopic_confirm','topics' => $topic['topic_name'].'<br />','url_yes' => $url_yes,'url_no' => $url_no)));
		}
	}
}
/* Gestion de la modération du sondage */
if (!empty($topic['poll_id'])) {
	if ($_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup']) || $_SESSION['cb_user']->userid == $topic['topic_starter']) {
		require_once(CB_PATH.'include/lib/lib.moderators.php');
		managePollOptions ($topic,$pagenumber);
	}
}

/* Suivre (ou ne plus...) le sujet */
if (isset($_GET['track']) && $_SESSION['cb_user']->logged) {
	if ((int)$_GET['track']==1 || (int)$_GET['track']==0) {
		if (isset($topic['ut_msgread'])) $GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'usertopics SET ut_mail='.(int)$_GET['track'].' WHERE ut_userid='.$_SESSION['cb_user']->userid.' AND ut_topicid='.$topic['topic_id']);
		else $GLOBALS['cb_db']->query('INSERT DELAYED INTO '.$GLOBALS['cb_db']->prefix.'usertopics(ut_userid,ut_topicid,ut_mail) VALUES('.$_SESSION['cb_user']->userid.','.$topic['topic_id'].','.(int)$_GET['track'].')');
		redirect(manage_url('index.php?showtopic='.$topic['topic_id'].((isset($_GET['page']))?'&page='.(int)$_GET['page']:''),'forum-t'.$topic['topic_id'].((isset($_GET['page']))?'-p'.(int)$_GET['page']:'').','.rewrite_words($topic['topic_name']).'.html'));
	}
}
/* Ajouter (ou retirer) des favoris */
if (isset($_GET['bookmark']) && $_SESSION['cb_user']->logged) {
	if ((int)$_GET['bookmark']==1 || (int)$_GET['bookmark']==0) {
		if (isset($topic['ut_msgread'])) $GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'usertopics SET ut_bookmark='.(int)$_GET['bookmark'].' WHERE ut_userid='.$_SESSION['cb_user']->userid.' AND ut_topicid='.$topic['topic_id']);
		else $GLOBALS['cb_db']->query('INSERT DELAYED INTO '.$GLOBALS['cb_db']->prefix.'usertopics(ut_userid,ut_topicid,ut_bookmark) VALUES('.$_SESSION['cb_user']->userid.','.$topic['topic_id'].','.(int)$_GET['bookmark'].')');
		redirect(manage_url('index.php?showtopic='.$topic['topic_id'].((isset($_GET['page']))?'&page='.(int)$_GET['page']:''),'forum-t'.$topic['topic_id'].((isset($_GET['page']))?'-p'.(int)$_GET['page']:'').','.rewrite_words($topic['topic_name']).'.html'));
	}
}
/* Vote blanc dans un sondage */
if (isset($_POST['white']) && $_SESSION['cb_user']->logged) {
	$voted = $GLOBALS['cb_db']->single_result('SELECT poll_voted FROM '.$GLOBALS['cb_db']->prefix.'polls LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topics ON topic_poll=poll_id WHERE poll_id='.$topic['poll_id'].' AND topic_status!=1');
	if ($voted !== false) {
		if (!in_array($_SESSION['cb_user']->userid,explode('/',$voted))) {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'polls SET poll_white=poll_white+1,poll_voted=CONCAT(poll_voted,\'/'.$_SESSION['cb_user']->userid.'\') WHERE poll_id='.$topic['poll_id']);
		}
	}

	redirect(manage_url('index.php?showtopic='.$topic['topic_id'].'&page='.$pagenumber.'#poll','forum-t'.$topic['topic_id'].'-p'.$pagenumber.','.rewrite_words($topic['topic_name']).'.html#poll'));
}
/* Vote normal dans un sondage */
elseif (isset($_POST['vote'])) {
	if (isset($_POST['choice']) && $_SESSION['cb_user']->logged && is_numeric($_POST['choice'])) {
		$voted = $GLOBALS['cb_db']->single_result('SELECT poll_voted FROM '.$GLOBALS['cb_db']->prefix.'polls LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topics ON topic_poll=poll_id WHERE poll_id='.$topic['poll_id'].' AND topic_status!=1');
		if ($voted !== false) {
			if (!in_array($_SESSION['cb_user']->userid,explode('/',$voted))) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'pollpossibilities SET poss_votes=poss_votes+1 WHERE poss_pollid='.$topic['poll_id'].' AND poss_id='.(int)$_POST['choice']);
				if ($GLOBALS['cb_db']->affected_rows() > 0) {
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'polls SET poll_voted=CONCAT(poll_voted,\'/'.$_SESSION['cb_user']->userid.'\'),poll_totalvotes=poll_totalvotes+1 WHERE poll_id='.$topic['poll_id']);
				} else trigger_error(lang('t_poll_notgood_voted'),E_USER_WARNING);
			}
		}
	} else trigger_error(lang('t_poll_notgood_voted'),E_USER_WARNING);

	redirect(manage_url('index.php?showtopic='.$topic['topic_id'].'&page='.$pagenumber.'#poll','forum-t'.$topic['topic_id'].'-p'.$pagenumber.','.rewrite_words($topic['topic_name']).'.html#poll'));
}

/* Nombre de vues du sujet */
$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_views=topic_views+1 WHERE topic_id='.$topic['topic_id']);

/* Fil RSS associé */
$GLOBALS['cb_rsslink'] = '<link rel="alternate" type="application/rss+xml" title="'.$GLOBALS['cb_cfg']->config['forumname'].' - '.$topic['topic_name'].'" href="http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).((utf8_substr(dirname($_SERVER['PHP_SELF']),-1)!='/')?'/':'').'rss.php?showtopic='.$topic['topic_id'].'" />';
$GLOBALS['cb_tpl']->assign('rss_tag','<a href="http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).((utf8_substr(dirname($_SERVER['PHP_SELF']),-1)!='/')?'/':'').'rss.php?showtopic='.$topic['topic_id'].'" class="ico_rss"><span>RSS</span></a>');

/* On indique qu'on est connecté dans ce sujet */
$_SESSION['cb_user']->connected('index_'.getTgPathIds($topic['topic_fromtopicgroup']).'_t_'.$topic['topic_id']);

/* Infos générales du sujet */
$GLOBALS['cb_addressbar'] = array_merge($GLOBALS['cb_addressbar'],getTgPath($topic['topic_fromtopicgroup']));
$GLOBALS['cb_addressbar'][] = '<a href="'.manage_url('index.php?showtopicgroup='.$topic['topic_fromtopicgroup'],'forum-tg'.$topic['topic_fromtopicgroup'].','.rewrite_words($GLOBALS['cb_str_tgnames'][$topic['topic_fromtopicgroup']]).'.html').'">'.$GLOBALS['cb_str_tgnames'][$topic['topic_fromtopicgroup']].'</a>';
$GLOBALS['cb_addressbar'][] = $topic['topic_name'];
$GLOBALS['cb_addressbar_double'] = true;
$GLOBALS['cb_tpl']->assign(array(
	't_topicid' => $topic['topic_id'],
	't_parent' => $topic['topic_fromtopicgroup'],
	't_topicname' => $topic['topic_name'],
	't_topiccomment' => $topic['topic_comment'],
	't_topictrack' => ($GLOBALS['cb_cfg']->config['enabletopictrack']=='yes'),
	't_topictracked' => (isset($topic['ut_mail']) && $topic['ut_mail']==1),
	't_bookmarked' => (isset($topic['ut_bookmark']) && $topic['ut_bookmark']==1)
	));

$GLOBALS['cb_pagename'][] = $topic['topic_name'];

/* Création des boutons d'options pour le topic. */
$topicoptionmenu = array();
if ($topic['topic_status']==1 && $topic['topic_type']!=2) {
	$topicoptionmenu[] = 'closed';
	if ($_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup']))
		$topicoptionmenu[] = 'reply';
}
if ($topic['topic_type']==2 && $_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup']))
	$topicoptionmenu[] = 'reply';

if ($topic['topic_status']!=1 && $topic['topic_type']!=2 && $_SESSION['cb_user']->getAuth('reply',$topic['topic_fromtopicgroup']))
	$topicoptionmenu[] = 'reply';

if ($_SESSION['cb_user']->getAuth('create',$topic['topic_fromtopicgroup'])) {
	$topicoptionmenu[] = 'topic';
	$topicoptionmenu[] = 'poll';
}
$GLOBALS['cb_tpl']->assign('t_optionbuttons',$topicoptionmenu);

/* Modération */
$GLOBALS['cb_tpl']->assign('t_ismod',$_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup']));
if ($_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup'])) {
	require_once(CB_PATH.'include/lib/lib.moderators.php');
	$GLOBALS['cb_tpl']->assign('t_modmenu',getModMenu($topic['topic_id'],$topic['topic_type'],$topic['topic_status'],$topic['topic_name'],$topic['topic_comment'],$topic['topic_fromtopicgroup']));
	$GLOBALS['cb_tpl']->assign('t_modaction',manage_url('index.php?showtopic='.$topic['topic_id'],'forum-t'.$topic['topic_id'].','.rewrite_words($topic['topic_name']).'.html'));
}

/* Nombre de  pages du topic. */
$GLOBALS['cb_tpl']->assign('t_pagemenu',pageMenu($topic['topic_nbreply']+1,$pagenumber,$_SESSION['cb_user']->usr_pref_msgs,manage_url('index.php?showtopic='.$topic['topic_id'].'&amp;page=[num_page]'.((isset($_GET['hl']))?'&hl='.$_GET['hl']:''),'forum-t'.$topic['topic_id'].'-p[num_page],'.rewrite_words($topic['topic_name']).'.html'.((isset($_GET['hl']))?'?hl='.$_GET['hl']:''))));

/* Sondage */
if (!empty($topic['poll_id'])) {
	$voted = ($_SESSION['cb_user']->logged) ? in_array($_SESSION['cb_user']->userid,explode('/',$topic['poll_voted'])) : false ;

	$editing = isset($_GET['editpoll']) && ($_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup']) || $_SESSION['cb_user']->userid == $topic['topic_starter']);

	$rp = $GLOBALS['cb_db']->query('SELECT poss_name,poss_votes,poss_id
		FROM '.$GLOBALS['cb_db']->prefix.'pollpossibilities
		WHERE poss_pollid='.$topic['poll_id'].'
		ORDER BY poss_id');
	$results=array();
	$num=0;
	$maxres=0;
	$max_bar_size=150;  // taille de la plus grande des barres de pourcentage.
	while ($poss=$GLOBALS['cb_db']->fetch_assoc($rp)) {
		$percentage = ($topic['poll_totalvotes']>0) ? ($poss['poss_votes']/$topic['poll_totalvotes'])*100 : 0;
		$results[$num]['poss_id']			= $poss['poss_id'];
		$results[$num]['poss_name']			= ($editing)?unclean($poss['poss_name']):$poss['poss_name'];
		$results[$num]['poss_votes']		= $poss['poss_votes'];
		$results[$num]['poss_percentage']	= $percentage;
		if ($percentage>$maxres) $maxres=$percentage;
		$num++;
	}
	foreach ($results as $key => $poss) {
		$results[$key]['poss_barwidth'] = ((($topic['poll_totalvotes']>0))?round($results[$key]['poss_percentage']*$max_bar_size/$maxres):0);
		$results[$key]['poss_percentage'] = number_format($results[$key]['poss_percentage'],2);
	}

	$GLOBALS['cb_tpl']->assign(array(
		't_poll_title' 			=> $topic['poll_question'],
		't_poll_info' 			=> (($_SESSION['cb_user']->logged)?(($voted || $topic['topic_status']==1)?'t_poll_alreadyvoted':'t_poll_notvoted'):''),
		't_poll_alreadyvoted' 	=> ($voted || $topic['topic_status']==1),
		't_poll_white' 			=> $topic['poll_white'],
		't_poll_totalvotes' 	=> $topic['poll_totalvotes'],
		't_poll_canedit' 		=> ($_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup']) || $_SESSION['cb_user']->userid == $topic['topic_starter']),
		't_poll_editlink' 		=> manage_url('index.php?showtopic='.$topic['topic_id'].'&amp;page='.$pagenumber.'&amp;editpoll=1','forum-t'.$topic['topic_id'].'-p'.$pagenumber.'-editpoll.html'),
		't_poll_normallink' 	=> manage_url('index.php?showtopic='.$topic['topic_id'].'&amp;page='.$pagenumber,'forum-t'.$topic['topic_id'].'-p'.$pagenumber.','.rewrite_words($topic['topic_name']).'.html'),
		't_poll_editing' 		=> $editing,
		't_poll_results' 		=> $results
		));
}

/* Récupération des messages du topic. */
$ret_msgs = $GLOBALS['cb_db']->query('SELECT SQL_CALC_FOUND_ROWS msg_id FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_topicid='.$topic['topic_id'].' ORDER BY msg_id LIMIT '.((($pagenumber-1)*$_SESSION['cb_user']->usr_pref_msgs)).','.$_SESSION['cb_user']->usr_pref_msgs);
$msgids = array();
while ($m = $GLOBALS['cb_db']->fetch_assoc($ret_msgs)) $msgids[] = $m['msg_id'];

/* Vérification qu'il n'y a pas de décalage */
$nb_replies = $GLOBALS['cb_db']->single_result('SELECT FOUND_ROWS()')-1;
if ($nb_replies != $topic['topic_nbreply'] && $nb_replies >= 0) {
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_nbreply='.$nb_replies.' WHERE topic_id='.$topic['topic_id']);
	header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	exit();
}

$return4 = $GLOBALS['cb_db']->query('SELECT
		msg_id,msg_timestamp,msg_userid,msg_guest,msg_userip,msg_message,msg_modified,msg_modifieduser,
		con_timestamp,
		nu.usr_registertime AS normregtime,nu.usr_name AS messcleanusername,nu.usr_avatar AS messavatar,nu.usr_website AS messwebsite,nu.usr_class AS messgroupid,
		nu.usr_nbmess AS messnbmess,nu.usr_signature AS messcleansignature,IF(nu.usr_publicemail,nu.usr_email,\'\') AS messemail,nu.usr_reputation AS messreputation,
		mu.usr_name AS modifusername
	FROM '.$GLOBALS['cb_db']->prefix.'messages
	LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'users AS nu ON nu.usr_id=msg_userid
	LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'users AS mu ON mu.usr_id=msg_modifieduser
	LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'connected ON nu.usr_id=con_id
	WHERE msg_id IN ('.implode(',',$msgids).')
	ORDER BY msg_id');

$firstmess = ($pagenumber!=1)?false:true;
$maxid = 0;
$msg_localid = (($pagenumber-1)*$_SESSION['cb_user']->usr_pref_msgs)+1;

require_once(CB_CACHE_CLASSES);

$t_messages=array();
while ($msgs = $GLOBALS['cb_db']->fetch_assoc($return4)) {
	// On détermine si le message a déja été lu
	$read = false;
	if (!$_SESSION['cb_user']->logged)
		$read = true;
	elseif ($_SESSION['cb_user']->logged && $msgs['msg_id'] <= $_SESSION['cb_user']->mark_as_read)
		$read = true;
	elseif (isset($topic['utg_markasread']) && $msgs['msg_id'] <= $topic['utg_markasread'])
		$read = true;
	elseif (isset($topic['ut_msgread']) && $msgs['msg_id'] <= $topic['ut_msgread'])
		$read = true;
	
	// Pour les messages lus
	$maxid = $msgs['msg_id'];
	
	// Données du message
	$msg=array(
		'user_id'				   => $msgs['msg_userid'],
		'mess_userlink'			=> getUserLink($msgs['msg_userid'],$msgs['messcleanusername'],$msgs['msg_guest']),
		'mess_id'				   => $msgs['msg_id'],
		'mess_read'				   => $read,
		'mess_localid'			   => $msg_localid++,
		'user_ip'				   => long2ip($msgs['msg_userip']),
		'u_showip'				   => $_SESSION['cb_user']->isAdmin(),
		'mess_inlink'			   => '<a name="'.$msgs['msg_id'].'"></a>',
		'mess_time'				   => dateFormat($msgs['msg_timestamp'],1,true),
		'mess_messcontent'		   => ((isset($_GET['hl']))?preg_replace('#('.str_replace('-','|',urldecode($_GET['hl'])).')#i','<span class="hl">$1</span>',$msgs['msg_message']):$msgs['msg_message']),
		'mess_link'				   => 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).((utf8_substr(dirname($_SERVER['PHP_SELF']),-1)!='/')?'/':'').manage_url('index.php?showtopic='.$topic['topic_id'].'&amp;message='.$msgs['msg_id'],'forum-t'.$topic['topic_id'].'-m'.$msgs['msg_id'].'.html')
		);
	
	// Données du message relatives au membre considéré
	if ($msgs['msg_userid']) {
		$msg = array_merge($msg,array(
			'mess_userinfo_avatar'	 => ((!empty($msgs['messavatar']))?getAvatar($msgs['messavatar']):''),
			'mess_userinfo_reputation' => $_SESSION['cb_user']->isModerator()?getReputation($msgs['messreputation'],$msgs['msg_userid']):'',
			'mess_userinfo_group_img'  => ((file_exists(CB_PATH.'skins/'.$_SESSION['cb_user']->getPreferredSkin().'/class'.$msgs['messgroupid'].'.jpg'))?'<img src="skins/'.$_SESSION['cb_user']->getPreferredSkin().'/class'.$msgs['messgroupid'].'.jpg" alt="'.$GLOBALS['cb_classes'][$msgs['messgroupid']]['gr_name'].'" />':''),
			'mess_userinfo_group'	  => $GLOBALS['cb_classes'][$msgs['messgroupid']]['gr_name'],
			'mess_userinfo_posts'	  => $msgs['messnbmess'],
			'mess_userinfo_rank'	   => getRank($msgs['messnbmess']),
			'mess_userinfo_registered' => dateFormat($msgs['normregtime'],2,true),
			'mess_userinfo_connected'  => (((time()-$msgs['con_timestamp'])<($GLOBALS['cb_cfg']->config['connectedlimit']*60))?'<span class="usr_online" title="'.lang('usr_online').'"><span>'.lang('usr_online').'</span></span>':'<span class="usr_offline" title="'.lang('usr_offline').'"><span>'.lang('usr_offline').'</span></span>'),
			'mess_mpicon' 			   => '<a href="'.manage_url('index.php?act=mp&amp;sub=3&amp;mpto='.$msgs['msg_userid'],'forum-mp-write.html?mpto='.$msgs['msg_userid']).'" class="mp" title="'.lang('mp').'"><span>'.lang('mp').'</span></a>',
			'mess_mailicon' 		   => ((!empty($msgs['messemail']))?'<a href="mailto: '.$msgs['messemail'].'" class="mail" title="'.lang('mail').'"><span>'.lang('mail').'</span></a>':''),
			'mess_wwwicon'			   => ((!empty($msgs['messwebsite']))?'<a href="'.((!preg_match('`^(http|ftp)://(.+?)$`',$msgs['messwebsite']))?'http://':'').$msgs['messwebsite'].'" class="www" title="'.lang('www').'"><span>'.lang('www').'</span></a>':''),
			'u_canpunish'			   => ($_SESSION['cb_user']->isModerator() && $GLOBALS['cb_classes'][$msgs['messgroupid']]['gr_status']==0)?true:false
			));
	}

	// Boutons d'options pour le message
	$bopt=array();
	if ($_SESSION['cb_user']->logged)
		$bopt[] = 'report';
	if ($_SESSION['cb_user']->logged) {
		if ($_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup']) && !$firstmess)
			$bopt[] = 'delete';
	}
	if ($_SESSION['cb_user']->logged && $_SESSION['cb_user']->getAuth('reply',$topic['topic_fromtopicgroup'])) {
		if (($msgs['msg_userid']==$_SESSION['cb_user']->userid && $topic['topic_status']!=1) || $_SESSION['cb_user']->isMod($topic['topic_fromtopicgroup'])) {
			$bopt[] = 'edit';
		}
	}
	if ($_SESSION['cb_user']->getAuth('reply',$topic['topic_fromtopicgroup']) && $topic['topic_status']!=1)
		$bopt[] = 'quote';
	$msg['mess_buttonoptions'] = $bopt;

	// Avis d'édition
	$msg['mess_edited'] = $msgs['msg_modified']>0;
	if ($msg['mess_edited']) {
		$msg['mess_edit_userlink'] = '<a href="'.manage_url('index.php?act=user&amp;showprofile='.$msgs['msg_modifieduser'],'forum-m'.$msgs['msg_modifieduser'].','.rewrite_words($msgs['modifusername']).'.html').'">'.$msgs['modifusername'].'</a>';
		$msg['mess_edit_date1'] = dateFormat($msgs['msg_modified'],2);
		$msg['mess_edit_date2'] = dateFormat($msgs['msg_modified'],3);
	}

	// Signature
	$msg['mess_signature'] = $msgs['messcleansignature'];

	$t_messages[] = $msg;
	$firstmess=false;
}
$GLOBALS['cb_tpl']->assign_ref('t_messages',$t_messages);

/* FlashReply */
if ($topic['topic_status']!=1) {
	$GLOBALS['cb_tpl']->assign('t_fr_action',manage_url('index.php?act=wm&amp;addreply='.$topic['topic_id'],'forum-wmsg-t'.$topic['topic_id'].'.html'));
	$GLOBALS['cb_tpl']->assign('t_flashreply',true);
} else $GLOBALS['cb_tpl']->assign('t_flashreply',false);

/* On s'occupe des messages lus */
if (isset($_SESSION['cb_read_in']) && !in_array($topic['topic_fromtopicgroup'],$_SESSION['cb_read_in'])) 
	$_SESSION['cb_read_in'][] = $topic['topic_fromtopicgroup'];
if ($_SESSION['cb_user']->logged && $maxid > $_SESSION['cb_user']->mark_as_read) {
	if (isset($topic['utg_markasread']) && $maxid < $topic['utg_markasread']) {
		// On ne fait rien...
	} elseif (isset($topic['ut_msgread'])) {
		if ($maxid > $topic['ut_msgread']) $GLOBALS['cb_db']->query('UPDATE IGNORE '.$GLOBALS['cb_db']->prefix.'usertopics SET ut_msgread='.$maxid.' WHERE ut_userid='.$_SESSION['cb_user']->userid.' AND ut_topicid='.$topic['topic_id']);
	} else $GLOBALS['cb_db']->query('INSERT DELAYED INTO '.$GLOBALS['cb_db']->prefix.'usertopics(ut_userid,ut_topicid,ut_msgread) VALUES('.$_SESSION['cb_user']->userid.','.$topic['topic_id'].','.$maxid.')');
}

$GLOBALS['cb_tpl']->assign('g_part','part_showtopic.php');
?>