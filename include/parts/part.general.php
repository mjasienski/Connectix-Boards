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

/* On rend le dernier lien de la barre d'adresse accessible en backlink */
if (count($GLOBALS['cb_addressbar']) > 1) {
	$ct_ab = count($GLOBALS['cb_addressbar']);
	for ($i = $ct_ab-2 ; $i>=0 ; $i--) {
		if (utf8_strpos($GLOBALS['cb_addressbar'][$i],'</a>') !== false) {
			$GLOBALS['cb_addressbar'][$i] = '<a class="backlink" '.utf8_substr($GLOBALS['cb_addressbar'][$i],3);
			break;
		}
	}
}

/* Variables de la page */
$GLOBALS['cb_tpl']->assign(array(
	'g_pagename' => implode(' - ',$GLOBALS['cb_pagename']),
	'g_pagesymbol' => (isset($_GET['act'],$acts[$_GET['act']]))?$acts[$_GET['act']]:'',
	'g_csslink' => $GLOBALS['cb_tpl']->get_css(),
	'g_javascript' => implode("\n\t",array_unique($GLOBALS['cb_javascript'])),
	'g_skinpath' => 'skins/'.$_SESSION['cb_user']->getPreferredSkin().'/',
	'g_rsslink' => $GLOBALS['cb_rsslink'],
	'g_forumname' => $GLOBALS['cb_cfg']->config['forumname'],
	'g_addr_sep' => CB_ADDR_SEP,
	'g_addressbar' => $GLOBALS['cb_addressbar'],
	'g_addressbar_double' => $GLOBALS['cb_addressbar_double'],
	'g_paused' => isPaused(),
	'g_langs' => langs(),
	'g_mpadv' => '',
	'g_islogged' => $_SESSION['cb_user']->logged
	));

/* Affichages dépendant du fait qu'on soit loggé ou pas */
if ($_SESSION['cb_user']->logged) {
	$GLOBALS['cb_tpl']->assign(array(
		'g_user_id' => $_SESSION['cb_user']->userid,
		'g_user_name' => $_SESSION['cb_user']->username,
		'g_user_link' => '<a href="'.manage_url('index.php?act=user&amp;showprofile='.$_SESSION['cb_user']->userid,'forum-m'.$_SESSION['cb_user']->userid.','.rewrite_words($_SESSION['cb_user']->username).'.html').'">'.$_SESSION['cb_user']->username.'</a>',
		'g_user_admin' => $_SESSION['cb_user']->isAdmin(),
		'g_user_mod' => $_SESSION['cb_user']->isModerator(),
		'g_newmessages' => $_SESSION['cb_user']->nbmp
		));

	if ($_SESSION['cb_user']->mpadv == 1) {
		$GLOBALS['cb_tpl']->assign('g_mpadv',$_SESSION['cb_user']->mpadv == 1);
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_mpadv=0 WHERE usr_id='.$_SESSION['cb_user']->userid);
		$_SESSION['cb_user']->mpadv = 0;
	}
} else $GLOBALS['cb_tpl']->assign('g_suspendregister',($GLOBALS['cb_cfg']->config['suspend_register']=='yes'));

