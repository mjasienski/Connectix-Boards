
	<?php foreach ($topic_list as $topic): ?>
	<tr id="topic_<?php echo $topic['tg_t_id']; ?>" class="field<?php echo manage_cycle('1,2'); ?> navitem">
		<td class="topicicon">
			<span class="<?php echo $topic['tg_t_topicicon']; ?>" title="<?php echo lang($topic['tg_t_topicicon']); ?>"><span><?php echo lang(array('item' => $topic['tg_t_topicicon'])); ?></span></span>
		</td>
		<td class="statusicon">
			<?php if ( !empty($topic['tg_t_statusicon']) ): ?>
				<span class="<?php echo $topic['tg_t_statusicon']; ?>" title="<?php echo lang(array('item' => $topic['tg_t_statusicon'])); ?>"><span><?php echo lang(array('item' => $topic['tg_t_statusicon'])); ?></span></span><?php endif; ?>
		</td>
		<?php if ($tg_moderation): ?>
		<td class="topicmod">
			<?php if ( $topic['tg_t_status'] != 2 ): ?>
			<input type="checkbox" name="tgmod[]" value="<?php echo $topic['tg_t_id']; ?>" />
			<?php endif; ?>
		</td>
		<?php endif; ?>
		<td class="topicinfo">
			<?php echo $topic['tg_t_quickicon']; ?>
			<?php if ( $topic['tg_t_status'] == 2 ):  echo lang('t_displaced'); ?> : <?php elseif ($topic['tg_t_type'] == 1):  echo lang('pinned'); ?> : <?php elseif ($topic['tg_t_type'] == 2):  echo lang('announcement'); ?> : <?php endif; ?>
			<?php $tid = ($topic['tg_t_status'] == 2)?$topic['tg_t_displaced']:$topic['tg_t_id']; ?>
			<?php echo $topic['tg_t_path']; ?><span class="row_title"><a href="<?php echo manage_url('index.php?showtopic='.$tid.((!empty($highlight))?'&amp;hl='.$highlight:''), 'forum-t'.$tid.'-p1,'.rewrite_words($topic['tg_t_name']).'.html'.((!empty($highlight))?'?hl='.$highlight:'')); ?>" id="mainlink_topic_<?php echo $topic['tg_t_id']; ?>"><?php echo $topic['tg_t_name']; ?></a></span> <?php echo $topic['tg_t_topicpages']; ?>
			<?php if ( !empty($topic['tg_t_topiccomment']) ): ?>
				<br /><span class="row_comment"><?php echo $topic['tg_t_topiccomment']; ?></span>
			<?php endif; ?>
		</td>
		<td class="topicstarter">
			<?php echo $topic['tg_t_topicstarterconnected']; ?> <?php echo $topic['tg_t_topicstarter']; ?>
		</td>
		<td class="topicposts">
			<?php echo $topic['tg_t_nbreply']; ?>
		</td>
		<td class="topicviews">
			<?php echo $topic['tg_t_views']; ?>
		</td>
		<td class="topiclastmessage">
			<?php if ( $topic['tg_t_status'] == 2 ): ?>---<?php else: ?>
			<a href="<?php echo $topic['tg_t_lastreply_url']; ?>" title="<?php echo lang('t_lastmessage_title'); ?>"><?php echo $topic['tg_t_lastreply_date']; ?></a><br />
			<?php echo lang('by'); ?> <?php echo $topic['tg_t_lastreply_userlink']; ?>
			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
