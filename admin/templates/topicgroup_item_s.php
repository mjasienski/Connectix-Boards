		
		<?php
		$indentline = '';
		for ($i = 0; $i<$indent; $i++) $indentline .= '--';
		?>
		
		<tr class="str_<?php if (isset($pa_structure_ff[$tgid])): ?>tg<?php else: ?>stg<?php endif; ?>">
			<td class="str_tgop2">
				<span class="indent"><?php echo '&nbsp;&nbsp;'.str_replace('-','&nbsp;',$indentline); ?></span>
				<?php if (!empty($pa_tg_updown[$tgid]['up'])): ?><a href="<?php echo manage_url('admin.php', 'forum-admin.html').'?act=str&amp;sub=1&amp;'.$pa_tg_updown[$tgid]['up']; ?>" class="up" title="<?php echo lang('pa_str_up'); ?>"><span><?php echo lang('pa_str_up'); ?></span></a><?php endif; ?>
				<?php if (!empty($pa_tg_updown[$tgid]['down'])): ?><a href="<?php echo manage_url('admin.php', 'forum-admin.html').'?act=str&amp;sub=1&amp;'.$pa_tg_updown[$tgid]['down']; ?>" class="down" title="<?php echo lang('pa_str_down'); ?>"><span><?php echo lang('pa_str_down'); ?></span></a><?php endif; ?>
			</td>
			<td class="str_tgname">
				<?php echo $indentline; ?>&nbsp;<?php echo $pa_tgnames[$tgid]; ?>
			</td>
			<td class="str_tgop1">
				<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=str&amp;sub=4&amp;deletetg=<?php echo $tgid; ?>"><?php echo lang('pa_str_delete'); ?></a> - <a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=str&amp;sub=3&amp;edittg=<?php echo $tgid; ?>"><?php echo lang('pa_str_edit'); ?></a>
			</td>
		</tr>
		<?php 
		$indent++;
		if (isset($pa_structure_tg[$tgid])):
			foreach ($pa_structure_tg[$tgid] as $tgid):
				include ($template_path.'topicgroup_item_s.php');
			endforeach;
		endif;
		$indent--;
		?>
		
