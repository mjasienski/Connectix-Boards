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

require_once(CB_PATH.'include/lib/lib.structure.php');

if (isset($_GET['ff'],$_GET['ft'])) {
	if (false !== $bf = $GLOBALS['cb_db']->single_result('SELECT forum_order FROM '.$GLOBALS['cb_db']->prefix.'forums WHERE forum_id='.(int)$_GET['ff'])) {
		if (false !== $bt = $GLOBALS['cb_db']->single_result('SELECT forum_order FROM '.$GLOBALS['cb_db']->prefix.'forums WHERE forum_id='.(int)$_GET['ft'])) {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'forums SET forum_order='.$bf.' WHERE forum_id='.(int)$_GET['ft']);
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'forums SET forum_order='.$bt.' WHERE forum_id='.(int)$_GET['ff']);

			cacheStructure();
			redirect(manage_url('admin.php','forum-admin.html').'?act=str&sub=1');
		}
	}
} elseif (isset($_GET['tgf'],$_GET['tgt'])) {
	if (false !== $bf = $GLOBALS['cb_db']->single_result('SELECT tg_order FROM '.$GLOBALS['cb_db']->prefix.'topicgroups WHERE tg_id='.(int)$_GET['tgf'])) {
		if (false !== $bt = $GLOBALS['cb_db']->single_result('SELECT tg_order FROM '.$GLOBALS['cb_db']->prefix.'topicgroups WHERE tg_id='.(int)$_GET['tgt'])) {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_order='.$bf.' WHERE tg_id='.(int)$_GET['tgt']);
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_order='.$bt.' WHERE tg_id='.(int)$_GET['tgf']);

			cacheStructure();
			redirect(manage_url('admin.php','forum-admin.html').'?act=str&sub=1');
		}
	}
} elseif (isset($_POST['newforum'])) {
	if (isset($_POST['fname'])) {
		if (utf8_strlen($_POST['fname'])>3) {
			if (isset($_GET['editforum']) && isForum((int)$_GET['editforum'])) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'forums SET forum_name=\''.clean($_POST['fname']).'\' WHERE forum_id='.(int)$_GET['editforum']);
				trigger_error(str_replace('{name}',clean($_POST['fname'],STR_TODISPLAY),lang('forum_success_edited')),E_USER_NOTICE);
			} else {
				$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'forums(forum_name) VALUES(\''.clean($_POST['fname']).'\')');
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'forums SET forum_order=forum_id WHERE forum_id='.$GLOBALS['cb_db']->insert_id());
				trigger_error(str_replace('{name}',clean($_POST['fname'],STR_TODISPLAY),lang('forum_success_created')),E_USER_NOTICE);
			}
			cacheStructure();
		} else trigger_error(lang('error_f_name'),E_USER_WARNING);
	}
} elseif (isset($_POST['newtopicgroup'])) {
	if (isset($_POST['tgname'],$_POST['tgcomment'],$_POST['fromforum'],$_POST['tglink'])) {
		if (utf8_strlen($_POST['tgname'])>1) {
			if (!empty($_POST['fromforum']) && $_POST['fromforum']!=='default') {
				$fromf = (utf8_strpos($_POST['fromforum'],'f_') !== false)?(int)utf8_substr($_POST['fromforum'],2):0;
				$fromtg = (utf8_strpos($_POST['fromforum'],'tg_') !== false)?(int)utf8_substr($_POST['fromforum'],3):0;
				if ((isForum($fromf) || isTg($fromtg)) && $fromf*$fromtg == 0) {
					$tgid = 0;
					if (isset($_GET['edittg']) && isTg((int)$_GET['edittg'])) {
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_name=\''.clean($_POST['tgname']).'\',tg_comment=\''.clean($_POST['tgcomment'],STR_PARSEBB + STR_MULTILINE).'\',tg_visibility='.((isset($_POST['visibility']) && $_POST['visibility']=='hide')?1:0).',tg_fromforum='.$fromf.',tg_fromtopicgroup='.$fromtg.',tg_link=\''.clean($_POST['tglink']).'\' WHERE tg_id='.(int)$_GET['edittg']);
						$tgid = (int)$_GET['edittg'];
						trigger_error(str_replace('{name}',clean($_POST['tgname'],STR_TODISPLAY),lang('tg_success_edited')),E_USER_NOTICE);
					} else {
						$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'topicgroups(tg_name,tg_comment,tg_fromforum,tg_fromtopicgroup,tg_visibility,tg_lasttopic,tg_link) VALUES(\''.clean($_POST['tgname']).'\',\''.clean($_POST['tgcomment'],STR_PARSEBB + STR_MULTILINE).'\','.$fromf.','.$fromtg.','.((isset($_POST['visibility']) && $_POST['visibility']=='hide')?1:0).',0,\''.clean($_POST['tglink']).'\')');
						$tgid = $GLOBALS['cb_db']->insert_id();
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_order=tg_id WHERE tg_id='.$GLOBALS['cb_db']->insert_id());
						trigger_error(str_replace('{name}',clean($_POST['tgname'],STR_TODISPLAY),lang('tg_success_created')),E_USER_NOTICE);
					}
					
					$return = $GLOBALS['cb_db']->query('SELECT gr_id,gr_auth_reply,gr_auth_create,gr_auth_see FROM '.$GLOBALS['cb_db']->prefix.'groups WHERE gr_id != 0 AND gr_status != 2 ORDER BY gr_status DESC, gr_cond' );
					while ($groupdata = $GLOBALS['cb_db']->fetch_assoc($return)) {
						$grid = $groupdata['gr_id'];
						$auth_create = explode('/',$groupdata['gr_auth_create']);
						$auth_see = explode('/',$groupdata['gr_auth_see']);
						$auth_reply = explode('/',$groupdata['gr_auth_reply']);
						
						if (isset($_POST['see_'.$grid]) && $_POST['see_'.$grid] == 'on') {
							if (!in_array($tgid,$auth_see)) $auth_see[] = $tgid;
						} else {
							if (in_array($tgid,$auth_see)) $auth_see = array_diff($auth_see,array($tgid));
						}
						if (isset($_POST['reply_'.$grid]) && $_POST['reply_'.$grid] == 'on') {
							if (!in_array($tgid,$auth_reply)) $auth_reply[] = $tgid;
						} else {
							if (in_array($tgid,$auth_reply)) $auth_reply = array_diff($auth_reply,array($tgid));
						}
						if (isset($_POST['create_'.$grid]) && $_POST['create_'.$grid] == 'on') {
							if (!in_array($tgid,$auth_create)) $auth_create[] = $tgid;
						} else {
							if (in_array($tgid,$auth_create)) $auth_create = array_diff($auth_create,array($tgid));
						}
						
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'groups 
													SET gr_auth_see=\''.implode('/',$auth_see).'\',
														gr_auth_reply=\''.implode('/',$auth_reply).'\',
														gr_auth_create=\''.implode('/',$auth_create).'\' 
													WHERE gr_id='.$grid);
					}
					
					cacheStructure();
					cacheClasses();
					if (isset($_GET['edittg']) && isTg((int)$_GET['edittg']))
						resetTgCounts();
					
				} else trigger_error(lang('error_tg_fromforum'),E_USER_WARNING);
			} else trigger_error(lang('error_tg_fromforum'),E_USER_WARNING);
		} else trigger_error(lang('error_tg_name'),E_USER_WARNING);
	}
} elseif (isset($_POST['deleteitem'],$_POST['delete_topics']) && ((isset($_GET['deleteforum']) && isForum($_GET['deleteforum'])) || (isset ($_GET['deletetg']) && isTg($_GET['deletetg'])))) {
	if ($_POST['delete_topics'] == 'no' && isTg((int)$_POST['desttg'])) {
		$todisplace = array();
		if (isset($_GET['deleteforum'])) $todisplace = getSubTopicGroupsOfF($_GET['deleteforum']); 
		if (isset($_GET['deletetg'])) {
			$todisplace = getSubTopicGroupsOfTg($_GET['deletetg']); 
			$todisplace[] = $_GET['deletetg'];
		}
		if (count($todisplace) > 0) {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_fromtopicgroup='.(int)$_POST['desttg'].' WHERE topic_fromtopicgroup IN ('.implode(',',$todisplace).')');
			resetTgCounts();
		}
	}
	if (isset($_GET['deleteforum']) && isForum($_GET['deleteforum'])) {
		require_once(CB_PATH.'include/lib/lib.moderators.php');
		deleteForum($_GET['deleteforum']);
		setAllUsersPostClass();
		cacheStructure();
		trigger_error(lang('forum_success_deleted'),E_USER_NOTICE);
	} elseif (isset ($_GET['deletetg']) && isTg($_GET['deletetg'])) {
		require_once(CB_PATH.'include/lib/lib.moderators.php');
		deleteTopicGroup($_GET['deletetg']);
		setAllUsersPostClass();
		cacheStructure();
		trigger_error(lang('tg_success_deleted'),E_USER_NOTICE);
	}
	redirect(manage_url('admin.php','forum-admin.html').'?act=str');
}

