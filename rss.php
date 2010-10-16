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

require('common.php');
require(CB_PATH.'include/lib/class.rssflow.php');

$GLOBALS['cb_tpl']->lang_load('rss.lang');

if (isset($_GET['showtopic'])) {
	/* Sujet en RSS (15 derniers messages) */
	$return=$GLOBALS['cb_db']->query('SELECT topic_comment,topic_name,topic_id FROM '.$GLOBALS['cb_db']->prefix.'topics WHERE topic_id='.(int)$_GET['showtopic']);

	if ($topic = $GLOBALS['cb_db']->fetch_assoc($return)) {
		if($_SESSION['cb_user']->getAuth('see',$topic['topic_id'])) {
			$rss = new rssflow($GLOBALS['cb_cfg']->config['forumname'].' - '.$topic['topic_name'],'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/'.manage_url('index.php?showtopic='.(int)$_GET['showtopic'],'forum-t'.(int)$_GET['showtopic'].','.rewrite_words($topic['topic_name']).'.html'),$topic['topic_comment'],'Topic RSS');

			$ret = $GLOBALS['cb_db']->query('SELECT msg_id,msg_timestamp,usr_name,msg_message,msg_guest,msg_userid
					FROM '.$GLOBALS['cb_db']->prefix.'messages
					LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users ON usr_id=msg_userid
					WHERE '.$GLOBALS['cb_db']->prefix.'messages.msg_topicid='.(int)$_GET['showtopic'].'
					ORDER BY msg_id DESC LIMIT 15');
			
			while ($messages = $GLOBALS['cb_db']->fetch_assoc($ret)) {
				$rss->addItem(
					lang('rss_replyby').' '.($messages['msg_userid'] != 0)?$messages['usr_name']:$messages['msg_guest'].' '.lang('rss_date').' '.dateFormat($messages['msg_timestamp']),
					'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).((utf8_substr(dirname($_SERVER['PHP_SELF']),-1)!=='/')?'/':'').manage_url('index.php?showtopic='.(int)$_GET['showtopic'].'&amp;message='.$messages['msg_id'],'forum-t'.(int)$_GET['showtopic'].'-m'.$messages['msg_id'].'.html'),
					truncate(strip_tags($messages['msg_message']),120),
					$messages['msg_timestamp'],
					($messages['msg_userid'] != 0)?$messages['usr_name']:$messages['msg_guest']
					);
			}

			$rss->sendFlow();
		}
	}
} elseif (isset($_GET['showtopicgroup'])) {
	/* Groupe de sujets en RSS (15 derniers sujets). */
	$return = $GLOBALS['cb_db']->query('SELECT tg_id,tg_name,tg_comment
			FROM '.$GLOBALS['cb_db']->prefix.'topicgroups
			WHERE '.$GLOBALS['cb_db']->prefix.'topicgroups.tg_id='.(int)$_GET['showtopicgroup']);

	if ($topicgroup = $GLOBALS['cb_db']->fetch_assoc($return)) {
		if($_SESSION['cb_user']->getAuth('see',$topicgroup['tg_id'])) {
			$rss = new rssflow($GLOBALS['cb_cfg']->config['forumname'].' - '.$topicgroup['tg_name'],'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/'.manage_url('index.php?showtopicgroup='.(int)$_GET['showtopicgroup'],'forum-tg'.(int)$_GET['showtopicgroup'].','.rewrite_words($topicgroup['tg_name']).'.html'),$topicgroup['tg_comment'],'TopicGroup RSS');

			$ret=$GLOBALS['cb_db']->query('SELECT topic_id,topic_name,topic_fromtopicgroup,topic_comment,topic_nbreply,topic_starter,topic_guest,msg_id,msg_timestamp,msg_userid,msg_guest,startusers.usr_name AS usr_name_start,lastusers.usr_name AS usr_name_last
					FROM '.$GLOBALS['cb_db']->prefix.'topics
					LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users AS startusers ON startusers.usr_id=topic_starter
					LEFT JOIN '.$GLOBALS['cb_db']->prefix.'messages ON msg_id=topic_lastmessage
					LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users AS lastusers ON lastusers.usr_id=msg_userid
					WHERE topic_fromtopicgroup='.(int)$_GET['showtopicgroup'].'
					ORDER BY topic_lastmessage DESC
					LIMIT 15'
					);
			
			while ($topics=$GLOBALS['cb_db']->fetch_assoc($ret)) {
				$rss->addItem(
					$topics['topic_name'].((!empty($topics['topic_comment']))?', '.$topics['topic_comment']:''),
					'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).((utf8_substr(dirname($_SERVER['PHP_SELF']),-1)!=='/')?'/':'').manage_url('index.php?showtopic='.$topics['topic_id'].'&amp;message='.$topics['msg_id'],'forum-t'.$topics['topic_id'].'-m'.$topics['msg_id'].'.html'),
					lang('rss_subjectby').' '.(($topics['topic_starter']!=0)?$topics['usr_name_start']:$topics['topic_guest']).', '.lang('rss_containing').' '.$topics['topic_nbreply'].' '.(($topics['topic_nbreply']==1||$topics['topic_nbreply']==0)?lang('rss_reply'):lang('rss_replies')).'. '.lang('rss_last_message').' '.(($topics['msg_userid']!=0)?$topics['usr_name_last']:$topics['msg_guest']).' '.lang('rss_date').' '.dateFormat($topics['msg_timestamp']),
					$topics['msg_timestamp'],
					($topics['msg_userid']!=0)?$topics['usr_name_last']:$topics['msg_guest']
					);
			}

			$rss->sendFlow();
		}
	}
} elseif (isset($_GET['showall'])) {
	/* Tout le forum en RSS (15 derniers sujets). */
	$rss = new rssflow($GLOBALS['cb_cfg']->config['forumname'],'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/'.manage_url('index.php','forum.html'),$GLOBALS['cb_cfg']->config['forumname'].' forum RSS flow',$GLOBALS['cb_cfg']->config['forumname'].' RSS flow');

	$where='';
	if (count($_SESSION['cb_user']->gr_auth_see) > 0)
		$where=' WHERE topic_fromtopicgroup NOT IN ('.implode(',',$_SESSION['cb_user']->gr_auth_see).')';

	$ret=$GLOBALS['cb_db']->query('SELECT topic_id,topic_name,topic_fromtopicgroup,topic_comment,topic_nbreply,msg_timestamp,startusers.usr_name AS usr_name_start,lastusers.usr_name AS usr_name_last
			FROM '.$GLOBALS['cb_db']->prefix.'topics
			LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users AS startusers ON startusers.usr_id=topic_starter
			LEFT JOIN '.$GLOBALS['cb_db']->prefix.'messages ON msg_id=topic_lastmessage
			LEFT JOIN '.$GLOBALS['cb_db']->prefix.'users AS lastusers ON lastusers.usr_id=msg_userid
			'.$where.'
			ORDER BY topic_lastmessage DESC
			LIMIT 15'
			);
	
	while ($topics=$GLOBALS['cb_db']->fetch_assoc($ret)) {
		$rss->addItem(
			$topics['topic_name'].((!empty($topics['topic_comment']))?', '.$topics['topic_comment']:''),
			'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).((utf8_substr(dirname($_SERVER['PHP_SELF']),-1)!=='/')?'/':'').manage_url('index.php?showtopic='.$topics['topic_id'],'forum-t'.$topics['topic_id'].','.rewrite_words($topics['topic_name']).'.html'),
			lang('rss_subjectby').' '.$topics['usr_name_start'].', '.lang('rss_containing').' '.$topics['topic_nbreply'].' '.(($topics['topic_nbreply']==1||$topics['topic_nbreply']==0)?lang('rss_reply'):lang('rss_replies')).'. '.lang('rss_last_message').' '.$topics['usr_name_last'].' '.lang('rss_date').' '.dateFormat($topics['msg_timestamp']),
			$topics['msg_timestamp'],
			$topics['usr_name_last']
			);
	}

	$rss->sendFlow();
} else redirect();
?>