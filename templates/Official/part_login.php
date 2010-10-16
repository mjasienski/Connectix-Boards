<?php if (!defined('CB_TEMPLATE')) exit('Incorrect access attempt !!'); ?>

<form action="<?php echo $l_loginaction; ?>" method="post" id="form_login">
	<div class="container">
		<h2><?php echo $title_pre; ?><?php echo lang('login'); ?></h2>
		<div class="subcontainer">
			<fieldset>
				<legend><?php echo lang('login_enterinfos'); ?></legend>
				<p>
					<label><strong><?php echo lang('username'); ?></strong><br />
					<input type="text" name="username" size="18" /></label>
				</p>
				<p>
					<label><strong><?php echo lang('password'); ?></strong><br />
					<input type="password" name="password" size="18" /></label>
				</p>
				<p>
					<label><?php echo lang('remember'); ?> &nbsp; <input type="checkbox" checked="checked" name="remember" /></label>
				</p>
			</fieldset>
			<div class="confirm">
				<input type="submit" name="confirm" value="<?php echo lang('login_confirm'); ?>" />
			</div>
			<div class="confirm">
				<a href="<?php echo manage_url('index.php?act=cp', 'forum-changepass.html'); ?>"><?php echo lang('lost_password'); ?></a>
			</div>
		</div>
	</div>
</form>
