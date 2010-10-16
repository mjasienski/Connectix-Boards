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

$_SESSION['cb_user']->connected('index_search');
$GLOBALS['cb_tpl']->lang_load('search.lang');
$GLOBALS['cb_tpl']->lang_load('ftg.lang');

require_once(CB_PATH.'include/lib/lib.search.php');

/* Traitement des données */
$tgid=0; // Si la recherche se fait dans un groupe de sujets particulier...
$displayTopics=true;
$srcTitle=true;
$nbresults=0;
if (isset($_GET['search']) && ((isset($_GET['keys']) && utf8_strlen(trim($_GET['keys']))>2) || (isset($_GET['author']) && utf8_strlen(trim($_GET['author']))>1))) {
	$nbwords = 0;
	$src_userid = 0;
	$src_onlyusr = false;

	/* Recherche dans les titres ou dans les messages */
	$srcTitle = (isset($_GET['torm']) && $_GET['torm']=='msgs')?false:true;

	/* Affichage des réponses par messages ou sujets */
	$displayTopics = $srcTitle || ((isset($_GET['display']) && $_GET['display']=='msgs')?false:true);

	/* DEBUT REQUETE RECUPERATION DES IDS */
	/*** Composition de la requète ***/
	$queryselect='SELECT sm_topicid,sm_msgid,COUNT(*) as cnt
		FROM '.$GLOBALS['cb_db']->prefix.'src_words sw
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'src_matches sm ON sm_wordid=sw_id
		';
	$msgid_field = 'sm_msgid';
	$topicid_field = 'sm_topicid';

	/*** Conditions WHERE ***/
	// Va être utile pour les jointures
	$src_join = array(
		'topics' => array(false,'sm_topicid','topic_id'),
		'messages' => array(false,($srcTitle?'topic_lastmessage':'sm_msgid'),'msg_id'),
		'users' => array(false,'msg_userid','usr_id'),
		'topicgroups' => array(false,'topic_fromtopicgroup','tg_id')
		);
	
	// Si on recherche dans les titres ou les messages
	$where = ' WHERE sm_msgid'.($srcTitle?'=0':'!=0');

	// Par Mots-clés
	$insidewords = array();
	$highlight='';
	if (isset($_GET['keys']) && utf8_strlen(trim($_GET['keys']))>1) {
		$keywords = explode (' ',$_GET['keys']);
		$tempw='';
		foreach ($keywords as $value) {
			if (utf8_strlen(trim($value)) >= SRC_MIN_LENGTH && utf8_strlen(trim($value)) <= SRC_MAX_LENGTH) {
				$nbwords++;
				if (utf8_strpos($value,'%') !== false)
					$tempw.=' OR sw_word LIKE \''.utf8_strtolower(clean($value)).'\'';
				else
					$insidewords[] = utf8_strtolower(clean($value));
			}
		}
		$highlight = $srcTitle?'':implode('-',$insidewords);
		$where.=' AND ('.($insidewords?'sw_word IN (\''.implode('\',\'',$insidewords).'\')':'0').$tempw.')';
	}

	// Par Auteur de message
	if (isset($_GET['author']) && utf8_strlen(trim($_GET['author']))>1) {
		$src_userid = $GLOBALS['cb_db']->single_result('SELECT usr_id FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_name=\''.clean($_GET['author']).'\'');
		if ($src_userid) {
			if (!$nbwords) {
				$queryselect='SELECT msg_topicid,msg_id FROM '.$GLOBALS['cb_db']->prefix.'messages sw';
				$msgid_field = 'msg_id';
				$topicid_field = 'msg_topicid';
				$src_join['topics'][1] = 'msg_topicid';
				$srcTitle=false;
				$displayTopics=false;
				$src_onlyusr = true;
				$where = ' WHERE 1';
			}
			if ($srcTitle) {
				$src_join['topics'][0] = true;
				$where.=' AND topic_starter='.$src_userid;
			} else {
				if (!$src_onlyusr) {
					if ($srcTitle) $src_join['topics'][0] = true;
					$src_join['messages'][0] = true;
				}
				$where.=' AND msg_userid='.$src_userid;
			}
		} else trigger_error(lang('src_novalidusername'),E_USER_WARNING);
	}

	// On vérifie que la recherche a un sens
	if (!$nbwords && !$src_userid)
		trigger_error(lang('src_novalidcrits'),E_USER_WARNING);
	else {
		// Spécification du topicgroup
		if (isset($_GET['where'])) {
			if ($_GET['where']=='tg_def') {
				// On ne fait rien...
			} elseif (preg_match('#^tg_[0-9]+$#',$_GET['where'])) {
				$tgid=(int)(preg_replace('#tg_([0-9]+)#','$1',$_GET['where']));
				if (isTg($tgid)) {
					$src_join['topics'][0] = true;
					$tgids = array($tgid);
					if (isset($_GET['where_includesub']) && $_GET['where_includesub']=='on') {
						require_once(CB_PATH.'include/lib/lib.structure.php');
						$tgids = array_merge($tgids,getSubTopicGroupsOfTg($tgid));
					}
					$where.=' AND topic_fromtopicgroup IN ('.implode(',',$tgids).')';
				} else $tgid=0;
			}
		}

		// On interdit la recherche dans les groupes de sujets dont on n'a pas les droits
		if (count($_SESSION['cb_user']->gr_auth_see) > 0) {
			$src_join['topics'][0] = true;
			$where.=' AND topic_fromtopicgroup NOT IN ('.implode(',',$_SESSION['cb_user']->gr_auth_see).')';
		}

		// Spécification de la limite de temps
		$from_limit = (isset($_GET['from']))?$_GET['from']:'fr_365';
		if(preg_match('#^fr_[0-9]+$#',$from_limit)) {
			$nbdays = preg_replace('#fr_([0-9]+)#','$1',$from_limit);
			$limit = $nbdays*24*3600 + (int)date('i')*60 + (int)date('s');
			
			if (!$src_onlyusr) {
				if ($srcTitle) $src_join['topics'][0] = true;
				$src_join['messages'][0] = true;
			}

			$where.=' AND msg_timestamp>'.(time()-$limit);
		}

		/*** Conditions GROUP BY ***/
		$group = $src_onlyusr ? '' : ' GROUP BY '.($srcTitle?'sm_topicid':'sm_msgid');

		/*** Conditions ORDER BY ***/
		$order='';
		if (isset($_GET['sort'])) {
			$order=' ORDER BY';
			switch ($_GET['sort']) {
				case 'so_time':
					if (!$src_onlyusr) {
						if ($srcTitle) $src_join['topics'][0] = true;
						$src_join['messages'][0] = true;
					}
					$order.=' msg_id';
					break;
				case 'so_title':
					$src_join['topics'][0] = true;
					$order.=' topic_name';
					break;
				case 'so_author':
					if ($srcTitle) {
						$src_join['topics'][0] = true;
						$src_join['users'][0] = true;
						$src_join['users'][1] = 'topic_starter';
					} else {
						$src_join['messages'][0] = true;
						$src_join['users'][0] = true;
						$src_join['users'][1] = 'msg_userid';
					}
					$order.=' usr_name';
					break;
				case 'so_tg':
					$src_join['topics'][0] = true;
					$src_join['topicgroups'][0] = true;
					$order.=' tg_name';
					break;
				default:
					if (!$src_onlyusr) {
						if ($srcTitle) $src_join['topics'][0] = true;
						$src_join['messages'][0] = true;
					}
					$order.=' msg_timestamp';
			}
			if (isset($_GET['sort_order']) && $_GET['sort_order']=='asc') $order.=' ASC';
			else $order.=' DESC';
		} else {
			if (!$src_onlyusr) {
				if ($srcTitle) $src_join['topics'][0] = true;
				$src_join['messages'][0] = true;
			}
			$order=' ORDER BY msg_timestamp DESC';
		}

		/*** Clause Having ***/
		$having = $nbwords?' HAVING cnt='.$nbwords:'';

		/*** Conditions LIMIT (en rapport avec la page) ***/
		$pagenumber=(isset($_GET['page']))?(int)$_GET['page']:1;
		
		/*** Jointures ***/
		foreach ($src_join as $table => $data) {
			if ($data[0])
				$queryselect.=' LEFT JOIN '.$GLOBALS['cb_db']->prefix.$table.' ON '.$data[1].'='.$data[2].' '."\n";
		}
		/* FIN REQUETE */
		
		$GLOBALS['cb_tpl']->assign('s_displayresults',false);
		
		/* On fait la requète, si elle n'est pas déja en cache */
		$req = $queryselect.$where.$group.$having;
		$srcresults = array();
		if (isset($_SESSION['cb_src_cache'][md5($req)])) {
			$srcresults = $_SESSION['cb_src_cache'][md5($req)];
		} else {
			$return = $GLOBALS['cb_db']->query($req);
			$srcresults = array();
			while ($d=$GLOBALS['cb_db']->fetch_assoc($return))
				$srcresults[] = ($displayTopics?$d[$topicid_field]:$d[$msgid_field]);
			$srcresults = array_unique($srcresults);
			$_SESSION['cb_src_cache'][md5($req)] = $srcresults;
		}
		$nbresults = count($srcresults);
		
		/* URL courante */
		$currenturl='';
		foreach($_GET as $key => $value) {
			if ($key!=='page' && $key !== 'act') {
				if (empty($currenturl)) $currenturl.=$key.'='.$value;
				else $currenturl.='&amp;'.$key.'='.$value;
			}
		}
		
		if ($nbresults) {
			/* Affichage */
			$GLOBALS['cb_tpl']->assign('s_pagemenu',pageMenu($nbresults,$pagenumber,$_SESSION['cb_user']->usr_pref_res,manage_url('index.php?act=src&amp;page=[num_page]&amp;'.$currenturl,'forum-search-p[num_page].html?'.$currenturl)));
			$GLOBALS['cb_tpl']->assign('s_nbresults',$nbresults);
			$GLOBALS['cb_tpl']->assign('s_displayresults',true);
			
			// On limite en fonction de la page
			$limit=' LIMIT '.(($pagenumber-1)*$_SESSION['cb_user']->usr_pref_res).','.$_SESSION['cb_user']->usr_pref_res;
			
			// On récupère les données à afficher
			$results=array();
			if ($displayTopics) {
				$GLOBALS['cb_tpl']->assign('highlight',$highlight);
				require_once(CB_PATH.'include/lib/lib.topics.php');
				$results = &getTopics($srcresults,false,true,$order.$limit);
			} else {
				$return=$GLOBALS['cb_db']->query('SELECT msg_id,topic_id,topic_name,tg_id,tg_name,msg_message,msg_timestamp,usr_id,usr_name,msg_guest
					FROM '.$GLOBALS['cb_db']->prefix.'messages
					LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topics ON topic_id=msg_topicid
					LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id=msg_userid
					LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topicgroups ON tg_id=topic_fromtopicgroup
					WHERE msg_id IN ('.implode(',',$srcresults).')
					'.$order.$limit);

				while ($result=$GLOBALS['cb_db']->fetch_assoc($return)) {
					$results[] = array(
						's_r_topic_id' => $result['topic_id'],
						's_r_topic_name' => $result['topic_name'],
						's_r_msg_id' => $result['msg_id'],
						's_r_topic_path' => implode(' '.CB_ADDR_SEP.' ',getTgPath($result['tg_id'])).' '.CB_ADDR_SEP.' <a href="'.manage_url('index.php?showtopicgroup='.$result['tg_id'],'forum-tg'.$result['tg_id'].','.rewrite_words($result['tg_name']).'.html').'">'.$result['tg_name'].'</a>',
						's_r_messcontents' => (!empty($highlight))?preg_replace('#('.str_replace('-','|',urldecode($highlight)).')#i','<span class="hl">$1</span>',$result['msg_message']):$result['msg_message'],
						's_r_userlink' => getUserLink($result['usr_id'],$result['usr_name'],$result['msg_guest']),
						's_r_date' => dateFormat($result['msg_timestamp'],1,true)
						);
				}
			}

			$GLOBALS['cb_tpl']->assign_ref('s_results',$results);

			trigger_error(str_replace('{n}',$nbresults,lang('src_results_notice')),E_USER_NOTICE);
		} else trigger_error(lang('src_noresult'),E_USER_WARNING);
	}
}

$GLOBALS['cb_tpl']->assign('s_srctitles',$srcTitle);
$GLOBALS['cb_tpl']->assign('s_displaytopics',$displayTopics);
$GLOBALS['cb_addressbar'][] = (($nbresults > 0)?lang('src_results').' ('.lang(array('item' => 'src_nbresults', 'n' => $nbresults)).')':lang('search'));
$GLOBALS['cb_pagename'][] = lang('search');

/* Menus de classement */
$items = array(
	array('name' => 'fr_def','selected' => (isset($_GET['from']) && $_GET['from']=='fr_def'),'value' => '','lang' => 'src_from_all'),
	array('name' => 'fr_1','selected' => (isset($_GET['from']) && $_GET['from']=='fr_1'),'value' => '','lang' => 'src_from_oneday'),
	array('name' => 'fr_2','selected' => (isset($_GET['from']) && $_GET['from']=='fr_2'),'value' => '','lang' => 'src_from_twodays'),
	array('name' => 'fr_7','selected' => (isset($_GET['from']) && $_GET['from']=='fr_7'),'value' => '','lang' => 'src_from_oneweek'),
	array('name' => 'fr_14','selected' => (isset($_GET['from']) && $_GET['from']=='fr_14'),'value' => '','lang' => 'src_from_twoweeks'),
	array('name' => 'fr_30','selected' => (isset($_GET['from']) && $_GET['from']=='fr_30'),'value' => '','lang' => 'src_from_onemonth'),
	array('name' => 'fr_365','selected' => ((isset($_GET['from']) && $_GET['from']=='fr_365')||!isset($_GET['from'])),'value' => '','lang' => 'src_from_oneyear')
	);
$fromMenu = array ( 'name' => 'from', 'style' => 300, 'items' => $items );

$items = array(
	array('name' => 'so_time','selected' => (isset($_GET['sort']) && $_GET['sort']=='so_time'),'value' => '','lang' => 'src_sort_time'),
	array('name' => 'so_title','selected' => (isset($_GET['sort']) && $_GET['sort']=='so_title'),'value' => '','lang' => 'src_sort_title'),
	array('name' => 'so_author','selected' => (isset($_GET['sort']) && $_GET['sort']=='so_author'),'value' => '','lang' => 'src_sort_author'),
	array('name' => 'so_tg','selected' => (isset($_GET['sort']) && $_GET['sort']=='so_tg'),'value' => '','lang' => 'src_sort_tg')
	);
$sortMenu = array ( 'name' => 'sort', 'style' => 300, 'items' => $items );

$GLOBALS['cb_tpl']->assign('sort_checked',(isset($_GET['sort_order']) && $_GET['sort_order'] == 'asc')?'asc':'desc');

$GLOBALS['cb_tpl']->assign('s_form_wheremenu',showForumMenu ('where','src_def',0,$tgid,0,0,true));
$GLOBALS['cb_tpl']->assign('s_form_where_includesub',((isset($_GET['where_includesub']) && $_GET['where_includesub']=='on')?'checked="checked"':''));
$GLOBALS['cb_tpl']->assign('s_form_frommenu',$fromMenu);
$GLOBALS['cb_tpl']->assign('s_form_sortmenu',$sortMenu);
$GLOBALS['cb_tpl']->assign('s_form_keywords',((isset($_GET['keys']))?clean($_GET['keys'],STR_TODISPLAY):''));
if ($_SESSION['cb_user']->logged) {
	$GLOBALS['cb_tpl']->assign('src_connected',true);
	$GLOBALS['cb_tpl']->assign('s_form_read',((isset($_GET['read']) && $_GET['read']=='on')?'checked="checked"':''));
} else $GLOBALS['cb_tpl']->assign('src_connected',false);
$GLOBALS['cb_tpl']->assign('s_form_author',((isset($_GET['author']))?clean($_GET['author'],STR_TODISPLAY):''));

$GLOBALS['cb_tpl']->assign('g_part','part_search.php');
?>