/* Affichage des utilisateurs connectés sur cette partie du site. */
if ($_SESSION['cb_user']->connected_position=='index' || $GLOBALS['cb_cfg']->config['displayconnected']=='yes') {
	$returncon = $GLOBALS['cb_db']->query('SELECT usr_id,usr_name,usr_class
		FROM '.$GLOBALS['cb_db']->prefix.'connected
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id=con_id
		WHERE con_position LIKE \''.$_SESSION['cb_user']->connected_position.'%\' AND con_timestamp>'.(time()-($GLOBALS['cb_cfg']->config['connectedlimit']*60)).'
		ORDER BY con_timestamp DESC');
	
	require_once(CB_CACHE_CLASSES);
	
	$contents='';
	$nummembers=0;
	$numguests=0;
	while ($fetch=$GLOBALS['cb_db']->fetch_assoc($returncon)) {
		if (isset($fetch['usr_id'])) {
			$contents.=((!empty($contents))?' - ':'').'<a href="'.manage_url('index.php?act=user&amp;showprofile='.$fetch['usr_id'],'forum-m'.$fetch['usr_id'].','.rewrite_words($fetch['usr_name']).'.html').'" title="'.$GLOBALS['cb_classes'][$fetch['usr_class']]['gr_name'].'" '.((!empty($GLOBALS['cb_classes'][$fetch['usr_class']]['gr_color']))?'style="color:'.$GLOBALS['cb_classes'][$fetch['usr_class']]['gr_color'].';"':'').'>'.$fetch['usr_name'].'</a>';
			$nummembers++;
		} else $numguests++;
	}

	if (preg_match('`^index_[0-9]+$`',$_SESSION['cb_user']->connected_position))
		$position = 'index_f';
	elseif (preg_match('`^index(_[0-9]+)+$`',$_SESSION['cb_user']->connected_position))
		$position = 'index_tg';
	elseif (preg_match('`^index(_[0-9]+)+_t_[0-9]+$`',$_SESSION['cb_user']->connected_position))
		$position = 'index_t';
	elseif (preg_match('`^index(_[0-9]+)+_t_[0-9]+_wm$`',$_SESSION['cb_user']->connected_position))
		$position = 'index_t_wm';
	elseif (preg_match('`^index(_[0-9]+)+_wm$`',$_SESSION['cb_user']->connected_position))
		$position = 'index_tg_wm';
	else $position = $_SESSION['cb_user']->connected_position;

	$GLOBALS['cb_tpl']->assign(array(
		'g_displayconnected' => true,
		'g_membersconnectedtype' => $position,
		'g_membersconnected' => $contents,
		'g_connected_nummembers' => $nummembers,
		'g_connected_numguests' => $numguests,
		'g_connected_total' => ($nummembers+$numguests),
		'g_connected_minutes' => getTimeFormat($GLOBALS['cb_cfg']->config['connectedlimit']*60)
		));
} else $GLOBALS['cb_tpl']->assign('g_displayconnected',false);

/* Menu de redirection rapide */
$GLOBALS['cb_tpl']->assign('g_displayfastredirect',$GLOBALS['cb_cfg']->config['displayfastredirect']=='yes');
if ($GLOBALS['cb_cfg']->config['displayfastredirect']=='yes') {
	$GLOBALS['cb_tpl']->assign('g_selectskin_nbskins',nbSkins());
	$GLOBALS['cb_tpl']->assign('g_selectskin_menu',skinMenu('skin',$_SESSION['cb_user']->getPreferredSkin()));
	$GLOBALS['cb_tpl']->assign('g_fastredirect_menu',showForumMenu('showtopicgroup','fastredirect_index',0,(isset($_GET['showtopicgroup'])?(int)$_GET['showtopicgroup']:0),0,0,true,'','',null,'fastredirect_select'));
}

/* Statistiques */
if ($_SESSION['cb_user']->connected_position=='index') {
	require_once(CB_CACHE_CLASSES);
	$GLOBALS['cb_cfg']->setStats();

	$GLOBALS['cb_tpl']->assign(array(
		'g_membersregistered' => $GLOBALS['cb_cfg']->stats['registered_users'],
		'g_totalmessages' => $GLOBALS['cb_cfg']->stats['total_messages'],
		'g_totaltopics' => $GLOBALS['cb_cfg']->stats['total_topics'],
		'g_nbreports' => $GLOBALS['cb_cfg']->stats['nb_reports'],
		'g_classes_legend' => $GLOBALS['cb_legend'],
		'g_showstats' => true
	));
} else $GLOBALS['cb_tpl']->assign('g_showstats',false);

/* Affichage des requètes effectuées */
if (defined('CB_DISPLAY_QUERIES')) {
	$GLOBALS['cb_tpl']->lang_load('auxi.lang');
	$GLOBALS['cb_tpl']->assign(array(
		'g_debug_queries'			=> $GLOBALS['cb_db']->queriesdone,
		'g_debug_numberqueries'		=> $GLOBALS['cb_db']->gettotalqueries(),
		'g_debug_totalquerytime'	=> $GLOBALS['cb_db']->querytime.' sec',
		'g_debugging' 				=> true
		));
} else $GLOBALS['cb_tpl']->assign('g_debugging',false);

/* Informations diverses */
$GLOBALS['cb_tpl']->assign(array(
	'g_backtowebsite' => $GLOBALS['cb_cfg']->config['website'],
	'g_version' => $GLOBALS['cb_cfg']->config['forumversion'],
	'g_foruminfobottom' => $GLOBALS['cb_cfg']->config['foruminfobot'],
	'g_foruminfotop' => $GLOBALS['cb_cfg']->config['foruminfotop'],
	'g_foruminfobottom_dyn' => $GLOBALS['cb_cfg']->config['foruminfobot_dyn'],
	'g_foruminfotop_dyn' => $GLOBALS['cb_cfg']->config['foruminfotop_dyn']
	));
$GLOBALS['cb_tpl']->assign('g_queries',$GLOBALS['cb_db']->gettotalqueries());

/* Fin du timer de script. */
$GLOBALS['cb_tpl']->assign('g_execution',number_format((microtime_float()-$GLOBALS['micro1']),3));
?>