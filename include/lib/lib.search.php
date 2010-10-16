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

define ('SRC_MIN_LENGTH',3);
define ('SRC_MAX_LENGTH',50);

/**
 * Ajout ou suppression des mots dans la bdd, pour l'algorithme de recherche
 * $message Le bbcode a déja été parsé!
 */
function parseMessageSearch ( $message , $topicid , $msgid = 0 , $edit = false ) {
	// Si il s'agit d'une édition de message, on supprime les anciennes correspondances
	if ($edit)
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'src_matches WHERE sm_topicid='.$topicid.' AND sm_msgid='.$msgid);

	// Tableau des mots contenus dans le message
	$words = preg_split ('#[^\w]+#i', html_entity_decode(strip_tags($message),ENT_QUOTES));

	$words = array_filter ($words , '_filterWords'); // On supprime les mots trop courts ou trop longs
	$words = array_map ('_convertWord' , $words); 	 // On met les mots en minuscules
	$words = array_unique ($words); 				 // On supprime les doublons du tableau

	// Vérification qu'il y a des mots à insérer
	if (count($words) == 0) return true;

	// Tableau qui va contenir les ids des mots correspondants
	$words = array_combine ($words,array_fill(0,count($words),0));

	// On récupère les mots déja existants
	$r = $GLOBALS['cb_db']->query('SELECT sw_id,sw_word FROM '.$GLOBALS['cb_db']->prefix.'src_words WHERE sw_word IN (\''.implode('\',\'',array_keys($words)).'\')');
	while ($d=$GLOBALS['cb_db']->fetch_assoc($r)) { 
		if (in_array($d['sw_word'],array_keys($words))) 
			$words[$d['sw_word']] = $d['sw_id'];
	}

	// On crée la chaine des correspondances à insérer
	// On insère dans la bdd les mots qui n'y étaients pas
	$matches = array();
	foreach ($words as $word => $id) {
		if ($id == 0) {
			$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'src_words(sw_word) VALUES (\''.$word.'\')');
			$words[$word] = $GLOBALS['cb_db']->insert_id();
			$matches[] = '('.$words[$word].','.$msgid.','.$topicid.')';
		} else
			$matches[] = '('.$id.','.$msgid.','.$topicid.')';
	}

	// On insère dans la bdd les correspondances des mots
	if (count($matches))
		$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'src_matches(sm_wordid,sm_msgid,sm_topicid) VALUES '.implode(',',$matches));

	return true;
}
function _filterWords ($var) {
	$len = utf8_strlen($var);
	return ($len >= SRC_MIN_LENGTH && $len <= SRC_MAX_LENGTH);
}
function _convertWord ($wrd) {
	return strtr(utf8_strtolower($wrd), 'àáâäéèêëíìîïóòôöúùûüýÿç', 'aaaaeeeeiiiioooouuuuyyc');
}
?>