$sub=(isset($_GET['sub']) && (int)$_GET['sub']>0 && (int)$_GET['sub']<5)?(int)$_GET['sub']:1;

function upanddown (&$tg_ud,$tgs) {
	$preid = null;
	foreach ($tgs as $tgid => $tg) {
		if ($preid != null) {
			$tg_ud[$preid]['down'] = 'tgf='.$preid.'&amp;tgt='.$tgid;
			$tg_ud[$tgid]['up'] = 'tgf='.$tgid.'&amp;tgt='.$preid;
		}
		if (isset($GLOBALS['cb_str_ptg'][$tgid]))
			upanddown($tg_ud,$GLOBALS['cb_str_ptg'][$tgid]);
		$preid = $tgid;
	}
}

if ($sub==1) {
	$f_ud = array();
	$tg_ud = array();
	
	$preid = null;
	foreach ($GLOBALS['cb_str_fnames'] as $fid => $foo) {
		if ($preid != null) {
			$f_ud[$preid]['down'] = 'ff='.$preid.'&amp;ft='.$fid;
			$f_ud[$fid]['up'] = 'ff='.$fid.'&amp;ft='.$preid;
		}
		if (isset($GLOBALS['cb_str_pf'][$fid]))
			upanddown($tg_ud,$GLOBALS['cb_str_pf'][$fid]);
		$preid = $fid;
	}
	
	$GLOBALS['cb_tpl']->assign('pa_structure_f',$GLOBALS['cb_str_pf']);
	$GLOBALS['cb_tpl']->assign('pa_structure_ff',$GLOBALS['cb_str_ff']);
	$GLOBALS['cb_tpl']->assign('pa_structure_tg',$GLOBALS['cb_str_ptg']);
	$GLOBALS['cb_tpl']->assign('pa_fnames',$GLOBALS['cb_str_fnames']);
	$GLOBALS['cb_tpl']->assign('pa_tgnames',$GLOBALS['cb_str_tgnames']);
	$GLOBALS['cb_tpl']->assign('pa_f_updown',$f_ud);
	$GLOBALS['cb_tpl']->assign('pa_tg_updown',$tg_ud);
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_structure','pa_structure_overview'));
	$GLOBALS['cb_tpl']->assign('str_part','structure');
} elseif ($sub==2) {
	if (isset($_GET['editforum']) && isForum((int)$_GET['editforum'])) {
		$GLOBALS['cb_tpl']->assign('pa_str_fname',$GLOBALS['cb_str_fnames'][(int)$_GET['editforum']]);
		$GLOBALS['cb_tpl']->assign('pa_addforum_submit','pa_editforum');
		$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_structure','pa_structure_editforum'));
	} else {
		$GLOBALS['cb_tpl']->assign('pa_str_fname','');
		$GLOBALS['cb_tpl']->assign('pa_addforum_submit','pa_createforum');
		$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_structure','pa_structure_addforum'));
	}
	$GLOBALS['cb_tpl']->assign('str_part','addforum');
} elseif ($sub==3) {
	$tg_editing = 0;
	if (isset($_GET['edittg']) && isTg((int)$_GET['edittg'])) {
		$tg_editing = (int)$_GET['edittg'];
		$ret=$GLOBALS['cb_db']->query('SELECT tg_id,tg_name,tg_comment,tg_fromforum,tg_fromtopicgroup,tg_visibility,tg_link FROM '.$GLOBALS['cb_db']->prefix.'topicgroups WHERE tg_id='.$tg_editing);
		$tg=$GLOBALS['cb_db']->fetch_assoc($ret);
		$GLOBALS['cb_tpl']->assign('pa_addtg_name_in',$tg['tg_name']);
		$GLOBALS['cb_tpl']->assign('pa_addtg_comment_in',unclean($tg['tg_comment']));
		$GLOBALS['cb_tpl']->assign('pa_addtg_forummenu',showForumMenu('fromforum','pa_structure_choosefortg',$tg['tg_fromforum'],$tg['tg_fromtopicgroup'],0,$tg['tg_id']));
		$GLOBALS['cb_tpl']->assign('pa_addtg_submit','pa_edittopicgroup');
		$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_structure','pa_structure_edittopicgroup'));
		$GLOBALS['cb_tpl']->assign('pa_addtg_vis_checkvis',(($tg['tg_visibility']==0)?'checked="checked"':''));
		$GLOBALS['cb_tpl']->assign('pa_addtg_vis_checkhid',(($tg['tg_visibility']==1)?'checked="checked"':''));
		$GLOBALS['cb_tpl']->assign('pa_addtg_link',$tg['tg_link']);
	} else {
		$GLOBALS['cb_tpl']->assign('pa_addtg_name_in','');
		$GLOBALS['cb_tpl']->assign('pa_addtg_comment_in','');
		$GLOBALS['cb_tpl']->assign('pa_addtg_forummenu',showForumMenu('fromforum','pa_structure_choosefortg'));
		$GLOBALS['cb_tpl']->assign('pa_addtg_submit','pa_createtopicgroup');
		$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_structure','pa_structure_addtopicgroup'));
		$GLOBALS['cb_tpl']->assign('pa_addtg_vis_checkvis','checked="checked"');
		$GLOBALS['cb_tpl']->assign('pa_addtg_vis_checkhid','');
		$GLOBALS['cb_tpl']->assign('pa_addtg_link','');
	}
	
	$return = $GLOBALS['cb_db']->query('SELECT gr_id,gr_name,gr_auth_reply,gr_auth_create,gr_auth_see,gr_cond FROM '.$GLOBALS['cb_db']->prefix.'groups WHERE gr_id != 0 AND gr_status != 2 ORDER BY gr_status DESC, gr_cond' );
	$groups = array();
	$guestsgroup = array();
	while ($groupdata = $GLOBALS['cb_db']->fetch_assoc($return)) {
		$group = array();
		$group['id'] = $groupdata['gr_id'];
		$group['name'] = $groupdata['gr_name'];
		$group['create'] = $tg_editing > 0 ? in_array($tg_editing, explode('/',$groupdata['gr_auth_create'])) : $groupdata['gr_cond'] == -2;
		$group['see'] = $tg_editing > 0 ? in_array($tg_editing, explode('/',$groupdata['gr_auth_see'])) : 0;
		$group['reply'] = $tg_editing > 0 ? in_array($tg_editing, explode('/',$groupdata['gr_auth_reply'])) : $groupdata['gr_cond'] == -2;
		
		if ($groupdata['gr_cond'] == -2)
			$guestsgroup = $group;
		else
			$groups[] = $group;
	}
	$guestsgroup['name'] = lang('pa_addtopicgroup_userrights_guests');
	$groups[] = $guestsgroup;
	
	$GLOBALS['cb_tpl']->assign('pa_addtg_userrights_tgid',$tg_editing);
	$GLOBALS['cb_tpl']->assign('pa_addtg_userrights_groups',$groups);
	
	$GLOBALS['cb_tpl']->assign('str_part','addtg');
} elseif ($sub==4) {
	if (isset($_GET['deleteforum']) && isForum((int)$_GET['deleteforum'])) {
		$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_structure','pa_structure_confirmforum'));
		$GLOBALS['cb_tpl']->assign('pa_delete_message',lang(array('item' => 'pa_structure_confirmforum_txt','name' => $GLOBALS['cb_str_fnames'][(int)$_GET['deleteforum']])));
		$GLOBALS['cb_tpl']->assign('pa_delete_tgmenu',showForumMenu('desttg','pa_structure_desttg',0,0,(int)$_GET['deleteforum'],0,true,''));
	} elseif (isset($_GET['deletetg']) && isTg((int)$_GET['deletetg'])) {
		$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_structure','pa_structure_confirmtg'));
		$GLOBALS['cb_tpl']->assign('pa_delete_message',lang(array('item' => 'pa_structure_confirmtg_txt','name' => $GLOBALS['cb_str_tgnames'][(int)$_GET['deletetg']])));
		$GLOBALS['cb_tpl']->assign('pa_delete_tgmenu',showForumMenu('desttg','pa_structure_desttg',0,0,0,(int)$_GET['deletetg'],true,''));
	} else redirect(manage_url('admin.php','forum-admin.html').'?act=str');
	$GLOBALS['cb_tpl']->assign('str_part','confirm');
}
$GLOBALS['cb_tpl']->assign('g_part','admin_structure.php');
?>
