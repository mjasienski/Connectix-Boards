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

/**
* This file is loaded at each CB page. It contains all essential functions.
* Different sorts of functions are contained in this file, but those are put together in here for efficiency purposes.
*/

//// Fonctions d'intérêt général ////

/* Fonction de redirection. */
function redirect($url='' , $msg=null , $delay=3 , $nolink = false) {
	$full_url = dirname($_SERVER['QUERY_STRING']).((utf8_substr(dirname($_SERVER['QUERY_STRING']),-1)!=='/')?'/':'').$url;
	if (!empty($msg) && isset($GLOBALS['cb_tpl'])) {
		$GLOBALS['cb_tpl']->lang_load('redirect.lang');
		
		$GLOBALS['cb_tpl']->assign(array(
			'm_msg' => $msg,
			'm_title' => lang('redirection'),
			'm_info' => lang(array('item' => 'r_info','n' => $delay)),
			'm_delay' => $delay,
			'm_url' => $full_url,
			'm_nolinks' => $nolink,
			'm_css' => messageCss()
			));
		
		$GLOBALS['cb_tpl']->display('gen_message.php');
	} else header('Location: '.$full_url);
	exit();
}

/* Fonction pour afficher un message, tout seul */
function message($msg) {
	$GLOBALS['cb_tpl']->assign(array(
		'm_msg' => $msg,
		'm_title' => 'Message',
		'm_css' => messageCss()
		));
	
	$GLOBALS['cb_tpl']->display('gen_message.php');
	exit();
}

/* Fonction qui renvoie le css à utiliser pour les messages */
function messageCss () {
	return (isset($_SESSION['cb_user']) && file_exists('skins/'.$_SESSION['cb_user']->getPreferredSkin().'/message.css')?'skins/'.$_SESSION['cb_user']->getPreferredSkin():'admin/design').'/message.css';
}

//// Fonctions de sécurité ////

/* Fonction servant à hasher les mots de passe */
function cbHash ($password,$notype = false) {
	if (!$notype) {
		if ($GLOBALS['cb_cfg']->config['hash_type'] == 'cb')
			return md5($password);

		if ($GLOBALS['cb_cfg']->config['hash_type'] == 'pun') {
			if (function_exists('sha1'))
				return sha1($password);
			elseif (function_exists('mhash'))
				return bin2hex(mhash(MHASH_SHA1, $password));
			else
				return md5($password);
		}
	}
	return md5($password);
}

/* Fonction qui dit si le forum est en pause ou non */
function isPaused () {
	return $GLOBALS['cb_cfg']->config['paused']=='yes';
}

/* Fonction de génération d'un code unique. */
function genValidCode() {
	return md5(uniqid(mt_rand(),true));
}

//// Gestion des dates et temps ////

/* Fonction de format de la date. */
/* Cette fonction est utilisée pour tous les affichages, et uniquement pour ca */
/* $date_type: 1->jour et heure;2->jour;3->heure  */
function dateFormat ($timestamp, $date_type=1, $display_full_day=false) {
	// Adaptation du timestamp en fonction du fuseau horaire de l'utilisateur
	$timestamp = localTimestamp($timestamp);
	$now_local = localTimestamp(time());
	
	if ($date_type == 3)
		return date('H\hi',$timestamp);

	$day = date('d/m/Y',$timestamp);
	if ($display_full_day) {
		// Seconds in current day
		$sec_in_day = date('G',$now_local) * 3600 + date('i',$now_local) * 60 + date('s',$now_local);
		
		// Difference, in seconds, from now to timestamp
		$diff_from_now = $now_local - $timestamp;
		
		if ($diff_from_now < 10800) {
			return lang(array('item' => 'ago', 'time' => getTimeFormat($diff_from_now,true)));
		} elseif ($diff_from_now < $sec_in_day) {
			$day = lang('today');
		} elseif ($diff_from_now < $sec_in_day + 86400) 
			$day = lang('yesterday');
	}

	if ($date_type == 2)
		return $day;
	else
		return $day.' - '.date('H\hi', $timestamp);
}

