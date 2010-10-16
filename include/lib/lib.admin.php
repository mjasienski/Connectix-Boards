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

//// Fonctions de gestion des fichiers de cache ////

/* Fonction qui met en cache la structure du forum (ce dont on a besoin en tout cas). */
function cacheStructure () {
	$forums_p = array();
	$topicgroups_ff = array();
	$topicgroups_ftg = array();
	$topicgroups_p = array();
	$topicgroups_l = array();
	$topicgroups_unvis = array();
	$fnames = array();
	$tgnames = array();
	$tgcomments = array();
	$pre='';
	
	$fnames = $GLOBALS['cb_db']->assoc_results('SELECT forum_id,forum_name
		FROM '.$GLOBALS['cb_db']->prefix.'forums
		ORDER BY forum_order,forum_id');

	$tgQuery=$GLOBALS['cb_db']->query('SELECT tg.tg_id AS tgid,tg.tg_name AS tgname,tg_comment AS tgcomment,tg.tg_fromforum AS tgff,tg.tg_fromtopicgroup AS tgftg,tg.tg_visibility AS tgvis,tg.tg_link AS tglink
		FROM '.$GLOBALS['cb_db']->prefix.'topicgroups tg
		LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'forums f ON tg.tg_fromforum != 0 AND tg.tg_fromforum = f.forum_id
		ORDER BY forum_order,forum_id,tg.tg_order,tg.tg_id');

	while ($tg=$GLOBALS['cb_db']->fetch_assoc($tgQuery)) {
		$tg['tgid'] = (int)$tg['tgid'];
		$tg['tgff'] = (int)$tg['tgff'];
		$tg['tgftg'] = (int)$tg['tgftg'];
		
		if ($tg['tgff'] != 0) {
			$topicgroups_ff[$tg['tgid']] = $tg['tgff'];
			
			if (isset($forums_p[$tg['tgff']]))
				$forums_p[$tg['tgff']][$tg['tgid']] = $tg['tgid'];
			else
				$forums_p[$tg['tgff']] = array($tg['tgid'] => $tg['tgid']);
		} else {
			$topicgroups_ftg[$tg['tgid']] = $tg['tgftg'];
			if (isset($topicgroups_p[$tg['tgftg']]))
				$topicgroups_p[$tg['tgftg']][$tg['tgid']] = $tg['tgid'];
			else
				$topicgroups_p[$tg['tgftg']] = array($tg['tgid'] => $tg['tgid']);
		}
		if ($tg['tgvis']==1)
			$topicgroups_unvis[$tg['tgid']] = $tg['tgid'];
		if (!empty($tg['tglink']))
			$topicgroups_l[$tg['tgid']] = $tg['tglink'];
		if (!empty($tg['tgcomment']))
			$tgcomments[$tg['tgid']] = $tg['tgcomment'];
		$tgnames[$tg['tgid']] = $tg['tgname'];
	}
	
	file_put_contents(CB_CACHE_STRUCT,
		'<?php'."\n".
		'$GLOBALS[\'cb_str_fnames\'] = unserialize(\''.str_replace("'","\\'",serialize($fnames)).'\');'."\n".
		'$GLOBALS[\'cb_str_tgnames\'] = unserialize(\''.str_replace("'","\\'",serialize($tgnames)).'\');'."\n".
		'$GLOBALS[\'cb_str_tgcomments\'] = unserialize(\''.str_replace("'","\\'",serialize($tgcomments)).'\');'."\n".
		'$GLOBALS[\'cb_str_tglinks\'] = unserialize(\''.str_replace("'","\\'",serialize($topicgroups_l)).'\');'."\n".
		'$GLOBALS[\'cb_str_ff\'] = unserialize(\''.serialize($topicgroups_ff).'\');'."\n".
		'$GLOBALS[\'cb_str_pf\'] = unserialize(\''.serialize($forums_p).'\');'."\n".
		'$GLOBALS[\'cb_str_ftg\'] = unserialize(\''.serialize($topicgroups_ftg).'\');'."\n".
		'$GLOBALS[\'cb_str_ptg\'] = unserialize(\''.serialize($topicgroups_p).'\');'."\n".
		'$GLOBALS[\'cb_str_unvis\'] = unserialize(\''.serialize($topicgroups_unvis).'\');'."\n".
		'?>');
	
	require(CB_CACHE_STRUCT);
}
/* Fonction qui met en cache les groupes d'utilisateurs */
function cacheClasses () {
	$classes = array();
	$legend = array();

	$r = $GLOBALS['cb_db']->query('SELECT gr_id,gr_name,gr_color,gr_cond,gr_hide,gr_status FROM '.$GLOBALS['cb_db']->prefix.'groups WHERE gr_cond!=-2 ORDER BY gr_cond,gr_name');
	while ($gr = $GLOBALS['cb_db']->fetch_assoc($r)) {
		$classes[$gr['gr_id']] = $gr;
		if ($gr['gr_hide'] == 0)
			$legend[] = '<a href="'.manage_url('index.php?act=members&amp;su_class='.$gr['gr_id'], 'forum-members.html?su_class='.$gr['gr_id']).'" '.(!empty($gr['gr_color'])?'style="color:'.$gr['gr_color'].'"':'').'>'.$gr['gr_name'].'</a>';
	}
	
	$legend = implode(' - ',$legend);
	
	$ranks = $GLOBALS['cb_db']->assoc_results('SELECT rk_posts,rk_name FROM '.$GLOBALS['cb_db']->prefix.'ranks ORDER BY rk_posts');
	
	$ga = $GLOBALS['cb_db']->query('SELECT gr_auth_see AS see,gr_auth_create AS `create`,gr_auth_reply AS reply FROM '.$GLOBALS['cb_db']->prefix.'groups WHERE gr_cond=-2');
	$guests_auth = $GLOBALS['cb_db']->fetch_assoc($ga);
	
	file_put_contents(CB_CACHE_CLASSES,'<?php'."\n".
		'$GLOBALS[\'cb_classes\'] = unserialize(\''.str_replace("'","\'",serialize($classes)).'\');'."\n".
		'$GLOBALS[\'cb_legend\'] = unserialize(\''.str_replace("'","\'",serialize($legend)).'\');'."\n".
		'$GLOBALS[\'cb_ranks\'] = unserialize(\''.str_replace("'","\'",serialize($ranks)).'\');'."\n".
		'$GLOBALS[\'cb_guests_auth\'] = unserialize(\''.str_replace("'","\'",serialize($guests_auth)).'\');'."\n".
		'?>');
}
/* Fonction qui met en cache les modérateurs. */
function cacheMods () {
	$rm=$GLOBALS['cb_db']->query('SELECT usr_id,usr_name,usr_mod,gr_id,gr_name,gr_mod,gr_color
			FROM '.$GLOBALS['cb_db']->prefix.'users 
			LEFT JOIN '.$GLOBALS['cb_db']->prefix.'groups ON gr_id=usr_class 
			WHERE gr_status=1 ORDER BY gr_id');
	
	$tgmod_gr = array();
	$tgmod_usr = array();
	$oldgr = 0;
	$gmod = array();
	while ($um = $GLOBALS['cb_db']->fetch_array($rm)) {
		if ($oldgr != $um['gr_id']) {
			$gmod = array_filter(explode('/',$um['gr_mod']),'is_numeric');
			foreach ($gmod as $tgid) {
				$tgmod_gr[$tgid] = (isset($tgmod_gr[$tgid])?$tgmod_gr[$tgid].' - ':'').'<a href="'.manage_url('index.php?act=members&amp;su_class='.$um['gr_id'],'forum-members.html?su_class='.$um['gr_id']).'" '.(!empty($um['gr_color'])?'style="color:'.$um['gr_color'].';"':'').'>'.$um['gr_name'].'</a>';
			}
			$oldgr = $um['gr_id'];
		}
		$umod = array_filter(explode('/',$um['usr_mod']),'is_numeric');
		foreach ($umod as $tgid) {
			if (!in_array($tgid,$gmod)) {
				$tgmod_usr[$tgid] = (isset($tgmod_usr[$tgid])?$tgmod_usr[$tgid].' - ':'').'<a href="'.manage_url('index.php?act=user&amp;showprofile='.$um['usr_id'],'forum-m'.$um['usr_id'].','.rewrite_words($um['usr_name']).'.html').'">'.$um['usr_name'].'</a>';
			}
		}
	}
	foreach ($tgmod_usr as $tgid => $desc) {
		$tgmod_gr[$tgid] = (isset($tgmod_gr[$tgid])?$tgmod_gr[$tgid].' - ':'').$tgmod_usr[$tgid];
	}
	
	file_put_contents(CB_CACHE_MODS,'<?php '."\n".'$tgmod =  unserialize(\''.str_replace("'","\\'",serialize($tgmod_gr)).'\');'."\n".'?>');
}

//// Fonctions relatives à l'url rewriting

/**
* Fonction qui essaye de déterminer automatiquement si l'url rewriting est activé
* Renvoie: 	1 si il est activé
*		0 s'il ne l'est pas
*		-1 si il n'a pas été possible de le déterminer automatiquement
 */
function rewrite_on () {
	if (function_exists('apache_get_modules'))
		return (in_array('mod_rewrite',apache_get_modules()))?1:0;
	if (function_exists('phpinfo')) {
		ob_start();
		phpinfo();
		$r = ob_get_contents();
		ob_end_clean();
		if (utf8_strpos($r,'mod_rewrite') !== false)
			return 1;
	}
	return -1;
}

//// Fonctions de maintenance ////

/* Mise à jour des statistiques */
function resetStats() {
	// Membres enregistrés
	$tot_members = $GLOBALS['cb_db']->single_result('SELECT COUNT(*) FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_registered=\'TRUE\' OR usr_registered LIKE \'change%\'');

	// Sujets
	$tot_topics = $GLOBALS['cb_db']->single_result('SELECT COUNT(*) FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_status!=2');

	// Messages
	$tot_msgs = $GLOBALS['cb_db']->single_result('SELECT COUNT(*) FROM '.$GLOBALS['cb_db']->prefix.'messages');

	// Messages signalés
	$tot_reports = $GLOBALS['cb_db']->single_result('SELECT COUNT(*) FROM '.$GLOBALS['cb_db']->prefix.'reports');

	// Comptes des sujets et réponses dans les groupes de sujets
	resetTgCounts();

	// Modification de la tables stats
	$GLOBALS['cb_db']->query('REPLACE INTO '.$GLOBALS['cb_db']->prefix.'stats(st_field,st_value) VALUES (\'registered_users\','.$tot_members.'),(\'total_topics\','.$tot_topics.'),(\'total_messages\','.$tot_msgs.'),(\'nb_reports\','.$tot_reports.')');
}
/* Fonction qui remet à jour le nombre de sujets et de messages de chaque groupe de sujets, et leurs derniers messages */
function resetTgCounts () {
	$tgs = array();
	$lm = array();
	$rtg = $GLOBALS['cb_db']->query('SELECT SUM(topic_nbreply)+COUNT(topic_id) AS nbmsgs,COUNT(topic_id) AS nbtopics,MAX(topic_lastmessage) AS lastmessage,topic_fromtopicgroup FROM '.$GLOBALS['cb_db']->prefix.'topics GROUP BY topic_fromtopicgroup');
	while ($tg=$GLOBALS['cb_db']->fetch_assoc($rtg)) {
		$tgs[$tg['topic_fromtopicgroup']] = array('nbmsgs' => $tg['nbmsgs'],'nbtopics' => $tg['nbtopics'],'lasttopic' => 0);
		$lm[] = $tg['lastmessage'];
	}
	
	if (!empty($lm)) {
		$rlm = $GLOBALS['cb_db']->query('SELECT topic_fromtopicgroup,topic_id FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_lastmessage IN ('.implode(',',$lm).')');
		while ($d=$GLOBALS['cb_db']->fetch_assoc($rlm))
			$tgs[$d['topic_fromtopicgroup']]['lasttopic'] = $d['topic_id'];
	}
	
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_nbtopics=0,tg_nbmess=0');
	foreach ($tgs as $tgid => $tg)
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_nbtopics='.$tg['nbtopics'].',tg_nbmess='.$tg['nbmsgs'].',tg_lasttopic='.$tg['lasttopic'].' WHERE tg_id='.$tgid);
}

//// Gestion de la structure ////

/* Fonction qui retourne un tableau avec comme index i la string description du topicgroup i */
function getTopicGroupsArray () {
	$array=null;
	foreach ($GLOBALS['cb_str_pf'] as $fid => $contents) {
		foreach ($contents as $tgid) {
			getTopicgroupsArray_rec ($array,$tgid);
		}
	}
	if (empty($array)) return false;
	return $array;
}
function getTopicgroupsArray_rec (&$array,$tgid) {
	$array[$tgid] = implode(' - ',getTgPath($tgid,false)).' - '.$GLOBALS['cb_str_tgnames'][$tgid];
	if (isset($GLOBALS['cb_str_ptg'][$tgid])) {
		foreach ($GLOBALS['cb_str_ptg'][$tgid] as $tg) {
			getTopicgroupsArray_rec ($array,$tg);
		}
	}
}

//// Fonctions d'affichage ////

/* Fonction qui affiche un menu déroulant de classes de type admin. */
function getAdminClassMenu ($name) {
	$items = array();
	$items[] = array('name' => 'default','selected' => false,'value' => '','lang' => 'pa_xpostsclass');
	$return=$GLOBALS['cb_db']->query('SELECT gr_id,gr_name,gr_cond FROM '.$GLOBALS['cb_db']->prefix.'groups ORDER BY gr_id ASC');
	while ($data=$GLOBALS['cb_db']->fetch_assoc($return)) {
		if ($data['gr_cond']==-1) $items[] = array('name' => $data['gr_id'],'selected' => false,'value' => $data['gr_name'],'lang' => '');
	}
	$GLOBALS['cb_tpl']->assign('list',array ( 'name' => $name, 'style' => 170, 'items' => $items ));
	return $GLOBALS['cb_tpl']->fetch('menu_list.php');
}

//// Gestion des fichiers ////

/*Fonction qui crée un dossier et tous ses dossiers parents*/
//exemple: $directory est "chemin/vers/mon/dossier" est ces 4 dossiers n'existent pas.
function mkdirs($directory) {
	do {
		$dir = $directory;
		if(file_exists($dir)) break;
		while (!@mkdir($dir)) {
			$dir = dirname($dir);
			if ($dir == '/' || is_dir($dir)) break;
		}
	} while ($dir != $directory);
}
/*Fonction qui supprime un dossier et tous ses dossiers parents tant que ceux-ci sont vides*/
//exemple: $directory est "chemin/vers/mon/dossier".
function rmdirs($directory,$excepted = '') {
	$dir = $directory;
	$excepted = trim($excepted,'/ .');

	do {
		//regarde si le dossier est vide ou pas
		$is_empty=true;
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..') {
					$is_empty=false;
					return;
				}
			}
			closedir($handle);
		} else return;

		if ($is_empty && trim($dir,'/ .') !== $excepted) rmdir($dir);
		$dir=dirname($dir);
	} while ($is_empty && trim($dir,'/ .') !== $excepted);
}
/* Fonction qui supprime l'intégralité d'un dossier */
function deletedir($dirName) {
	if(empty($dirName)) {
		return;
	}
	if(file_exists($dirName)) {
		$dir = dir($dirName);
		while($file = $dir->read()) {
			if($file != '.' && $file != '..') {
				if(is_dir($dirName.'/'.$file)) {
					deletedir($dirName.'/'.$file);
				} else {
					@unlink($dirName.'/'.$file) or trigger_error('File '.$dirName.'/'.$file.' couldn\'t be deleted!',E_USER_WARNING);
				}
			}
		}
		@rmdir($dirName) or trigger_error('Folder '.$dirName.'/'.$file.' couldn\'t be deleted!',E_USER_WARNING);
	} else {
		trigger_error('Folder "<b>'.$dirName.'</b>" doesn\'t exist.',E_USER_WARNING);
	}
}

