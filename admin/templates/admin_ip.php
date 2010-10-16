<?php if ($ip_part == "analyze_ip"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_ip_analyze_infos'); ?>
		</div>
		<br /><br />
		<div class="centerformtext">
			<?php echo lang('pa_ip_analyze_ip_insert'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="ip" value="<?php echo $analyze_ip; ?>" />
		</div>
		<div class="centerformtext">
			<input type="submit" name="analyze_ip" value="<?php echo lang('pa_ip_analyze_ip_confirm'); ?>" />
		</div>
		<br /><br />
		<?php if (count($analyze_results)>0): ?>
		<table class="table" border="0" cellspacing="1" cellpadding="4">
			<tr class="titlerow">
				<td class="anz_user">
					<?php echo lang('pa_ip_analyze_name'); ?>
				</td>
				<td class="anz_lastdate">
					<?php echo lang('pa_ip_analyze_lastdate'); ?>
				</td>
				<td class="anz_count">
					<?php echo lang('pa_ip_analyze_count'); ?>
				</td>
				<td class="anz_options">
					<?php echo lang('pa_ip_analyze_options'); ?>
				</td>
			</tr>

			<?php foreach ($analyze_results as $r): ?>
			<tr>
				<td class="anz_user">
					<a href="index.php?act=user&amp;showprofile=<?php echo $r['userid']; ?>"><?php echo $r['username']; ?></a>
				</td>
				<td class="anz_lastdate">
					<a href="index.php?showtopic=<?php echo $r['topicid']; ?>&amp;message=<?php echo $r['msgid']; ?>"><?php echo $r['lastdate']; ?></a>
				</td>
				<td class="anz_count">
					<?php echo $r['totalmsgs']; ?>
				</td>
				<td class="anz_options">
					<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=ip&amp;sub=4&amp;analyze=<?php echo $r['userid']; ?>"><?php echo lang('pa_ip_analyze_thisuser'); ?></a>
				</td>
			</tr>
			<?php endforeach; ?>

		</table>
		<?php endif; ?>
<?php elseif ($ip_part == "analyze_user"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_ip_analyze_infos'); ?>
		</div>
		<br /><br />
		<div class="centerformtext">
			<?php echo lang('pa_ip_analyze_user_insert'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="user" value="<?php echo $analyze_user; ?>" />
		</div>
		<div class="centerformtext">
			<input type="submit" name="analyze_user" value="<?php echo lang('pa_ip_analyze_user_confirm'); ?>" />
		</div>
		<br /><br />
		<?php if (count($analyze_results)>0): ?>
		<table class="table" border="0" cellspacing="1" cellpadding="4">
			<tr class="titlerow">
				<td class="anz_ip">
					<?php echo lang('pa_ip_analyze_ipad'); ?>
				</td>
				<td class="anz_lastdate">
					<?php echo lang('pa_ip_analyze_lastdate'); ?>
				</td>
				<td class="anz_count">
					<?php echo lang('pa_ip_analyze_count'); ?>
				</td>
				<td class="anz_options">
					<?php echo lang('pa_ip_analyze_options'); ?>
				</td>
			</tr>

			<?php foreach ($analyze_results as $r): ?>
			<tr>
				<td class="anz_ip">
					<?php echo $r['userip']; ?>
				</td>
				<td class="anz_lastdate">
					<a href="index.php?showtopic=<?php echo $r['topicid']; ?>&amp;message=<?php echo $r['msgid']; ?>"><?php echo $r['lastdate']; ?></a>
				</td>
				<td class="anz_count">
					<?php echo $r['totalmsgs']; ?>
				</td>
				<td class="anz_options">
					<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=ip&amp;sub=3&amp;analyze=<?php echo $r['userip']; ?>"><?php echo lang('pa_ip_analyze_thisip'); ?></a> - <a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=ip&amp;sub=2&amp;ban=<?php echo $r['userip']; ?>"><?php echo lang('pa_ip_analyze_banip'); ?></a>
				</td>
			</tr>
			<?php endforeach; ?>

		</table>
		<?php endif; ?>
<?php elseif ($ip_part == "ban_ip"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_ip_ban_infos'); ?>
		</div>
		<br /><br />
		<div class="centerformtext">
			<?php echo lang('pa_ip_ban_ip'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="ip" value="<?php echo $ban_ip; ?>" <?php if ($ban_editing): ?>readonly="readonly"<?php endif; ?> />
		</div>
		<div class="centerformtext">
			<?php echo lang('pa_ip_ban_expires'); ?>
		</div>
		<div class="centerforminput">
			<input type="text" name="expires" value="<?php echo $ban_expires; ?>" />
		</div>
		<div class="centerformtext">
			<input type="submit" name="ban_ip" value="<?php echo lang('pa_ip_ban_confirm'); ?>" />
		</div>
<?php elseif ($ip_part == "show_banned"): ?>
		<?php if (count($ip_banned)>0): ?>
		<table class="table" border="0" cellspacing="1" cellpadding="4">
			<tr class="titlerow">
				<td class="ban_ip">
					<?php echo lang('pa_ip_show_banned_name'); ?>
				</td>
				<td class="ban_expires">
					<?php echo lang('pa_ip_show_banned_expires'); ?>
				</td>
				<td class="ban_options">
					<?php echo lang('pa_ip_show_banned_options'); ?>
				</td>
			</tr>

			<?php foreach ($ip_banned as $ip): ?>
			<tr>
				<td class="ban_ip">
					<?php echo $ip['ban_ip']; ?>
				</td>
				<td class="ban_expires">
					<?php echo $ip['ban_expires']; ?>
				</td>
				<td class="ban_options">
					<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=ip&amp;sub=1&amp;delete=<?php echo $ip['ban_ip']; ?>"><?php echo lang('pa_ip_show_banned_cancel'); ?></a> - <a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=ip&amp;sub=2&amp;edit=<?php echo $ip['ban_ip']; ?>"><?php echo lang('pa_ip_show_banned_edit'); ?></a>
				</td>
			</tr>
			<?php endforeach; ?>

		</table>
		<?php else: ?>
		<div class="centerformtext">
			<?php echo lang('pa_ip_show_banned_nothing'); ?>
		</div>
		<?php endif; ?>
<?php elseif ($ip_part == "detect_double"): ?>
		<div class="centerformtext">
			<?php echo lang('pa_ip_detect_double_user'); ?>
		</div>
		<?php if (isset($analyze_results)): ?>
		<div class="centerforminput">
			<input type="text" name="dd_user" value="<?php echo $dd_usr; ?>" />
		</div>
		<div class="centerformtext">
			<input type="submit" name="detect_double" value="<?php echo lang('pa_ip_detect_double_confirm'); ?>" />
		</div>
		<br /><br />
		<?php if (count($analyze_results)>0): ?>
		<?php if ( !empty($ip_pagemenu) ) : ?>
		<div class="pa_pagemenu"><?php echo lang('pages'); ?> <?php echo $ip_pagemenu; ?></div>
		<?php endif; ?>
		<table class="table" border="0" cellspacing="1" cellpadding="4">
			<tr class="titlerow">
				<td class="dd_ip">
					<?php echo lang('pa_ip_analyze_ipad'); ?>
				</td>
				<td class="dd_options">
					<?php echo lang('pa_ip_analyze_options'); ?>
				</td>
				<td class="dd_user">
					<?php echo lang('pa_ip_analyze_name'); ?>
				</td>
				<td class="dd_options2">
					<?php echo lang('pa_ip_analyze_options'); ?>
				</td>
			</tr>

			<?php $pre_ip = ''; ?>
			<?php foreach ($analyze_results as $r): ?>
			<tr>
				<?php if ($r['userip'] != $pre_ip): ?>
				<td class="dd_ip">
					<?php echo $pre_ip = $r['userip']; ?>
				</td>
				<td class="dd_options">
					<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=ip&amp;sub=3&amp;analyze=<?php echo $r['userip']; ?>"><?php echo lang('pa_ip_analyze_thisip'); ?></a> - <a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=ip&amp;sub=2&amp;ban=<?php echo $r['userip']; ?>"><?php echo lang('pa_ip_analyze_banip'); ?></a>
				</td>
				<?php else: ?>
				<td class="dd_ip"> </td>
				<td class="dd_options"> </td>
				<?php endif; ?>
				<td class="dd_user">
					<a href="index.php?act=user&amp;showprofile=<?php echo $r['userid']; ?>"><?php echo $r['username']; ?></a>
				</td>
				<td class="dd_options2">
					 <a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=ip&amp;sub=4&amp;analyze=<?php echo $r['userid']; ?>"><?php echo lang('pa_ip_analyze_thisuser'); ?></a>
				</td>
			</tr>
			<?php endforeach; ?>

		</table>
		<?php if ( !empty($ip_pagemenu) ) : ?>
		<div class="pa_pagemenu"><?php echo lang('pages'); ?> <?php echo $ip_pagemenu; ?></div>
		<?php endif; ?>
		<?php endif; ?>
		<?php endif; ?>
<?php endif; ?>
