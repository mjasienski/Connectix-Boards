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

$rewrite_apache_loaded = rewrite_on();

if (isset($_POST['rw_disable'])) {
	$GLOBALS['cb_cfg']->updateElements(array('url_rewrite' => 'no'));
	if (file_exists(CB_PATH.'.htaccess')) unlink(CB_PATH.'.htaccess');
	redirect('admin.php?act=rewrite');
} elseif (isset($_POST['rw_enable']) && $rewrite_apache_loaded != 0) {
	file_put_contents(CB_PATH.'.htaccess',file_get_contents(CB_PATH.'admin/htaccess.txt'));
	$GLOBALS['cb_cfg']->updateElements(array('url_rewrite' => 'yes'));
	redirect('forum-admin.html?act=rewrite');
}

switch ($rewrite_apache_loaded) {
	case 1:
		$GLOBALS['cb_tpl']->assign('rw_msg','');
	break;

	case 0:
		$GLOBALS['cb_tpl']->assign('rw_msg','pa_rw_apache_ko');
	break;

	case -1:
		$GLOBALS['cb_tpl']->assign('rw_msg','pa_rw_verify');
	break;
}

$GLOBALS['cb_cfg']->resetConfig();

$GLOBALS['cb_tpl']->assign('rewrite_on',($GLOBALS['cb_cfg']->config['url_rewrite']=='yes'));
$GLOBALS['cb_tpl']->assign('rewrite_apache_on',$rewrite_apache_loaded);
$GLOBALS['cb_tpl']->assign('g_subtitle','pa_rewrite');

$GLOBALS['cb_tpl']->assign('g_part','admin_rewrite.php');
?>