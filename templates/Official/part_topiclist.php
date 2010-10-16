
<div class="select_tlist">
	<form action="" method="get" id="tlist_form">
		<fieldset>
			<legend><span onclick="hideAndShow('tlist_form'); hideAndShow('tlist_selectcrits');"><?php echo lang('tl_m_title'); ?></span></legend>
			<p id="tlist_crits">
				<?php if (manage_url(true,false)): ?><input type="hidden" name="act" value="tlist" /><?php endif; ?>
				<label><input type="checkbox" name="unread" id="tl_unread" value="1" <?php echo $tl_unread_chk; ?> /> <?php echo lang('tl_m_unread'); ?></label>
				<label><input type="checkbox" name="posted" id="tl_posted" value="1" <?php echo $tl_posted_chk; ?> /> <?php echo lang('tl_m_posted'); ?></label>
				<label><input type="checkbox" name="noreply" id="tl_noreply" value="1" <?php echo $tl_noreply_chk; ?> /> <?php echo lang('tl_m_noreply'); ?></label>
				<label><input type="checkbox" name="bookmark" id="tl_bookmark" value="1" <?php echo $tl_bookmark_chk; ?> /> <?php echo lang('tl_m_bookmark'); ?></label>
				<label><input type="checkbox" name="tracked" id="tl_tracked" value="1" <?php echo $tl_tracked_chk; ?> /> <?php echo lang('tl_m_tracked'); ?></label>
				<label><input type="checkbox" name="poll" id="tl_poll" value="1" <?php echo $tl_poll_chk; ?> /> <?php echo lang('tl_m_poll'); ?></label>
			</p>
			<p id="tlist_confirm">
				<input type="submit" value="<?php echo lang('confirm'); ?>" />
			</p>
		</fieldset>
	</form>
</div>

<div class="bigmenu">
	<div class="pagemenu">
		<?php if (!empty($tg_pagemenu)): ?><?php echo lang('pages').' '.$tg_pagemenu; ?><?php endif; ?><br />
		<span id="tlist_selectcrits" onclick="hideAndShow('tlist_form'); hideAndShow('tlist_selectcrits');" style="display:none;"><?php echo lang('tl_m_title'); ?></span>
	</div>
</div>
<script type="text/javascript">hideAndShow('tlist_form'); hideAndShow('tlist_selectcrits');</script>

<?php if ( count($tg_groups)>0 ): ?>

<div class="table" id="table_topiclist">
<table>
	<caption>
		<?php echo $title_pre; ?>
		<a href="<?php echo $tl_url; ?>">
			<?php echo $tl_page_title; ?>
		</a>
	</caption>

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

	<?php $topic_list = &$tg_groups; require($template_path.'topic_list.php'); ?>
	
</table>
</div>

<?php if (!empty($tg_pagemenu)): ?>
<div class="bigmenu">
	<div class="pagemenu">
		<?php echo lang('pages').' '.$tg_pagemenu; ?>
	</div>
</div>
<?php endif; ?>

<?php endif; ?>
