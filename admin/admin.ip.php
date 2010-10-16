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

$sub=(isset($_GET['sub']))?(int)$_GET['sub']:1;

if ($sub==1 && isset($_GET['delete'])) {
	if (long2ip(ip2long($_GET['delete'])) == $_GET['delete'])
		$GLOBALS['cb_cfg']->removeBan(clean($_GET['delete']));
} elseif ($sub==2 && isset($_POST['ban_ip'])) {
	if (!empty($_POST['ip']) && isset($_POST['expires']) && is_numeric($_POST['expires'])) {
		if (is_numeric($_POST['expires']) && (int)$_POST['expires']>=0) {
			if (long2ip(ip2long($_POST['ip'])) == $_POST['ip']) {
				if ($GLOBALS['cb_cfg']->banIp(clean($_POST['ip']),((int)$_POST['expires'] != 0)?(time()+(((int)$_POST['expires'])*86400)):2147385647)) {
					trigger_error(str_replace('{ip}',clean($_POST['ip'],STR_TODISPLAY),lang('pa_ip_ban_success')),E_USER_NOTICE);
				}
			} else trigger_error(lang('pa_ip_ban_error_badip'),E_USER_WARNING);
		} else trigger_error(lang('pa_ip_ban_error_badexpires'),E_USER_WARNING);
	} else trigger_error(lang('pa_ip_ban_error_allfields'),E_USER_WARNING);
}

