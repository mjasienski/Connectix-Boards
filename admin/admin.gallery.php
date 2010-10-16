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
if (!defined('CB_ADMIN')) exit('Access denied!');

require_once(CB_PATH.'include/lib/lib.users.php');
$GLOBALS['cb_tpl']->lang_load('userprofile.lang');

if (isset($_POST['a_send'])) {
	if ($image=getimagesize(trim($_FILES['imagefile']['tmp_name']))) {
		require_once(CB_PATH.'include/lib/lib.images.php');
		if (in_array($image[2],getSupportedImages()) && $_FILES['imagefile']['size'] <= 1000000) {
			$fname = basename($_FILES['imagefile']['name']);
			$temp_file='avatars/temp/'.$fname;
			$real_file='avatars/gallery/'.$fname;

			$short_name=utf8_substr($fname,0,utf8_strrpos($fname,'.'));
			$ext=utf8_strtolower(utf8_substr($fname,utf8_strrpos($fname,'.')+1));
			if (in_array($ext,array('gif','png','jpg','jpeg'))) {
				$i=1;
				while (file_exists($real_file)) {
					$real_file = 'avatars/gallery/'.$short_name.'-'.$i.'.'.$ext;
					$i++;
				}

				if (@move_uploaded_file($_FILES['imagefile']['tmp_name'],$temp_file)) {
					resizeImage($temp_file,$image,$real_file,$GLOBALS['cb_cfg']->config['maxsize'],$GLOBALS['cb_cfg']->config['maxsize']);
					unlink($temp_file);
					chmod($real_file,0777);
					trigger_error(lang('pa_gallery_add_successloaded'),E_USER_NOTICE);
				} else trigger_error(lang('usr_badpath'),E_USER_WARNING);
			} else trigger_error(lang('usr_badformat'),E_USER_WARNING);
		} else trigger_error(lang('usr_badformat'),E_USER_WARNING);
	} else trigger_error(lang('usr_badpath'),E_USER_WARNING);
} elseif (isset($_GET['delete'])) {
	if (file_exists('avatars/gallery/'.basename($_GET['delete']))) unlink('avatars/gallery/'.basename($_GET['delete']));
}

$avatars=getGallery();
$GLOBALS['cb_tpl']->assign('a_avatars',$avatars);
if (count($avatars)==0) trigger_error(lang('pa_gallery_all_empty'),E_USER_NOTICE);
$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_gallery','pa_gallery_all'));

$GLOBALS['cb_tpl']->assign('g_part','admin_gallery.php');
?>