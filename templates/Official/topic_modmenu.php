<div class="moditem">
	<?php echo lang('mod_infos'); ?>
</div>
<?php if ( $mm_type == 0 ): ?>
<div class="moditem">
	<label><input type="checkbox" name="select_setpinned" /> <?php echo lang('mod_setpinned'); ?></label>
</div>
<?php endif; ?>
<?php if ( $mm_type == 1 ): ?>
<div class="moditem">
	<label><input type="checkbox" name="select_unsetpinned" /> <?php echo lang('mod_unsetpinned'); ?></label>
</div>
<?php endif; ?>
<?php if ( $mm_type != 2 && $mm_status == 0 ): ?>
<div class="moditem">
	<label><input type="checkbox" name="select_closetopic" /> <?php echo lang('mod_closetopic'); ?></label>
</div>
<?php endif; ?>
<?php if ( $mm_type != 2 && $mm_status == 1 ): ?>
<div class="moditem">
	<label><input type="checkbox" name="select_opentopic" /> <?php echo lang('mod_opentopic'); ?></label>
</div>
<?php endif; ?>
<?php if ( $mm_type == 2 ): ?>
<div class="moditem">
	<label><input type="checkbox" name="select_removeannounce" /> <?php echo lang('mod_removeannounce'); ?></label>
</div>
<?php endif; ?>
<div class="moditem">
	<label><input type="checkbox" name="select_changetitle" id="mm_changetitle" /> <?php echo lang('mod_changetitle'); ?> : </label>
	<input type="text" name="newtitle" value="<?php echo $mm_topicname; ?>" onchange="document.getElementById('mm_changetitle').checked=true;" />, 
	<input type="text" name="newcomment" value="<?php echo $mm_topiccomment; ?>" onchange="document.getElementById('mm_changetitle').checked=true;" />
</div>
<div class="moditem">
	<label><input type="checkbox" name="select_displacetopic" id="mm_displacetopic" /> <?php echo lang('mod_displacetopic'); ?></label> : 
	<?php echo $mm_topicgroupmenu; ?> 
	<script type="text/javascript">document.getElementById('newtg').onchange = function() { document.getElementById('mm_displacetopic').checked=true; }</script>
	( <label><input type="checkbox" name="leavetrace" onchange="document.getElementById('mm_displacetopic').checked=true;" /> <?php echo lang('mod_displacetopic_leavetrace'); ?> </label> )
</div>
<?php if ( $mm_type != 2 && !empty($mm_automess) ): ?>
<div class="moditem">
	<label><input type="checkbox" name="select_automessage" id="mm_automessage" /> <?php echo lang('mod_automess'); ?></label> 
	(<a href="<?php echo manage_url('index.php?act=mods&amp;page=4', 'forum-moderators.html?page=4'); ?>">...</a>) : 
	<?php echo $mm_automess; ?>
	<script type="text/javascript">document.getElementById('am_id').onchange = function() { document.getElementById('mm_automessage').checked=true; }</script>
</div>
<?php endif; ?>
<div class="moditem">
	<input type="submit" name="mod_submit" value="<?php echo lang('confirm'); ?>" />
	<?php if ( $mm_delete_ok ): ?>
	&nbsp;&nbsp; / &nbsp; <a href="<?php echo manage_url('index.php?showtopic='.$mm_topicid.'&amp;deletetopic=1', 'forum-t'.$mm_topicid.','.rewrite_words($mm_topicname).'.html?deletetopic=1'); ?>" class="deletetopic"><?php echo lang('mod_deletethistopic'); ?></a>
	<?php endif; ?>
</div>

