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

if ($sub==1) {
	if (isset($_POST['createaccount'])) {
		/* Création d'un membre*/
		if (isset($_POST['username'])) {
			if (isset($_POST['password1'],$_POST['password2'])) {
				if ($_POST['password1']==$_POST['password2']) {
					if (isset($_POST['email']) && !empty($_POST['email'])) {
						if (isset($_POST['nameclass']) && (is_numeric($_POST['nameclass']) || $_POST['nameclass'] == 'default')) {
							require_once(CB_PATH.'include/lib/lib.users.php');
							if (registerUser($_POST['username'],$_POST['password1'],$_POST['email'],($_POST['nameclass']=='default'?0:$_POST['nameclass']),false,false)) {
								trigger_error(str_replace('{name}',clean($_POST['username'],STR_TODISPLAY),lang('user_success_created')),E_USER_NOTICE);
							}
						} else trigger_error(lang('error_class_noexist'),E_USER_WARNING);
					} else trigger_error(lang('error_emptymail'),E_USER_WARNING);
				} else trigger_error(lang('error_password'),E_USER_WARNING);
			}
		} else trigger_error(lang('error_username'),E_USER_WARNING);
	} elseif (isset($_POST['deleteaccount'])) {
		/* Suppression d'un membre*/
		if (isset($_POST['delete_user']) && utf8_strlen(trim($_POST['delete_user']))>1) {
			$uid = $GLOBALS['cb_db']->single_result('SELECT usr_id FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_name=\''.clean($_POST['delete_user']).'\' AND usr_id != 1');
			if ($uid !== false) {
				require_once(CB_PATH.'include/lib/lib.users.php');
				deleteUser($uid);
			} else trigger_error(lang('error_user_noexist'),E_USER_WARNING);
		} else trigger_error(lang('error_username'),E_USER_WARNING);
	}
} elseif ($sub==2) {
	/* Suppression d'un groupe de membres */
	if (isset($_GET['delgroup']) && is_numeric($_GET['delgroup']) && (int)$_GET['delgroup'] != 3) {
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'groups WHERE gr_id='.(int)$_GET['delgroup'].' AND gr_id!=1');
		setAllUsersPostClass();
		cacheClasses();
		redirect(manage_url('admin.php','forum-admin.html').'?act=users&sub=2');
	} elseif (isset($_GET['delgroup'])) {
		redirect(manage_url('admin.php','forum-admin.html').'?act=users&sub=2');
	/* Cacher un groupe d'utilisateurs */
	} elseif (isset($_POST['hide_confirm'])) {
		$new_hide = array_filter($_POST['hide'],'is_numeric');
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'groups SET gr_hide = 0');
		if (count($new_hide) > 0)
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'groups SET gr_hide = 1 WHERE gr_id IN ('.implode(',',$new_hide).')');
		cacheClasses();
		redirect(manage_url('admin.php','forum-admin.html').'?act=users&sub=2');
	/* Supprimer un rang */
	} elseif (isset($_GET['delrank']) && is_numeric($_GET['delrank'])) {
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'ranks WHERE rk_id='.(int)$_GET['delrank'].' AND rk_id!=1');
		cacheClasses();
		redirect(manage_url('admin.php','forum-admin.html').'?act=users&sub=2');
	}
} elseif ($sub==3) {
	/* Création d'un groupe de membres */
	if (isset($_POST['createclass'])) {
		if (isset($_POST['classtitle']) && utf8_strlen(trim($_POST['classtitle']))>2) {
			$condition='';
			if ($_POST['condition']=='posts') $condition=abs((int)$_POST['postscond']);
			else $condition=-1;
			if ((int)$_GET['edit']==3) $condition=0;

			$canflood=0;
			if (isset($_POST['canflood']) && $_POST['canflood']=='on') $canflood=1;

			$auth_reply = array();
			$auth_create = array();
			$auth_see = array();
			foreach ($_POST as $key => $value) {
				if (preg_match('#^see_(.+?)$#',$key)) {
					if ($value=='on')
						$auth_see[] = preg_replace('#^see_(.+?)$#','$1',$key);
				} elseif (preg_match('#^reply_(.+?)$#',$key)) {
					if ($value=='on')
						$auth_reply[] = preg_replace('#^reply_(.+?)$#','$1',$key);
				} elseif (preg_match('#^create_(.+?)$#',$key)) {
					if ($value=='on')
						$auth_create[] = preg_replace('#^create_(.+?)$#','$1',$key);
				}
			}
			$auth_reply = array_unique(array_merge($auth_reply,$auth_see));
			$auth_create = array_unique(array_merge($auth_reply,$auth_create));
			$auth_see = implode('/',$auth_see);
			$auth_reply = implode('/',$auth_reply);
			$auth_create = implode('/',$auth_create);

			if (isset($_GET['edit']) && isClass((int)$_GET['edit'])){
				$st=0;
				if ($_POST['type']=='admin' || (int)$_GET['edit']==1) $st=2;
				elseif ($_POST['type']=='mod') $st=1;

				$t='';
				if ((int)$_POST['numbermps']>=0) $t=',gr_mps='.(int)$_POST['numbermps'];

				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'groups SET gr_name=\''.clean($_POST['classtitle']).'\',gr_color=\''.clean($_POST['classcolor']).'\',gr_cond='.$condition.',gr_auth_create=\''.$auth_create.'\',gr_auth_reply=\''.$auth_reply.'\',gr_auth_see=\''.$auth_see.'\',gr_auth_flood='.$canflood.',gr_status='.$st.''.$t.' WHERE gr_id='.(int)$_GET['edit']);

				cacheMods();
				setAllUsersPostClass();
				cacheClasses();

				redirect(manage_url('admin.php','forum-admin.html').'?act=users&sub=3&edit='.(int)$_GET['edit']);
			} else {
				$st=0;$t='';
				if ($_POST['type']=='admin') $st=2;
				elseif ($_POST['type']=='mod') $st=1;
				if ((int)$_POST['numbermps']>=0) $t.='gr_mps='.(int)$_POST['numbermps'];
				$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'groups(gr_name,gr_color,gr_cond,gr_auth_create,gr_auth_reply,gr_auth_see,gr_auth_flood,gr_status'.(((int)$_POST['numbermps']>=0)?',gr_mps':'').') VALUES(\''.clean($_POST['classtitle']).'\',\''.clean($_POST['classcolor']).'\','.$condition.',\''.$auth_create.'\',\''.$auth_reply.'\',\''.$auth_see.'\','.$canflood.','.$st.''.(((int)$_POST['numbermps']>=0)?','.(int)$_POST['numbermps']:'').')');
				cacheMods();
				setAllUsersPostClass();
				cacheClasses();

				redirect(manage_url('admin.php','forum-admin.html').'?act=users&sub=2');
			}
		} else trigger_error(lang('error_class_title'),E_USER_WARNING);
	}
} elseif ($sub==4) {
	if (isset($_POST['changemod'])) {
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_mod=\'\' WHERE usr_mod!=0');
		
		$rm=$GLOBALS['cb_db']->query('SELECT usr_id,gr_id
			FROM '.$GLOBALS['cb_db']->prefix.'users 
			LEFT JOIN '.$GLOBALS['cb_db']->prefix.'groups ON gr_id=usr_class 
			WHERE gr_status=1 ORDER BY gr_id');
		
		$oldgr = 0;
		while ($um = $GLOBALS['cb_db']->fetch_array($rm)) {
			if (isset($_POST['gr'.$um['gr_id']]) && $oldgr != $um['gr_id']) {
				$gmod = array_filter($_POST['gr'.$um['gr_id']],'is_numeric');
				if (count($gmod)) {
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'groups SET gr_mod=\''.implode('/',$gmod).'\' WHERE gr_id='.$um['gr_id']);
				}
				$oldgr = $um['gr_id'];
			}
			if (isset($_POST['usr'.$um['usr_id']])) {
				$umod = array_filter($_POST['usr'.$um['usr_id']],'is_numeric');
				if (count($umod)) {
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_mod=\''.implode('/',$umod).'\' WHERE usr_id='.$um['usr_id']);
				}
			}
		}
		
		cacheMods();
	}
} elseif ($sub==5) {
	if (isset($_POST['changeclass'])) {
		if (isset($_POST['selectuser_type'],$_POST['selectuser_name'],$_POST['selectuser_id'])) {
			$id=(int)$_POST['selectuser_id'];
			if ($_POST['selectuser_type']=='name') $id=getUserid($_POST['selectuser_name']);
			if ($id!==1) {
				if (isUser($id)) {
					if ($_POST['nameclass']=='default') {
						require_once(CB_PATH.'include/lib/lib.users.php');
						setUserPostClass($id,true);
						trigger_error(str_replace('{name}',getUserName($id),lang('user_success_tonormal')),E_USER_NOTICE);
					} elseif (is_numeric($_POST['nameclass'])) {
						require_once(CB_CACHE_CLASSES);
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_class=\''.(int)$_POST['nameclass'].'\' WHERE usr_id='.$id);
						trigger_error(str_replace(array('{name}','{class}'),array(getUserName($id),$GLOBALS['cb_classes'][$_POST['nameclass']]['gr_name']),lang('user_success_toclass')),E_USER_NOTICE);
					} else trigger_error(lang('error_class_noexist'),E_USER_WARNING);
					cacheMods();
				} else trigger_error(lang('error_user_noexist'),E_USER_WARNING);
			}
		}
	}
} elseif ($sub==6) {
	if (isset($_POST['setrights'])) {
		$auth_see = array();
		$auth_reply = array();
		$auth_create = array();
		
		foreach ($_POST as $key => $value) {
			if (preg_match('#^see_(.+?)$#',$key)) {
				if ($value=='on')
					$auth_see[] = preg_replace('#^see_(.+?)$#','$1',$key);
			}
			if (preg_match('#^reply_(.+?)$#',$key)) {
				if ($value=='on')
					$auth_reply[] = preg_replace('#^reply_(.+?)$#','$1',$key);
			}
			if (preg_match('#^create_(.+?)$#',$key)) {
				if ($value=='on')
					$auth_create[] = preg_replace('#^create_(.+?)$#','$1',$key);
			}
		}
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'groups 
								SET gr_auth_see=\''.implode('/',$auth_see).'\',
									gr_auth_reply=\''.implode('/',$auth_reply).'\',
									gr_auth_create=\''.implode('/',$auth_create).'\' 
								WHERE gr_cond=-2');
		
		redirect(manage_url('admin.php','forum-admin.html').'?act=users&sub=6');
	}
} elseif ($sub==7) {
	if (isset($_POST['search'])) {
		$url='act=users&sub=7';
		if (!empty($_POST['author'])) $url.='&author='.$_POST['author'];
		if (!empty($_POST['type'])) $url.='&type='.$_POST['type'];
		if (!empty($_POST['concerns'])) $url.='&concerns='.$_POST['concerns'];
		redirect(manage_url('admin.php','forum-admin.html').'?'.$url);
	}
} elseif ($sub==8) {
	if (isset($_GET['val']) || isset($_GET['del'])) {
		$val=isset($_GET['val']);
		$accid = ($val)?(int)$_GET['val']:(int)$_GET['del'];
		$accname = $GLOBALS['cb_db']->single_result('SELECT usr_name FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_id = '.$accid.' AND usr_registered != \'TRUE\'');
		$acclact = $GLOBALS['cb_db']->single_result('SELECT usr_lastaction FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_id = '.$accid.' AND usr_registered != \'TRUE\'');
		if ($accname) {
			if ($val) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_registered=\'TRUE\' WHERE usr_id='.$accid);
				if (!$acclact)
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value+1 WHERE st_field=\'registered_users\'');
			} elseif (!$acclact) 
				$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_id='.$accid);

			trigger_error(lang(array('item' => $val?'pa_mnv_ok_validate':'pa_mnv_ok_delete','name' => $accname)),E_USER_NOTICE);
		} else redirect(manage_url('admin.php','forum-admin.html').'?act=users&sub=8'.(isset($_GET['page'])?'&page='.(int)$_GET['page']:''));
	}
} elseif ($sub==9) {
	if (isset($_POST['renameuser'],$_POST['renameuser_old'],$_POST['renameuser_new'])) {
		$old = getUserId($_POST['renameuser_old']);
		if ((bool)$old) {
			if ($old != 1 || $_SESSION['cb_user']->userid == 1) {
				if (!(bool)getUserId($_POST['renameuser_new'])) {
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_name=\''.clean($_POST['renameuser_new']).'\' WHERE usr_id='.$old);
					trigger_error(lang(array('item'=>'pa_renameuser_success','old'=>clean($_POST['renameuser_old'],STR_TODISPLAY),'new'=>clean($_POST['renameuser_new'],STR_TODISPLAY))),E_USER_NOTICE);
				} else trigger_error(lang('pa_renameuser_error_new'),E_USER_WARNING);
			} else trigger_error(lang('pa_renameuser_error_admin'),E_USER_WARNING);
		} else trigger_error(lang('pa_renameuser_error_old'),E_USER_WARNING);
	}
} elseif ($sub==10) {
	if (isset($_POST['createrank'],$_POST['ranktitle'],$_POST['rankposts'])) {
		if (utf8_strlen(trim($_POST['ranktitle']))>2) {
			if (ctype_digit($_POST['rankposts']) && (int)$_POST['rankposts']>=0) {
				if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
					if ((int)$_GET['edit'] == 1 && (int)$_POST['rankposts']==0) {
						trigger_error(lang('error_rank_basic'),E_USER_WARNING);
					} else {
						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'ranks SET rk_name=\''.clean($_POST['ranktitle']).'\',rk_posts='.(int)$_POST['rankposts'].' WHERE rk_id='.(int)$_GET['edit']);
					}
				} else {
					$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'ranks(rk_name,rk_posts) VALUES (\''.clean($_POST['ranktitle']).'\','.(int)$_POST['rankposts'].')');
				}
				cacheClasses();
				redirect(manage_url('admin.php','forum-admin.html').'?act=users&sub=2');
			} else trigger_error(lang('error_rank_posts'),E_USER_WARNING);
		} else trigger_error(lang('error_rank_title'),E_USER_WARNING);
	}
}

