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
if (isset($_POST['pause'],$_POST['pausemessage']))
	$GLOBALS['cb_cfg']->updateElements(array('paused' => (($_POST['pause']=='on')?'yes':'no'),'pausemessage' => clean($_POST['pausemessage'],STR_MULTILINE + STR_PARSEBB)));

$GLOBALS['cb_tpl']->assign('g_subtitle','pa_pause');

$GLOBALS['cb_tpl']->assign('p_yes_checked',((isPaused())?'checked="checked"':''));
$GLOBALS['cb_tpl']->assign('p_no_checked',((!isPaused())?'checked="checked"':''));
$GLOBALS['cb_tpl']->assign('p_pausemessage_contents',unclean($GLOBALS['cb_cfg']->config['pausemessage']));

$GLOBALS['cb_tpl']->assign('g_part','admin_pause.php');
?>
