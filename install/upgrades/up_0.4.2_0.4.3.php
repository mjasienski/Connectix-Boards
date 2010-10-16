<?php
$GLOBALS['cb_db']->query("REPLACE INTO ".$GLOBALS['cb_db']->prefix."config (cf_field,cf_value) VALUES('suspend_register','no')");
?>