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

$GLOBALS['cb_tpl']->lang_load('auxi.lang');
$_SESSION['cb_user']->connected('index_rules');
$GLOBALS['cb_addressbar'][] = lang('rules');
$GLOBALS['cb_pagename'][] = lang('rules');

$GLOBALS['cb_tpl']->assign('r_contents',$GLOBALS['cb_cfg']->config['forumrules']);
$GLOBALS['cb_tpl']->assign('g_part','part_rules.php');
?>
