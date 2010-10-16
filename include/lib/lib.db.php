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

/** Fonctions relatives à la gestion de la base de données **/

/* Ecrit dans $fh le dump de la table $table_name, suivant l'ordre du champ $order et à partir de $begin */
function dump_table($fh , $table_name , $order , $begin = 0) {
	$block_size = 2000;
	$result = $GLOBALS['cb_db']->query('SELECT * FROM '.$GLOBALS['cb_db']->prefix.$table_name.' ORDER BY '.$order.' LIMIT '.$begin.','.$block_size);
	
	if ($begin == 0)
		gzwrite($fh,"\n".'--'."\n".'-- Connectix Boards :: Dump of table '.$table_name."\n".'--'."\n");
	
	$done = 0;
	while ($d = $GLOBALS['cb_db']->fetch_assoc($result)) {
		$fields = '';
		$values = '';

		foreach ($d as $key => $value) {
			if (!empty($fields)) $fields .= ',';
			$fields .= $key;
			if (!empty($values)) $values .= ',';
			$values .= '\''.str_replace(array("\n","\r","\t"),'',$GLOBALS['cb_db']->escape($value)).'\'';
		}
		gzwrite($fh,'INSERT INTO CB_TABLE_PREFIX'.$table_name.' VALUES('.$values.");\n");
		$done++;
	}
	$GLOBALS['cb_db']->free_result($result);
	
	return (($done == $block_size)?($begin+$done):true);
}
/* Exécute un fichier sql (gzip ou pas) formaté pour CB (CB_TABLE_PREFIX pour le préfixe des tables...). */
function execute_sqlfile($file_name) {
	$gz = null;
	if (function_exists('gzopen'))
		$gz = true;
	elseif (utf8_substr($file_name,-3) != '.gz')
		$gz = false;
	else trigger_error(lang('error_nozlib'),E_USER_ERROR);
	
	$fh = wopen($file_name,'r',$gz);
	if ($fh !== false) {
		$b = 0;
		while (!weof($fh,$gz))
			$b = execute_sqlpart($fh,$b);
		wclose($fh,$gz);
	}
}
/* Exécute une partie d'un fichier ($nb_instr instructions). $fr est la ressource du fichier, $b est l'offset à utiliser pour le démarrage. */
function execute_sqlpart ($fr,$b = 0,$nb_instr = 1000) {
	$instr = '';
	$nb_instr_done = 0;
	
	$gz = function_exists('gzopen');
	
	wseek($fr,$b,$gz);
	while (!weof($fr,$gz)){
		$sql_line = trim(wgets($fr,$gz));
		if ($sql_line != '' && utf8_strpos($sql_line, '--') !== 0)
			$instr .= ' '.$sql_line;
		
		if (utf8_substr($sql_line,-1) == ';') {
			$GLOBALS['cb_db']->query(str_replace('CB_TABLE_PREFIX',$GLOBALS['cb_db']->prefix,$instr));
			$instr='';
			$nb_instr_done++;
			
			if ($nb_instr_done >= $nb_instr) return ftell($fr);
		}
	}
}
/* Fonctions pour compatibilité gz et f */
function wopen($fn,$t,$gz = true) {
	return $gz ? gzopen($fn,$t) : fopen($fn,$t);
}
function wseek($fr,$nb,$gz = true) {
	return $gz ? gzseek($fr,$nb) : fseek($fr,$nb);
}
function wgets($fr,$gz = true) {
	return $gz ? gzgets($fr) : fgets($fr);
}
function weof($fr,$gz = true) {
	return $gz ? gzeof($fr) : feof($fr);
}
function wclose($fn,$gz = true) {
	return $gz ? gzclose($fn) : fclose($fn);
}
?>