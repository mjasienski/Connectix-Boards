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

$_SESSION['cb_user']->connected('index_showusers');
$GLOBALS['cb_tpl']->lang_load('users.lang');
$GLOBALS['cb_addressbar'][] = lang('users');
$GLOBALS['cb_pagename'][] = lang('users');

/* Création de la requète */
$url='';
$sortposs=array('usr_name','usr_id','usr_class','usr_nbmess');
$query = 'SELECT SQL_CALC_FOUND_ROWS usr_id,usr_nbmess,usr_registertime,usr_name,usr_class,con_timestamp,gr_name,gr_color 
	FROM '.$GLOBALS['cb_db']->prefix.'users
	LEFT JOIN '.$GLOBALS['cb_db']->prefix.'groups ON gr_id=usr_class
	LEFT OUTER JOIN  '.$GLOBALS['cb_db']->prefix.'connected ON con_id=usr_id
	WHERE usr_registered=\'TRUE\' ';
if (isset($_GET['su_class']) && is_numeric($_GET['su_class'])) {
	$query.='AND usr_class=\''.clean($_GET['su_class']).'\' ';
	$url.='&amp;su_class='.clean($_GET['su_class']);
}
if (isset($_GET['su_name']) && !empty($_GET['su_name'])) {
	$query.='AND usr_name LIKE \''.clean($_GET['su_name']).'%\' ';
	$url.='&amp;su_name='.clean($_GET['su_name']);
}
if (isset($_GET['su_sort']) && in_array($_GET['su_sort'],$sortposs)) {
	$query.='ORDER BY '.$_GET['su_sort'].' ';
	$url.='&amp;su_sort='.$_GET['su_sort'];
	if (isset($_GET['su_order']) && $_GET['su_order']=='desc') {
		$query.='DESC ';
		$url.='&amp;su_order='.$_GET['su_order'];
	}
} else {
	$query.='ORDER BY usr_name ';
	if (isset($_GET['su_order']) && $_GET['su_order']=='desc') {
		$query.='DESC ';
		$url.='&amp;su_order='.$_GET['su_order'];
	}
}

/* Page courante */
$currentpage=1;
if (isset($_GET['page'])) $currentpage=(int)$_GET['page'];
$query.='LIMIT '.(($currentpage-1)*$_SESSION['cb_user']->usr_pref_usrs).','.$_SESSION['cb_user']->usr_pref_usrs;

/* On fait la requète */
$return=$GLOBALS['cb_db']->query($query);

/* Nombre de résultats */
$numusers=$GLOBALS['cb_db']->single_result('SELECT FOUND_ROWS()');

/* Menu de pages. */
$GLOBALS['cb_tpl']->assign('lu_pagemenu',pageMenu($numusers,$currentpage,$_SESSION['cb_user']->usr_pref_usrs,manage_url('index.php?act=members&amp;page=[num_page]'.$url,'forum-members-p[num_page].html'.((!empty($url))?'?'.utf8_substr($url,5):''))));

if ($numusers>0) {
	/* Affichage des utilisateurs */
	$users = array();
	while ($usr=$GLOBALS['cb_db']->fetch_assoc($return)) {
		$users[] = array(
			'lu_u_connected' 	=> ((time()-$usr['con_timestamp']<$GLOBALS['cb_cfg']->config['connectedlimit']*60)?'<span class="usr_online"><span>'.lang('usr_online').'</span></span>':'<span class="usr_offline"><span>'.lang('usr_offline').'</span></span>'),
			'lu_u_userlink' 	=> '<a href="'.manage_url('index.php?act=user&amp;showprofile='.$usr['usr_id'],'forum-m'.$usr['usr_id'].','.rewrite_words($usr['usr_name']).'.html').'" title="'.$usr['gr_name'].'" style="color:'.$usr['gr_color'].';">'.$usr['usr_name'].'</a> ('.$usr['usr_id'].')',
			'lu_u_nbmess' 		=> $usr['usr_nbmess'],
			'lu_u_class' 		=> $usr['gr_name'],
			'lu_u_reg' 			=> dateFormat($usr['usr_registertime'],2,true)
			);
	}
	$GLOBALS['cb_tpl']->assign_ref('lu_users',$users);
} else trigger_error(lang('users_noone_todisplay'),E_USER_NOTICE);

/* Menus de recherche */
$GLOBALS['cb_tpl']->assign('su_name',((isset($_GET['su_name']))?clean($_GET['su_name'],STR_TODISPLAY):''));

$items = array();
$items[] = array('name' => 'default','selected' => (isset($_GET['su_class']) && $_GET['su_class']=='default'),'value' => '','lang' => 'users_allclasses');
require_once(CB_CACHE_CLASSES);
foreach ($GLOBALS['cb_classes'] as $gr) {
	if ($gr['gr_hide'] == 0) 
		$items[] = array('name' => $gr['gr_id'],'selected' => (isset($_GET['su_class']) && $_GET['su_class']==$gr['gr_id']),'value' => $gr['gr_name'],'lang' => '');
}
$GLOBALS['cb_tpl']->assign('class_list',array ( 'name' => 'su_class', 'style' => 150, 'items' => $items ));

$items = array(
	array('name' => 'usr_name','selected' => (isset($_GET['su_sort']) && $_GET['su_sort']=='usr_name'),'value' => '','lang' => 'users_sort_name'),
	array('name' => 'usr_id','selected' => (isset($_GET['su_sort']) && $_GET['su_sort']=='usr_id'),'value' => '','lang' => 'users_sort_reg'),
	array('name' => 'usr_class','selected' => (isset($_GET['su_sort']) && $_GET['su_sort']=='usr_class'),'value' => '','lang' => 'users_sort_class'),
	array('name' => 'usr_nbmess','selected' => (isset($_GET['su_sort']) && $_GET['su_sort']=='usr_nbmess'),'value' => '','lang' => 'users_sort_posts')
	);
$GLOBALS['cb_tpl']->assign('sort_list',array ( 'name' => 'su_sort', 'style' => 150, 'items' => $items ));

$items = array(
	array('name' => 'asc','selected' => (isset($_GET['su_order']) && $_GET['su_order']=='asc'),'value' => '','lang' => 'users_asc'),
	array('name' => 'desc','selected' => (isset($_GET['su_order']) && $_GET['su_order']=='desc'),'value' => '','lang' => 'users_desc')
	);
$GLOBALS['cb_tpl']->assign('order_list',array ( 'name' => 'su_order', 'style' => 100, 'items' => $items ));

$GLOBALS['cb_tpl']->assign('g_part','part_showusers.php');
?>