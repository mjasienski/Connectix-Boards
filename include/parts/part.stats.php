<?php
/**
*	Connectix Boards 0.8, free interactive php bulletin boards.
*	Copyright (C) 2005-2007  Jasienski Martin.
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

require_once(CB_PATH.'include/lib/lib.stats.php');
//Vérification si l'utilisateur a les permissions suffisantes par rapport à la configuration
if(getStatsAuth($GLOBALS['cb_cfg']->config['stats_auth']) == FALSE)
{
	trigger_error(lang('error_permerror'),E_USER_ERROR);
}

$GLOBALS['cb_tpl']->lang_load('stats.lang');
$_SESSION['cb_user']->connected('index_stats');
$GLOBALS['cb_addressbar'][] = lang('stats');
$GLOBALS['cb_pagename'][] = lang('stats');

//On vérifie d'abord si le cache existe et s'il n'est pas périmé.  Dans le cas contraire, on effectue les requêtes et on met tout ça en cache
$cache = CB_CACHE_STATS;
$expire = time() - 3600 * 6;
	
//Vérification si le fichier de cache est à jour
if(file_exists($cache) && filemtime($cache) > $expire)
{
	require_once(CB_CACHE_STATS);
}
else
{
	//on va chercher les données nécessaires pour afficher les stats générales
	require_once(CB_CACHE_CLASSES);
	$GLOBALS['cb_cfg']->setStats();

	//Ouverture et âge du forum
	$regdate = $GLOBALS['cb_db']->single_result('SELECT usr_registertime FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_id=1');

	$opendate = dateFormat($regdate);
	$boardage = floor((time() - $regdate)/86400);
	$topicperday = round($GLOBALS['cb_cfg']->stats['total_topics'] / $boardage,2);
	$msgperday = round($GLOBALS['cb_cfg']->stats['total_messages'] / $boardage,2);
	$userperday = round($GLOBALS['cb_cfg']->stats['registered_users'] / $boardage,2);
	$now = time();
	$lastgentime = dateFormat($now);
	$nextgentime = dateFormat($now + (6*3600));

	//Dernier enregistré
	$lastuser = $GLOBALS['cb_db']->single_result('SELECT MAX(usr_id) FROM '.$GLOBALS['cb_db']->prefix.'users');
	$req1 = $GLOBALS['cb_db']->query('SELECT usr_name, usr_registertime
										FROM '.$GLOBALS['cb_db']->prefix.'users
										WHERE usr_id='.$lastuser);
	$infos = $GLOBALS['cb_db']->fetch_assoc($req1);
	$lastuserlink = getUserLink($lastuser['usr_id'],$infos['usr_name'],'');
	$lastusertime = dateFormat($infos['usr_registertime']);

	$list_stats = array('totalregistered' => $GLOBALS['cb_cfg']->stats['registered_users'], 'totalmsgs' => $GLOBALS['cb_cfg']->stats['total_messages'],'totaltopics' => $GLOBALS['cb_cfg']->stats['total_topics'],'opendate' => $opendate,'boardage' => $boardage,'topicperday' => $topicperday,'msgperday' => $msgperday,'userperday' => $userperday,'lastusertime' => $lastusertime,'lastuserlink' => $lastuserlink,'lastgentime' => $lastgentime,'nextgentime' => $nextgentime);
	
	//On passe maintenant aux statistiques détaillées.
	//Mais tout d'abord, on doit préparer notre variable de triage qui sera utilisée plusieurs fois.
	if(!($_SESSION['cb_user']->isAdmin()) && !empty($_SESSION['cb_user']->gr_auth_see))
	{
		//Le visiteur n'est pas admin et n'a pas accès à certaines sections
		//On exclut donc les sujets qui sont dans les sections qui lui sont interdites, en vue de la requête SQL
		$no_auth = "NOT IN (".implode(',',$_SESSION['cb_user']->gr_auth_see).")";
	}
	else
	{
		//Le visiteur est admin ou a accès à toutes les sections, donc rien à exclure
		$no_auth = '';
	}
	
	//On a notre variable de triage, on commence par les sujets les plus actifs
	$req2 = $GLOBALS['cb_db']->query('SELECT topic_id, topic_name, topic_nbreply 
									FROM '.$GLOBALS['cb_db']->prefix.'topics 
									'.($no_auth != '' ? 'WHERE topic_fromtopicgroup '.$no_auth : '').'
									ORDER BY topic_nbreply DESC LIMIT 0,10');
	
	$at_num=1;
	$at_replies = array();
	$at_topicid = array();
	$at_topicname = array();
	
	while($activet = $GLOBALS['cb_db']->fetch_assoc($req2))
	{
		$at_topicid[$at_num] = $activet['topic_id'];
		$at_replies[$at_num] = $activet['topic_nbreply'];
		$at_topicname[$at_num] = $activet['topic_name'];
		$at_num++;
	}
	
	//On passe maintenant aux sujets les plus vus
	$req3 = $GLOBALS['cb_db']->query('SELECT topic_id, topic_name, topic_views
									FROM '.$GLOBALS['cb_db']->prefix.'topics
									'.($no_auth != '' ? 'WHERE topic_fromtopicgroup '.$no_auth : '').'
									ORDER BY topic_views DESC LIMIT 0,10');
	
	$vt_num=1;
	$vt_views = array();
	$vt_topicid = array();
	$vt_topicname = array();
	
	while($viewt = $GLOBALS['cb_db']->fetch_assoc($req3))
	{
		$vt_topicid[$vt_num] = $viewt['topic_id'];
		$vt_views[$vt_num] = $viewt['topic_views'];
		$vt_topicname[$vt_num] = $viewt['topic_name'];
		$vt_num++;
	}

	//On y va maintenant avec les "power-topicstarters"
	$req4 = $GLOBALS['cb_db']->query('SELECT COUNT(topic_starter) AS nb_starters, usr_id, usr_name
									FROM '.$GLOBALS['cb_db']->prefix.'topics
									LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id=topic_starter
									GROUP BY topic_starter
									HAVING usr_id > 0
									ORDER BY nb_starters DESC
									LIMIT 0,10');
	
	$stu_num=1;
	$stu_nbstart = array();
	$stu_userid = array();
	$stu_username = array();
	
	while($startu = $GLOBALS['cb_db']->fetch_assoc($req4))
	{
		$stu_nbstart[$stu_num] = $startu['nb_starters'];
		$stu_userid[$stu_num] = $startu['usr_id'];
		$stu_username[$stu_num] = $startu['usr_name'];
		$stu_num++;
	}
	
	//Et maintenant, les posteurs les plus actifs de la semaine
	$req5 = $GLOBALS['cb_db']->query('SELECT COUNT(msg_userid) AS nb_posters, usr_id, usr_name, msg_timestamp
									FROM '.$GLOBALS['cb_db']->prefix.'messages
									LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id=msg_userid
									WHERE (msg_timestamp BETWEEN '.minWeekTimestamp().' AND '.maxWeekTimestamp().') AND msg_userid > 0
									GROUP BY msg_userid
									ORDER BY nb_posters DESC
									LIMIT 0,10');
	$wku_num=1;
	while($weeku = $GLOBALS['cb_db']->fetch_assoc($req5))
	{
		$wku_nbpost[$wku_num] = $weeku['nb_posters'];
		$wku_userid[$wku_num] = $weeku['usr_id'];
		$wku_username[$wku_num] = $weeku['usr_name'];
		$wku_num++;
	}
	if(empty($wku_nbpost))
	{
		$wku_nbpost = array();
		$wku_userid = array();
		$wku_username = array();
	}
	
	//Puis les posteurs les plus actifs du mois
	$req6 = $GLOBALS['cb_db']->query('SELECT COUNT(msg_userid) AS nb_posters, usr_id, usr_name, msg_timestamp
									FROM '.$GLOBALS['cb_db']->prefix.'messages
									LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id=msg_userid
									WHERE (msg_timestamp BETWEEN '.minMonthTimestamp().' AND '.maxMonthTimestamp().') AND msg_userid > 0
									GROUP BY msg_userid
									ORDER BY nb_posters DESC
									LIMIT 0,10');
	$mtu_num=1;
	$mtu_nbpost = array();
	$mtu_userid = array();
	$mtu_username = array();
	
	while($monthu = $GLOBALS['cb_db']->fetch_assoc($req6))
	{
		$mtu_nbpost[$mtu_num] = $monthu['nb_posters'];
		$mtu_userid[$mtu_num] = $monthu['usr_id'];
		$mtu_username[$mtu_num] = $monthu['usr_name'];
		$mtu_num++;
	}

	//Meilleurs posteurs
	$req7 = $GLOBALS['cb_db']->query('SELECT COUNT(msg_userid) AS nb_posters, usr_id, usr_name
									FROM '.$GLOBALS['cb_db']->prefix.'messages
									LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id=msg_userid
									GROUP BY msg_userid
									HAVING usr_id > 0
									ORDER BY nb_posters DESC
									LIMIT 0,10');
	$bpu_num=1;
	$bpu_nbpost = array();
	$bpu_userid = array();
	$bpu_username = array();
	
	while($bestpostu = $GLOBALS['cb_db']->fetch_assoc($req7))
	{
		$bpu_nbpost[$bpu_num] = $bestpostu['nb_posters'];
		$bpu_userid[$bpu_num] = $bestpostu['usr_id'];
		$bpu_username[$bpu_num] = $bestpostu['usr_name'];
		$bpu_num++;
	}

	//Nouveaux sujets au cours des 12 derniers mois
	$cur_month = date('n',$now);
	$cur_year = date('Y',$now) - 1;
	
	$twmonths = array();
	$twentries = array();
	
	for ($i = 1; $i <= 12; $i++)
	{
		++$cur_month;
		if ($cur_month > 12)
		{
			$cur_month -= 12;
			++$cur_year;
		}
		
		$next_month = $cur_month + 1;
		$next_year = $cur_year;
		if ($next_month > 12)
		{
			$next_month -= 12;
			++$next_year;
		}
		
		$twmonths[] = lang('st_'.strtolower(date('M', mktime(0,0,0,$cur_month,1,$cur_year))).'_short').'&nbsp;'.$cur_year;
		$twentries[] = $GLOBALS['cb_db']->single_result('SELECT COUNT(msg_id)
									FROM '.$GLOBALS['cb_db']->prefix.'messages
									WHERE (msg_timestamp BETWEEN 
										'.mktime(0,0,0,$cur_month,1,$cur_year).' 
										AND '.(mktime(0,0,0,$next_month,1,$next_year) - 1).')');
	}

	//Notre pâte à gâteau est prête, on verse dans des moules et on enfourne le tout à 350°F pendant 35 minutes.
	file_put_contents(CB_CACHE_STATS,'<?php '."\n".
							'$GLOBALS[\'cb_stats\'] = unserialize(\''.str_replace("'","\\'",serialize($list_stats)).'\');'."\n".
							'$GLOBALS[\'cb_atopics_replies\'] = unserialize(\''.str_replace("'","\\'",serialize($at_replies)).'\');'."\n".
							'$GLOBALS[\'cb_atopics_topicid\'] = unserialize(\''.str_replace("'","\\'",serialize($at_topicid)).'\');'."\n".
							'$GLOBALS[\'cb_atopics_topicname\'] = unserialize(\''.str_replace("'","\\'",serialize($at_topicname)).'\');'."\n".
							'$GLOBALS[\'cb_vtopics_views\'] = unserialize(\''.str_replace("'","\\'",serialize($vt_views)).'\');'."\n".
							'$GLOBALS[\'cb_vtopics_topicid\'] = unserialize(\''.str_replace("'","\\'",serialize($vt_topicid)).'\');'."\n".
							'$GLOBALS[\'cb_vtopics_topicname\'] = unserialize(\''.str_replace("'","\\'",serialize($vt_topicname)).'\');'."\n".
							'$GLOBALS[\'cb_stusers_nbstart\'] = unserialize(\''.str_replace("'","\\'",serialize($stu_nbstart)).'\');'."\n".
							'$GLOBALS[\'cb_stusers_userid\'] = unserialize(\''.str_replace("'","\\'",serialize($stu_userid)).'\');'."\n".
							'$GLOBALS[\'cb_stusers_username\'] = unserialize(\''.str_replace("'","\\'",serialize($stu_username)).'\');'."\n".
							'$GLOBALS[\'cb_wkusers_nbpost\'] = unserialize(\''.str_replace("'","\\'",serialize($wku_nbpost)).'\');'."\n".
							'$GLOBALS[\'cb_wkusers_userid\'] = unserialize(\''.str_replace("'","\\'",serialize($wku_userid)).'\');'."\n".
							'$GLOBALS[\'cb_wkusers_username\'] = unserialize(\''.str_replace("'","\\'",serialize($wku_username)).'\');'."\n".
							'$GLOBALS[\'cb_mtusers_nbpost\'] = unserialize(\''.str_replace("'","\\'",serialize($mtu_nbpost)).'\');'."\n".
							'$GLOBALS[\'cb_mtusers_userid\'] = unserialize(\''.str_replace("'","\\'",serialize($mtu_userid)).'\');'."\n".
							'$GLOBALS[\'cb_mtusers_username\'] = unserialize(\''.str_replace("'","\\'",serialize($mtu_username)).'\');'."\n".
							'$GLOBALS[\'cb_bpusers_nbpost\'] = unserialize(\''.str_replace("'","\\'",serialize($bpu_nbpost)).'\');'."\n".
							'$GLOBALS[\'cb_bpusers_userid\'] = unserialize(\''.str_replace("'","\\'",serialize($bpu_userid)).'\');'."\n".
							'$GLOBALS[\'cb_bpusers_username\'] = unserialize(\''.str_replace("'","\\'",serialize($bpu_username)).'\');'."\n".
							'$GLOBALS[\'cb_twtopics_months\'] = unserialize(\''.str_replace("'","\\'",serialize($twmonths)).'\');'."\n".
							'$GLOBALS[\'cb_twtopics_number\'] = unserialize(\''.str_replace("'","\\'",serialize($twentries)).'\');'."\n".
						'?>');

	//On affiche maintenant les données fraîchement mises en cache
	require_once(CB_CACHE_STATS);
}

//On associe le tout aux templates

/*Stats générales*/
$GLOBALS['cb_tpl']->assign(array(
		'st_totalusers' => $GLOBALS['cb_stats']['totalregistered'],
		'st_totalmsgs' => $GLOBALS['cb_stats']['totalmsgs'],
		'st_totaltopics' => $GLOBALS['cb_stats']['totaltopics'],
		'st_opendate' => $GLOBALS['cb_stats']['opendate'],
		'st_boardage' => $GLOBALS['cb_stats']['boardage'],
		'st_topicperday' => $GLOBALS['cb_stats']['topicperday'],
		'st_msgperday' => $GLOBALS['cb_stats']['msgperday'],
		'st_userperday' => $GLOBALS['cb_stats']['userperday'],
		'st_lastregdate' => $GLOBALS['cb_stats']['lastusertime'],
		'st_lastregistered' => $GLOBALS['cb_stats']['lastuserlink'],
		'st_lastgentime' => $GLOBALS['cb_stats']['lastgentime'],
		'st_nextgentime' => $GLOBALS['cb_stats']['nextgentime']
));

