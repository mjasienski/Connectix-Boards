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

$GLOBALS['cb_javascript'][] = '<script type="text/javascript" src="include/javascripts/cb_ajax.js"></script>';

/* Nombre de MP affichés par page. */
define('MP_PAGE',20);

/* Il faut être loggé pour accéder à cette section */
if (!$_SESSION['cb_user']->logged)
	trigger_error(lang('error_mustlogin'),E_USER_ERROR);

$GLOBALS['cb_tpl']->lang_load('mp.lang');
$GLOBALS['cb_addressbar'][] = lang('mp_title');
$GLOBALS['cb_pagename'][] = lang('mp_title');

$_SESSION['cb_user']->connected('index_mp');

require_once(CB_PATH.'include/lib/lib.mp.php');

/* Page demandée */
$askedpage = (isset($_GET['sub'])) ? (int)$_GET['sub'] : 1;

/** Gestion des variables POST **/
// Envoi d'un message
if (isset($_POST['mp_send'],$_POST['mp_subj'],$_POST['mp_mess'],$_POST['mp_to'])) {
	if (!empty($_POST['mp_to'])) {
		$touserid=getUserId($_POST['mp_to']);
		if ($touserid!=0) {
			if (sendMp($_SESSION['cb_user']->userid,$touserid,$_POST['mp_subj'],$_POST['mp_mess']))
				redirect(manage_url('index.php?act=mp&sub=1','forum-mp-inbox.html'));
		} else trigger_error(lang('error_user_noexist'),E_USER_WARNING);
	} else trigger_error(lang('error_to_noexist'),E_USER_WARNING);
// Suppression de messages
} elseif (isset($_POST['delete']) && (isset($_POST['messfrom']) || isset($_POST['messto']))) {
	$type = (isset($_POST['messfrom'])?'from':'to');
	$mpids = array_filter($_POST['mess'.$type],'ctype_digit');
	
	if (!empty($mpids)) {
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'mp SET mp_'.$type.'_del=1 WHERE mp_'.$type.'='.$_SESSION['cb_user']->userid.' AND mp_id IN ('.implode(',',$mpids).')');
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'mp WHERE mp_to_del=1 AND mp_from_del=1');
		if ($type == 'to') {
			$cnt_nbmp = $GLOBALS['cb_db']->single_result('SELECT COUNT(*) FROM '.$GLOBALS['cb_db']->prefix.'mp WHERE mp_to='.$_SESSION['cb_user']->userid.' AND mp_read = 0 AND mp_to_del = 0');
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_nbmp = '.$cnt_nbmp.' WHERE usr_id='.$_SESSION['cb_user']->userid);
			$_SESSION['cb_user']->nbmp = $cnt_nbmp;
		}
	}
	redirect(manage_url('index.php?act=mp&sub='.(($type=='from')?2:1),'forum-mp-'.(($type=='from')?'outbox':'inbox').'.html'));
// Suppression d'un message
} elseif (isset($_GET['delete'])) {
	$q = $GLOBALS['cb_db']->query('SELECT mp_id,mp_from,mp_to,mp_read FROM '.$GLOBALS['cb_db']->prefix.'mp  WHERE mp_id='.(int)$_GET['delete']);
	$redirect = $askedpage;
	if ($mp = $GLOBALS['cb_db']->fetch_assoc($q)) {
		if ($mp['mp_to'] == $_SESSION['cb_user']->userid) {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'mp SET mp_to_del=1 WHERE mp_id='.(int)$_GET['delete']);
			if ($mp['mp_read'] == 0) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_nbmp=usr_nbmp-1 WHERE usr_id='.$_SESSION['cb_user']->userid.' AND usr_nbmp > 0');
				$_SESSION['cb_user']->nbmp--;
			}
			if ($askedpage == 4) $redirect = 1;
		} elseif ($mp['mp_from'] == $_SESSION['cb_user']->userid) {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'mp SET mp_from_del=1 WHERE mp_id='.(int)$_GET['delete']);
			if ($askedpage == 4) $redirect = 2;
		}
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'mp WHERE mp_to_del=1 AND mp_from_del=1');
	}
	redirect(manage_url('index.php?act=mp&sub='.$redirect,'forum-mp-'.(($redirect==1)?'inbox':(($redirect==2)?'outbox':(($redirect==3)?'write':'read'))).'.html'));
}

