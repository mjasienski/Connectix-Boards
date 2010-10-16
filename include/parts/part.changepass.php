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
$GLOBALS['cb_addressbar'][] = lang('changepass');
$GLOBALS['cb_pagename'][] = lang('changepass');

/* Gestion des variables POST. */
if (isset($_POST['email'])) {
	$random_conf = genValidCode();
	$random_pass = utf8_substr(str_shuffle($random_conf),0,8);
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_changepass=\''.cbHash($random_pass).'\',usr_changepass_c=\''.$random_conf.'\' WHERE usr_email=\''.clean($_POST['email']).'\'');
	if ($GLOBALS['cb_db']->affected_rows() > 0) {
		$patterns=array(
			'{--mail_user_name--}'	 =>  $GLOBALS['cb_db']->single_result('SELECT usr_name FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_email=\''.clean($_POST['email']).'\''),
			'{--mail_user_password--}' =>  $random_pass,
			'{--mail_forumname--}'	 =>  $GLOBALS['cb_cfg']->config['forumname'],
			'{--mail_confirm_link--}'  =>  'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).((utf8_substr(dirname($_SERVER['PHP_SELF']),-1)!=='/')?'/':'').manage_url('index.php?act=validate&changepass='.$random_conf,'forum-validate.html?changepass='.$random_conf),
			'{--mail_forum_owner--}'   =>  $GLOBALS['cb_cfg']->config['forumowner']
			);
		$mailmsg=str_replace(array_keys($patterns),$patterns,$GLOBALS['cb_cfg']->config['mail_cp']);

		require_once(CB_PATH.'include/lib/lib.mails.php');
		if (sendMail(clean($_POST['email'],STR_TODISPLAY),str_replace('{--mail_forumname--}',$GLOBALS['cb_cfg']->config['forumname'],$GLOBALS['cb_cfg']->config['mailsubject_cp']),$mailmsg)) {
			trigger_error(lang('cp_success_mail'),E_USER_NOTICE);
		} else trigger_error(lang('error_sendmail'),E_USER_WARNING);
	} else trigger_error(lang('error_user_noexist'),E_USER_WARNING);
}

$_SESSION['cb_user']->connected('index_changepass');
$GLOBALS['cb_tpl']->assign('cp_username',(isset($_POST['email']))?clean($_POST['email'],STR_TODISPLAY):'');

$GLOBALS['cb_tpl']->assign('g_part','part_changepass.php');
?>