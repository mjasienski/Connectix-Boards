
<?php if ($u_subpart == "showprofile"): ?>
<div class="container" id="cont_showprofile">
	<h2><?php echo $title_pre;  echo lang('usr_userprofile'); ?> : <?php echo $u_name; ?></h2>
	<div class="subcontainer">
		<fieldset>
			<legend><?php echo lang('usr_sp_informations'); ?></legend>
			<p>
				<strong><?php echo lang('class'); ?></strong> : <?php echo $u_class; ?><br />
				<strong><?php echo lang('usr_realname'); ?></strong> : <?php if (!empty($u_realname)):  echo $u_realname;  else: ?><span class="i"><?php echo lang('usr_noinfo'); ?></span><?php endif; ?><br />
				<strong><?php echo lang('usr_birthdate'); ?></strong> : <?php if (!empty($u_birthdate)):  echo $u_birthdate;  else: ?><span class="i"><?php echo lang('usr_noinfo'); ?></span><?php endif; ?><br />
				<strong><?php echo lang('usr_gender'); ?></strong> : <?php echo (($u_gender==1)?lang('usr_gender_male'):(($u_gender==2)?lang('usr_gender_female'):'<span class="i">'.lang('usr_noinfo').'</span>')); ?><br />
				<strong><?php echo lang('usr_place'); ?></strong> : <?php if (!empty($u_place)):  echo $u_place;  else: ?><span class="i"><?php echo lang('usr_noinfo'); ?></span><?php endif; ?><br />
				<strong><?php echo lang('usr_presentation'); ?></strong> : <?php if (!empty($u_pres)):  echo '<br /><span class="presentation">'.$u_pres.'</span>';  else: ?><span class="i"><?php echo lang('usr_noinfo'); ?></span><?php endif; ?>
			</p>
		</fieldset>
		<fieldset>
			<legend><?php echo lang('usr_sp_communication'); ?></legend>
			<p>
				<strong><?php echo lang('usr_website'); ?></strong> : <?php if (!empty($u_www)): ?><a href="<?php echo $u_www; ?>"><?php echo $u_www; ?></a><?php else: ?><span class="i"><?php echo lang('usr_noinfo'); ?></span><?php endif; ?><br />
				<strong><?php echo lang('mail'); ?></strong> : <?php if (!empty($u_mail)):  echo $u_mail;  else: ?><span class="i"><?php echo lang('usr_mail_notpublic'); ?></span><?php endif; ?><br />
				<strong><?php echo lang('usr_msn'); ?></strong> : <?php if (!empty($u_msn)):  echo $u_msn;  else: ?><span class="i"><?php echo lang('usr_noinfo'); ?></span><?php endif; ?><br />
				<strong><?php echo lang('usr_icq'); ?></strong> : <?php if (!empty($u_icq)):  echo $u_icq;  else: ?><span class="i"><?php echo lang('usr_noinfo'); ?></span><?php endif; ?><br />
				<strong><?php echo lang('usr_aim'); ?></strong> : <?php if (!empty($u_aim)):  echo $u_aim;  else: ?><span class="i"><?php echo lang('usr_noinfo'); ?></span><?php endif; ?><br />
				<strong><?php echo lang('usr_yahoo'); ?></strong> : <?php if (!empty($u_yahoo)):  echo $u_yahoo;  else: ?><span class="i"><?php echo lang('usr_noinfo'); ?></span><?php endif; ?><br />
				<strong><?php echo lang('mp'); ?></strong> : <a href="<?php echo $u_mplink; ?>"><?php echo lang('usr_sp_mptitle'); ?></a>
			</p>
		</fieldset>
		<fieldset>
			<legend><?php echo lang('usr_sp_activities'); ?></legend>
			<p>
				<strong><?php echo lang('registered'); ?></strong> : <?php echo $u_regtime; ?><br />
				<strong><?php echo lang('lastconnect'); ?></strong> : <?php echo $u_lastconnect; ?><br />
				<strong><?php echo lang('posts'); ?></strong> : <?php echo $u_nbposts; ?> (<?php echo lang(array('item' => 'usr_sp_msgperday', 'msgs' => number_format( (((time()-$u_regtimestamp)>86400)?(($u_nbposts*86400)/(time()-$u_regtimestamp)):$u_nbposts) , 2 ))); ?>) - <a href="<?php echo manage_url('index.php?act=src&amp;author='.$u_name.'&amp;from=fr_def&amp;search=go', 'forum-search.html?author='.$u_name.'&amp;from=fr_def&amp;search=go'); ?>"><?php echo lang('usr_findposts'); ?></a><br />
				<strong><?php echo lang('usr_sp_lastaction'); ?></strong> : <?php if (!empty($u_lastaction)):  echo lang(array('item' => $u_lastaction, 'f' => $u_lastaction_f, 'tg' => $u_lastaction_tg)); ?> ( <?php echo $u_lastaction_time; ?> )<?php else: ?><span class="i"><?php echo lang('usr_sp_lastaction_not'); ?></span><?php endif; ?>
				<?php if ($u_mod): ?><br /><strong><?php echo lang('usr_modoptions'); ?></strong> : <?php if ($u_status==0): ?><a href="<?php echo manage_url('index.php?act=mods&amp;page=2&amp;punish='.$u_id, 'forum-moderators.html?page=2&amp;punish='.$u_id); ?>"><?php echo lang('punish'); ?></a> - <?php endif; ?><a href="<?php echo manage_url('index.php?act=user&amp;editprofile='.$u_id, 'forum-profile'.$u_id.'.html'); ?>"><?php echo lang('mod_editprofile'); ?></a><?php endif; ?>
			</p>
		</fieldset>
		<fieldset>
			<legend><?php echo lang('usr_sp_personalization'); ?></legend>
			<p>
				<strong><?php echo lang('avatar'); ?></strong> :
				<span class="usrpr_avatar">
					<?php if (!empty($u_avatar)):  echo $u_avatar; ?><br /><?php else: ?><span class="i"><?php echo lang('usr_sp_noavatar'); ?></span><br /><?php endif; ?>
					<?php echo $u_classimage; ?>
				</span>
			</p>
			<p>
				<strong><?php echo lang('signature'); ?></strong> :
				<span class="usrpr_signature">
					<?php if (!empty($u_sign)):  echo $u_sign;  else: ?><span class="i"><?php echo lang('usr_sp_nosignature'); ?></span><?php endif; ?>
				</span>
			</p>
		</fieldset>
		<div class="clearfix"></div>
	</div>
