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
if (!defined('CB_ADMIN')) exit('Access denied!');

$sub=(isset($_GET['sub']))?(int)$_GET['sub']:1;

if (isset($_POST['massmail'])) {
	if (isset($_POST['mm_type']) && in_array($_POST['mm_type'],array('mp','mail'))) {
		if (!empty($_POST['mm_groups']) && is_array($_POST['mm_groups']) && count($_POST['mm_groups'])>0) {
			if (isset($_POST['mm_message'],$_POST['mm_subject']) && utf8_strlen(trim($_POST['mm_message'])) > 2 && utf8_strlen(trim($_POST['mm_subject'])) > 2) {
				if ($_POST['mm_type'] == 'mp') {
					$msgtosend = clean($_POST['mm_message'],STR_MULTILINE + STR_PARSEBB);
					$r = $GLOBALS['cb_db']->query('SELECT usr_name,usr_id,usr_email,usr_pref_mailmp FROM '.$GLOBALS['cb_db']->prefix.'users WHERE (usr_registered=\'TRUE\' OR usr_registered LIKE \'change%\') AND usr_class IN ('.implode(',',$_POST['mm_groups']).')');
					$usr_ids = array();
					$patterns = array(
						'{--mail_poster--}'		 =>  $_SESSION['cb_user']->username,
						'{--mail_forumname--}'   =>  $GLOBALS['cb_cfg']->config['forumname'],
						'{--mail_forum_owner--}' =>  $GLOBALS['cb_cfg']->config['forumowner']
						);
					ob_start();
					while ($mpuser = $GLOBALS['cb_db']->fetch_assoc($r)) {
						$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'mp(mp_subj,mp_content,mp_read,mp_to,mp_from,mp_to_del,mp_from_del,mp_timestamp) VALUES(\''.clean($_POST['mm_subject']).'\',\''.str_replace('{username}',$mpuser['usr_name'],$msgtosend).'\',0,'.$mpuser['usr_id'].','.$_SESSION['cb_user']->userid.',0,1,'.time().')');

						if ($mpuser['usr_pref_mailmp']) {
							$patterns['{--mail_user_name--}'] = $mpuser['usr_name'];
							$subject = str_replace('{--mail_forumname--}',$GLOBALS['cb_cfg']->config['forumname'],$GLOBALS['cb_cfg']->config['mailsubject_mp']);
							$mailmsg = str_replace(array_keys($patterns),$patterns,$GLOBALS['cb_cfg']->config['mail_mp']);

							require_once(CB_PATH.'include/lib/lib.mails.php');
							sendMail($mpuser['usr_email'],$subject,$mailmsg);
						}

						$usr_ids[] = $mpuser['usr_id'];
					}
					ob_end_clean();
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'users SET usr_nbmp=usr_nbmp+1,usr_mpadv=1 WHERE usr_id IN ('.implode(',',$usr_ids).')');
					trigger_error(lang('pa_massmail_ok_mp'),E_USER_NOTICE);
				} else {
					require_once(CB_PATH.'include/lib/lib.mails.php');
					$msgtosend = clean($_POST['mm_message'],STR_TODISPLAY + STR_MULTILINE + STR_PARSEBB + STR_PUTLONGURL);
					$r = $GLOBALS['cb_db']->query('SELECT usr_name,usr_email FROM '.$GLOBALS['cb_db']->prefix.'users WHERE (usr_registered=\'TRUE\' OR usr_registered LIKE \'change%\') AND usr_class IN ('.implode(',',$_POST['mm_groups']).') AND usr_email REGEXP \'.*@.*\..*\' AND usr_pref_allowmassmail=1');
					$nb = 0;
					while ($mailuser = $GLOBALS['cb_db']->fetch_assoc($r)) {
						if (!sendMail($mailuser['usr_email'],clean($_POST['mm_subject'],STR_TODISPLAY),str_replace('{username}',$mailuser['usr_name'],$msgtosend),true)) {
							break;
						}
						$nb++;
					}
					if ($nb) trigger_error(lang(array('item' => 'pa_massmail_ok_mail','nb' => $nb)),E_USER_NOTICE);
					else trigger_error(lang('pa_massmail_ko_mail'),E_USER_WARNING);
				}
			} else trigger_error(lang('pa_massmail_nomessage'),E_USER_WARNING);
		} else trigger_error(lang('pa_massmail_nogroups'),E_USER_WARNING);
	}
} elseif (isset($_POST['mail_ci'],$_POST['mailsubject_ci'],$_POST['changeconfirminscr']) && !empty($_POST['mail_ci']) && !empty($_POST['mailsubject_ci'])) {
	$GLOBALS['cb_cfg']->updateElements(array('mail_ci' => clean($_POST['mail_ci'],STR_MULTILINE + STR_PARSEBB),'mailsubject_ci' => clean($_POST['mailsubject_ci'])));
	redirect(manage_url('admin.php','forum-admin.html').'?act=mails&sub=2');
} elseif (isset($_POST['mail_cm'],$_POST['mailsubject_cm'],$_POST['changeconfirmchangemail']) && !empty($_POST['mail_cm']) && !empty($_POST['mailsubject_cm'])) {
	$GLOBALS['cb_cfg']->updateElements(array('mail_cm' => clean($_POST['mail_cm'],STR_MULTILINE + STR_PARSEBB),'mailsubject_cm' => clean($_POST['mailsubject_cm'])));
	redirect(manage_url('admin.php','forum-admin.html').'?act=mails&sub=3');
} elseif (isset($_POST['mail_cp'],$_POST['mailsubject_cp'],$_POST['changeconfirmchangepass']) && !empty($_POST['mail_cp']) && !empty($_POST['mailsubject_cp'])) {
	$GLOBALS['cb_cfg']->updateElements(array('mail_cp' => clean($_POST['mail_cp'],STR_MULTILINE + STR_PARSEBB),'mailsubject_cp' => clean($_POST['mailsubject_cp'])));
	redirect(manage_url('admin.php','forum-admin.html').'?act=mails&sub=4');
} elseif (isset($_POST['mail_tt'],$_POST['mailsubject_tt'],$_POST['changetopictrack']) && !empty($_POST['mail_tt']) && !empty($_POST['mailsubject_tt'])) {
	$GLOBALS['cb_cfg']->updateElements(array('mail_tt' => clean($_POST['mail_tt'],STR_MULTILINE + STR_PARSEBB),'mailsubject_tt' => clean($_POST['mailsubject_tt'])));
	redirect(manage_url('admin.php','forum-admin.html').'?act=mails&sub=5');
} elseif (isset($_POST['mail_mp'],$_POST['mailsubject_mp'],$_POST['changemailmp']) && !empty($_POST['mail_mp']) && !empty($_POST['mailsubject_mp'])) {
	$GLOBALS['cb_cfg']->updateElements(array('mail_mp' => clean($_POST['mail_mp'],STR_MULTILINE + STR_PARSEBB),'mailsubject_mp' => clean($_POST['mailsubject_mp'])));
	redirect(manage_url('admin.php','forum-admin.html').'?act=mails&sub=6');
}

