<?php if ($r_needform): ?>
<form action="<?php echo $r_action; ?>" method="post" id="form_register">
	<div class="container">
		<h2><?php echo $title_pre; ?><?php echo lang('register'); ?></h2>
		<div class="subcontainer">
			<fieldset>
				<legend><?php echo lang('login_enterinfos'); ?></legend>
				<p id="reg_login">
					<label><strong><?php echo lang('username'); ?></strong><br />
					<input type="text" name="username" size="30" maxlength="30" value="<?php echo $r_form_pre_username; ?>" onblur="regFormCheck('reg_login',this.value);" /></label>
				</p>
				<p id="reg_password">
					<label><strong><?php echo lang('password'); ?></strong><br />
					<input type="password" name="password1" id="field_pass1" size="30" maxlength="30" value="<?php echo $r_form_pre_pass1; ?>" /></label>
				</p>
				<p id="reg_password_confirm">
					<label><strong><?php echo lang('password_confirm'); ?></strong><br />
					<input type="password" name="password2" id="field_pass2" size="30" maxlength="30" value="<?php echo $r_form_pre_pass2; ?>" onblur="regFormPass();" /></label>
				</p>
				<p id="reg_mail">
					<label><strong><?php echo lang('insert_mail'); ?></strong><br />
					<input type="text" name="email1" size="30" maxlength="60" value="<?php echo $r_form_pre_mail; ?>" onblur="regFormCheck('reg_mail',this.value);" /></label>
				</p>
				<?php if ($r_form_pre_mail_confirmactivated): ?>
				<p id="reg_mail_warning">
					<span>
					<?php echo lang('r_mail_warning'); ?>
					</span>
				</p>
				<?php endif; ?>
				<p id="reg_captcha">
					<strong><?php echo lang('captcha_title'); ?></strong><br />
					<?php include($template_path.'menu_captcha.php'); ?>
				</p>
				<p id="reg_rules">
					<strong><?php echo lang('rules'); ?></strong>
					<span id="r_rules_details">
						<?php echo $r_rules; ?>
					</span>
					<label><input type="checkbox" name="rules" />  &nbsp; <?php echo lang('r_acceptrules'); ?></label>
				</p>
			</fieldset>
			<div class="confirm">
				<input type="submit" name="confirm" value="<?php echo lang('register'); ?>" />
			</div>
		</div>
	</div>
</form>
<?php endif; ?>
