<?php
/**
*	Conversion de PhpBB 2.0.21 vers Connectix Boards.
*/
if (!defined('CB_INC')) exit('Incorrect access attempt!!');

/* INITIALISATION */
function m_init (&$notices) { 
	$_SESSION['dump_maxuser'] = $GLOBALS['cb_db']->single_result('SELECT MAX(user_id) FROM '.$_SESSION['params']['migr_prefix'].'users');
	$_SESSION['dump_maxtopic'] = $GLOBALS['cb_db']->single_result('SELECT MAX(topic_id) FROM '.$_SESSION['params']['migr_prefix'].'topics WHERE topic_status != 2');
	$_SESSION['dump_maxpost'] = $GLOBALS['cb_db']->single_result('SELECT MAX(post_id) FROM '.$_SESSION['params']['migr_prefix'].'posts');
}

/* UTILISATEURS */
function m_u_query () {
	return 
		'SELECT
			user_id-1 AS usr_id,
			username AS usr_name,
			user_password AS usr_password,
			\'TRUE\' AS usr_registered,
			user_regdate AS usr_registertime,
			user_lastvisit AS usr_lastconnect,
			user_lastvisit AS usr_lastaction,
			user_email AS usr_email,
			user_msnm AS usr_msn,
			user_icq AS usr_icq,
			user_aim AS usr_aim,
			user_yim AS usr_yahoo,
			IF(user_viewemail=0,0,1) AS usr_publicemail,
			user_from AS usr_place,
			user_website AS usr_website,
			user_posts AS usr_nbmess,
			IF(user_level=0,3,user_level) AS usr_class
		FROM '.$_SESSION['params']['migr_prefix'].'users
		WHERE user_active=1 AND user_id != -1 AND user_id > '.($_SESSION['dump_lastusr']+1).'
		ORDER BY user_id
		LIMIT '.CB_BATCH_USERS;
}
function m_u_process (&$d) {
	$d['usr_name'] = clean($d['usr_name']);
	$d['usr_email'] = clean($d['usr_email']);
	$d['usr_msn'] = clean($d['usr_msn']);
	$d['usr_icq'] = clean($d['usr_icq']);
	$d['usr_yahoo'] = clean($d['usr_yahoo']);
	$d['usr_aim'] = clean($d['usr_aim']);
	$d['usr_place'] = clean($d['usr_place']);
	$d['usr_website'] = clean($d['usr_website']);
}

/* FORUMS  */
function m_f_query () {
	return 
		'SELECT 
			cat_id AS forum_id,
			cat_title AS forum_name,
			cat_order+1 AS forum_order
		FROM '.$_SESSION['params']['migr_prefix'].'categories 
		ORDER BY cat_id';
}
function m_f_process (&$d) {
	$d['forum_name'] = clean($d['forum_name']);
}

/* GROUPES DE SUJETS */
function m_tg_query () {
	return 
		'SELECT 
			f.forum_id AS tg_id,
			forum_name AS tg_name,
			forum_desc AS tg_comment,
			cat_id AS tg_fromforum,
			topic_id AS tg_lasttopic,
			forum_topics AS tg_nbtopics,
			forum_posts AS tg_nbmess,
			forum_order AS tg_order
		FROM '.$_SESSION['params']['migr_prefix'].'forums f
		LEFT JOIN '.$_SESSION['params']['migr_prefix'].'posts ON post_id=forum_last_post_id
		ORDER BY f.forum_id';
}
function m_tg_process (&$d) {
	$d['tg_name'] = clean($d['tg_name']);
	$d['tg_comment'] = clean($d['tg_comment']);
}

