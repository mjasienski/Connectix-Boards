<?php if ($m_part == "massmail"): ?>
		<?php if ($mm_previsualization): ?>
		<div class="centerformtext">
			<?php echo lang('pa_massmail_previs'); ?>
		</div>
		<div class="mm_previs">
			<?php echo $mm_previs_message; ?>
		</div>
		<?php endif; ?>
		<div class="centerformtext">
			<?php echo lang('pa_massmail_info'); ?>
		</div>
		<div class="centerforminput">
			<?php echo lang('pa_massmail_type'); ?> : &nbsp;
			<select name="mm_type">
				<option value="mp" <?php if ($mm_type == 'mp'): ?>selected="selected" <?php endif; ?>/> <?php echo lang('pa_massmail_type_mp'); ?></option>
				<option value="mail" <?php if ($mm_type == 'mail'): ?>selected="selected" <?php endif; ?>/> <?php echo lang('pa_massmail_type_mail'); ?></option>
			</select>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_massmail_groups'); ?>
		</div>
		<div class="centerforminput">
			<select name="mm_groups[]" multiple="multiple">
				<?php foreach ($mm_groups as $gr): ?>
				<option value="<?php echo $gr['id']; ?>" <?php if (in_array($gr['id'],$mm_groups_selected)): ?>selected="selected"<?php endif; ?>><?php echo $gr['name']; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_massmail_subject'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="mm_subject" value="<?php echo $mm_subject; ?>" size="60" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_massmail_message'); ?>
		</div>
		<div class="centerforminput">
			<?php $ta_opt = array('name' => 'mm_message', 'id' => 'message', 'value' => $mm_message); ?>
			<?php include($template_path.'menu_writemsg.php'); ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="mm_previs" value="<?php echo lang('pa_massmail_previs'); ?>" />
			<input type="submit" name="massmail" value="<?php echo lang('confirm'); ?>" />
		</div>
<?php elseif ($m_part == "changeconfirminscr"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_mailsubject_title'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="mailsubject_ci" size="70" value="<?php echo $pa_mailsubject_ci; ?>" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_changemail_title'); ?><br />
			<?php echo lang('pa_changemail_ci_info'); ?>
		</div>
		<div class="centerforminput">
			<textarea name="mail_ci" rows="12" cols="70"><?php echo $pa_changemail_ci_contents; ?></textarea>
		</div>
		<div class="centerformtext">
			<input type="submit" name="changeconfirminscr" value="<?php echo lang('confirm'); ?>" />
		</div>
<?php elseif ($m_part == "changeconfirmchangemail"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_mailsubject_title'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="mailsubject_cm" size="70" value="<?php echo $pa_mailsubject_cm; ?>" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_changemail_title'); ?><br />
			<?php echo lang('pa_changemail_cm_info'); ?>
		</div>
		<div class="centerforminput">
			<textarea name="mail_cm" rows="12" cols="70"><?php echo $pa_changemail_cm_contents; ?></textarea>
		</div>
		<div class="centerformtext">
			<input type="submit" name="changeconfirmchangemail" value="<?php echo lang('confirm'); ?>" />
		</div>
<?php elseif ($m_part == "changeconfirmchangepass"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_mailsubject_title'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="mailsubject_cp" size="70" value="<?php echo $pa_mailsubject_cp; ?>" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_changemail_title'); ?><br />
			<?php echo lang('pa_changemail_cp_info'); ?>
		</div>
		<div class="centerforminput">
			<textarea name="mail_cp" rows="12" cols="70"><?php echo $pa_changemail_cp_contents; ?></textarea>
		</div>
		<div class="centerformtext">
			<input type="submit" name="changeconfirmchangepass" value="<?php echo lang('confirm'); ?>" />
		</div>
<?php elseif ($m_part == "changetopictrack"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_mailsubject_title'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="mailsubject_tt" size="70" value="<?php echo $pa_mailsubject_tt; ?>" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_changemail_title'); ?><br />
			<?php echo lang('pa_changemail_tt_info'); ?>
		</div>
		<div class="centerforminput">
			<textarea name="mail_tt" rows="12" cols="70"><?php echo $pa_changemail_tt_contents; ?></textarea>
		</div>
		<div class="centerformtext">
			<input type="submit" name="changetopictrack" value="<?php echo lang('confirm'); ?>" />
		</div>
<?php elseif ($m_part == "changemailmp"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_mailsubject_title'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="mailsubject_mp" size="70" value="<?php echo $pa_mailsubject_mp; ?>" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_changemail_title'); ?><br />
			<?php echo lang('pa_changemail_mp_info'); ?>
		</div>
		<div class="centerforminput">
			<textarea name="mail_mp" rows="12" cols="70"><?php echo $pa_changemail_mp_contents; ?></textarea>
		</div>
		<div class="centerformtext">
			<input type="submit" name="changemailmp" value="<?php echo lang('confirm'); ?>" />
		</div>
<?php endif; ?>
