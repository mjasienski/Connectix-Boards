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

// Variables de dump
$dump_file = 'CB_'.$GLOBALS['cb_cfg']->config['forumversion'].'_backup.sql.gz';
$_SESSION['cb_dump_file'] = isset($_SESSION['cb_dump_file'])?$_SESSION['cb_dump_file']:CB_PATH.'data/temp/'.genValidCode().'.gz';
$_SESSION['cb_reset_file'] = isset($_SESSION['cb_reset_file'])?$_SESSION['cb_reset_file']:CB_PATH.'data/temp/'.genValidCode().'.gz';
$dump_firstline = '-- Connectix Boards '.CB_VERSION.' dump file --';
$dump_tables = array(
	'config' => 'cf_field',
	'groups' => 'gr_id',
	'users' => 'usr_id',
	'banned' => 'ban_ip',
	'stats' => 'st_field',
	'automessages' => 'am_id',
	'forums' => 'forum_id',
	'log' => 'log_id',
	'messages' => 'msg_id',
	'modnotes' => 'mn_id',
	'mp' => 'mp_id',
	'pollpossibilities' => 'poss_id',
	'polls' => 'poll_id',
	'reports' => 'rep_id',
	'smileys' => 'sm_id',
	'src_matches' => 'sm_wordid,sm_msgid',
	'src_words' => 'sw_id',
	'topicgroups' => 'tg_id',
	'topics' => 'topic_id',
	'usertgs' => 'utg_userid,utg_tgid',
	'usertopics' => 'ut_userid,ut_topicid'
	);

$sub = (isset($_GET['sub']) && $_GET['sub']<4 && $_GET['sub']>0)?$_GET['sub']:1;

