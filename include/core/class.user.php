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
class user {
	var $time_loaded;			// Timestamp du dernier chargement des données
	var $time_to_wait = 5;		// Temps (en minutes) à attendre pour un rechargement des données
	var $sessid;
	var $logged;
	var $username;
	var $userid;
	var $userclass;
	var $nbmp;
	var $mod;
	var $punished;
	var $mpadv;
	var $gr_status;
	var $gr_mps;
	var $gr_auth_see;
	var $gr_auth_reply;
	var $gr_auth_create;
	var $gr_auth_flood;
	var $usr_pref_res=15;
	var $usr_pref_usrs=20;
	var $usr_pref_msgs=15;
	var $usr_pref_topics=20;
	var $usr_pref_skin;
	var $usr_pref_skin_guest;
	var $usr_pref_lang;
	var $usr_pref_lang_guest;
	var $usr_pref_timezone;
	var $usr_pref_ctsummer;
	var $connected_position;
	var $mark_as_read;

	/* Crée un objet de gestion des membres. */
	function user () {
		$this->logged = false;
		
		$this->sessid = mt_rand();
		
		/* Gestion du login automatique par cookies */
		if (isset($_COOKIE['cb_username']) && isset($_COOKIE['cb_password'])) {
			if (!$this->isRegistered($_COOKIE['cb_username'],$_COOKIE['cb_password'],true,true)) {
				$this->userCookies();
			}
		}

		/* Si on n'est pas loggé, on initialise les droits invité */
		if (!$this->logged) {
			$this->setVars();
			
			if (isset($_COOKIE['cb_guest_lang']) && isLang($_COOKIE['cb_guest_lang']))
				$this->usr_pref_lang_guest = $_COOKIE['cb_guest_lang'];
			if (isset($_COOKIE['cb_guest_skin']) && isLang($_COOKIE['cb_guest_skin']))
				$this->usr_pref_skin_guest = $_COOKIE['cb_guest_skin'];
		}
	}
	
	/* Fonction à appeler à chaque nouvelle page, si l'objet a déja été instancié. */
	function newPage () {
		if ($this->logged)
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_lastaction='.time().' WHERE usr_id='.$this->userid);

		if ($GLOBALS['cb_cfg']->config['last-cached'] > time() || time()-$this->time_to_wait*60 > $this->time_loaded) 
			$this->setVars();
	}
	
