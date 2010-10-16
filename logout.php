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
define('CB_INC', 'CB');
require('common.php');

$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'connected WHERE con_id='.$_SESSION['cb_user']->conId());
$_SESSION['cb_user']->userCookies();

session_destroy();
session_start();

$_SESSION = array();
$_SESSION['destroyable']=false;

$referer = parse_url($_SERVER['HTTP_REFERER']);
if (isset($_SERVER['HTTP_REFERER']) && isset($referer['host']) && $referer['host'] == $_SERVER['HTTP_HOST']) {
	header('Location: '.$_SERVER['HTTP_REFERER']);
	exit();
} else redirect();
?>