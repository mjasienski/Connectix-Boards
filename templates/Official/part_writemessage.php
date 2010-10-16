
<form action="<?php echo $wm_formaction; ?>" method="post" id="form_writemessage">
	<div class="container">
		<h2><?php echo $title_pre; ?><?php echo lang(array('item' => $wm_title)); ?></h2>
		
		<div class="subcontainer">
			<?php if (isset($wm_prev_message)): ?>
			<fieldset id="wm_previsualization">
				<legend><?php echo lang('wm_previsualization'); ?></legend>
				<p>
					<?php echo $wm_prev_message; ?>
				</p>
			</fieldset>
			<?php endif; ?>

			<?php if (!$g_islogged): ?>
			<fieldset id="wm_guestform">
				<legend><?php echo lang('wm_guestidentification'); ?></legend>
				<p>
					<label><strong><?php echo lang('wm_guestname'); ?></strong><br />
					<input type="text" name="guestname" size="40" maxlength="60" value="<?php echo $wm_guestname; ?>" tabindex="1" /></label>
				</p>
				<p>
					<strong><?php echo lang('wm_captcha'); ?></strong><br />
					<?php include($template_path.'menu_captcha.php'); ?>
				</p>
			</fieldset>
			<?php endif; ?>

			<?php if ($wm_newtopic || $wm_w_edittopictitle): ?>
			<fieldset id="wm_edittopic">
				<legend><?php echo lang('wm_topic_infos'); ?></legend>
				<p>
					<label><strong><?php echo lang('wm_topictitle'); ?></strong><br />
					<input type="text" name="topictitle" size="65" maxlength="65" value="<?php echo $wm_w_topictitle; ?>" tabindex="3" /></label>
				</p>
				<p>
					<label><strong><?php echo lang('wm_topiccomment'); ?></strong><br />
					<input type="text" name="topiccomment" size="65" maxlength="65" value="<?php echo $wm_w_topiccomment; ?>" tabindex="4" /></label>
				</p>
			</fieldset>
			<?php endif; ?>

			<?php if ($wm_newpoll): ?>
			<fieldset id="wm_writepoll">
				<legend><?php echo lang('wm_poll_infos'); ?></legend>
				<p>
					<label><strong><?php echo lang('wm_pollquestion'); ?></strong><br />
					<input type="text" name="pollquestion" size="65" maxlength="65" value="<?php echo $wm_w_pollquestion; ?>" tabindex="5" /></label>
				</p>
				<p>
					<label><strong><?php echo lang('wm_pollpossibilities'); ?></strong><br />
					<textarea name="pollpossibilities" id="poll" rows="15" cols="50" tabindex="6"><?php echo $wm_w_pollpossibilities; ?></textarea></label>
				</p>
			</fieldset>
			<?php endif; ?>

			<fieldset id="wm_message">
				<legend><?php echo lang('wm_message'); ?></legend>
				<p>
					<?php $ta_opt = array('name' => 'message', 'id' =>  'message', 'tabindex' => 7, 'value' => $wm_w_message); ?>
					<?php include($template_path.'menu_writemsg.php'); ?>
					<br />
					<span class="i"><?php echo lang('infomessage'); ?></span>
				</p>
			</fieldset>
			
			<fieldset id="wm_redirect">
				<legend><?php echo lang('wm_redirect'); ?></legend>
				<p>
					<?php $list = $wm_w_selectredirect; ?><?php include ($template_path.'menu_list.php'); ?>
				</p>
			</fieldset>

			<?php if ($wm_needmodoptions): ?>
			<fieldset id="wm_modoptions">
				<legend><?php echo lang('wm_modoptions'); ?></legend>
				<p>
					<label><strong><?php echo lang('wm_mod_topicstatus'); ?></strong><br />
					<?php $list = $wm_w_modoptions_menu; ?><?php include ($template_path.'menu_list.php'); ?>
					</label>
				</p>
			<?php if ($wm_action=='edit'): ?>
				<p>
					<strong><?php echo lang('wm_mod_edit'); ?></strong><br />
					<label><input type="radio" name="mod_edit" value="yes" checked="checked" /> <?php echo lang('yes'); ?></label><br />
					<label><input type="radio" name="mod_edit" value="no" /> <?php echo lang('no'); ?></label>
				</p>
			<?php endif; ?>
			</fieldset>
			<?php endif; ?>

			<div class="confirm">
				<input type="submit" name="prev" value="<?php echo lang('wm_previsualization'); ?>" tabindex="9" />
				<input type="submit" name="confirm" value="<?php echo lang($wm_w_submitmessage); ?>" tabindex="8" />
				<a href="<?php echo $wm_w_cancelurl; ?>"><input type="button" value="<?php echo lang('cancel'); ?>" onclick="window.location='<?php echo $wm_w_cancelurl; ?>';" /></a>
			</div>
		</div>
	</div>
</form>

<?php if (isset($wm_lastmessages)): ?>
<div class="table" id="table_lastmessages">
<table>
	<caption>
		<?php echo $title_pre; ?><?php echo lang('wm_lastmessages'); ?>
	</caption>

	<tfoot>
		<tr>
			<th></th>
			<th></th>
		</tr>
	</tfoot>

	<?php foreach ($wm_lastmessages as $message): ?>
	<!-- MESSAGE -->
	<tr class="field1">
		<td class="messageuser">
			<?php echo $message['wm_lm_msglink']; ?><?php echo $message['wm_lm_userlink']; ?>
		</td>
		<td class="messageheader">
			<?php echo $message['wm_lm_time']; ?>
		</td>
	</tr>
	<tr class="field1">
		<td class="messageuser2">
		</td>
		<td class="message">
			<div class="messagecontent">
				<?php echo $message['wm_lm_message']; ?>
			</div>
			<?php if (isset($message['wm_lm_modif_userlink'])): ?>
			<div class="messageedit">
				<?php echo lang(array('item' => 't_edited', 'user' => $message['wm_lm_modif_userlink'], 'date1' => $message['wm_lm_modif_date1'], 'date2' => $message['wm_lm_modif_date2'])); ?>
			</div>
			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>
</div>
<?php endif; ?>
