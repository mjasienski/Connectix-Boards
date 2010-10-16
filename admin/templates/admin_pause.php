		<div class="centerformtext">
			<?php echo lang('pa_setpause'); ?>
		</div>
		<div class="centerforminput">
			<input type="radio" name="pause" value="on" <?php echo $p_yes_checked; ?> /> <?php echo lang('yes'); ?><br />
			<input type="radio" name="pause" value="off" <?php echo $p_no_checked; ?> /> <?php echo lang('no'); ?>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_pausemessage'); ?>
		</div>
		<div class="centerforminput">
			<textarea name="pausemessage" rows="6" cols="63"><?php echo $p_pausemessage_contents; ?></textarea>
		</div>
		<div class="centerformtext">
			<input type="submit" name="confirm" value="<?php echo lang('confirm'); ?>" />
		</div>
