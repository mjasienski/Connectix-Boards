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

//// Fonctions de gestion des images (librairie GD) ////

/* Fonction qui détermine si gd2 est activé ou pas. */
function isGdEnabled () {
	if (extension_loaded('gd')) {
		$gd=gd_info();
		preg_match('/\d/', $gd['GD Version'], $match);
		if ((int)$match[0] >= 2) return true;
	}
	return false;
}
/* Fonction qui retourne les formats supportés. */
function getSupportedImages () {
	if (!isGdEnabled()) return array();
	$ok=array();
	$gd=gd_info();
	if ($gd['GIF Read Support'] && $gd['GIF Create Support']) $ok[] = IMAGETYPE_GIF;
	if ($gd['JPG Support']) $ok[] = IMAGETYPE_JPEG;
	if ($gd['PNG Support']) $ok[] = IMAGETYPE_PNG;
	return $ok;
}
/* Fonction qui renvoie l'extension se rapportant à un entier de type d'image. */
function getExtension ($imagetype) {
	switch ($imagetype) {
		case IMAGETYPE_GIF: return 'gif'; break;
		case IMAGETYPE_PNG: return 'png'; break;
		case IMAGETYPE_JPEG: return 'jpeg'; break;
		default: return 'dat';
	}
}
/* Fonctions qui lancent la fonction gd demandée. */
function imagecreatefromfile($file,$imagetype) {
	$r=null;
	switch ($imagetype) {
		case IMAGETYPE_JPEG:
			$r=imagecreatefromjpeg($file);
		break;
		case IMAGETYPE_BMP:
			$r=imagecreatefrombmp($file);
		break;
		case IMAGETYPE_GIF:
			$r=imagecreatefromgif($file);
		break;
		case IMAGETYPE_PNG:
			$r=imagecreatefrompng($file);
		break;
	}
	$transp=imagecolorallocatealpha($r, 255, 255, 255, 127);
	imagecolortransparent ($r,$transp);
	return $r;
}
function imagefile ($res,$path = null,$imagetype) {
	$f = null;
	switch ($imagetype) {
		case IMAGETYPE_JPEG: $f = 'imagejpeg';
		break;
		case IMAGETYPE_BMP:
			$f = 'imagewbmp';
		break;
		case IMAGETYPE_GIF:
			$f = 'imagegif';
		break;
		case IMAGETYPE_PNG:
			$f = 'imagepng';
		break;
	}
	if ($f != null) {
		if ($path == null) $f($res);
		else $f($res,$path);
	}
}
/* Fonction qui renvoie la taille redimensionnée d'une image. */
function getNewImageSize ( $src_x , $src_y , $max_x , $max_y ) {
	$dest_x=$src_x;
	$dest_y=$src_y;
	if ($src_x > $max_x || $src_y > $max_y) {
		$r=$src_x/$src_y;
		if ($src_x/$max_x <= $src_y/$max_y) {
			$dest_x=$max_x*$r;
			$dest_y=$max_y;
		} else {
			$dest_x=$max_x;
			$dest_y=$max_y/$r;
		}
	}
	return array($dest_x,$dest_y);
}
/* Fonction qui redimensionne une image. */
function reSizeImage ($src_file,$src_infos,$dest_file,$max_x,$max_y) {
	$src=imagecreatefromfile($src_file,$src_infos[2]);
	list ($dest_x,$dest_y) = getNewImageSize($src_infos[0],$src_infos[1],$max_x,$max_y);
	$dest=null;
	if ($src_infos[2] != IMAGETYPE_GIF) $dest = imagecreatetruecolor($dest_x,$dest_y);
	else $dest = imagecreate($dest_x,$dest_y);
	imagecopyresampled($dest,$src,0,0,0,0,$dest_x,$dest_y,$src_infos[0],$src_infos[1]);
	imagefile($dest,$dest_file,$src_infos[2]);
}
/* Fonction qui supprime l'avatar courant de l'utilisateur du ftp. */
function deleteAvatar ($id_user) {
	$h = opendir(CB_PATH.'avatars/users');
	while (false !== ($file = readdir ($h))) {
		if ($file != '.' && $file != '..' && utf8_strpos($file,'user'.$id_user.'.') !== false) {
			@unlink(CB_PATH.'avatars/users/'.$file);
		}
	}
	closedir($h);
}
/* Renvoie un captcha, en régénérant son code si demandé */
function getCaptcha ($regen = true) {
	if (!isset($_SESSION['verifnbr']) || $regen) 
		$_SESSION['verifnbr'] = str_replace('0','G',utf8_strtoupper(utf8_substr(genValidCode(),0,6)));
	
	$verif=null;
	if (extension_loaded('gd') && in_array(IMAGETYPE_JPEG,getSupportedImages())) {
		$c = genValidCode();
		return '<img src="'.manage_url('image.php?id='.$c,'forum-image-'.$c.'.html').'" alt="Anti-Bot Verification Number" id="captcha" />';
	} else 
		return '<span id="verifpseudoimage">'.$_SESSION['verifnbr'].'</span>';
}
?>