/* Préremplissage des champs */
$mp_to	=(isset($_POST['mp_to']))?clean($_POST['mp_to'],STR_TODISPLAY):'';
$mp_subj=(isset($_POST['mp_subj']))?clean($_POST['mp_subj'],STR_TODISPLAY):'';
$mp_mess=(isset($_POST['mp_mess']))?clean($_POST['mp_mess'],STR_TODISPLAY):'';

$GLOBALS['cb_tpl']->assign(array(
	'mp_formaction' 	=> 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']),
	'mp_nbmp' 			=> getTotMp($_SESSION['cb_user']->userid),
	'mp_allowed'		=> $_SESSION['cb_user']->gr_mps,
	'mp_menu'			=> array(
		'title' => 'mp_command',
		'currentpage' => $askedpage,
		'url' => manage_url('index.php?act=mp&amp;sub=[num_page]','forum-mp-[num_page].html'),
		'items' => array(
			array('id' => manage_url(3,'write') , 'cid' => 3 , 'title' => 'mp_menu_write'),
			array('id' => manage_url(1,'inbox') , 'cid' => 1 , 'title' => 'mp_menu_recieved'),
			array('id' => manage_url(2,'outbox') , 'cid' => 2 , 'title' => 'mp_menu_sent')
		))
	));

