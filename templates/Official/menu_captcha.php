<?php if (!defined('CB_TEMPLATE')) exit('Incorrect access attempt !!'); ?>

		<span id="captcha_box">
			<span id="captcha_code">
				<span id="captcha_img"><?php echo $captcha_code; ?></span> <br />
				<a onclick="newCaptcha();" id="captcha_reload" style="display:none;"><?php echo lang('captcha_reload'); ?></a>
				<script type="text/javascript">document.getElementById('captcha_reload').style.display = 'inline';</script>
			</span>
			<span id="captcha_form">
				<input type="text" id="captcha_input" name="captcha" size="10" maxlength="6" style="vertical-align:middle;" <?php if (isset($captcha_typed)) echo 'value="'.$captcha_typed.'"'; ?> /> <br /> 
				<?php echo lang('captcha_info'); ?>
			</span>
		</span>