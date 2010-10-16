<?php if (!defined('CB_TEMPLATE')) exit('Incorrect access attempt !!'); ?>

<div class="table" id="table_connusers">
<table>
	<caption><?php echo $title_pre; ?><?php echo lang('connusers'); ?></caption>

	<thead>
		<tr>
			<th class="ppl_name">
				<?php echo lang('cu_ppl_name'); ?>
			</th>
			<th class="ppl_location">
				<?php echo lang('cu_ppl_location'); ?>
			</th>
			<th class="ppl_lastclick">
				<?php echo lang('cu_ppl_lastclick'); ?>
			</th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<th></th>
			<th></th>
			<th></th>
		</tr>
	</tfoot>

	<tbody>
		<?php foreach ($cu_ppl as $ppl): ?>
		<tr class="field<?php echo manage_cycle ('1,2'); ?>">
			<td class="ppl_name">
				<?php if ( !empty($ppl['ppl_link']) ): ?><?php echo $ppl['ppl_link']; else: ?><span class="i"><?php echo lang('cu_guest'); ?></span><?php endif; ?>
			</td>
			<td class="ppl_location">
				<?php echo lang(array('item' => $ppl['ppl_location'], 'f' => $ppl['ppl_f'], 'tg' => $ppl['ppl_tg'])); ?>
			</td>
			<td class="ppl_lastclick">
				<?php echo $ppl['ppl_lastclick']; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</div>
