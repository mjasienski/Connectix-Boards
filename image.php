<?php
/**
*	Connectix Boards 1.0, free interactive php bulletin boards.
*	Copyright (C) 2005-2010  Jasienski Martin.
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 3 of the License, or
*	(at your option) any later version.
*
*	This program is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU General Public License for more details.
*
*	You can find a copy of the GNU General Public License at 
*	<http://www.connectix-boards.org/license.txt>.
*/

define('CB_INC', 'CB');

require('common.php');
restore_error_handler();
require_once(CB_PATH.'include/lib/lib.images.php');

$code = 'ERROR';
if (isset($_SESSION['verifnbr']) && extension_loaded('gd') && in_array(IMAGETYPE_PNG,getSupportedImages()))
	$code = $_SESSION['verifnbr'];

$wc = 25; // Largeur d'une case de lettres
$hc = 40; // Hauteur d'une case de lettres
$fs = 22; // Taille de la police
$sd = 20; // Bords latéraux
$an = 25; // Inclinaison maximale des lettres
$rl = 10; // Nombre de lignes aléatoires

/* Taille de l'image */
$w = $wc*utf8_strlen($code) + 2*$sd;
$h = $hc;

/*Création de l'image et de ses couleurs associées */
$image = imagecreate($w,$h);
$back = imagecolorallocate($image,220,220,220);

/* Police de caractères, si arial ne fonctionne pas */
$myfont = imageloadfont('include/fonts/dreamofme.gdf');

/* Ecriture du texte */
error_reporting(0);
for ($i = 0; $i<utf8_strlen($code); $i++) {
	$gs = mt_rand(10,60);
	$font = imagecolorallocate($image,$gs,$gs,$gs);
	$ks = imagettftext ($image, $fs, mt_rand(-$an,$an), $sd+$wc*$i+2, $hc-mt_rand(4,10), $font, 'arial', $code{$i} );
	if (!$ks) {
		imagechar($image, $myfont, $sd+$wc*$i+mt_rand(0,5), mt_rand(2,20), $code{$i}, $font);
		$rl = 3;
	}
}
error_reporting(E_ALL);

/* Fond de lignes aléatoires */
for ($i=0;$i<$rl;$i++) {
	$gs = mt_rand(10,60);
	$blur = imagecolorallocate($image,$gs,$gs,$gs);
	$wb = (bool)mt_rand(0,1);
	$pts = array ();
	$pts[$wb?1:2] = 0;
	$pts[$wb?3:4] = $wb?$w-1:$h-1;
	$pts[$wb?2:1] = mt_rand(0,$wb?$h-1:$w-1);
	$pts[$wb?4:3] = mt_rand(0,$wb?$h-1:$w-1);
	imageline($image,$pts[1],$pts[2],$pts[3],$pts[4],$blur);
}

/* Déformation de l'image */
$canvas = imagecreatetruecolor($w, $h);
$osc = mt_rand(1,2)*(round(mt_rand(0,1))?1:-1);
$max_distort=mt_rand(3,4);

/* Déformation verticale */
for ($i = 0; $i < $w; $i++) {
	$distortion = $max_distort*sin(deg2rad(2*$i*$osc));
	imagecopyresized($canvas, $image, 0 + $i, 0 + $distortion, 0 + $i, 0, $w + $i, $h, $w + $i, $h);
}

/* Déformation horizontale */
for ($i = 0; $i < $h; $i++) {
	$distortion = 10*sin (deg2rad(2*$i*$osc));
	imagecopyresized($image, $canvas, 0 + $distortion, 0 + $i, 0, 0 + $i, $w, $h + $i, $w, $h + $i);
}

/* Pointillés sur l'image */
for($i=0;$i<80;$i++) {
	$gs = mt_rand(10,60);
	$blur = imagecolorallocate($image,$gs,$gs,$gs);
	for($j=0;$j<10;$j++) imagesetpixel($image,rand(1,$w-1),rand(1,$h-1),$blur);
}

/* Bord de l'image */
imagepolygon($image,array(0,0,0,$h-1,$w-1,$h-1,$w-1,0,0,0),5,$font);

header ('Content-type: image/png');
imagefile($image,null,IMAGETYPE_PNG);
?>