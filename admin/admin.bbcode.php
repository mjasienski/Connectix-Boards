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

require(CB_PATH.'include/lib/class.smileysmanager.php');
$smile = new smileysmanager;

function formatFunction ($func) {
	$func=str_replace("\n",'/* \n */',$func);
	$func=str_replace("\r",'',$func);
	return $func;
}
function unFormatFunction ($func) {
	$func=str_replace('/* \n */',"\n",$func);
	return $func;
}
function getFileNameServer ($noext = false) {
	global $smile;
	$f = $smile->smiley_librariesdir.$_POST['sm_filenameserver'].($noext?'.gif':'');
	$f = str_replace(array('../','..\\'),'',trim($f));
	$f = strtr($f, 'àáâäéèêëíìîïóòôöúùûüýÿç', 'aaaaeeeeiiiioooouuuuyyc');
	$f = preg_replace('#(&[a-z0-9\#]+;)#','-',$f);
	$f = preg_replace('#[^a-zA-Z0-9/\.]#','-',$f);
	$f = preg_replace('#-+#', '-', $f);
	if (!preg_match('#\.(gif|jpg|jpeg|png|swf)$#i',$f))
		$f = $smile->smiley_librariesdir.utf8_substr(genValidCode(),0,10).'.gif';
	return $f;
}

$sub=(isset($_GET['sub']) && (int)$_GET['sub']>0 && (int)$_GET['sub']<6)?(int)$_GET['sub']:1;

