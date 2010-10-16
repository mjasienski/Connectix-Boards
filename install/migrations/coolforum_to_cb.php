<?php
/**
*	Conversion de CoolForum 0.8.x vers Connectix Boards.
*/
if (!defined('CB_INC')) exit('Incorrect access attempt!!');

/* INITIALISATION */
function m_init (&$notices) {
	$_SESSION['cf_codage'] = $GLOBALS['cb_db']->single_result("SELECT valeur FROM ".$_SESSION['params']['migr_prefix']."config WHERE options='chainecodage'");
	
	$_SESSION['dump_maxuser'] = $GLOBALS['cb_db']->single_result('SELECT MAX(userid) FROM '.$_SESSION['params']['migr_prefix'].'user');
	$_SESSION['dump_maxtopic'] = $GLOBALS['cb_db']->single_result('SELECT MAX(idtopic) FROM '.$_SESSION['params']['migr_prefix'].'topics');
	$_SESSION['dump_maxpost'] = $GLOBALS['cb_db']->single_result('SELECT MAX(idpost) FROM '.$_SESSION['params']['migr_prefix'].'posts');
}

/* UTILISATEURS */
function m_u_query () {
	return 
		'SELECT 
			userid AS usr_id,
			login AS usr_name,
			password AS usr_password,
			IF(userstatus=4,1,IF(userstatus=3,2,3)) AS usr_class,
			\'TRUE\' AS usr_registered,
			registerdate AS usr_registertime,
			usermsg AS usr_nbmess,
			usermail AS usr_email,
			usersite AS usr_website,
			usersign AS usr_signature,
			IF(showmail=\'Y\',1,0) AS usr_publicemail,
			lastvisit AS usr_lastconnect,
			lastvisit AS usr_lastaction,
			icq AS usr_icq,
			aim AS usr_aim,
			yahoomsg AS usr_yahoo,
			msn AS usr_msn,
			description AS usr_presentation
		FROM '.$_SESSION['params']['migr_prefix'].'user
		LEFT JOIN '.$_SESSION['params']['migr_prefix'].'userplus ON idplus=userid
		WHERE userid > '.$_SESSION['dump_lastusr'].'
		ORDER BY userid
		LIMIT '.CB_BATCH_USERS;
}
function m_u_process (&$d) {
	$d['usr_name'] = clean(unclean($d['usr_name'],STR_REMOVESPECIALCHARS));
	$d['usr_email'] = clean(unclean($d['usr_email'],STR_REMOVESPECIALCHARS));
	$d['usr_signature'] = clean(bbcodeCfToBbcode(unclean($d['usr_signature'],STR_REMOVESPECIALCHARS)),STR_MULTILINE + STR_PARSEBB);
	$d['usr_presentation'] = clean(bbcodeCfToBbcode(unclean($d['usr_presentation'],STR_REMOVESPECIALCHARS)),STR_MULTILINE + STR_PARSEBB);
	$d['usr_msn'] = clean(unclean($d['usr_msn'],STR_REMOVESPECIALCHARS));
	$d['usr_icq'] = clean(unclean($d['usr_icq'],STR_REMOVESPECIALCHARS));
	$d['usr_aim'] = clean(unclean($d['usr_aim'],STR_REMOVESPECIALCHARS));
	$d['usr_yahoo'] = clean(unclean($d['usr_yahoo'],STR_REMOVESPECIALCHARS));
	$d['usr_website'] = clean(unclean($d['usr_website'],STR_REMOVESPECIALCHARS));
	$d['usr_password'] = md5(getdecrypt(rawurldecode($d['usr_password']), $_SESSION['cf_codage']));
}

/* FORUMS  */
function m_f_query () {
	return 
		'SELECT 
			catid AS forum_id,
			cattitle AS forum_name,
			catorder AS forum_order
		FROM '.$_SESSION['params']['migr_prefix'].'categorie 
		ORDER BY catid';
}
function m_f_process (&$d) {
	$d['forum_name'] = clean(unclean($d['forum_name'],STR_REMOVESPECIALCHARS));
}

/* GROUPES DE SUJETS */
function m_tg_query () {
	return 
		'SELECT
			forumid AS tg_id,
			forumcat AS tg_fromforum,
			forumtitle AS tg_name,
			forumcomment AS tg_comment,
			forumorder AS tg_order,
			lastidpost AS tg_lasttopic,
			forumtopic AS tg_nbtopics,
			forumposts AS tg_nbmess
		FROM '.$_SESSION['params']['migr_prefix'].'forums
		ORDER BY forumid';
}
function m_tg_process (&$d) {
	$d['tg_name'] = clean(unclean($d['tg_name'],STR_REMOVESPECIALCHARS));
	$d['tg_comment'] = clean(unclean($d['tg_comment'],STR_REMOVESPECIALCHARS));
}

/* SUJETS */
function m_t_query() {
	return
		'SELECT
			idtopic AS topic_id,
			idforum AS topic_fromtopicgroup,
			sujet AS topic_name,
			nbrep AS topic_nbreply,
			nbvues AS topic_views,
			idderpost AS topic_lastmessage,
			IF(opentopic=\'N\',1,0) AS topic_status,
			postit AS topic_type,
			idmembre AS topic_starter
		FROM '.$_SESSION['params']['migr_prefix'].'topics 
		WHERE	idtopic > '.$_SESSION['dump_lasttopic'].'
		ORDER BY idtopic LIMIT '.CB_BATCH_TOPICS;
}
function m_t_process (&$d) {
	$d['topic_name'] = clean(unclean($d['topic_name'],STR_REMOVESPECIALCHARS));
}

/* MESSAGES  */
function m_m_query () {
	return 
		'SELECT
			idpost AS msg_id,
			date AS msg_timestamp,
			parent AS msg_topicid,
			msg AS msg_message,
			idmembre AS msg_userid,
			postip AS msg_userip
		FROM '.$_SESSION['params']['migr_prefix'].'posts
		WHERE idpost > '.$_SESSION['dump_lastpost'].'
		ORDER BY idpost LIMIT '.CB_BATCH_MSGS;
}
function m_m_process (&$d) {
	$d['msg_message'] = clean(bbcodeCfToBbcode(unclean($d['msg_message'],STR_REMOVESPECIALCHARS)),STR_MULTILINE + STR_PARSEBB);
	$d['msg_userip'] = ip2long($d['msg_userip']);
}

/* SONDAGES  */
function m_p_manage (&$notices) {
	$rq=$GLOBALS['cb_db']->query('SELECT id,idtopic,question,choix,rep,votants FROM '.$_SESSION['params']['migr_prefix'].'poll p LEFT JOIN '.$_SESSION['params']['migr_prefix'].'topics t ON poll = id');
	while($data = $GLOBALS['cb_db']->fetch_assoc ($rq)) {
		if (!empty($data['idtopic'])) {
			$voted = explode(' >> ','/',$data['votants']);
			$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'polls ( `poll_id` , `poll_question` , `poll_totalvotes` , `poll_white`, `poll_voted`) VALUES ('.$data['id'].', \''.clean(unclean($data['question'],STR_REMOVESPECIALCHARS)).'\', '.count($voted).', 0, \''.implode('/',$voted).'\')');
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_poll='.$data['id'].' WHERE topic_id='.$data['idtopic']);
			
			$poss = explode(' >> ',$data['choix']);
			$res = explode(' >> ',$data['rep']);
			$add = array();
			foreach($poss as $id => $value)
				$add[] = '('.$data['pollid'].', \''.clean(unclean($value),STR_REMOVESPECIALCHARS).'\', '.$res[$id].')';
			if (count($add) > 0) $GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'pollpossibilities ( `poss_pollid` , `poss_name` , `poss_votes` ) VALUES '.implode(',',$add));
		}
	}
}

