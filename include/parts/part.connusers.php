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

$_SESSION['cb_user']->connected('index_connusers');
$GLOBALS['cb_tpl']->lang_load('auxi.lang');
$GLOBALS['cb_addressbar'][] = lang('connusers');
$GLOBALS['cb_pagename'][] = lang('connusers');

require_once(CB_PATH.'include/lib/lib.users.php');

$return = $GLOBALS['cb_db']->query('SELECT con_timestamp,con_position,usr_id,usr_name,gr_name,gr_color
	FROM '.$GLOBALS['cb_db']->prefix.'connected c
	LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'users u ON c.con_id=u.usr_id
	LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'groups g ON g.gr_id=u.usr_class
	WHERE con_timestamp>'.(time()-($GLOBALS['cb_cfg']->config['connectedlimit']*60)).'
	ORDER BY con_timestamp DESC');

/* Affichage des utilisateurs */
$people = array();
while ($ppl=$GLOBALS['cb_db']->fetch_assoc($return)) {
	$pos = getUserLocation ($ppl['con_position']);

	$people[] = array(
		'ppl_location' 	=> 'ttl_'.$pos['position'],
		'ppl_f' 		=> $pos['f'],
		'ppl_tg'		=> $pos['tg'],
		'ppl_link' 		=> ((isset($ppl['usr_id']))?'<a href="'.manage_url('index.php?act=user&amp;showprofile='.$ppl['usr_id'],'forum-m'.$ppl['usr_id'].','.rewrite_words($ppl['usr_name']).'.html').'" title="'.$ppl['gr_name'].'" style="color:'.$ppl['gr_color'].';">'.$ppl['usr_name'].'</a>':''),
		'ppl_lastclick' => dateFormat($ppl['con_timestamp'])
		);
}
$GLOBALS['cb_tpl']->assign_ref('cu_ppl',$people);

$GLOBALS['cb_tpl']->assign('g_part','part_connusers.php');
?>