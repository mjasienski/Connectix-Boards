
<?php
$menu=&$m_menu;
include ($template_path.'menu_links.php');
?>

<form action="<?php echo $m_formaction; ?>" method="post" id="form_modpanel">
<?php if ( $m_contents == 'punishuser' ): ?>
	<div class="container">
		<h2><?php echo $title_pre; ?><?php echo lang('mod_punish'); ?></h2>
		<div class="subcontainer">
			<?php if ($m_selectuser): ?>
			<fieldset>
				<legend><?php echo lang('mod_selectuser'); ?></legend>
				<p>
					<label><input type="radio" name="selectuser_type" value="id" class="radiobutton" checked="checked" /> <?php echo lang('mod_selectuser_id'); ?></label> &nbsp;&nbsp;&nbsp; <input type="text" name="selectuser_id" size="10" value="" /><br />
					<label><input type="radio" name="selectuser_type" value="name" class="radiobutton" /> <?php echo lang('mod_selectuser_name'); ?></label> &nbsp;&nbsp;&nbsp; <input type="text" name="selectuser_name" size="30" />
				</p>
			</fieldset>
			<div class="confirm">
				<input type="submit" name="punish" value="<?php echo lang('confirm'); ?>" />
			</div>
			<?php else: ?>
			<fieldset>
				<legend><?php echo lang('mod_selecteduser'); ?></legend>
				<p>
					<?php echo lang('mod_selecteduser_done'); ?> : <?php echo $m_moduser_link; ?>
				</p>
				<p>
					<?php echo lang('mod_selecteduser_reputation'); ?> : <?php echo lang('reput_'.(int)$m_moduser_reputation); ?><br />
					<?php echo getReputation ($m_moduser_reputation,$m_moduser_id); ?>
				</p>
				<p>
					<?php if ($m_moduser_punished): ?>
					<?php echo lang('mod_selecteduser_punished'); ?> : <?php echo lang(array('item' => 'mod_'.$m_moduser_pun_type)); ?>.<br />
					<?php echo lang('mod_selecteduser_endpunished'); ?> : <?php echo $m_moduser_pun_time; ?>.<br />
					<a href="<?php echo manage_url('index.php?act=mods&amp;page=2&amp;cancel='.$m_moduser_id, 'forum-moderators.html?page=2&amp;cancel='.$m_moduser_id); ?>"><?php echo lang('mod_sp_cancel'); ?></a>
					<?php else: ?>
					<?php echo lang('mod_selecteduser_notpunished'); ?>
					<?php endif; ?>
				</p>
			</fieldset>
			<fieldset>
				<legend><?php echo lang('mod_changereputation'); ?></legend>
				<p>
					<?php echo lang('mod_reputation_info'); ?>
				</p>
				<p>
					<label><strong><?php echo lang('mod_newreputation'); ?></strong> : &nbsp;
					<select name="newreputation">
						<?php for($i=0;$i<=5;$i++): ?>
						<option value="<?php echo $i; ?>" <?php echo (($m_moduser_reputation==$i)?'selected="selected"':''); ?>><?php echo lang('reput_'.$i); ?></option>
						<?php endfor; ?>
					</select></label>
				</p>
				<p>
					<input type="submit" name="changereputation" value="<?php echo lang('mod_changereputation'); ?>" />
				</p>
			</fieldset>
			<fieldset>
				<legend><?php echo lang('mod_punishtype_title'); ?></legend>
				<p>
					<label><strong><?php echo lang('mod_punishtype'); ?></strong> : &nbsp;
					<select name="punishtype">
						<option value="readonly"><?php echo lang('mod_readonly'); ?></option>
						<option value="ban"><?php echo lang('mod_ban'); ?></option>
					</select></label>
				</p>
				<p>
					<label><strong><?php echo lang('mod_punishtime'); ?></strong> : &nbsp; <input type="text" name="punishtime" value="2" size="10" /></label>
				</p>
				<p>
					<input type="submit" name="punish" value="<?php echo lang('mod_reallypunish'); ?>" /><br />
					<?php echo lang('mod_punish_info'); ?>
				</p>
			</fieldset>
			<fieldset>
				<legend><?php echo lang('mod_notes'); ?></legend>
				
				<?php if (count($m_moduser_notes) > 0): ?>
				<?php foreach ($m_moduser_notes as $note): ?>
				<div class="mod_note">
					<?php echo lang('by'); ?> <?php echo $note['user']; ?> (<?php echo $note['date']; ?>)
					<?php if (isset($note['mod'])): ?>
					 - <?php echo $note['mod']; ?>
					<?php endif; ?>
					<?php if (isset($note['note'])): ?>
					<hr /><?php echo $note['note']; ?>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
				<?php else: ?>
				<p>
					<?php echo lang('mod_nonote'); ?>
				</p>
				<?php endif; ?>
				<p>
					<label><strong><?php echo lang('mod_addnote'); ?></strong> : <br />
					<textarea name="note" id="note"></textarea></label>
				</p>
				<p>
					<input type="submit" name="sendnote" value="<?php echo lang('mod_savenote'); ?>" />
				</p>
			</fieldset>
			<div class="clearfix"></div>
			<?php endif; ?>
		</div>
	</div>
