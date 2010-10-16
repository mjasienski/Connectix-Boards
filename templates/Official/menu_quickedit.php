<?php if (!defined('CB_TEMPLATE')) exit('Incorrect access attempt !!'); ?>

	<form id="form_<?php echo $msg_id ?>" action="<?php echo manage_url('index.php?act=wm&amp;editmessage='.$msg_id.'&amp;intopic='.$topic_id,'forum-editmsg-m'.$msg_id.'-t'.$topic_id.'.html'); ?>" method="post" onsubmit="return false;">
		<div class="form_fields">
			<?php $ta_opt = array('name' => 'message_'.$msg_id, 'id' =>  'message_'.$msg_id, 'nomenu' => true,'value' => $msg_contents) ?>
			<?php include($template_path.'menu_writemsg.php'); ?>
			<input type="hidden" name="prev" value="ok" />
		</div>
		<div class="form_buttons">
			<input type="button" value="<?php echo lang('form_confirm'); ?>" onclick="quickEdit_sendform('<?php echo $msg_id ?>');" />
			<input type="submit" value="<?php echo lang('form_previs'); ?>" onclick="quickEdit_previs('<?php echo $msg_id ?>');" />
			<input type="button" value="<?php echo lang('form_cancel'); ?>" onclick="quickEdit_cancelform('<?php echo $msg_id ?>');" />
		</div>
	</form>