// Gestion des opérations demandées
if (isset($_POST['deleteoldtopics'])) {
	if (isset($_POST['deleteold_days'],$_POST['deleteold_location']) && is_numeric($_POST['deleteold_days'])) {
		$conditions=' AND (';
		$or = false;
		if (isset($_POST['deleteold_normal']) && $_POST['deleteold_normal']=='on') { $conditions.=(($or)?' OR ':'').'topic_type=0'; $or=true; }
		if (isset($_POST['deleteold_pinned']) && $_POST['deleteold_pinned']=='on') { $conditions.=(($or)?' OR ':'').'topic_type=1'; $or=true; }
		if (isset($_POST['deleteold_announce']) && $_POST['deleteold_announce']=='on') { $conditions.=(($or)?' OR ':'').'topic_type=2'; $or=true; }
		if (isset($_POST['deleteold_replied']) && $_POST['deleteold_replied']=='on') { $conditions.=(($or)?' OR ':'').'topic_nbreply=0'; $or=true; }
		if ($or) $conditions.=')';
		else $conditions = '';
		if ($_POST['deleteold_location']!='default') {
			$inf = (utf8_strpos($_POST['deleteold_location'],'f_') !== false)?(int)utf8_substr($_POST['deleteold_location'],2):0;
			$intg = (utf8_strpos($_POST['deleteold_location'],'tg_') !== false)?(int)utf8_substr($_POST['deleteold_location'],3):0;

			if ($inf*$intg != 0) $intg = 0;

			$tg = array();
			require_once(CB_PATH.'include/lib/lib.structure.php');
			if ($inf != 0 && isForum($inf)) {
				$tg = getSubTopicGroupsOfF($inf);
			} elseif ($intg != 0 && isTg($intg)) {
				$tg = getSubTopicGroupsOfTg($intg);
			}

			$or2 = false;
			foreach ($tg as $tgid) {
				if (!$or2) $conditions.=' AND (';
				$conditions.=(($or2)?' OR ':'').'topic_fromtopicgroup='.$tgid;
				$or = true; $or2 = true;
			}
			if ($or2) $conditions.=')';
		}

		$r = $GLOBALS['cb_db']->query('SELECT topic_id FROM '.$GLOBALS['cb_db']->prefix.'topics
			LEFT JOIN '.$GLOBALS['cb_db']->prefix.'messages ON msg_id=topic_lastmessage
			WHERE msg_timestamp<'.(time()-((abs((int)$_POST['deleteold_days']))*24*3600)).' '.(($or)?$conditions:''));

		$topicsdeleted=0;
		while ($d = $GLOBALS['cb_db']->fetch_assoc($r)) {
			require_once(CB_PATH.'include/lib/lib.moderators.php');
			deleteTopic($d['topic_id']);
			$topicsdeleted++;
		}
		$_POST = array();
		if ($topicsdeleted>0) trigger_error(str_replace('{nb}',$topicsdeleted,lang('pa_db_deleteold_success')),E_USER_NOTICE);
		else trigger_error(lang('pa_db_deleteold_notopics'),E_USER_WARNING);
	} else trigger_error(lang('pa_db_deleteold_error_days'),E_USER_WARNING);
} elseif (isset($_POST['deleteoldusers'])) {
	$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_registered!=\'TRUE\' AND utf8_substr(usr_registered,0,6)!=\'change\' AND usr_registertime<'.(time()-(31*24*3600)));
	$accountsdeleted = $GLOBALS['cb_db']->affected_rows();
	if ($accountsdeleted>0) trigger_error(lang(array('item' => 'pa_db_deleteoldusers_success','nb' => $accountsdeleted)),E_USER_NOTICE);
	else trigger_error(lang('pa_db_deleteoldusers_noaccounts'),E_USER_WARNING);
} elseif (isset($_POST['deleteoldlogentries'])) {
	$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'log WHERE log_timestamp<'.(time()-(31*6*24*3600)));
	$logentriesdeleted = $GLOBALS['cb_db']->affected_rows();
	if ($logentriesdeleted>0) trigger_error(lang(array('item' => 'pa_db_deleteoldlogentries_success','nb' => $logentriesdeleted)),E_USER_NOTICE);
	else trigger_error(lang('pa_db_deleteoldlogentries_noentries'),E_USER_WARNING);
} elseif (isset($_POST['deleteoldtopictrackers'])) {
	$ids = $GLOBALS['cb_db']->assoc_results('SELECT tracker.topic_id,tracker.topic_id FROM '.$GLOBALS['cb_db']->prefix.'topics AS tracker
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topics AS realtopic ON realtopic.topic_id = tracker.topic_displaced
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'messages ON msg_id = realtopic.topic_lastmessage 
				WHERE tracker.topic_displaced>0 AND msg_timestamp<'.(time()-(31*2*24*3600)));
	$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_id IN ('.implode($ids,',').')');
	$topictrackersdeleted = count($ids);
	if ($topictrackersdeleted>0) trigger_error(lang(array('item' => 'pa_db_deleteoldtopictrackers_success','nb' => $topictrackersdeleted)),E_USER_NOTICE);
	else trigger_error(lang('pa_db_deleteoldtopictrackers_notrackers'),E_USER_WARNING);
} elseif (isset($_POST['reset_db']) || isset($_POST['reset_db_ftp'],$_POST['dumped_db_ftp'])) {
	if (!function_exists('gzopen'))
		trigger_error(lang('error_nozlib'),E_USER_ERROR);
	
	$begin_reset = false;
	if (isset($_POST['reset_db'])) {
		if (!empty($_FILES['dumped_db']['name'])) {
			if ($_FILES['dumped_db']['error'] == 0) {
				if (@move_uploaded_file($_FILES['dumped_db']['tmp_name'],$_SESSION['cb_reset_file'])) {
					$begin_reset = true;
				} else trigger_error(lang('pa_db_error_upload'),E_USER_WARNING);
			} else {
				if ($_FILES['dumped_db']['error'] <= 2)
					trigger_error(lang(array('item' => 'pa_db_error_toobig','size' => ini_get('upload_max_filesize').'o')),E_USER_WARNING);
				else
					trigger_error(lang('pa_db_error_upload'),E_USER_WARNING);
			}
		} else trigger_error(lang('pa_db_error_upload'),E_USER_WARNING);
	} elseif (isset($_POST['reset_db_ftp'],$_POST['dumped_db_ftp'])) {
		$ufile = CB_PATH.'data/temp/'.basename($_POST['dumped_db_ftp']);
		if (file_exists($ufile)) {
			if (copy($ufile,$_SESSION['cb_reset_file'])) {
				@unlink($ufile);
				$begin_reset = true;
			} else trigger_error(lang('pa_db_error_upload'),E_USER_WARNING);
		} else trigger_error(lang('pa_db_error_nofile'),E_USER_WARNING);
	}
	
	if ($begin_reset) {
		$fh = gzopen($_SESSION['cb_reset_file'],'r');
		if ($fh !== false) {
			$fline = gzgets($fh);
			if (trim($fline) == $dump_firstline) {
				foreach ($dump_tables as $table => $order)
					$GLOBALS['cb_db']->query('TRUNCATE TABLE '.$GLOBALS['cb_db']->prefix.$table);
				
				require_once(CB_PATH.'include/lib/lib.db.php');
				$_SESSION['offset'] = execute_sqlpart($fh);
				
				redirect(manage_url('admin.php','forum-admin.html').'?act=db&sub=3&reset=1',lang('db_reset_wait'),1,true);
			} else trigger_error(lang('pa_db_error_file'),E_USER_WARNING);
		} else trigger_error(lang('pa_db_error_file'),E_USER_WARNING);
	}
} elseif (isset($_GET['reset'],$_SESSION['offset']) && $_GET['reset']==1 && $_SESSION['offset'] != 0) {
	if (!function_exists('gzopen'))
		trigger_error(lang('error_nozlib'),E_USER_ERROR);
	
	require_once(CB_PATH.'include/lib/lib.db.php');
	
	$timelimit = 5;
	$time_begin = time();
	
	$fh = gzopen($_SESSION['cb_reset_file'],'r');
	if ($fh !== false) {
		fseek($fh,$_SESSION['offset']);
		while (!gzeof($fh)) {
			$_SESSION['offset'] = execute_sqlpart($fh,$_SESSION['offset']);
			
			if ((time() - $time_begin) > $timelimit)
				redirect(manage_url('admin.php','forum-admin.html').'?act=db&sub=3&reset=1',lang('db_reset_wait'),1,true);
		}
	} else trigger_error(lang('pa_db_error_file'),E_USER_WARNING);
	
	error_reporting(0);
	@chmod(CB_PATH.'data/temp/',0755);
	@unlink($_SESSION['cb_reset_file']);
	error_reporting(E_ALL);
	
	require_once(CB_PATH.'include/lib/class.smileysmanager.php');
	$smile = new smileysmanager();
	
	cacheStructure();
	cacheMods();
	cacheClasses();
	$smile->cacheSmileys();
	$GLOBALS['cb_cfg']->cacheConfig();
	
	error_reporting(0);
	if ($GLOBALS['cb_cfg']->config['url_rewrite']=='yes')
		file_put_contents(CB_PATH.'.htaccess',file_get_contents(CB_PATH.'admin/htaccess.txt'));
	error_reporting(E_ALL);
	
	trigger_error(lang('pa_db_reset_success'),E_USER_NOTICE);
} elseif (isset($_GET['dump']) && $_GET['dump']==1) {
	if (!function_exists('gzopen'))
		trigger_error(lang('error_nozlib'),E_USER_ERROR);
	
	require_once(CB_PATH.'include/lib/lib.db.php');
	
	$timelimit = 5;
	$time_begin = time();
	
	if (!isset($_SESSION['cb_dump_table']) && file_exists($_SESSION['cb_dump_file']))
		unlink($_SESSION['cb_dump_file']);
	
	if (!isset($_SESSION['cb_dump_table'])) {
		$_SESSION['cb_dump_table'] = 'config';
		redirect(manage_url('admin.php','forum-admin.html').'?act=db&sub=2&dump=1',lang('db_dump_wait'),1);
	}
	
	$_SESSION['cb_dump_begin'] = ((!isset($_SESSION['cb_dump_begin']))?0:$_SESSION['cb_dump_begin']);
	$_SESSION['cb_dump_end'] = ((!isset($_SESSION['cb_dump_end']))?false:$_SESSION['cb_dump_end']);
	
	if (!$_SESSION['cb_dump_end']) {
		if ($h=gzopen($_SESSION['cb_dump_file'],'a')) {
			if ($_SESSION['cb_dump_table'] == 'config' && $_SESSION['cb_dump_begin']==0) {
				gzwrite($h,$dump_firstline."\n");
			}
			$donext = false;
			foreach ($dump_tables as $table => $order) {
				if ($table == $_SESSION['cb_dump_table'] || $donext) {
					$_SESSION['cb_dump_table'] = $table;
					
					while ($_SESSION['cb_dump_begin'] !== true) {
						$_SESSION['cb_dump_begin'] = dump_table($h,$table,$order,$_SESSION['cb_dump_begin']);
						if ((time() - $time_begin) > $timelimit)
							redirect(manage_url('admin.php','forum-admin.html').'?act=db&sub=2&dump=1',lang('db_dump_wait'),1,true);
					}
					
					$_SESSION['cb_dump_begin'] = 0;
					$donext = true;
				}
			}
			gzclose($h);
			$_SESSION['cb_dump_end'] = true;
			
			$GLOBALS['cb_tpl']->assign('g_redirect',manage_url('admin.php','forum-admin.html').'?act=db&sub=2&dump=1');
			$GLOBALS['cb_tpl']->assign('g_redirect_delay',1);
		} else trigger_error('Could not open '.$_SESSION['cb_dump_file'].', please check its rights.',E_USER_WARNING);
	} else {
		header('Content-Type: application/x-gzip');
		header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
		if (preg_match('@MSIE ([0-9].[0-9]{1,2})@', $_SERVER['HTTP_USER_AGENT'])) {
			header('Content-Disposition: inline; filename="'.$dump_file.'"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		} else {
			header('Content-Disposition: attachment; filename="'.$dump_file.'"');
			header('Pragma: no-cache');
		}
		
		unset($_SESSION['cb_dump_end']);
		unset($_SESSION['cb_dump_table']);
		unset($_SESSION['cb_dump_begin']);
		
		readfile($_SESSION['cb_dump_file']);
		unlink($_SESSION['cb_dump_file']);
	}
}

// Affichage des différentes sections
if ($sub==1) {
	$GLOBALS['cb_tpl']->assign('db_deleteold_selectdate_input','<input type="text" name="deleteold_days" value="'.((isset($_POST['deleteold_days']))?abs((int)$_POST['deleteold_days']):365).'" size="2" />');
	$GLOBALS['cb_tpl']->assign('db_deleteold_selecttype_normal_checked',((isset($_POST['deleteold_normal']) && $_POST['deleteold_normal']=='on')?'checked="checked"':''));
	$GLOBALS['cb_tpl']->assign('db_deleteold_selecttype_pinned_checked',((isset($_POST['deleteold_pinned']) && $_POST['deleteold_pinned']=='on')?'checked="checked"':''));
	$GLOBALS['cb_tpl']->assign('db_deleteold_selecttype_announce_checked',((isset($_POST['deleteold_announce']) && $_POST['deleteold_announce']=='on')?'checked="checked"':''));
	$GLOBALS['cb_tpl']->assign('db_deleteold_selecttype_replied_checked',((isset($_POST['deleteold_replied']) && $_POST['deleteold_replied']=='on')?'checked="checked"':''));
	$GLOBALS['cb_tpl']->assign('db_deleteold_selectlocation_choose',showForumMenu('deleteold_location','pa_db_deleteold_selectlocation_def',((isset($_POST['deleteold_location']) && $_POST['deleteold_location']!='default')?(int)$_POST['deleteold_location']:null)));
	$GLOBALS['cb_tpl']->assign('db_part','deleteold');
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_database','pa_db_deleteold'));
} elseif ($sub==2) {
	if (!function_exists('gzopen'))
		trigger_error(lang('error_nozlib'),E_USER_WARNING);
	
	$GLOBALS['cb_tpl']->assign('db_part','dump');
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_database','pa_db_dump'));
} elseif ($sub==3) {
	if (!function_exists('gzopen'))
		trigger_error(lang('error_nozlib'),E_USER_WARNING);
	
	$GLOBALS['cb_tpl']->assign('db_reset_version',$GLOBALS['cb_cfg']->config['forumversion']);
	$GLOBALS['cb_tpl']->assign('db_part','reset');
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_database','pa_db_reset'));
}

$GLOBALS['cb_tpl']->assign('g_part','admin_database.php');
?>