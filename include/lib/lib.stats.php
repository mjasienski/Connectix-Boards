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

//// Fonctions de gestion des fichiers et dossiers ////

//Fonction vérifiant si l'utilisateur a les permissions suffisantes par rapport à la configuration pour voir les stats
function getStatsAuth($config)
{
	if($GLOBALS['cb_cfg']->config['stats_auth'] == 'admin')
	{
		if(!($_SESSION['cb_user']->isAdmin())) {
			return FALSE;
		}
		else return TRUE;
	}
	elseif($GLOBALS['cb_cfg']->config['stats_auth'] == 'mod')
	{
		if(!($_SESSION['cb_user']->isModerator())) {
			return FALSE;
		}
		else return TRUE;
	}
	elseif($GLOBALS['cb_cfg']->config['stats_auth'] == 'registered')
	{
		if(!($_SESSION['cb_user']->logged)) {
			return FALSE;
		}
		else return TRUE;
	}
	else return TRUE;
}

//Fonction calculant la longueur de la barre pour les stats, à partir de la valeur la plus élevée
function calculateBarLenght($arraytocheck1) {
	$max_lenght = 300;
	$max_value = max($arraytocheck1);
	$lenght[] = array();
	foreach($arraytocheck1 AS $key => $number)
	{
		$lenght[$key] = round(($max_lenght*$number)/$max_value);
	}
	return $lenght;
}
//Fonction calculant le pourcentage pour les stats, en partant du total des entrées recueillies
function calculateBarPercent($arraytocheck2)
{
	$total = array_sum($arraytocheck2);
	$maxres=0;
	$percent[] = array();
	foreach($arraytocheck2 AS $key => $number)
	{
		$percent[$key] = ($total > 0) ? round(($number/$total)*100) :0;
		if($percent[$key] > $maxres)
		{
			$maxres = $percent[$key];
		}
	}
	return $percent;
}

//Calcul des timestamps (min et max).  Pas le choix vu que les dates sont stockées en BIGINT
function minWeekTimestamp()
{
	$current_time = localTimestamp(time());
	$to_midnight = date('G',$current_time) * 3600 + date('i',$current_time) * 60 + date('s',$current_time);
	$to_monday = ((date('w',$current_time) + 6) % 7) * 24 * 3600;
	return $current_time - $to_midnight - $to_monday;
}
function maxWeekTimestamp()
{
	$current_time = localTimestamp(time());
	$to_midnight = date('G',$current_time) * 3600 + date('i',$current_time) * 60 + date('s',$current_time);
	$to_sunday = (7 - ((date('w',$current_time) + 6) % 7)) * 24 * 3600;
	return $current_time - $to_midnight + $to_sunday - 1;
}
function minMonthTimestamp()
{
	$current_time = localTimestamp(time());
	$to_midnight = date('G',$current_time) * 3600 + date('i',$current_time) * 60 + date('s',$current_time);
	$to_first = (date('j',$current_time) - 1) * 24 * 3600;
	return $current_time - $to_midnight - $to_first;
}
function maxMonthTimestamp()
{
	$current_time = localTimestamp(time());
	$to_midnight = date('G',$current_time) * 3600 + date('i',$current_time) * 60 + date('s',$current_time);
	$to_last = (date('t',$current_time) - date('j',$current_time) + 1) * 24 * 3600;
	return $current_time - $to_midnight + $to_last - 1;
}
?>
