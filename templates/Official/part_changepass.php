<?php if (!defined('CB_TEMPLATE')) exit('Incorrect access attempt !!'); ?>

<form action="" method="post" id="form_changepass">
	<div class="container">
		<h2><?php echo $title_pre; ?><?php echo lang('changepass'); ?></h2>
		<div class="subcontainer">
			<p class="inforow">
				<span class="i"><?php echo lang('cp_infos'); ?></span>
			</p>
			<fieldset>
				<legend><?php echo lang('cp_email'); ?></legend>
				<p>
					<input type="text" name="email" size="30" value="<?php echo $cp_email; ?>" />
				</p>
			</fieldset>
			<div class="confirm">
				<input type="submit" name="confirm" value="<?php echo lang('confirm'); ?>" />
			</div>
		</div>
	</div>
</form>
