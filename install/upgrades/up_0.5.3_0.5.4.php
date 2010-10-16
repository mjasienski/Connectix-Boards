<?php
$GLOBALS['cb_db']->query("ALTER TABLE `".$GLOBALS['cb_db']->prefix."topicgroups` ADD `tg_link` TEXT NOT NULL");
$GLOBALS['cb_db']->query("REPLACE INTO `".$GLOBALS['cb_db']->prefix."config` (`cf_field`, `cf_value`) VALUES ('hash_type', 'cb')");
?>