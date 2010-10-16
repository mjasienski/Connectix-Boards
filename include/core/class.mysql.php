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
class mysql {
	var $sqlhost;
	var $sqlusername;
	var $sqlpassword;
	var $sqldatabase;
	var $queriescount = 0;
	var $connected;
	var $prefix;
	var $queriesdone = array();
	var $querytime = 0;
	var $sqlresource;
	
	/* Crée le gestionnaire MySQL, $fatalerror doit être mis à true pour qu'une tentative infructueuse génère une erreur de niveau E_USER_ERROR. */
	function mysql ( $fatalerror=true ) {
		if (!file_exists(CB_PATH.'data/settings.php')) redirect('install.php');

		require(CB_PATH.'data/settings.php');

		if ($this->sqlresource = @mysql_connect($this->sqlhost,$this->sqlusername,$this->sqlpassword)) {
			if (@mysql_select_db($this->sqldatabase,$this->sqlresource)) {
				$this->connected=true;
				$this->query('SET NAMES UTF8');
				
				return true;
			}
		}
		$this->connected=false;
		if ($fatalerror) $this->_error('Could not reach MySQL database.');
		return false;
	}
	
	/* Effectue une requète. */
	function query($query,$unbuffered=false) {
		if (defined('CB_DISPLAY_QUERIES')) $b = microtime_float();

		if ($unbuffered) $return=mysql_unbuffered_query($query,$this->sqlresource) or $this->_error($query);
		else $return=mysql_query($query,$this->sqlresource) or $this->_error($query);
		$this->queriescount++;

		if (defined('CB_DISPLAY_QUERIES')) {
			$this->queriesdone[] = array('time' => sprintf('%f',microtime_float() - $b),'query' => str_replace(array("\t",','),array('',', '),$query));
			$this->querytime += (microtime_float() - $b);
		}
		return $return;
	}
	
	/* Renvoie un résultat unique (pour des requètes ne retournant qu'une ligne d'un seul élément) */
	function single_result($query) {
		$r = $this->query($query);
		if ($this->num_rows($r) > 0)
			return @mysql_result($r,0);
		return false;
	}
	
	/* Renvoie un tableau associatif pour une requète renvoyant des lignes de deux éléments: la clé et la valeur */
	function &assoc_results($query) {
		$q = $this->query($query);
		$a = array();
		while ($r = $this->fetch_row($q)) 
			$a[$r[0]] = $r[1];
		return $a;
	}
	
	/* Gestion des erreurs mysql */
	function _error ($query) {
		$err ='A MySQL error occurred  ('.mysql_errno($this->sqlresource).').<br />';
		$err.='Server responded: '.mysql_error($this->sqlresource).'<br /><br />';
		$err.='The script was attempting to do the following action:'.'<br /><br /><span class="code">'.str_replace("\t",'',$query).'</span>';
		trigger_error($err,E_USER_ERROR);
	}
	
	/* Différentes fonction de 'fetch' */
	function fetch_array($data) {
		return mysql_fetch_array($data);
	}
	function fetch_assoc($data) {
		return mysql_fetch_assoc($data);
	}
	function fetch_row($data) {
		return mysql_fetch_row($data);
	}
	
	/* Dernier ID inséré */
	function insert_id() {
		return mysql_insert_id($this->sqlresource);
	}
	
	/* Ferme la connexion */
	function close() {
		return mysql_close($this->sqlresource);
	}
	
	/* Nombre de lignes du résultat $item */
	function num_rows($item) {
		return mysql_num_rows($item);
	}
	
	/* Nombre total de requètes effectuées */
	function gettotalqueries() {
		return $this->queriescount;
	}
	
	/* Détermine si l'objet est connecté au serveur MySQL ou pas */
	function isconnected() {
		return $this->connected;
	}
	
	/* Lignes affectées par le dernier UPDATE ou DELETE */
	function affected_rows () {
		return mysql_affected_rows($this->sqlresource);
	}
	
	/* Libère les ressources MySQL */
	function free_result($resource) {
		return mysql_free_result($resource);
	}
	
	/* Echappe une chaine de caractères */
	function escape ($str) {
		if (function_exists('mysql_real_escape_string'))
			return mysql_real_escape_string($str);
		else
			return mysql_escape_string($str);
	}
}
?>