if ($sub == 1) {
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_mails','pa_massmail'));

	$classescontents=array();
	$return = $GLOBALS['cb_db']->query('SELECT gr_id,gr_name,gr_cond FROM '.$GLOBALS['cb_db']->prefix.'groups WHERE gr_cond>-2 ORDER BY gr_cond');
	while ($groupdata = $GLOBALS['cb_db']->fetch_assoc($return))
		$classescontents[] = array('name' => $groupdata['gr_name'],'id' => $groupdata['gr_id']);
	$GLOBALS['cb_tpl']->assign_ref('mm_groups',$classescontents);
	$GLOBALS['cb_tpl']->assign('mm_groups_selected',((!empty($_POST['mm_groups']))?$_POST['mm_groups']:array()));
	$GLOBALS['cb_tpl']->assign('mm_type',((isset($_POST['mm_type']) && $_POST['mm_type']=='mail')?'mail':'mp'));
	$GLOBALS['cb_tpl']->assign('mm_subject',(isset($_POST['mm_subject'])?htmlspecialchars($_POST['mm_subject'],ENT_QUOTES):''));
	$GLOBALS['cb_tpl']->assign('mm_message',(isset($_POST['mm_message'])?htmlspecialchars($_POST['mm_message'],ENT_QUOTES):''));

	$GLOBALS['cb_tpl']->assign('mm_previsualization',false);
	if (isset($_POST['mm_previs'])) {
		$GLOBALS['cb_tpl']->assign('mm_previsualization',true);
		$GLOBALS['cb_tpl']->assign('mm_previs_message',clean($_POST['mm_message'],STR_TODISPLAY + STR_MULTILINE + STR_PARSEBB));
	}

	$GLOBALS['cb_tpl']->assign('m_part','massmail');
} elseif ($sub == 2) {
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_mails','pa_changeconfirminscrmail'));
	$GLOBALS['cb_tpl']->assign('pa_changemail_ci_contents',unclean($GLOBALS['cb_cfg']->config['mail_ci']));
	$GLOBALS['cb_tpl']->assign('pa_mailsubject_ci',$GLOBALS['cb_cfg']->config['mailsubject_ci']);
	$GLOBALS['cb_tpl']->assign('m_part','changeconfirminscr');
} elseif ($sub == 3) {
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_mails','pa_changeconfirmchangemail'));
	$GLOBALS['cb_tpl']->assign('pa_changemail_cm_contents',unclean($GLOBALS['cb_cfg']->config['mail_cm']));
	$GLOBALS['cb_tpl']->assign('pa_mailsubject_cm',$GLOBALS['cb_cfg']->config['mailsubject_cm']);
	$GLOBALS['cb_tpl']->assign('m_part','changeconfirmchangemail');
} elseif ($sub == 4) {
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_mails','pa_changeconfirmchangepass'));
	$GLOBALS['cb_tpl']->assign('pa_changemail_cp_contents',unclean($GLOBALS['cb_cfg']->config['mail_cp']));
	$GLOBALS['cb_tpl']->assign('pa_mailsubject_cp',$GLOBALS['cb_cfg']->config['mailsubject_cp']);
	$GLOBALS['cb_tpl']->assign('m_part','changeconfirmchangepass');
} elseif ($sub == 5) {
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_mails','pa_changetopictrack'));
	$GLOBALS['cb_tpl']->assign('pa_changemail_tt_contents',unclean($GLOBALS['cb_cfg']->config['mail_tt']));
	$GLOBALS['cb_tpl']->assign('pa_mailsubject_tt',$GLOBALS['cb_cfg']->config['mailsubject_tt']);
	$GLOBALS['cb_tpl']->assign('m_part','changetopictrack');
} elseif ($sub == 6) {
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_mails','pa_changemailmp'));
	$GLOBALS['cb_tpl']->assign('pa_changemail_mp_contents',unclean($GLOBALS['cb_cfg']->config['mail_mp']));
	$GLOBALS['cb_tpl']->assign('pa_mailsubject_mp',$GLOBALS['cb_cfg']->config['mailsubject_mp']);
	$GLOBALS['cb_tpl']->assign('m_part','changemailmp');
}
$GLOBALS['cb_tpl']->assign('g_part','admin_mails.php');
?>
