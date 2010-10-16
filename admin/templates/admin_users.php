<?php if ($usr_part == "adduser"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_delete_user'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="delete_user" maxlength="60" size="30" />
		</div>
		<div class="centerforminput">
			<input type="radio" name="deleteuser_msg" value="name" class="radiobutton" checked="checked" /> <?php echo lang('pa_deleteuser_msg_name'); ?><br />
			<input type="radio" name="deleteuser_msg" value="guest" class="radiobutton" /> <?php echo lang('pa_deleteuser_msg_guest'); ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="deleteaccount" value="<?php echo lang('pa_deleteaccount'); ?>" /><br />
			<?php echo lang('pa_deleteaccount_mess'); ?>
		</div>
		
		<br /><br />
		
		<div class="centerformtext">
			<?php echo lang('pa_create_user'); ?>
		</div>
		<div class="centerformtext">
			<?php echo lang('username'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="username" maxlength="60" size="30" />
		</div>
		<div class="centerformtext">
			<?php echo lang('password'); ?>
		</div>
		<div class="centerforminput">
			<input type="password" name="password1" maxlength="60" size="30" />
		</div>
		<div class="centerformtext">
			<?php echo lang('password_confirm'); ?>
		</div>
		<div class="centerforminput">
			<input type="password" name="password2" maxlength="60" size="30" />
		</div>
		<div class="centerformtext">
			<?php echo lang('mail'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="email" maxlength="60" size="30" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_putuserinclass_wantedclass'); ?>
		</div>
		<div class="centerforminput">
			<?php echo $pa_users_add_classmenu; ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="createaccount" value="<?php echo lang('pa_createaccount'); ?>" /><br />
			<?php echo lang('pa_createaccount_mess'); ?>
		</div>
<?php elseif ($usr_part == "changeuser"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_putuserinclass_title'); ?>
		</div>
		<div class="centerforminput">
			<input type="radio" name="selectuser_type" value="id" class="radiobutton" checked="checked" /> <?php echo lang('pa_selectuser_id'); ?>&nbsp;&nbsp;&nbsp;<input type="text" name="selectuser_id" size="10" /><br />
			<input type="radio" name="selectuser_type" value="name" class="radiobutton" /> <?php echo lang('pa_selectuser_name'); ?>&nbsp;&nbsp;&nbsp;<input type="text" name="selectuser_name" size="30" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_putuserinclass_wantedclass'); ?>
		</div>
		<div class="centerforminput">
			<?php echo $pa_users_showclasses_putuser_classmenu; ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="changeclass" value="<?php echo lang('pa_putuserinclass_confirm'); ?>" />
		</div>
<?php elseif ($usr_part == "renameuser"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_renameuser_title'); ?>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_renameuser_oldname'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="renameuser_old" size="35" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_renameuser_newname'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="renameuser_new" size="35" />
		</div>
		<div class="centerformtext">
			<input type="submit" name="renameuser" value="<?php echo lang('pa_renameuser_confirm'); ?>" />
		</div>
<?php elseif ($usr_part == "showclasses"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_classes'); ?>
		</div>
		<div class="sublink">
			<a href="<?php echo manage_url('admin.php','forum-admin.html').'?act=users&amp;sub=3'; ?>"><?php echo lang('pa_submenu_users_createclasses'); ?></a>
		</div>
		<div class="centerforminput">
			<table class="table" border="0" cellspacing="1" cellpadding="4">
				<tr class="titlerow">
					<td class="group_title">
						<?php echo lang('pa_existingclasses'); ?>
					</td>
					<td class="group_options">
						<?php echo lang('pa_options'); ?>
					</td>
					<td class="group_cond">
						<?php echo lang('pa_condition'); ?>
					</td>
					<td class="group_hide">
						<?php echo lang('pa_hide'); ?>
					</td>
				</tr>

				<?php foreach ($pa_users_showclasses_classes as $class): ?>
				<tr>
					<td class="group_title">
						<?php echo $class['name']; ?>
					</td>
					<td class="group_options">
						<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=users&amp;sub=3&amp;edit=<?php echo $class['id']; ?>"><?php echo lang('edit'); ?></a><?php if ($class['id'] != 1): ?> - <a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=users&amp;sub=2&amp;delgroup=<?php echo $class['id']; ?>"><?php echo lang('delete'); ?></a><?php endif; ?>
					</td>
					<td class="group_cond">
						<?php echo $class['cond']; ?>
					</td>
					<td class="group_hide">
						<input type="checkbox" name="hide[]" value="<?php echo $class['id']; ?>" <?php if ($class['hide']): ?>checked="checked"<?php endif; ?> />
					</td>
				</tr>
				<?php endforeach; ?>

			</table>
		</div>
		<div class="centerformtext">
			<input type="submit" name="hide_confirm" value="<?php echo lang('confirm'); ?>" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_hide_note'); ?>
		</div>
		<br /><br />
		<div class="centerformtext">
			<?php echo lang('pa_ranks'); ?>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_ranks_info'); ?>
		</div>
		<div class="sublink">
			<a href="<?php echo manage_url('admin.php','forum-admin.html').'?act=users&amp;sub=10'; ?>"><?php echo lang('pa_ranks_create'); ?></a>
		</div>
		<div class="centerforminput">
			<table class="table" border="0" cellspacing="1" cellpadding="4">
				<tr class="titlerow">
					<td class="rank_title">
						<?php echo lang('pa_ranks_name'); ?>
					</td>
					<td class="rank_posts">
						<?php echo lang('pa_ranks_posts'); ?>
					</td>
					<td class="rank_options">
						<?php echo lang('pa_options'); ?>
					</td>
				</tr>

				<?php foreach ($pa_users_showclasses_ranks as $rank): ?>
				<tr>
					<td class="rank_title">
						<?php echo $rank['name']; ?>
					</td>
					<td class="rank_posts">
						<?php echo $rank['posts']; ?>
					</td>
					<td class="rank_options">
						<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=users&amp;sub=10&amp;edit=<?php echo $rank['id']; ?>"><?php echo lang('edit'); ?></a> - <a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=users&amp;sub=2&amp;delrank=<?php echo $rank['id']; ?>"><?php echo lang('delete'); ?></a>
					</td>
				</tr>
				<?php endforeach; ?>

			</table>
		</div>
<?php elseif ($usr_part == "editclasses"): ?>
		<?php if ($pa_editing): ?>
		<div class="centerformtext">
			<?php echo lang('warning'); ?><br />
			<?php echo lang(array('item' => 'pa_editingclass', 'name' => $pa_editing_name, 'id' => $pa_editing_id)); ?>
		</div>
		<?php endif; ?>
		<div class="centerformtext">
			<?php echo lang('pa_classtitle'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="classtitle" maxlength="60" size="30" value="<?php echo $pa_users_editclass_classtitle; ?>" /><br /><?php echo lang('pa_classtitle_edit'); ?>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_classcolor'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="classcolor" maxlength="60" size="30" value="<?php echo $pa_users_editclass_classcolor; ?>" /><br /><?php echo lang('pa_classcolor_infos'); ?>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_classimage'); ?>
		</div>
		<div class="centerforminput">
			<?php echo lang('pa_classimage_infos'); ?>
		</div>
		<?php if (!$pa_editing || ($pa_editing && $pa_editing_id != 3)): ?>
		<div class="centerformtext">
			<?php echo lang('pa_classcond'); ?>
		</div>
		<div class="centerforminput">
			<input type="radio" class="radiobutton" name="condition" value="posts" <?php echo $pa_users_editclass_classcond_posts_checked; ?> /> <?php echo lang(array('item' => 'pa_classcond_posts', 'X' => $pa_users_editclass_classcond_posts_input)); ?><br />
			<input type="radio" class="radiobutton" name="condition" value="admin" <?php echo $pa_users_editclass_classcond_admin_checked; ?> /> <?php echo lang('pa_classcond_admin'); ?>
		</div>
		<?php endif; ?>
		<div class="centerformtext">
			<?php echo lang('pa_canflood'); ?>
		</div>
		<div class="centerforminput">
			<input type="checkbox" name="canflood" <?php echo $pa_users_editclass_canflood_checked; ?> />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_mpallowed'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="numbermps" size="5" value="<?php echo $pa_users_editclass_numbermps; ?>" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_classtype'); ?>
		</div>
		<div class="centerforminput">
			<input type="radio" class="radiobutton" name="type" value="normal" <?php echo $pa_users_editclass_classtype_menu_normal_checked; ?> /> <?php echo lang('normal'); ?><br />
			<input type="radio" class="radiobutton" name="type" value="mod"	<?php echo $pa_users_editclass_classtype_menu_mod_checked; ?> /> <?php echo lang('moderator'); ?><br />
			<input type="radio" class="radiobutton" name="type" value="admin"  <?php echo $pa_users_editclass_classtype_menu_admin_checked; ?> /> <?php echo lang('admin'); ?>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_classauth'); ?>
		</div>
		<div class="centerforminput">
			<table class="table" border="0" cellspacing="1" cellpadding="4">
				<tr class="titlerow">
					<td class="group_auth_title">
						<?php echo lang('pa_auth_title'); ?>
					</td>
					<td class="group_auth_see">
						<?php echo lang('pa_auth_see'); ?>
					</td>
					<td class="group_auth_reply">
						<?php echo lang('pa_auth_reply'); ?>
					</td>
					<td class="group_auth_create">
						<?php echo lang('pa_auth_create'); ?>
					</td>
				</tr>
				<?php foreach ($pa_auth_topicgroups as $tg): ?>
				<tr>
					<td class="group_auth_title">
						<?php echo $tg['tg_name']; ?>
					</td>
					<td class="group_auth_see">
						<input type="checkbox" id="see_<?php echo $tg['tg_id']; ?>" onclick="javascript:authfunc('see',<?php echo $tg['tg_id']; ?>);" name="see_<?php echo $tg['tg_id']; ?>"<?php if ($tg['see_checked']): ?> checked="checked"<?php endif; ?> />
					</td>
					<td class="group_auth_reply">
						<input type="checkbox" id="reply_<?php echo $tg['tg_id']; ?>" onclick="javascript:authfunc('reply',<?php echo $tg['tg_id']; ?>);" name="reply_<?php echo $tg['tg_id']; ?>"<?php if ($tg['reply_checked']): ?> checked="checked"<?php endif; ?> />
					</td>
					<td class="group_auth_create">
						<input type="checkbox" id="create_<?php echo $tg['tg_id']; ?>" onclick="javascript:authfunc('create',<?php echo $tg['tg_id']; ?>);" name="create_<?php echo $tg['tg_id']; ?>"<?php if ($tg['create_checked']): ?> checked="checked"<?php endif; ?> />
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
		</div>
		<div class="centerformtext">
			<input type="submit" name="createclass" value="<?php echo lang($pa_users_editclass_submit); ?>" /><br />
			<?php if ($pa_editing): ?><?php echo lang(array('item' => 'pa_editclass_confirm_info', 'name' => $pa_users_editclass_classtitle)); ?><?php endif; ?>
		</div>
<?php elseif ($usr_part == "moderators"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_modusers_expl'); ?>
		</div>
		<div class="centerforminput">
			<?php if (count($pa_m_corr) > 0): ?>
			<table class="modtable" cellspacing="1" cellpadding="4">
				<tr class="titlerow">
					<td><?php echo lang('username'); ?></td>
					<?php echo $pa_m_cols; ?>
				</tr>
				<?php foreach ($pa_m_modtable as $m): ?>
				<?php if (isset($m['uid'])): ?>
				<tr class="field-<?php echo $m['grid']; ?>">
					<td>
						<a href="<?php echo manage_url('index.php?act=user&amp;showprofile='.$m['uid'],'forum-m'.$m['uid'].','.rewrite_words($m['uname']).'.html'); ?>"><?php echo $m['uname']; ?></a>
					</td>
					<?php foreach ($pa_m_corr as $tgid => $foo): ?>
					<td id="<?php echo 'c'.$tgid.'-'.$m['grid'].'-'.$m['uid']; ?>" class="<?php echo (in_array($tgid,$m['grcheck'])?'modusergr':''); ?>">
						<input type="checkbox" name="usr<?php echo $m['uid']; ?>[]" value="<?php echo $tgid; ?>" id="<?php echo 'u'.$tgid.'-'.$m['grid'].'-'.$m['uid']; ?>" class="<?php echo 'u'.$tgid.'-'.$m['grid']; ?>" onclick="userCl(<?php echo $m['uid'].','.$m['grid'].','.$tgid; ?>);" <?php if (in_array($tgid,$m['tocheck'])): ?>checked="checked"<?php endif; ?> />
					</td>
					<?php endforeach; ?>
				</tr>
				<?php else: ?>
				<tr class="field">
					<td class="modgroup">
						<a onclick="hideAndShowC('field-<?php echo $m['grid']; ?>');"><?php echo $m['grname']; ?>
					</td>
					<?php foreach ($pa_m_corr as $tgid => $foo): ?>
					<td id="<?php echo 'cr'.$tgid.'-'.$m['grid']; ?>" class="<?php echo (in_array($tgid,$m['tocheck'])?'modgroupch':'modgroup'); ?>">
						<input type="checkbox" name="gr<?php echo $m['grid']; ?>[]" value="<?php echo $tgid; ?>" id="<?php echo 'gr'.$tgid.'-'.$m['grid']; ?>" onclick="groupCl(<?php echo $m['grid'].','.$tgid; ?>);" <?php if (in_array($tgid,$m['tocheck'])): ?>checked="checked"<?php endif; ?> />
					</td>
					<?php endforeach; ?>
				</tr>
				<?php endif; ?>
				<?php endforeach; ?>
			</table>
			<?php else: ?>
			<?php echo lang('pa_nomodusers'); ?>
			<?php endif; ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="changemod" value="<?php echo lang('pa_changemod'); ?>" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_corr'); ?>
		</div>
		<div class="centerforminput">
			<?php if (count($pa_m_corr) > 0): ?>
			<table class="table" border="0" cellspacing="1" cellpadding="4">
				<tr class="titlerow">
					<td class="m_corr_id">
						<?php echo lang('pa_m_corr_id'); ?>
					</td>
					<td class="m_corr_name">
						<?php echo lang('pa_m_corr_name'); ?>
					</td>
				</tr>
				<?php foreach ($pa_m_corr as $id => $name): ?>
				<tr>
					<td class="m_corr_id">
						<?php echo $id; ?>
					</td>
					<td class="m_corr_name">
						<?php echo $name; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
			<?php else: ?>
			<?php echo lang('pa_nocorr'); ?>
			<?php endif; ?>
		</div>
<?php elseif ($usr_part == "notconnected"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_notconnected_rights'); ?>
		</div>
		<?php if (count($gr_guests_topicgroups) > 0): ?>
		<div class="centerforminput">
			<table class="table" border="0" cellspacing="1" cellpadding="4">
				<tr class="titlerow">
					<td class="group_auth_title">
						<?php echo lang('pa_auth_title'); ?>
					</td>
					<td class="group_auth_see">
						<?php echo lang('pa_auth_see'); ?>
					</td>
					<td class="group_auth_reply">
						<?php echo lang('pa_auth_reply'); ?>
					</td>
					<td class="group_auth_create">
						<?php echo lang('pa_auth_create'); ?>
					</td>
				</tr>
				<?php foreach ($gr_guests_topicgroups as $tg): ?>
				<tr>
					<td class="group_auth_title">
						<?php echo $tg['tg_name']; ?>
					</td>
					<td class="group_auth_see">
						<input type="checkbox" id="see_<?php echo $tg['tg_id']; ?>" onclick="javascript:authfunc('see',<?php echo $tg['tg_id']; ?>);" name="see_<?php echo $tg['tg_id']; ?>"<?php if ($tg['see_checked']): ?> checked="checked"<?php endif; ?> />
					</td>
					<td class="group_auth_reply">
						<input type="checkbox" id="reply_<?php echo $tg['tg_id']; ?>" onclick="javascript:authfunc('reply',<?php echo $tg['tg_id']; ?>);" name="reply_<?php echo $tg['tg_id']; ?>"<?php if ($tg['reply_checked']): ?> checked="checked"<?php endif; ?> />
					</td>
					<td class="group_auth_create">
						<input type="checkbox" id="create_<?php echo $tg['tg_id']; ?>" onclick="javascript:authfunc('create',<?php echo $tg['tg_id']; ?>);" name="create_<?php echo $tg['tg_id']; ?>"<?php if ($tg['create_checked']): ?> checked="checked"<?php endif; ?> />
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
		</div>
		<div class="centerformtext">
			<input type="submit" name="setrights" value="<?php echo lang('pa_notconnected_confirm'); ?>" />
		</div>
		<?php endif; ?>
<?php elseif ($usr_part == "lastmodacts"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_o_lastactions'); ?>
		</div>
		<?php if (!empty($o_pagemenu)): ?>
		<div class="pa_pagemenu">
			<?php echo lang('pages').' '.$o_pagemenu; ?>
		</div>
		<?php endif; ?>
		<div class="centerforminput">
			<?php if ($o_searching || count($o_log) != 0): ?>
			<table class="table" border="0" cellspacing="1" cellpadding="4">
			<?php if (count($o_log) != 0): ?>
			<tr class="titlerow">
				<td class="log_make"><?php echo lang('pa_o_log_make'); ?></td>
				<td class="log_time"><?php echo lang('pa_o_log_time'); ?></td>
				<td class="log_type"><?php echo lang('pa_o_log_type'); ?></td>
				<td class="log_concerns"><?php echo lang('pa_o_log_concerns'); ?></td>
			</tr>
			<?php foreach ($o_log as $log): ?>
			<tr>
				<td class="log_make"><?php echo $log['log_make']; ?></td>
				<td class="log_time"><?php echo $log['log_time']; ?></td>
				<td class="log_type"><?php echo lang(array('item' => $log['log_type'])); ?></td>
				<td class="log_concerns"><?php echo $log['log_concerns']; ?></td>
			</tr>
			<?php endforeach; ?>
			<?php endif; ?>
			<tr class="titlerow">
				<td class="log_make"><?php echo lang('pa_o_log_make_sort'); ?></td>
				<td class="log_time">---</td>
				<td class="log_type"><?php echo lang('pa_o_log_type_sort'); ?></td>
				<td class="log_concerns"><?php echo lang('pa_o_log_concerns_sort'); ?></td>
			</tr>
			<tr>
				<td class="log_make"><input type="text" name="author" value="<?php echo $o_log_make; ?>" size="15" /></td>
				<td class="log_time"></td>
				<td class="log_type"><?php echo $o_log_type_choosemenu; ?></td>
				<td class="log_concerns"><input type="text" name="concerns" value="<?php echo $o_log_concerns; ?>" size="15" /></td>
			</tr>
			<tr>
				<td class="log_make"><input type="submit" name="search" value="<?php echo lang('confirm'); ?>" /></td>
				<td class="log_time"></td>
				<td class="log_type"></td>
				<td class="log_concerns"></td>
			</tr>
			</table>
			<?php else: ?>
			<?php echo lang('pa_o_log_empty'); ?>
			<?php endif; ?>
		</div>
		<?php if (!empty($o_pagemenu)): ?>
		<div class="pa_pagemenu">
			<?php echo lang('pages').' '.$o_pagemenu; ?>
		</div>
		<?php endif; ?>
<?php elseif ($usr_part == "mannotval"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_mannotval_infos'); ?>
		</div>
		<?php if (count($mnv_users) > 0): ?>
		<?php if ( !empty($mnv_pagemenu) ) : ?>
		<div class="pa_pagemenu"><?php echo lang('pages'); ?> <?php echo $mnv_pagemenu; ?></div>
		<?php endif; ?>
		<div class="centerforminput">
			<table class="table" border="0" cellspacing="1" cellpadding="4">
				<tr class="titlerow">
					<td class="mnv_name">
						<?php echo lang('pa_mnv_name'); ?>
					</td>
					<td class="mnv_issue">
						<?php echo lang('pa_mnv_issue'); ?>
					</td>
					<td class="mnv_mail">
						<?php echo lang('pa_mnv_mail'); ?>
					</td>
					<td class="mnv_date">
						<?php echo lang('pa_mnv_date'); ?>
					</td>
					<td class="mnv_options">
						<?php echo lang('pa_mnv_options'); ?>
					</td>
				</tr>
				<?php foreach ($mnv_users as $acc): ?>
				<tr>
					<td class="mnv_name">
						<?php echo $acc['name']; ?>
					</td>
					<td class="mnv_issue">
						<?php echo lang((!$acc['change'])?'pa_mnv_register':'pa_mnv_changemail'); ?>
					</td>
					<td class="mnv_mail">
						<?php echo $acc['mail']; ?>
					</td>
					<td class="mnv_date">
						<?php echo $acc['date']; ?>
					</td>
					<td class="mnv_options">
						<a href="<?php echo manage_url('admin.php?act=users&amp;sub=8&amp;val='.$acc['id'].'&amp;page='.$mnv_page,'forum-admin.html?act=users&amp;sub=8&amp;val='.$acc['id'].'&amp;page='.$mnv_page); ?>"><?php echo lang('pa_mnv_validate'); ?></a>
						<?php if (!$acc['change']): ?> - <a href="<?php echo manage_url('admin.php?act=users&amp;sub=8&amp;del='.$acc['id'].'&amp;page='.$mnv_page,'forum-admin.html?act=users&amp;sub=8&amp;del='.$acc['id'].'&amp;page='.$mnv_page); ?>"><?php echo lang('pa_mnv_delete'); ?></a><?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
		</div>
		<?php if ( !empty($mnv_pagemenu) ) : ?>
		<div class="pa_pagemenu"><?php echo lang('pages'); ?> <?php echo $mnv_pagemenu; ?></div>
		<?php endif; ?>
		<?php else: ?>
		<div class="centerformtext">
			<?php echo lang('pa_mannotval_nobody'); ?>
		</div>
		<?php endif; ?>
<?php elseif ($usr_part == "ranks"): ?>
		<?php if ($pa_editing): ?>
		<div class="centerformtext">
			<?php echo lang('warning'); ?><br />
			<?php echo lang(array('item' => 'pa_editingrank', 'name' => $pa_editing_name)); ?>
		</div>
		<?php endif; ?>
		<div class="centerformtext">
			<?php echo lang('pa_ranks_name'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="ranktitle" maxlength="60" size="30" value="<?php echo $pa_users_editrank_title; ?>" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_ranks_posts'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="rankposts" maxlength="60" size="30" value="<?php echo $pa_users_editrank_posts; ?>" />
		</div>
		<div class="centerformtext">
			<input type="submit" name="createrank" value="<?php echo lang($pa_editing?'pa_ranks_submitedit':'pa_ranks_submitcreate'); ?>" />
		</div>
<?php endif; ?>
