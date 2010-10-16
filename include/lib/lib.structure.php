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

//// Fonctions utiles pour les lus/non-lus ////

/* Fonction qui renvoie les groupes de sujets non-lus */
function getUnreadTgs($fid = null,$tgid = null) {
	// On ne peut pas ne pas être connecté
	if (!$_SESSION['cb_user']->logged) return array();
	
	// Groupes à considérer
	$yes_tgs = array();
	if ($fid != null)
		$yes_tgs = getSubTopicGroupsOfF ($fid);
	elseif ($tgid != null)
		$yes_tgs = getSubTopicGroupsOfTg ($tgid);
	else
		$yes_tgs = array_keys($GLOBALS['cb_str_tgnames']);
	
	$yes_tgs = array_diff($yes_tgs,$_SESSION['cb_user']->gr_auth_see);
	if (!$_SESSION['cb_user']->isModerator())
		$yes_tgs = array_diff($yes_tgs,$GLOBALS['cb_str_unvis']);
	
	// Inventaire des groupes de sujets dans lesquels on a lu quelque chose
	// Ils sont peut-être passés de non-lus à lus
	$read_by_user = array_intersect($_SESSION['cb_read_in'],$yes_tgs);
	
	// Déjà vérifiés, lus au moment où on les a vérifiés
	// Ils contiennent peut-être de nouveaux messages
	$checked_read = array();
	if (!empty($_SESSION['cb_tgs_maxids'])) {
		$checked_read = array_intersect(array_keys($_SESSION['cb_tgs_maxids']),$yes_tgs);
	}
	
	// Déjà vérifiés, non-lus
	$checked_unread = array();
	// Déjà vérifiés, non-lus et qu'on peut considérer comme tels, car on a rien lu dedans
	$checked_unread_ok = array();
	// Déjà vérifiés, non-lus et qu'il faut revérifier
	$checked_unread_ko = array();
	
	if (!empty($_SESSION['cb_unread_tgs'])) {
		$checked_unread = array_intersect($_SESSION['cb_unread_tgs'],$yes_tgs);
		$checked_unread_ok = array_diff($checked_unread,$read_by_user);
		$checked_unread_ko = array_intersect($checked_unread,$read_by_user);
	}
	
	// Ceux qui n'ont jamais été vérifiés
	$not_checked = array_diff($yes_tgs,$checked_unread,$checked_read);
	
	// A partir d'ici, 4 catégories de tgs:
	// - $checked_unread_ok : non-lus, pas besoin de traitement
	// - $checked_unread_ko : besoin du traitement lourd (getUnreadTgs_tool)
	// - $checked_read : besoin d'un traitement léger pour voir si de nouveaux messages ont été postés
	// - $not_checked : traitement simplifié ou lourd, selon qu'on gère les messages par session ou pas
	// On va donc tout vérifier un à un
	
	// Variables de résultats
	// On ne remplit que ce dont on est certain
	$unread = $checked_unread_ok;
	$read = array();
	
	// On vérifie que les topics précédemment lus ne sont pas devenus non-lus :
	// traitement de $checked_read
	if (count($checked_read) > 0) {
		$qverif = $GLOBALS['cb_db']->query('SELECT tg_id,topic_lastmessage 
			FROM '.$GLOBALS['cb_db']->prefix.'topicgroups
			LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topics ON topic_id = tg_lasttopic
			LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'usertgs ON utg_userid = '.$_SESSION['cb_user']->userid.' AND utg_tgid = topic_fromtopicgroup
			LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'usertopics ON ut_userid = '.$_SESSION['cb_user']->userid.' AND ut_topicid = topic_id
			WHERE 
				'.getUnreadTgs_attr($checked_read).'
				'.(($_SESSION['cb_user']->mark_as_read > 0)?'topic_lastmessage > '.$_SESSION['cb_user']->mark_as_read.' AND ':'').'
				(utg_tgid IS NULL OR utg_markasread < topic_lastmessage)
				AND (ut_topicid IS NULL OR ut_msgread < topic_lastmessage)');
		
		while ($tgu = $GLOBALS['cb_db']->fetch_assoc($qverif)) {
			if ((isset($_SESSION['cb_tgs_maxids'][$tgu['tg_id']]) && $_SESSION['cb_tgs_maxids'][$tgu['tg_id']] < $tgu['topic_lastmessage']) || !isset($_SESSION['cb_tgs_maxids'][$tgu['tg_id']]))
				$unread[$tgu['tg_id']] = $tgu['tg_id'];
		}
		
		// On remplit le tableau des lus
		$read = array_diff($checked_read,$unread);
	}
	
	// On vérifie que les topics précédemment non-lus ne sont pas devenus lus:
	// traitement de $checked_unread_ko
	if (count($checked_unread_ko) > 0) {
		// On fait la vérification (lent)
		$new_unread = getUnreadTgs_tool(getUnreadTgs_attr($checked_unread_ko));
		$new_read = array_diff($checked_unread_ko,$new_unread);
		
		// On met tout ce qu'il faut à jour
		$unread = array_merge($unread,$new_unread);
		$read = array_merge($read,$new_read);
		$_SESSION['cb_read_in'] = array_diff($_SESSION['cb_read_in'],$checked_unread_ko);
	}
	
	// On vérifie ce qui n'avait encore jamais été vérifié
	if (count($not_checked) > 0) {
		if ($GLOBALS['cb_cfg']->config['readornot_sessions'] == 'yes') {
			// Version rapide! On peut le faire grâce au fait que tous les messages de la dernière session sont  à marquer comme lus.
			$qverif = $GLOBALS['cb_db']->query('SELECT tg_id 
				FROM '.$GLOBALS['cb_db']->prefix.'topicgroups
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topics ON topic_id = tg_lasttopic
				LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'usertgs ON utg_userid = '.$_SESSION['cb_user']->userid.' AND utg_tgid = topic_fromtopicgroup
				LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'usertopics ON ut_userid = '.$_SESSION['cb_user']->userid.' AND ut_topicid = topic_id
				WHERE 
					'.getUnreadTgs_attr($yes_tgs).'
					'.(($_SESSION['cb_user']->mark_as_read > 0)?'topic_lastmessage > '.$_SESSION['cb_user']->mark_as_read.' AND ':'').'
					(utg_tgid IS NULL OR utg_markasread < topic_lastmessage)
					AND (ut_topicid IS NULL OR ut_msgread < topic_lastmessage)');
			
			while ($tgu = $GLOBALS['cb_db']->fetch_assoc($qverif))
				$unread[$tgu['tg_id']] = $tgu['tg_id'];
		} else {
			// Version lente
			$unread = array_merge($unread,getUnreadTgs_tool(getUnreadTgs_attr($not_checked)));
		}
		$read = array_merge($read,array_diff($not_checked,$unread));
	}
	
	// On supprime les doublons éventuels des variables de résultats
	$unread = array_unique($unread);
	$read = array_unique($read);
	
	// On marque comme non-lu un tg parent d'un tg non-lu
	$copy = $unread;
	foreach ($copy as $value) {
		$parents = getUpperTopicGroupsOfTg($value);
		foreach ($parents as $value2) {
			if (!in_array($value2,$unread)) 
				$unread[$value2] = $value2;
		}
	}
	
	// On assigne les variables de souvenir pour la prochaine fois
	$_SESSION['cb_max_id'] = $GLOBALS['cb_db']->single_result('SELECT MAX(msg_id) FROM '.$GLOBALS['cb_db']->prefix.'messages');
	$_SESSION['cb_unread_tgs'] = array_diff($_SESSION['cb_unread_tgs'],$read);
	$_SESSION['cb_unread_tgs'] = array_unique(array_merge($_SESSION['cb_unread_tgs'],$unread));
	
	// Groupes de sujets lus, quant à eux, pour avoir une info de leur dernière vérification
	foreach ($_SESSION['cb_tgs_maxids'] as $tgi => $maxid) {
		if (isset($unread[$tgi])) {
			$_SESSION['cb_tgs_maxids'][$tgi] = null;
			unset($_SESSION['cb_tgs_maxids'][$tgi]);
		}
	}
	foreach ($read as $tgi) $_SESSION['cb_tgs_maxids'][$tgi] = $_SESSION['cb_max_id'];
	
	// Et enfin, on retourne les topicgroups non-lus
	return $unread;
}
/* Fonction utile à getUnreadTgs, requète lourde */
function getUnreadTgs_tool ($attr = '') {
	$unread = array();
	$r = $GLOBALS['cb_db']->query('SELECT DISTINCT topic_fromtopicgroup
		FROM '.$GLOBALS['cb_db']->prefix.'topics
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'usertgs ON utg_userid = '.$_SESSION['cb_user']->userid.' AND utg_tgid = topic_fromtopicgroup
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'usertopics ON ut_userid = '.$_SESSION['cb_user']->userid.' AND ut_topicid = topic_id
		WHERE
			'.$attr.'
			'.(($_SESSION['cb_user']->mark_as_read > 0)?'topic_lastmessage > '.$_SESSION['cb_user']->mark_as_read.' AND ':'').'
			(utg_tgid IS NULL OR utg_markasread < topic_lastmessage)
			AND (ut_topicid IS NULL OR ut_msgread < topic_lastmessage)
			AND topic_status!=2');
	
	while ($tgunread = $GLOBALS['cb_db']->fetch_assoc($r))
		$unread[$tgunread['topic_fromtopicgroup']] = $tgunread['topic_fromtopicgroup'];
	
	return $unread;
}

/* Fonction utile à getUnreadTgs, crée un attribut de requète */
function getUnreadTgs_attr ($yes_tgs,$no_tgs = array()) {
	if (count($no_tgs)>0 || count($yes_tgs)>0) {
		if (count($yes_tgs) > 0) {
			if (count(array_diff($yes_tgs,$no_tgs)) > 0)
				return 'topic_fromtopicgroup IN ('.implode(',',array_diff($yes_tgs,$no_tgs)).') AND ';
			else 
				return 'topic_fromtopicgroup = NULL AND ';
		} else return 'topic_fromtopicgroup NOT IN ('.implode(',',$no_tgs).') AND ';
	}
	return '';
}

/* Fonction qui sert à 'marquer comme lu' */
function markread ($fid = null,$tgid = null) {
	// On ne peut pas ne pas être connecté
	if (!$_SESSION['cb_user']->logged) return false;
	
	$maxmsg = $GLOBALS['cb_db']->single_result('SELECT MAX(msg_id) FROM '.$GLOBALS['cb_db']->prefix.'messages');
	$redirect = manage_url('index.php','forum.html');
	$topicgroups = array();
	
	if (!empty($fid)) {
		// Un forum particulier
		$topicgroups = getSubTopicGroupsOfF($fid);
		$redirect = manage_url('index.php?showforum='.$fid,'forum-f'.$fid.'.html');
	} elseif (!empty($tgid)) {
		// Un groupe de sujets particulier
		$topicgroups = array_merge(array($tgid),getSubTopicGroupsOfTg($tgid));
		$redirect = manage_url('index.php?showtopicgroup='.$tgid,'forum-tg'.$tgid.'.html');
	} else {
		// Tout le forum
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_markasread='.$maxmsg.' WHERE usr_id='.$_SESSION['cb_user']->userid);
		$_SESSION['cb_user']->mark_as_read = $maxmsg;
		
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'usertgs WHERE utg_userid='.$_SESSION['cb_user']->userid.' AND utg_mail = 0');
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'usertopics WHERE ut_userid='.$_SESSION['cb_user']->userid.' AND ut_posted=0 AND ut_bookmark=0 AND ut_mail=0');
	}
	
	// Si on marque des groupes de sujets: on les marque comme lus dans la bdd
	if (count($topicgroups) > 0) {
		// On marque comme lu
		$qry = '';
		foreach ($topicgroups as $val) {
			$qry.= ((!empty($qry))?',':'').'('.$_SESSION['cb_user']->userid.','.$val.','.$maxmsg.')';
		}
		$GLOBALS['cb_db']->query('REPLACE INTO '.$GLOBALS['cb_db']->prefix.'usertgs(utg_userid,utg_tgid,utg_markasread) VALUES '.$qry);
		
		// On supprime les entrées inutiles de usertopics
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'usertopics
			USING '.$GLOBALS['cb_db']->prefix.'usertopics,'.$GLOBALS['cb_db']->prefix.'topics
			WHERE 
				ut_topicid=topic_id 
				AND ut_userid='.$_SESSION['cb_user']->userid.' 
				AND topic_fromtopicgroup IN ('.implode(',',$topicgroups).') 
				AND ut_posted=0 AND ut_bookmark=0 AND ut_mail=0');
	}
	
	// On s'occupe des variables de session qui servent à déterminer si des groupes sont non-lus
	if (count($topicgroups) > 0) {
		if (isset($_SESSION['cb_read_in']))
			$_SESSION['cb_read_in'] = array_diff($_SESSION['cb_read_in'],$topicgroups);
		
		if (isset($_SESSION['cb_unread_tgs']))
			$_SESSION['cb_unread_tgs'] = array_diff($_SESSION['cb_unread_tgs'],$topicgroups);
		
		foreach ($topicgroups as $tgi)
			$_SESSION['cb_tgs_maxids'][$tgi] = $maxmsg;
	} else {
		$_SESSION['cb_read_in'] = array();
		$_SESSION['cb_unread_tgs'] = array();
		$_SESSION['cb_max_id'] = $maxmsg;
		
		foreach ($GLOBALS['cb_str_tgnames'] as $tgi => $val)
			$_SESSION['cb_tgs_maxids'][$tgi] = $maxmsg;
	}
	
	redirect($redirect);
}

//// Fonctions relatives à la gestion de la structure du forum ////

/* Fonction qui renvoie un tableau avec tous les id des sous-groupes du forum $fid */
function getSubTopicGroupsOfF ($fid) {
	$ids = array();
	foreach ($GLOBALS['cb_str_pf'][$fid] as $tgid) {
		$ids[]=$tgid;
		getSubTopicGroups_rec($ids,$tgid);
	}
	return $ids;
}
/* Fonction qui renvoie un tableau avec tous les id des sous-groupes du groupe $tgid */
function getSubTopicGroupsOfTg ($tgid) {
	$ids = array();
	getSubTopicGroups_rec($ids,$tgid);
	return $ids;
}
/* Fonction récursive servant aux deux fonctions précédentes */
function getSubTopicGroups_rec (&$ids,$tgid) {
	if (isset($GLOBALS['cb_str_ptg'][$tgid])) {
		foreach ($GLOBALS['cb_str_ptg'][$tgid] as $stgid) {
			$ids[]=$stgid;
			getSubTopicGroups_rec ($ids,$stgid);
		}
	}
}
/* Fonction qui renvoie un tableau avec tous les id des groupes parents du groupe $tgid (contient le groupe $tgid) */
function getUpperTopicGroupsOfTg ($tgid) {
	$ids = array();
	getUpperTopicGroupsOfTg_rec($ids,$tgid);
	return $ids;
}
/* Fonction récursive servant à la fonction précédente */
function getUpperTopicGroupsOfTg_rec (&$ids,$tgid) {
	$ids[] = $tgid;
	if (isset($GLOBALS['cb_str_ftg'][$tgid]))
		getUpperTopicGroupsOfTg_rec($ids,$GLOBALS['cb_str_ftg'][$tgid]);
}
?>