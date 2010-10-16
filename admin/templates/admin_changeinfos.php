		<div class="centerformtext" valign="top">
			<?php echo lang(array('item' => $pa_changeinfos_title)); ?><br />
			<?php echo lang(array('item' => $pa_changeinfos_info)); ?>
		</div>
		<div class="centerforminput">
			<textarea name="<?php echo $pa_inputfield; ?>" rows="12" cols="70"><?php echo $pa_changeinfos_msg; ?></textarea>
		</div>
		<div class="centerformtext">
			<input type="submit" name="changeforuminfo" value="<?php echo lang('pa_changefinfo_confirm'); ?>" />
		</div>
		<?php if ($pa_changeinfos_dynamic_fields): ?>
		<br /><br />
		<div class="centerformtext" valign="top">
			<?php echo lang('pa_changefinfo_dyn'); ?>
		</div>
		<div class="centerforminput">
			<textarea name="<?php echo $pa_inputfield; ?>_dyn" rows="12" cols="70"><?php echo $pa_changeinfos_msg_dyn; ?></textarea>
		</div>
		<div class="centerformtext">
			<input type="submit" name="changeforuminfo_dyn" value="<?php echo lang('pa_changefinfo_confirm'); ?>" />
		</div>
		<?php endif; ?>
