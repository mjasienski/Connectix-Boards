<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title><?php echo lang('paneladmin'); ?><?php if (is_array($g_subtitle)): ?><?php foreach ($g_subtitle as $title): ?> - <?php echo lang(array('item' => $title)); ?><?php endforeach; ?><?php else: ?> - <?php echo lang(array('item' => $g_subtitle)); ?><?php endif; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php if (isset($g_redirect)): ?>
	<meta http-equiv="refresh" content="<?php echo $g_redirect_delay; ?>; url=<?php echo $g_redirect; ?>" />
	<?php endif; ?>
	<script type="text/javascript" src="include/javascripts/cb_base.js"></script>
	<script type="text/javascript" src="include/javascripts/cb_bbcode.js"></script>
	<link rel="stylesheet" media="screen" type="text/css" title="Design" href="admin/design/style.css" />
</head>

<body>
<div id="template">
<div id="main">


<h1 id="header">
	<a href="index.php" id="headerlink"><span><?php echo $g_forumname; ?></span></a>
</h1>

<div id="headmenu">
	<ul>
		<li><a href="<?php echo manage_url('index.php','forum.html'); ?>" class="hm_backtosite"><span><?php echo lang('pa_backtoforum'); ?></span></a></li>
	</ul>
</div>

<div id="addressbar">
	<p>
		<a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>"><?php echo lang('pa_bigtitle'); ?></a><?php if (is_array($g_subtitle)): ?><?php foreach ($g_subtitle as $title): ?> <?php echo $g_addr_sep; ?> <?php echo lang(array('item' => $title)); ?><?php endforeach; ?><?php else: ?> <?php echo $g_addr_sep; ?> <?php echo lang(array('item' => $g_subtitle)); ?><?php endif; ?>
	</p>
</div>

<dl id="menu">
	<?php foreach ($g_links as $link): ?>
	<dt <?php if (!empty($link['accesscode'])): ?> onclick="javascript:hideAndShow('<?php echo $link['accesscode']; ?>');" <?php endif; ?>>
	<?php if (!empty($link['main_link'])): ?> <a href="<?php echo $link['main_link']; ?>"><?php echo lang(array('item' => $link['name'])); ?></a><?php else: ?><?php echo lang(array('item' => $link['name'])); ?><?php endif; ?>
	</dt>
	<?php if (isset($link['sub_links']) && count($link['sub_links']) > 0 && !empty($link['accesscode'])): ?>
		<dd id="<?php echo $link['accesscode']; ?>"><ul>
		<?php foreach ($link['sub_links'] as $sublink): ?>
			<li><a href="<?php echo $sublink['url']; ?>"><?php echo lang(array('item' => $sublink['name'])); ?></a></li>
		<?php endforeach; ?>
		</ul></dd>
		<?php if ($link['nodisplay']): ?><script type="text/javascript">hideAndShow('<?php echo $link['accesscode']; ?>');</script><?php endif; ?>
	<?php endif; ?>
	<?php endforeach; ?>
</dl>

<form action="" method="post" enctype="multipart/form-data">
	<div id="mainpanel">
		<?php if (!empty($warning)): ?>
		<div class="warning">
			<?php foreach ($warning as $war): ?>
			<p>
				<?php echo $war['str']; ?> <?php echo $war['pos']; ?>
			</p>
			<?php endforeach; ?>

		</div>

		<?php endif; ?>
		<?php if (!empty($notice)): ?>
		<div class="notice">
			<?php foreach ($notice as $not): ?>
			<p>
				<?php echo $not['str']; ?> <?php echo $not['pos']; ?>
			</p>
			<?php endforeach; ?>
		</div>

		<?php endif; ?>

		<?php include ($template_path.$g_part); ?>

	</div>
</form>

<div id="footer">
	<p id="copyright">
		Powered by <a href="http://www.connectix-boards.org">Connectix Boards</a> <?php echo $g_version; ?> &copy; 2005-<?php echo date('Y'); ?> &nbsp; (<?php echo $g_queries; ?> queries, <?php echo $g_execution; ?> sec)
	</p>
</div>


</div>
</div>
</body>
</html>