//// Fonctions relatives aux groupes d'utilisateurs ////

/* Fonction qui vérifie qu'un groupe d'utilisateurs existe */
function isClass ($gr_id) {
	require_once(CB_CACHE_CLASSES);
	return array_key_exists($gr_id,$GLOBALS['cb_classes']);
}
/* Fonction qui fait un setUserPostClass pour chaque utilisateur de la bdd. */
function setAllUsersPostClass () {
	require_once(CB_PATH.'include/lib/lib.users.php');
	$return=$GLOBALS['cb_db']->query('SELECT usr_id FROM '.$GLOBALS['cb_db']->prefix.'users');
	while ($users_c=$GLOBALS['cb_db']->fetch_array($return)) {
		setUserPostClass($users_c['usr_id']);
	}
}

////  Relatif aux smileys  ////

function getFilesInLibraryDir ($path = null) {
	if ($path == null) {
		require_once(CB_PATH.'include/lib/class.smileysmanager.php');
		$smile = new smileysmanager;
		$path = $smile->smiley_dir.$smile->smiley_librariesdir;
	}
	
	$allfiles = array();
	$handle = opendir($path);
	while (false !== ($file = readdir ($handle))) {
		if ($file != '.' && $file != '..') {
			if (is_dir($path.$file)) {
				$allfiles = array_merge($allfiles,getFilesInLibraryDir($path.$file.'/'));
			} else {
				if ($image=getimagesize($path.$file)) {
					if (in_array($image[2],array(IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG))) {
						$allfiles[] = $path.$file;
					}
				}
			}
		}
	}
	return $allfiles;
}
?>