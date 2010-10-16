<?php if (!defined('CB_TEMPLATE')) exit('Incorrect access attempt !!'); ?>

<div class="menu">
	<h3><?php echo lang(array('item' => $menu['title'])); ?></h3>
	<?php if ( count($menu['items']) > 0 ): ?>
	<ul>
	<?php foreach ($menu['items'] as $link): ?>
	<?php if ( $link['cid'] == $menu['currentpage'] ): ?>
		<li class="menuitem menuitem_selected"><span><?php echo lang(array('item' => $link['title'])); ?></span></li>
	<?php else: ?>
		<li class="menuitem"><a href="<?php echo str_replace('[num_page]',$link['id'],$menu['url']); ?>"><span><?php echo lang(array('item' => $link['title'])); ?></span></a></li>
	<?php endif; ?>
	<?php endforeach; ?>
	</ul>
	<?php endif; ?>
</div>
