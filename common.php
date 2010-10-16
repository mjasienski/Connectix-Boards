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
if (!defined('CB_INC')) exit('Incorrect access attempt !!');

/* Définition des constantes de chemin et de version */
define('CB_PATH',dirname(__FILE__).'/');
define('CB_VERSION', '0.8.4');

/** Constantes de 'débug' :  Laisser en commentaire pour un meilleur affichage. **/
/* Affichage des fichiers et lignes des notices et warnings. */
define('CB_DISPLAY_ERRPOS',1);
/* Affichage des requètes effectuées. */
define('CB_DISPLAY_QUERIES',1);

/* Définition de la constante de séparation des éléments de l'addressbar. */
define('CB_ADDR_SEP', '>');

/* Constantes liées au cache de fichiers */
define('CB_CACHE_CONFIG',	CB_PATH . 'data/config.cache.php');		// Fichier de cache pour la config
define('CB_CACHE_SMILEYS',	CB_PATH . 'data/smileys.cache.php');	// Fichier de cache pour les smileys
define('CB_CACHE_MODS',		CB_PATH . 'data/mods.cache.php');		// Fichier de cache pour les modérateurs
define('CB_CACHE_STRUCT',	CB_PATH . 'data/structure.cache.php');	// Fichier de cache pour la structure
define('CB_CACHE_CLASSES',	CB_PATH . 'data/classes.cache.php');	// Fichier de cache pour les groupes d'utilisateurs

define('CB_CACHE_STATS',	CB_PATH . 'data/stats.cache.php');	// Fichier de cache pour les groupes d'utilisateurs

/* Fonction de retour précis du timestamp. */
function microtime_float() {
   list($usec,$sec) = explode(' ',microtime());
   return ((float)$usec + (float)$sec);
}

/* Timer du script */
$GLOBALS['micro1'] = microtime_float();

/* Pour les & dans les adresses et quelques détails au niveau du html. */
@ini_set('arg_separator.output','&amp;');
@ini_set('url_rewriter.tags','a=href,area=href,frame=src,iframe=src,input=src');

/* Pour empêcher la création automatique des sessions */
@ini_set('session.auto_start','0');

/* On initialise la librairie MB_STRING en utf8, si elle existe */
if (PHP_EXTENSION_MBSTRING)
	mb_internal_encoding('UTF-8');

/* On impose l'utilisation de la librairie MB_STRING */
@ini_set('mbstring.func_overload','7');

/* Core de l'application */
require(CB_PATH.'include/lib/lib.cb.php');
require(CB_PATH.'include/core/class.mysql.php');
require(CB_PATH.'include/core/class.user.php');
require(CB_PATH.'include/core/class.config.php');
require(CB_PATH.'include/core/class.template.php');

/* Démarrage de session et vérification qu'une session parasite n'a pas été créée. */
ob_start();
session_start();
ob_end_clean();

$need_refresh = !isset($_SESSION['cb_directory_check']);
if (!$need_refresh && $_SESSION['cb_directory_check'] != dirname($_SERVER['PHP_SELF']))
	$need_refresh = true;

if ($need_refresh) {
	$_SESSION = array();
	session_destroy();
	ob_start();
	session_start();
	ob_end_clean();
}
$_SESSION['cb_directory_check'] = dirname($_SERVER['PHP_SELF']);

/* Empêche la mise en cache des pages... */
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

/* Encodage des fichiers */
header('Content-Type: text/html; charset=utf-8');

/* Si les magic_quotes sont activés, on traite les variables touchées. */
if (get_magic_quotes_gpc()) {
	$_POST = stripslashes_rec($_POST);
	$_COOKIE = stripslashes_rec($_COOKIE);
	$_GET = stripslashes_rec($_GET);
}

/* Initialisation du moteur de templates. */
$GLOBALS['cb_tpl'] = new template();

/* Gestion des erreurs */
$GLOBALS['cb_warn'] = array();
$GLOBALS['cb_ntc']  = array();
$GLOBALS['cb_tpl']->assign_ref('warning',$GLOBALS['cb_warn']);
$GLOBALS['cb_tpl']->assign_ref('notice',$GLOBALS['cb_ntc']);

if (!defined('E_STRICT')) define('E_STRICT',2048);

function errorHandler ($errno,$errstr,$errfile,$errline) {
	if (in_array($errno,array(E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR))) {
		$GLOBALS['cb_tpl']->assign(array(
			'm_title' => ((utf8_strpos($errstr,lang('paused')) !== false)?'Message':'Fatal Error'),
			'm_msg' => $errstr,
			'm_pos' => ((defined('CB_DISPLAY_ERRPOS'))?basename($errfile).' - line '.$errline:''),
			'm_css' => messageCss()
			));

		$GLOBALS['cb_tpl']->display('gen_message.php');
		exit();
	} elseif (in_array($errno,array(E_WARNING,E_CORE_WARNING,E_COMPILE_WARNING,E_USER_WARNING))) {
		$GLOBALS['cb_warn'][] = array(
			'str' => $errstr,
			'pos' => ((defined('CB_DISPLAY_ERRPOS'))?' ('.basename($errfile).' - line '.$errline.')':'')
			);
	} elseif ($errno!=E_STRICT) {
		$GLOBALS['cb_ntc'][] = array(
			'str' => $errstr,
			'pos' => ((defined('CB_DISPLAY_ERRPOS'))?' ('.basename($errfile).' - line '.$errline.')':'')
			);
	}
}
set_error_handler('errorHandler');
error_reporting(E_ALL);

/* Initialisation de certaines variables de session */
if (!isset($_SESSION['flood']))  $_SESSION['flood'] = 0;

/* Initialisation des objets. */
$GLOBALS['cb_db']  = new mysql();
$GLOBALS['cb_cfg'] = new config();

/* Chargement de la structure du forum */
require(CB_CACHE_STRUCT);

/* Si l'ip de l'utilisateur a été bannie... */
if (array_key_exists(ip2long($_SERVER['REMOTE_ADDR']),$GLOBALS['cb_cfg']->banned)) {
	if ($GLOBALS['cb_cfg']->banned[ip2long($_SERVER['REMOTE_ADDR'])] > time())
		trigger_error('Your ip has been banned from these forums. You can\'t connect here ! Please contact the administrators team.',E_USER_ERROR);
}

/* Chargement de l'objet utilisateur */
if (!isset($_SESSION['cb_user'])) 
	$_SESSION['cb_user'] = new user();
else 
	$_SESSION['cb_user']->newpage();

/* Chargement du fichier de langue de base */
$GLOBALS['cb_tpl']->lang_load('general.lang');
$GLOBALS['cb_tpl']->lang_load('errors.lang');

/* Vérification des templates à utiliser */
$GLOBALS['cb_tpl']->check_tpl();

/* Initialisation des variables de tracking des groupes de sujets lus */
$_SESSION['cb_unread_tgs'] = ((!empty($_SESSION['cb_unread_tgs']))?$_SESSION['cb_unread_tgs']:array());
$_SESSION['cb_read_in'] = ((!empty($_SESSION['cb_read_in']))?$_SESSION['cb_read_in']:array());
$_SESSION['cb_tgs_maxids'] = ((!empty($_SESSION['cb_tgs_maxids']))?$_SESSION['cb_tgs_maxids']:array());

?>