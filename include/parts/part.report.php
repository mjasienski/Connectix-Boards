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

if (!$_SESSION['cb_user']->logged)
	trigger_error(lang('error_permerror'),E_USER_ERROR);

$GLOBALS['cb_tpl']->lang_load('auxi.lang');
$GLOBALS['cb_addressbar'][] = lang('rep_report');
$GLOBALS['cb_pagename'][] = lang('report');

require_once(CB_PATH.'include/lib/lib.writing.php');

// Vérification si le message existe
if (isset($_GET['mess']) && !isMess((int)$_GET['mess']))
	redirect (manage_url('index.php','forum.html'));

// Gestion de l'envoi du signalement
if (isset($_POST['report'],$_POST['message'],$_GET['mess'])) {
	$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'reports (rep_desc,rep_msgid,rep_userid,rep_timestamp) VALUES(\''.clean($_POST['message'],STR_MULTILINE).'\','.(int)$_GET['mess'].','.$_SESSION['cb_user']->userid.','.time().')');
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value+1 WHERE st_field=\'nb_reports\'');
	$_SESSION['reported']=true;
	redirect(manage_url('index.php?act=report','forum-report.html'));
}

$_SESSION['cb_user']->connected('index_report');

$_SESSION['reported'] = (isset($_SESSION['reported']) && $_SESSION['reported']) ? true : false ;

if (isset($_GET['mess'])) {
	$GLOBALS['cb_tpl']->assign('r_needform',true);
} elseif ($_SESSION['reported']) {
	trigger_error(lang('rep_success'),E_USER_NOTICE);
	$GLOBALS['cb_tpl']->assign('r_needform',false);
} else redirect();

$GLOBALS['cb_tpl']->assign('g_part','part_report.php');
?>