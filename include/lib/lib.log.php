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

//// Fonctions de gestion du log ////

/* Définition des constantes des différents types de reports dans le log. */
define('LOG_READONLY' 				, 1);	// Mise en lecture seule d'un membre
define('LOG_BAN' 					, 2);	// Bannissement d'un membre
define('LOG_CANCELPUNISH'			, 3);	// Bannissement d'un membre
define('LOG_DELETEMESS' 			, 4);	// Suppression d'un message
define('LOG_DELETETOPIC' 			, 5);	// Suppression d'un sujet
define('LOG_CLOSETOPIC'		 		, 6);	// Fermeture d'un sujet
define('LOG_OPENTOPIC' 				, 7);	// Ouverture d'un sujet
define('LOG_PINTOPIC' 				, 8);	// Mise d'un sujet en épinglé
define('LOG_UNPINTOPIC' 			, 9);	// 'Désépinglage' d'un sujet
define('LOG_UNANNOUNCETOPIC'	 	, 10);	// 'Désannoncage' d'un sujet
define('LOG_DISPLACETOPIC' 			, 11);	// Déplacement d'un sujet
define('LOG_MANAGEREPORT' 			, 12);	// Traitement d'un message signalé
define('LOG_CHANGETOPICTITLE'		, 13);	// Changement du titre d'un sujet
define('LOG_EDITPROFILE_GENERAL'	, 14);	// Modification des données personnelles
define('LOG_EDITPROFILE_AVATAR'		, 15);	// Modification de l'avatar
define('LOG_EDITPROFILE_SIGNATURE'	, 16);	// Modification de la signature
define('LOG_ADDNOTE'				, 17);	// Ajout d'une note sur un membre
define('LOG_REPUTATION'				, 18);	// Modification de la réputation d'un membre

$GLOBALS['cb_log_values'] = array(
	LOG_READONLY => 'log_readonly',
	LOG_BAN => 'log_ban',
	LOG_CANCELPUNISH => 'log_cancelpunish',
	LOG_DELETEMESS => 'log_deletemess',
	LOG_DELETETOPIC => 'log_deletetopic',
	LOG_CLOSETOPIC => 'log_closetopic',
	LOG_OPENTOPIC => 'log_opentopic',
	LOG_PINTOPIC => 'log_pintopic',
	LOG_UNPINTOPIC => 'log_unpintopic',
	LOG_UNANNOUNCETOPIC => 'log_unannouncetopic',
	LOG_DISPLACETOPIC => 'log_displacetopic',
	LOG_MANAGEREPORT => 'log_managereport',
	LOG_CHANGETOPICTITLE => 'log_changetopictitle',
	LOG_EDITPROFILE_GENERAL => 'log_editprofile_general',
	LOG_EDITPROFILE_AVATAR => 'log_editprofile_avatar',
	LOG_EDITPROFILE_SIGNATURE => 'log_editprofile_signature',
	LOG_ADDNOTE => 'log_addnote',
	LOG_REPUTATION => 'log_reputation'
	);


/* Fonction qui ajoute un élément de log. */
function addLog ( $log_type,$log_rep_user,$log_rep_topic,$log_rep_msg,$log_param = 0 ) {
	$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'log (log_type,log_usermake,log_timestamp,log_rep_user,log_rep_topic,log_rep_msg,log_param) VALUES('.$log_type.','.$_SESSION['cb_user']->userid.','.time().','.(int)$log_rep_user.','.(int)$log_rep_topic.','.(int)$log_rep_msg.','.(int)$log_param.')');
}
/* Fonction qui renvoie la description d'un type donné. */
function getLogDesc ($log_type) {
	$GLOBALS['cb_tpl']->lang_load('log.lang');
	if (isset($GLOBALS['cb_log_values'][$log_type]))
		return $GLOBALS['cb_log_values'][$log_type];
	else return false;
}
/* Fonction qui renvoie un menu de choix entre les différents types de report. */
function chooseMenuLog ($name,$selected,$width=220) {
	$GLOBALS['cb_tpl']->lang_load('log.lang');
	$items = array(array('name' => '','selected' => false,'value' => '','lang' => 'log_choosetype'));
	foreach ($GLOBALS['cb_log_values'] as $log_id => $log_desc)
		$items[] = array('name' => $log_id, 'selected' => ($selected == $log_id),'value' => '', 'lang' => $log_desc);
	$GLOBALS['cb_tpl']->assign('list',array ( 'name' => $name, 'style' => $width, 'items' => $items ));
	return $GLOBALS['cb_tpl']->fetch('menu_list.php');
}
?>