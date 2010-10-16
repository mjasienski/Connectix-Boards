<ul class="bbcode_list">{--CONTENTS--}</ul>

$c=trim($c); if (strpos("<br />\n",$c) !== false && strpos("<br />\n",$c) == 0) $c=substr($c,8); $c=str_replace(array("[*]<br />\n","[*] <br />\n","[*]  <br />\n"),'',$c); $c=str_replace('[*]','</li>'."\n".'<li><span class="nodisplay">[CB_BBCODE_LIST_STAR]</span>',$c); $c=substr($c,6).'</li>';

