<?php if ($str_part == "structure"): ?>
		<div class="sublink">
			<a href="<?php echo manage_url('admin.php','forum-admin.html').'?act=str&amp;sub=2'; ?>"><?php echo lang('pa_structure_addforum'); ?></a>
		</div>
		<div class="sublink">
			<a href="<?php echo manage_url('admin.php','forum-admin.html').'?act=str&amp;sub=3'; ?>"><?php echo lang('pa_structure_addtopicgroup'); ?></a>
		</div>
		
		<div class="centerforminput">
			<table class="str_table" border="0" cellspacing="1" cellpadding="4">
				<tr class="str_ttl">
					<td class="str_tgop2">
						<?php echo lang('pa_str_op2_title'); ?>
					</td>
					<td class="str_tgname">
						<?php echo lang('pa_str_tgname_title'); ?>
					</td>
					<td class="str_tgop1">
						<?php echo lang('pa_str_op1_title'); ?>
					</td>
				</tr>

				<?php foreach ($pa_fnames as $fid => $fname): ?>
				<tr class="str_f">
					<td class="str_tgop2">
						<?php if (!empty($pa_f_updown[$fid]['up'])): ?><a href="<?php echo manage_url('admin.php', 'forum-admin.html').'?act=str&amp;sub=1&amp;'.$pa_f_updown[$fid]['up']; ?>" class="up" title="<?php echo lang('pa_str_up'); ?>"><span><?php echo lang('pa_str_up'); ?></span></a><?php endif; ?>
						<?php if (!empty($pa_f_updown[$fid]['down'])): ?><a href="<?php echo manage_url('admin.php', 'forum-admin.html').'?act=str&amp;sub=1&amp;'.$pa_f_updown[$fid]['down']; ?>" class="down" title="<?php echo lang('pa_str_down'); ?>"><span><?php echo lang('pa_str_down'); ?></span></a><?php endif; ?>
					</td>
					<td class="str_tgname">
						<?php echo $fname; ?>
					</td>
					<td class="str_tgop1">
						<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=str&amp;sub=4&amp;deleteforum=<?php echo $fid; ?>"><?php echo lang('pa_str_delete'); ?></a> - <a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=str&amp;sub=2&amp;editforum=<?php echo $fid; ?>"><?php echo lang('pa_str_edit'); ?></a>
					</td>
				</tr>
				<?php 
				if (isset($pa_structure_f[$fid])) {
					foreach ($pa_structure_f[$fid] as $tgid) {
						$indent = 0;
						include ($template_path.'topicgroup_item_s.php');
					} 
				}
				?>
				<?php endforeach; ?>

			</table>
		</div>
		
		<div class="sublink">
			<a href="<?php echo manage_url('admin.php','forum-admin.html').'?act=str&amp;sub=2'; ?>"><?php echo lang('pa_structure_addforum'); ?></a>
		</div>
		<div class="sublink">
			<a href="<?php echo manage_url('admin.php','forum-admin.html').'?act=str&amp;sub=3'; ?>"><?php echo lang('pa_structure_addtopicgroup'); ?></a>
		</div>
<?php elseif ($str_part == "addforum"): ?>
		<div class="centerformtext">
			<?php echo lang('name'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="fname" maxlength="60" size="30" value="<?php echo $pa_str_fname; ?>" />
		</div>
		<div class="centerformtext">
			<input type="submit" name="newforum" value="<?php echo lang(array('item' => $pa_addforum_submit)); ?>" />
		</div>
<?php elseif ($str_part == "addtg"): ?>
		<div class="centerformtext">
			<?php echo lang('name'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="tgname" maxlength="60" size="30" value="<?php echo $pa_addtg_name_in; ?>" />
		</div>
		<div class="centerformtext">
			<?php echo lang('comment'); ?><br />
			<?php echo lang('infomessage'); ?>
		</div>
		<div class="centerforminput">
			<textarea name="tgcomment" style="width:400px;height:50px;"><?php echo $pa_addtg_comment_in; ?></textarea>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_addtopicgroup_link'); ?><br />
			<?php echo lang('pa_addtopicgroup_link2'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="tglink" maxlength="100" size="30" value="<?php echo $pa_addtg_link; ?>" />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_addtopicgroup_forum'); ?>
		</div>
		<div class="centerforminput">
			<?php echo $pa_addtg_forummenu; ?>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_addtopicgroup_visibility'); ?>
		</div>
		<div class="centerforminput">
			<?php echo lang('pa_addtopicgroup_visibility_normal'); ?> <input type="radio" name="visibility" value="normal" <?php echo $pa_addtg_vis_checkvis; ?> /> <?php echo lang('pa_addtopicgroup_visibility_hidden'); ?> <input type="radio" name="visibility" value="hide" <?php echo $pa_addtg_vis_checkhid; ?> /><br />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_addtopicgroup_userrights'); ?><br />
			<?php echo lang('pa_classauth'); ?>
		</div>
		<div class="centerforminput">
			<table class="table" border="0" cellspacing="1" cellpadding="4">
				<tr class="titlerow">
					<td class="group_title">
						<?php echo lang('pa_existingclasses'); ?>
					</td>
					<td class="group_auth_see">
						<?php echo lang('pa_auth_see'); ?>
					</td>
					<td class="group_auth_reply">
						<?php echo lang('pa_auth_reply'); ?>
					</td>
					<td class="group_auth_create">
						<?php echo lang('pa_auth_create'); ?>
					</td>
				</tr>

				<?php foreach ($pa_addtg_userrights_groups as $group): ?>
				<tr>
					<td class="group_title">
						<?php echo $group['name']; ?>
					</td>
					<td class="group_auth_see">
						<input type="checkbox" id="see_<?php echo $group['id']; ?>" onclick="javascript:authfunc('see',<?php echo $group['id']; ?>);" name="see_<?php echo $group['id']; ?>"<?php if ($group['see']): ?> checked="checked"<?php endif; ?> />
					</td>
					<td class="group_auth_reply">
						<input type="checkbox" id="reply_<?php echo $group['id']; ?>" onclick="javascript:authfunc('reply',<?php echo $group['id']; ?>);" name="reply_<?php echo $group['id']; ?>"<?php if ($group['reply']): ?> checked="checked"<?php endif; ?> />
					</td>
					<td class="group_auth_create">
						<input type="checkbox" id="create_<?php echo $group['id']; ?>" onclick="javascript:authfunc('create',<?php echo $group['id']; ?>);" name="create_<?php echo $group['id']; ?>"<?php if ($group['create']): ?> checked="checked"<?php endif; ?> />
					</td>
				</tr>
				<?php endforeach; ?>

			</table>
		</div>
		<div class="centerformtext">
			<input type="submit" name="newtopicgroup" value="<?php echo lang(array('item' => $pa_addtg_submit)); ?>" /><br />
		</div>
<?php elseif ($str_part == "confirm"): ?>
		<div class="centerformtext">
			<?php echo $pa_delete_message; ?>
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_structure_wftopics'); ?>
		</div>
		<div class="centerforminput">
			<input type="radio" name="delete_topics" value="yes" checked="checked"><?php echo lang('pa_structure_deletetopics_yes'); ?><br />
			<input type="radio" name="delete_topics" value="no"><?php echo lang('pa_structure_deletetopics_no'); ?><br />
			<?php echo $pa_delete_tgmenu; ?>
		</div>
		<div class="centerformtext">
			<input type="submit" name="deleteitem" value="<?php echo lang('confirm'); ?>" />
		</div>
<?php endif; ?>
