		<div class="centerformtext">
			<?php echo lang('pa_gallery_add_filename'); ?>
		</div>
		<div class="centerforminput">
			<input type="file" name="imagefile" class="input_file" size="40" />
		</div>
		<div class="centerformtext">
			<input type="submit" name="a_send" value="<?php echo lang('confirm'); ?>" />
		</div>
		<?php foreach ($a_avatars as $avatar): ?>
		<div class="avatar">
			<img src="avatars/gallery/<?php echo $avatar; ?>" alt="Avatar" /><br />
			<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>?act=gal&amp;delete=<?php echo $avatar; ?>"><?php echo lang('pa_gallery_all_delete'); ?></a>
		</div>
		<?php endforeach; ?>