if ($sub==1) { //Add validated user
	$GLOBALS['cb_tpl']->assign('pa_users_add_classmenu',getAdminClassMenu('nameclass'));
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_users','pa_submenu_users_addvaluser'));
	$GLOBALS['cb_tpl']->assign('usr_part','adduser');
} elseif ($sub==2) { // Show User Classes and ranks
	$classescontents=array();
	$return = $GLOBALS['cb_db']->query('SELECT gr_id,gr_name,gr_cond,gr_hide FROM '.$GLOBALS['cb_db']->prefix.'groups WHERE gr_cond>-2 ORDER BY gr_cond');
	while ($groupdata = $GLOBALS['cb_db']->fetch_assoc($return)) {
		$classescontents[] = array(
			'cond' => ($groupdata['gr_cond']>=0)?$groupdata['gr_cond']:'admin',
			'name' => $groupdata['gr_name'],
			'id' => $groupdata['gr_id'],
			'hide' => $groupdata['gr_hide']
			);
	}
	$GLOBALS['cb_tpl']->assign('pa_users_showclasses_classes',$classescontents);
	
	$rankscontents=array();
	$return = $GLOBALS['cb_db']->query('SELECT rk_id,rk_name,rk_posts FROM '.$GLOBALS['cb_db']->prefix.'ranks ORDER BY rk_posts');
	while ($rankdata = $GLOBALS['cb_db']->fetch_assoc($return)) {
		$rankscontents[] = array(
			'name' => $rankdata['rk_name'],
			'id' => $rankdata['rk_id'],
			'posts' => $rankdata['rk_posts']
			);
	}
	$GLOBALS['cb_tpl']->assign('pa_users_showclasses_ranks',$rankscontents);
	
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_users','pa_submenu_users_showclasses'));
	$GLOBALS['cb_tpl']->assign('usr_part','showclasses');
} elseif ($sub==3) { // Create User Class
	$edit=false;
	$title='';
	$color='';
	$condition='';
	$type='normal';
	$canflood=false;
	$auth_create=array();
	$auth_see=array();
	$auth_reply=array();
	$mps=20;
	if (isset($_GET['edit'])) {
		$return = $GLOBALS['cb_db']->query('SELECT gr_id,gr_name,gr_cond,gr_auth_reply,gr_mps,gr_auth_create,gr_auth_see,gr_auth_flood,gr_color,gr_status FROM '.$GLOBALS['cb_db']->prefix.'groups WHERE gr_id!=0 AND gr_id='.(int)$_GET['edit']);
		if ($groupdata = $GLOBALS['cb_db']->fetch_assoc($return)) {
			$edit=true;
			$title=$groupdata['gr_name'];
			$color=$groupdata['gr_color'];
			$condition=($groupdata['gr_cond']>=0)?$groupdata['gr_cond']:'admin';
			$auth_create=explode('/',$groupdata['gr_auth_create']);
			$auth_see=explode('/',$groupdata['gr_auth_see']);
			$auth_reply=explode('/',$groupdata['gr_auth_reply']);
			$canflood=($groupdata['gr_auth_flood']==1)?true:false;
			$mps=$groupdata['gr_mps'];
			if ($groupdata['gr_status']==2) $type='admin';
			elseif ($groupdata['gr_status']==1) $type='mod';

			$GLOBALS['cb_tpl']->assign('pa_editing',true);
			$GLOBALS['cb_tpl']->assign('pa_editing_id',$groupdata['gr_id']);
			$GLOBALS['cb_tpl']->assign('pa_editing_name',$groupdata['gr_name']);
		} else $GLOBALS['cb_tpl']->assign('pa_editing',false);
	} else $GLOBALS['cb_tpl']->assign('pa_editing',false);

	$GLOBALS['cb_tpl']->assign('pa_users_editclass_classtitle',$title);
	$GLOBALS['cb_tpl']->assign('pa_users_editclass_classcolor',$color);
	$GLOBALS['cb_tpl']->assign('pa_users_editclass_canflood_checked',(($canflood)?'checked="checked"':''));
	$GLOBALS['cb_tpl']->assign('pa_users_editclass_numbermps',$mps);

	if (empty($condition)) {
		$GLOBALS['cb_tpl']->assign('pa_users_editclass_classcond_posts_input','<input type="text" name="postscond" maxlength="5" size="1" value="0" />');
		$GLOBALS['cb_tpl']->assign('pa_users_editclass_classcond_posts_checked','checked="checked"');
		$GLOBALS['cb_tpl']->assign('pa_users_editclass_classcond_admin_checked','');
	} else {
		if ($condition=='admin') {
			$GLOBALS['cb_tpl']->assign('pa_users_editclass_classcond_posts_input','<input type="text" name="postscond" maxlength="5" size="1" value="0" />');
			$GLOBALS['cb_tpl']->assign('pa_users_editclass_classcond_posts_checked','');
			$GLOBALS['cb_tpl']->assign('pa_users_editclass_classcond_admin_checked','checked="checked"');
		} else {
			$GLOBALS['cb_tpl']->assign('pa_users_editclass_classcond_posts_input','<input type="text" name="postscond" maxlength="5" size="1" value="'.$condition.'" />');
			$GLOBALS['cb_tpl']->assign('pa_users_editclass_classcond_posts_checked','checked="checked"');
			$GLOBALS['cb_tpl']->assign('pa_users_editclass_classcond_admin_checked','');
		}
	}
	$GLOBALS['cb_tpl']->assign('pa_users_editclass_classtype_menu_normal_checked',(($type=='normal')?' checked="checked" ':''));
	$GLOBALS['cb_tpl']->assign('pa_users_editclass_classtype_menu_mod_checked',(($type=='mod')?' checked="checked" ':''));
	$GLOBALS['cb_tpl']->assign('pa_users_editclass_classtype_menu_admin_checked',(($type=='admin')?' checked="checked" ':''));

	$topicgroups = array();
	if ($tg=getTopicGroupsArray()) {
		foreach ($tg as $id => $name) {
			$topicgroups[] = array(
				'tg_name' => $name,
				'tg_id' => $id,
				'see_checked' => (in_array($id,$auth_see)),
				'create_checked' => (in_array($id,$auth_create)),
				'reply_checked' => (in_array($id,$auth_reply))
				);
		}
	}
	$GLOBALS['cb_tpl']->assign('pa_auth_topicgroups',$topicgroups);

	if (!$edit) {
		$GLOBALS['cb_tpl']->assign('pa_users_editclass_submit','pa_createclass_confirm');
		$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_users','pa_submenu_users_createclasses'));
	} else {
		$GLOBALS['cb_tpl']->assign('pa_users_editclass_submit','pa_editclass_confirm');
		$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_users','pa_submenu_users_editclass'));
	}
	$GLOBALS['cb_tpl']->assign('usr_part','editclasses');
} elseif ($sub==4) { // Mods
	if ($tg=getTopicGroupsArray()) {
		$returnmod=$GLOBALS['cb_db']->query('SELECT usr_id,usr_name,usr_mod,gr_id,gr_name,gr_mod 
			FROM '.$GLOBALS['cb_db']->prefix.'users 
			LEFT JOIN '.$GLOBALS['cb_db']->prefix.'groups ON gr_id=usr_class 
			WHERE gr_status=1 ORDER BY gr_name,usr_name');
		
		$pname = '';
		$usersmod = array();
		while ($um = $GLOBALS['cb_db']->fetch_array($returnmod)) {
			$grmod = explode('/',$um['gr_mod']);
			$umod = array_merge(explode('/',$um['usr_mod']),$grmod);
			if ($pname != $um['gr_name']) {
				$usersmod[] = array(
					'grid' => $um['gr_id'],
					'grname' => $um['gr_name'],
					'tocheck' => $grmod,
					'grcheck' => $grmod
					);
				$pname = $um['gr_name'];
			}
			$usersmod[] = array(
				'uid' => $um['usr_id'],
				'uname' => $um['usr_name'],
				'grid' => $um['gr_id'],
				'tocheck' => $umod,
				'grcheck' => $grmod
				);
		}
		
		$m_cols = '';
		foreach ($tg as $tg_id => $tg_name)
			$m_cols .= '<td class="modnum"><span title="'.$tg_name.'">'.$tg_id.'</span></td>';
		
		$GLOBALS['cb_tpl']->assign('pa_m_cols',$m_cols);
		$GLOBALS['cb_tpl']->assign('pa_m_modtable',$usersmod);
		$GLOBALS['cb_tpl']->assign('pa_m_corr',$tg);
	}
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_users','pa_submenu_users_mods'));
	$GLOBALS['cb_tpl']->assign('usr_part','moderators');
} elseif ($sub==5) { // Change a user's class
	$GLOBALS['cb_tpl']->assign('pa_users_showclasses_putuser_classmenu',getAdminClassMenu('nameclass'));
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_users','pa_submenu_users_changeuser'));
	$GLOBALS['cb_tpl']->assign('usr_part','changeuser');
} elseif ($sub==6) { // Set non-connected persons properties
	$guests_auth = $GLOBALS['cb_db']->single_result('SELECT CONCAT(gr_auth_see,\'|\',gr_auth_create,\'|\',gr_auth_reply) FROM '.$GLOBALS['cb_db']->prefix.'groups WHERE gr_cond=-2');
	$guests_auth = explode('|',$guests_auth);
	$auth_create = explode('/',$guests_auth[1]);
	$auth_see = explode('/',$guests_auth[0]);
	$auth_reply = explode('/',$guests_auth[2]);
	$topicgroups = array();
	if ($tg=getTopicGroupsArray()) {
		foreach ($tg as $id => $name) {
			$topicgroups[] = array(
				'tg_name' => $name,
				'tg_id' => $id,
				'see_checked' => (in_array($id,$auth_see)),
				'create_checked' => (in_array($id,$auth_create)),
				'reply_checked' => (in_array($id,$auth_reply))
				);
		}
	}
	$GLOBALS['cb_tpl']->assign('gr_guests_topicgroups',$topicgroups);
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_users','pa_submenu_users_notconnected'));
	$GLOBALS['cb_tpl']->assign('usr_part','notconnected');
} elseif ($sub==7) { // Show last moderators actions
	require_once(CB_PATH.'include/lib/lib.log.php');

	$pagenumber = (isset($_GET['page']))?(int)$_GET['page']:1;
	$nbdisp = 20;
	
	/* Mise en place des conditions. */
	$where = '';
	$url = '';
	if (!empty($_GET['author'])) {
		$where.=' WHERE u1.usr_name LIKE \''.clean($_GET['author']).'%\' ';
		$url.='&amp;author='.clean($_GET['author'],STR_TODISPLAY);
	}
	if (!empty($_GET['type'])) {
		$where.=((!empty($where))?' AND ':' WHERE ').'log_type='.(int)$_GET['type'];
		$url.='&amp;type='.(int)$_GET['type'];
	}
	if (!empty($_GET['concerns'])) {
		$where.=((!empty($where))?' AND ':' WHERE ').' u2.usr_name LIKE \''.$_GET['concerns'].'%\'';
		$url.='&amp;concerns='.clean($_GET['concerns'],STR_TODISPLAY);
	}

	$searching = (!empty($_GET['author']) || !empty($_GET['type']) || !empty($_GET['concerns']));

	$r = $GLOBALS['cb_db']->query('SELECT SQL_CALC_FOUND_ROWS log_type,log_timestamp,u1.usr_name AS modname,u1.usr_id AS modid,u2.usr_id AS uid,u2.usr_name AS uname,t2.topic_id AS topicid,t2.topic_name AS topicname,m.msg_id AS mid,t1.topic_id AS mtopicid,t1.topic_name AS mtname FROM '.$GLOBALS['cb_db']->prefix.'log AS l
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users AS u1 ON u1.usr_id = l.log_usermake
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'users AS u2 ON u2.usr_id = l.log_rep_user
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'messages AS m ON m.msg_id = l.log_rep_msg
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'topics AS t1 ON t1.topic_id = m.msg_topicid
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'topics AS t2 ON t2.topic_id = l.log_rep_topic
		'.$where.'
		ORDER BY log_timestamp DESC 
		LIMIT '.(($pagenumber-1)*$nbdisp).','.$nbdisp);
	
	$nblog = $GLOBALS['cb_db']->single_result('SELECT FOUND_ROWS()');
	
	$log=array();
	while ($d = $GLOBALS['cb_db']->fetch_assoc($r)) {
		$concerns='';
		if (isset($d['uid'])) $concerns.='<a href="'.manage_url('index.php?act=user&amp;showprofile='.$d['uid'],'forum-m'.$d['uid'].','.rewrite_words($d['uname']).'.html').'">'.$d['uname'].'</a>';
		if (isset($d['topicid'])) $concerns.=((!empty($concerns))?' - ':'').'<a href="'.manage_url('index.php?showtopic='.$d['topicid'],'forum-t'.$d['topicid'].','.rewrite_words($d['topicname']).'.html').'">'.$d['topicname'].'</a>';
		if (isset($d['mid'],$d['mtopicid'])) $concerns.=((!empty($concerns))?' - ':'').'<a href="'.manage_url('index.php?showtopic='.$d['mtopicid'].'&amp;message='.$d['mid'],'forum-t'.$d['mtopicid'].'-m'.$d['mid'].'.html').'">'.$d['mtname'].'</a>';
		if (empty($concerns)) $concerns='---';

		$log[] = array(
			'log_type' => getLogDesc($d['log_type']),
			'log_time' => dateFormat($d['log_timestamp']),
			'log_make' => '<a href="'.manage_url('index.php?act=user&amp;showprofile='.$d['modid'],'forum-m'.$d['modid'].','.rewrite_words($d['modname']).'.html').'">'.$d['modname'].'</a>',
			'log_concerns' => $concerns
			);
	}
	
	$GLOBALS['cb_tpl']->assign('o_pagemenu',pageMenu($nblog,$pagenumber,$nbdisp,manage_url('admin.php','forum-admin.html').'?act=users&amp;sub=7&amp;page=[num_page]'.$url));
	
	$GLOBALS['cb_tpl']->assign('o_log',$log);
	$GLOBALS['cb_tpl']->assign('o_searching',$searching);
	if (count($log) == 0 && $searching) trigger_error(lang('pa_o_log_nosearch'),E_USER_NOTICE);
	$GLOBALS['cb_tpl']->assign('o_log_type_choosemenu',chooseMenuLog('type',((!empty($_GET['type']))?(int)$_GET['type']:0)));
	$GLOBALS['cb_tpl']->assign('o_log_make',((!empty($_GET['author']))?clean($_GET['author'],STR_TODISPLAY):''));
	$GLOBALS['cb_tpl']->assign('o_log_concerns',((!empty($_GET['concerns']))?clean($_GET['concerns'],STR_TODISPLAY):''));

	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_users','pa_submenu_users_lastactions'));
	$GLOBALS['cb_tpl']->assign('usr_part','lastmodacts');
} elseif ($sub==8) { // Show not validated accounts
	$results = array();

	$pagenumber=1;
	if (isset($_GET['page'])) $pagenumber=(int)$_GET['page'];

	$nb = 20;
	$r = $GLOBALS['cb_db']->query('
		SELECT SQL_CALC_FOUND_ROWS usr_name,usr_id,usr_lastaction,usr_email,usr_registertime
		FROM '.$GLOBALS['cb_db']->prefix.'users
		WHERE usr_registered != \'TRUE\'
		ORDER BY usr_lastaction DESC,usr_registertime DESC
		LIMIT '.(($pagenumber-1)*$nb).','.$nb);

	$nb_nvm = $GLOBALS['cb_db']->single_result('SELECT FOUND_ROWS()');

	while ($d = $GLOBALS['cb_db']->fetch_assoc($r)) {
		$results[] = array(
			'id' => $d['usr_id'],
			'name' => $d['usr_name'],
			'mail' => $d['usr_email'],
			'date' => dateFormat(($d['usr_lastaction'] != 0)?$d['usr_lastaction']:$d['usr_registertime']),
			'change' => ($d['usr_lastaction'] != 0)
			);
	}

	$GLOBALS['cb_tpl']->assign('mnv_pagemenu',pageMenu($nb_nvm,$pagenumber,$nb,manage_url('admin.php?act=users&amp;sub=8&amp;page=[num_page]','forum-admin.html?act=users&amp;sub=8&amp;page=[num_page]')));
	$GLOBALS['cb_tpl']->assign('mnv_users',$results);
	$GLOBALS['cb_tpl']->assign('mnv_page',$pagenumber);

	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_users','pa_submenu_users_mannotval'));
	$GLOBALS['cb_tpl']->assign('usr_part','mannotval');
} elseif ($sub==9) { // Rename user
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_users','pa_submenu_users_renameuser'));
	$GLOBALS['cb_tpl']->assign('usr_part','renameuser');
} elseif ($sub==10) { // Create or edit rank
	$edit=false;
	$title='';
	$posts='';
	
	if (isset($_GET['edit'])) {
		$return = $GLOBALS['cb_db']->query('SELECT rk_name,rk_posts FROM '.$GLOBALS['cb_db']->prefix.'ranks WHERE rk_id='.(int)$_GET['edit']);
		if ($rankdata = $GLOBALS['cb_db']->fetch_assoc($return)) {
			$edit=true;
			$title = $rankdata['rk_name'];
			$posts = $rankdata['rk_posts'];
		} else $GLOBALS['cb_tpl']->assign('pa_editing',false);
	} else $GLOBALS['cb_tpl']->assign('pa_editing',false);

	$GLOBALS['cb_tpl']->assign('pa_users_editrank_title',$title);
	$GLOBALS['cb_tpl']->assign('pa_users_editrank_posts',$posts);
	$GLOBALS['cb_tpl']->assign('pa_editing',$edit);

	if (!$edit) {
		$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_users','pa_submenu_users_createrank'));
	} else {
		$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_users','pa_submenu_users_editrank'));
	}
	$GLOBALS['cb_tpl']->assign('usr_part','ranks');
}

$GLOBALS['cb_tpl']->assign('g_part','admin_users.php');
?>
