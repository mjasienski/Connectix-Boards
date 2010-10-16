<div class="phpcode"><p>{--CONTENTS--}</p></div>
-noparse-
$c = preg_replace('#color="(.*?)"#', 'class="php_$1" style="color: $1;"', preg_replace('#style="color: \#(.*?)"#','class="php_$1" style="color: #$1"', str_replace(array('<font ', '</font>'."\n", "'"),array('<span ', '</span>'."\n", '&#39;'), highlight_string(html_entity_decode(trim($c),ENT_QUOTES) ,true))));