/* Fonction qui donne le timestamp local en fonction du timestamp normal */
function localTimestamp ($timestamp) {
	return $timestamp - ((int)date('Z',$timestamp)) // On réaligne avec GMT
		+ $_SESSION['cb_user']->usr_pref_timezone*3600 // Décalage à cause du fuseau horaire
		+ $_SESSION['cb_user']->usr_pref_ctsummer*((int)date('I',$timestamp))*3600; // Décalage à cause du changement d'heure été/hiver
}	

/* Fonction qui renvoie la date du jour demandé, dans le format qui nous convient */
function getMyDate ($date) {
	if (empty($date) || $date == '0000-00-00') return '';
	list ($year,$month,$day) = explode('-',$date);
	return $day.'-'.$month.'-'.$year;
}

/* Fonction qui retourne le nombre de secondes donné en jours, heures, minutes et secondes, dans une chaine de caractères. */
function getTimeFormat ($seconds,$onlybiggest = false) {
	if ($seconds == 0) return '0 '.lang('seconds');

	$timetxt = '';
	$times = array('day' => 86400,'hour' => 3600,'minute' => 60);
	foreach ($times as $period => $sec) {
		$time=floor($seconds/$sec);
		if ($time>0) {
			$timetxt .= $time.' '.lang($period.($time>1?'s':'')).' ';
			if ($onlybiggest) return $timetxt;
		}
		$seconds-=$time*$sec;
	}
	
	if ($seconds>0) $timetxt .= $seconds.' '.lang('second'.($seconds>1?'s':''));
	return $timetxt;
}

/* Age d'un utilisateur, à partir de sa date de naissance */
function getAge ($date) {
	$now = date('d-m-Y',time());
	list($day_n,$month_n,$year_n) = explode('-',$now);
	list($year,$month,$day) = explode('-',$date);
	
	$age = (int)$year_n - (int)$year;
	if ((int)$month_n < (int)$month) $age--;
	elseif ((int)$month==(int)$month_n && (int)$day_n < (int)$day) $age--;
	
	return $age;
}

//// Fonctions relatives à l'affichage ////

/* Détermine la validité d'un code langue. */
function isLang ($langtype) {
	return is_dir('lang/'.clean($langtype,STR_TODISPLAY));
}

/* Détermine la validité d'un code de skin. */
function isSkin ($skintype) {
	return is_dir('skins/'.clean($skintype,STR_TODISPLAY));
}

/* Fomulaire de choix  de langue. */
function langMenu ($inputname,$current=null) {
	$output='<select name="'.$inputname.'" class="langchooser">';
	foreach (langs() as $lang) {
		$output.='<option value="'.$lang.'" '.(($lang==$current)?'selected="selected"':'').'>'.$lang.'</option>';
	}
	$output.='</select>';
	return $output;
}

/* Fomulaire de choix  de skin. */
function skinMenu ($inputname,$current=null) {
	$output='<select name="'.$inputname.'" onchange="javascript:fast_list(\''.$inputname.'\')">';
	$handle = opendir(CB_PATH.'skins/');
	while (false !== ($file = readdir ($handle))) {
		if ($file != '.' && $file != '..' && is_dir(CB_PATH.'skins/'.$file)) {
			$output.='<option value="'.$file.'" '.(($file==$current)?'selected="selected"':'').'>'.$file.'</option>';
		}
	}
	closedir($handle);
	$output.='</select>';
	return $output;
}

/* Compte le nombre de skins */
function nbSkins () {
	$cnt = 0;
	$handle = opendir(CB_PATH.'skins/');
	while (false !== ($file = readdir ($handle))) {
		if ($file != '.' && $file != '..' && is_dir(CB_PATH.'skins/'.$file)) {
			$cnt++;
		}
	}
	return $cnt;
}

/* Returns all langs supported */
function langs() {
	$langs = array();
	$handle = opendir(CB_PATH.'lang/');
	while (false !== ($file = readdir ($handle))) {
		if ($file != '.' && $file != '..' && is_dir(CB_PATH.'lang/'.$file)) {
			$langs[]=$file;
		}
	}
	closedir($handle);
	return $langs;
}