/* CONFIGURATION */
function m_cf_form (&$form) {}
function m_cf_form_ok () {
	return true;
}
function m_cf_data () {
	return array(
		'forumname' => html_entity_decode($GLOBALS['cb_db']->single_result('SELECT valeur FROM '.$_SESSION['params']['migr_prefix'].'config WHERE options=\'forumname\''),ENT_QUOTES),
		'forumowner' => html_entity_decode($GLOBALS['cb_db']->single_result('SELECT valeur FROM '.$_SESSION['params']['migr_prefix'].'config WHERE options=\'forumname\''),ENT_QUOTES),
		'supportmail' => html_entity_decode($GLOBALS['cb_db']->single_result('SELECT valeur FROM '.$_SESSION['params']['migr_prefix'].'config WHERE options=\'contactmail\''),ENT_QUOTES),
		'website' => html_entity_decode($GLOBALS['cb_db']->single_result('SELECT valeur FROM '.$_SESSION['params']['migr_prefix'].'config WHERE options=\'siteurl\''),ENT_QUOTES)
		);
}

/* FINALISATION */
function m_finished (&$notices) {}

/* FONCTIONS ANNEXES */
//Fonction pour décrypter le mot de passe.
function getdecrypt($tplxt, $cle) {
	$ctr=0;
	$decode='';
	for ($i=0;$i<utf8_strlen($tplxt);$i++) {
		if ($ctr==utf8_strlen($cle))
			$ctr=0;
		$decode.=(utf8_substr($tplxt,$i,1)) ^ (utf8_substr($cle,$ctr,1));
		$ctr++;
	}
	$tmp = '';
	for ($i=0;$i<utf8_strlen($decode);$i+=2)
		$tmp.= (utf8_substr($decode,$i,1))^(utf8_substr($decode,$i+1,1));
	return $tmp;
}
//Fonction pour transformer quelque balises bbcode et quelques smileys (les principaux)
function bbcodeCfToBbcode($texte) {
	$bbcode = array(
		'[ita]' => '[i]',
		'[under]' => '[u]',
		'[bold]' => '[b]',
		'[/ita]' => '[/i]',
		'[/under]' => '[/u]',
		'[/bold]' => '[/b]',
		'::)' => ':)',
		'::(' => ':(', 
		'::o' => ':o',
		'::D' => ':D',
		':;)' => ';)',
		'::P' => ':p',
		':sun:' => 'B)',
		':what:' => ':pigekedal:'
		);
	return str_replace(array_keys($bbcode), $bbcode, $texte);
}
?>