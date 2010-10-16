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

//// Fonction qui retourne le code html d'une liste de groupes de sujets ////
function &getForums ($fid = null,$tgid = null) {
	// Groupes à considérer
	$yes_tgs = array();
	if ($fid != null)
		$yes_tgs = $GLOBALS['cb_str_pf'][$fid];
	elseif ($tgid != null)
		$yes_tgs = $GLOBALS['cb_str_ptg'][$tgid];
	else {
		foreach($GLOBALS['cb_str_pf'] as $f)
			$yes_tgs = array_merge($yes_tgs,$f);
	}
	
	// Groupes qu'on ne doit pas considérer
	$no_tgs = $_SESSION['cb_user']->gr_auth_see;
	if (!$_SESSION['cb_user']->isModerator())
		$no_tgs = array_unique(array_merge($no_tgs,$GLOBALS['cb_str_unvis']));
	$yes_tgs = array_diff($yes_tgs,$no_tgs);
	
	// On ne continue pas si on n'a rien à afficher!
	if (count($yes_tgs) == 0) return $yes_tgs;
	
	// Récupération du nombre de messages postés dans les groupes de sujets considérés
	// et récupération des derniers messages de ces groupes
	$check = $yes_tgs;
	$corr = array_combine($yes_tgs,$yes_tgs);
	$walk = array_intersect($yes_tgs,array_keys($GLOBALS['cb_str_ptg']));
	foreach ($walk as $tgi) {
		require_once(CB_PATH.'include/lib/lib.structure.php');
		$sub = array_diff(getSubTopicGroupsOfTg($tgi),$no_tgs);
		if (count($sub) > 0) {
			$check = array_merge($check,$sub);
			foreach ($sub as $subtg) $corr[$subtg] = $tgi;
		}
	}
	
	$rInfos = $GLOBALS['cb_db']->query('SELECT 
			tg_id,tg_nbtopics,tg_nbmess,
			msg_id,msg_timestamp,msg_guest,
			topic_id,topic_name,topic_nbreply,
			usr_id,usr_name
		FROM '.$GLOBALS['cb_db']->prefix.'topicgroups
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topics ON topic_id=tg_lasttopic
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'messages ON msg_id=topic_lastmessage
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id=msg_userid
		WHERE tg_id IN ('.implode(',',$check).')');
	
	$counts = array_combine($yes_tgs,array_pad(array(),count($yes_tgs),array('t'=>0,'m'=>0)));
	$last_msgs = array();
	while ($lm = $GLOBALS['cb_db']->fetch_assoc($rInfos)) {
		if ($lm['msg_id'] > 0) {
			if (!isset($last_msgs[$corr[$lm['tg_id']]]) || 
				$last_msgs[$corr[$lm['tg_id']]]['msg_id'] < $lm['msg_id']) $last_msgs[$corr[$lm['tg_id']]] = $lm;
		}
		$counts[$corr[$lm['tg_id']]]['t'] += $lm['tg_nbtopics'];
		$counts[$corr[$lm['tg_id']]]['m'] += $lm['tg_nbmess'];
	}
	
	// Groupes de sujets non-lus
	$unread = array();
	if ($_SESSION['cb_user']->logged) {
		require_once(CB_PATH.'include/lib/lib.structure.php');
		$unread = getUnreadTgs($fid,$tgid);
	}
	
	// Boucle qui gère l'affichage des groupes de sujets demandés
	$cats=array();
	$forums = array('id' => 0,'name' => '');
	$cfid=0;
	
	foreach ($yes_tgs as $tg_id) {
		if (isset($GLOBALS['cb_str_ff'][$tg_id]) && $cfid !== $GLOBALS['cb_str_ff'][$tg_id]) {
			if ($cfid != 0) {
				$cats[] = $forums;
				$forums = array();
			}
			$cfid = $GLOBALS['cb_str_ff'][$tg_id];
			$forums['id']	= $GLOBALS['cb_str_ff'][$tg_id];
			$forums['name']	= $GLOBALS['cb_str_fnames'][$GLOBALS['cb_str_ff'][$tg_id]];
		}
		
		$tg_sub = '';
		if (isset($GLOBALS['cb_str_ptg'][$tg_id])) {
			$subtgs = array_diff($GLOBALS['cb_str_ptg'][$tg_id],$no_tgs);
			foreach ($subtgs as $stgid)
				$tg_sub.=(empty($tg_sub)?'':' - ').'<a href="'.manage_url('index.php?showtopicgroup='.$stgid, 'forum-tg'.$stgid.','.rewrite_words($GLOBALS['cb_str_tgnames'][$stgid]).'.html').'">'.$GLOBALS['cb_str_tgnames'][$stgid].'</a>';
		}

		$tgdata = array(
			'tg_read' =>		!in_array($tg_id,$unread),
			'tg_id' =>	 		$tg_id,
			'tg_name' =>		$GLOBALS['cb_str_tgnames'][$tg_id],
			'tg_subtgs' =>		$tg_sub,
			'tg_visible' =>	 	!isset($GLOBALS['cb_str_unvis'][$tg_id]),
			'tg_comment' =>	 	(isset($GLOBALS['cb_str_tgcomments'][$tg_id])?$GLOBALS['cb_str_tgcomments'][$tg_id]:''),
			'tg_link' =>	 	(isset($GLOBALS['cb_str_tglinks'][$tg_id])?$GLOBALS['cb_str_tglinks'][$tg_id]:''),
			'tg_islink' =>	 	isset($GLOBALS['cb_str_tglinks'][$tg_id]),
			'tg_nbtopics' =>	$counts[$tg_id]['t'],
			'tg_nbmess' =>	 	$counts[$tg_id]['m'],
			'tg_lastm_tid' =>	isset($last_msgs[$tg_id])?$last_msgs[$tg_id]['topic_id']:0
			);
		if (isset($last_msgs[$tg_id])) {
			$tgdata = array_merge($tgdata,array(
				'tg_lastm_time' =>	 dateFormat($last_msgs[$tg_id]['msg_timestamp'],1,true),
				'tg_lastm_page' =>	 ceil(($last_msgs[$tg_id]['topic_nbreply']+1)/$_SESSION['cb_user']->usr_pref_msgs),
				'tg_lastm_mid' =>	 $last_msgs[$tg_id]['msg_id'],
				'tg_lastm_tname' =>	 $last_msgs[$tg_id]['topic_name'],
				'tg_lastm_ulink' =>	 getUserLink($last_msgs[$tg_id]['usr_id'],$last_msgs[$tg_id]['usr_name'],$last_msgs[$tg_id]['msg_guest'])
				));
		}
		
		$forums['contents'][] = $tgdata;
	}
	
	$cats[] = $forums;
	return $cats;
}
?>