<?php
// Constantes utiles
define('CB_BATCH_USERS',800);
define('CB_BATCH_TOPICS',800);
define('CB_BATCH_MSGS',300);

// Renvoie ce qui a été fait
function installSteps ($todo,&$form,&$errors,&$notices) {
	$s = count($todo);
	$done = array_fill(0,$s,false);

	$i = 0;
	while ($i < $s && count($errors) == 0) {
		$done[$i] = manageStep ($todo[$i],(isset($done[$i-1])?$done[$i-1]:true),$form,$errors,$notices);
		$i++;
	}

	$newToDo = array();
	foreach ($todo as $id => $td) {
		if (!$done[$id]) $newToDo[] = $td;
	}
	return $newToDo;
}
function manageStep ($id,$pre_managed,&$form,&$errors,&$notices) {
	$managed = false;
	switch ($id) {
		/*  INITIALISATION  */
		case 0:
			if (isset($_POST['choose'],$_POST['forum_version']) && $_POST['choose'] == 'migrate' && in_array($_POST['forum_version'],array_keys($GLOBALS['to_migrate']))) {
				$_SESSION['params']['forum'] = $_POST['forum_version'];
				$_SESSION['params']['migr_prefix'] = '';
				$_SESSION['params']['todo']=array(1,21,29,5,22,23,24,25,26,27,28,30,5,31);
				redirect('install.php');
			} elseif (isset($_POST['choose']) && $_POST['choose'] == 'install') {
				$_SESSION['params']['todo']=array(1,3,2,4,5,6);
				redirect('install.php');
			}

			if (!$managed) {
				$sel_forum = '<select name="forum_version">';
				foreach ($GLOBALS['to_migrate'] as $key => $value) {
					$sel_forum .= '<option value="'.$key.'">'.$value.'</option>';
				}
				$sel_forum .= '</select>';

				$form[] = array(
					'title' => 'Choisissez votre type d\'installation',
					'elements' => array(
						array('Installer Connectix Boards','<input type="radio" name="choose" value="install" checked="checked" />'),
						array('Effectuer une migration depuis un autre script de forums: '.$sel_forum,'<input type="radio" name="choose" value="migrate" />')
						)
					);
			}
			break;

		/*  INSTALLATION  */
		case 1: // Création du fichier de configuration et initialisation des tables
			if ($pre_managed && isset($_POST['sqlhost'],$_POST['sqlname'],$_POST['sqlpass'],$_POST['sqldb'],$_POST['prefix'])) {
				if (!@chmod('data',0755))
					$notices[] = 'Le changement des droits sur le dossier "data" a échoué. Veuillez le mettre manuellement en 777.';

				if (@mysql_connect($_POST['sqlhost'],$_POST['sqlname'],$_POST['sqlpass']) !== false ) {
					if (@mysql_select_db($_POST['sqldb']) !== false ) {
						mysql_close();
						if ($file = @fopen('data/settings.php','w')) {
							fputs($file,'<?php'."\n");
							fputs($file,"if (!defined('CB_INC')) exit('Access denied!');\n\n");
							fputs($file,'$this->sqlhost="'.addslashes($_POST['sqlhost']).'";'."\n");
							fputs($file,'$this->sqlusername="'.addslashes($_POST['sqlname']).'";'."\n");
							fputs($file,'$this->sqlpassword="'.addslashes($_POST['sqlpass']).'";'."\n");
							fputs($file,'$this->sqldatabase="'.addslashes($_POST['sqldb']).'";'."\n");
							fputs($file,'$this->prefix="'.addslashes($_POST['prefix']).'";'."\n");
							fputs($file,'?>');
							fclose($file);

							$GLOBALS['cb_db'] = new mysql();
							$return = $GLOBALS['cb_db']->query("SHOW TABLES");
							$found = 0;
							while ($data = $GLOBALS['cb_db']->fetch_row($return)) {
								foreach ($GLOBALS['tables_used'] as $values) {
									if ($GLOBALS['cb_db']->prefix.$values == $data[0]) {
										$errors[] = 'Table préexistante trouvée: '.$GLOBALS['cb_db']->prefix.$values;
										$found++;
									}
								}
							}
							if ($found > 0) {
								$errors[] = 'Certaines tables existent déjà. Si vous voulez uniquement effectuer une mise à jour, rendez vous sur le site officiel (www.connectix-boards.org) pour télécharger le fichier à installer. Si vous voulez faire une nouvelle installation, veuillez changer le préfixe des tables.';
								unlink('data/settings.php');
							} else {
								require_once(CB_PATH.'include/lib/lib.db.php');
								execute_sqlfile('install/sql/tables.sql');
								$managed = true;
							}
						} else $errors[] = 'Impossible d\'écrire dans le fichier data/settings.php ! Veuillez changer ses droits en écriture manuellement.';
					} else $errors[] = 'Mauvais identifiant de base de données.';
				} else $errors[] = 'Impossible de se connecter à MySQL. Vérifiez vos identifiants.';
			}

			if (!$managed) {
				$form[] = array(
					'title' => 'Paramètres MySQL pour Connectix Boards',
					'elements' => array(
						array('Hôte MySQL','<input type="text" name="sqlhost" size="18" value="'.((!empty($_POST['sqlhost']))?htmlspecialchars($_POST['sqlhost'],ENT_QUOTES):'localhost').'" />'),
						array('Nom d\'utilisateur MySQL','<input type="text" name="sqlname" size="18" value="'.((!empty($_POST['sqlname']))?htmlspecialchars($_POST['sqlname'],ENT_QUOTES):'root').'" />'),
						array('Mot de passe MySQL','<input type="password" name="sqlpass" size="18" />'),
						array('Nom de la base de données MySQL','<input type="text" name="sqldb" size="18" value="'.((!empty($_POST['sqldb']))?htmlspecialchars($_POST['sqldb'],ENT_QUOTES):'').'" />'),
						array('Préfixe à utiliser pour les tables','<input type="text" name="prefix" size="18" value="'.((!empty($_POST['prefix']))?htmlspecialchars($_POST['prefix'],ENT_QUOTES):'cb_').'" />')
						)
					);
			}
			break;
		case 2: // Création du compte administrateur
			if ($pre_managed && isset($_POST['adminname'],$_POST['adminpass1'],$_POST['adminpass2'],$_POST['adminmail'])) {
				if (utf8_strlen($_POST['adminname'])>=3) {
					if ($_POST['adminpass1']==$_POST['adminpass2'] && utf8_strlen($_POST['adminpass1'])>3) {
						if (isset($_POST['adminmail']) && !empty($_POST['adminmail'])) {
							$GLOBALS['cb_db'] = new mysql();
							$GLOBALS['cb_db']->query("INSERT INTO ".$GLOBALS['cb_db']->prefix."users(usr_id,usr_name,usr_password,usr_registered,usr_registertime,usr_email,usr_publicemail,usr_class,usr_nbmess) VALUES(1,'".clean($_POST['adminname'])."','".cbHash($_POST['adminpass1'],true)."','TRUE',".time().",'".$_POST['adminmail']."',0,1,1)");
							$managed = true;
						} else $errors[] = 'Entrez une adresse mail valide pour l\'administrateur.';
					} else $errors[] = 'Les mots de passe entrés doivent être identiques et faire au moins 4 caractères.';
				} else $errors[] = 'Le nom de l\'administrateur doit faire au moins 3 caractères.';
			}

			if (!$managed) {
				$form[] = array(
					'title' => 'Compte Administrateur',
					'elements' => array(
						array('Nom','<input type="text" name="adminname" size="18" value="'.((!empty($_POST['adminname']))?htmlspecialchars($_POST['adminname'],ENT_QUOTES):'').'" />'),
						array('Mot de passe','<input type="password" name="adminpass1" size="18" />'),
						array('Confirmer le mot de passe','<input type="password" name="adminpass2" size="18" />'),
						array('Adresse mail','<input type="text" name="adminmail" size="18" value="'.((!empty($_POST['adminmail']))?htmlspecialchars($_POST['adminmail'],ENT_QUOTES):'').'" />')
						)
					);
			}
			break;
		case 3: // Remplissage des tables
			if ($pre_managed && isset($_POST['forumname'],$_POST['supportmail'],$_POST['forumowner'],$_POST['defaultlanguage'])) {
				if (!empty($_POST['forumname']) && !empty($_POST['supportmail']) && !empty($_POST['forumowner']) && isLang($_POST['defaultlanguage'])) {
					$GLOBALS['cb_db'] = new mysql();
					_filltables(array(
						'forumname' => clean($_POST['forumname']),
						'forumowner' => clean($_POST['forumowner']),
						'supportmail' => clean($_POST['supportmail']),
						'defaultlanguage' => $_POST['defaultlanguage']
						));
					$managed = true;
				} else $errors[] = 'Les paramètres généraux du forum n\'ont pas été correctement insérés.';
			}

			if (!$managed) {
				$form[] = array(
					'title' => 'Paramètres généraux',
					'elements' => array(
						array('Nom du forum','<input type="text" name="forumname" size="18" value="'.((!empty($_POST['forumname']))?htmlspecialchars($_POST['forumname'],ENT_QUOTES):'').'" />'),
						array('Propriétaire du forum','<input type="text" name="forumowner" size="18" value="'.((!empty($_POST['forumowner']))?htmlspecialchars($_POST['forumowner'],ENT_QUOTES):'').'" />'),
						array('Adresse mail de support','<input type="text" name="supportmail" size="18" value="'.((!empty($_POST['supportmail']))?htmlspecialchars($_POST['supportmail'],ENT_QUOTES):'').'" />'),
						array('Langage par défaut',langMenu('defaultlanguage'))
						)
					);
			}
			break;
		case 4: // Message de test-démo
			if ($pre_managed) {
				require_once(CB_PATH.'include/lib/lib.search.php');
				$GLOBALS['cb_db']=new mysql();

				$ttl = 'Sujet de test';
				$cmt = 'Posté automatiquement à l&#039;installation !';
				$msg = 'Ceci est un simple message de test, dans un sujet de test.<br />\n<br />\nIls peuvent être modifiés ou supprimés tous les deux.<br />\n<br />\nMerci d&#039;avoir choisi Connectix Boards.';

				$GLOBALS['cb_db']->query("INSERT INTO `".$GLOBALS['cb_db']->prefix."forums` (forum_name,forum_order) VALUES('Forum de test',1)");
				$GLOBALS['cb_db']->query("INSERT INTO `".$GLOBALS['cb_db']->prefix."topicgroups` (tg_name,tg_comment,tg_fromforum,tg_nbtopics,tg_nbmess,tg_lasttopic) VALUES('Groupe de sujets de test','Vous pouvez le modifier dans le panneau d&#039;administration.',1,1,1,1)");

				$GLOBALS['cb_db']->query("INSERT INTO `".$GLOBALS['cb_db']->prefix."topics` (topic_name,topic_comment,topic_fromtopicgroup,topic_starter,topic_nbreply,topic_lastmessage) VALUES('".$ttl."','".$cmt."',1,1,0,1)");
				parseMessageSearch($ttl.' '.$cmt,1);

				$GLOBALS['cb_db']->query("INSERT INTO `".$GLOBALS['cb_db']->prefix."messages` (msg_topicid,msg_userid,msg_message,msg_timestamp) VALUES(1,1,'".$msg."',".time().")");
				parseMessageSearch($msg,1,1);

				$managed = true;
			}
			break;
		case 5: // Fichiers de cache
			if ($pre_managed) {
				$GLOBALS['cb_db'] = new mysql();
				$GLOBALS['cb_cfg'] = new config();
				$smile = new smileysmanager();

				if (!@chmod('avatars',0755) || !@chmod('avatars/gallery',0755) || !@chmod('avatars/temp',0755) || !@chmod('avatars/users',0755))
					$notices[] = 'Le changement des droits sur le dossier "avatars" et ses sous-dossiers a échoué. Veuillez le mettre manuellement en 777.';

				cacheStructure();
				cacheMods();
				cacheClasses();
				$smile->cacheSmileys();
				$GLOBALS['cb_cfg']->cacheConfig();
				
				error_reporting(0);
				if ($GLOBALS['cb_cfg']->config['url_rewrite']=='yes')
					file_put_contents(CB_PATH.'.htaccess',file_get_contents(CB_PATH.'admin/htaccess.txt'));
				error_reporting(E_ALL);

				$managed = true;
			}
			break;
		case 6: // Installation terminée
			if ($pre_managed) {
				$GLOBALS['cb_db'] = new mysql();
				$GLOBALS['cb_cfg'] = new config();
				$GLOBALS['cb_cfg']->updateElements(array('paused' => 'no'));
				$notices[] = 'L\'installation s\'est déroulée avec succès. Merci d\'avoir choisi Connectix Boards.<br /><br />
					<a href="index.php">Index du forum</a><br />
					<a href="admin.php">Panneau d\'administration</a>';
				$managed = true;
			}
			break;

		/*  MISE A JOUR  */
		case 11: // Mise à jour du numéro de version
			if ($pre_managed) {
				$GLOBALS['cb_db'] = new mysql();
				$GLOBALS['cb_db']->query("REPLACE INTO ".$GLOBALS['cb_db']->prefix."config (cf_field,cf_value) VALUES('forumversion','".CUR_VERSION."')");
				$managed = true;
			}
			break;
		case 12: // Appels successifs des fichiers nécessaires à la mise à jour
			if ($pre_managed) {
				$GLOBALS['cb_db'] = new mysql();
				$GLOBALS['cb_cfg'] = new config();

				$begin = false;
				foreach ($GLOBALS['to_upgrade'] as $key => $value) {
					if ($value == $_SESSION['params']['current']) {
						$begin = true;
					}
					if ($begin) {
						$next = isset($GLOBALS['to_upgrade'][$key+1])?$GLOBALS['to_upgrade'][$key+1]:CUR_VERSION;
						require('install/upgrades/up_'.$value.'_'.$next.'.php');

						if ($GLOBALS['skip']) break;
						else $_SESSION['params']['current'] = $next;
					}
				}

				if ($_SESSION['params']['current'] == CUR_VERSION && !$GLOBALS['skip'])
					$managed = true;
			}
			break;
		case 13: // Mise à jour terminée
			if ($pre_managed) {
				$GLOBALS['cb_db'] = new mysql();
				$GLOBALS['cb_cfg'] = new config();
				$GLOBALS['cb_cfg']->updateElements(array('paused' => 'no'));
				$notices[] = 'La mise à jour s\'est déroulée avec succès. Merci d\'avoir choisi Connectix Boards.<br /><br />
					<a href="index.php">Index du forum</a><br />
					<a href="admin.php">Panneau d\'administration</a>';
				$managed = true;
			}
			break;

		/*  MIGRATION  */
		case 21: // Préfixe des tables de l'ancien forum (migration)
			if ($pre_managed && isset($_POST['migr_prefix'])) {
				$_SESSION['params']['migr_prefix'] = $_POST['migr_prefix'];
				$managed = true;
			}

			if (!$managed) {
				$form[] = array(
					'title' => 'Paramètre de la base de données '.$GLOBALS['to_migrate'][$_SESSION['params']['forum']],
					'elements' => array(
						array('Préfixe des tables','<input type="text" name="migr_prefix" size="18" value="'.$GLOBALS['to_migrate_prefix'][$_SESSION['params']['forum']].'" />')
						)
					);
			}
			break;
		case 22: // Initialisation
			if ($pre_managed) {
				if (!isset($_SESSION['dump_lastusr'])) {
					_prerogative();
					m_init($notices);
					$_SESSION['dump_lastusr'] = -1;
					$_SESSION['dump_lasttopic'] = -1;
					$_SESSION['dump_lastpost'] = -1;
					$notices[] = 'Initialisation de la migration en cours.<br />Veuillez patienter...';
					$GLOBALS['skip'] = true;
					$managed = false;
				} else $managed = true;
			}
			break;
		case 23: // Utilisateurs (LIMIT)
			if ($pre_managed) {
				_prerogative();
				
				$q_users = $GLOBALS['cb_db']->query(m_u_query());
				
				$try = false;
				while ($d = $GLOBALS['cb_db']->fetch_assoc($q_users)) {
					$try = true;
					m_u_process($d);
					$GLOBALS['cb_db']->query(_createquery('users',$d));
					$_SESSION['dump_lastusr'] = $d['usr_id'];
				}
				
				if ($try) {
					$notices[] = 'Avancement de la migration: '.number_format(10*$_SESSION['dump_lastusr']/$_SESSION['dump_maxuser']).'%<br />Veuillez patienter...';
					$GLOBALS['skip'] = true;
				} else {
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_markasread='.time());
				}
				$managed = !$try;
			}
			break;
		case 24: // Forums
			if ($pre_managed) {
				_prerogative();
				
				$q_forums = $GLOBALS['cb_db']->query(m_f_query());
				
				while ($d = $GLOBALS['cb_db']->fetch_assoc($q_forums)) {
					m_f_process($d);
					$GLOBALS['cb_db']->query(_createquery('forums',$d));
				}
				
				$managed = true;
			}
			break;
		case 25: //  Groupes de sujets
			if ($pre_managed) {
				_prerogative();
				
				$q_tgs = $GLOBALS['cb_db']->query(m_tg_query());
				
				while ($d = $GLOBALS['cb_db']->fetch_assoc($q_tgs)) {
					m_tg_process($d);
					$GLOBALS['cb_db']->query(_createquery('topicgroups',$d));
				}
				
				$managed = true;
			}
			break;
		case 26: // Sujets (LIMIT)
			if ($pre_managed) {
				_prerogative();
				require_once(CB_PATH.'include/lib/lib.search.php');
				
				$q_topics = $GLOBALS['cb_db']->query(m_t_query());
				
				$try = false;
				while ($d = $GLOBALS['cb_db']->fetch_assoc($q_topics)) {
					$try = true;
					m_t_process($d);
					$GLOBALS['cb_db']->query(_createquery('topics',$d));
					parseMessageSearch($d['topic_name'].(isset($d['topic_comment'])?' '.$d['topic_comment']:''),$d['topic_id']);
					$_SESSION['dump_lasttopic'] = $d['topic_id'];
				}
				
				if ($try) {
					$notices[] = 'Avancement de la migration: '.number_format(10+10*$_SESSION['dump_lasttopic']/$_SESSION['dump_maxtopic']).'%<br />Veuillez patienter...';
					$GLOBALS['skip'] = true;
				}
				$managed = !$try;
			}
			break;
		case 27: // Messages (LIMIT)
			if ($pre_managed) {
				_prerogative();
				require_once(CB_PATH.'include/lib/lib.search.php');
				
				$q_msgs = $GLOBALS['cb_db']->query(m_m_query());
				
				$try = false;
				while ($d = $GLOBALS['cb_db']->fetch_assoc($q_msgs)) {
					$try = true;
					m_m_process($d);
					$GLOBALS['cb_db']->query(_createquery('messages',$d));
					$GLOBALS['cb_db']->query('REPLACE INTO '.$GLOBALS['cb_db']->prefix.'usertopics(ut_userid,ut_topicid,ut_posted) VALUES ('.$d['msg_userid'].','.$d['msg_topicid'].',1)');
					parseMessageSearch($d['msg_message'],$d['msg_topicid'],$d['msg_id']);
					$_SESSION['dump_lastpost'] = $d['msg_id'];
				}
				
				if ($try) {
					$notices[] = 'Avancement de la migration: '.number_format(20+75*$_SESSION['dump_lastpost']/$_SESSION['dump_maxpost']).'%<br />Veuillez patienter...';
					$GLOBALS['skip'] = true;
				}
				$managed = !$try;
			}
			break;
		case 28: // Sondages (stand-alone)
			if ($pre_managed) {
				_prerogative();
				m_p_manage($notices);
				$managed = true;
			}
			break;
		case 29: // Remplissage de la config et des autres tables
			if ($pre_managed) {
				_prerogative();
				if (m_cf_form_ok()) {
					_filltables(m_cf_data());
					$managed=true;
				}
			}
			if (!$managed) {
				require_once(CB_PATH.'install/migrations/'.$_SESSION['params']['forum'].'_to_cb.php');
				m_cf_form($form);
			}
			break;
		case 30: // Statistiques et finalisation
			if ($pre_managed) {
				_prerogative();
				
				m_finished($notices);
				resetStats();
				setAllUsersPostClass();
				
				// Ordre des tg et f
				$q = $GLOBALS['cb_db']->query('SELECT tg_id FROM '.$GLOBALS['cb_db']->prefix.'topicgroups LEFT JOIN '.$GLOBALS['cb_db']->prefix.'forums ON forum_id = tg_fromforum ORDER BY forum_order ASC,tg_order ASC');
				$i = 1;
				while ($r = $GLOBALS['cb_db']->fetch_assoc($q))
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_order='.($i++).' WHERE tg_id='.$r['tg_id']);

				$q = $GLOBALS['cb_db']->query('SELECT forum_id FROM '.$GLOBALS['cb_db']->prefix.'forums ORDER BY forum_order ASC');
				$i = 1;
				while ($r = $GLOBALS['cb_db']->fetch_assoc($q))
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'forums SET forum_order='.($i++).' WHERE forum_id='.$r['forum_id']);
				
				$managed = true;
			}
			break;
		case 31: // Migration terminée
			if ($pre_managed) {
				$GLOBALS['cb_db'] = new mysql();
				$GLOBALS['cb_cfg'] = new config();
				$GLOBALS['cb_cfg']->updateElements(array('paused' => 'no'));
				$notices[] = 'La migration s\'est déroulée avec succés. Veuillez mettre à jour les paramètres du forum dans le panneau d\'administration (droits des utilisateurs, groupes, ...) pour revenir à une situation proche de votre ancien forum.<br />Merci d\'avoir choisi Connectix Boards.<br /><br />
					<a href="index.php">Index du forum</a><br />
					<a href="admin.php">Panneau d\'administration</a>';
				$managed = true;
			}
			break;
	}
	return $managed;
}
function _prerogative () {
	$GLOBALS['cb_db'] = new mysql();
	$GLOBALS['cb_cfg'] = new config();

	require_once(CB_PATH.'install/migrations/'.$_SESSION['params']['forum'].'_to_cb.php');
}
function _createquery ($table,$data) {
	return 'INSERT INTO '.$GLOBALS['cb_db']->prefix.$table.' ('.implode(',',array_keys($data)).') VALUES (\''.implode('\',\'',$data).'\')';
}
function _filltables ($cfg_data) {
	$fname = (!empty($cfg_data['forumname']))?clean($cfg_data['forumname']):'Forum de discussions';
	
	$cfg_basedata = array(
		'forumversion' => CUR_VERSION,
		'forumname' => $fname,
		'forumowner' => '',
		'supportmail' => '',
		'defaultstyle' => CB_SKIN,
		'defaultlanguage' => CB_LANG,
		'connectedlimit' => '10',
		'maxsize' => '140',
		'mail_ci' => "Bonjour {--mail_user_name--},\r\n\r\nCe mail vous est envoyé suite à votre inscription sur {--mail_forumname--}.\r\n\r\nVous pouvez valider votre compte simplement en cliquant sur le lien ci-dessous, ou en le copiant dans votre navigateur favori :\r\n{--mail_confirm_link--}\r\n\r\nVotre nom d&#039;utilisateur est {--mail_user_name--} et votre mot de passe est {--mail_user_password--}.\r\n\r\nMerci beaucoup et à bientôt sur nos forums.\r\n\r\n{--mail_forum_owner--}",
		'mailsubject_cm' => 'Changement du mail de votre compte sur le forum de '.$fname.' !',
		'mail_cm' => "Bonjour {--mail_user_name--},\r\n\r\nCe mail vous est envoyé suite à votre demande de changement d&#039;adresse mail sur {--mail_forumname--}.\r\n\r\nVotre compte ayant été désactivé suite à cette manoeuvre, il vous faut le réactiver en cliquant sur ce lien ou en le copiant dans votre navigateur favori :\r\n{--mail_confirm_link--}\r\n\r\nMerci de votre compréhension et à bientôt sur nos forums.\r\n\r\n{--mail_forum_owner--}",
		'mailsubject_cp' => 'Récupération de vos informations d\'identification ! !',
		'mail_cp' => "Bonjour {--mail_user_name--},\r\n\r\nCe mail vous est envoyé suite à votre demande de récupération d\'informations personnelles sur {--mail_forumname--}. Si cette demande est une erreur, ne tenez pas compte de ce mail.\r\n\r\nLe mot de passe aléatoire qui a été généré est le suivant: {--mail_user_password--}\r\n\r\nVous pouvez valider ce mot de passe simplement en cliquant sur le lien ci-dessous, ou en le copiant dans votre navigateur favori :\r\n{--mail_confirm_link--}\r\n\r\nMerci beaucoup et à bientôt sur nos forums.\r\n\r\n{--mail_forum_owner--}",
		'mailsubject_ci' => 'Validation de votre compte sur le forum de '.$fname.' !',
		'foruminfobot' => '',
		'foruminfobot_dyn' => '',
		'forumrules' => "Pour que l&#039;ambiance du forum reste la meilleure possible, voici quelques règles que tous les utilisateurs devront respecter durant leurs pérégrinations sur Connectix Boards.<br />\r\n<br />\r\nCe forum est destiné à être fréquenté dans le respect mutuel, et toute personne contrevenant à ce principe se verra sanctionnée en conséquence.<br />\r\nLes propos tenus sur ces forums ne peuvent porter sur le racisme, la ségrégation, le piratage, ni faire de la pub pour qui que ce soit.<br />\r\nTout message jugé outrageux par les modérateurs pourra être modifié ou supprimé sans préavis.<br />\r\n<br />\r\nMerci de bien vouloir respecter ces quelques règles.",
		'foruminfotop' => '',
		'foruminfotop_dyn' => '',
		'enablemail' => 'yes',
		'deleteallowed' => 'no',
		'paused' => 'yes',
		'pausemessage' => 'Maintenance du forum.',
		'suspend_register' => 'no',
		'website' => '',
		'bb_sign_forbidden' => '',
		'banned_ips' => '',
		'url_rewrite' => 'no',
		'hash_type' => 'cb',
		'mailsubject_tt' => '{--mail_forumname--} : Nouveau message dans le sujet {--mail_topic_name--}',
		'mail_tt' => "Bonjour {--mail_user_name--},\r\n\r\nUn ou plusieurs nouveau(x) message(s) a (ont) été posté(s) dans le sujet {--mail_topic_name--} depuis votre dernière visite de celui-ci. Pour y accéder, cliquez sur le lien ci-dessous ou copiez le dans votre navigateur favori:\r\n{--mail_topic_link--}\r\n\r\nCe mail vous est envoyé suite à votre demande de suivre un sujet sur {--mail_forumname--}. Si cette demande est une erreur, rendez-vous sur ce sujet et cliquez sur &#39;Ne plus suivre ce sujet&#39;.\r\n\r\nA bientôt sur nos forums,\r\n{--mail_forum_owner--}",
		'floodlimit' => '30',
		'edittopictitle' => 'no',
		'enabletopictrack' => 'yes',
		'displayconnected' => 'yes',
		'displayfastredirect' => 'yes',
		'postguest' => 'no',
		'gzip_output' => 'yes',
		'readornot_sessions' => 'no',
		'pass_salt' => genValidCode(),
		'cookie_path' => '/',
		'mail_mp' => "Bonjour {--mail_user_name--},\r\n\r\nVous avez reçu un (ou plusieurs) nouveau(x) message(s) personnel(s), dont le premier est de la part de {--mail_poster--} sur {--mail_forumname--}.\r\n\r\nPour le(s) consulter, connectez-vous au forum et rendez-vous dans votre panneau de gestion des messages personnels, ou suivez le lien suivant:\r\n{--mail_mp_link--}\r\n\r\nA bientot sur nos forums,\r\n\r\n{--mail_forum_owner--}",
		'mailsubject_mp' => 'Vous avez recu un nouveau MP sur {--mail_forumname--}!'
		);
	
	foreach ($cfg_data as $key => $value) {
		if (!empty($value))
			$cfg_basedata[$key] = $value;
	}
	
	$cfg_input = array();
	foreach($cfg_basedata as $key => $value)
		$cfg_input[] = '(\''.$key.'\',\''.clean($value).'\')';
	
	$GLOBALS['cb_db']->query("REPLACE INTO ".$GLOBALS['cb_db']->prefix."config (cf_field,cf_value) VALUES ".implode(',',$cfg_input));
	$GLOBALS['cb_db']->query("REPLACE INTO ".$GLOBALS['cb_db']->prefix."stats (st_field,st_value) VALUES
		('registered_users', '1'),
		('total_topics', '1'),
		('total_messages', '1'),
		('nb_reports','0')
		");
	$GLOBALS['cb_db']->query("INSERT INTO ".$GLOBALS['cb_db']->prefix."groups
			(gr_id,gr_name,gr_status,gr_cond,gr_color,gr_mps,gr_hide,gr_mod,gr_auth_create,gr_auth_reply,gr_auth_see,gr_auth_flood)
		VALUES
			(1, 'Administrateur', 	  2, -1, 'red',  100, 0, '', '', '', '', 1),
			(2, 'Modérateur', 		  1, -1, 'blue', 100, 0, '', '', '', '', 1),
			(3, 'Nouvel Utilisateur', 0, 0,  '', 	 20,  0, '', '', '', '', 0),
			(4, '', 				  0, -2, '', 	 0,   0, '', '', '', '', 0)");
	require_once(CB_PATH.'include/lib/lib.db.php');
	execute_sqlfile(CB_PATH.'install/sql/smileys.sql');
}
?>