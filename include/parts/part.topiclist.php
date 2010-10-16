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

$GLOBALS['cb_tpl']->lang_load('ftg.lang');

/* Options possibles */
$page_title = '';
$page_url = '';
$w_noreply = false;
$w_posted = false;
$w_unread = false;
$w_bookmark = false;
$w_tracked = false;
$w_poll = false;
if (isset($_GET['poll']) && $_GET['poll']==1) {
	$w_poll = true;
	$page_url .= '&amp;poll=1';
}
if (isset($_GET['noreply']) && $_GET['noreply']==1) {
	$w_noreply = true;
	$page_title .= ' '.lang('tl_noreply');
	$page_url .= '&amp;noreply=1';
}
if ($_SESSION['cb_user']->logged) {
	if (isset($_GET['posted']) && $_GET['posted']==1) {
		$w_posted = true;
		$page_title .= ' '.lang('tl_posted');
		$page_url .= '&amp;posted=1';
	}
	if (isset($_GET['unread']) && $_GET['unread']==1) {
		$w_unread = true;
		$page_title .= ' '.lang('tl_unread');
		$page_url .= '&amp;unread=1';
	}
	if (isset($_GET['bookmark']) && $_GET['bookmark']==1) {
		$w_bookmark = true;
		$page_title .= ' '.lang('tl_bookmark');
		$page_url .= '&amp;bookmark=1';
	}
	if (isset($_GET['tracked']) && $_GET['tracked']==1) {
		if ($GLOBALS['cb_cfg']->config['enabletopictrack'] == 'yes') {
			$w_tracked = true;
			$page_title .= ' '.lang('tl_tracked');
			$page_url .= '&amp;tracked=1';
		} else trigger_error(lang('usr_topicstracked_notpossible'),E_USER_NOTICE);
	}
}

if (utf8_strlen($page_title) == 0) {
	$page_title = lang($w_poll?'tl_lastpolls':'tl_lastmessages');
} else {
	$page_title = ($w_poll?lang('tl_poll'):lang('topiclist')).$page_title;
}

/* Remaniement de l'url pour le rewrite */
$page_url = manage_url('index.php?act=tlist','forum-tlist.html').(($GLOBALS['cb_cfg']->config['url_rewrite']=='yes')?'?'.utf8_substr($page_url,5):$page_url);

/* Titre de la page */
$_SESSION['cb_user']->connected('index_topiclist');
$GLOBALS['cb_addressbar'][] = $page_title;
$GLOBALS['cb_pagename'][] = $page_title;
$GLOBALS['cb_tpl']->assign('tl_url',$page_url);
$GLOBALS['cb_tpl']->assign('tl_page_title',$page_title);

/* Gestion des numeros de pages. */
$pagenumber=1;
if (isset($_GET['page'])) $pagenumber=(int)$_GET['page'];

/* Requète */
$no_tgs = array();
if (count($_SESSION['cb_user']->gr_auth_see) > 0)
	$no_tgs = $_SESSION['cb_user']->gr_auth_see;
if (!$_SESSION['cb_user']->isModerator())
	$no_tgs = array_merge($no_tgs,$GLOBALS['cb_str_unvis']);

$topic_ids = array();

$returnbis=$GLOBALS['cb_db']->query('SELECT SQL_CALC_FOUND_ROWS topic_id
	FROM '.$GLOBALS['cb_db']->prefix.'topics t
	'.($_SESSION['cb_user']->logged?'
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'usertgs ON utg_userid='.$_SESSION['cb_user']->userid.' AND utg_tgid=t.topic_fromtopicgroup
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'usertopics ON ut_userid='.$_SESSION['cb_user']->userid.' AND ut_topicid=t.topic_id
	':'').'
	WHERE
		topic_status != 2
		'.($w_unread?' AND (ut_msgread IS NULL OR topic_lastmessage > ut_msgread)
		AND (utg_markasread IS NULL OR topic_lastmessage > utg_markasread)
		AND topic_lastmessage > '.$_SESSION['cb_user']->mark_as_read.' ':'').
		($w_posted?' AND ut_posted=1 ':'').
		($w_noreply?' AND topic_nbreply=0 ':'').
		($w_bookmark?' AND ut_bookmark=1 ':'').
		($w_tracked?' AND ut_mail=1 ':'').
		($w_poll?' AND topic_poll!=0 ':'').
		((count($no_tgs) > 0)?' AND t.topic_fromtopicgroup NOT IN ('.implode(',',$no_tgs).')':'').'
	ORDER BY topic_lastmessage DESC
	LIMIT '.(($pagenumber-1)*$_SESSION['cb_user']->usr_pref_topics).','.$_SESSION['cb_user']->usr_pref_topics
	);

while ($tid = $GLOBALS['cb_db']->fetch_assoc($returnbis))
	$topic_ids[] = $tid['topic_id'];

$nbtopics = $GLOBALS['cb_db']->single_result('SELECT FOUND_ROWS()');

$GLOBALS['cb_tpl']->assign(array(
	'tg_pagemenu' => pageMenu($nbtopics,$pagenumber,$_SESSION['cb_user']->usr_pref_topics,$page_url.((utf8_strpos($page_url,'?') !== false)?'&amp;':'?').'page=[num_page]'),
	'tl_unread_chk' => (isset($_GET['unread']) && (int)$_GET['unread'] == 1)?'checked="checked"':'',
	'tl_posted_chk' => (isset($_GET['posted']) && (int)$_GET['posted'] == 1)?'checked="checked"':'',
	'tl_noreply_chk' => (isset($_GET['noreply']) && (int)$_GET['noreply'] == 1)?'checked="checked"':'',
	'tl_bookmark_chk' => (isset($_GET['bookmark']) && (int)$_GET['bookmark'] == 1)?'checked="checked"':'',
	'tl_tracked_chk' => (isset($_GET['tracked']) && (int)$_GET['tracked'] == 1)?'checked="checked"':'',
	'tl_poll_chk' => (isset($_GET['poll']) && (int)$_GET['poll'] == 1)?'checked="checked"':''
	));

/* Affichage des résultats */
if ($nbtopics) {
	require_once(CB_PATH.'include/lib/lib.topics.php');
	$GLOBALS['cb_tpl']->assign_ref('tg_groups',getTopics($topic_ids,false,true));
	$GLOBALS['cb_addressbar_double'] = true;
} else {
	trigger_error(lang('tg_noresults'),E_USER_NOTICE);
}

$GLOBALS['cb_tpl']->assign('g_part','part_topiclist.php');
?>