/* Renvoie le menu de smileys */
function getSmileyMenu ($taid, $extended = false) {
	require_once(CB_CACHE_SMILEYS);
	
	$count=count($GLOBALS['cb_smileys']);
	if ($count == 0) return '';
	
	$return='';
	foreach ($GLOBALS['cb_smileys'] as $smiley) {
		if (($smiley['form'] == 1 && !$extended) || ($smiley['form'] == 2 && $extended))
			$return.='<a href="javascript:emoticon(\''.$smiley['symbol'].'\',\''.$taid.'\')"><img src="smileys/'.$smiley['filename'].'" alt="'.$smiley['symbol'].'" /></a>'."\n";
	}
	return $return;
}

/* Fonction qui affiche le menu de numéros de pages d'une section. */
function pageMenu($nbmess,$currentpage,$maxperpage,$url) {
	$menu='';
	if ($nbmess>$maxperpage) {
		$range = 3;
		$pages = ceil($nbmess/$maxperpage);
		$menu .= ': ';
		if ($currentpage>$range) $menu.=' <a href="'.str_replace('[num_page]',1,$url).'" class="pagenum firstpage">1</a>';
		
		if ($currentpage>$range+1) $menu.=' <span class="pagenum pagedots">…</span>';
		elseif ($currentpage>$range) $menu.=' <span class="pagenum pageseparator">-</span>';
		
		$first=true;
		for ($p=$currentpage-$range+1;$p<=$currentpage+$range-1;$p++) {
			if ($p>0 && $p<=$pages) {
				if ($currentpage != $p) 
					$menu.=((!$first)?' <span class="pagenum pageseparator">-</span>':'').' <a href="'.str_replace('[num_page]',$p,$url).'" class="pagenum somepage'.($p == $currentpage - 1?' nav_prevpage':($p == $currentpage + 1?' nav_nextpage':'')).'">'.$p.'</a>';
				else 
					$menu.=((!$first)?' <span class="pagenum pageseparator">-</span>':'').' <span class="pagenum currentpage">'.$p.'</span>';
				$first=false;
			}
		}
		if (($pages-$currentpage)>($range)) $menu.=' <span class="pagenum pagedots">…</span>';
		elseif (($pages-$currentpage)>($range-1)) $menu.=' <span class="pagenum pageseparator">-</span>';
		
		if (($pages-$currentpage)>($range-1)) $menu.=' <a href="'.str_replace('[num_page]',$pages,$url).'" class="pagenum lastpage">'.$pages.'</a>';
	}
	return $menu;
}

/* Fonction de gestion des éléments de langue. */
function lang ($params) {
	if (is_array($params)) {
		$lang_item = null;
		$replace_from = array();
		$replace_to   = array();

		foreach ($params as $key => $value) {
			if ($key == 'item') $lang_item = $value;
			else {
				$replace_from[] = '{'.$key.'}';
				$replace_to[] = $value;
			}
		}

		if (empty($lang_item)) 
			trigger_error('Lang item not defined. Bad use of \'lang\' function.',E_USER_WARNING);
		
		if (count($replace_from) == 0) 
			return $GLOBALS['cb_tpl']->lang[$lang_item];
		
		return str_replace($replace_from,$replace_to,$GLOBALS['cb_tpl']->lang[$lang_item]);
	} else 
		return $GLOBALS['cb_tpl']->lang[$params];
}

/* Fonction de gestion des url, pour faire un choix entre des url utilisant le rewrite ou pas, en fonction du fait que l'url rewriting est activé ou non. */
function manage_url ($norm,$rewr) {
	return ($GLOBALS['cb_cfg']->config['url_rewrite']=='yes')?$rewr:$norm;
}

