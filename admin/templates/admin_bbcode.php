<?php if ($bb_part == "bb_showall"): ?>
		<div class="sublink">
			<a href="<?php echo manage_url('admin.php','forum-admin.html').'?act=bb&amp;sub=2'; ?>"><?php echo lang('pa_bbcode_add'); ?></a>
		</div>
		
		<div class="centerforminput">
			<table class="table" border="0" cellspacing="1" cellpadding="4">
				<tr class="titlerow">
					<td class="bb_name">
						<?php echo lang('pa_bbcode_name'); ?>
					</td>
					<td class="bb_options">
						<?php echo lang('pa_bbcode_options'); ?>
					</td>
					<td class="bb_sign">
						<?php echo lang('pa_bbcode_signature'); ?>
					</td>
				</tr>

				<?php foreach ($pa_bb_bbcodes as $bbcode): ?>
				<tr>
					<td class="bb_name">
						<?php echo $bbcode['pa_bb_name']; ?> <?php if ($bbcode['pa_bb_args']): ?>(<?php echo lang('pa_bbcode_args'); ?>)<?php endif; ?>
					</td>
					<td class="bb_options">
						<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=bb&amp;sub=2&amp;edit=<?php echo $bbcode['pa_bb_name']; ?><?php if ($bbcode['pa_bb_args']): ?>=<?php endif; ?>"><?php echo lang('edit'); ?></a> - <a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=bb&amp;sub=1&amp;delete=<?php echo $bbcode['pa_bb_name']; ?><?php if ($bbcode['pa_bb_args']): ?>=<?php endif; ?>"><?php echo lang('delete'); ?></a>
					</td>
					<td class="bb_sign">
						<input type="checkbox" name="sign_<?php echo $bbcode['pa_bb_name']; ?><?php if ($bbcode['pa_bb_args']): ?>=<?php endif; ?>" <?php if ($bbcode['pa_bb_sign']): ?>checked="checked"<?php endif; ?> />
					</td>
				</tr>
				<?php endforeach; ?>

			</table>
		</div>
		<div class="centerformtext">
			<input type="submit" name="bb_submit" value="<?php echo lang('confirm'); ?>" />
		</div>
<?php elseif ($bb_part == "bb_add"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_bbcode_name_title'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="bb_name" value="<?php echo $pa_bbcode_add_name; ?>" size="40" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_bbcode_args_add'); ?>
		</div>
		<div class="centerforminput">
			<textarea name="bb_args" rows="6" cols="70"><?php echo $pa_bbcode_add_args; ?></textarea>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_bbcode_parse'); ?>
		</div>
		<div class="centerforminput">
			<input type="checkbox" name="bb_parse" <?php echo $pa_bbcode_add_parse; ?> />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_bbcode_addsize'); ?>
		</div>
		<div class="centerforminput">
			<input type="checkbox" name="bb_addsize" <?php echo $pa_bbcode_add_addsize; ?> />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_bbcode_funcont'); ?>
		</div>
		<div class="centerforminput">
			<?php echo $pa_bbcode_add_funcont_before; ?><br />
			<textarea name="bb_funcont" rows="6" cols="70"><?php echo $pa_bbcode_add_funcont; ?></textarea><br />
			<?php echo $pa_bbcode_add_funcont_after; ?>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_bbcode_funargs'); ?>
		</div>
		<div class="centerforminput">
			<?php echo $pa_bbcode_add_funargs_before; ?><br />
			<textarea name="bb_funargs" rows="6" cols="70"><?php echo $pa_bbcode_add_funargs; ?></textarea><br />
			<?php echo $pa_bbcode_add_funargs_after; ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="bb_send" value="<?php echo lang('confirm'); ?>" />
		</div>