if (isset($_POST['bb_send'])) {
	$name=utf8_strtolower(rewrite_words($_POST['bb_name']));
	if (utf8_strpos($_POST['bb_args'],'{--CONTENTS--}') !== false && !empty($name)) {
		$f=str_replace(array("\n","\r"),'',$_POST['bb_args'])."\n";
		$f.=((!(isset($_POST['bb_parse']) && $_POST['bb_parse']=='on'))?'-noparse- ':'').((isset($_POST['bb_addsize']) && $_POST['bb_addsize']=='on')?'-addsize-':'')."\n";
		$f.=formatFunction($_POST['bb_funcont'])."\n";
		$f.=formatFunction($_POST['bb_funargs'])."\n";

		$h=fopen(CB_PATH.'bbcode/'.$name.((utf8_strpos($_POST['bb_args'],'{--ARGS--}') !== false)?'=':'').'.bb','w');
		fwrite($h,$f);
		fclose($h);

		redirect(manage_url('admin.php','forum-admin.html').'?act=bb&sub=1');
	} else trigger_error(lang('pa_bbcode_error_name'),E_USER_WARNING);
} elseif ($sub==1 && isset($_GET['delete'])) {
	if (file_exists('./bbcode/'.str_replace('/','',$_GET['delete']).'.bb')) {
		@unlink('./bbcode/'.str_replace('/','',$_GET['delete']).'.bb');
		redirect (manage_url('admin.php','forum-admin.html').'?act=bb&sub=1');
	}
} elseif ($sub==1 && isset($_POST['bb_submit'])) {
	$forbidden = array();
	foreach ($_POST as $key => $value) {
		$matches = array();
		if (preg_match('`^sign_([a-z]+=?)$`',$key,$matches)) {
			$forbidden[] = $matches[1];
		}
	}
	$GLOBALS['cb_cfg']->updateElements(array('bb_sign_forbidden' => implode('|',$forbidden)));

	redirect (manage_url('admin.php','forum-admin.html').'?act=bb&sub=1');
} elseif ($sub==3 && isset($_GET['deletesmiley'])) {
	//ajout d'une requête
	$sm_filename = $GLOBALS['cb_db']->single_result('SELECT sm_filename FROM '.$GLOBALS['cb_db']->prefix.'smileys WHERE sm_id='.(int)$_GET['deletesmiley']);
	//si ce n'est pas un smiley original
	if (utf8_strpos($sm_filename,$smile->smiley_librariesdir) !== false) {
		$smile->smiley_delete($sm_filename);
		$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'smileys WHERE sm_id='.(int)$_GET['deletesmiley']);
	} else $GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'smileys SET sm_orig_used=\'non\' WHERE sm_id='.(int)$_GET['deletesmiley']);
	$smile->cacheSmileys();
} elseif ($sub==3 && isset($_GET['activatesmiley'])) {
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'smileys SET sm_orig_used=\'oui\' WHERE sm_id='.(int)$_GET['activatesmiley']);
	$smile->cacheSmileys();
} elseif ($sub==3 && isset($_GET['noactivatesmiley'])) {
	$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'smileys SET sm_orig_used=\'non\' WHERE sm_id='.(int)$_GET['noactivatesmiley']);
	$smile->cacheSmileys();
} elseif ($sub==3 && isset($_POST['sm_select'])) {
	$selected = array_filter($_POST['sm_select'],'is_numeric');
	if (count($selected) > 0) {
		if (isset($_POST['sm_mass_activate'])) {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'smileys SET sm_orig_used=\'oui\' WHERE sm_id IN ('.implode(',',$selected).')');
		} elseif (isset($_POST['sm_mass_noactivate'])) {
			$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'smileys SET sm_orig_used=\'non\' WHERE sm_id IN ('.implode(',',$selected).')');
		} elseif (isset($_POST['sm_mass_delete'])) {
			$smd = $GLOBALS['cb_db']->query('SELECT sm_id,sm_filename FROM '.$GLOBALS['cb_db']->prefix.'smileys WHERE sm_id IN ('.implode(',',$selected).')');
			$todelete = array();
			while ($dsm = $GLOBALS['cb_db']->fetch_assoc($smd)) {
				if (utf8_strpos($dsm['sm_filename'],$smile->smiley_librariesdir) !== false) {
					$smile->smiley_delete($dsm['sm_filename']);
					$todelete[] = $dsm['sm_id'];
				}
			}
			if (count($todelete) > 0)
				$GLOBALS['cb_db']->query('DELETE FROM '.$GLOBALS['cb_db']->prefix.'smileys WHERE sm_id IN ('.implode(',',$todelete).')');
			if (count(array_diff($selected,$todelete)) > 0)
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'smileys SET sm_orig_used=\'non\' WHERE sm_id IN ('.implode(',',array_diff($selected,$todelete)).')');
		}
		$smile->cacheSmileys();
		redirect(manage_url('admin.php','forum-admin.html').'?act=bb&sub=3');
	}
} elseif (isset($_POST['sm_send'])) {
	if (utf8_strlen(trim($_POST['sm_name']))>1 && !empty($_POST['sm_filenameserver'])) {
		$do=false;
		$edit=false;
		$filename = '';

		if (isset($_GET['editsmiley']) && is_numeric($_GET['editsmiley'])) {
			$filename = $GLOBALS['cb_db']->single_result('SELECT sm_filename FROM '.$GLOBALS['cb_db']->prefix.'smileys WHERE sm_id='.(int)$_GET['editsmiley']);
			if ($filename != false)
				$edit = true;
			else $filename = '';
		}
		
		// Already used symbols
		require_once(CB_CACHE_SMILEYS);
		$forbidden_symbols = array();
		foreach ($GLOBALS['cb_smileys'] as $smiley)
			if (!$edit || ($edit && $smiley['id'] != (int)$_GET['editsmiley']))
				$forbidden_symbols[] = $smiley['symbol'];
		
		if (!in_array(trim($_POST['sm_name']),$forbidden_symbols)) {
			if(isset($_POST['wherefile']) && !(isset($_GET['editsmiley']) && is_numeric($_GET['editsmiley']) && !$edit)) {
				if ($_POST['wherefile']=='upload') {
					if (!empty($_FILES['uploadimage']['size'])){
						if ($image=getimagesize(trim($_FILES['uploadimage']['tmp_name']))) {
							$val = array(IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG);
							if ($_FILES['uploadimage']['size'] <= 20480 && in_array($image[2],$val)) {
								$filename = getFileNameServer();
								//si le filenameserver contient un dossier : on crée ce dossier:
								mkdirs($smile->smiley_dir.dirname($filename));
								if (move_uploaded_file($_FILES['uploadimage']['tmp_name'], $smile->smiley_dir.$filename)) {
									$do=true;
								} else trigger_error(lang('pa_smileys_error_upload'),E_USER_WARNING);
							} else trigger_error(lang('pa_smileys_error_format'),E_USER_WARNING);
						} else trigger_error(lang('pa_smileys_error_upload'),E_USER_WARNING);
					} else trigger_error(lang('pa_smileys_error_upload'),E_USER_WARNING);
				} elseif ($_POST['wherefile']=='old' && !empty($_POST['sm_old_filenameserver']) && $edit) {
					$oldfilename = $GLOBALS['cb_db']->single_result('SELECT sm_filename FROM '.$GLOBALS['cb_db']->prefix.'smileys WHERE sm_id='.(int)$_GET['editsmiley']);
					$filename = getFileNameServer();
					if(!file_exists($smile->smiley_dir.$filename)) {
						//si le filenameserver contient un dossier : on crée ce dossier:
						mkdirs($smile->smiley_dir.dirname($filename));
						if (copy($smile->smiley_dir.$oldfilename,$smile->smiley_dir.$filename)) {
							$smile->smiley_delete($oldfilename) or trigger_error(lang('pa_smileys_error_upload'),E_USER_WARNING);
						} else trigger_error(lang('pa_smileys_error_upload'),E_USER_WARNING);
					}
					$do=true;
				} elseif ($_POST['wherefile']=='server') {
					$filename = getFileNameServer();
					if (file_exists($smile->smiley_dir.$filename)) $do=true;
					else trigger_error(lang('pa_smileys_error_notup'),E_USER_WARNING);
				} elseif ($_POST['wherefile']=='created' && !empty($_SESSION['bank']) && !empty($_SESSION['sm_ids'])) {
					$filename = getFileNameServer(true);
					if ($smile->smiley_save($filename,$smile->getSmileyFileName($_SESSION['bank'],$_SESSION['sm_ids'])))
						$do=true;
					else
						trigger_error(lang('pa_sm_create_error_27'),E_USER_WARNING);
				}
			}

			if (($do && file_exists($smile->smiley_dir.$filename)) || $edit) {
				if (!$edit) 
					$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'smileys(sm_symbol,sm_filename) VALUES(\''.clean($_POST['sm_name']).'\',\''.clean($filename).'\')');
				else 
					$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'smileys SET sm_symbol=\''.clean($_POST['sm_name']).'\',sm_filename=\''.clean($filename).'\' WHERE sm_id='.(int)$_GET['editsmiley']);

				$smile->cacheSmileys();
				redirect(manage_url('admin.php','forum-admin.html').'?act=bb&sub=3');
			}
		} else trigger_error(lang('pa_smileys_error_symbol').' ( '.clean(trim($_POST['sm_name']),STR_TODISPLAY).' )',E_USER_WARNING);
	} else trigger_error(lang('pa_smileys_error_form'),E_USER_WARNING);
} elseif (isset($_POST['sm_ma_send'])) {
	require_once(CB_CACHE_SMILEYS);
	$forbidden_symbols = array();
	foreach ($GLOBALS['cb_smileys'] as $smiley)
		$forbidden_symbols[] = $smiley['symbol'];
	
	foreach ($_POST as $key => $value) {
		if (utf8_substr($key,0,8) == 'sm_name_' && utf8_strlen(trim($value)) > 1) {
			$smid = utf8_substr($key,8);
			$path = utf8_substr($_POST['sm_path_'.$smid],utf8_strlen($smile->smiley_dir));
			$form = (int)$_POST['sm_form_'.$smid];
			if (!in_array(clean(trim($value)),$forbidden_symbols))
				$GLOBALS['cb_db']->query('INSERT INTO '.$GLOBALS['cb_db']->prefix.'smileys(sm_symbol,sm_filename,sm_form) VALUES(\''.clean($value).'\',\''.clean($path).'\','.$form.')');
			else
				trigger_error(lang('pa_smileys_error_symbol').' ( '.clean(trim($value),STR_TODISPLAY).' )',E_USER_WARNING);
		}
		
		$smile->cacheSmileys();
	}
} elseif (isset($_POST['sm_confirm_form'])) {
	foreach ($_POST as $key => $value) {
		if (utf8_substr($key,0,8) == 'sm_form_') {
			if ((int)$value == 1 || (int)$value == 2) {
				$smid = (int)utf8_substr($key,8);
				$GLOBALS['cb_db']->query('UPDATE '.$GLOBALS['cb_db']->prefix.'smileys SET sm_form='.(int)$value.' WHERE sm_id='.$smid);
			}
		}
	}
	$smile->cacheSmileys();
} elseif ($sub==5 && isset($_GET['create']) && $_GET['create']=='new') {
	$_SESSION['step'] = 1;
	redirect(manage_url('admin.php','forum-admin.html').'?act=bb&sub=5');
}

