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
/* Définition de la constante d'include. */
define('CB_INC', 'CB');
define('CB_ADMIN', 'CB');

require('common.php');
require(CB_PATH.'include/lib/lib.admin.php');

$GLOBALS['cb_tpl']->lang_load('paneladmin.lang');

if (!$_SESSION['cb_user']->logged) {
	require(CB_PATH.'admin/admin.login.php');
	$_SESSION['cb_user']->connected('index_login');
} elseif (!$_SESSION['cb_user']->isAdmin())
	redirect();
else {
	$_SESSION['cb_user']->connected('index_paneladmin');

	$acts = array(
		'overview' => 'overview',
		'ci'       => 'changeinfos',
		'cfg'      => 'config',
		'str'      => 'structure',
		'mails'    => 'mails',
		'users'    => 'users',
		'pause'    => 'pause',
		'bb'       => 'bbcode',
		'am'       => 'automessages',
		'db'	   => 'database',
		'gal'	   => 'gallery',
		'mt'	   => 'maintenance',
		'ip'	   => 'ip',
		'rewrite'  => 'rewrite'
		);

	if (isset($_GET['act']) && in_array($_GET['act'],array_keys($acts)))
		require(CB_PATH.'admin/admin.'.$acts[$_GET['act']].'.php');
	else
		require(CB_PATH.'admin/admin.overview.php');
}

require(CB_PATH.'admin/admin.general.php');

$GLOBALS['cb_tpl']->display('gen_main.php');

$GLOBALS['cb_db']->close();
?>