	/* Fonction qui insère tout ce qu'il faut dans la table connected et users. */
	function connected ($position) {
		$this->connected_position = $position;
		$GLOBALS['cb_db']->query('REPLACE DELAYED INTO '.$GLOBALS['cb_db']->prefix.'connected(con_ip,con_id,con_timestamp,con_position) VALUES('.(!empty($_SERVER['REMOTE_ADDR'])?ip2long($_SERVER['REMOTE_ADDR']):0).','.$this->conId().','.time().',\''.$this->connected_position.'\')');
		
		if (mt_rand(0,100) < 2) 
			$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'connected WHERE con_timestamp<'.(time()-($GLOBALS['cb_cfg']->config['connectedlimit']*60)));
	}
	
	/* Fonction qui renvoie l'identifiant de connexion pour la table connected. */
	function conId () {
		if ($this->logged) 
			return $this->userid;
		else 
			return 1000000 + $this->sessid;
	}
	
	/* La fonction isRegistered vérifie si l'utilisateur est bien enregistré dans la BDD et renvoit true si c'est le cas, false sinon. */
	function isRegistered ($username,$password,$passSalted = false,$remember = false) {
		$q = $GLOBALS['cb_db']->query('SELECT
				usr_id,usr_punished,usr_password,usr_registered,usr_name,usr_lastaction,usr_markasread'.(($GLOBALS['cb_cfg']->config['hash_type'] == 'ipb')?',usr_password_s':'').'
			FROM '.$GLOBALS['cb_db']->prefix.'users
			WHERE usr_name=\''.clean($username).'\'');

		if ($ud = $GLOBALS['cb_db']->fetch_assoc($q)) {
			$pun=explode('|',$ud['usr_punished']);

			// Conditions pour être connecté (password ok et compte validé)
			$cond_pass = false;
			if ($passSalted) $cond_pass = (cbHash($ud['usr_password'].$GLOBALS['cb_cfg']->config['pass_salt']) == $password);
			else $cond_pass = ($ud['usr_password'] == cbHash($password));
			
			$cond_val = ($ud['usr_registered']=='TRUE');

			// Vérification, à cause de la migration IPB
			if (!($cond_pass && $cond_val) && $GLOBALS['cb_cfg']->config['hash_type'] == 'ipb' && !empty($ud['usr_password_s'])) {
				// Si le password est bon, au sens IPB
				if ($ud['usr_password'] == md5(md5($ud['usr_password_s']).cbHash($password))) {
					$cond_pass = true;
					// Modification des infos utilisateur pour être compatible CB (IPB, c'est nul!)
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_password=\''.cbHash($password).'\',usr_password_s=\'\' WHERE usr_id='.$ud['usr_id']);
				}
			}

			if ($cond_val) {
				if ($cond_pass) {
					if (!($pun[0]=='ban' && ($pun[1]+$pun[2]>time() || $pun[2] == 0))) {
						$this->logged = true;
						$this->userid = $ud['usr_id'];

						$conid=str_pad(utf8_substr(str_replace('.','',$_SERVER['REMOTE_ADDR']),0,11),10,'0');
						$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'connected WHERE con_id='.(int)$conid);

						$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_lastconnect='.time().',usr_lastaction='.time().' WHERE usr_id='.$this->userid);

						if ($remember)
							$this->userCookies($ud['usr_name'],cbHash($ud['usr_password'].$GLOBALS['cb_cfg']->config['pass_salt']));
						
						if ($GLOBALS['cb_cfg']->config['readornot_sessions'] == 'yes') {
							$msg_mar = $GLOBALS['cb_db']->single_result('SELECT MAX(msg_id) FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_timestamp <='.$ud['usr_lastaction']);
							if (is_numeric($msg_mar))
								$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_markasread='.(int)$msg_mar.' WHERE usr_id='.$ud['usr_id']);
						}
						
						$this->setVars();
						return true;
					} elseif ($GLOBALS['cb_tpl']->lang_loaded('errors.lang')) {
						if ($pun[2] == 0)
							trigger_error(lang('error_banned_undef'),E_USER_WARNING);
						else
							trigger_error(str_replace('timeleft',getTimeFormat($pun[1]+$pun[2]-time()),lang('error_banned_time')),E_USER_WARNING);
					}
				} elseif ($GLOBALS['cb_tpl']->lang_loaded('errors.lang')) trigger_error(lang('error_wrongpassword'),E_USER_WARNING);
			} elseif ($GLOBALS['cb_tpl']->lang_loaded('errors.lang')) trigger_error(lang('error_validateaccount'),E_USER_WARNING);
		} elseif ($GLOBALS['cb_tpl']->lang_loaded('errors.lang')) trigger_error(lang('error_notregistered'),E_USER_WARNING);
		return false;
	}
	
	/* Gestion du formulaire de connexion rapide */
	function fastConnect() {
		if (!$this->logged && isset($_POST['fast_connect'])) {
			if (!empty($_POST['fast_login']) && !empty($_POST['fast_password'])) {
				if ($this->isRegistered($_POST['fast_login'], $_POST['fast_password'], false, (isset($_POST['fast_remember']) && $_POST['fast_remember']=='on'))) {
					$parsed = parse_url($_SERVER['HTTP_REFERER']);
					if (isset($_SERVER['HTTP_REFERER']) && $parsed['host'] == $_SERVER['HTTP_HOST'] && utf8_strpos($_SERVER['HTTP_REFERER'],'login') === false)  {
						header('Location: '.$_SERVER['HTTP_REFERER']);
						exit();
					} else redirect();
				}
			} else trigger_error(lang('error_fillfields'),E_USER_WARNING);
		}
	}
	
	/* Fonction qui spécifie si un utilisateur a les droits de modération sur le groupe de topics demandé. */
	function isMod ($tg) {
		if ($this->logged!==true) return false;
		if ($this->isAdmin()) return true;
		elseif ($this->isModerator()) return in_array($tg,$this->mod);
		else return false;
	}
	
	/* Fonction qui détermine si l'utilisateur est administrateur ou pas. */
	function isAdmin () {
		if ($this->logged!==true) return false;
		return ($this->gr_status == 2);
	}
	
	/* Fonction qui détermine si l'utilisateur est modérateur ou pas (ne tient pas compte des groupes de sujets modérés). */
	function isModerator () {
		if ($this->logged!==true) return false;
		return ($this->gr_status >= 1);
	}
	
	/* Fonction qui détermine les droits de l'utilisateur -> renvoie true si l'utilisateur possède le droit considéré. */
	function getAuth ($auth_type,$tg_id) {
		if ($this->isAdmin())
			return true;
		
		if (in_array($tg_id,$this->gr_auth_see)) return false;
		if ($auth_type=='see') return true;

		if (isset($this->punished[2]) && $this->punished[0]=='readonly' && 
			($this->punished[1]+$this->punished[2] > time() || $this->punished[2] == 0)) 
				return false;

		if (in_array($tg_id,$this->gr_auth_reply)) return false;
		if ($auth_type=='reply') return true;

		if (in_array($tg_id,$this->gr_auth_create)) return false;

		return true;
	}
	
	/* Fonction qui détermine si l'utilisateur a le droit de flooder ou non. */
	function canFlood() {
		if ($this->logged!==true) return false;
		if ($this->gr_auth_flood==1) return true;
		return false;
	}
	
	/* Fonction qui renvoie la langue à utiliser. */
	function getPreferredLang () {
		if (!$this->logged) {
			if (isset($_GET['lang']) && isLang($_GET['lang'])) {
				$this->usr_pref_lang_guest = $_GET['lang'];
				setcookie('cb_guest_lang',$_GET['lang'],time() + 31536000,$GLOBALS['cb_cfg']->config['cookie_path']);
				return $_GET['lang'];
			} elseif (!empty($this->usr_pref_lang_guest) && isLang($this->usr_pref_lang_guest)) {
				return $this->usr_pref_lang_guest;
			} else {
				return $GLOBALS['cb_cfg']->config['defaultlanguage'];
			}
		} else {
			if (isset($_GET['lang']) && isLang($_GET['lang'])) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users
					SET usr_pref_lang=\''.clean($_GET['lang']).'\'
					WHERE usr_id='.$this->userid);
				$this->usr_pref_lang = $_GET['lang'];
				return $_GET['lang'];
			} elseif (!empty($this->usr_pref_lang) && isLang($this->usr_pref_lang))
				return $this->usr_pref_lang;
			else
				return $GLOBALS['cb_cfg']->config['defaultlanguage'];
		}
	}
	
	/* Fonction qui renvoie la skin à utiliser. */
	function getPreferredSkin () {
		if (!$this->logged) {
			if (isset($_GET['skin']) && isSkin($_GET['skin'])) {
				$this->usr_pref_skin_guest = $_GET['skin'];
				setcookie('cb_guest_skin',$_GET['skin'],time() + 31536000,$GLOBALS['cb_cfg']->config['cookie_path']);
				return $_GET['skin'];
			} elseif (!empty($this->usr_pref_skin_guest) && isSkin($this->usr_pref_skin_guest)) {
				return $this->usr_pref_skin_guest;
			} else {
				return $GLOBALS['cb_cfg']->config['defaultstyle'];
			}
		} else {
			if (isset($_GET['skin']) && isSkin($_GET['skin'])) {
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users
					SET usr_pref_skin=\''.clean($_GET['skin']).'\'
					WHERE usr_id='.$this->userid);
				$this->usr_pref_skin = $_GET['skin'];
				return $_GET['skin'];
			} elseif (!empty($this->usr_pref_skin) && isSkin($this->usr_pref_skin))
				return $this->usr_pref_skin;
			else
				return $GLOBALS['cb_cfg']->config['defaultstyle'];
		}
	}
	
	/* Fonction qui modifie les cookies utilisateur ou les supprime */
	function userCookies ($uname = '',$upass = '', $delay = 31536000) {
		$expire = time() + $delay;
		setcookie('cb_username',$uname,$expire,$GLOBALS['cb_cfg']->config['cookie_path']);
		setcookie('cb_password',$upass,$expire,$GLOBALS['cb_cfg']->config['cookie_path']);
	}
	
	/* Fonction qui initialise toutes les variables nécéssaires. */
	function setVars () {
		if ($this->logged) {
			$ud = $GLOBALS['cb_db']->query('SELECT
					usr_name,usr_nbmp,usr_mod,usr_mpadv,usr_punished,usr_markasread,
					usr_pref_msgs,usr_pref_res,usr_pref_topics,usr_pref_usrs,usr_pref_skin,usr_pref_lang,usr_pref_timezone,usr_pref_ctsummer,
					gr_id,gr_status,gr_mod,gr_mps,gr_auth_flood,gr_auth_see,gr_auth_reply,gr_auth_create
				FROM '.$GLOBALS['cb_db']->prefix.'users
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'groups ON gr_id=usr_class
				WHERE usr_id='.$this->userid);
			$ud = $GLOBALS['cb_db']->fetch_assoc($ud);
			
			$this->punished  = explode('|',$ud['usr_punished']);

			if ($this->punished[0] == 'ban' && ($this->punished[1]+$this->punished[2]>time() || $this->punished[2] == 0)) {
				$_SESSION['logged'] = false;
				$this->logged = false;
				$_SESSION['cb_directory_check'] = '';
				if ($this->punished[2] == 0)
					trigger_error(lang(array('item' => 'error_banned_undef')),E_USER_ERROR);
				else
					trigger_error(lang(array('item' => 'error_banned_time','timeleft' => ($this->punished[1]+$this->punished[2]-time()))),E_USER_ERROR);
			}
			
			$this->username  = $ud['usr_name'];
			$this->userclass = $ud['gr_id'];
			$this->nbmp	  = $ud['usr_nbmp'];
			$this->mod	   = array_filter(explode('/',$ud['usr_mod'].'/'.$ud['gr_mod']));
			$this->mpadv	 = $ud['usr_mpadv'];
			$this->gr_status = $ud['gr_status'];
			$this->gr_mps	= $ud['gr_mps'];
			$this->gr_auth_flood	= $ud['gr_auth_flood'];
			$this->gr_auth_see	  = array_filter(explode('/',$ud['gr_auth_see']));
			$this->gr_auth_reply	= array_filter(explode('/',$ud['gr_auth_reply']));
			$this->gr_auth_create   = array_filter(explode('/',$ud['gr_auth_create']));
			$this->usr_pref_msgs	= $ud['usr_pref_msgs'];
			$this->usr_pref_topics  = $ud['usr_pref_topics'];
			$this->usr_pref_usrs	= $ud['usr_pref_usrs'];
			$this->usr_pref_res	 = $ud['usr_pref_res'];
			$this->usr_pref_skin	= $ud['usr_pref_skin'];
			$this->usr_pref_lang	= $ud['usr_pref_lang'];
			$this->usr_pref_timezone= ($ud['usr_pref_timezone']!='')?$ud['usr_pref_timezone']:$GLOBALS['cb_cfg']->config['timezone'];
			$this->usr_pref_ctsummer= ($ud['usr_pref_timezone']!='')?$ud['usr_pref_ctsummer']:(($GLOBALS['cb_cfg']->config['summertime']=='yes')?1:0);
			$this->mark_as_read		= $ud['usr_markasread'];
		} else {
			require_once(CB_CACHE_CLASSES);
			$this->gr_auth_create = array_filter(explode('/',$GLOBALS['cb_guests_auth']['create']));
			$this->gr_auth_see = array_filter(explode('/',$GLOBALS['cb_guests_auth']['see']));
			$this->gr_auth_reply = array_filter(explode('/',$GLOBALS['cb_guests_auth']['reply']));
			$this->usr_pref_timezone = $GLOBALS['cb_cfg']->config['timezone'];
			$this->usr_pref_ctsummer = ($GLOBALS['cb_cfg']->config['summertime']=='yes')?1:0;
		}
		$this->time_loaded = time();
	}
}
?>