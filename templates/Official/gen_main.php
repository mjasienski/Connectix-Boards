<?php if (!defined('CB_TEMPLATE')) exit('Incorrect access attempt !!'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title><?php echo $g_pagename; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php echo $g_csslink; ?>
	<?php echo $g_rsslink; ?>
	<?php echo $g_javascript; ?>
</head>

<body>
<div id="template">
<div id="main">

<h1 id="header">
	<a href="<?php echo manage_url('index.php', 'forum.html'); ?>" id="headerlink"><span><?php echo $g_forumname; ?></span></a>
</h1>

<?php require($template_path.'gen_contents.php'); ?>

</div>
</div>
</body>
</html>