if ($askedpage==1) {
	/* Listage des messages recus. */
	$nbmp=$GLOBALS['cb_db']->single_result('SELECT COUNT(mp_id) FROM '.$GLOBALS['cb_db']->prefix.'mp WHERE mp_to='.$_SESSION['cb_user']->userid.' AND mp_to_del=0');
	$nbpages = ceil($nbmp/MP_PAGE);
	$pagenumber = (isset($_GET['page']) && (int)$_GET['page']>0 && (int)$_GET['page']<=$nbpages) ? (int)$_GET['page'] : 1 ;

	$messages=array();
	$return=$GLOBALS['cb_db']->query('SELECT mp_read,mp_from,mp_id,mp_timestamp,mp_subj,usr_name
		FROM '.$GLOBALS['cb_db']->prefix.'mp
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id=mp_from
		WHERE mp_to='.$_SESSION['cb_user']->userid.' AND mp_to_del=0
		ORDER BY mp_id DESC
		LIMIT '.(($pagenumber-1)*MP_PAGE).','.MP_PAGE);
	while ($mp=$GLOBALS['cb_db']->fetch_assoc($return)) {
		$messages[]	= array(
			'mp_m_id' 	=> $mp['mp_id'],
			'mp_m_read' 	=> '<span class="'.(($mp['mp_read']==1)?'mp_r':'mp_u').'"><span>'.$GLOBALS['cb_tpl']->lang[(($mp['mp_read']==1)?'mp_r':'mp_u')].'</span></span>',
			'mp_m_date' 	=> dateFormat($mp['mp_timestamp'],1,true),
			'mp_m_userlink' => getUserLink($mp['mp_from'],$mp['usr_name'],''),
			'mp_m_subject' 	=> '<a href="'.manage_url('index.php?act=mp&amp;sub=4&amp;mess='.$mp['mp_id'],'forum-mp-read.html?mess='.$mp['mp_id']).'" id="mainlink_mp_'.$mp['mp_id'].'">'.$mp['mp_subj'].'</a>',
			'mp_m_delete'	=> '<a href="'.manage_url('index.php?act=mp&amp;sub=1&amp;delete='.$mp['mp_id'],'forum-mp-inbox.html?delete='.$mp['mp_id']).'" class="mp_del"><span>'.lang('mp_del').'</span></a>',
			'mp_m_checkbox' => '<input type="checkbox" name="messto[]" value="'.$mp['mp_id'].'" />'
			);
	}
	$GLOBALS['cb_tpl']->assign('mp_pagemenu',pageMenu($nbmp,$pagenumber,MP_PAGE,manage_url('index.php?act=mp&amp;sub=1&amp;page=[num_page]','forum-mp-inbox-p[num_page].html')));
	$GLOBALS['cb_tpl']->assign('mp_typebox','inbox');
	$GLOBALS['cb_tpl']->assign_ref('mp_messages',$messages);
	$GLOBALS['cb_tpl']->assign('mp_contents','inandoutbox');
} else if ($askedpage==2) {
	/* Listage des messages envoyés. */
	$nbmp=$GLOBALS['cb_db']->single_result('SELECT COUNT(mp_id) FROM '.$GLOBALS['cb_db']->prefix.'mp WHERE mp_from='.$_SESSION['cb_user']->userid.' AND mp_from_del=0');
	$nbpages = ceil($nbmp/MP_PAGE);
	$pagenumber = (isset($_GET['page']) && (int)$_GET['page']>0 && (int)$_GET['page']<=$nbpages) ? (int)$_GET['page'] : 1 ;

	$return=$GLOBALS['cb_db']->query('SELECT mp_read,mp_to,mp_id,mp_subj,mp_timestamp,usr_name
		FROM '.$GLOBALS['cb_db']->prefix.'mp
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id=mp_to
		WHERE mp_from='.$_SESSION['cb_user']->userid.' AND mp_from_del=0
		ORDER BY mp_id DESC
		LIMIT '.(($pagenumber-1)*MP_PAGE).','.MP_PAGE);
	$messages=array();
	while ($mp=$GLOBALS['cb_db']->fetch_assoc($return)) {
		$messages[] = array(
			'mp_m_id' 	=> $mp['mp_id'],
			'mp_m_read' 	=> '<span class="'.(($mp['mp_read']==1)?'mp_r':'mp_u').'"><span>'.$GLOBALS['cb_tpl']->lang[(($mp['mp_read']==1)?'mp_r':'mp_u')].'</span></span>',
			'mp_m_date' 	=> dateFormat($mp['mp_timestamp'],1,true),
			'mp_m_userlink' => getUserLink($mp['mp_to'],$mp['usr_name'],''),
			'mp_m_subject' 	=> '<a href="'.manage_url('index.php?act=mp&amp;sub=4&amp;mess='.$mp['mp_id'],'forum-mp-read.html?mess='.$mp['mp_id']).'" id="mainlink_mp_'.$mp['mp_id'].'">'.$mp['mp_subj'].'</a>',
			'mp_m_delete'	=> '<a href="'.manage_url('index.php?act=mp&amp;sub=2&amp;delete='.$mp['mp_id'],'forum-mp-outbox.html?delete='.$mp['mp_id']).'" class="mp_del"><span>'.lang('mp_del').'</span></a>',
			'mp_m_checkbox' => '<input type="checkbox" name="messfrom[]" value="'.$mp['mp_id'].'" />'
			);
	}
	$GLOBALS['cb_tpl']->assign('mp_pagemenu',pageMenu($nbmp,$pagenumber,MP_PAGE,manage_url('index.php?act=mp&amp;sub=2&amp;page=[num_page]','forum-mp-outbox-p[num_page].html')));
	$GLOBALS['cb_tpl']->assign('mp_typebox','outbox');
	$GLOBALS['cb_tpl']->assign_ref('mp_messages',$messages);
	$GLOBALS['cb_tpl']->assign('mp_contents','inandoutbox');
} else if ($askedpage==3) {
	/* Ecriture d'un message si la boite n'est pas pleine. */
	if (canHaveNewMp($_SESSION['cb_user']->userid,getTotMp($_SESSION['cb_user']->userid),$_SESSION['cb_user']->gr_mps)) {
		if (isset($_GET['reply']) && !isset($_POST['mp_previs'])) {
			$returnrep=$GLOBALS['cb_db']->query('SELECT mp_to,usr_name,mp_subj,mp_timestamp,mp_content FROM '.$GLOBALS['cb_db']->prefix.'mp LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id=mp_from WHERE mp_id='.(int)$_GET['reply']);
			if ($mprep=$GLOBALS['cb_db']->fetch_assoc($returnrep)) {
				if ($mprep['mp_to']==$_SESSION['cb_user']->userid) {
					$mp_to = $mprep['usr_name'];
					$mp_subj=''.((utf8_substr($mprep['mp_subj'],0,5)!=='Re : ')?'Re : ':'').$mprep['mp_subj'];
					$time=date('d/m/Y - H\hi', $mprep['mp_timestamp']);
					$mp_mess='[quote='.($mprep['usr_name']).' @ '.$time.']'.unclean($mprep['mp_content']).'[/quote]';
				}
			}
		} 
		if (isset($_GET['mpto'])) {
			if ((int)$_GET['mpto'] == $_SESSION['cb_user']->userid)
				trigger_error(lang('error_user_automess'),E_USER_WARNING);
			else
				$mp_to = getUserName((int)$_GET['mpto']);
		}
		if (isset($_POST['mp_previs']))
			$GLOBALS['cb_tpl']->assign('mp_w_previs_contents',clean($_POST['mp_mess'],STR_TODISPLAY + STR_MULTILINE + STR_PARSEBB));
		
		$GLOBALS['cb_tpl']->assign('mp_w_to',$mp_to);
		$GLOBALS['cb_tpl']->assign('mp_w_subject',$mp_subj);
		$GLOBALS['cb_tpl']->assign('mp_w_message',$mp_mess);
		$GLOBALS['cb_tpl']->assign('mp_contents','writing');
	} else {
		$GLOBALS['cb_tpl']->assign('mp_error','error_mp_mefull');
		$GLOBALS['cb_tpl']->assign('mp_contents','error');
	}
} else if ($askedpage==4) {
	/* Lecture d'un message */
	$messid=(isset($_GET['mess']))?(int)$_GET['mess']:0;
	$error='';
	if ($messid!==0) {
		$return=$GLOBALS['cb_db']->query('SELECT mp_from,mp_read,mp_from_del,mp_to,mp_to_del,mp_id,mp_subj,mp_content,mp_timestamp,usersfrom.usr_name AS fromusername,usersto.usr_name AS tousername,usersfrom.usr_avatar AS fromavatar,confrom.con_timestamp AS fromtime,conto.con_timestamp AS totime
			FROM '.$GLOBALS['cb_db']->prefix.'mp
			LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users AS usersfrom ON usersfrom.usr_id=mp_from
			LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users AS usersto ON usersto.usr_id=mp_to
			LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'connected AS confrom ON usersfrom.usr_id=confrom.con_id
			LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'connected AS conto ON usersto.usr_id=conto.con_id
			WHERE mp_id='.(int)$messid);
		if ($mp=$GLOBALS['cb_db']->fetch_assoc($return)) {
			if (($_SESSION['cb_user']->userid==$mp['mp_from'] && $mp['mp_from_del']==0) || ($_SESSION['cb_user']->userid==$mp['mp_to'] && $mp['mp_to_del']==0)) {
				if ($mp['mp_read']==0 && $mp['mp_to']==$_SESSION['cb_user']->userid) {
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_nbmp=usr_nbmp-1 WHERE usr_id='.$_SESSION['cb_user']->userid.' AND usr_nbmp > 0');
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'mp SET mp_read=1 WHERE mp_id='.(int)$messid);
					$_SESSION['cb_user']->setVars();
				}
				if ($mp['mp_to']==$_SESSION['cb_user']->userid) {
					$GLOBALS['cb_tpl']->assign('mp_typebox','inbox');
					$GLOBALS['cb_tpl']->assign('mp_r_userlink',getUserLink($mp['mp_from'],$mp['fromusername'],''));
					$GLOBALS['cb_tpl']->assign('mp_r_read','');
				} else {
					$GLOBALS['cb_tpl']->assign('mp_typebox','outbox');
					$GLOBALS['cb_tpl']->assign('mp_r_userlink',getUserLink($_SESSION['cb_user']->userid,$_SESSION['cb_user']->username,''));
					$GLOBALS['cb_tpl']->assign('mp_r_tolink',getUserLink($mp['mp_to'],$mp['tousername'],''));
					$GLOBALS['cb_tpl']->assign('mp_r_read',(($mp['mp_read']==1)?true:false));
				}
				$GLOBALS['cb_tpl']->assign('mp_r_fromcon',((isset($messages['fromtime']) && (time()-$messages['fromtime'])<($GLOBALS['cb_cfg']->config['connectedlimit']*60))?'<span class="usr_online"><span>'.lang('usr_online').'</span></span>':'<span class="usr_offline"><span>'.lang('usr_offline').'</span></span>'));
				$GLOBALS['cb_tpl']->assign('mp_r_date',dateFormat($mp['mp_timestamp'],1,true));
				$GLOBALS['cb_tpl']->assign('mp_r_id',$mp['mp_id']);
				$GLOBALS['cb_tpl']->assign('mp_r_avatar',getAvatar($mp['fromavatar']));
				$GLOBALS['cb_tpl']->assign('mp_r_subject',$mp['mp_subj']);
				$GLOBALS['cb_tpl']->assign('mp_r_message',$mp['mp_content']);
				$GLOBALS['cb_tpl']->assign('mp_contents','reading');
			} else $error='error_mp_notyou';
		} else $error='error_mp_noexist';
	} else $error='error_mp_noexist';
	if (!empty($error))  {
		$GLOBALS['cb_tpl']->assign('mp_error',$error);
		$GLOBALS['cb_tpl']->assign('mp_contents','error');
	}
}
$GLOBALS['cb_tpl']->assign('g_part','part_mps.php');
?>