<?php elseif ($bb_part == "sm_showall"): ?>
		<div class="sublink">
			<a href="<?php echo manage_url('admin.php','forum-admin.html').'?act=bb&amp;sub=4'; ?>"><?php echo lang('pa_smileys_add'); ?></a>
		</div>
		<div class="sublink">
			<a href="<?php echo manage_url('admin.php','forum-admin.html').'?act=bb&amp;sub=5&amp;create=new'; ?>"><?php echo lang('pa_smileys_create'); ?></a>
		</div>
		
		<div class="sm_optmenu">
			<input type="submit" name="sm_confirm_form" value="<?php echo lang('pa_sm_confirmformoptions'); ?>" /> <br />
			<input type="button" value="<?php echo lang('pa_sm_invertselection'); ?>" onClick='invertselection("sm_select[]");' /> - 
			<?php echo lang('pa_sm_forselection'); ?> : 
			<input type="submit" name="sm_mass_activate" value="<?php echo lang('pa_sm_activate'); ?>" />
			<input type="submit" name="sm_mass_noactivate" value="<?php echo lang('pa_sm_noactivate'); ?>" />
			<input type="submit" name="sm_mass_delete" value="<?php echo lang('delete'); ?>" />
		</div>
		<div class="centerforminput">
			<table class="table" border="0" cellspacing="1" cellpadding="4">
				<tr class="titlerow">
					<td class="sm_image">
						<?php echo lang('pa_smileys_image'); ?>
					</td>
					<td class="sm_name">
						<?php echo lang('pa_smileys_name'); ?>
					</td>
					<td class="sm_file">
						<?php echo lang('pa_smileys_file'); ?>
					</td>
					<td class="sm_options">
						<?php echo lang('pa_smileys_options'); ?>
					</td>
					<td class="sm_form1">
						<?php echo lang('pa_smileys_form_base'); ?>
					</td>
					<td class="sm_form2">
						<?php echo lang('pa_smileys_form_extended'); ?>
					</td>
					<td class="sm_checkbox">
					</td>
				</tr>

				<?php foreach ($pa_sm_smileys as $smiley): ?>
				<tr>
					<td class="sm_image">
						<?php echo $smiley['pa_sm_image']; ?>
					</td>
					<td class="sm_name">
						<?php echo $smiley['pa_sm_name']; ?>
					</td>
					<td class="sm_file">
						<?php echo $smiley['pa_sm_file']; ?>
					</td>
					<td class="sm_options">
						<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=bb&amp;sub=4&amp;editsmiley=<?php echo $smiley['pa_sm_id']; ?>"><?php echo lang('edit'); ?></a> - <?php if ($smiley['pa_sm_orig_used']): ?><a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=bb&amp;sub=3&amp;noactivatesmiley=<?php echo $smiley['pa_sm_id']; ?>"><?php echo lang('pa_sm_noactivate'); ?></a><?php else: ?><a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=bb&amp;sub=3&amp;activatesmiley=<?php echo $smiley['pa_sm_id']; ?>"><?php echo lang('pa_sm_activate'); ?></a><?php endif; ?><?php if (!$smiley['pa_sm_library']): ?> - <a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=bb&amp;sub=3&amp;deletesmiley=<?php echo $smiley['pa_sm_id']; ?>"><?php echo lang('delete'); ?></a><?php endif; ?>
					</td>
					<td class="sm_form1">
						<input type="radio" name="sm_form_<?php echo $smiley['pa_sm_id']; ?>" value="1"<?php if ($smiley['pa_sm_form'] == 1) echo ' checked="checked"'; ?> />
					</td>
					<td class="sm_form2">
						<input type="radio" name="sm_form_<?php echo $smiley['pa_sm_id']; ?>" value="2"<?php if ($smiley['pa_sm_form'] == 2) echo ' checked="checked"'; ?> />
					</td>
					<td class="sm_checkbox">
						<input type="checkbox" name="sm_select[]" value="<?php echo $smiley['pa_sm_id']; ?>" />
					</td>
				</tr>
				<?php endforeach; ?>

			</table>
		</div>
		<div class="sm_optmenu">
			<input type="submit" name="sm_confirm_form" value="<?php echo lang('pa_sm_confirmformoptions'); ?>" /> <br />
			<input type="button" value="<?php echo lang('pa_sm_invertselection'); ?>" onClick='invertselection("sm_select[]");' /> - 
			<?php echo lang('pa_sm_forselection'); ?> : 
			<input type="submit" name="sm_mass_activate" value="<?php echo lang('pa_sm_activate'); ?>" />
			<input type="submit" name="sm_mass_noactivate" value="<?php echo lang('pa_sm_noactivate'); ?>" />
			<input type="submit" name="sm_mass_delete" value="<?php echo lang('delete'); ?>" />
		</div>
