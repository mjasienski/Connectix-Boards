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

$GLOBALS['cb_tpl']->assign(array(
	'g_csslink' => 'skins/'.$_SESSION['cb_user']->getPreferredSkin().'/style.css',
	'g_forumname' => $GLOBALS['cb_cfg']->config['forumname'],
	'g_version' => $GLOBALS['cb_cfg']->config['forumversion'],
	'g_queries' => $GLOBALS['cb_db']->gettotalqueries(),
	'g_addr_sep' => CB_ADDR_SEP
	));

if (isPaused())
	trigger_error($GLOBALS['cb_tpl']->lang['pa_paused'],E_USER_NOTICE);

$links=array();

if ($_SESSION['cb_user']->isAdmin()) {
	$links[]= array(
		'name' => 'pa_settings',
		'accesscode' => 'menu_settings',
		'nodisplay' => (isset($_GET['act']) && in_array($_GET['act'],array('str','cfg','pause','rewrite')))?false:true,
		'sub_links' => array(
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=cfg', 			'name' => 'pa_gensettings'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=rewrite',			'name' => 'pa_rewrite'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=pause',			'name' => 'pa_pause'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=str&amp;sub=1',	'name' => 'pa_structure_overview')
			)
		);
	
	$links[]= array(
		'name' => 'pa_groups',
		'accesscode' => 'menu_groups',
		'nodisplay' => (isset($_GET['act']) && $_GET['act'] == 'users' && in_array((int)$_GET['sub'],array(2,3,4,6,7)))?false:true,
		'sub_links' => array(
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=users&amp;sub=6', 'name' => 'pa_submenu_users_notconnected'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=users&amp;sub=2', 'name' => 'pa_submenu_users_classes'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=users&amp;sub=4', 'name' => 'pa_submenu_users_mods'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=users&amp;sub=7', 'name' => 'pa_submenu_users_lastactions')
			)
		);
	
	$links[]= array(
		'name' => 'pa_users',
		'accesscode' => 'menu_users',
		'nodisplay' => (isset($_GET['act']) && $_GET['act'] == 'users' && in_array((int)$_GET['sub'],array(1,5,8,9)))?false:true,
		'sub_links' => array(
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=users&amp;sub=1', 'name' => 'pa_submenu_users_addvaluser'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=users&amp;sub=5', 'name' => 'pa_submenu_users_changeuser'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=users&amp;sub=9', 'name' => 'pa_submenu_users_renameuser'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=users&amp;sub=8', 'name' => 'pa_submenu_users_mannotval')
			)
		);

	$links[]= array(
		'name' => 'pa_changeinfos',
		'accesscode' => 'menu_changeinfos',
		'nodisplay' => (isset($_GET['act']) && $_GET['act'] == 'ci')?false:true,
		'sub_links' => array(
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=ci&amp;sub=1', 'name' => 'pa_changeinfos_top'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=ci&amp;sub=2', 'name' => 'pa_changeinfos_bot'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=ci&amp;sub=3', 'name' => 'pa_changeinfos_rules')
			)
		);

	$links[]= array(
		'name' => 'pa_tools',
		'accesscode' => 'menu_tools',
		'nodisplay' => (isset($_GET['act']) && in_array($_GET['act'],array('bb','gal','am')))?false:true,
		'sub_links' => array(
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=bb&amp;sub=1', 'name' => 'pa_bbcode'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=bb&amp;sub=3', 'name' => 'pa_smileys'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=gal', 'name' => 'pa_gallery'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=am&amp;sub=1', 'name' => 'pa_automessages')
			)
		);

	$links[]= array(
		'name' => 'pa_database',
		'accesscode' => 'menu_database',
		'nodisplay' => (isset($_GET['act']) && $_GET['act'] == 'db')?false:true,
		'sub_links' => array(
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=db&amp;sub=1', 'name' => 'pa_db_deleteold'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=db&amp;sub=2', 'name' => 'pa_db_dump'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=db&amp;sub=3', 'name' => 'pa_db_reset')
			)
		);

	$links[]= array(
		'name' => 'pa_ip',
		'accesscode' => 'menu_ips',
		'nodisplay' => (isset($_GET['act']) && $_GET['act'] == 'ip')?false:true,
		'sub_links' => array(
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=ip&amp;sub=1', 'name' => 'pa_ip_show_banned'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=ip&amp;sub=2', 'name' => 'pa_ip_ban'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=ip&amp;sub=3', 'name' => 'pa_ip_analyze_ip'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=ip&amp;sub=4', 'name' => 'pa_ip_analyze_user'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=ip&amp;sub=5', 'name' => 'pa_ip_detect_double')
			)
		);
	
	$links[]= array(
		'name' => 'pa_maintenance',
		'main_link' => manage_url('admin.php','forum-admin.html').'?act=mt'
		);

	$links[]= array(
		'name' => 'pa_mails',
		'accesscode' => 'menu_mails',
		'nodisplay' => (isset($_GET['act']) && $_GET['act'] == 'mails')?false:true,
		'sub_links' => array(
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=mails&amp;sub=1', 'name' => 'pa_massmail'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=mails&amp;sub=2', 'name' => 'pa_changeconfirminscrmail'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=mails&amp;sub=3', 'name' => 'pa_changeconfirmchangemail'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=mails&amp;sub=4', 'name' => 'pa_changeconfirmchangepass'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=mails&amp;sub=5', 'name' => 'pa_changetopictrack'),
			array ( 'url' => manage_url('admin.php','forum-admin.html').'?act=mails&amp;sub=6', 'name' => 'pa_changemailmp')
			)
		);
}

$GLOBALS['cb_tpl']->assign('g_links',$links);

/* Fin du timer de script. */
$GLOBALS['cb_tpl']->assign('g_execution',number_format((microtime_float()-$GLOBALS['micro1']),3));
?>