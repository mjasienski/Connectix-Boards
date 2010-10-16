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

require_once(CB_PATH.'include/lib/lib.users.php');

if (isset($_POST['changeforumsettings'])) {
	$toUpdate = array();
	foreach ($_POST as $key => $value) {
		if ($key!=='changeforumsettings') {
			$toUpdate[clean($key)] = clean($value);
		}
	}
	$GLOBALS['cb_cfg']->updateElements($toUpdate);
	trigger_error(lang('pa_confirmed'),E_USER_NOTICE);
}

$GLOBALS['cb_cfg']->resetConfig();

$GLOBALS['cb_tpl']->assign('pa_c_fname',$GLOBALS['cb_cfg']->config['forumname']);
$GLOBALS['cb_tpl']->assign('pa_c_fowner',$GLOBALS['cb_cfg']->config['forumowner']);
$GLOBALS['cb_tpl']->assign('pa_c_supportmail',$GLOBALS['cb_cfg']->config['supportmail']);
$GLOBALS['cb_tpl']->assign('pa_c_defstyle',skinMenu('defaultstyle',$GLOBALS['cb_cfg']->config['defaultstyle']));
$GLOBALS['cb_tpl']->assign('pa_c_deflanguage',langMenu('defaultlanguage',$GLOBALS['cb_cfg']->config['defaultlanguage']));
$GLOBALS['cb_tpl']->assign('pa_c_connectedlimit',$GLOBALS['cb_cfg']->config['connectedlimit']);
$GLOBALS['cb_tpl']->assign('pa_c_maxsize',$GLOBALS['cb_cfg']->config['maxsize']);
$GLOBALS['cb_tpl']->assign('pa_c_floodlimit',$GLOBALS['cb_cfg']->config['floodlimit']);
$GLOBALS['cb_tpl']->assign('pa_cookie_path',$GLOBALS['cb_cfg']->config['cookie_path']);
$GLOBALS['cb_tpl']->assign('pa_c_enablemail_yes_checked',(($GLOBALS['cb_cfg']->config['enablemail']=='yes')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_enablemail_no_checked',(($GLOBALS['cb_cfg']->config['enablemail']=='no')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_enabletopictrack_yes_checked',(($GLOBALS['cb_cfg']->config['enabletopictrack']=='yes')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_enabletopictrack_no_checked',(($GLOBALS['cb_cfg']->config['enabletopictrack']=='no')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_displayconnected_yes_checked',(($GLOBALS['cb_cfg']->config['displayconnected']=='yes')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_displayconnected_no_checked',(($GLOBALS['cb_cfg']->config['displayconnected']=='no')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_displayfastredirect_yes_checked',(($GLOBALS['cb_cfg']->config['displayfastredirect']=='yes')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_displayfastredirect_no_checked',(($GLOBALS['cb_cfg']->config['displayfastredirect']=='no')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_deletet_yes_checked',(($GLOBALS['cb_cfg']->config['deleteallowed']=='yes')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_deletet_no_checked',(($GLOBALS['cb_cfg']->config['deleteallowed']=='no')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_gzip_output_yes_checked',(($GLOBALS['cb_cfg']->config['gzip_output']=='yes')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_gzip_output_no_checked',(($GLOBALS['cb_cfg']->config['gzip_output']=='no')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_suspendregister_yes_checked',(($GLOBALS['cb_cfg']->config['suspend_register']=='yes')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_suspendregister_no_checked',(($GLOBALS['cb_cfg']->config['suspend_register']=='no')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_readornot_sessions_yes_checked',(($GLOBALS['cb_cfg']->config['readornot_sessions']=='yes')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_readornot_sessions_no_checked',(($GLOBALS['cb_cfg']->config['readornot_sessions']=='no')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_edittopictitle_yes_checked',(($GLOBALS['cb_cfg']->config['edittopictitle']=='yes')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_edittopictitle_no_checked',(($GLOBALS['cb_cfg']->config['edittopictitle']=='no')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_backtowebsite',$GLOBALS['cb_cfg']->config['website']);
$GLOBALS['cb_tpl']->assign('pa_c_timezones',getTimeZones());
$GLOBALS['cb_tpl']->assign('pa_c_timezone_default',$GLOBALS['cb_cfg']->config['timezone']);
$GLOBALS['cb_tpl']->assign('pa_c_summertime_yes_checked',(($GLOBALS['cb_cfg']->config['summertime']=='yes')?'checked="checked" ':''));
$GLOBALS['cb_tpl']->assign('pa_c_summertime_no_checked',(($GLOBALS['cb_cfg']->config['summertime']=='no')?'checked="checked" ':''));

$GLOBALS['cb_tpl']->assign('g_subtitle','pa_settings');

$GLOBALS['cb_tpl']->assign('g_part','admin_config.php');
?>
