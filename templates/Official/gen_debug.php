<?php if (!defined('CB_TEMPLATE')) exit('Incorrect access attempt !!'); ?>

<div class="table" id="table_debug">
<table>
	<caption><?php echo $title_pre; ?><?php echo lang('debug_title'); ?></caption>

	<thead>
		<tr>
			<th class="debug_time">
				<?php echo lang('debug_time_title'); ?>
			</th>
			<th class="debug_query">
				<?php echo lang('debug_query_title'); ?>
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
		<?php foreach ($g_debug_queries as $query): ?>
		<tr class="field<?php echo manage_cycle (array('values' => '1,2')); ?>">
			<td class="debug_time">
				<?php echo $query['time']; ?>
			</td>
			<td class="debug_query">
				<pre><?php echo $query['query']; ?></pre>
			</td>
		</tr>
		<?php endforeach; ?>
		<tr class="field<?php echo manage_cycle ('1,2'); ?>">
			<td class="debug_time">
				<?php echo lang('debug_numberqueries'); ?> : <?php echo $g_debug_numberqueries; ?>
			</td>
			<td class="debug_query">
				<?php echo lang('debug_totaltime'); ?> : <?php echo $g_debug_totalquerytime; ?> <br />
		Page execution time : <?php echo $g_execution; ?> sec
			</td>
		</tr>
	</tbody>
</table>
</div>
