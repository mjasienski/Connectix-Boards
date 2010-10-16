
			<?php $form_id = 'form_'.genValidCode(); ?>

			<span id="<?php echo $form_id; ?>" class="bbcodeandsmilies" style="display:none;">
				<span class="bbcode_input">
					<input type="button" id="b" value="b" onclick="tag('b', '<?php echo $ta_opt['id']; ?>');" style="font-weight:bold;" />
					<input type="button" id="i" value="i" onclick="tag('i', '<?php echo $ta_opt['id']; ?>');" style="font-style:italic;" />
					<input type="button" id="u" value="u" onclick="tag('u', '<?php echo $ta_opt['id']; ?>');" style="text-decoration:underline;" />
					<input type="button" id="quote" value="quote" onclick="tag('quote', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" id="code" value="code" onclick="tag('code', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" id="img" value="img" onclick='tag_image("<?php echo $ta_opt['id']; ?>");' />
					<input type="button" name="url" value="http://" onclick='tag_url("<?php echo $ta_opt['id']; ?>");' />
					<input type="button" name="email" value="email" onclick='tag_email("<?php echo $ta_opt['id']; ?>");' />
					<input type="button" id="s" value="s" onclick="tag('s', '<?php echo $ta_opt['id']; ?>');" style="text-decoration:line-through;" />
					<input type="button" id="spoil" value="spoil" onclick="tag('spoil', '<?php echo $ta_opt['id']; ?>');" />
					<input type="button" id="center" value="center" onclick="tag('center', '<?php echo $ta_opt['id']; ?>');" />
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
					<select name="police" class="fontchooser" onchange="tag_select(this.form.police,'<?php echo $ta_opt['id']; ?>','font');">
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
				</span>
				<span class="smilies">
					<?php echo getSmileyMenu($ta_opt['id'],17); ?>
				</span>
			</span>

			<script type="text/javascript">
				hideAndShow("<?php echo $form_id; ?>");
				var form_url = "<?php echo lang('form_inserturl'); ?>";
				var form_mail = "<?php echo lang('form_insertmail'); ?>";
				var form_img = "<?php echo lang('form_insertimg'); ?>";
			</script>

			<textarea <?php foreach ($ta_opt as $k => $v): echo ($k != 'value')?' '.$k.'="'.$v.'"':''; endforeach; ?> class="wmessage"><?php echo (isset($ta_opt['value']))?$ta_opt['value']:''; ?></textarea>
