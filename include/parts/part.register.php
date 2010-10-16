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

if ($GLOBALS['cb_cfg']->config['suspend_register'] == 'yes')
	trigger_error(lang('error_register_suspended'),E_USER_ERROR);

$GLOBALS['cb_tpl']->lang_load('auxi.lang');
$GLOBALS['cb_addressbar'][] = lang('register');
$GLOBALS['cb_pagename'][] = lang('register');
$GLOBALS['cb_javascript'][] = '<script type="text/javascript" src="include/javascripts/cb_ajax.js"></script>';

require_once(CB_PATH.'include/lib/lib.images.php');

/* Gestion du formulaire. */
$needform=true;
if (isset($_POST['username'])) {
	if (isset($_POST['password1'],$_POST['password2']) && !empty($_POST['password1']) && !empty($_POST['password2'])) {
		if ($_POST['password1']==$_POST['password2']) {
			if (isset($_POST['email1']) && !empty($_POST['email1'])) {
				if ((extension_loaded('gd') && in_array(IMAGETYPE_JPEG,getSupportedImages()) && isset($_POST['captcha']) && !empty($_POST['captcha'])) || (!extension_loaded('gd'))) {
					if ($_POST['captcha']==$_SESSION['verifnbr'] || (!extension_loaded('gd') && in_array(IMAGETYPE_JPEG,getSupportedImages()))) {
						if (isset($_POST['rules']) && $_POST['rules']=='on') {
							if ($GLOBALS['cb_db']->single_result('SELECT 1 FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_registertime>'.(time() - 3600).' AND usr_ip='.ip2long($_SERVER['REMOTE_ADDR'])) === false) {
								require_once(CB_PATH.'include/lib/lib.users.php');
								if (registerUser($_POST['username'],$_POST['password1'],$_POST['email1']))
									$needform=false;
							} else trigger_error(lang('error_reg_successiveregister'),E_USER_WARNING);
						} else trigger_error(lang('error_rules'),E_USER_WARNING);
					} else trigger_error(lang('error_reg_mistypenumber'),E_USER_WARNING);
				} else trigger_error(lang('error_reg_nonumber'),E_USER_WARNING);
			} else trigger_error(lang('error_reg_nomail'),E_USER_WARNING);
		} else trigger_error(lang('error_reg_mispasswords'),E_USER_WARNING);
	} else trigger_error(lang('error_reg_nopassword'),E_USER_WARNING);
}

$_SESSION['cb_user']->connected('index_register');

/* Préreremplissage du formulaire. */
$username  = (isset($_POST['username']))?clean($_POST['username'],STR_TODISPLAY):'';
$password1 = (isset($_POST['password1']))?clean($_POST['password1'],STR_TODISPLAY):'';
$password2 = (isset($_POST['password2']))?clean($_POST['password2'],STR_TODISPLAY):'';
$email1	= (isset($_POST['email1']))?clean($_POST['email1'],STR_TODISPLAY):'';

/* Affichage du formulaire. */
$GLOBALS['cb_tpl']->assign('r_needform',$needform);

if ($needform) {
	$GLOBALS['cb_tpl']->assign('r_action','http://'.htmlspecialchars($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));
	$GLOBALS['cb_tpl']->assign('r_form_pre_username',$username);
	$GLOBALS['cb_tpl']->assign('r_form_pre_pass1',$password1);
	$GLOBALS['cb_tpl']->assign('r_form_pre_pass2',$password2);
	$GLOBALS['cb_tpl']->assign('r_form_pre_mail',$email1);
	$GLOBALS['cb_tpl']->assign('r_rules',$GLOBALS['cb_cfg']->config['forumrules']);
	$GLOBALS['cb_tpl']->assign('r_form_pre_mail_confirmactivated',($GLOBALS['cb_cfg']->config['enablemail']=='yes'));
	$GLOBALS['cb_tpl']->assign('captcha_code',getCaptcha());
}

$GLOBALS['cb_tpl']->assign('g_part','part_register.php');
?>