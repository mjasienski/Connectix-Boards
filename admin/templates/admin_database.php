<?php if ($db_part == "deleteold"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_db_deleteold_warning'); ?>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_db_deleteold_selectdate'); ?>
		</div>
		<div class="centerforminput">
			<?php echo lang(array('item' => 'pa_db_deleteold_selectdate_criterion', 'input' => $db_deleteold_selectdate_input)); ?>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_db_deleteold_selecttype'); ?>
		</div>
		<div class="centerforminput">
			<input type="checkbox" name="deleteold_normal" <?php echo $db_deleteold_selecttype_normal_checked; ?> /> <?php echo lang('pa_db_deleteold_selecttype_normal'); ?><br />
			<input type="checkbox" name="deleteold_pinned" <?php echo $db_deleteold_selecttype_pinned_checked; ?> /> <?php echo lang('pa_db_deleteold_selecttype_pinned'); ?><br />
			<input type="checkbox" name="deleteold_announce" <?php echo $db_deleteold_selecttype_announce_checked; ?> /> <?php echo lang('pa_db_deleteold_selecttype_announce'); ?><br />
			<input type="checkbox" name="deleteold_replied" <?php echo $db_deleteold_selecttype_replied_checked; ?> /> <?php echo lang('pa_db_deleteold_selecttype_replied'); ?>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_db_deleteold_selectlocation'); ?>
		</div>
		<div class="centerforminput">
			<?php echo $db_deleteold_selectlocation_choose; ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="deleteoldtopics" value="<?php echo lang('pa_db_deleteold_submit'); ?>" />
		</div>
		<br /><br /><br />
		<div class="centerformtext">
			<?php echo lang('pa_db_deleteoldusers_info'); ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="deleteoldusers" value="<?php echo lang('pa_db_deleteoldusers_submit'); ?>" />
		</div>
		<br /><br /><br />
		<div class="centerformtext">
			<?php echo lang('pa_db_deleteoldlogentries_info'); ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="deleteoldlogentries" value="<?php echo lang('pa_db_deleteoldlogentries_submit'); ?>" />
		</div>
		<br /><br /><br />
		<div class="centerformtext">
			<?php echo lang('pa_db_deleteoldtopictrackers_info'); ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="deleteoldtopictrackers" value="<?php echo lang('pa_db_deleteoldtopictrackers_submit'); ?>" />
		</div>
<?php elseif ($db_part == "dump"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_db_dump_info'); ?>
		</div>
		<div class="centerformtext">
			<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=db&amp;dump=1"><?php echo lang('pa_db_dump'); ?></a>
		</div>
<?php elseif ($db_part == "reset"): ?>
		<div class="centerformtext">
			<?php echo lang(array('item' => 'pa_db_reset_info', 'version' => $db_reset_version)); ?>
		</div>
		<br /><br />
		<div class="centerformtext">
			<?php echo lang('pa_db_reset_upload'); ?>
		</div>
		<div class="centerforminput">
			<input type="file" name="dumped_db" class="input_file" size="50" />
		</div>
		<div class="centerformtext">
			<input type="submit" name="reset_db" value="<?php echo lang('pa_db_reset'); ?>" />
		</div>
		<br /><br />
		<div class="centerformtext">
			<?php echo lang('pa_db_reset_ftp'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="dumped_db_ftp" size="50" />
		</div>
		<div class="centerformtext">
			<input type="submit" name="reset_db_ftp" value="<?php echo lang('pa_db_reset'); ?>" />
		</div>
<?php endif; ?>
