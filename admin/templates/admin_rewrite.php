		<?php if ($rewrite_on): ?>
		<div class="centerformtext">
			<?php echo lang('pa_rw_on'); ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="rw_disable" value="<?php echo lang('pa_rw_disable_conf'); ?>" />
		</div>
		<?php else: ?>
		<div class="centerformtext">
			<?php echo lang('pa_rw_off'); ?>
		</div>
		<?php if (!empty($rw_msg)): ?>
		<br />
		<div class="centerformtext" style="text-align:left;">
			<?php echo lang(array('item' => $rw_msg)); ?>
		</div>
		<?php endif; ?>
		<?php if ($rewrite_apache_on != 0): ?>
		<br />
		<div class="centerformtext">
			<?php echo lang(array('item' => ($rewrite_apache_on == -1)?'pa_rw_enable_withmsg':'pa_rw_enable_nomsg')); ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="rw_enable" value="<?php echo lang('pa_rw_enable_conf'); ?>" />
		</div>
		<?php endif; ?>
		<?php endif; ?>

