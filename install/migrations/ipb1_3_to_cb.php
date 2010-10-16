<?php
/**
*	Conversion de IPB 1.3 vers Connectix Boards.
*/
if (!defined('CB_INC')) exit('Incorrect access attempt!!');

/* INITIALISATION */
function m_init (&$notices) {
	$_SESSION['dump_maxuser'] = $GLOBALS['cb_db']->single_result('SELECT MAX(id) FROM '.$_SESSION['params']['migr_prefix'].'members');
	$_SESSION['dump_maxtopic'] = $GLOBALS['cb_db']->single_result('SELECT MAX(tid) FROM '.$_SESSION['params']['migr_prefix'].'topics WHERE state != \'link\'');
	$_SESSION['dump_maxpost'] = $GLOBALS['cb_db']->single_result('SELECT MAX(pid) FROM '.$_SESSION['params']['migr_prefix'].'posts');
}

/* UTILISATEURS */
function m_u_query () {
	return 
		'SELECT
			id AS usr_id,
			name AS usr_name,
			password AS usr_password,
			\'TRUE\' AS usr_registered,
			joined AS usr_registertime,
			last_visit AS usr_lastconnect,
			last_visit AS usr_lastaction,
			email AS usr_email,
			msnname AS usr_msn,
			icq_number AS usr_icq,
			aim_name AS usr_aim,
			yahoo AS usr_yahoo,
			IF(hide_email=1,0,1) AS usr_publicemail,
			location AS usr_place,
			website AS usr_website,
			posts AS usr_nbmess,
			IF(mgroup=4,1,3) AS usr_class
		FROM '.$_SESSION['params']['migr_prefix'].'members
		WHERE id > '.$_SESSION['dump_lastusr'].' AND id <> 0
		ORDER BY id
		LIMIT '.CB_BATCH_USERS;
}
function m_u_process (&$d) {
	$d['usr_name'] = escape_string($d['usr_name']);
	$d['usr_email'] = escape_string($d['usr_email']);
	$d['usr_msn'] = escape_string($d['usr_msn']);
	$d['usr_icq'] = escape_string($d['usr_icq']);
	$d['usr_yahoo'] = escape_string($d['usr_yahoo']);
	$d['usr_aim'] = escape_string($d['usr_aim']);
	$d['usr_place'] = escape_string($d['usr_place']);
	$d['usr_website'] = escape_string($d['usr_website']);
}

/* FORUMS  */
function m_f_query () {
	return 
		'SELECT 
			id AS forum_id,
			name AS forum_name,
			position+1 AS forum_order
		FROM '.$_SESSION['params']['migr_prefix'].'categories 
		WHERE id > 0 ORDER BY id';
}
function m_f_process (&$d) {
	$d['forum_name'] = escape_string($d['forum_name']);
}

/* GROUPES DE SUJETS */
function m_tg_query () {
	return 
		'SELECT 
			id AS tg_id,
			name AS tg_name,
			description AS tg_comment,
			IF(parent_id=-1,category,0) AS tg_fromforum,
			IF(parent_id>-1,parent_id,0) AS tg_fromtopicgroup,
			last_id AS tg_lasttopic,
			topics AS tg_nbtopics,
			posts AS tg_nbmess,
			position AS tg_order
		FROM '.$_SESSION['params']['migr_prefix'].'forums 
		ORDER BY id';
}
function m_tg_process (&$d) {
	$d['tg_name'] = escape_string($d['tg_name']);
	$d['tg_comment'] = escape_string($d['tg_comment']);
}