<?php elseif ($m_contents == 'showpunished'): ?>
	<?php if ( count($m_punishedusers) > 0 ): ?>
	<div class="table" id="table_punished">
	<table>
		<caption>
			<?php echo $title_pre; ?><?php echo lang('mod_showpunished'); ?>
		</caption>

		<thead>
			<tr>
				<th class="sp_name">
					<?php echo lang('mod_sp_name'); ?>
				</th>
				<th class="sp_type">
					<?php echo lang('mod_sp_type'); ?>
				</th>
				<th class="sp_timeleft">
					<?php echo lang('mod_sp_timeleft'); ?>
				</th>
				<th class="sp_options">
					<?php echo lang('mod_sp_options'); ?>
				</th>
			</tr>
		</thead>

		<tfoot>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
		</tfoot>

		<tbody>
			<?php foreach ($m_punishedusers as $user): ?>
			<tr class="field1">
				<td class="sp_name">
					<a href="<?php echo manage_url('index.php?act=mods&amp;page=2&amp;punish='.$user['usr_id'], 'forum-moderators.html?page=2&amp;punish='.$user['usr_id']); ?>"><?php echo $user['usr_name']; ?></a>
				</td>
				<td class="sp_type">
					<?php echo lang(array('item' => 'mod_'.$user['pun_type'])); ?>
				</td>
				<td class="sp_timeleft">
					<?php echo $user['pun_time']; ?>
				</td>
				<td class="sp_options">
					<a href="<?php echo manage_url('index.php?act=mods&amp;page=3&amp;cancel='.$user['usr_id'], 'forum-moderators.html?page=3&amp;cancel='.$user['usr_id']); ?>"><?php echo lang('mod_sp_cancel'); ?></a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	</div>
	<?php else: ?>
	<div class="container">
		<h2><?php echo $title_pre; ?><?php echo lang('mod_showpunished'); ?></h2>
		<div class="subcontainer">
			<p class="inforow">
				<?php echo lang('mod_showpunished_noone'); ?>
			</p>
		</div>
	</div>
	<?php endif; ?>
<?php elseif ($m_contents == 'showreports'): ?>
	<?php if ( !empty($m_showreports) ): ?>
	<div class="table" id="table_reports">
	<table>
		<caption><?php echo $title_pre; ?><?php echo lang('mod_badmessages'); ?></caption>

		<thead>
			<tr>
				<th class="rep_user">
					<?php echo lang('mod_rep_user'); ?>
				</th>
				<th class="rep_time">
					<?php echo lang('mod_rep_time'); ?>
				</th>
				<th class="rep_desc">
					<?php echo lang('mod_rep_desc'); ?>
				</th>
				<th class="rep_options">
					<?php echo lang('mod_rep_options'); ?>
				</th>
			</tr>
		</thead>

		<tfoot>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
		</tfoot>

		<tbody>
			<?php foreach ($m_showreports as $report): ?>
			<tr class="field<?php echo manage_cycle('1,2'); ?>">
				<td class="rep_user">
					<a href="<?php echo manage_url('index.php?act=user&amp;showprofile='.$report['usr_id'], 'forum-m'.$report['usr_id'].','.rewrite_words($report['usr_name']).'.html'); ?>"><?php echo $report['usr_name']; ?></a>
				</td>
				<td class="rep_time">
					<?php echo $report['rep_time']; ?>
				</td>
				<td class="rep_desc">
					<?php echo $report['rep_desc']; ?>
				</td>
				<td class="rep_options">
					<a href="<?php echo manage_url('index.php?showtopic='.$report['rep_topic'].'&amp;message='.$report['rep_message'], 'forum-t'.$report['rep_topic'].'-m'.$report['rep_message'].'.html'); ?>"><?php echo lang('mod_rep_see'); ?></a> - <a href="<?php echo manage_url('index.php?act=mods&amp;page=1&amp;delete='.$report['rep_id'], 'forum-moderators.html?page=1&amp;delete='.$report['rep_id']); ?>"><?php echo lang('mod_rep_cancel'); ?></a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	</div>
	<?php else: ?>
	<div class="container">
		<h2><?php echo $title_pre; ?><?php echo lang('mod_showreports'); ?></h2>
		<div class="subcontainer">
			<p class="inforow">
				<?php echo lang('mod_rep_nomessages'); ?>
			</p>
		</div>
	</div>
	<?php endif; ?>
<?php elseif ($m_contents == "showautomessages"): ?>
	<?php if ( count($m_automessages) > 0 ): ?>
	<div class="container">
		<h2><?php echo $title_pre; ?><?php echo lang('mod_showautomessages'); ?></h2>
		<div class="subcontainer">
			<?php foreach ($m_automessages as $automessage): ?>
			<div class="am_field">
				<strong><?php echo $automessage['m_showam_title']; ?></strong><hr />
				<?php echo $automessage['m_showam']; ?>
			</div>
			<?php endforeach; ?>
			<div class="clearfix"></div>
		</div>
	</div>
	<?php else: ?>
	<div class="container">
		<h2><?php echo $title_pre; ?><?php echo lang('mod_showautomessages'); ?></h2>
		<div class="subcontainer">
			<p class="inforow">
				<?php echo lang('mod_am_nomessage'); ?>
			</p>
		</div>
	</div>
	<?php endif; ?>
<?php endif; ?>
</form>
