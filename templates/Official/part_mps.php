<form action="<?php echo $mp_formaction; ?>" method="post" id="form_mps">
<?php $menu = $mp_menu; ?><?php include ($template_path.'menu_links.php'); ?>

	<p class="mp_infos">
		<?php echo lang(array('item' => "mp_limitmessages", 'nbmp' => $mp_nbmp, 'all' => $mp_allowed)); ?>
	</p>

<?php if ($mp_contents == "inandoutbox"): ?>
	<?php if (!empty($mp_messages)): ?>
	<div class="mp_menu">
		<div class="mp_menu_pages">
			<?php if (!empty($mp_pagemenu)): ?><?php echo lang('pages'); ?> <?php echo $mp_pagemenu; ?><?php endif; ?>
		</div>
		<div class="mp_menu_forselection">
			<input type="button" value="<?php echo lang('mp_invertselection'); ?>" onClick='invertselection("<?php echo (($mp_typebox == 'inbox')?'messto[]':'messfrom[]'); ?>");' /> - <?php echo lang('mp_forselection'); ?> : <input type="submit" name="delete" value="<?php echo lang('mp_delete'); ?>" />
		</div>
	</div>
	<?php endif; ?>

	<?php if (empty($mp_messages)): ?>
	<div class="container">
		<h2><?php echo $title_pre; ?><?php echo lang('mp_title'); ?> - <?php if ($mp_typebox == "inbox"): ?><?php echo lang('mp_menu_recieved'); ?><?php else: ?><?php echo lang('mp_menu_sent'); ?><?php endif; ?></h2>
		<div class="subcontainer">
			<p class="inforow">
				<?php if ($mp_typebox == "inbox"): ?><?php echo lang('mp_nomessagesto'); ?><?php else: ?><?php echo lang('mp_nomessagesfrom'); ?><?php endif; ?>
			</p>
		</div>
	</div>
	<?php else: ?>
	<div class="table" id="table_mps_inoutbox">
	<table>
		<caption><?php echo $title_pre; ?><?php echo lang('mp_title'); ?> - <?php if ($mp_typebox == "inbox"): ?><?php echo lang('mp_menu_recieved'); ?><?php else: ?><?php echo lang('mp_menu_sent'); ?><?php endif; ?></caption>

		<thead>
			<tr>
				<th class="mp_tbl_readornot">
				</th>
				<th class="mp_tbl_subj">
					<?php echo lang('mp_subj'); ?>
				</th>
				<th class="mp_tbl_date">
					<?php echo lang('mp_date'); ?>
				</th>
				<th class="mp_tbl_sender">
					<?php if ($mp_typebox == "inbox"): ?><?php echo lang('mp_from'); ?><?php else: ?><?php echo lang('mp_to'); ?><?php endif; ?>
				</th>
				<th class="mp_tbl_delete">
				</th>
				<th class="mp_tbl_radio">
				</th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
			</tr>
		</tfoot>

		<tbody>
			<?php foreach ($mp_messages as $message): ?>
			<tr id="mp_<?php echo $message['mp_m_id']; ?>" class="field<?php echo manage_cycle('1,2'); ?> navitem">
				<td class="mp_tbl_readornot">
					<?php echo $message['mp_m_read']; ?>
				</td>
				<td class="mp_tbl_subj">
					<?php echo $message['mp_m_subject']; ?>
				</td>
				<td class="mp_tbl_date">
					<?php echo $message['mp_m_date']; ?>
				</td>
				<td class="mp_tbl_sender">
					<?php echo $message['mp_m_userlink']; ?>
				</td>
				<td class="mp_tbl_delete">
					<?php echo $message['mp_m_delete']; ?>
				</td>
				<td class="mp_tbl_radio">
					<?php echo $message['mp_m_checkbox']; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	</div>
	<div class="mp_menu">
		<div class="mp_menu_pages">
			<?php if (!empty($mp_pagemenu)): ?><?php echo lang('pages'); ?> <?php echo $mp_pagemenu; ?><?php endif; ?>
		</div>
		<div class="mp_menu_forselection">
			<input type="button" value="<?php echo lang('mp_invertselection'); ?>" onClick='invertselection("<?php echo (($mp_typebox == 'inbox')?'messto[]':'messfrom[]'); ?>");' /> - <?php echo lang('mp_forselection'); ?> : <input type="submit" name="delete" value="<?php echo lang('mp_delete'); ?>" />
		</div>
	</div>
	<?php endif; ?>
