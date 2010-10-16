<?php if ($am_part == "am_showall"): ?>
		<div class="sublink">
			<a href="<?php echo manage_url('admin.php','forum-admin.html').'?act=am&amp;sub=2'; ?>"><?php echo lang('pa_automessages_add'); ?></a>
		</div>
		
		<?php if (count($pa_am_automessages)>0): ?>
		<div class="centerforminput">
			<table class="table" border="0" cellspacing="1" cellpadding="4">
				<tr class="titlerow">
					<td class="am_name">
						<?php echo lang('pa_am_name_title'); ?>
					</td>
					<td class="am_options">
						<?php echo lang('pa_am_options_title'); ?>
					</td>
				</tr>

				<?php foreach ($pa_am_automessages as $mess): ?>
				<tr>
					<td class="am_name">
						<?php echo $mess['pa_am_name']; ?>
					</td>
					<td class="am_options">
						<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=am&amp;sub=1&amp;see=<?php echo $mess['pa_am_id']; ?>"><?php echo lang('see'); ?></a> - <a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=am&amp;sub=2&amp;edit=<?php echo $mess['pa_am_id']; ?>"><?php echo lang('edit'); ?></a> - <a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=am&amp;sub=1&amp;delete=<?php echo $mess['pa_am_id']; ?>"><?php echo lang('delete'); ?></a>
					</td>
				</tr>
				<?php endforeach; ?>

			</table>
		</div>
		<?php endif; ?>
		<?php if (isset($pa_previs)): ?>
		<div class="centerformtext">
			<?php echo lang('pa_am_previs'); ?> : <?php echo $pa_previs_name; ?>
		</div>
		<div class="forminput">
			<?php echo $pa_previs; ?>
		</div>
		<?php endif; ?>
<?php elseif ($am_part == "am_add"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_am_add_name_title'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="am_name" value="<?php echo $pa_am_add_name; ?>" size="40" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_am_add_message_title'); ?>
		</div>
		<div class="centerforminput">
			<?php $ta_opt = array('name' => 'am_message', 'id' =>  'message', 'value' => $pa_am_add_message); ?>
			<?php include($template_path.'menu_writemsg.php'); ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="am_send" value="<?php echo lang('confirm'); ?>" />
		</div>
<?php endif; ?>
