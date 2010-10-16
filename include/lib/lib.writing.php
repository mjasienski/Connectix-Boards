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

//// Fonction d'écriture des messages et sujets dans la base de données ////

/*
* Le tableau $wr_args contient ce qui définit le message:
* - towrite : Le type d'écriture (addmessage, newtopic ou editmessage)
* - wmessage : Le contenu du message
* - wtopictitle : Le titre du sujet (si newtopic ou editmessage)
* - wtopiccomment : Le commentaire du sujet (si newtopic ou editmessage)
* - type : Type du sujet (0: normal, 1: épinglé, 2: annonce) (si newtopic)
* - poll : Booleen disant s'il faut écrire un sondage (si newtopic)
* - pollquestion : Question du sondage (si newtopic)
* - pollpossibilities : Possibilités de réponse au sondage (si newtopic)
* - towriteid : L'id de l'entité dans laquelle écrire (topicgroup ou topic, si newtopic ou addreply)
* - redirect : L'url de la redirection souhaitée, si besoin
* $redir définit s'il faut faire une redirection ou pas
* 
* Renvoie l'id du topic concerné par le message traité
*/
function writeMessage ( $wr_arr , $redir = true ) {
	require_once(CB_PATH.'include/lib/lib.search.php');
	require_once(CB_PATH.'include/lib/lib.users.php');

	/* Variables nécéssaires */
	$toshowafter=''; // Page à afficher après écriture du message
	$topicid=null;

	/* On initialise si l'utilisateur est invité ou non */
	if ($_SESSION['cb_user']->logged) {
		$writerid = $_SESSION['cb_user']->userid;
		$writername = 'NULL';
		$guest = false;
	} else {
		$writerid = 0;
		$writername = '\''.$_SESSION['guest_name'].'\'';
		$guest = true;
	}

	/* Ecriture dans la bdd. */
	if ($wr_arr['towrite']=='newtopic') {
		if (!empty($wr_arr['wtopictitle']) && !empty($wr_arr['wmessage']) && !empty($wr_arr['towriteid'])) {
			$timestamp=time();

			$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'topics(topic_name,topic_comment,topic_starter,topic_guest,topic_type,topic_status,topic_fromtopicgroup)
				VALUES(\''.clean($wr_arr['wtopictitle']).'\',\''.clean($wr_arr['wtopiccomment']).'\','.$writerid.','.$writername.','.$wr_arr['type'].','.(($wr_arr['type']==2)?1:0).','.$wr_arr['towriteid'].')');
			$topicid=$GLOBALS['cb_db']->insert_id();

			parseMessageSearch(clean($wr_arr['wtopictitle'],STR_TODISPLAY).' '.clean($wr_arr['wtopiccomment'],STR_TODISPLAY),$topicid);

			if (isset($wr_arr['poll']) && $wr_arr['poll']) {
				if (isset($wr_arr['pollquestion'],$wr_arr['pollpossibilities'])) {
					writePoll(array(
						'question' => $wr_arr['pollquestion'],
						'possibilities' => $wr_arr['pollpossibilities'],
						'topic_id' => $topicid
						));
				} else redirect();
			}

			$msgcontents = clean($wr_arr['wmessage'],STR_MULTILINE + STR_PARSEBB);
			$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'messages (msg_topicid,msg_userid,msg_guest,msg_userip,msg_message,msg_timestamp,msg_modified,msg_modifieduser) VALUES('.$topicid.','.$writerid.','.$writername.','.ip2long($_SERVER['REMOTE_ADDR']).',\''.$msgcontents.'\','.$timestamp.',0,\'NULL\')');
			$messid=$GLOBALS['cb_db']->insert_id();

			parseMessageSearch($msgcontents,$topicid,$messid);

			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_nbtopics=tg_nbtopics+1,tg_nbmess=tg_nbmess+1,tg_lasttopic='.$topicid.' WHERE tg_id='.$wr_arr['towriteid']);
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_lastmessage='.$messid.' WHERE topic_id='.$topicid);
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value+1 WHERE st_field=\'total_topics\' OR st_field=\'total_messages\'');

			if (!$guest) {
				$GLOBALS['cb_db']->query('REPLACE INTO '.$GLOBALS['cb_db']->prefix.'usertopics(ut_userid,ut_topicid,ut_msgread,ut_posted) VALUES('.$_SESSION['cb_user']->userid.','.$topicid.','.$messid.',1)');
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_nbmess=usr_nbmess+1 WHERE usr_id='.$_SESSION['cb_user']->userid);
				setUserPostClass($_SESSION['cb_user']->userid);
			}

			$_SESSION['flood']=$timestamp;
			$toshowafter=manage_url('index.php?showtopic='.$topicid.'#'.$messid,'forum-t'.$topicid.'.html#'.$messid);
		}
	} elseif ($wr_arr['towrite']=='addmessage') {
		if (!empty($wr_arr['wmessage']) && !empty($wr_arr['towriteid'])) {
			$timestamp=time();
			
			$infos = $GLOBALS['cb_db']->query('SELECT 
					t1.topic_lastmessage AS tlastmess, t1.topic_fromtopicgroup AS ftg,
					t1.topic_name AS tname, t2.topic_lastmessage AS tglastmess
				FROM '.$GLOBALS['cb_db']->prefix.'topics AS t1
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topicgroups ON tg_id = t1.topic_fromtopicgroup 
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topics AS t2 ON tg_lasttopic = t2.topic_fromtopicgroup
				WHERE t1.topic_id='.$wr_arr['towriteid']);
			$infos = $GLOBALS['cb_db']->fetch_assoc($infos);
			$blastm = $infos['tlastmess'];
			$tgid = $infos['ftg'];
			$tname = $infos['tname'];
			$tglastm = $infos['tglastmess'];
			$topicid = $wr_arr['towriteid'];
			
			$msgcontents = clean($wr_arr['wmessage'],STR_MULTILINE + STR_PARSEBB);
			$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'messages (msg_topicid,msg_userid,msg_guest,msg_userip,msg_message,msg_timestamp,msg_modified,msg_modifieduser) VALUES('.$wr_arr['towriteid'].','.$writerid.','.$writername.','.ip2long($_SERVER['REMOTE_ADDR']).',\''.$msgcontents.'\','.$timestamp.',0,\'NULL\')');
			$messid = $GLOBALS['cb_db']->insert_id();

			parseMessageSearch($msgcontents,$wr_arr['towriteid'],$messid);

			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics
				LEFT JOIN '.$GLOBALS['cb_db']->prefix.'topicgroups ON tg_id=topic_fromtopicgroup
				SET topic_nbreply=topic_nbreply+1,topic_lastmessage='.$messid.((isset($wr_arr['status']))?',topic_status = IF(topic_type != 2,'.$wr_arr['status'].',topic_status)':'').'
				WHERE topic_id='.$wr_arr['towriteid']);
			
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topicgroups SET tg_nbmess=tg_nbmess+1,tg_lasttopic='.$wr_arr['towriteid'].' WHERE tg_id='.$tgid);
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'stats SET st_value=st_value+1 WHERE st_field=\'total_messages\'');

			if (!$guest) {
				$mb = $GLOBALS['cb_db']->single_result('SELECT CONCAT(ut_mail,\',\',ut_bookmark) FROM '.$GLOBALS['cb_db']->prefix.'usertopics WHERE ut_userid='.$_SESSION['cb_user']->userid.' AND ut_topicid='.$wr_arr['towriteid']);
				$GLOBALS['cb_db']->query('REPLACE INTO '.$GLOBALS['cb_db']->prefix.'usertopics(ut_userid,ut_topicid,ut_msgread,ut_posted,ut_mail,ut_bookmark) VALUES('.$_SESSION['cb_user']->userid.','.$wr_arr['towriteid'].','.$messid.',1,'.(empty($mb)?'0,0':$mb).')');
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_nbmess=usr_nbmess+1 WHERE usr_id='.$_SESSION['cb_user']->userid);
				setUserPostClass($_SESSION['cb_user']->userid);
			}

			$_SESSION['flood'] = $timestamp;

			// Envoi des mails pour les personnes qui suivent le sujet
			if ($GLOBALS['cb_cfg']->config['enabletopictrack'] == 'yes') {
				$rm = $GLOBALS['cb_db']->query('SELECT SQL_CALC_FOUND_ROWS usr_name,usr_email 
					FROM '.$GLOBALS['cb_db']->prefix.'usertopics 
					LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id = ut_userid 
					LEFT OUTER JOIN '.$GLOBALS['cb_db']->prefix.'usertgs ON utg_userid = ut_userid
					WHERE 
						ut_topicid='.$wr_arr['towriteid'].' 
						AND ut_mail=1 
						AND ut_userid!='.$writerid.' 
						AND usr_email REGEXP \'.*@.*\'
						AND (
							ut_msgread = '.$blastm.'
							OR (utg_markasread IS NOT NULL AND utg_markasread > '.$blastm.')
							OR usr_markasread > '.$blastm.'
						)');
				
				// A adapter -  follow up des groupes de sujets!
				$rmc = $GLOBALS['cb_db']->query('SELECT SQL_CALC_FOUND_ROWS usr_name,usr_email 
					FROM '.$GLOBALS['cb_db']->prefix.'usertgs 
					LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id = utg_userid 
					WHERE 
						utg_tgid='.$tgid.' 
						AND utg_mail=1 
						AND utg_userid!='.$writerid.' 
						AND usr_email REGEXP \'.*@.*\'
						AND (
							(utg_markasread IS NOT NULL AND utg_markasread > '.$blastm.')
							OR usr_markasread > '.$blastm.'
						)');
				
				if ($GLOBALS['cb_db']->single_result('SELECT FOUND_ROWS()')) {
					$patterns=array(
						'{--mail_topic_name--}'  =>  $tname,
						'{--mail_poster--}'		 =>  $_SESSION['cb_user']->username,
						'{--mail_topic_link--}'  =>  'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).((utf8_substr(dirname($_SERVER['PHP_SELF']),-1)!=='/')?'/':'').manage_url('index.php?showtopic='.$wr_arr['towriteid'].'&amp;message='.$messid,'forum-t'.$wr_arr['towriteid'].'-m'.$messid.'.html'),
						'{--mail_forumname--}'   =>  $GLOBALS['cb_cfg']->config['forumname'],
						'{--mail_forum_owner--}' =>  $GLOBALS['cb_cfg']->config['forumowner']
						);
					$subject = str_replace(array('{--mail_forumname--}','{--mail_topic_name--}'),array($GLOBALS['cb_cfg']->config['forumname'],$tname),$GLOBALS['cb_cfg']->config['mailsubject_tt']);
					ob_start();
					while ($dm = $GLOBALS['cb_db']->fetch_assoc($rm)) {
						$patterns['{--mail_user_name--}'] = $dm['usr_name'];
						$mailmsg=str_replace(array_keys($patterns),$patterns,$GLOBALS['cb_cfg']->config['mail_tt']);
						require_once(CB_PATH.'include/lib/lib.mails.php');
						sendMail($dm['usr_email'],$subject,$mailmsg);
					}
					ob_end_clean();
				}
			}

			$toshowafter=manage_url('index.php?showtopic='.$wr_arr['towriteid'].'&message='.$messid,'forum-t'.$wr_arr['towriteid'].'-m'.$messid.'.html');
		}
	} elseif ($wr_arr['towrite']=='editmessage' && !$guest) {
		if (!empty($wr_arr['wmessage']) && !empty($wr_arr['towriteid']) && !empty($wr_arr['toedit'])) {
			$msgcontents = clean($wr_arr['wmessage'],STR_MULTILINE + STR_PARSEBB);
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'messages SET msg_message=\''.$msgcontents.'\''.(($wr_arr['edit_show'])?',msg_modified=\''.time().'\',msg_modifieduser='.$_SESSION['cb_user']->userid:'').' WHERE msg_id='.$wr_arr['toedit']);
			
			parseMessageSearch($msgcontents,$wr_arr['towriteid'],$wr_arr['toedit'],true);

			if (!empty($wr_arr['wtopictitle']))
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_name=\''.clean($wr_arr['wtopictitle']).'\',topic_comment=\''.clean($wr_arr['wtopiccomment']).'\' WHERE topic_id='.$wr_arr['towriteid']);

			$nbmess = $GLOBALS['cb_db']->single_result('SELECT COUNT(*) FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_topicid='.$wr_arr['towriteid'].' AND msg_id<'.$wr_arr['toedit']);
			
			$topicid = $wr_arr['towriteid'];
			$toshowafter=manage_url('index.php?showtopic='.$wr_arr['towriteid'].'&page='.ceil(($nbmess+1)/$_SESSION['cb_user']->usr_pref_msgs).'#'.$wr_arr['toedit'],'forum-t'.$wr_arr['towriteid'].'-p'.ceil(($nbmess+1)/$_SESSION['cb_user']->usr_pref_msgs).'.html#'.$wr_arr['toedit']);
		}
	}

	if ($redir) {/* On détermine la redirection demandée. */
		if (isset($wr_arr['redirect'])) {
			if ($wr_arr['redirect']=='message') {
				/* On laisse le $toshowafter tel quel. */
			} elseif (preg_match('#^tg_[0-9]+$#',$wr_arr['redirect'])) {
				$toshowafter = manage_url('index.php?showtopicgroup='.preg_replace('#^tg_([0-9]+)#','$1',$wr_arr['redirect']),'forum-tg'.preg_replace('#^tg_([0-9]+)#','$1',$wr_arr['redirect']).'.html');
			} elseif (preg_match('#^f_[0-9]+$#',$wr_arr['redirect'])) {
				$toshowafter = manage_url('index.php?showforum='.preg_replace('#^f_([0-9]+)#','$1',$wr_arr['redirect']),'forum-f'.preg_replace('#^f_([0-9]+)#','$1',$wr_arr['redirect']).'.html');
			}
		}

		/* Fin de la page: on redirige... */
		redirect($toshowafter);
	}
	
	return $topicid;
}

