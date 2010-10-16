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

$sub=(isset($_GET['sub']) && (int)$_GET['sub']>0 && (int)$_GET['sub']<3)?(int)$_GET['sub']:1;

if ($sub==2 && isset($_POST['am_send'])) {
	if (!empty($_POST['am_name']) && !empty($_POST['am_message'])) {
		if (isset($_GET['edit']) && is_numeric($_GET['edit'])) $GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'automessages SET am_name=\''.clean($_POST['am_name']).'\',am_message=\''.clean($_POST['am_message'],STR_MULTILINE + STR_PARSEBB).'\' WHERE am_id='.(int)$_GET['edit']);
		else $GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'automessages(am_name,am_message) VALUES(\''.clean($_POST['am_name']).'\',\''.clean($_POST['am_message'],STR_MULTILINE + STR_PARSEBB).'\')');
		redirect(manage_url('admin.php','forum-admin.html').'?act=am&sub=1');
	}
} elseif ($sub==1 && isset($_GET['delete'])) {
	$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'automessages WHERE am_id='.(int)$_GET['delete']);
}

if ($sub==1) {
	$automessages=array();
	$ret=$GLOBALS['cb_db']->query('SELECT am_id,am_name,am_message FROM '.$GLOBALS['cb_db']->prefix.'automessages ORDER BY am_id ASC');
	$first=true;
	while ($data=$GLOBALS['cb_db']->fetch_assoc($ret)) {
		$automessages[] = array(
			'pa_am_name' => $data['am_name'],
			'pa_am_id' => $data['am_id'],
			);
		$first=false;
		if (isset($_GET['see']) && $_GET['see']==$data['am_id']) {
			$GLOBALS['cb_tpl']->assign('pa_previs_name',$data['am_name']);
			$GLOBALS['cb_tpl']->assign('pa_previs',$data['am_message']);
		}
	}
	$GLOBALS['cb_tpl']->assign('pa_am_automessages',$automessages);

	if ($first) trigger_error(lang('pa_am_noam'),E_USER_WARNING);

	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_automessages','pa_automessages_all'));
	$GLOBALS['cb_tpl']->assign('am_part','am_showall');
} elseif ($sub==2) {
	$edit=false;
	$name='';
	$message='';
	if (isset($_GET['edit'])) {
		$ret=$GLOBALS['cb_db']->query('SELECT am_name,am_message FROM '.$GLOBALS['cb_db']->prefix.'automessages WHERE am_id='.(int)$_GET['edit']);
		if ($data=$GLOBALS['cb_db']->fetch_assoc($ret)) {
			$edit=true;
			$name=$data['am_name'];
			$message=$data['am_message'];
		}
	}
	$GLOBALS['cb_tpl']->assign('pa_am_add_name',$name);
	$GLOBALS['cb_tpl']->assign('pa_am_add_message',unclean($message));

	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_automessages',((!$edit)?'pa_automessages_add':'pa_automessages_edit')));
	$GLOBALS['cb_tpl']->assign('am_part','am_add');
}
$GLOBALS['cb_tpl']->assign('g_part','admin_automessages.php');
?>