/* SUJETS */
function m_t_query() {
	return
		'SELECT 
			topic_id,
			topic_title AS topic_name,
			topic_views,
			forum_id AS topic_fromtopicgroup,
			topic_replies AS topic_nbreply,
			topic_last_post_id AS topic_lastmessage,
			topic_status,
			topic_poster-1 AS topic_starter
		FROM '.$_SESSION['params']['migr_prefix'].'topics 
		WHERE topic_status != 2 AND topic_id > '.$_SESSION['dump_lasttopic'].' 
		ORDER BY topic_id LIMIT '.CB_BATCH_TOPICS;
}
function m_t_process (&$d) {
	$d['topic_name'] = clean($d['topic_name']);
}

/* MESSAGES  */
function m_m_query () {
	return 
		'SELECT
			p.post_id AS msg_id,
			topic_id AS msg_topicid,
			poster_id-1 AS msg_userid,
			REPLACE(post_text,CONCAT(\':\',bbcode_uid),\'\') AS msg_message,
			post_time AS msg_timestamp,
			poster_ip AS msg_userip
		FROM '.$_SESSION['params']['migr_prefix'].'posts p
		LEFT JOIN '.$_SESSION['params']['migr_prefix'].'posts_text pt ON pt.post_id = p.post_id
		WHERE p.post_id > '.$_SESSION['dump_lastpost'].'
		ORDER BY p.post_id LIMIT '.CB_BATCH_MSGS;
}
function m_m_process (&$d) {
	$d['msg_message'] = clean(html_entity_decode($d['msg_message'],ENT_QUOTES),STR_MULTILINE + STR_PARSEBB);
	$d['msg_userip'] = hexdec($d['msg_userip']);
}

/* SONDAGES  */
function m_p_manage (&$notices) {
	$rq=$GLOBALS['cb_db']->query('SELECT vote_id,topic_id,vote_text,vote_start FROM '.$_SESSION['params']['migr_prefix'].'vote_desc');
	while($data = $GLOBALS['cb_db']->fetch_assoc ($rq)) {
		$r = $GLOBALS['cb_db']->query('SELECT vote_user_id FROM '.$_SESSION['params']['migr_prefix'].'vote_voters WHERE vote_id='.$data['vote_id']);
		$voted = array();
		while($d = $GLOBALS['cb_db']->fetch_assoc ($r))
			$voted[]= ($d['vote_user_id']-1);

		$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'polls ( `poll_id` , `poll_question` , `poll_totalvotes` , `poll_white`, `poll_voted`) VALUES ('.$data['vote_id'].', \''.clean($data['vote_text']).'\', '.count($voted).', 0, \''.implode('/',$voted).'\')');
		$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'topics SET topic_poll='.$data['vote_id'].' WHERE topic_id='.$data['topic_id']);
	}
	$rq=$GLOBALS['cb_db']->query('SELECT vote_id,vote_option_text,vote_result FROM '.$_SESSION['params']['migr_prefix'].'vote_results');
	$add = array();
	while($data = $GLOBALS['cb_db']->fetch_assoc ($rq))
		$add[] = '('.$data['vote_id'].', \''.clean($data['vote_option_text']).'\', '.$data['vote_result'].')';
	if (count($add) > 0) $GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'pollpossibilities ( `poss_pollid` , `poss_name` , `poss_votes` ) VALUES '.implode(',',$add));
}

/* CONFIGURATION */
function m_cf_form (&$form) {}
function m_cf_form_ok () {
	return true;
}
function m_cf_data () {
	return array(
		'forumname' => $GLOBALS['cb_db']->single_result('SELECT config_value FROM '.$_SESSION['params']['migr_prefix'].'config WHERE config_name=\'sitename\''),
		'forumowner' => $GLOBALS['cb_db']->single_result('SELECT config_value FROM '.$_SESSION['params']['migr_prefix'].'config WHERE config_name=\'board_email_sig\''),
		'supportmail' => $GLOBALS['cb_db']->single_result('SELECT config_value FROM '.$_SESSION['params']['migr_prefix'].'config WHERE config_name=\'board_email\'')
		);
}

/* FINALISATION */
function m_finished (&$notices) {}
?>