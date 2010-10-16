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

class config {
	/** Tableaux de données **/
	var $config;		// Tableau qui contient les variables de configuration
	var $stats;			// Tableau qui contient les statistiques
	var $banned;		// Contient les ip bannies

	/* Chargement de la configuration */
	function config () {
		if (!file_exists(CB_CACHE_CONFIG)) 
			$this->cacheConfig();
		else 
			require(CB_CACHE_CONFIG);
	}
	/* Chargement des statistiques */
	function setStats () {
		$this->stats = $GLOBALS['cb_db']->assoc_results('SELECT st_field,st_value FROM '.$GLOBALS['cb_db']->prefix.'stats');
	}
	/* Chargement de la configuration */
	function resetConfig () {
		$this->config = $GLOBALS['cb_db']->assoc_results('SELECT cf_field,cf_value FROM '.$GLOBALS['cb_db']->prefix.'config');
		$this->banned = $GLOBALS['cb_db']->assoc_results('SELECT ban_ip,ban_expires FROM '.$GLOBALS['cb_db']->prefix.'banned');
	}
	/* Mise en cache de la configuration */
	function cacheConfig () {
		$this->resetConfig ();
		$this->config['last-cached'] = time();
		
		file_put_contents(CB_CACHE_CONFIG,'<?php'."\n".
			'if (!defined(\'CB_INC\')) exit(\'Access denied!\');'.
			'$this->config = unserialize(\''.str_replace("'","\\'",serialize($this->config)).'\');'."\n".
			'$this->banned = unserialize(\''.str_replace("'","\\'",serialize($this->banned)).'\');'."\n".'?>');
	}
	/* Ajoute ou modifie des éléments dans la config ($elts est un tableau associatif). */
	function updateElements ( $elts ) {
		$values = '';
		foreach ($elts as $key => $value) {
			$values.=((empty($values))?'':',').'(\''.$key.'\',\''.$value.'\')';
		}
		$GLOBALS['cb_db']->query('REPLACE INTO '.$GLOBALS['cb_db']->prefix.'config(cf_field,cf_value) VALUES '.$values);
		$this->cacheConfig();
	}
	/* Ajoute ou modifie un ban sur une ip  */
	function banIp ($ip,$expires) {
		if (false !== $GLOBALS['cb_db']->single_result('SELECT 1 FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_userid=1 AND msg_userip='.ip2long($ip).' LIMIT 1')) {
			trigger_error(lang('pa_ip_ban_error_adminip'),E_USER_WARNING);
			return false;
		}
		$GLOBALS['cb_db']->query('REPLACE INTO '.$GLOBALS['cb_db']->prefix.'banned(ban_ip,ban_expires) VALUES ('.ip2long($ip).','.$expires.')');
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'banned WHERE ban_expires<'.time());
		$this->cacheConfig();
		return true;
	}
	/* Supprime un ban sur une ip */
	function removeBan ($ip) {
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'banned WHERE ban_ip='.ip2long($ip).' OR ban_expires<'.time());
		$this->cacheConfig();
	}
}
?>