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
if (!defined('CB_ADMIN')) exit('Access denied!');

$redirect = 0;
if (isset($_POST['changeforuminfo'])) {
	if (isset($_POST['foruminfotop'])) {
		$GLOBALS['cb_cfg']->updateElements(array('foruminfotop' => clean($_POST['foruminfotop'],STR_MULTILINE + STR_PARSEBB)));
		$redirect = 1;
	} elseif (isset($_POST['foruminfobot'])) {
		$GLOBALS['cb_cfg']->updateElements(array('foruminfobot' => clean($_POST['foruminfobot'],STR_MULTILINE + STR_PARSEBB)));
		$redirect = 2;
	} elseif (isset($_POST['forumrules'])) {
		$GLOBALS['cb_cfg']->updateElements(array('forumrules' => clean($_POST['forumrules'],STR_MULTILINE + STR_PARSEBB)));
		$redirect = 3;
	}
}
if (isset($_POST['changeforuminfo_dyn']) && $_SESSION['cb_user']->userid == 1) {
	if (isset($_POST['foruminfotop_dyn'])) {
		$GLOBALS['cb_cfg']->updateElements(array('foruminfotop_dyn' => $GLOBALS['cb_db']->escape($_POST['foruminfotop_dyn'])));
		$redirect = 1;
	} elseif (isset($_POST['foruminfobot_dyn'])) {
		$GLOBALS['cb_cfg']->updateElements(array('foruminfobot_dyn' => $GLOBALS['cb_db']->escape($_POST['foruminfobot_dyn'])));
		$redirect = 0;
	}
}
if ($redirect != 0) redirect(manage_url('admin.php','forum-admin.html').'?act=ci&sub='.$redirect);

$sub=(isset($_GET['sub']) && $_GET['sub']<4 && $_GET['sub']>0)?$_GET['sub']:1;

if ($sub==1) {
	$GLOBALS['cb_tpl']->assign('pa_changeinfos_title','pa_changefinfotop');
	$GLOBALS['cb_tpl']->assign('pa_changeinfos_info','pa_infochangefinfo');
	$GLOBALS['cb_tpl']->assign('pa_changeinfos_dynamic_fields',($_SESSION['cb_user']->userid == 1));
	if ($_SESSION['cb_user']->userid == 1) $GLOBALS['cb_tpl']->assign('pa_changeinfos_msg_dyn',$GLOBALS['cb_cfg']->config['foruminfotop_dyn']);
	$GLOBALS['cb_tpl']->assign('pa_changeinfos_msg',unclean($GLOBALS['cb_cfg']->config['foruminfotop']));
	$GLOBALS['cb_tpl']->assign('pa_inputfield','foruminfotop');
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_changeinfos','pa_changeinfos_top'));
} elseif ($sub==2) {
	$GLOBALS['cb_tpl']->assign('pa_changeinfos_title','pa_changefinfobot');
	$GLOBALS['cb_tpl']->assign('pa_changeinfos_info','pa_infochangefinfo');
	$GLOBALS['cb_tpl']->assign('pa_changeinfos_dynamic_fields',($_SESSION['cb_user']->userid == 1));
	if ($_SESSION['cb_user']->userid == 1) $GLOBALS['cb_tpl']->assign('pa_changeinfos_msg_dyn',$GLOBALS['cb_cfg']->config['foruminfobot_dyn']);
	$GLOBALS['cb_tpl']->assign('pa_changeinfos_msg',unclean($GLOBALS['cb_cfg']->config['foruminfobot']));
	$GLOBALS['cb_tpl']->assign('pa_inputfield','foruminfobot');
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_changeinfos','pa_changeinfos_bot'));
} elseif ($sub==3) {
	$GLOBALS['cb_tpl']->assign('pa_changeinfos_title','pa_rules');
	$GLOBALS['cb_tpl']->assign('pa_changeinfos_info','pa_rules_info');
	$GLOBALS['cb_tpl']->assign('pa_changeinfos_dynamic_fields',false);
	$GLOBALS['cb_tpl']->assign('pa_changeinfos_msg',unclean($GLOBALS['cb_cfg']->config['forumrules']));
	$GLOBALS['cb_tpl']->assign('pa_inputfield','forumrules');
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_changeinfos','pa_changeinfos_rules'));
}

$GLOBALS['cb_tpl']->assign('g_part','admin_changeinfos.php');
?>
