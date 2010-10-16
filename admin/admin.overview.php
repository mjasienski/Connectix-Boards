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

$GLOBALS['cb_tpl']->assign('g_subtitle','pa_o_title');

if (function_exists('fsockopen')) {
	error_reporting(0);
	$handle = @fsockopen('www.connectix-boards.org', 80, $errno, $errstr, 1.2);
	if(!$handle) $GLOBALS['cb_tpl']->assign('o_newversion','pa_o_nv_nofile');
	else {
		$out = "GET /cb_last.txt HTTP/1.1\r\n";
		$out .= "Host: www.connectix-boards.org\r\n";
		$out .= "Connection: Close\r\n\r\n";

		/* On récupère la dernière ligne du fichier */
		$in = '';
		fwrite($handle, $out);
		while (!feof($handle)) {
			$in = fgets($handle, 128);
		}
		fclose($handle);

		$last_v = trim(strip_tags(str_replace(array("\n","\r",' '),'',trim($in))));
		if(isset($GLOBALS['cb_cfg']->config['forumversion']) && $GLOBALS['cb_cfg']->config['forumversion'] == $last_v)
			$GLOBALS['cb_tpl']->assign('o_newversion','pa_o_nv_goodversion');
		elseif ($last_v == CB_VERSION)
			$GLOBALS['cb_tpl']->assign('o_newversion','pa_o_nv_installupgrade');
		elseif (!empty($last_v))
			$GLOBALS['cb_tpl']->assign('o_newversion','pa_o_nv_notgoodversion');
		else
			$GLOBALS['cb_tpl']->assign('o_newversion','pa_o_nv_nofile');
	}
	error_reporting(E_ALL);
} else $GLOBALS['cb_tpl']->assign('o_newversion','pa_o_nv_nofile');

$GLOBALS['cb_tpl']->assign('g_part','admin_overview.php');
?>
