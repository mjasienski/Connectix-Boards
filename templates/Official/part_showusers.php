
<div class="bigmenu">
	<div class="pagemenu">
		<?php if (!empty($lu_pagemenu)): ?><?php echo lang('pages'); ?> <?php echo $lu_pagemenu; ?><?php endif; ?>
	</div>
	<div class="optionmenu">
	</div>
</div>

<form action="<?php manage_url('index.php','forum-members.html'); ?>" method="get" id="form_showusers">
	<div class="container">
		<h2><?php echo $title_pre; ?><?php echo lang('users_search_title'); ?></h2>
		<div class="subcontainer">
			<fieldset>
				<legend><?php echo lang('users_search_crits'); ?></legend>
				<p class="sulabel">
					<?php echo manage_url('<input type="hidden" name="act" value="members" />',''); ?>
					<label><strong><?php echo lang('username'); ?></strong><br />
					<input type="text" name="su_name" size="15" value="<?php echo $su_name; ?>" /></label>
				</p>
				<p class="sulabel">
					<label><strong><?php echo lang('class'); ?></strong><br />
					<?php $list = $class_list; ?><?php include ($template_path.'menu_list.php'); ?>
					</label>
				</p>
				<p class="sulabel">
					<label><strong><?php echo lang('users_sort'); ?></strong><br/>
					<?php $list = $sort_list; ?><?php include ($template_path.'menu_list.php'); ?>
					</label>
				</p>
				<p class="sulabel">
					<label><strong><?php echo lang('users_order'); ?></strong><br/>
					<?php $list = $order_list; ?><?php include ($template_path.'menu_list.php'); ?>
					</label>
				</p>
			</fieldset>
			<div class="confirm">
				<input type="submit" value="<?php echo lang('confirm'); ?>" />
			</div>
		</div>
	</div>
</form>
<br />
<?php if (count($lu_users)>0): ?>
<div class="table" id="table_showusers">
<table>
	<caption><?php echo $title_pre; ?><?php echo lang('users_title'); ?></caption>

	<thead>
		<tr>
			<th class="usersconnected">
			</th>
			<th class="usersname">
				<?php echo lang('username'); ?> (id)
			</th>
			<th class="usersposts">
				<?php echo lang('posts'); ?>
			</th>
			<th class="usersclass">
				<?php echo lang('class'); ?>
			</th>
			<th class="usersreg">
				<?php echo lang('users_reg'); ?>
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
		</tr>
	</tfoot>

	<tbody>
		<?php foreach ($lu_users as $user): ?>
		<tr class="field<?php echo manage_cycle('1,2'); ?>">
			<td class="usersconnected">
				<?php echo $user['lu_u_connected']; ?>
			</td>
			<td class="usersname">
				<?php echo $user['lu_u_userlink']; ?>
			</td>
			<td class="usersposts">
				<?php echo $user['lu_u_nbmess']; ?>
			</td>
			<td class="usersclass">
				<?php echo $user['lu_u_class']; ?>
			</td>
			<td class="usersreg">
				<?php echo $user['lu_u_reg']; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</div>
<?php endif; ?>

<div class="bigmenu">
	<div class="pagemenu">
		<?php if (!empty($lu_pagemenu)): ?><?php echo lang('pages'); ?> <?php echo $lu_pagemenu; ?><?php endif; ?>
	</div>
	<div class="optionmenu">
	</div>
</div>
