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
/* Pour la protection d'inclusion */
define('CB_TEMPLATE','TPL');

class template {
	var $lang = array();
	var $lang_loaded = array();
	var $config = array();
	var $css = array();
	var $template_dir = 'templates';
	var $_vars = array();

	function template () {
		$this->template_dir = (basename($_SERVER['PHP_SELF'])=='admin.php') ? CB_PATH.'admin/templates/' : CB_PATH.'templates/Official';
	}
	
	/* Assignation de variable */
	function assign($key, $value = null) {
		if (is_array($key)) {
			foreach($key as $var => $val)
				if ($var != '' && !is_numeric($var))
					$this->_vars[$var] = $val;
		} elseif ($key != '' && !is_numeric($key))
			$this->_vars[$key] = $value;
	}
	
	/* Assignation de variable par référence */
	function assign_ref($key, &$value) {
		if ($key != '' && !is_numeric($key))
			$this->_vars[$key] = &$value;
	}
	
	/* Affichage d'un template */
	function display($file) {
		if (isset($GLOBALS['cb_cfg']->config['gzip_output']) && $GLOBALS['cb_cfg']->config['gzip_output'] == 'yes') 
			ob_start('ob_gzhandler');
		
		$this->fetch($file,true);
	}
	
	/* Traitement d'un template */
	function fetch($file,$display = false) {
		if (utf8_substr($this->template_dir, -1) != DIRECTORY_SEPARATOR)
			$this->template_dir .= DIRECTORY_SEPARATOR;
		
		if (!file_exists($this->template_dir.$file))
			trigger_error('[TPL] File '.$file.' does not exist', E_USER_ERROR);
		
		extract($this->_vars);
		$template_path = $this->template_dir;
		
		if (!$display) {
			ob_start();
			include($this->template_dir.$file);
			$r = ob_get_contents();
			ob_end_clean();
			return $r;
		} else {
			include($this->template_dir.$file);
		}
	}

	/* Pour vérifier le jeu de templates à utiliser */
	function check_tpl () {
		$css_ln = 1;
		if (file_exists(CB_PATH.'skins/'.$_SESSION['cb_user']->getPreferredSkin().'/config.txt') && (basename($_SERVER['PHP_SELF']) !== 'admin.php')) {
			$config = file(CB_PATH.'skins/'.$_SESSION['cb_user']->getPreferredSkin().'/config.txt');

			if (is_dir(CB_PATH.'templates/'.trim($config[0])) && utf8_strpos(trim($config[0]),'/') === false && utf8_strlen(trim($config[0])) > 0) {
				$this->config['tpl_dir'] = trim($config[0]);
				$this->template_dir = CB_PATH.'templates/'.$this->config['tpl_dir'];
			}

			while (!empty($config[$css_ln])) {
				list($name,$csslink) = explode('|',$config[$css_ln]);
				$this->css[$name] = trim($csslink);
				++$css_ln;
			}
		}
		if ($css_ln == 1)
			$this->css['Normal'] = 'style.css';
	}

	/* Renvoie le code CSS associé à la skin utilisée */
	function get_css () {
		$csscode = '';
		foreach ($this->css as $name => $link) 
			$csscode.='<link rel="'.(empty($csscode)?'stylesheet':'alternate stylesheet').'" type="text/css" title="'.$name.'" href="skins/'.$_SESSION['cb_user']->getPreferredSkin().'/'.$link.'?'.filemtime('skins/'.$_SESSION['cb_user']->getPreferredSkin().'/'.$link).'" />'."\n";
		return $csscode;
	}

	/* Fonctions pour la gestion de la langue */
	/* ATTENTION !! Fonctions à n'utiliser qu'après création de l'objet utilisateur  */
	function lang_load ($file) {
		if (!$this->lang_loaded($file)) {
			require(CB_PATH.'lang/'.$_SESSION['cb_user']->getPreferredLang().'/'.$file);
			$this->lang_loaded[] = $file;
		}
	}

	/* Renvoie true si le fichier demandé a été chargé */
	function lang_loaded ($file) {
		return in_array($file,$this->lang_loaded);
	}
}
?>