/* SUJETS */
function m_t_query() {
	return
		'SELECT 
			tid AS topic_id,
			title AS topic_name,
			description AS topic_comment,
			views AS topic_views,
			forum_id AS topic_fromtopicgroup,
			posts AS topic_nbreply,
			IF(state=\'closed\',1,0) AS topic_status,
			starter_id AS topic_starter,
			pinned AS topic_type
		FROM '.$_SESSION['params']['migr_prefix'].'topics 
		WHERE state != \'link\' AND tid > '.$_SESSION['dump_lasttopic'].'
		ORDER BY tid LIMIT '.CB_BATCH_TOPICS;
}
function m_t_process (&$d) {
	$d['topic_name'] = escape_string($d['topic_name']);
	$d['topic_comment'] = escape_string($d['topic_comment']);
}

/* MESSAGES  */
function m_m_query () {
	return 
		'SELECT
			pid AS msg_id,
			topic_id AS msg_topicid,
			IF(author_id=-1,0,author_id) AS msg_userid,
			IF(author_id=0,author_name,\'\') AS msg_guest,
			post AS msg_message,
			post_date AS msg_timestamp,
			ip_address AS msg_userip
		FROM '.$_SESSION['params']['migr_prefix'].'posts
		WHERE pid > '.$_SESSION['dump_lastpost'].'
		ORDER BY pid LIMIT '.CB_BATCH_MSGS;
}
function m_m_process (&$d) {
	$d['msg_message'] = escape_string(str_replace('style_emoticons/<#EMO_DIR#>','smileys',$d['msg_message']));
	$d['msg_userip'] = ip2long($d['msg_userip']);
}

/* SONDAGES  */
function m_p_manage (&$notices) {
	$r = $GLOBALS['cb_db']->query("SELECT pid,tid,poll_question,choices,votes FROM ".$_SESSION['params']['migr_prefix']."polls");
	$to_add='';
	while($d = $GLOBALS['cb_db']->fetch_assoc ($r)) {
		$ri = $GLOBALS['cb_db']->query("SELECT member_id FROM ".$_SESSION['params']['migr_prefix']."voters WHERE tid=".$d['tid']);
		$voted = array();
		while($di = $GLOBALS['cb_db']->fetch_assoc ($ri))
			$voted[] = $di['member_id'];
		
		$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'polls ( `poll_id` , `poll_question` , `poll_totalvotes` , `poll_white`, `poll_voted`) VALUES ('.$d['pid'].", '".escape_string($d['poll_question'])."', ".$d['votes'].", 0, '".implode('/',$voted)."')");
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_poll='.$d['pid'].' WHERE topic_id='.$d['tid']);

		$choices = unserialize($d['choices']);
		if (count($choices) > 0) {
			$to_add2='';
			foreach ($choices as $id => $value) {
				$to_add2.=((empty($to_add2))?'':',')."(".$d['pid'].", '".str_replace("'",'&#39;',$value[1])."', ".$value[2].")";
			}
			if (!empty($to_add2)) $GLOBALS['cb_db']->query ("INSERT INTO ".$GLOBALS['cb_db']->prefix."pollpossibilities ( `poss_pollid` , `poss_name` , `poss_votes` ) VALUES ".$to_add2);
		}
	}
}

/* CONFIGURATION */
function m_cf_form (&$form) {
	$form[] = array(
		'title' => 'Paramètres généraux du forum',
		'elements' => array(
			array('Nom du forum','<input type="text" name="forumname" size="18" value="'.((!empty($_POST['forumname']))?htmlspecialchars($_POST['forumname'],ENT_QUOTES):'').'" />'),
			array('Propriétaire du forum','<input type="text" name="forumowner" size="18" value="'.((!empty($_POST['forumowner']))?htmlspecialchars($_POST['forumowner'],ENT_QUOTES):'').'" />'),
			array('Adresse mail de support','<input type="text" name="supportmail" size="18" value="'.((!empty($_POST['supportmail']))?htmlspecialchars($_POST['supportmail'],ENT_QUOTES):'').'" />'),
			array('Langage par défaut',langMenu('defaultlanguage'))
			)
		);
}
function m_cf_form_ok () {
	return (bool)(!empty($_POST['forumname']) && !empty($_POST['supportmail']) && !empty($_POST['forumowner']) && isLang($_POST['defaultlanguage']));
}
function m_cf_data () {
	return array(
		'forumname' => clean($_POST['forumname']),
		'forumowner' => clean($_POST['forumowner']),
		'supportmail' => clean($_POST['supportmail']),
		'defaultlanguage' => $_POST['defaultlanguage']
		);
}

/* FINALISATION */
function m_finished (&$notices) {
	/* Derniers messages des sujets */
	$r = $GLOBALS['cb_db']->query('SELECT MAX(msg_id) AS msgid,msg_topicid FROM '.$GLOBALS['cb_db']->prefix.'messages GROUP BY msg_topicid');
	while($d = $GLOBALS['cb_db']->fetch_assoc ($r))
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_lastmessage='.$d['msgid'].' WHERE topic_id='.$d['msg_topicid']);
}

// Fonction de nettoyage mysql
function escape_string($str) {
	if (function_exists('mysql_real_escape_string')) return mysql_real_escape_string($str);
	else return mysql_escape_string($str);
}
?>
