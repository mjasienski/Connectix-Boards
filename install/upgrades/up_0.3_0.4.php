<?php
if (!defined('CB_INC')) redirect('index.php');

require_once(CB_PATH.'include/lib/lib.db.php');
execute_sqlfile('install/sql/tables_0.3_0.4.sql');

$GLOBALS['cb_db']->query("REPLACE INTO ".$GLOBALS['cb_db']->prefix."config (cf_field,cf_value) VALUES('enablemail','yes')");
$GLOBALS['cb_db']->query("DELETE FROM ".$GLOBALS['cb_db']->prefix."config WHERE cf_field = 'fileallowed'");
$GLOBALS['cb_db']->query("DELETE FROM ".$GLOBALS['cb_db']->prefix."config WHERE cf_field = 'mailfrom'");

execute_sqlfile('install/sql/smileys.sql');
?>