//Array des rangs.  Celui-ci sera réutilisé
/*Sujets les plus actifs*/
if(!empty($GLOBALS['cb_atopics_replies']))
{
	$activet_rank = array_keys($GLOBALS['cb_atopics_replies']);
	$activet_replies = $GLOBALS['cb_atopics_replies'];
	$activet_topicid = $GLOBALS['cb_atopics_topicid'];
	$activet_topicname = $GLOBALS['cb_atopics_topicname'];
}
else
{
	$activet_rank = '';
	$activet_replies = '';
	$activet_topicid = '';
	$activet_topicname = '';
}
$GLOBALS['cb_tpl']->assign(array(
	'at_t_rank' => $activet_rank,
	'at_t_replies' => $activet_replies,
	'at_t_topicid' => $activet_topicid,
	'at_t_topicname' => $activet_topicname
));
/*Sujets les plus vus*/
if(!empty($GLOBALS['cb_vtopics_views']))
{
	$viewt_rank = array_keys($GLOBALS['cb_vtopics_views']);
	$viewt_views = $GLOBALS['cb_vtopics_views'];
	$viewt_topicid = $GLOBALS['cb_vtopics_topicid'];
	$viewt_topicname = $GLOBALS['cb_vtopics_topicname'];
}
else
{
	$viewt_rank = '';
	$viewt_views = '';
	$viewt_topicid = '';
	$viewt_topicname = '';
}
$GLOBALS['cb_tpl']->assign(array(
	'vt_t_rank' => $viewt_rank,
	'vt_t_views' => $viewt_views,
	'vt_t_topicid' => $viewt_topicid,
	'vt_t_topicname' => $viewt_topicname
));
/*Utilisateurs commençant le plus de sujets*/
if(!empty($GLOBALS['cb_stusers_userid']))
{
	$startu_rank = array_keys($GLOBALS['cb_stusers_userid']);
	$startu_nbstart = $GLOBALS['cb_stusers_nbstart'];
	$startu_userid = $GLOBALS['cb_stusers_userid'];
	$startu_username = $GLOBALS['cb_stusers_username'];
	$startu_bar = calculateBarLenght($startu_nbstart);
	$startu_percent = calculateBarPercent($startu_nbstart);
}
else
{
	$startu_rank = '';
	$startu_nbstart = '';
	$startu_userid = '';
	$startu_username = '';
	$startu_bar = '';
	$startu_percent = '';
}
$GLOBALS['cb_tpl']->assign(array(
	'stu_u_rank' => $startu_rank,
	'stu_u_nbstart' => $startu_nbstart,
	'stu_u_userid' => $startu_userid,
	'stu_u_username' => $startu_username,
	'stu_u_bar' => $startu_bar,
	'stu_u_percent' => $startu_percent
));
/*Utilisateurs les plus actifs de la semaine*/
if(!empty($GLOBALS['cb_wkusers_userid']))
{
	$weeku_rank = array_keys($GLOBALS['cb_wkusers_userid']);
	$weeku_nbpost = $GLOBALS['cb_wkusers_nbpost'];
	$weeku_userid = $GLOBALS['cb_wkusers_userid'];
	$weeku_username = $GLOBALS['cb_wkusers_username'];
	$weeku_bar = calculateBarLenght($weeku_nbpost);
	$weeku_percent = calculateBarPercent($weeku_nbpost);
}
else
{
	$weeku_rank = '';
	$weeku_nbpost = '';
	$weeku_userid = '';
	$weeku_username = '';
	$weeku_bar = '';
	$weeku_percent = '';
}
$weeku_day = array('Monday' => lang('st_monday'), 'Sunday' => lang('st_sunday'), 'Saturday' => lang('st_saturday'));
$weeku_firstdayname = $weeku_day[date('l',minWeekTimestamp())];
$weeku_firstdaynumber = date('j',minWeekTimestamp());
$weeku_firstday = $weeku_firstdayname.'&nbsp;'.$weeku_firstdaynumber;
$weeku_lastdayname = $weeku_day[date('l',maxWeekTimestamp())]; 
$weeku_lastdaynumber = date('j',maxWeekTimestamp());
$weeku_lastday = $weeku_lastdayname.'&nbsp;'.$weeku_lastdaynumber;
$GLOBALS['cb_tpl']->assign(array(
	'wku_u_rank' => $weeku_rank,
	'wku_u_nbpost' => $weeku_nbpost,
	'wku_u_userid' => $weeku_userid,
	'wku_u_username' => $weeku_username,
	'wku_u_bar' => $weeku_bar,
	'wku_u_percent' => $weeku_percent,
	'wku_u_firstday' => $weeku_firstday,
	'wku_u_lastday' => $weeku_lastday
));
//Utilisateurs les plus actifs du mois
if(!empty($GLOBALS['cb_mtusers_userid']))
{
	$monthu_rank = array_keys($GLOBALS['cb_mtusers_userid']);
	$monthu_nbpost = $GLOBALS['cb_mtusers_nbpost'];
	$monthu_userid = $GLOBALS['cb_mtusers_userid'];
	$monthu_username = $GLOBALS['cb_mtusers_username'];
	$monthu_bar = calculateBarLenght($monthu_nbpost);
	$monthu_percent = calculateBarPercent($monthu_nbpost);
}
else
{
	$monthu_rank = '';
	$monthu_nbpost = '';
	$monthu_userid = '';
	$monthu_username = '';
	$monthu_bar = '';
	$monthu_percent = '';
}
$monthu_currenttime = time();
$monthu_month = array('January' => lang('st_jan'), 'February' => lang('st_feb'), 'March' => lang('st_mar'), 'April' => lang('st_apr'), 'May' => lang('st_may'), 'June' => lang('st_jun'), 'July' => lang('st_jul'), 'August' => lang('st_aug'), 'September' => lang('st_sep'), 'October' => lang('st_oct'), 'November' => lang('st_nov'), 'December' => lang('st_dec'));
$monthu_monthname = $monthu_month[date('F',$monthu_currenttime)];
$monthu_year = date('Y',$monthu_currenttime);
$monthu_currentmonth = $monthu_monthname.'&nbsp;'.$monthu_year;
$GLOBALS['cb_tpl']->assign(array(
	'mtu_u_rank' => $monthu_rank,
	'mtu_u_nbpost' => $monthu_nbpost,
	'mtu_u_userid' => $monthu_userid,
	'mtu_u_username' => $monthu_username,
	'mtu_u_bar' => $monthu_bar,
	'mtu_u_percent' => $monthu_percent,
	'mtu_u_month' => $monthu_currentmonth
));
//Meilleurs posteurs
if(!empty($GLOBALS['cb_bpusers_userid']))
{
	$bestpostu_rank = array_keys($GLOBALS['cb_bpusers_userid']);
	$bestpostu_nbpost = $GLOBALS['cb_bpusers_nbpost'];
	$bestpostu_userid = $GLOBALS['cb_bpusers_userid'];
	$bestpostu_username = $GLOBALS['cb_bpusers_username'];
	$bestpostu_bar = calculateBarLenght($bestpostu_nbpost);
	$bestpostu_percent = calculateBarPercent($bestpostu_nbpost);
}
else
{
	$bestpostu_rank = '';
	$bestpostu_nbpost = '';
	$bestpostu_userid = '';
	$bestpostu_username = '';
	$bestpostu_bar = '';
	$bestpostu_percent = '';
}
$GLOBALS['cb_tpl']->assign(array(
	'bpu_u_rank' => $bestpostu_rank,
	'bpu_u_nbpost' => $bestpostu_nbpost,
	'bpu_u_userid' => $bestpostu_userid,
	'bpu_u_username' => $bestpostu_username,
	'bpu_u_bar' => $bestpostu_bar,
	'bpu_u_percent' => $bestpostu_percent
));

//Topics postés au cours des 12 derniers mois
$topics_rank = array_keys($GLOBALS['cb_twtopics_months']);
$topics_months = $GLOBALS['cb_twtopics_months'];
$topics_number = $GLOBALS['cb_twtopics_number'];
$topics_bar = calculateBarLenght($topics_number);
$topics_percent = calculateBarPercent($topics_number);
$GLOBALS['cb_tpl']->assign(array(
	'twt_t_rank' => $topics_rank,
	'twt_t_months' => $topics_months,
	'twt_t_number' => $topics_number,
	'twt_t_bar' => $topics_bar,
	'twt_t_percent' => $topics_percent
));

$GLOBALS['cb_tpl']->assign('g_part','part_stats.php');
?>
