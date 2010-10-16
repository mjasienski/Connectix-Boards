<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title>Connectix Boards - <?php echo $m_title; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php if (isset($m_delay,$m_url)): ?>
	<meta http-equiv="refresh" content="<?php echo $m_delay; ?>; url=<?php echo $m_url; ?>">
	<?php endif; ?>
	<link rel="stylesheet" media="screen" type="text/css" href="<?php echo $m_css; ?>" />
</head>

<body>
<div id="template">
<div id="main">

<h1 id="header">
	<a href="<?php echo manage_url('index.php', 'forum.html'); ?>" id="headerlink"><span>Connectix Boards</span></a>
</h1>

<div class="container">
	<h2>
		<span class="title_pre"><span>&nbsp;>&nbsp;&nbsp;</span></span>
		<?php echo $m_title; ?> 
		<?php if ( !empty($m_pos) ): ?>(<?php echo $m_pos; ?>)<?php endif; ?>
	</h2>
	
	<div class="inforow">
		<?php echo $m_msg; ?>
	</div>
	
	<?php if (isset($m_info)): ?>
	<div class="inforow">
		<?php echo $m_info; ?>
	</div>
	<?php endif; ?>
	
	<?php if (!isset($m_nolinks) || $m_nolinks == false): ?>
	<div class="inforow_links">
		<?php if (isset($m_url)): ?>
		<a href="<?php echo $m_url; ?>"><?php echo lang('r_nowait'); ?></a>
		<?php else: ?>
		<a href="<?php echo manage_url('index.php', 'forum.html'); ?>">Accueil du forum</a> - <a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>">Administration</a>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</div>

<div id="footer">
	<p id="copyright">
		Powered by <a href="http://www.connectix-boards.org">Connectix Boards</a> <?php echo CB_VERSION; ?> &copy; 2005-<?php echo date('Y'); ?>
	</p>
</div>

</div>
</div>
</body>
</html>
