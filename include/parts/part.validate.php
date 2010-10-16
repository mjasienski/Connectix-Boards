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

if ($_SESSION['cb_user']->logged)
	trigger_error(lang('error_alreadylogged'),E_USER_ERROR);

$GLOBALS['cb_tpl']->lang_load('auxi.lang');
$_SESSION['cb_user']->connected('index_validate');
$GLOBALS['cb_addressbar'][] = lang('validate');
$GLOBALS['cb_pagename'][] = lang('validate');

/* Vï¿½rification de la validitï¿½ du hash et actions vis-ï¿½-vis de cela. */
if (isset($_GET['hash']) || isset($_GET['regcode'])) {
	$validation = isset($_GET['regcode']) ? $_GET['regcode'] : $_GET['hash'];
	$retour = $GLOBALS['cb_db']->query('SELECT usr_registered FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_registered=\''.clean($validation).'\'');
	if ($trash=$GLOBALS['cb_db']->fetch_assoc($retour)) {
		/* Validation dans la bdd. */
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_registered=\'TRUE\' WHERE usr_registered=\''.clean($validation).'\'');
		/* Augmentation du nombre d'utilisateurs vï¿½rifiï¿½s. */
		if (!preg_match('#^change#',clean($validation))) {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value+1 WHERE st_field=\'registered_users\'');
		}
		trigger_error(lang('val_confirmed_mess'),E_USER_NOTICE);
	} else trigger_error(lang('val_badregcode'),E_USER_WARNING);
} elseif (isset($_GET['changepass']) && utf8_strlen($_GET['changepass'])==32) {
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_password=usr_changepass,usr_changepass=\'\',usr_changepass_c=\'\' WHERE usr_changepass_c=\''.clean($_GET['changepass']).'\'');
	if ($GLOBALS['cb_db']->affected_rows() > 0) trigger_error(lang('val_cp_ok'),E_USER_NOTICE);
	else trigger_error(lang('val_cp_ko'),E_USER_WARNING);
} else trigger_error(lang('val_badregcode'),E_USER_WARNING);

?>