/* Fonction qui 'coupe' les phrases trop longues. Reprise de smarty, et épurée pour les besoins cb. */
function truncate ($string, $length = 80, $etc = '...', $break_words = false, $middle = false) {
	if ($length == 0)
		return '';

	if (utf8_strlen($string) > $length) {
		$length -= utf8_strlen($etc);
		if (!$break_words && !$middle)
			$string = preg_replace('/\s+?(\S+)?$/', '', utf8_substr($string, 0, $length+1));
		
		if(!$middle)
			return utf8_substr($string, 0, $length).$etc;
		else
			return utf8_substr($string, 0, $length/2) . $etc . utf8_substr($string, -$length/2);
	} else return $string;
}

/* Fonction qui permet d'alterner des valeurs dans les templates. */
function manage_cycle($params) {
	static $cyv;
	if (empty($cyv['values'])) {
		$cyv['values'] = explode(',',$params);
		$cyv['size'] = count($cyv['values']);
		$cyv['position'] = 0;
	}
	return $cyv['values'][$cyv['position']++%$cyv['size']];
}

/* Fonction permettant de mettre des mots, phrases, etc... dans l'url si elle est en rewrite */
function rewrite_words ($texte) {
	$texte = utf8_strtolower($texte);

	$texte = strtr($texte, 'âäàáéèêëîïíìôöóòûüúùýÿç', 'aaaaeeeeiiiioooouuuuyyc');

	$texte = preg_replace('#(&[a-z0-9\#]+;)#','-',$texte);
	$texte = preg_replace('#[^a-z0-9]#','-',$texte);
	$texte = preg_replace('#-+#', '-', $texte);

	$texte = trim ($texte,'-');
	
	if (utf8_strlen($texte) == 0) $texte = '-';
	
	return urlencode($texte);
}

//// Aide à la gestion des utilisateurs  ////

/* Fonction qui vérifie si l'id d'un utilisateur existe. */
function isUser ($id) {
	if (empty($id) || !is_numeric($id))
		return false;
	return $GLOBALS['cb_db']->single_result('SELECT 1 FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_id='.(int)$id);
}

/* Fonction qui renvoie l'id d'un utilisateur à partir de son nom (brut). */
function getUserId ($name) {
	return $GLOBALS['cb_db']->single_result('SELECT usr_id FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_name = \''.clean($name).'\'');
}

/* Fonction qui renvoie le nom d'un utilisateur à partir de son id. */
function getUserName ($id) {
	return $GLOBALS['cb_db']->single_result('SELECT usr_name FROM '.$GLOBALS['cb_db']->prefix.'users WHERE usr_id='.(int)$id);
}

/* Fonction qui renvoie le lien d'un membre ou le nom de l'invité en fonction de ce qui est renvoyé par mysql - Modif Ishimaru Chiaki pour url */
function getUserLink ($id,$uname,$gname,$url = '') {
   if ($id > 0) 
	   return '<a href="'.manage_url($url.'index.php?act=user&amp;showprofile='.$id,$url.'forum-m'.$id.','.rewrite_words($uname).'.html').'">'.$uname.'</a>';
   else 
	   return '<span class="guest_name">'.($gname?$gname:lang('guest')).'</span>';
}

/* Fonction qui renvoie la balise img de l'avatar demandé ($avatar venant de la bdd). */
function getAvatar ($avatar) {
	if (utf8_strpos($avatar,'|') !== false) {
		$av=explode('|',$avatar);
		if ($av[1]=='h') return '<img src="'.$av[0].'" alt="" height="'.$GLOBALS['cb_cfg']->config['maxsize'].'" />';
		elseif ($av[1]=='w') return '<img src="'.$av[0].'" alt="" width="'.$GLOBALS['cb_cfg']->config['maxsize'].'" />';
	} elseif (file_exists($avatar)) return '<img src="'.$avatar.'" alt="" />';
	else return '';
}

/* Rang d'un utilisateur */
function getRank ($posts) {
	require_once(CB_CACHE_CLASSES);
	$rank = '';
	foreach ($GLOBALS['cb_ranks'] as $nb => $name) {
		if ($posts >= $nb) $rank = $name;
		else return $rank;
	}
	return $rank;
}

