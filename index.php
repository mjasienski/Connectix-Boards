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

/* Gestion du formulaire de connexion rapide */
$_SESSION['cb_user']->fastConnect(); 

/* Variables globales et communes à une page */
$GLOBALS['cb_pagename'] = array($GLOBALS['cb_cfg']->config['forumname']);
$GLOBALS['cb_rsslink'] = '';
$GLOBALS['cb_addressbar'] = array('<a href="'.manage_url('index.php', 'forum.html').'">'.$GLOBALS['cb_cfg']->config['forumname'].'</a>');
$GLOBALS['cb_addressbar_double'] = false;
$GLOBALS['cb_javascript'] = array(
	'<script type="text/javascript" src="include/javascripts/cb_base.js"></script>',
	'<script type="text/javascript" src="include/javascripts/cb_scroll.js"></script>',
	'<script type="text/javascript" src="lang/'.$_SESSION['cb_user']->getPreferredLang().'/javascript.lang.js"></script>',
	'<script type="text/javascript" src="include/javascripts/cb_bbcode.js"></script>');

/* Pages possibles - Abbréviations des noms de fichiers compatibles */
$acts = array(
	'wm'       => 'writemessage',
	'src'      => 'search',
	'cp'	   => 'changepass',
	'cu'	   => 'connusers',
	'tlist'    => 'topiclist'
	);

if (isPaused() && !$_SESSION['cb_user']->isAdmin())
	/* Le forum est en pause */
	message('<span class="i">'.lang('paused_info').'</span><br /><br />'.lang('adminmessage').' : <blockquote class="citation"><p>'.((!empty($GLOBALS['cb_cfg']->config['pausemessage']))?$GLOBALS['cb_cfg']->config['pausemessage']:lang('nomessage')).'</p></blockquote>');
else {
	/* On affiche ce qui a été demandé, sinon affichage du forum par défaut. */
	if (isset($_GET['act']) && preg_match('`[a-z]+`',$_GET['act']) && file_exists(CB_PATH.'include/parts/part.'.$_GET['act'].'.php')) 
		require(CB_PATH.'include/parts/part.'.$_GET['act'].'.php');
	elseif (isset($_GET['act']) && in_array($_GET['act'],array_keys($acts))) 
		require(CB_PATH.'include/parts/part.'.$acts[$_GET['act']].'.php');
	elseif (isset($_GET['showforum']))
		require(CB_PATH.'include/parts/part.showforum.php');
	elseif (isset($_GET['showtopicgroup'])) 
		require(CB_PATH.'include/parts/part.showtopicgroup.php');
	elseif (isset($_GET['showtopic']))
		require(CB_PATH.'include/parts/part.showtopic.php');
	else 
		require(CB_PATH.'include/parts/part.showforum.php');

	require(CB_PATH.'include/parts/part.general.php');

	$GLOBALS['cb_tpl']->display('gen_main.php');
}

$GLOBALS['cb_db']->close();
?>