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

/* Définition de la constante d'include. */
define('CB_INC', 'CB');
define('CB_ADMIN', 'CB');

define('CUR_VERSION','0.8.2');
define('CB_SKIN','Zephyr');
define('CB_LANG','fr');

define('CB_PATH',dirname(__FILE__).'/');

$GLOBALS['to_upgrade'] = array(
	'0.3',
	'0.4','0.4.1','0.4.2','0.4.3','0.4.4',
	'0.5','0.5.1','0.5.2','0.5.3','0.5.4','0.5.5',
	'0.6','0.6.1',
	'0.7','0.7.1',
	'0.8','0.8.1');

$GLOBALS['to_migrate'] = array(
	'coolforum' => 'CoolForum 0.8.X',
	'ipb1_3' => 'Invision Power Board 1.3',
	'ipb2' => 'Invision Power Board 2.X',
	'phpbb2' => 'PhpBB 2.X',
	'punbb' => 'PunBB 1.2.X');
$GLOBALS['to_migrate_prefix'] = array('coolforum' => 'cf_', 'ipb1_3' => 'ipb_', 'ipb2' => 'ipb_', 'phpbb2' => 'phpbb_', 'punbb' => 'punbb_');

$GLOBALS['tables_used'] = array('forums','topicgroups','topics','messages','mp','modnotes','users','connected','config','stats','automessages','groups','modnotes','pollpossibilities','polls','reports','banned','smileys','log','usertopics','usertgs','src_words','src_matches');

/* Pour les & dans les adresses. */
@ini_set('arg_separator.output','&amp;');
@ini_set('url_rewriter.tags','a=href,area=href,frame=src,iframe=src,input=src');

/* Pour que les choix des formulaires soient conservés. */
header("Cache-control: private");

/* Encodage */
header('Content-Type: text/html; charset=UTF-8');

/* Démarrage de session  */
ob_start();
session_start();
ob_end_clean();

require('include/lib/lib.cb.php');
require('include/lib/lib.db.php');
require('include/lib/lib.admin.php');
require('include/lib/lib.users.php');
require('install/installsteps.php');
require('include/core/class.mysql.php');
require('include/core/class.config.php');
require('include/core/class.template.php');
require('include/lib/class.smileysmanager.php');

/* Si les magic_quotes sont activés, on traite les variables touchées. */
if (get_magic_quotes_gpc()) {
	$_POST = stripslashes_rec($_POST);
	$_COOKIE = stripslashes_rec($_COOKIE);
	$_GET = stripslashes_rec($_GET);
}

if (!isset($_SESSION['params']['todo'])) {
	$_SESSION = array();
	session_destroy();
	session_start();
}

/* Initialisation des paramètres d'installation */
if (!isset($_SESSION['params']))
	$_SESSION['params']=array();
if (!isset($_SESSION['params']['todo']))
	$_SESSION['params']['todo'] = array();
if (!isset($_SESSION['params']['head']))
	$_SESSION['params']['head']='Installation de Connectix Boards';

$form = array();
$errors = array();
$notices = array();

$GLOBALS['skip'] = false;

/* Vérification que l'installation n'a pas déja été faite ou s'il faut mettre à jour. */
if (count($_SESSION['params']['todo']) == 0) {
	if (file_exists('data/settings.php')) {
		$cb_db = new mysql (false);
		if ($cb_db->isconnected()) {
			global $cb_db;
			$cf = new config();

			if (!isset($cf->config['forumversion']) || (isset($cf->config['forumversion']) && in_array($cf->config['forumversion'],$GLOBALS['to_upgrade']))) {
				$cf->updateElements(array('paused' => 'yes','pausemessage' => 'Maintenance du forum en cours...'));
				$_SESSION['params']['todo']    = array(12,11,5,13);
				$_SESSION['params']['current'] = (isset($cf->config['forumversion']))?$cf->config['forumversion']:$GLOBALS['to_upgrade'][0];
				redirect('install.php');
			}
		}
		$errors[] = 'Accès incorrect au fichier d\'installation. Le forum est déja installé. La version que vous voulez mettre à jour n\'est pas valide ou est déja à jour.<br />Rendez-vous sur <a href="http://www.connectix-boards.org">le site officiel</a> pour plus d\'informations.';
	} else $_SESSION['params']['todo'] = array(0);
}

if (count($_SESSION['params']['todo']) > 0)
	$_SESSION['params']['todo'] = installSteps ($_SESSION['params']['todo'],$form,$errors,$notices);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >
<head>
	<title><?php echo $_SESSION['params']['head']; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php if ($GLOBALS['skip']): ?>
	<meta http-equiv="refresh" content="1; url=install.php">
	<?php endif; ?>
	<link rel="stylesheet" media="screen" type="text/css" title="Design" href="install/installskin/style.css" />
</head>
<body>
	<div id="template">
		<div id="main">
			<h1 id="header">
				<a href="index.php" id="headerlink"><span>Connectix Boards</span></a>
			</h1>
			<?php if (count($errors) > 0): ?>
			<div class="warning">
			<?php foreach($errors as $e): ?>
				<p><?php echo $e; ?></p>
			<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if (count($notices) > 0): ?>
			<div class="notice">
			<?php foreach($notices as $n): ?>
				<p><?php echo $n; ?></p>
			<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if (count($form) > 0): ?>
			<form action="install.php" method="post">
				<div class="container">
					<h2><span class="title_pre"><span>&nbsp;>&nbsp;&nbsp;</span></span><?php echo $_SESSION['params']['head']; ?></h2>
					<table class="table" border="0" cellspacing="1" cellpadding="4">
						<?php foreach($form as $f): ?>
						<tr>
							<td colspan="2" class="f_title"><?php echo $f['title']; ?></td>
						</tr>
						<?php foreach($f['elements'] as $e): ?>
						<tr>
							<td class="f_text"><?php echo $e[0]; ?></td>
							<td class="f_input"><?php echo $e[1]; ?></td>
						</tr>
						<?php endforeach; ?>
						<?php endforeach; ?>
					</table>
					<div class="confirm">
						<input type="submit" name="submit" value="Confirmer" />
					</div>
				</div>
			</form>
			<?php endif; ?>
			<div id="footer">
				<p id="copyright">
					Powered by <a href="http://www.connectix-boards.org">Connectix Boards</a> <?php echo CUR_VERSION; ?> &copy; 2005-<?php echo date('Y'); ?>
				</p>
			</div>
		</div>
	</div>
</body>
</html>
