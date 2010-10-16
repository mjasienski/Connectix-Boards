<a href="{--CONTENTS--}">{--CONTENTS--}</a>
-noparse-noautolink-
$c = trim($c); if ( preg_match( "/([\.,\?]|&#33;)$/", $c) ) $c = preg_replace( "/([\.,\?]|&#33;)$/", "", $c );$c = preg_replace( "/javascript:/i", "java script&#58; ", $c );if ( ! preg_match("#^(http|https|ftp)://#", $c ) ) {$c = 'http://'.$c;}
