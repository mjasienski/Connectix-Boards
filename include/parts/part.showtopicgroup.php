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

/* Vérifications sur le groupe de sujets */
if (!is_numeric($_GET['showtopicgroup']))
	redirect();

if (!isTg((int)$_GET['showtopicgroup']))
	trigger_error(lang('error_tg_noexist'),E_USER_ERROR);

if (!$_SESSION['cb_user']->getAuth('see',(int)$_GET['showtopicgroup']))
	trigger_error(lang('error_permerror'),E_USER_ERROR);

$GLOBALS['cb_tpl']->lang_load('ftg.lang');

/* ID du groupe de sujets courant et numéro de page */
$tgid = (int)$_GET['showtopicgroup'];
$pagenumber = (isset($_GET['page']))?(int)$_GET['page']:1;

/* Markread. */
if ($_SESSION['cb_user']->logged && isset($_GET['markread'])) {
	require_once(CB_PATH.'include/lib/lib.structure.php');
	markread(null,$tgid);
}

/* Redirection s'il s'agit d'un lien. */
if (!empty($GLOBALS['cb_str_tglinks'][$tgid])) {
	header('Location: '.$GLOBALS['cb_str_tglinks'][$tgid]);
	exit();
}

/* Gestion des variables de modération de masse */
if ($_SESSION['cb_user']->isMod($tgid) && (isset($_POST['mod_close']) || isset($_POST['mod_open']) || isset($_POST['mod_pin']) || isset($_POST['mod_unpin']) || isset($_POST['mod_delete']) || isset($_POST['mod_disp']))) {
	require_once(CB_PATH.'include/lib/lib.moderators.php');
	manageTgModOptions($tgid);
}
if ($_SESSION['cb_user']->isMod($tgid) && isset($_SESSION['cb_deletetopics'],$_GET['deletetopics']) && $_GET['deletetopics']==1 && ($GLOBALS['cb_cfg']->config['deleteallowed']=='yes' || $_SESSION['cb_user']->isAdmin())) {
	require_once(CB_PATH.'include/lib/lib.moderators.php');
	manageMassDelete($tgid);
	redirect(manage_url('index.php?showtopicgroup='.$tgid,'forum-tg'.$tgid.','.rewrite_words($GLOBALS['cb_str_tgnames'][$tgid]).'.html'));
}

/* On s'occupe des sous-groupes de sujets, s'il y en a */
if (isset($GLOBALS['cb_str_ptg'][$tgid])) {
	require_once(CB_PATH.'include/lib/lib.forums.php');
	$GLOBALS['cb_tpl']->assign_ref('f_forums',getForums(null,$tgid));
}

/* Infos générales sur le groupe de sujets */
$GLOBALS['cb_tpl']->assign('tg_id',$tgid);
$GLOBALS['cb_tpl']->assign('tg_name',$GLOBALS['cb_str_tgnames'][$tgid]);

$GLOBALS['cb_addressbar'] = array_merge($GLOBALS['cb_addressbar'],getTgPath($tgid));
$GLOBALS['cb_addressbar'][] = $GLOBALS['cb_str_tgnames'][$tgid];
$GLOBALS['cb_pagename'][] = $GLOBALS['cb_str_tgnames'][$tgid];

$GLOBALS['cb_rsslink'] = '<link rel="alternate" type="application/rss+xml" title="'.$GLOBALS['cb_cfg']->config['forumname'].' - '.$GLOBALS['cb_str_tgnames'][$tgid].'" href="http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).((utf8_substr(dirname($_SERVER['PHP_SELF']),-1)!='/')?'/':'').'rss.php?showtopicgroup='.$tgid.'" />';
$GLOBALS['cb_tpl']->assign('rss_tag','<a href="http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).((utf8_substr(dirname($_SERVER['PHP_SELF']),-1)!='/')?'/':'').'rss.php?showtopicgroup='.$tgid.'" class="ico_rss"><span>RSS</span></a>');

$_SESSION['cb_user']->connected('index_'.getTgPathIds($tgid));

/* Modérateurs de ce groupe */
require(CB_CACHE_MODS);
if (isset($tgmod[$tgid])) {
	$GLOBALS['cb_tpl']->assign('tg_moderatorsnames',$tgmod[$tgid]);
	$GLOBALS['cb_tpl']->assign('tg_modexist',true);
} else $GLOBALS['cb_tpl']->assign('tg_modexist',false);

/* Récupération des topics à afficher */
$return_tpcs=$GLOBALS['cb_db']->query('SELECT SQL_CALC_FOUND_ROWS topic_id
	FROM '.$GLOBALS['cb_db']->prefix.'topics
	WHERE topic_fromtopicgroup='.$tgid.'
	ORDER BY topic_type DESC,topic_lastmessage DESC
	LIMIT '.(($pagenumber-1)*$_SESSION['cb_user']->usr_pref_topics).','.($_SESSION['cb_user']->usr_pref_topics));

$topic_ids = array();
while ($tid = $GLOBALS['cb_db']->fetch_assoc($return_tpcs)) $topic_ids[] = $tid['topic_id'];

/* Nombre de topics total de la table pour ce groupe */
$nbtopics = $GLOBALS['cb_db']->single_result('SELECT FOUND_ROWS()');

/* Menus de pages et d'options */
$GLOBALS['cb_tpl']->assign('tg_pagemenu',pageMenu($nbtopics,$pagenumber,$_SESSION['cb_user']->usr_pref_topics,manage_url('index.php?showtopicgroup='.$tgid.'&amp;page=[num_page]','forum-tg'.$tgid.'-p[num_page],'.rewrite_words($GLOBALS['cb_str_tgnames'][$tgid]).'.html')));
$GLOBALS['cb_tpl']->assign('tg_optionbuttons',$_SESSION['cb_user']->getAuth('create',$tgid));
$GLOBALS['cb_tpl']->assign('tg_moderation',$_SESSION['cb_user']->isMod($tgid));
if ($_SESSION['cb_user']->isMod($tgid)) {
	$GLOBALS['cb_tpl']->assign('tg_candelete',($GLOBALS['cb_cfg']->config['deleteallowed']=='yes' || $_SESSION['cb_user']->isAdmin()));
	$GLOBALS['cb_tpl']->assign('tg_displacemenu',showForumMenu('mod_disp','tg_displacetopic',0,0,0,$tgid,true,'','f_',150));
}

/* Infos nécéssaires pour la suite */
$mrkasrd=null;
if ($_SESSION['cb_user']->logged) {
	if (false != $markasrd_temp = $GLOBALS['cb_db']->single_result('SELECT utg_markasread FROM '.$GLOBALS['cb_db']->prefix.'usertgs WHERE utg_userid = '.$_SESSION['cb_user']->userid.' AND utg_tgid = '.$tgid))
		$mrkasrd = $markasrd_temp;
}

/* Affichage final */
if ($nbtopics) {
	require_once(CB_PATH.'include/lib/lib.topics.php');
	$GLOBALS['cb_tpl']->assign_ref('tg_groups',getTopics($topic_ids,true,false,null,(($mrkasrd==null)?-1:$mrkasrd)));
	$GLOBALS['cb_addressbar_double'] = true;
} else trigger_error(lang('tg_noresults'),E_USER_NOTICE);

$GLOBALS['cb_tpl']->assign('g_part','part_showtopicgroup.php');
?>