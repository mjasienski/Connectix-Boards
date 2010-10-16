
<?php foreach ($f_forums as $forums): ?>
<div class="table">
<table class="table_tglist">
	<caption>
		<?php if ($f_home): ?><a href="javascript:void(0);" onclick="hideAndShowF('<?php echo $forums['id']; ?>');" title="<?php echo lang('f_hideorshow'); ?>"><?php endif; ?><?php echo $title_pre; ?><?php if ($f_home): ?></a><?php endif; ?>
		<?php if ($forums['id'] != 0): ?>
		<a href="<?php echo manage_url('index.php?showforum='.$forums['id'],'forum-f'.$forums['id'].','.rewrite_words($forums['name']).'.html'); ?>"><?php echo $forums['name']; ?></a>
		<?php else: ?>
		<?php echo lang('tg_subtg'); ?>
		<?php endif; ?>
	</caption>

	<thead id="forum<?php echo $forums['id']; ?>_th">
		<tr>
			<th class="topicgroupicon">
			</th>
			<th class="topicgroupinfo">
				<?php echo lang('tg_title'); ?>
			</th>
			<th class="topicgrouptopics">
				<?php echo lang('tg_topics'); ?>
			</th>
			<th class="topicgroupposts">
				<?php echo lang('tg_posts'); ?>
			</th>
			<th class="topicgrouplastmessage">
				<?php echo lang('tg_lastmessage'); ?>
			</th>
		</tr>
	</thead>

	<tfoot id="forum<?php echo $forums['id']; ?>_tf">
		<tr>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
		</tr>
	</tfoot>

	<tbody id="forum<?php echo $forums['id']; ?>_tb">
		<?php foreach ($forums['contents'] as $tg): ?>
		<tr id="tg_<?php echo $tg['tg_id']; ?>" class="field1 navitem">
			<?php if ( $tg['tg_islink'] ): ?>
			<td class="topicgroupicon">
				<a href="<?php echo $tg['tg_link']; ?>" title="<?php echo lang('st_tg_link'); ?>" class="st_tg_link">
					<span><?php echo lang('st_tg_link'); ?></span>
				</a>
			</td>
			<td class="topicgroupinfo">
				<span class="row_title"><a href="<?php echo $tg['tg_link']; ?>" id="mainlink_tg_<?php echo $tg['tg_id']; ?>"><?php echo $tg['tg_name']; ?></a></span>
				<?php if ( !$tg['tg_visible'] ): echo '['.lang('visibility_hidden').']'; endif; ?>
				<?php if ( !empty($tg['tg_comment']) ): ?>
				<br /><span class="row_comment"><?php echo $tg['tg_comment']; ?></span>
				<?php endif; ?>
			</td>
			<td class="topicgrouptopics">--</td>
			<td class="topicgroupposts">--</td>
			<td class="topicgrouplastmessage">--</td>
			<?php else: ?>
			<td class="topicgroupicon">
				<?php if ( $g_islogged && !$tg['tg_read'] ): ?>
				<a href="<?php echo manage_url('index.php?showtopicgroup='.$tg['tg_id'].'&amp;markread=1', 'forum-tg'.$tg['tg_id'].'-mr.html'); ?>" title="<?php echo lang('tg_markread_title'); ?>" class="st_tg_u"><span><?php echo lang('st_tg_u'); ?></span></a>
				<?php else: ?>
				<span class="st_tg_r" title="<?php echo lang('st_tg_r'); ?>"><span><?php echo lang('st_tg_r'); ?></span></span>
				<?php endif; ?>
			</td>
			<td class="topicgroupinfo">
				<span class="row_title">
					<a href="<?php echo manage_url('index.php?showtopicgroup='.$tg['tg_id'], 'forum-tg'.$tg['tg_id'].','.rewrite_words($tg['tg_name']).'.html'); ?>" id="mainlink_tg_<?php echo $tg['tg_id']; ?>"><?php echo $tg['tg_name']; ?></a>
				</span>
				<?php if ( !$tg['tg_visible'] ): echo '['.lang('visibility_hidden').']'; endif; ?>
				<?php if ( !empty($tg['tg_comment']) ): ?><br />
				<span class="row_comment"><?php echo $tg['tg_comment']; ?></span>
				<?php endif; ?>
				<?php if ( !empty($tg['tg_subtgs']) ): ?><br />
				<span class="row_subtgs">
					<strong><?php echo lang('tg_subtg'); ?></strong> : 
					<?php echo $tg['tg_subtgs']; ?>
				</span>
				<?php endif; ?>
			</td>
			<td class="topicgrouptopics">
				<?php echo $tg['tg_nbtopics']; ?>
			</td>
			<td class="topicgroupposts">
				<?php echo $tg['tg_nbmess']; ?>
			</td>
			<td class="topicgrouplastmessage">
				<?php if ( $tg['tg_lastm_tid'] > 0 ): ?>
				<?php echo $tg['tg_lastm_time']; ?><br />
				<?php echo lang('in'); ?> <a href="<?php echo manage_url('index.php?showtopic='.$tg['tg_lastm_tid'].'&amp;page='.$tg['tg_lastm_page'].'#'.$tg['tg_lastm_mid'], 'forum-t'.$tg['tg_lastm_tid'].'-p'.$tg['tg_lastm_page'].','.rewrite_words($tg['tg_lastm_tname']).'.html#'.$tg['tg_lastm_mid']); ?>" title="<?php echo lang('t_lastmessage_title'); ?>"><?php echo truncate($tg['tg_lastm_tname'],30); ?></a><br />
				<?php echo lang('by'); ?> <?php echo $tg['tg_lastm_ulink']; ?>
				<?php else: ?>
				<?php echo lang('tg_nomessposted'); ?>
				<?php endif; ?>
			</td>
			<?php endif; ?>
		</tr>
		
		<?php endforeach; ?>
	</tbody>
</table>
</div>
<?php if ($f_home): ?>
<script type="text/javascript">checkF('<?php echo $forums['id']; ?>');</script>
<?php endif; ?>
<?php endforeach; ?>
