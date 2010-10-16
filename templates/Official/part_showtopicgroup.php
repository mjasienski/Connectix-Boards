
<?php if ( $tg_modexist ): ?>
<div class="modtitle">
	<?php echo lang('moderators'); ?> :: <?php echo $tg_moderatorsnames; ?>
</div>
<?php endif; ?>

<?php if ( !empty($f_forums) ): include ($template_path.'topicgroup_list.php'); endif; ?>

<?php if ( !empty($tg_pagemenu) || !empty($tg_optionbuttons) ): ?>
<div class="bigmenu">
	<div class="pagemenu">
		<?php if ( !empty($tg_pagemenu) ):  echo lang('pages').' '.$tg_pagemenu;  endif; ?>
		<?php if ( $g_islogged ):  if ( !empty($tg_pagemenu) ): ?><br /><?php endif; ?><a href="<?php echo manage_url('index.php?showtopicgroup='.$tg_id.'&amp;markread=1', 'forum-tg'.$tg_id.'-mr.html'); ?>" class="bb_markread"><span><?php echo lang('bb_markread'); ?></span></a><?php endif; ?>
	</div>
	<?php if ( $tg_optionbuttons ): ?>
	<ul class="optionmenu">
		<li><a href="<?php echo manage_url('index.php?act=wm&amp;newtopic='.$tg_id, 'forum-wtopic-tg'.$tg_id.'.html'); ?>" class="bb_tg_topic"><span><?php echo lang('bb_tg_topic'); ?></span></a></li>
		<li><a href="<?php echo manage_url('index.php?act=wm&amp;newpoll='.$tg_id, 'forum-wpoll-tg'.$tg_id.'.html'); ?>" class="bb_tg_poll"><span><?php echo lang('bb_tg_poll'); ?></span></a></li>
		<?php if ($tg_moderation): ?>
		<li><a href="javascript:void(0);" onclick="hideAndShowC('modmenu'); hideAndShowC('topicmod');" class="bb_t_mod"><span><?php echo lang('bb_tg_mod'); ?></span></a></li>
		<?php endif; ?>
	</ul>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php if ($tg_moderation): ?>
<form action="" method="post" id="mass_mod">
<div class="modmenu">
	<div class="moditem">
		<input type="button" value="<?php echo lang('tg_invertselection'); ?>" onClick='invertselection("tgmod[]");' /> -- 
		<?php echo lang('tg_forselection'); ?> : 
		<input type="submit" name="mod_close" id="tg_close" value="<?php echo lang('tg_closetopic'); ?>" /> - 
		<input type="submit" name="mod_open" id="tg_open" value="<?php echo lang('tg_opentopic'); ?>" /> - 
		<input type="submit" name="mod_pin" id="tg_pin" value="<?php echo lang('tg_pintopic'); ?>" /> - 
		<input type="submit" name="mod_unpin" id="tg_unpin" value="<?php echo lang('tg_unpintopic'); ?>" /> - 
		<?php echo $tg_displacemenu; ?><input type="submit" name="mod_displace" id="tg_displace" value="<?php echo lang('confirm'); ?>" />
		<?php if ($tg_candelete): ?> &nbsp;-&nbsp; <input type="submit" name="mod_delete" id="tg_delete" value="<?php echo lang('tg_deletetopic'); ?>" /><?php endif; ?>
	</div>
</div>
<script type="text/javascript">hideAndShow('tg_displace');</script>
<?php endif; ?>

<?php if (count($tg_groups)>0): ?>
<div class="table" id="table_topicgroup">
<table>
	<caption>
		<?php echo $rss_tag; ?>
		<?php echo $title_pre; ?>
		<a href="<?php echo manage_url('index.php?showtopicgroup='.$tg_id, 'forum-tg'.$tg_id.','.rewrite_words($tg_name).'.html'); ?>">
			<?php echo $tg_name; ?>
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

<?php foreach ($tg_groups as $groups): ?>
	<tr>
		<th class="topicicon">
		</th>
		<th class="statusicon">
		</th>
		<?php if ($tg_moderation): ?>
		<th class="topicmod">
		</th>
		<?php endif; ?>
		<th class="topicinfo">
			<?php if ($groups['tg_tt_type'] == 2): echo lang('t_announce'); elseif ($groups['tg_tt_type'] == 1):  echo lang('t_pinned'); else:  echo lang('t_title');  endif; ?>
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

	<?php $topic_list = &$groups['tg_topics']; require($template_path.'topic_list.php'); ?>
	
<?php endforeach; ?>
</table>
</div>

<?php if ($tg_moderation): ?>
</form>
<?php endif; ?>

<?php if ( !empty($tg_pagemenu) || !empty($tg_optionbuttons) ): ?>
<div class="bigmenu">
	<div class="pagemenu">
		<?php if ( !empty($tg_pagemenu) ):  echo lang('pages').' '.$tg_pagemenu;  endif; ?>
		<?php if ( $g_islogged ):  if ( !empty($tg_pagemenu) ): ?><br /><?php endif; ?><a href="<?php echo manage_url('index.php?showtopicgroup='.$tg_id.'&amp;markread=1', 'forum-tg'.$tg_id.'-mr.html'); ?>" class="bb_markread"><span><?php echo lang('bb_markread'); ?></span></a><?php endif; ?>
	</div>
	<?php if ( $tg_optionbuttons ): ?>
	<ul class="optionmenu">
		<li><a href="<?php echo manage_url('index.php?act=wm&amp;newtopic='.$tg_id, 'forum-wtopic-tg'.$tg_id.'.html'); ?>" class="bb_tg_topic"><span><?php echo lang('bb_tg_topic'); ?></span></a></li>
		<li><a href="<?php echo manage_url('index.php?act=wm&amp;newpoll='.$tg_id, 'forum-wpoll-tg'.$tg_id.'.html'); ?>" class="bb_tg_poll"><span><?php echo lang('bb_tg_poll'); ?></span></a></li>
	</ul>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>
<script type="text/javascript">hideAndShowC('modmenu'); hideAndShowC('topicmod');</script>
