<?php if (!defined('CB_TEMPLATE')) exit('Incorrect access attempt !!'); ?>

<select id="<?php echo $list['name']; ?>" name="<?php echo $list['name']; ?>"<?php if ( !empty($list['style']) ): ?> style="width:<?php echo $list['style']; ?>px;"<?php endif; ?><?php if ( !empty($list['class']) ): ?> class="<?php echo $list['class']; ?>"<?php endif; ?> onchange="javascript:fast_list('<?php echo $list['name']; ?>');">
<?php $opt=false;
$curoptgroup="";
foreach ($list['items'] as $elem): ?>
	<?php if ( isset($elem['optgroup']) && $elem['optgroup'] != $curoptgroup ): ?>
		<?php if ( $opt ): ?>
	</optgroup>
		<?php endif; ?>
	<optgroup label="<?php echo $elem['optgroup']; ?>"><?php $curoptgroup=$elem['optgroup']; $opt=true; ?>
	<?php endif; ?>
	<?php if ( $opt ): ?>	<?php endif; ?><option value="<?php echo $elem['name']; ?>"<?php if ( $elem['selected'] ): ?> selected="selected"<?php endif; ?><?php if ( isset($elem['disabled']) && $elem['disabled'] ): ?> disabled="disabled"<?php endif; ?>><?php if ( !empty($elem['lang']) ): ?> <?php echo lang(array('item' => $elem['lang'])); ?><?php else: ?><?php echo $elem['value']; ?><?php endif; ?></option>
<?php endforeach; ?>
<?php if ( $opt ): ?>
	</optgroup>
<?php endif; ?>
</select>
