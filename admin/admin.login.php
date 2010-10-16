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

/* Gestion des variables POST. */
if (isset($_POST['username'],$_POST['password'])) {
	if (!empty($_POST['username']) && !empty($_POST['password'])) {
		if ($_SESSION['cb_user']->isRegistered($_POST['username'],$_POST['password'],false,(isset($_POST['remember']) && $_POST['remember']=='on'))) {
			redirect(manage_url('admin.php','forum-admin.html').'');
		}
	} else trigger_error(lang('error_fillfields'),E_USER_WARNING);
}

$GLOBALS['cb_tpl']->assign('g_subtitle','login');
$GLOBALS['cb_tpl']->assign('g_part','admin_login.php');
?>