if ($sub==1) { // Afficher les ip bannies
	$banned = array();
	$r = $GLOBALS['cb_db']->query('SELECT ban_ip,ban_expires FROM '.$GLOBALS['cb_db']->prefix.'banned WHERE ban_expires>'.time().' ORDER BY ban_expires');
	while ($data = $GLOBALS['cb_db']->fetch_assoc($r)) {
		$banned[] = array('ban_ip' => long2ip($data['ban_ip']),'ban_expires' => dateFormat($data['ban_expires']));
	}
	$GLOBALS['cb_tpl']->assign('ip_banned',$banned);

	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_ip','pa_ip_show_banned'));
	$GLOBALS['cb_tpl']->assign('ip_part','show_banned');
} elseif ($sub==2) { // Bannir une ip
	$edit = false;
	if (isset($_GET['edit']) && long2ip(ip2long($_GET['edit'])) == $_GET['edit'] && !isset($_POST['ban_ip'])) {
		$exp = $GLOBALS['cb_db']->single_result('SELECT ban_expires FROM '.$GLOBALS['cb_db']->prefix.'banned WHERE ban_ip='.ip2long(clean($_GET['edit'])));
		if ($exp !== false) {
			$GLOBALS['cb_tpl']->assign('ban_ip',clean($_GET['edit']));
			$GLOBALS['cb_tpl']->assign('ban_expires',ceil(($exp-time())/86400));
			$edit=true;
		}
	}
	if (!$edit) {
		$GLOBALS['cb_tpl']->assign('ban_ip',(isset($_GET['ban']) && long2ip(ip2long($_GET['ban'])) == $_GET['ban'])?clean($_GET['ban'],STR_TODISPLAY):(isset($_POST['ip'])?clean($_POST['ip'],STR_TODISPLAY):''));
		$GLOBALS['cb_tpl']->assign('ban_expires',isset($_POST['expires'])?clean($_POST['expires'],STR_TODISPLAY):'');
	}
	$GLOBALS['cb_tpl']->assign('ban_editing',$edit);

	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_ip','pa_ip_ban'));
	$GLOBALS['cb_tpl']->assign('ip_part','ban_ip');
} elseif ($sub==3) { // Analyser une ip
	$results = array();
	if (isset($_POST['analyze_ip'])) {
		if (isset($_POST['ip']) && long2ip(ip2long($_POST['ip'])) == $_POST['ip']) {
			$r = $GLOBALS['cb_db']->query('SELECT usr_name,msg_id,msg_topicid,msg_userid,MAX(msg_timestamp) AS msg_lastdate,COUNT(*) AS total_msgs
				FROM '.$GLOBALS['cb_db']->prefix.'messages m
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users u ON u.usr_id=m.msg_userid
				WHERE msg_userip='.ip2long(clean($_POST['ip'])).' AND msg_userid > 0
				GROUP BY msg_userid
				ORDER BY msg_lastdate DESC');

			while ($data = $GLOBALS['cb_db']->fetch_assoc($r)) {
				$results[] = array(
					'topicid'		=> $data['msg_topicid'],
					'msgid'			=> $data['msg_id'],
					'userid' 		=> $data['msg_userid'],
					'username'		=> $data['usr_name'],
					'lastdate'		=> dateFormat($data['msg_lastdate']),
					'totalmsgs'		=> $data['total_msgs']
					);
			}

			if (count($results) == 0) trigger_error(lang('pa_ip_analyze_noresult'),E_USER_WARNING);
		} else trigger_error(lang('pa_ip_analyze_error_wrongip'),E_USER_WARNING);
	}
	$GLOBALS['cb_tpl']->assign('analyze_results',$results);
	$GLOBALS['cb_tpl']->assign('analyze_ip',(isset($_GET['analyze']) && long2ip(ip2long($_GET['analyze'])) == $_GET['analyze'] && !isset($_POST['analyze_ip']))?clean($_GET['analyze'],STR_TODISPLAY):(isset($_POST['ip'])?clean($_POST['ip'],STR_TODISPLAY):''));

	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_ip','pa_ip_analyze_ip'));
	$GLOBALS['cb_tpl']->assign('ip_part','analyze_ip');
} elseif ($sub==4) { // Analyser les ip d'un utilisateur
	$results = array();
	if (isset($_POST['analyze_user'])) {
		if (isset($_POST['user']) && !empty($_POST['user'])) {
			$r = $GLOBALS['cb_db']->query('SELECT msg_userip,msg_id,msg_topicid,MAX(msg_timestamp) AS msg_lastdate,COUNT(*) AS total_msgs
				FROM '.$GLOBALS['cb_db']->prefix.'messages m
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users u ON u.usr_id=m.msg_userid
				WHERE usr_name=\''.clean($_POST['user']).'\' AND msg_userip != 0 AND msg_userid > 0
				GROUP BY msg_userip
				ORDER BY msg_lastdate DESC');

			while ($data = $GLOBALS['cb_db']->fetch_assoc($r)) {
				$results[] = array(
					'topicid'		=> $data['msg_topicid'],
					'msgid'			=> $data['msg_id'],
					'userip'		=> long2ip($data['msg_userip']),
					'lastdate'		=> dateFormat($data['msg_lastdate']),
					'totalmsgs'		=> $data['total_msgs']
					);
			}

			if (count($results) == 0) trigger_error(lang('pa_ip_analyze_noresult'),E_USER_WARNING);
		} else trigger_error(lang('pa_ip_analyze_error_wronguser'),E_USER_WARNING);
	}
	$GLOBALS['cb_tpl']->assign('analyze_results',$results);
	$GLOBALS['cb_tpl']->assign('analyze_user',(isset($_GET['analyze']) && !isset($_POST['analyze_user']) && is_numeric($_GET['analyze']) && $username = getUserName((int)$_GET['analyze']))?$username:(isset($_POST['user'])?clean($_POST['user'],STR_TODISPLAY):''));

	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_ip','pa_ip_analyze_user'));
	$GLOBALS['cb_tpl']->assign('ip_part','analyze_user');
} elseif ($sub==5) { // Détecter les adresses IP utilisées par plusieurs membres
	$sqlver = $GLOBALS['cb_db']->single_result('SELECT VERSION()');
	$sqlver = explode('.',$sqlver);
	if ($sqlver[0] >= 4 && ($sqlver[0] > 4 || $sqlver[1] >= 1)) {
		$results = array();
		$pagenumber=1;
		if (isset($_GET['page'])) $pagenumber=(int)$_GET['page'];

		if (isset($_POST['dd_user'])) {
			if ($dd_usrid = $GLOBALS['cb_db']->single_result('SELECT usr_id FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_name=\''.clean($_POST['dd_user']).'\'')) {
				redirect(manage_url('admin.php?act=ip&sub=5&d_user='.$dd_usrid,'forum-admin.html?act=ip&sub=5&d_user='.$dd_usrid));
			}
		}

		$src_user = 0;
		if (isset($_GET['d_user'])) {
			if (isset($_POST['dd_user'])) {
				redirect(manage_url('admin.php?act=ip&sub=5','forum-admin.html?act=ip&sub=5'));
			} else {
				if ($dd_usrname = getUserName((int)$_GET['d_user'])) {
					$GLOBALS['cb_tpl']->assign_ref('dd_usr',$dd_usrname);
					$src_user = (int)$_GET['d_user'];
				}
			}
		}

		$nb = 20;
		$r1 = $GLOBALS['cb_db']->query('
			SELECT SQL_CALC_FOUND_ROWS msg_userip,COUNT(DISTINCT(msg_userid)) AS cnt'.(($src_user)?',GROUP_CONCAT(DISTINCT msg_userid SEPARATOR \',\') AS usrids':'').'
			FROM '.$GLOBALS['cb_db']->prefix.'messages m
			WHERE msg_userip != 0 AND msg_userid != 0
			GROUP BY msg_userip
			HAVING cnt>1'.(($src_user)?' AND FIND_IN_SET(\''.$src_user.'\',usrids) > 0':'').'
			ORDER BY msg_userip
			LIMIT '.(($pagenumber-1)*$nb).','.$nb);

		if ($nbips = $GLOBALS['cb_db']->single_result('SELECT FOUND_ROWS()')) {
			$double_ips = array();
			while ($d1 = $GLOBALS['cb_db']->fetch_assoc($r1))
				$double_ips[] = $d1['msg_userip'];

			$GLOBALS['cb_db']->free_result($r1);

			$r2 = $GLOBALS['cb_db']->query('
				SELECT DISTINCT(msg_userid),msg_userip,usr_name
				FROM '.$GLOBALS['cb_db']->prefix.'messages m
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id=msg_userid
				WHERE msg_userip IN ('.implode(',',$double_ips).') AND msg_userid != 0
				ORDER BY msg_userip');

			while ($d2 = $GLOBALS['cb_db']->fetch_assoc($r2))
				$results[] = array(
					'userip'		=> long2ip($d2['msg_userip']),
					'username'		=> $d2['usr_name'],
					'userid'		=> $d2['msg_userid']
					);

			$GLOBALS['cb_tpl']->assign('ip_pagemenu',pageMenu($nbips,$pagenumber,$nb,manage_url('admin.php?act=ip&amp;sub=5&amp;page=[num_page]'.($src_user?'&amp;d_user='.$src_user:''),'forum-admin.html?act=ip&amp;sub=5&amp;page=[num_page]'.($src_user?'&amp;d_user='.$src_user:''))));
		} else trigger_error(lang('pa_ip_detect_double_noresult'),E_USER_WARNING);

		$GLOBALS['cb_tpl']->assign_ref('analyze_results',$results);
	} else trigger_error(lang('pa_ip_sqlversion'),E_USER_WARNING);

	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_ip','pa_ip_detect_double'));
	$GLOBALS['cb_tpl']->assign('ip_part','detect_double');
}

$GLOBALS['cb_tpl']->assign('g_part','admin_ip.php');
?>