<?php elseif ($mp_contents == "writing"): ?>
	<div class="container" id="form_mps_writing">
		<h2><?php echo $title_pre; ?><?php echo lang('mp_title'); ?> - <?php echo lang('mp_menu_write'); ?></h2>
		<div class="subcontainer">
			<?php if (isset($mp_w_previs_contents)): ?>
			<fieldset>
				<legend><?php echo lang('mp_previs_in'); ?></legend>
				<p>
					<?php echo $mp_w_previs_contents; ?>
				</p>
			</fieldset>
			<?php endif; ?>
			<fieldset>
				<legend><?php echo lang('mp_to_select'); ?></legend>
				<p>
					<label><strong><?php echo lang('mp_to'); ?></strong><br />
					<input type="text" name="mp_to" size="68" value="<?php echo $mp_w_to; ?>" /></label>
				</p>
			</fieldset>
			<fieldset>
				<legend><?php echo lang('mp_mess_write'); ?></legend>
				<p>
					<label><strong><?php echo lang('mp_subj'); ?></strong><br />
					<input type="text" name="mp_subj" size="68" value="<?php echo $mp_w_subject; ?>" maxlength="50" /></label>
				</p>
				<p>
					<strong><?php echo lang('mp_mess'); ?></strong><br />
					<?php $ta_opt = array('name' => 'mp_mess', 'id' =>  'message', 'rows' => 15, 'cols' => 50, 'value' => $mp_w_message); ?>
					<?php include($template_path.'menu_writemsg.php'); ?>
				</p>
			</fieldset>
			<div class="confirm">
				<input type="submit" name="mp_previs" value="<?php echo lang('mp_previs'); ?>" />
				<input type="submit" name="mp_send" value="<?php echo lang('mp_send'); ?>" />
			</div>
		</div>
	</div>
<?php elseif ($mp_contents == "reading"): ?>
	<div class="table" id="table_mps_reading">
	<table>
		<caption>
			<?php echo $title_pre; ?><?php echo lang('mp_title'); ?> - <?php if ($mp_typebox == "inbox"): ?><?php echo lang('mp_menu_recieved'); ?><?php else: ?><?php echo lang('mp_menu_sent'); ?><?php endif; ?> - <?php echo $mp_r_subject; ?>
		</caption>

		<tfoot>
			<tr>
				<th></th>
				<th></th>
			</tr>
		</tfoot>

		<tr class="field1">
			<th class="messageuser">
				<?php echo $mp_r_userlink; ?>
			</th>
			<th class="messageheader">
				<div class="messheaddate">
					<?php echo $mp_r_date; ?>
				</div>
				<ul class="messheadoptions">
					<li><a href="<?php echo manage_url('index.php?act=mp&amp;sub=4&amp;delete='.$mp_r_id, 'forum-mp-read.html?delete='.$mp_r_id); ?>" class="mp_delete"><span><?php echo lang('delete'); ?></span></a></li>
					<?php if ($mp_typebox == "inbox"): ?><li><a href="<?php echo manage_url('index.php?act=mp&amp;sub=3&amp;reply='.$mp_r_id, 'forum-mp-write.html?reply='.$mp_r_id); ?>" class="mp_reply"><span><?php echo lang('mp_reply'); ?></span></a></li><?php endif; ?>

				</ul>
			</th>
		</tr>
		<tr class="field1">
			<td class="messageuser2">
				<p class="avatar">
					<?php if (!empty($mp_r_avatar)): ?><?php echo $mp_r_avatar; ?><?php endif; ?>
				</p>
				<p class="connected">
					<?php echo $mp_r_fromcon; ?>
				</p>
			</td>
			<td class="message">
				<?php if ($mp_typebox == "outbox"): ?>
				<div class="messageedit">
					<?php echo lang('mp_to_sent'); ?> : <?php echo $mp_r_tolink; ?><br />
					_________________________________
				</div>
				<br />
				<?php endif; ?>
				<div class="messagecontent">
					<?php echo $mp_r_message; ?>
				</div>
				<?php if (!empty($mp_r_read)): ?>
				<div class="messageedit">
					_________________________________<br />
					<?php if ($mp_r_read): ?>
					<?php echo lang('mp_alreadyread'); ?>
					<?php else: ?>
					<?php echo lang('mp_notread'); ?>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	</div>
<?php elseif ($mp_contents == "error"): ?>
	<div class="container">
		<h2><?php echo $title_pre; ?><?php echo lang('error'); ?></h2>
		<div class="subcontainer">
			<p class="inforow">
				<?php echo lang(array('item' => $mp_error)); ?>
			</p>
		</div>
	</div>
<?php endif; ?>
</form>