<?php elseif ($bb_part == "sm_add"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_smileys_name_title'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="sm_name" value="<?php echo $pa_sm_add_name; ?>" size="40" />
		</div>
		<div class="centerformtext">
			<?php if ($pa_sm_add_readonly): ?><?php echo lang('pa_smileys_filename_orig'); ?><?php else: ?><?php echo lang('pa_smileys_filenameserver'); ?><?php endif; ?>
		</div>
		<div class="centerforminput">
			<input <?php if ($pa_sm_add_subdir != ""): ?>type="text"<?php else: ?>type="hidden"<?php endif; ?> name="sm_filenamesubdir" value="<?php echo $pa_sm_add_subdir; ?>" size="10" readonly="true" /> <input type="text" name="sm_filenameserver" value="<?php echo $pa_sm_add_filenameserver; ?>" size="40" <?php if ($pa_sm_add_readonly): ?>readonly="true"<?php endif; ?> />
		</div>
		<?php if ($pa_sm_add_readonly != true): ?>
		<div class="centerformtext">
			<?php echo lang('pa_smileys_upload_title'); ?>
		</div>
		<?php endif; ?>
		<div class="centerforminput">
		<?php if ($pa_sm_add_readonly != true): ?>
			<?php if ($pa_sm_add_uploadimage_old_filename != null): ?>
			<input type="radio" name="wherefile" class="radiobutton" value="old" checked="checked" /><?php echo lang('pa_smileys_upload_edit_old'); ?> <input type="text" name="sm_old_filenameserver" value="<?php echo $pa_sm_add_uploadimage_old_filename; ?>" size="20" readonly="true" /><br />
			<?php endif; ?>
			<input type="radio" name="wherefile" class="radiobutton" value="server" /><?php if ($pa_sm_add_edit): ?><?php echo lang('pa_smileys_upload_edit_server'); ?><?php else: ?><?php echo lang('pa_smileys_upload_server'); ?><?php endif; ?><br />
			<input type="radio" name="wherefile" class="radiobutton" value="upload" /><?php echo lang('pa_smileys_upload_upload'); ?> : <input type="file" name="uploadimage" class="input_file" size="40" />
		<?php endif; ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="sm_send" value="<?php echo lang('confirm'); ?>" />
		</div>
		
		<br /><br />
		
		<?php if ($pa_sm_massadd): ?>
		<div class="centerformtext">
			<?php echo lang('pa_smileys_massadd'); ?>
		</div>
		<div class="centerforminput">
			<table class="table" id="sm_massadd" border="0" cellspacing="1" cellpadding="4">
				<tr class="titlerow">
					<td class="sm_ma_image">
						<?php echo lang('pa_smileys_image'); ?>
					</td>
					<td class="sm_ma_name">
						<?php echo lang('pa_smileys_name'); ?>
					</td>
					<td class="sm_form1">
						<?php echo lang('pa_smileys_form_base'); ?>
					</td>
					<td class="sm_form2">
						<?php echo lang('pa_smileys_form_extended'); ?>
					</td>
				</tr>
				
				<?php $smid = 1; foreach ($pa_sm_massadd_notused as $smileypath): ?>
				<tr>
					<td class="sm_ma_image">
						<img src="<?php echo $smileypath; ?>" alt="<?php echo $smileypath; ?>" />
					</td>
					<td class="sm_ma_name">
						<input type="text" name="sm_name_<?php echo $smid; ?>" value="" size="6" />
						<input type="hidden" name="sm_path_<?php echo $smid; ?>" value="<?php echo $smileypath; ?>" />
					</td>
					<td class="sm_form1">
						<input type="radio" name="sm_form_<?php echo $smid; ?>" value="1" />
					</td>
					<td class="sm_form2">
						<input type="radio" name="sm_form_<?php echo $smid; ?>" value="2" checked="checked" />
					</td>
				</tr>
				<?php $smid++; endforeach; ?>

			</table>
		</div>
		<div class="centerformtext">
			<input type="submit" name="sm_ma_send" value="<?php echo lang('confirm'); ?>" />
		</div>
		
		<?php endif; ?>
		
<?php elseif ($bb_part == "sm_create"): ?>
		<?php if ($sm_stage == "bankselection"): ?>
		<h3><?php echo lang('pa_sm_bankselection'); ?></h3>
		<?php echo $sm_banks_form; ?>
		<?php elseif ($sm_stage == "creation"): ?>
		<h3><?php echo lang('pa_sm_creation'); ?></h3>
			<?php if ($sm_created): ?>
			<div class="centerformtext">
				<?php echo lang('pa_sm_preview_congrat'); ?>
			</div>
			<div class="centerforminput">
				<img src="<?php echo $sm_imgfile; ?>" alt="<?php echo lang('pa_sm_preview'); ?>" title="<?php echo lang('pa_sm_preview'); ?>" />
				<br /><?php echo lang('pa_sm_name_title'); ?> : <input type="text" name="sm_name" size="40" value="<?php echo $sm_name; ?>" />
				<br /><?php echo lang('pa_sm_filename_title'); ?> : <input type="text" name="sm_filenamesubdir" value="<?php echo $sm_filename_subdir; ?>" size="10" readonly="true" /> <input type="text" name="sm_filenameserver" size="40" value="<?php echo $sm_filenameserver; ?>" /> <input type="text" name="sm_filenameextension" value="<?php echo $sm_filename_extension; ?>" size="10" readonly="true" />
			</div>
			<div class="centerformtext">
				<input type="hidden" name="wherefile" value="created" />
				<input type="submit" name="sm_send" value="<?php echo lang('pa_sm_send_title'); ?>" />
			</div>
			<div class="centerforminput">
				&nbsp;
			</div>
			<div class="centerformtext">
				<?php echo lang('pa_sm_preview_modification'); ?> :
			</div>
			<div class="centerforminput">
				&nbsp;
			</div>
			<?php endif; ?>
			<div class="centerforminput">
				<input type="submit" name="sm_back" value="<?php echo lang('pa_sm_back'); ?>" />
			</div>
			<?php echo $sm_components_form; ?>
		<?php endif; ?>
<?php endif; ?>
