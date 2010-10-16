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

//// Fonction qui retourne le tableau des données d'un sujet à afficher ////
function &getTopics (&$topic_ids , $group_data = false, $show_path = false, $order = null, $tg_markasread = 0) {
	if (empty($order)) {
		if ($group_data) $order='ORDER BY topic_type DESC,topic_lastmessage DESC';
		else $order='ORDER BY topic_lastmessage DESC';
	}

	$sql_result = $GLOBALS['cb_db']->query('
		SELECT
			topic_id,topic_name,topic_comment,topic_poll,topic_starter,topic_guest,topic_views,topic_status,topic_displaced,topic_fromtopicgroup,topic_lastmessage,topic_nbreply,topic_type,
			'.(($show_path)?'tg_id,tg_name,':'').'
			msg_timestamp,msg_id,msg_userid,msg_guest,
			lastusers.usr_name AS last_usr_name, startusers.usr_name AS start_usr_name
			'.(($_SESSION['cb_user']->logged)?',ut_posted,ut_msgread,ut_bookmark,ut_mail'.(($tg_markasread == 0)?',utg_markasread':''):'').'
		FROM '.$GLOBALS['cb_db']->prefix.'topics t
		LEFT JOIN '.$GLOBALS['cb_db']->prefix.'messages m ON m.msg_id=t.topic_lastmessage
		'.(($show_path)?'LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topicgroups tg ON tg.tg_id=t.topic_fromtopicgroup':'').'
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'users lastusers ON lastusers.usr_id=m.msg_userid
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'users startusers ON startusers.usr_id=t.topic_starter
		'.(($_SESSION['cb_user']->logged)?'
			LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'usertopics ON ut_userid='.$_SESSION['cb_user']->userid.' AND ut_topicid=t.topic_id
			'.(($tg_markasread == 0)?'
				LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'usertgs ON utg_userid='.$_SESSION['cb_user']->userid.' AND utg_tgid=t.topic_fromtopicgroup
			':'')
		:'').'
		WHERE topic_id IN ('.implode(',',$topic_ids).')
		'.$order);

	$groups=array();
	$topics=array();
	$pretype=null;
	$first=true;

	while ($tpc=$GLOBALS['cb_db']->fetch_assoc($sql_result)) {
		if ($pretype!=$tpc['topic_type'] && $group_data) {
			if (!$first) {
				$group['tg_topics'] = $topics;
				$groups[] = $group;
				$topics=array();
			}
			$group['tg_tt_type'] = $tpc['topic_type'];
			$pretype=$tpc['topic_type'];
		}

		/* Icone - Lu ou pas*/
		$read = true;
		if ($_SESSION['cb_user']->logged) {
			$mar = ($tg_markasread == 0)?$tpc['utg_markasread']:$tg_markasread;
			if ($tpc['msg_id'] > $_SESSION['cb_user']->mark_as_read && $tpc['msg_id'] > $mar) {
				$read = (isset($tpc['ut_msgread']) && $tpc['ut_msgread'] == $tpc['topic_lastmessage']);
			}
		}
		$icon = '';
		if ($tpc['topic_status']==2) $icon = 'st_disp';
		else {
			if (!empty($tpc['topic_poll']) && $tpc['topic_status']==0) $icon = 'st_poll';
			elseif ($tpc['topic_status']==1 && $tpc['topic_type']!=2) $icon = 'st_clsd';
			else $icon = 'st';
			$icon.= '_'.(($read)?'r':'u');
			if (isset($tpc['ut_posted']) && $tpc['ut_posted']==1) $icon.='_p';
		}
		
		$ticon = '';
		if ($tpc['topic_type']==1) 
			$ticon = 'st_pin';
		elseif ($tpc['topic_type']==2) 
			$ticon = 'st_ann';
		if (isset($tpc['ut_bookmark']) && $tpc['ut_bookmark'])
			$ticon = 'st_bookmark';
		elseif (isset($tpc['ut_mail']) && $tpc['ut_mail'])	
			$ticon = 'st_mail';
		
		/* Pour l'affichage des groupes de sujets et les sujets non-lus */
		if (!$read && !isset($_SESSION['cb_unread_tgs'][$tpc['topic_fromtopicgroup']]))
			$_SESSION['cb_unread_tgs'][$tpc['topic_fromtopicgroup']] = $tpc['topic_fromtopicgroup'];
		
		/* Toutes les infos */
		$topics[] = array(
			'tg_t_id' => $tpc['topic_id'],
			'tg_t_type' => $tpc['topic_type'],
			'tg_t_status' => $tpc['topic_status'],
			'tg_t_displaced' => $tpc['topic_displaced'],
			'tg_t_topicicon' => $icon,
			'tg_t_statusicon' => $ticon,
			'tg_t_quickicon' => ((!$read && $tpc['topic_status']!=2)?'<a href="'.manage_url('index.php?showtopic='.$tpc['topic_id'].'&amp;gotofirstunreadpost=1','forum-t'.$tpc['topic_id'].'-firstunreadpost.html').'" title="'.lang('t_firstunread_title').'" class="quickjoin"></a>':''),
			'tg_t_name' => $tpc['topic_name'],
			'tg_t_path' => ($show_path)?implode(' '.CB_ADDR_SEP.' ',getTgPath($tpc['tg_id'])).' '.CB_ADDR_SEP.' <a href="'.manage_url('index.php?showtopicgroup='.$tpc['topic_fromtopicgroup'],'forum-tg'.$tpc['topic_fromtopicgroup'].','.rewrite_words($tpc['tg_name']).'.html').'">'.$tpc['tg_name'].'</a> '.CB_ADDR_SEP.' ':'',
			'tg_t_topiccomment' => $tpc['topic_comment'],
			'tg_t_topicpages' => getQuickPages($tpc['topic_id'],$tpc['topic_nbreply'],rewrite_words($tpc['topic_name'])),
			'tg_t_topicstarter' => getUserLink($tpc['topic_starter'],$tpc['start_usr_name'],$tpc['topic_guest']),
			'tg_t_nbreply' => (($tpc['topic_status']==2)?'---':$tpc['topic_nbreply']),
			'tg_t_views' => (($tpc['topic_status']==2)?'---':$tpc['topic_views']),
			'tg_t_lastreply_url' => manage_url('index.php?showtopic='.$tpc['topic_id'].'&amp;page='.ceil(($tpc['topic_nbreply']+1)/$_SESSION['cb_user']->usr_pref_msgs).'#'.$tpc['msg_id'],'forum-t'.$tpc['topic_id'].'-p'.ceil(($tpc['topic_nbreply']+1)/$_SESSION['cb_user']->usr_pref_msgs).','.rewrite_words($tpc['topic_name']).'.html#'.$tpc['msg_id']),
			'tg_t_lastreply_date' => dateFormat($tpc['msg_timestamp'],1,true),
			'tg_t_lastreply_userlink' => (($tpc['msg_userid'])?'<a href="'.manage_url('index.php?act=user&amp;showprofile='.$tpc['msg_userid'],'forum-m'.$tpc['msg_userid'].','.rewrite_words($tpc['last_usr_name']).'.html').'">'.$tpc['last_usr_name'].'</a>':'<span class="guest_name">'.($tpc['msg_guest']?$tpc['msg_guest']:lang('guest')).'</span>')
			);

		$first=false;
	}

	if ($group_data) {
		$group['tg_topics'] = $topics;
		$groups[] = $group;

		return $groups;
	} else return $topics;
}
/* Fonction qui renvoie les liens vers quelques pages internes au sujet. */
function getQuickPages($topic,$replies,$rname) {
	if (($replies+1)>$_SESSION['cb_user']->usr_pref_msgs) {
		$nbpages=ceil(($replies+1)/$_SESSION['cb_user']->usr_pref_msgs);
		switch($nbpages) {
			case 2:
				return onepage($topic,1,$rname).onepage($topic,2,$rname);
				break;
			case 3:
				return onepage($topic,1,$rname).onepage($topic,2,$rname).onepage($topic,3,$rname);
				break;
			default:
				return onepage('','...',$rname).onepage($topic,($nbpages-2),$rname).onepage($topic,($nbpages-1),$rname).onepage($topic,$nbpages,$rname);
				break;
		}
	} else return '';
}
/* Fonction qui renvoie un lien numéroté vers la page du topic demandé. */
function onepage($t,$p,$rname) {
	if ($p!='...') 
		return '<a href="'.manage_url('index.php?showtopic='.$t.'&amp;page='.$p,'forum-t'.$t.'-p'.$p.','.$rname.'.html').'" class="pagenum somepage">'.$p.'</a>';
	else return '<span class="pagenum pagedots">…</span>';
}
?>
