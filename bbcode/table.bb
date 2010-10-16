<table class="tablebbcode">{--CONTENTS--}</table>

$c = str_replace(array("##$$##NEWLINE##$$##","##$$##DBSP##$$##"),array("\n","  "),trim(str_replace(array("\n","  "),array('<!-- <br /> -->','<!-- DBSP -->'),$c)));