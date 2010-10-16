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

/* Accueil ou forum particulier */
$forumid = null; 
if (isset($_GET['showforum']) && is_numeric($_GET['showforum'])) {
	if (isForum((int)$_GET['showforum'])) $forumid = (int)$_GET['showforum'];
	else trigger_error(lang('error_f_noexist'),E_USER_ERROR);
}

/* Markread */
if ($_SESSION['cb_user']->logged && isset($_GET['markread'])) {
	require_once(CB_PATH.'include/lib/lib.structure.php');
	markread($forumid);
}

/* Quelques variables à instancier pour les templates et autres... */
$GLOBALS['cb_tpl']->assign('f_markread_link',manage_url('index.php?'.((!empty($forumid))?'showforum='.(int)$forumid.'&amp;':'').'markread=1','forum'.((!empty($forumid))?'-f'.(int)$forumid:'').'-mr.html'));
$GLOBALS['cb_tpl']->assign('f_home',(empty($forumid)));

if ($forumid != null) {
	$_SESSION['cb_user']->connected('index_'.$forumid);
	$GLOBALS['cb_addressbar'][] = $GLOBALS['cb_str_fnames'][$forumid];
	$GLOBALS['cb_pagename'][] = $GLOBALS['cb_str_fnames'][$forumid];
} else {
	$GLOBALS['cb_rsslink'] = '<link rel="alternate" type="application/rss+xml" title="'.$GLOBALS['cb_cfg']->config['forumname'].'" href="http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).((utf8_substr(dirname($_SERVER['PHP_SELF']),-1)!='/')?'/':'').'rss.php?showall=1" />';
	$_SESSION['cb_user']->connected('index');
}

/* On récupère ce qu'il faut afficher */
require_once(CB_PATH.'include/lib/lib.forums.php');
$list = &getForums($forumid);

/* On dit au moteur de templates quoi afficher */
if (empty($list)) {
	trigger_error(lang('error_f_notfound'),E_USER_WARNING);
} else {
	$GLOBALS['cb_tpl']->assign_ref('f_forums',$list);
	$GLOBALS['cb_tpl']->assign('g_part','part_showforum.php');
}
?>