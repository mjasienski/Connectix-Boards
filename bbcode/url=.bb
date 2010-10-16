<a href="{--ARGS--}">{--CONTENTS--}</a>
-noautolink-

if ( preg_match( "/([\.,\?]|!)$/", $a) ) $a = preg_replace( "/([\.,\?]|!)$/", "", $a );$a = preg_replace( "/javascript:/i", "java script: ", $a );if ( ! preg_match("#^(http|https|ftp)://#", $a ) ) {$a = 'http://'.$a;}
