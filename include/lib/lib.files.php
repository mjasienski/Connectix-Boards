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

//// Fonctions de gestion des fichiers et dossiers ////

/* Fonction qui calcule la taille d'un fichier distant (avec une url). */
function remoteFileSize($uri) {
	if (utf8_strpos($uri,'http://') !== false) $uri = utf8_substr($uri,7);
	$host = ((utf8_strpos($uri,'/') !== false)?utf8_substr($uri,0,utf8_strpos($uri,'/')):$uri);
	if ($fp = fsockopen($host, 80, $errno, $errstr, 3)) {
		fwrite($fp, 'HEAD http://'.$uri." HTTP/1.0\r\n\r\n");
		$c='';
		while (!feof($fp)) {
			$c .= fgets($fp, 128);
		}
		fclose($fp);
		if (($p = utf8_strpos($c,'Content-Length')) !== false) {
			$c=utf8_substr($c,$p+16);
			if (($p2 = utf8_strpos($c,"\n")) !== false) {
				$c=utf8_substr($c,0,$p2);
				$c=str_replace(array("\r","\n"),'',$c);
				return (int)$c;
			}
		}
	}
	trigger_error('Failed to connect to '.$host.'. ',E_USER_WARNING);
	return false;
}
?>