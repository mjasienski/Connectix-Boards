
<?php if ($s_displayresults): ?>
<?php if (!empty($s_pagemenu)): ?>
<div class="bigmenu">
	<div class="pagemenu">
		<?php echo lang('pages'); ?> <?php echo $s_pagemenu; ?>
	</div>
	<div class="optionmenu">
	</div>
</div>
<?php endif; ?>
<div class="table" id="table_search">
	<table>
		<caption><?php echo $title_pre;  echo lang('src_results'); ?> ( <?php echo lang(array('item' => "src_nbresults", 'n' => $s_nbresults)); ?> )</caption>

		<?php if ($s_displaytopics) : ?>
		<thead>
			<tr>
				<th class="topicicon">
				</th>
				<th class="statusicon">
				</th>
				<th class="topicinfo">
					<?php echo lang('t_title'); ?>
				</th>
				<th class="topicstarter">
					<?php echo lang('t_starter'); ?>
				</th>
				<th class="topicposts">
					<?php echo lang('t_replies'); ?>
				</th>
				<th class="topicviews">
					<?php echo lang('t_views'); ?>
				</th>
				<th class="topiclastmessage">
					<?php echo lang('t_lastmessage'); ?>
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
				<th></th>
			</tr>
		</tfoot>

		<?php $topic_list = &$s_results; require($template_path.'topic_list.php'); ?>
		
		<?php else: ?>
		<thead>
			<tr>
				<th class="srcuser">
					<?php echo lang('src_author'); ?>
				</th>
				<th class="srcinfo">
					<?php echo lang('src_infos'); ?>
				</th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th></th>
				<th></th>
			</tr>
		</tfoot>

		<tbody>
			<?php foreach ($s_results as $result): ?>
			<tr class="field<?php echo manage_cycle('1,2'); ?>">
				<td class="srcuser">
					<?php echo $result['s_r_userlink']; ?><br />
					<?php echo $result['s_r_date']; ?>
				</td>
				<td class="srcinfo">
					<?php echo $result['s_r_topic_path'].' '.CB_ADDR_SEP; ?> <a href="<?php echo manage_url('index.php?showtopic='.$result['s_r_topic_id'].'&amp;message='.$result['s_r_msg_id'], 'forum-t'.$result['s_r_topic_id'].'-m'.$result['s_r_msg_id'].'.html'); ?>"><?php echo $result['s_r_topic_name']; ?></a><hr />
					<?php echo $result['s_r_messcontents']; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		<?php endif; ?>
	</table>
</div>

<?php if (!empty($s_pagemenu)): ?>
<div class="bigmenu">
	<div class="pagemenu">
		<?php echo lang('pages'); ?> <?php echo $s_pagemenu; ?>
	</div>
	<div class="optionmenu">
	</div>
</div>

<?php endif; ?>
<?php endif; ?>

<?php if ($s_displayresults): ?>
<div id="src_form_link" style="display:none;">
	<p>
		<a href="#src_form" onmousedown="hideAndShow('src_form')"><?php echo lang('src_form'); ?></a>
	</p>
</div>
<script type="text/javascript">document.getElementById('src_form_link').style.display='block';</script>
<?php endif; ?>

<form action="<?php manage_url('index.php','forum-search.html'); ?>" method="get" id="src_form">
	<div class="container">
		<h2><?php echo $title_pre;  echo lang('src_entercrits'); ?></h2>
		<div class="subcontainer">
			<fieldset>
				<legend><?php echo lang('src_keyscrits'); ?></legend>
				<p>
					<?php echo manage_url('<input type="hidden" name="act" value="src" />',''); ?>
					<label><strong><?php echo lang('src_keys'); ?></strong> (<?php echo lang('src_keys_joker'); ?>)<br />
					<input type="text" name="keys" size="46" value="<?php echo $s_form_keywords; ?>" /></label> &nbsp;( <?php echo lang('src_notabene'); ?> )
				</p>
				<p>
					<label><strong><?php echo lang('src_author_form'); ?></strong> (<?php echo lang('src_author_form_exact'); ?>)<br />
					<input type="text" name="author" size="46" value="<?php echo $s_form_author; ?>" /></label> &nbsp;( <?php echo lang('src_notabene'); ?> )
				</p>
				<p>
					<strong><?php echo lang('src_position_form'); ?></strong><br />
					<label><?php echo lang('src_position_in'); ?> : &nbsp;
					<select name="torm">
						<option value="ttls" <?php if ($s_srctitles): ?>selected="selected" <?php endif; ?>><?php echo lang('src_titles'); ?></option>
						<option value="msgs" <?php if (!$s_srctitles): ?>selected="selected" <?php endif; ?>><?php echo lang('src_msgs'); ?></option>
					</select></label>
				</p>
				<?php if ($src_connected && false): // Problèmes dans cette partie actuellement. Sera fait dans une version ultérieure... ?>
				<p>
					<label><input type="checkbox" name="read" <?php echo $s_form_read; ?> /> &nbsp;<?php echo lang('src_read'); ?></label>
				</p>
				<?php endif; ?>
			</fieldset>
			<fieldset>
				<legend><?php echo lang('src_limitcrits'); ?></legend>
				<p>
					<label><strong><?php echo lang('src_where'); ?></strong><br />
					<?php echo $s_form_wheremenu; ?></label><br />
					<label><input type="checkbox" name="where_includesub" <?php echo $s_form_where_includesub; ?> /> &nbsp;<?php echo lang('src_where_includesub'); ?></label>
				</p>
				<p>
					<label><strong><?php echo lang('src_from'); ?></strong><br />
					<?php $list = $s_form_frommenu; ?>
					<?php include ($template_path.'menu_list.php'); ?></label>
				</p>
			</fieldset>
			<fieldset>
				<legend><?php echo lang('src_sortcrits'); ?></legend>
				<p>
					<label><strong><?php echo lang('src_sort'); ?></strong><br />
					<?php $list = $s_form_sortmenu; ?>
					<?php include ($template_path.'menu_list.php'); ?></label>
					<select name="sort_order">
						<option value="desc"<?php if ($sort_checked == "desc"): ?> selected="selected"<?php endif; ?>><?php echo lang('src_sort_desc'); ?></option>
						<option value="asc"<?php if ($sort_checked == "asc"): ?> selected="selected"<?php endif; ?>><?php echo lang('src_sort_asc'); ?></option>
					</select>
				</p>
				<p>
					<strong><?php echo lang('src_display_form'); ?></strong><br />
					<label><?php echo lang('src_display_by'); ?> : &nbsp;
					<select name="display">
						<option value="tpcs" <?php if ($s_displaytopics): ?>selected="selected" <?php endif; ?>><?php echo lang('src_display_topics'); ?></option>
						<option value="msgs" <?php if (!$s_displaytopics): ?>selected="selected" <?php endif; ?>><?php echo lang('src_display_msgs'); ?></option>
					</select></label>
				</p>
			</fieldset>
			<div class="confirm">
				<input type="submit" name="search" value="<?php echo lang('src_submit_confirm'); ?>" />
			</div>
		</div>
	</div>
</form>

<?php if ($s_displayresults): ?>
<script type="text/javascript">document.getElementById('src_form').style.display='none';</script>
<?php endif; ?>