/* Représentation de la réputation d'un membre */
function getReputation ($rep,$uid) {
	$ret='<a href="'.manage_url('index.php?act=mods&page=2&punish='.$uid,'forum-moderators.html?page=2&punish='.$uid).'" class="reputation" title="'.lang('reputation').' : '.lang('reput_'.(int)$rep).'">';
	for ($i=1;$i<=5;$i++)
		$ret.='<span class="rep_star_'.(($i <= (5-(int)$rep))?'on':'off').'"></span>';
	return $ret.'</a>';
}

//// Fonctions relatives à la structure du forum ////

/* Fonction qui vérifie qu'un forum existe. */
function isForum ($id) {
	return isset($GLOBALS['cb_str_fnames'][$id]);
}

/* Fonction qui vérifie qu'un groupe de sujets existe. */
function isTg($id) {
	return isset($GLOBALS['cb_str_tgnames'][$id]);
}

/* Fonction qui vérifie qu'un topic existe. */
function isTopic ($id) {
	return (bool)$GLOBALS['cb_db']->single_result('SELECT 1 FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_id='.$id.' AND topic_status != 2');
}

/* Fonction qui renvoie l'id d'un topicgroup correspondant à l'id du topic donné. */
function getTopicgroupId ($topicid) {
	return $GLOBALS['cb_db']->single_result('SELECT topic_fromtopicgroup FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_id='.$topicid);;
}

/* Fonction qui renvoie le fil d'ariane pour arriver à un groupe de sujets (pour faire les addressbar, notamment) (ne comprend pas le lien du tg de base) */
function getTgPath ($tg_id,$links = true) {
	$path = array();
	while (isset($GLOBALS['cb_str_ftg'][$tg_id])) {
		$tg_id = $GLOBALS['cb_str_ftg'][$tg_id];
		$path[] = ($links?'<a href="'.manage_url('index.php?showtopicgroup='.$tg_id,'forum-tg'.$tg_id.','.rewrite_words($GLOBALS['cb_str_tgnames'][$tg_id]).'.html').'">'.$GLOBALS['cb_str_tgnames'][$tg_id].'</a>':$GLOBALS['cb_str_tgnames'][$tg_id]);
	}
	$f_id = $GLOBALS['cb_str_ff'][$tg_id];
	$path[] = ($links?'<a href="'.manage_url('index.php?showforum='.$f_id,'forum-f'.$f_id.','.rewrite_words($GLOBALS['cb_str_fnames'][$f_id]).'.html').'">'.$GLOBALS['cb_str_fnames'][$f_id].'</a>':$GLOBALS['cb_str_fnames'][$f_id]);

	return array_reverse($path);
}

/* Fonction qui renvoie les id des groupes et forums successifs pour arriver à un groupe de sujets dans une chaine (comprend le lien du tg de base) */
function getTgPathIds ($tg_id) {
	$path = $tg_id;
	while (isset($GLOBALS['cb_str_ftg'][$tg_id])) {
		$tg_id = $GLOBALS['cb_str_ftg'][$tg_id];
		$path = $tg_id.'_'.$path;
	}
	return $GLOBALS['cb_str_ff'][$tg_id].'_'.$path;
}

