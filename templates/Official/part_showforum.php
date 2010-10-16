
<?php if ( !empty($f_forums) ): include ($template_path.'topicgroup_list.php'); endif; ?>

<?php if ( $g_islogged): ?>
<div class="bigmenu">
	<div class="pagemenu">
		<?php if ( $g_islogged ): ?>
		<a href="<?php echo $f_markread_link; ?>" class="bb_markread"><span><?php echo lang('bb_markread'); ?></span></a>
		<?php endif; ?>
	</div>
	<div class="optionmenu">
	</div>
</div>
<?php endif; ?>