if ($sub==1) {
	$bbcodes=array();
	$h=opendir(CB_PATH.'bbcode');
	while (($file = readdir($h)) !== false) {
		if (utf8_substr($file,-3)=='.bb') {
			$bbcodes[] = array(
				'pa_bb_name' => ((utf8_substr($file,utf8_strlen($file)-4,1)=='=')?utf8_substr($file,0,utf8_strlen($file)-4):utf8_substr($file,0,utf8_strlen($file)-3)),
				'pa_bb_args' => (utf8_substr($file,utf8_strlen($file)-4,1)=='='),
				'pa_bb_sign' => in_array(utf8_substr($file,0,utf8_strlen($file)-3),explode('|',$GLOBALS['cb_cfg']->config['bb_sign_forbidden']))
				);
		}
	}
	closedir($h);

	$GLOBALS['cb_tpl']->assign('pa_bb_bbcodes',$bbcodes);

	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_bbcode','pa_bbcode_all'));
	$GLOBALS['cb_tpl']->assign('bb_part','bb_showall');
} elseif ($sub==2) {
	$l=array();
	$edit=false;
	if (isset($_GET['edit']) && file_exists(CB_PATH.'bbcode/'.basename($_GET['edit']).'.bb')) {
		if ($h=@fopen(CB_PATH.'bbcode/'.basename($_GET['edit']).'.bb','r')) {
			$n=1;
			while (!feof($h)) {
				$l[$n]=fgets($h);
				$n++;
			}
			fclose($h);
			$edit=true;
		}
	}
	$GLOBALS['cb_tpl']->assign('pa_bbcode_add_name',(($edit)?str_replace('=','',clean($_GET['edit'],STR_TODISPLAY)):''));
	$GLOBALS['cb_tpl']->assign('pa_bbcode_add_args',(($edit)?unFormatFunction($l[1]):''));
	$GLOBALS['cb_tpl']->assign('pa_bbcode_add_parse',(($edit && isset($l[2]) && utf8_strpos($l[2],'-noparse-') !== false)?'':'checked="checked"'));
	$GLOBALS['cb_tpl']->assign('pa_bbcode_add_addsize',(($edit && isset($l[2]) && utf8_strpos($l[2],'-addsize-') !== false)?'checked="checked"':''));
	$GLOBALS['cb_tpl']->assign('pa_bbcode_add_funcont_before','function content ($c) {');
	$GLOBALS['cb_tpl']->assign('pa_bbcode_add_funcont',(($edit && isset($l[3]))?unFormatFunction($l[3]):''));
	$GLOBALS['cb_tpl']->assign('pa_bbcode_add_funcont_after','return $c; }');
	$GLOBALS['cb_tpl']->assign('pa_bbcode_add_funargs_before','function args ($a) {');
	$GLOBALS['cb_tpl']->assign('pa_bbcode_add_funargs',(($edit && isset($l[4]))?unFormatFunction($l[4]):''));
	$GLOBALS['cb_tpl']->assign('pa_bbcode_add_funargs_after','return $a; }');

	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_bbcode',(($edit)?'pa_bbcode_edit':'pa_bbcode_add')));
	$GLOBALS['cb_tpl']->assign('bb_part','bb_add');
} elseif ($sub==3) {
	$smileyall=array();
	$ret = $GLOBALS['cb_db']->query('SELECT sm_id,sm_symbol,sm_filename,sm_form,sm_orig_used FROM '.$GLOBALS['cb_db']->prefix.'smileys ORDER BY sm_id ASC');
	while ($data = $GLOBALS['cb_db']->fetch_assoc($ret)) {
		$smileyall[] = array(
			'pa_sm_id' => $data['sm_id'],
			'pa_sm_image' => '<img src="'.$smile->smiley_dir.$data['sm_filename'].'" />',
			'pa_sm_name' => $data['sm_symbol'],
			'pa_sm_form' => $data['sm_form'],
			'pa_sm_file' => $data['sm_filename'],
			'pa_sm_orig_used' => $data['sm_orig_used']=='oui',
			'pa_sm_library' => (utf8_strpos($data['sm_filename'],$smile->smiley_librariesdir)===false)
			);
	}
	$GLOBALS['cb_tpl']->assign('pa_sm_smileys',$smileyall);

	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_bbcode','pa_smileys_all'));
	$GLOBALS['cb_tpl']->assign('bb_part','sm_showall');
} elseif ($sub==4) {
	$edit=false;
	$data=array(
		'sm_id' => 0,
		'sm_symbol' => '',
		'sm_filename' => '',
		'sm_orig_used' => ''
		);
	
	if (isset($_GET['editsmiley'])) {
		$ret = $GLOBALS['cb_db']->query('SELECT sm_id,sm_symbol,sm_filename,sm_orig_used FROM '.$GLOBALS['cb_db']->prefix.'smileys WHERE sm_id='.(int)$_GET['editsmiley']);
		if ($d = $GLOBALS['cb_db']->fetch_assoc($ret)) {
			$data = $d;
			$edit=true;
		} else redirect(manage_url('admin.php','forum-admin.html').'?act=bb&sub=4');
	}
	
	//si c'est un smiley de base, on ne peut pas le modifier
	$readonly=(utf8_strpos($data['sm_filename'],$smile->smiley_librariesdir)===false && $data['sm_filename']!='');
	$GLOBALS['cb_tpl']->assign('pa_sm_add_readonly',$readonly);

	$GLOBALS['cb_tpl']->assign('pa_sm_add_edit',$edit);
	$GLOBALS['cb_tpl']->assign('pa_sm_add_name',$data['sm_symbol']);
	$GLOBALS['cb_tpl']->assign('pa_sm_add_filenameserver',str_replace($smile->smiley_librariesdir,'',$data['sm_filename']));
	$GLOBALS['cb_tpl']->assign('pa_sm_add_subdir',!$readonly?$smile->smiley_librariesdir:'');
	$GLOBALS['cb_tpl']->assign('pa_sm_add_uploadimage_old_filename',($edit)?$data['sm_filename']:null);
	
	$GLOBALS['cb_tpl']->assign('pa_sm_massadd', $edit ? false : true);
	if (!$edit) {
		$ret = $GLOBALS['cb_db']->query('SELECT sm_filename FROM '.$GLOBALS['cb_db']->prefix.'smileys');
		$usedimages = array();
		while ($data = $GLOBALS['cb_db']->fetch_assoc($ret)) {
			$usedimages[] = $smile->smiley_dir.$data['sm_filename'];
		}
		
		$serverimages = getFilesInLibraryDir();
		$notusedimages = array_diff($serverimages,$usedimages);
		
		$GLOBALS['cb_tpl']->assign('pa_sm_massadd_notused',$notusedimages);
	}
	
	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_bbcode',(($edit)?'pa_smileys_edit':'pa_smileys_add')));
	$GLOBALS['cb_tpl']->assign('bb_part','sm_add');
} elseif ($sub==5) {
	$_SESSION['step'] = (!isset($_SESSION['step'])) ? 1 : $_SESSION['step'];
	require_once(CB_PATH.'include/lib/lib.images.php');

	if (isset($_POST['sm_bank']) && is_numeric($_POST['sm_bank'])) {
		$_SESSION['step'] = 2;
		$_SESSION['bank'] = (int)$_POST['sm_bank'];
	}
	if (isset($_POST['sm_back'])) {
		$_SESSION['step'] = 1;
		$_SESSION['bank'] = null;
	}

	//ETAPE 1 : il faut choisir une banque
	if ($_SESSION['step']==1) {
		$GLOBALS['cb_tpl']->assign('sm_stage','bankselection');
		$GLOBALS['cb_tpl']->assign('sm_banks_form',$smile->getBanksForm());
	}
	//ETAPE 2 : il faut choisir les couches et demander un aperçu et/ou enregistrer le fichier.
	else {
		$GLOBALS['cb_tpl']->assign('sm_stage','creation');

		$_SESSION['sm_ids'] = isset($_SESSION['sm_ids'])?$_SESSION['sm_ids']:array();

		//si aperçu et envoi (ou modification)
		if (isset($_POST['sm_preview']) || isset($_POST['sm_send'])) {
			if (isset($_POST['sm_preview']))
				$_SESSION['sm_ids'] = $smile->getSelectedComponents();

			if (count($_SESSION['sm_ids']) == 0)
				trigger_error(lang('pa_sm_create_error_1'),E_USER_WARNING);
			else {
				//création du fichier du smiley
				$GLOBALS['cb_tpl']->assign('sm_created',true);
				$GLOBALS['cb_tpl']->assign('sm_imgfile',$smile->getSmileyFileName($_SESSION['bank'],$_SESSION['sm_ids']));

				//pour valider le smiley
				$GLOBALS['cb_tpl']->assign('sm_filename_extension','.gif');
				$GLOBALS['cb_tpl']->assign('sm_filename_subdir',$smile->smiley_librariesdir);
				$GLOBALS['cb_tpl']->assign('sm_name',isset($_POST['sm_name'])?$_POST['sm_name']:'');
				$GLOBALS['cb_tpl']->assign('sm_filenameserver',isset($_POST['sm_filenameserver'])?$_POST['sm_filenameserver']:'');
			}
		} else $_SESSION['sm_ids'] = array();

		$GLOBALS['cb_tpl']->assign('sm_components_form',$smile->getComponentsForm($_SESSION['bank'],((!empty($_SESSION['sm_ids']))?$_SESSION['sm_ids']:array())));
	}

	$GLOBALS['cb_tpl']->assign('g_subtitle',array('pa_bbcode','pa_smileys_create'));
	$GLOBALS['cb_tpl']->assign('bb_part','sm_create');
}

$GLOBALS['cb_tpl']->assign('g_part','admin_bbcode.php');
?>