/* Fonctions qui affiche un menu déroulant avec tous les forums, groupes et sous-groupes de sujets, de nom $name, avec le forum d'id $id présélectionné. */
function showForumMenu($name,$defTitle,$fid = 0,$tgid = 0,$ownf = 0,$owntg = 0,$f_optgroups=false,$tg_prefix = 'tg_',$f_prefix = 'f_',$m_style = 350, $m_class = null) {
	if ($fid*$tgid != 0) $tgid = 0;
	
	$ids = array();
	foreach ($GLOBALS['cb_str_fnames'] as $forumid => $foo) {
		$ids[$forumid] = array();
		if (in_array($forumid,array_keys($GLOBALS['cb_str_pf'])))
			getTgMenu($ids[$forumid],$GLOBALS['cb_str_pf'][$forumid],($f_optgroups)?'':'--');
	}
	
	$items = array(array('name' => 'default','selected' => false,'value' => '','lang' => $defTitle));
	foreach ($ids as $forumid => $fcts) {
		if (count($fcts) > 0 || !$f_optgroups) {
			$f_optgroup = '';
			if (!$f_optgroups) 
				$items[] = array(
					'name' => $f_prefix.$forumid,
					'selected' => $forumid == $fid,
					'value' => $GLOBALS['cb_str_fnames'][$forumid],
					'lang' => '',
					'disabled' => $ownf == $forumid);
			else
				$f_optgroup = $GLOBALS['cb_str_fnames'][$forumid];
			
			foreach ($fcts as $tgd) {
				$item = array(
					'name' => $tg_prefix.$tgd[0],
					'selected' => $tgd[0] == $tgid,
					'value' => $tgd[1].' '.$GLOBALS['cb_str_tgnames'][$tgd[0]],
					'lang' => '',
					'disabled' => $owntg == $tgd[0]);
				if (!empty($f_optgroup)) $item['optgroup'] = $f_optgroup;
				$items[] = $item;
			}
		}
	}
	
	$GLOBALS['cb_tpl']->assign('list',array ( 'name' => $name, 'style' => $m_style, 'class' => $m_class, 'items' => $items ));
	return $GLOBALS['cb_tpl']->fetch('menu_list.php');
}

/* Fonction récursive pour la fonction précédente */
function getTgMenu (&$ids,$cts,$indent) {
	foreach ($cts as $tgid) {
		if ($_SESSION['cb_user']->getAuth('see',$tgid) && (!in_array($tgid,$GLOBALS['cb_str_unvis']) || $_SESSION['cb_user']->isModerator())) {
			$ids[] = array($tgid,$indent);
			if (isset($GLOBALS['cb_str_ptg'][$tgid]))
				getTgMenu($ids,$GLOBALS['cb_str_ptg'][$tgid],$indent.'--');
		}
	}
}

//// Fonctions de gestion des chaines de caractéres ////

define('STR_MULTILINE',1);
define('STR_TODISPLAY',2);
define('STR_SIGNATURE',4);
define('STR_PUTLONGURL',8);
define('STR_REMOVESPECIALCHARS',16);
define('STR_PARSEBB',32);

// Fonction de nettoyage
function clean ($str,$opt = 0) {
	$str = str_replace("\r",'',$str);
	$str = htmlspecialchars(trim($str),ENT_QUOTES);
	if ($opt & STR_PARSEBB) {
		require_once(CB_PATH.'include/lib/lib.bbcode.php');
		$str = parse_bb($str,$opt);
	}
	if ($opt & STR_MULTILINE) 
		$str = str_replace("\n","<br />\n",$str);
	$str = str_replace("\t",(($opt & STR_MULTILINE)?'&nbsp; &nbsp; ':' '),$str);
	$str = str_replace('  ',' &nbsp;',$str);
	if (!($opt & STR_TODISPLAY)) 
		$str = $GLOBALS['cb_db']->escape($str);
	return $str;
}

// On 'dénettoie', pour éditer ou citer par exemple...
function unclean($str,$opt = 0) {
	require_once(CB_PATH.'include/lib/lib.bbcode.php');
	$str = str_replace('&nbsp;',' ',$str);
	$str = removeSmileys($str);
	$str = unparse_bb($str);
	$str = str_replace(array('<!-- <br /> -->','<!-- DBSP -->'),array('<br />','  '),$str);
	$str = str_replace("\n",'',$str);
	$str = str_replace('<br />',"\n",$str);
	$str = strip_tags($str);
	if ($opt & STR_REMOVESPECIALCHARS) 
		$str = html_entity_decode($str,ENT_QUOTES);
	return $str;
}

/* Fonction qui retire les slashes d'un array (multidimensionnel). */
function stripslashes_rec($arr) {
	return (is_array($arr)) ? array_map('stripslashes_rec',$arr) : stripslashes($arr);
}

