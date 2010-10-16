<?php
/**
*	Conversion de PunBB 1.2.x vers Connectix Boards.
*/
if (!defined('CB_INC')) exit('Incorrect access attempt!!');

/* INITIALISATION */
function m_init (&$notices) {
	$_SESSION['dump_maxuser'] = $GLOBALS['cb_db']->single_result('SELECT MAX(id) FROM '.$_SESSION['params']['migr_prefix'].'users');
	$_SESSION['dump_maxtopic'] = $GLOBALS['cb_db']->single_result('SELECT MAX(id) FROM '.$_SESSION['params']['migr_prefix'].'topics');
	$_SESSION['dump_maxpost'] = $GLOBALS['cb_db']->single_result('SELECT MAX(id) FROM '.$_SESSION['params']['migr_prefix'].'posts');
	
	$GLOBALS['cb_db']->query('ALTER TABLE '.$GLOBALS['cb_db']->prefix.'users CHANGE `usr_password` `usr_password` VARCHAR( 40 ) NOT NULL');
	$GLOBALS['cb_db']->query('REPLACE INTO '.$GLOBALS['cb_db']->prefix.'config (`cf_field`, `cf_value`) VALUES (\'hash_type\', \'pun\')');
}

/* UTILISATEURS */
function m_u_query () {
	return 
		'SELECT
			id-1 AS usr_id,
			username AS usr_name,
			password AS usr_password,
			\'TRUE\' AS usr_registered,
			registered AS usr_registertime,
			last_visit AS usr_lastconnect,
			last_visit AS usr_lastaction,
			email AS usr_email,
			msn AS usr_msn,
			icq AS usr_icq,
			aim AS usr_aim,
			yahoo AS usr_yahoo,
			IF(email_setting=0,1,0) AS usr_publicemail,
			location AS usr_place,
			url AS usr_website,
			num_posts AS usr_nbmess,
			IF(id=2,1,3) AS usr_class,
			registration_ip AS usr_ip,
			signature AS usr_signature
		FROM '.$_SESSION['params']['migr_prefix'].'users
		WHERE id > '.($_SESSION['dump_lastusr']+1).' AND id != 1
		ORDER BY id LIMIT '.CB_BATCH_USERS;
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
	$d['usr_ip'] = ip2long($d['usr_ip']);
	$d['usr_signature'] = clean($d['usr_signature'],STR_PARSEBB + STR_MULTILINE);
}

/* FORUMS  */
function m_f_query () {
	return 
		'SELECT 
			id AS forum_id,
			cat_name AS forum_name,
			disp_position+1 AS forum_order
		FROM '.$_SESSION['params']['migr_prefix'].'categories 
		ORDER BY id';
}
function m_f_process (&$d) {
	$d['forum_name'] = clean($d['forum_name']);
}

/* GROUPES DE SUJETS */
function m_tg_query () {
	return 
		'SELECT 
			f.id AS tg_id,
			forum_name AS tg_name,
			forum_desc AS tg_comment,
			redirect_url AS tg_link, 
			cat_id AS tg_fromforum,
			topic_id AS tg_lasttopic,
			num_topics AS tg_nbtopics,
			num_posts AS tg_nbmess,
			f.id AS tg_order
		FROM '.$_SESSION['params']['migr_prefix'].'forums AS f 
		LEFT JOIN '.$_SESSION['params']['migr_prefix'].'posts AS p ON p.id=f.last_post_id 
		ORDER BY f.id';
}
function m_tg_process (&$d) {
	$d['tg_name'] = clean($d['tg_name']);
	$d['tg_comment'] = clean($d['tg_comment']);
}

/* SUJETS */
function m_t_query() {
	return
		'SELECT 
			t.id AS topic_id,
			subject AS topic_name,
			num_views AS topic_views,
			forum_id AS topic_fromtopicgroup,
			num_replies AS topic_nbreply,
			last_post_id AS topic_lastmessage,
			closed AS topic_status,
			u.id-1 AS topic_starter,
			sticky AS topic_type
		FROM '.$_SESSION['params']['migr_prefix'].'topics AS t
		LEFT JOIN '.$_SESSION['params']['migr_prefix'].'users AS u ON u.username=t.poster
		WHERE t.id > '.$_SESSION['dump_lasttopic'].'
		ORDER BY t.id LIMIT '.CB_BATCH_TOPICS;
}
function m_t_process (&$d) {
	$d['topic_name'] = clean($d['topic_name']);
}

/* MESSAGES  */
function m_m_query () {
	return 
		'SELECT
			id AS msg_id,
			topic_id AS msg_topicid,
			poster_id-1 AS msg_userid, 
			message AS msg_message,
			posted AS msg_timestamp
		FROM '.$_SESSION['params']['migr_prefix'].'posts
		WHERE id > '.$_SESSION['dump_lastpost'].'
		ORDER BY id LIMIT '.CB_BATCH_MSGS;
}
function m_m_process (&$d) {
	$d['msg_message'] = clean($d['msg_message'],STR_MULTILINE + STR_PARSEBB);
}

/* CONFIGURATION */
function m_cf_form (&$form) {}
function m_cf_form_ok () {
	return true;
}
function m_cf_data () {
	return array(
		'forumname' => $GLOBALS['cb_db']->single_result('SELECT conf_value FROM '.$_SESSION['params']['migr_prefix'].'config WHERE conf_name=\'o_board_title\''),
		'forumowner' => $GLOBALS['cb_db']->single_result('SELECT conf_value FROM '.$_SESSION['params']['migr_prefix'].'config WHERE conf_name=\'o_board_title\''),
		'supportmail' => $GLOBALS['cb_db']->single_result('SELECT conf_value FROM '.$_SESSION['params']['migr_prefix'].'config WHERE conf_name=\'o_admin_email\'')
		);
}

/* SONDAGES  */
function m_p_manage (&$notices) {}

/* FINALISATION */
function m_finished (&$notices) {}
?>