//// Ajout de sondages ////

/*
* Le tableau $args doit contenir les paramètres du sondage:
* - question : la question du sondage
* - possibilities : les possibilités de réponse (un tableau de chaines de caractères)
* - topic_id : l'id du topic contenant le sondage
* Les vérifications d'usage sur l'existence ou la conformité (longueur, topic existant,...) des champs ne sont PAS faites ici.
* Par contre, les champs sont nettoyés, donc il ne faut pas le faire avant!
*/
function writePoll($args) {
	// Référence du sondage
	$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'polls(poll_question) VALUES(\''.clean($args['question']).'\')');
	$pollid = $GLOBALS['cb_db']->insert_id();

	// On l'assigne à son topic parent
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_poll='.$pollid.' WHERE topic_id='.$args['topic_id']);
	
	// Création des possibilités de réponse
	$query='INSERT INTO '.$GLOBALS['cb_db']->prefix.'pollpossibilities(poss_pollid,poss_name) VALUES ';
	foreach ($args['possibilities'] as $value)
		$query.='('.$pollid.',\''.clean($value,STR_PARSEBB).'\'),';
	$GLOBALS['cb_db']->query(utf8_substr($query,0,-1));
	
	return $pollid;
}

//// Fonctions de vérification pour l'écriture de messages ////

/* Fonction qui vérifie qu'un message existe. */
function isMess($id) {
	return (bool)$GLOBALS['cb_db']->single_result('SELECT msg_id FROM '.$GLOBALS['cb_db']->prefix.'messages WHERE msg_id='.$id);
}
/* Fonction qui vérifie qu'un topic n'est pas fermé. */
function isClosed($topic) {
	return (bool)$GLOBALS['cb_db']->single_result('SELECT IF(topic_type=2,1,IF(topic_status=1,1,0)) FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_id='.$topic);
}
?>