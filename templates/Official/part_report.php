
<?php if ($r_needform): ?>
<form action="" method="post" id="form_report">
	<div class="container">
		<h2><?php echo $title_pre; ?><?php echo lang('rep_report'); ?></h2>
		<p class="inforow">
			<?php echo lang('rep_infos'); ?>
		</p>
		<fieldset>
			<legend><?php echo lang('rep_message_title'); ?></legend>
			<p>
				<textarea name="message" id="message" rows="15" cols="50"></textarea>
			</p>
		</fieldset>
		<div class="confirm">
			<input type="submit" name="report" value="<?php echo lang('confirm'); ?>" />
		</div>
	</div>
</form>
<?php endif; ?>
