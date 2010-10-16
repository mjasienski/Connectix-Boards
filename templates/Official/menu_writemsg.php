<?php if (!defined('CB_TEMPLATE')) exit('Incorrect access attempt !!'); ?>

			<?php $form_id = 'form_'.genValidCode(); ?>

			<span class="showwritehelper">
				<a onclick="hideAndShow('<?php echo $form_id; ?>');"><?php echo lang('wm_hideorshowmenu'); ?></a>
			</span>

			<span id="<?php echo $form_id; ?>" class="bbcodeandsmilies" style="display:none;">
				<span class="bbcode_input">
					<input type="button" class="bbcode_bold" 		title="<?php echo lang('bbcode_bold'); ?> (Ctrl+Alt+b)" value="b" onclick="tag('b', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_italic" 		title="<?php echo lang('bbcode_italic'); ?> (Ctrl+Alt+i)" value="i" onclick="tag('i', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_underlined" 	title="<?php echo lang('bbcode_underlined'); ?> (Ctrl+Alt+u)" value="u" onclick="tag('u', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_striked" 	title="<?php echo lang('bbcode_striked'); ?> (Ctrl+Alt+s)" value="s" onclick="tag('s', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_quote" 		title="<?php echo lang('bbcode_quote'); ?> (Ctrl+Alt+q)" value="quote" onclick="tag('quote', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_code" 		title="<?php echo lang('bbcode_code'); ?> (Ctrl+Alt+d)" value="code" onclick="tag('code', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_spoil" 		title="<?php echo lang('bbcode_spoil'); ?> (Ctrl+Alt+p)" value="spoil" onclick="tag('spoil', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_img" 		title="<?php echo lang('bbcode_image'); ?> (Ctrl+Alt+g)" value="img" onclick="tag_image('<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_url" 		title="<?php echo lang('bbcode_url'); ?> (Ctrl+Alt+l)" name="url" value="http://" onclick="tag_url('<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_mail" 		title="<?php echo lang('bbcode_mail'); ?> (Ctrl+Alt+m)" name="email" value="email" onclick="tag_email('<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_left" 		title="<?php echo lang('bbcode_left'); ?> (Ctrl+Alt+f)" value="left" onclick="tag('left', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_center" 		title="<?php echo lang('bbcode_center'); ?> (Ctrl+Alt+c)" value="center" onclick="tag('center', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_right" 		title="<?php echo lang('bbcode_right'); ?> (Ctrl+Alt+r)" value="right" onclick="tag('right', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_justified" 	title="<?php echo lang('bbcode_justified'); ?> (Ctrl+Alt+j)" value="justified" onclick="tag('justified', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_youtube" 	title="<?php echo lang('bbcode_youtube'); ?>" value="youtube" onclick="tag('youtube', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" class="bbcode_dailymotion" title="<?php echo lang('bbcode_dailymotion'); ?>" value="dailymotion" onclick="tag('dailymotion', '<?php echo $ta_opt['id']; ?>');" />
				</span>
				<span class="bbcode_select">
					<select name="color" class="colorchooser" onchange="tag_select(this.form.color,'<?php echo $ta_opt['id']; ?>','color');">
						<option class="colortitle"	selected="selected" 	disabled="disabled"><?php echo lang('color'); ?></option>
						<option value="yellow" 	class="coloritem" 	style="background-color:yellow;clear:both;">yellow</option>
						<option value="olive" 	class="coloritem" 	style="background-color:olive;">olive</option>
						<option value="lime" 	class="coloritem" 	style="background-color:lime;">lime</option>
						<option value="green" 	class="coloritem" 	style="background-color:green;">green</option>
						<option value="orange"  class="coloritem" 	style="background-color:orange;clear:both;">orange</option>
						<option value="purple" 	class="coloritem" 	style="background-color:purple;">purple</option>
						<option value="red" 	class="coloritem" 	style="background-color:red;">red</option>
						<option value="maroon" 	class="coloritem" 	style="background-color:maroon;">maroon</option>
						<option value="aqua" 	class="coloritem" 	style="background-color:aqua;clear:both;">aqua</option>
						<option value="teal" 	class="coloritem" 	style="background-color:teal;">teal</option>
						<option value="blue" 	class="coloritem" 	style="background-color:blue;">blue</option>
						<option value="navy" 	class="coloritem" 	style="background-color:navy;">navy</option>
						<option value="white" 	class="coloritem" 	style="background-color:white;clear:both;">white</option>
						<option value="silver" 	class="coloritem" 	style="background-color:silver;">silver</option>
						<option value="gray" 	class="coloritem" 	style="background-color:gray;">gray</option>
						<option value="black" 	class="coloritem" 	style="background-color:black;">black</option>
					</select>
					<select name="taille" class="sizechooser" onchange="tag_select(this.form.taille,'<?php echo $ta_opt['id']; ?>','size');">
						<option class="sizetitle" selected="selected" disabled="disabled"><?php echo lang('size'); ?></option>
						<option value="6"  class="sizeitem" style="font-size:6;">6</option>
						<option value="8"  class="sizeitem" style="font-size:8;">8</option>
						<option value="9"  class="sizeitem" style="font-size:9;">9</option>
						<option value="10" class="sizeitem" style="font-size:10;">10</option>
						<option value="11" class="sizeitem" style="font-size:11;">11</option>
						<option value="12" class="sizeitem" style="font-size:12;">12</option>
						<option value="14" class="sizeitem" style="font-size:14;">14</option>
						<option value="16" class="sizeitem" style="font-size:16;">16</option>
						<option value="20" class="sizeitem" style="font-size:20;">20</option>
					</select>
					<select name="font" class="fontchooser" onchange="tag_select(this.form.police,'<?php echo $ta_opt['id']; ?>','font');">
						<option class="fonttitle" selected="selected" disabled="disabled"><?php echo lang('font'); ?></option>
						<option value="serif"  class="fontitem" style="font-family:serif;">serif</option>
						<option value="sans-serif"  class="fontitem" style="font-family:sans-serif;">sans-serif</option>
						<option value="cursive"  class="fontitem" style="font-family:cursive;">cursive</option>
						<option value="fantasy"  class="fontitem" style="font-family:fantasy;">fantasy</option>
						<option value="monospace"  class="fontitem" style="font-family:monospace;">monospace</option>
						<option value="times"  class="fontitem" style="font-family:times;">times</option>
						<option value="courier"  class="fontitem" style="font-family:courier;">courier</option>
						<option value="arial"  class="fontitem" style="font-family:arial;">arial</option>
					</select>
					<select name="list" class="listchooser" onchange="tag_list(this.form.list,'<?php echo $ta_opt['id']; ?>');">
						<option class="listtitle" selected="selected" disabled="disabled"><?php echo lang('list'); ?></option>
						<option value="1"  class="fontitem">1 <?php echo lang('element'); ?></option>
						<option value="2"  class="fontitem">2 <?php echo lang('elements'); ?></option>
						<option value="3"  class="fontitem">3 <?php echo lang('elements'); ?></option>
						<option value="4"  class="fontitem">4 <?php echo lang('elements'); ?></option>
						<option value="5"  class="fontitem">5 <?php echo lang('elements'); ?></option>
						<option value="6"  class="fontitem">6 <?php echo lang('elements'); ?></option>
						<option value="7"  class="fontitem">7 <?php echo lang('elements'); ?></option>
						<option value="8"  class="fontitem">8 <?php echo lang('elements'); ?></option>
					</select>
				</span>
				<span class="smilies">
					<?php echo getSmileyMenu($ta_opt['id']); ?>
				</span>
				<?php if (strlen(getSmileyMenu($ta_opt['id'],true)) > 0): ?>
				<span class="moresmilies">
					<a onclick="SmiliesExtendedForm('<?php echo $ta_opt['id']; ?>');"><?php echo lang('moresmilies'); ?></a>
				</span>
				<?php endif; ?>
			</span>
			
			<textarea <?php foreach ($ta_opt as $k => $v): echo ($k != 'value' && $k != 'nomenu')?' '.$k.'="'.$v.'"':''; endforeach; ?> class="wmessage"><?php echo (isset($ta_opt['value']))?$ta_opt['value']:''; ?></textarea>
			
			<script type="text/javascript">
				<?php if (!isset($ta_opt['nomenu'])): ?>hideAndShow("<?php echo $form_id; ?>");<?php endif; ?>
				document.getElementById("<?php echo $ta_opt['id']; ?>").onkeyup = manage_shortcuts;
			</script>
