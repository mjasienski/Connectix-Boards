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

if (!($_SESSION['cb_user']->isModerator()))
	trigger_error(lang('error_permerror'),E_USER_ERROR);

require_once(CB_PATH.'include/lib/lib.moderators.php');
$GLOBALS['cb_tpl']->lang_load('moderators.lang');

$GLOBALS['cb_addressbar'][] = lang('modpanel');
$GLOBALS['cb_pagename'][] = lang('modpanel');

$pagenumber=(isset($_GET['page']) && $_GET['page']<5 && $_GET['page']>0)?(int)$_GET['page']:1;

if (isset($_POST['punish'])) {
	if (isset($_POST['selectuser_type'])) {
		$user_i=null;
		if ($_POST['selectuser_type']=='id' && isset($_POST['selectuser_id']) && isUser((int)$_POST['selectuser_id']))
			$user_i=$_POST['selectuser_id'];
		elseif ($_POST['selectuser_type']=='name' && isset($_POST['selectuser_name']) && $user_i=getUserId($_POST['selectuser_name'])) {
			// Plus rien à faire...
		} else trigger_error(lang('mod_punish_nouser'),E_USER_WARNING);
		
		if ($user_i) redirect(manage_url('index.php?act=mods&page=2','forum-moderators.html?page=2').'&punish='.$user_i);
	} elseif (isset($_POST['punishtype'],$_POST['punishtime'],$_GET['punish'])) {
		if (isUser((int)$_GET['punish'])) {
			if (is_numeric($_POST['punishtime'])) {
				$punish='';
				if ($_POST['punishtype']=='ban') $punish.='ban';
				else $punish.='readonly';
				$punish.='|'.time();
				$punish.='|'.((int)$_POST['punishtime']*86400); // Temps en secondes
				
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users LEFT JOIN '.$GLOBALS['cb_db']->prefix.'groups ON gr_id=usr_class SET usr_punished=\''.$punish.'\' WHERE usr_id='.(int)$_GET['punish'].' AND gr_status=0');
				if ($GLOBALS['cb_db']->affected_rows()==1) {
					require_once(CB_PATH.'include/lib/lib.log.php');
					addLog( (($_POST['punishtype'] == 'ban') ? LOG_BAN:LOG_READONLY),(int)$_GET['punish'],'','',(int)$_POST['punishtime'] );
					redirect(manage_url('index.php?act=mods&page=2','forum-moderators.html?page=2').'&punish='.(int)$_GET['punish']);
				} else trigger_error(lang('mod_punish_cannot'),E_USER_WARNING);
			} else trigger_error(lang('mod_punish_badtime'),E_USER_WARNING);
		}
	}
} elseif (isset($_POST['note'],$_POST['sendnote'])) {
	if (isUser((int)$_GET['punish'])) {
		if (utf8_strlen(trim($_POST['note']))>0) {
			$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'modnotes (mn_modid,mn_userid,mn_date,mn_note) VALUES ('.$_SESSION['cb_user']->userid.','.(int)$_GET['punish'].','.time().',\''.clean($_POST['note'],STR_MULTILINE).'\')');
			require_once(CB_PATH.'include/lib/lib.log.php');
			addLog( LOG_ADDNOTE,(int)$_GET['punish'],'','' );
		}
		redirect(manage_url('index.php?act=mods&page=2','forum-moderators.html?page=2').'&punish='.(int)$_GET['punish']);
	}
} elseif (isset($_POST['changereputation'],$_POST['newreputation'])) {
	if (isUser((int)$_GET['punish'])) {
		if (is_numeric($_POST['newreputation']) && (int)$_POST['newreputation']>=0 && (int)$_POST['newreputation']<=5) {
			if ((int)$_POST['newreputation'] != $GLOBALS['cb_db']->single_result('SELECT usr_reputation FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_id='.(int)$_GET['punish'])) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_reputation='.(int)$_POST['newreputation'].' WHERE usr_id='.(int)$_GET['punish']);
				require_once(CB_PATH.'include/lib/lib.log.php');
				addLog( LOG_REPUTATION,(int)$_GET['punish'],'','',(int)$_POST['newreputation'] );
			}
		}
		redirect(manage_url('index.php?act=mods&page=2','forum-moderators.html?page=2').'&punish='.(int)$_GET['punish']);
	}
} elseif (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_punished=\'\' WHERE usr_id='.(int)$_GET['cancel']);
	require_once(CB_PATH.'include/lib/lib.log.php');
	addLog( LOG_CANCELPUNISH,(int)$_GET['cancel'],'','' );
	redirect(manage_url('index.php?act=mods&page='.$pagenumber,'forum-moderators.html?page='.$pagenumber).(($pagenumber==2)?'&punish='.(int)$_GET['cancel']:''));
} elseif (isset($_GET['delete']) && is_numeric($_GET['delete']) && $pagenumber==1) {
	$msgid = $GLOBALS['cb_db']->single_result('SELECT rep_msgid FROM '.$GLOBALS['cb_db']->prefix.'reports WHERE rep_id='.(int)$_GET['delete']);
	$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'reports WHERE rep_id='.(int)$_GET['delete']);
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value-1 WHERE st_field=\'nb_reports\'');
	require_once(CB_PATH.'include/lib/lib.log.php');
	addLog( LOG_MANAGEREPORT,'','',$msgid );
	redirect(manage_url('index.php?act=mods&page=1','forum-moderators.html?page=1'));
}