/* Substr compatible utf8 */
function utf8_substr ($str,$from,$len = 0) {
	if (extension_loaded('mbstring')) {
		if ($len == 0)
			return (mb_substr($str,$from));
		else
			return (mb_substr($str,$from,$len));
	}
	
	$length = utf8_strlen($str);
	
	if (abs($from) >= $length)
		return '';
	
	if ($from < 0)
		$from = $length + $from;
	
	$maxlen = $length - $from;
	
	if ($len == 0 || abs($len) > $maxlen)
		$len = $maxlen;
	
	if ($len < 0)
		$len = $maxlen + $len;
	
	return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'. $from .'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'. $len .'}).*#s','$1', $str);
}

/* Strlen compatible utf8 */
function utf8_strlen ($str) {
	if (extension_loaded('mbstring')) 
		return (mb_strlen($str));
	
	return (strlen(preg_replace('#[\x80-\xBF]#S', '', $str))); 
}

/* Strpos compatible utf8 */
function utf8_strpos ($str,$ndl) {
	if (extension_loaded('mbstring')) 
		return (mb_strpos($str,$ndl));
	
	$pos = utf8_strlen(preg_replace('#^((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+)*)'.preg_escape_metachars($ndl).'(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+)*$#SU', '$1', $str));
	$len = utf8_strlen($str);
	
	return $pos == $len ? false : $pos; 
}

/* Strrpos compatible utf8 */
function utf8_strrpos ($str,$ndl) {
	if (extension_loaded('mbstring')) 
		return (mb_strrpos($str,$ndl));
	
	$pos = utf8_strlen(preg_replace('#^(?:(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+)*'.preg_escape_metachars($ndl).')+([\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+)*$#SU', '$1', $str));
	$len = utf8_strlen($str);
	
	return $pos == $len ? false : $len - $pos - 1;
}

/* Substr_count compatible utf8 */
function utf8_substr_count ($str,$ndl) {
	if (extension_loaded('mbstring')) 
		return (mb_substr_count($str,$ndl));
	
	preg_replace('#'.preg_escape_metachars($ndl).'#', 'r', $str, -1, $count);
	return $count;
}

/* Strtolower compatible utf8 */
function utf8_strtolower ($str) {
	if (extension_loaded('mbstring')) 
		return (mb_strtolower($str));
	
	return utf8_encode(strtolower(utf8_decode($str)));
}

/* Strtoupper compatible utf8 */
function utf8_strtoupper ($str) {
	if (extension_loaded('mbstring')) 
		return (mb_strtoupper($str));
	
	return utf8_encode(strtoupper(utf8_decode($str)));
}

/* To escape metachars for regexp. */
function preg_escape_metachars($str) {
	$metacharsorig=array('\\'  ,'^' ,'|' ,'?' ,'*' ,'+' ,'{' ,'}' ,'[' ,']' ,'(' ,')' ,'.' ,'-' ,'!' );//,'<' ,'>' ,'=' ,':' );
	$metacharsrepl=array('\\\\','\^','\|','\?','\*','\+','\{','\}','\[','\]','\(','\)','\.','\-','\!');// ,'\<' ,'\>' ,'\=' ,'\:' );
	return str_replace($metacharsorig,$metacharsrepl,$str);
}

/* Décodage d'une chaine utf8 encodée par 'escape' de javascript. */
function utf8_js_decode($str) {
	return (preg_replace('#%u([[:alnum:]]{4})#i', '&#x\\1;', $str));
}

//// FONCTIONS PARFOIS INEXISTANTES (versions de PHP) ////

/* array_combine */
if (!function_exists('array_combine')) {
	function array_combine($keys, $values) {
		$out = array();
		foreach($keys as $key) 
			$out[$key] = array_shift($values);
		return $out;
	}
}

/* file_put_contents */
if (!function_exists('file_put_contents')) {
	function file_put_contents ($file,$string) {
		if ($h=fopen($file,'w')) {
			if (fwrite($h,$string) !== false) {
				fclose($h);
				return true;
			}
			fclose($h);
			trigger_error('Could not write data in '.$file.' !',E_USER_WARNING);
		} else trigger_error('Could not open '.$file.', please check its rights.',E_USER_WARNING);
		return false;
	}
}
?>