</div>
<?php elseif ($u_subpart == "editprofile" && !in_array($u_contents,array('topicstracked','bookmarks'))): ?>
<form action="<?php echo $u_formaction; ?>" method="post" enctype="multipart/form-data" id="form_editprofile">
<?php $menu = &$u_menu;  include ($template_path.'menu_links.php'); ?>

	<div class="container" id="cont_editprofile">
		<h2><?php echo $title_pre;  echo lang(array('item' => $u_title)); ?></h2>
			<div class="subcontainer">
	<?php if ($u_contents == "general"): ?>
			<fieldset>
				<legend><?php echo lang('usr_sp_informations'); ?></legend>
				<p>
					<strong><?php echo lang('username'); ?></strong> : <?php echo $u_name_link; ?>
				</p>
				<p>
					<label><strong><?php echo lang('usr_gender'); ?></strong> : 
					<select name="gender">
						<option value="default"><?php echo lang('usr_gender_select'); ?></option>
						<option value="male" <?php if ($u_gender == 1) : echo 'selected="selected"'; endif; ?>><?php echo lang('usr_gender_male'); ?></option>
						<option value="female" <?php if ($u_gender == 2) : echo 'selected="selected"'; endif; ?>><?php echo lang('usr_gender_female'); ?></option>
					</select>
					</label>
				</p>
				<p>
					<label><strong><?php echo lang('usr_realname'); ?></strong> : <br />
					<input type="text" name="realname" size="60" value="<?php echo $u_realname; ?>" /></label>
				</p>
				<p>
					<label><strong><?php echo lang('usr_birthdate'); ?></strong> : <br />
					<input type="text" name="birthdate" size="60" value="<?php echo $u_birthdate; ?>" /> <br />
					<?php echo lang('usr_birthdate_info'); ?> </label>
				</p>
				<p>
					<label><strong><?php echo lang('usr_place'); ?></strong> : <br />
					<input type="text" name="place" size="60" value="<?php echo $u_place; ?>" /></label>
				</p>
				<p>
					<label><strong><?php echo lang('usr_website'); ?></strong> : <br />
					<input type="text" name="website" size="60" value="<?php echo $u_website; ?>" /></label>
				</p>
				<p>
					<label><strong><?php echo lang('usr_presentation'); ?></strong> : <br />
					<textarea name="presentation" id="presentation"><?php echo $u_pres; ?></textarea></label>
				</p>
			</fieldset>
			<fieldset>
				<legend><?php echo lang('usr_sp_communication'); ?></legend>
				<p>
					<label><strong><?php echo lang('usr_msn'); ?></strong> : <br />
					<input type="text" name="msn" size="60" value="<?php echo $u_msn; ?>" /></label>
				</p>
				<p>
					<label><strong><?php echo lang('usr_icq'); ?></strong> : <br />
					<input type="text" name="icq" size="60" value="<?php echo $u_icq; ?>" /></label>
				</p>
				<p>
					<label><strong><?php echo lang('usr_aim'); ?></strong> : <br />
					<input type="text" name="aim" size="60" value="<?php echo $u_aim; ?>" /></label>
				</p>
				<p>
					<label><strong><?php echo lang('usr_yahoo'); ?></strong> : <br />
					<input type="text" name="yahoo" size="60" value="<?php echo $u_yahoo; ?>" /></label>
				</p>
				<p>
					<label><strong><?php echo lang('usr_public_email'); ?></strong> ( <?php echo $u_mail; ?> ) : &nbsp;
					<select name="publicemail">
						<option value="yes" <?php echo $u_pmail_yes_checked; ?>><?php echo lang('yes'); ?></option>
						<option value="no" <?php echo $u_pmail_no_checked; ?>><?php echo lang('no'); ?></option>
					</select></label>
				</p>
				<p>
					<label><strong><?php echo lang('usr_allow_massmail'); ?></strong> : &nbsp;
					<select name="allowmm">
						<option value="yes" <?php echo $u_allowmm_yes_checked; ?>><?php echo lang('yes'); ?></option>
						<option value="no" <?php echo $u_allowmm_no_checked; ?>><?php echo lang('no'); ?></option>
					</select></label>
				</p>
				<p>
					<label><strong><?php echo lang('usr_mailmp'); ?></strong> : &nbsp;
					<select name="mailmp">
						<option value="yes" <?php echo $u_mailmp_yes_checked; ?>><?php echo lang('yes'); ?></option>
						<option value="no" <?php echo $u_mailmp_no_checked; ?>><?php echo lang('no'); ?></option>
					</select></label>
				</p>
			</fieldset>
			<div class="confirm">
				<input type="submit" name="changeinfos" size="60" value="<?php echo lang('confirm'); ?>"/>
			</div>
	<?php elseif ($u_contents == "changemail"): ?>
			<p class="inforow">
				<?php echo lang('usr_changemail_infos'); ?>
			</p>
			<fieldset>
				<legend><?php echo lang('usr_changemail_title'); ?></legend>
				<p>
					<input type="text" size="60" name="changemail" />
				</p>
			</fieldset>
			<div class="confirm">
				<input type="submit" name="mailchange" value="<?php echo lang('confirm'); ?>" />
			</div>
	<?php elseif ($u_contents == "changepass"): ?>
			<fieldset>
				<legend><?php echo lang('usr_changepass'); ?></legend>
				<p>
					<label><strong><?php echo lang('usr_oldpass'); ?></strong> :<br />
					<input type="password" name="password" size="25" /></label>
				</p>
				<p>
					<label><strong><?php echo lang('usr_newpass'); ?></strong> :<br />
					<input type="password" name="password1" size="25" /></label>
				</p>
				<p>
					<label><strong><?php echo lang('usr_confirmpass'); ?></strong> :<br />
					<input type="password" name="password2" size="25" /></label>
				</p>
			</fieldset>
			<div class="confirm">
				<input type="submit" name="passwordchange" value="<?php echo lang('confirm'); ?>" />
			</div>
	<?php elseif ($u_contents == "changeavatar"): ?>
			<fieldset>
				<legend><?php echo lang('usr_currentavatar'); ?></legend>
				<div class="usrpr_avatar">
					<?php if (!empty($u_avatar_link)):  echo $u_avatar_link;  else: ?><span class="i"><?php echo lang('usr_noavatar'); ?></span><?php endif; ?>
				</div>
			</fieldset>
			<fieldset>
				<legend><?php echo lang('usr_changeavatar'); ?></legend>
				<p>
					<label><strong><?php echo lang('usr_avatarurl'); ?></strong> :<br />
					<input type="text" name="avatar" size="40" maxlength="255" value="" /></label>
					<input type="submit" name="avatarchange" value="<?php echo lang('confirm'); ?>" />
				</p>
				<p>
					<label><strong><?php echo lang('usr_avatarfile'); ?></strong> :<br />
					<input type="file" name="imagefile" class="input_file" size="40" /></label>
					<input type="submit" name="avatarchangefile" value="<?php echo lang('confirm'); ?>" />
				</p>
			</fieldset>
			<fieldset>
				<legend><?php echo lang('usr_gallery_choose'); ?></legend>
				<p>
					<?php if (count($u_avatar_gallery)>0): ?>
					<?php foreach ($u_avatar_gallery as $avatar): ?>
					<span class="gallery_avatar">
						<a href="<?php echo manage_url('index.php?act=user&amp;editprofile='.$g_user_id.'&amp;page=4&amp;gallery='.$avatar, 'forum-profile'.$g_user_id.'-avatar.html?gallery='.$avatar); ?>"><img src="avatars/gallery/<?php echo $avatar; ?>" alt="avatar" /></a>
					</span>
					<?php endforeach; ?>
					<?php else: ?>
					<span class="i"><?php echo lang('usr_gallery_empty'); ?></span>
					<?php endif; ?>
				</p>
			</fieldset>
			<div class="clearfix"></div>
	<?php elseif ($u_contents == "changesign"): ?>
			<fieldset>
				<legend><?php echo lang('usr_currentsignature'); ?></legend>
				<div class="usrpr_signature">
					<?php if (!empty($u_signature_link)):  echo $u_signature_link;  else: ?><span class="i"><?php echo lang('usr_nosignature'); ?></span><?php endif; ?>
				</div>
			</fieldset>
			<fieldset>
				<legend><?php echo lang('usr_changesignature'); ?></legend>
				<p>
					<?php if (count($u_sign_bb_forbidden)>0): ?><strong><?php echo lang('usr_changesign_bb_forbidden'); ?></strong> : <?php $first = true;  foreach ($u_sign_bb_forbidden as $bb):  if (!$first): ?>, <?php endif;  $first = false; ?>[<?php echo $bb; ?>]<?php endforeach;  endif; ?>
					<?php $ta_opt = array('name' => 'signature', 'id' =>  'message', 'rows' => 15, 'cols' => 50, 'value' => $u_sign); ?>
					<?php include($template_path.'menu_writemsg.php'); ?><br />
				</p>
			</fieldset>
			<div class="confirm">
				<input type="submit" name="signaturechange" value="<?php echo lang('confirm'); ?>" />
			</div>
	<?php elseif ($u_contents == "changeparams"): ?>
			<fieldset>
				<legend><?php echo lang('usr_p_pageview'); ?></legend>
				<p>
					<label><strong><?php echo lang('usr_p_usrs'); ?></strong> :<br />
					<input type="text" name="p_usrs" value="<?php echo $u_params_usrs; ?>" size="5" maxlength="2" /></label> (<?php echo lang('usr_p_notice'); ?>)
				</p>
				<p>
					<label><strong><?php echo lang('usr_p_topics'); ?></strong> :<br />
					<input type="text" name="p_topics" value="<?php echo $u_params_topics; ?>" size="5" maxlength="2" /></label> (<?php echo lang('usr_p_notice'); ?>)
				</p>
				<p>
					<label><strong><?php echo lang('usr_p_msgs'); ?></strong> :<br />
					<input type="text" name="p_msgs" value="<?php echo $u_params_msgs; ?>" size="5" maxlength="2" /></label> (<?php echo lang('usr_p_notice'); ?>)
				</p>
				<p>
					<label><strong><?php echo lang('usr_p_res'); ?></strong> :<br />
					<input type="text" name="p_res" value="<?php echo $u_params_res; ?>" size="5" maxlength="2" /></label> (<?php echo lang('usr_p_notice'); ?>)
				</p>
			</fieldset>
			<fieldset>
				<legend><?php echo lang('usr_p_timezone_change'); ?></legend>
				<p>
					<label>
						<strong><?php echo lang('usr_p_timezone'); ?></strong> :<br />
						<select name='p_timezone'>
							<?php foreach ($u_timezone as $key => $value): ?>
							<option value="<?php echo $key; ?>"<?php if ($key == $u_params_timezone): ?> selected="selected"<?php endif;?>><?php echo $value; ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				</p>
				<p>
					<label>
						<input type="checkbox" name="p_ctsummer" <?php if ($u_params_ctsummer): ?>checked="checked"<?php endif; ?> /> <?php echo lang('usr_p_ctsummer'); ?>
					</label>
				</p>
			</fieldset>
			<fieldset>
				<legend><?php echo lang('usr_p_appear'); ?></legend>
				<p>
					<label><strong><?php echo lang('usr_p_skin'); ?></strong> :<br />
					<?php echo $u_params_skin; ?></label>
				</p>
			</fieldset>
			<fieldset>
				<legend><?php echo lang('usr_p_language'); ?></legend>
				<p>
					<label><strong><?php echo lang('usr_p_lang'); ?></strong> :<br />
					<?php echo $u_params_lang; ?></label>
				</p>
			</fieldset>
			<div class="confirm">
				<input type="submit" name="changeparams" value="<?php echo lang('confirm'); ?>" />
			</div>
	<?php endif; ?>
		</div>
	</div>
</form>

<?php endif; ?>