$_SESSION['cb_user']->connected('index_modpanel');

$GLOBALS['cb_tpl']->assign( array(
	'm_formaction'	=>  'http://'.htmlspecialchars($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']),
	'm_menu' =>  array(
		'title' => 'mod_command',
		'currentpage' => $pagenumber,
		'url' => manage_url('index.php?act=mods&amp;page=[num_page]','forum-moderators.html?page=[num_page]'),
		'items' => array(
			array('id' => 1, 'cid' => 1 , 'title' => 'mod_badmessages'),
			array('id' => 2, 'cid' => 2 , 'title' => 'mod_punish'),
			array('id' => 3, 'cid' => 3 , 'title' => 'mod_showpunished'),
			array('id' => 4, 'cid' => 4 , 'title' => 'mod_showautomessages')
		)
	)));

$contents='';

if ($pagenumber==1) {
	$ret=$GLOBALS['cb_db']->query('SELECT msg_topicid,usr_name,usr_id,rep_id,rep_msgid,rep_desc,rep_timestamp
		FROM '.$GLOBALS['cb_db']->prefix.'reports
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'messages ON msg_id=rep_msgid
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id=rep_userid
		ORDER BY rep_timestamp DESC');

	$reports = array();
	while ($data=$GLOBALS['cb_db']->fetch_assoc($ret)) {
		$reports[]= array (
			'usr_id' => $data['usr_id'],
			'usr_name' => $data['usr_name'],
			'rep_time' => dateFormat($data['rep_timestamp']),
			'rep_desc' => $data['rep_desc'],
			'rep_topic' => $data['msg_topicid'],
			'rep_message' => $data['rep_msgid'],
			'rep_id' =>$data['rep_id']
			);
	}

	$GLOBALS['cb_tpl']->assign('m_showreports',$reports);

	$GLOBALS['cb_tpl']->assign('m_contents','showreports');
} elseif ($pagenumber==2) {
	$GLOBALS['cb_tpl']->assign('m_selectuser',true);
	if (isset($_GET['punish']) && is_numeric($_GET['punish'])) {
		$q = $GLOBALS['cb_db']->query('SELECT usr_id,usr_name,usr_punished,usr_reputation FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_id='.(int)$_GET['punish']);
		if ($usr = $GLOBALS['cb_db']->fetch_assoc($q)) {
			$GLOBALS['cb_tpl']->assign('m_selectuser',false);
			$GLOBALS['cb_tpl']->assign('m_moduser_id',$usr['usr_id']);
			$GLOBALS['cb_tpl']->assign('m_moduser_reputation',$usr['usr_reputation']);
			$GLOBALS['cb_tpl']->assign('m_moduser_link','<a href="'.manage_url('index.php?act=user&amp;showprofile='.$usr['usr_id'],'forum-m'.$usr['usr_id'].','.rewrite_words($usr['usr_name']).'.html').'">'.$usr['usr_name'].'</a>');
			
			$pun=explode('|',$usr['usr_punished']);
			if (isset($pun[1],$pun[2]) && $pun[1]+$pun[2]>time()) {
				$GLOBALS['cb_tpl']->assign('m_moduser_punished',true);
				$GLOBALS['cb_tpl']->assign('m_moduser_pun_type', (($pun[0]=='ban')?'ban':'readonly'));
				$GLOBALS['cb_tpl']->assign('m_moduser_pun_time', getTimeFormat($pun[1]+$pun[2]-time()));
			} else $GLOBALS['cb_tpl']->assign('m_moduser_punished',false);
			
			$ret = $GLOBALS['cb_db']->query('
				SELECT mn_modid,usr_name,mn_date,mn_note 
				FROM '.$GLOBALS['cb_db']->prefix.'modnotes 
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id = mn_modid
				WHERE mn_userid='.(int)$_GET['punish'].' 
				ORDER BY mn_id');
			
			$notes = array();
			$dates = array();
			while ($data=$GLOBALS['cb_db']->fetch_assoc($ret)) {
				$notes[] = array('user' => '<a href="'.manage_url('index.php?act=user&amp;showprofile='.$data['mn_modid'],'forum-m'.$data['mn_modid'].','.rewrite_words($data['usr_name']).'.html').'">'.$data['usr_name'].'</a>' , 'date' => dateFormat($data['mn_date']) , 'note' => $data['mn_note']);
				$dates[] = $data['mn_date'];
			}
			
			require_once(CB_PATH.'include/lib/lib.log.php');
			$ret = $GLOBALS['cb_db']->query('
				SELECT log_type,log_usermake,log_timestamp, log_param, usr_name
				FROM '.$GLOBALS['cb_db']->prefix.'log 
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id = log_usermake
				WHERE log_rep_user='.(int)$_GET['punish'].' AND log_type != '.LOG_ADDNOTE.'
				ORDER BY log_id');
			
			while ($data=$GLOBALS['cb_db']->fetch_assoc($ret)) {
				$param = '';
				if ($data['log_type'] == LOG_REPUTATION) 
					$param = ' ('.lang('mod_reputation').' : '.lang('reput_'.(int)$data['log_param']).')';
				elseif ($data['log_param'] > 0)
					$param = ' ('.lang('mod_period').' : '.$data['log_param'].' '.lang('days').')';
				
				$notes[] = array('user' => '<a href="'.manage_url('index.php?act=user&amp;showprofile='.$data['log_usermake'],'forum-m'.$data['log_usermake'].','.rewrite_words($data['usr_name']).'.html').'">'.$data['usr_name'].'</a>' , 'date' => dateFormat($data['log_timestamp']) , 'mod' => lang(getLogDesc($data['log_type'])).$param);
				$dates[] = $data['log_timestamp'];
			}
			
			array_multisort($dates, SORT_ASC, $notes);
			
			$GLOBALS['cb_tpl']->assign('m_moduser_notes',$notes);
		} else redirect(manage_url('index.php?act=mods&page=2','forum-moderators.html?page=2'));
	}
	$GLOBALS['cb_tpl']->assign('m_contents','punishuser');
} elseif ($pagenumber==3) {
	$ret=$GLOBALS['cb_db']->query('SELECT usr_id,usr_name,usr_punished FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_punished!=\'\'');
	$punishedusers = array();
	while ($data=$GLOBALS['cb_db']->fetch_assoc($ret)) {
		$pun=explode('|',$data['usr_punished']);
		if ($pun[1]+$pun[2]>time()) {
			$punishedusers[]=array(
				'usr_id' => $data['usr_id'],
				'usr_name' => $data['usr_name'],
				'pun_type' => (($pun[0]=='ban')?'ban':'readonly'),
				'pun_time' => getTimeFormat($pun[1]+$pun[2]-time())
				);
		}
	}

	$GLOBALS['cb_tpl']->assign('m_punishedusers',$punishedusers);

	$GLOBALS['cb_tpl']->assign('m_contents','showpunished');
} elseif ($pagenumber==4) {
	$ret=$GLOBALS['cb_db']->query('SELECT am_name,am_message FROM '.$GLOBALS['cb_db']->prefix.'automessages ORDER BY am_id ASC');
	
	$automessages=array();
	while ($data=$GLOBALS['cb_db']->fetch_assoc($ret))
		$automessages[] = array('m_showam_title' => $data['am_name'] , 'm_showam' => $data['am_message']);

	$GLOBALS['cb_tpl']->assign('m_automessages',$automessages);

	$GLOBALS['cb_tpl']->assign('m_contents','showautomessages');
}

$GLOBALS['cb_tpl']->assign('g_part','part_modpanel.php');
?>