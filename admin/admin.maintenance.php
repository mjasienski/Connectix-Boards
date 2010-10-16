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
if (isset($_POST['resetcache'])) {
	// Cache des smileys
	require_once(CB_PATH.'include/lib/class.smileysmanager.php');
	$sm = new smileysmanager();
	$sm->cacheSmileys();

	//Cache de la structure du forum
	cacheStructure();

	// Cache des modérateurs
	cacheMods();

	// Cache des groupes d'utilisateurs
	cacheClasses();

	// Cache de la configuration
	$GLOBALS['cb_cfg']->cacheConfig();

	trigger_error(lang('pa_mt_resetcache_notice'),E_USER_NOTICE);
} elseif (isset($_POST['resetstats'])) {
	resetStats();
	trigger_error(lang('pa_mt_resetstats_notice'),E_USER_NOTICE);
}

$GLOBALS['cb_tpl']->assign('g_subtitle','pa_maintenance');

$GLOBALS['cb_tpl']->assign('g_part','admin_maintenance.php');
?>