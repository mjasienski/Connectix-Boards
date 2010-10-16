
		<table class="table" border="0" cellspacing="1" cellpadding="4">
			<tr class="titlerow">
				<td class="cfg_name">
					<?php echo lang('pa_cfg_fieldname'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_cfg_explane'); ?>
				</td>
				<td class="cfg_param">
					<?php echo lang('pa_cfg_param'); ?>
				</td>
			</tr>

			<tr>
				<td colspan="3" class="cfg_cat">
					<?php echo lang('pa_cfg_general'); ?>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_forumname'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_forumname_info'); ?>
				</td>
				<td class="cfg_param">
					<input type="text" name="forumname" maxlength="60" size="30" value="<?php echo $pa_c_fname; ?>"/>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_forumowner'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_forumowner_info'); ?>
				</td>
				<td class="cfg_param">
					<input type="text" name="forumowner" maxlength="60" size="30" value="<?php echo $pa_c_fowner; ?>"/>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_supportmail'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_supportmail_info'); ?>
				</td>
				<td class="cfg_param">
					<input type="text" name="supportmail" maxlength="60" size="30" value="<?php echo $pa_c_supportmail; ?>"/>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_backtowebsite'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_backtowebsite_info'); ?>
				</td>
				<td class="cfg_param">
					<input type="text" name="website" maxlength="60" size="30" value="<?php echo $pa_c_backtowebsite; ?>"/>
				</td>
			</tr>

			<tr>
				<td colspan="3" class="cfg_cat">
					<?php echo lang('pa_cfg_display'); ?>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_defaultstyle'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_defaultstyle_info'); ?>
				</td>
				<td class="cfg_param">
					<?php echo $pa_c_defstyle; ?>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_defaultlanguage'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_defaultlanguage_info'); ?>
				</td>
				<td class="cfg_param">
					<?php echo $pa_c_deflanguage; ?>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_maxsize'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_maxsize_info'); ?>
				</td>
				<td class="cfg_param">
					<input type="text" name="maxsize" maxlength="5" size="5" value="<?php echo $pa_c_maxsize; ?>"/>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_displayfastredirect'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_displayfastredirect_info'); ?>
				</td>
				<td class="cfg_param">
					<?php echo lang('yes'); ?> <input type="radio" name="displayfastredirect" value="yes" <?php echo $pa_c_displayfastredirect_yes_checked; ?> /> <?php echo lang('no'); ?> <input type="radio" name="displayfastredirect" value="no" <?php echo $pa_c_displayfastredirect_no_checked; ?> />
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_timeconfig'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_timeconfig_info'); ?>
				</td>
				<td class="cfg_param">
					<select name="timezone">
						<?php foreach ($pa_c_timezones as $key => $value): ?>
						<option value="<?php echo $key; ?>"<?php if ($key == $pa_c_timezone_default): ?> selected="selected"<?php endif;?>><?php echo $value; ?></option>
						<?php endforeach; ?>
					</select><br /><br />
					<?php echo lang('pa_timeconfig_summertime'); ?><br />
					<?php echo lang('yes'); ?> <input type="radio" name="summertime" value="yes" <?php echo $pa_c_summertime_yes_checked; ?> /> <?php echo lang('no'); ?> <input type="radio" name="summertime" value="no" <?php echo $pa_c_summertime_no_checked; ?> />
				</td>
			</tr>

			<tr>
				<td colspan="3" class="cfg_cat">
					<?php echo lang('pa_cfg_register'); ?>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_enablemail'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_enablemail_info'); ?>
				</td>
				<td class="cfg_param">
					<?php echo lang('yes'); ?> <input type="radio" name="enablemail" value="yes" <?php echo $pa_c_enablemail_yes_checked; ?> /> <?php echo lang('no'); ?> <input type="radio" name="enablemail" value="no" <?php echo $pa_c_enablemail_no_checked; ?> />
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_suspendregister'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_suspendregister_info'); ?>
				</td>
				<td class="cfg_param">
					<?php echo lang('yes'); ?> <input type="radio" name="suspend_register" value="yes" <?php echo $pa_c_suspendregister_yes_checked; ?> /> <?php echo lang('no'); ?> <input type="radio" name="suspend_register" value="no" <?php echo $pa_c_suspendregister_no_checked; ?> />
				</td>
			</tr>

			<tr>
				<td colspan="3" class="cfg_cat">
					<?php echo lang('pa_cfg_moderation'); ?>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_deletetopicallowed'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_deletetopicallowed_info'); ?>
				</td>
				<td class="cfg_param">
					<?php echo lang('yes'); ?> <input type="radio" name="deleteallowed" value="yes" <?php echo $pa_c_deletet_yes_checked; ?> /> <?php echo lang('no'); ?> <input type="radio" name="deleteallowed" value="no" <?php echo $pa_c_deletet_no_checked; ?> />
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_edittopictitle'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_edittopictitle_info'); ?>
				</td>
				<td class="cfg_param">
					<?php echo lang('yes'); ?> <input type="radio" name="edittopictitle" value="yes" <?php echo $pa_c_edittopictitle_yes_checked; ?> /> <?php echo lang('no'); ?> <input type="radio" name="edittopictitle" value="no" <?php echo $pa_c_edittopictitle_no_checked; ?> />
				</td>
			</tr>

			<tr>
				<td colspan="3" class="cfg_cat">
					<?php echo lang('pa_cfg_perfs'); ?>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_readornot_sessions'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_readornot_sessions_info'); ?>
				</td>
				<td class="cfg_param">
					<?php echo lang('yes'); ?> <input type="radio" name="readornot_sessions" value="yes" <?php echo $pa_c_readornot_sessions_yes_checked; ?> /> <?php echo lang('no'); ?> <input type="radio" name="readornot_sessions" value="no" <?php echo $pa_c_readornot_sessions_no_checked; ?> />
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_gzip_output'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_gzip_output_info'); ?>
				</td>
				<td class="cfg_param">
					<?php echo lang('yes'); ?> <input type="radio" name="gzip_output" value="yes" <?php echo $pa_c_gzip_output_yes_checked; ?> /> <?php echo lang('no'); ?> <input type="radio" name="gzip_output" value="no" <?php echo $pa_c_gzip_output_no_checked; ?> />
				</td>
			</tr>
			
			<tr>
				<td colspan="3" class="cfg_cat">
					<?php echo lang('pa_cfg_advanced'); ?>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_connectedlimit'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_connectedlimit_info'); ?>
				</td>
				<td class="cfg_param">
					<input type="text" name="connectedlimit" maxlength="5" size="5" value="<?php echo $pa_c_connectedlimit; ?>"/>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_floodlimit'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_floodlimit_info'); ?>
				</td>
				<td class="cfg_param">
					<input type="text" name="floodlimit" maxlength="5" size="5" value="<?php echo $pa_c_floodlimit; ?>"/>
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_enabletopictrack'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_enabletopictrack_info'); ?>
				</td>
				<td class="cfg_param">
					<?php echo lang('yes'); ?> <input type="radio" name="enabletopictrack" value="yes" <?php echo $pa_c_enabletopictrack_yes_checked; ?> /> <?php echo lang('no'); ?> <input type="radio" name="enabletopictrack" value="no" <?php echo $pa_c_enabletopictrack_no_checked; ?> />
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_displayconnected'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_displayconnected_info'); ?>
				</td>
				<td class="cfg_param">
					<?php echo lang('yes'); ?> <input type="radio" name="displayconnected" value="yes" <?php echo $pa_c_displayconnected_yes_checked; ?> /> <?php echo lang('no'); ?> <input type="radio" name="displayconnected" value="no" <?php echo $pa_c_displayconnected_no_checked; ?> />
				</td>
			</tr>
			<tr>
				<td class="cfg_name">
					<?php echo lang('pa_cookie_path'); ?>
				</td>
				<td class="cfg_explane">
					<?php echo lang('pa_cookie_path_info'); ?>
				</td>
				<td class="cfg_param">
					<input type="text" name="cookie_path" size="30" value="<?php echo $pa_cookie_path; ?>"/>
				</td>
			</tr>
		</table>
		
		
		
		<div class="centerformtext">
			<input type="submit" name="changeforumsettings" value="<?php echo lang('pa_changesett'); ?>" />
		</div>
