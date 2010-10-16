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

/**
* Les smileys ajoutés ou créés sont inscrits dans le dossier $smiley_dir/$smiley_librariesdir/
* Si spécifié, les smileys ajoutés peuvent l'être dans un sous dossier de $smiley_librariesdir/
* Si le fichier se trouve  dans le dossier $smiley_librariesdir alors il sera effacé lors de sa suppression.
*/

class smileysmanager {
	var $smiley_dir	= 'smileys/';				// Répertoire de stockage des smileys
	var $smiley_librariesdir = 'libraries/';	// Le répertoire des smileys ajoutés ou créés.
	var $smiley_cbpath = 'http://smileys.connectix-boards.org/';

	/* Constructeur. */
	function smileysmanager() {
		if (!defined('CB_ADMIN')) redirect();
	}

	/* Fonction qui écrit le fichier de cache des smileys. */
	function cacheSmileys () {
		$smileys = array();
		$result = $GLOBALS['cb_db']->query('SELECT sm_id,sm_symbol,sm_filename,sm_form FROM '.$GLOBALS['cb_db']->prefix.'smileys WHERE sm_orig_used=\'oui\' ORDER BY sm_id ASC');
		while ($data = $GLOBALS['cb_db']->fetch_assoc($result)) {
			$smileys[] = array(
				'id' => $data['sm_id'],
				'symbol' => $data['sm_symbol'],
				'filename' => $data['sm_filename'],
				'form' => $data['sm_form']
				);
		}
		
		file_put_contents(CB_CACHE_SMILEYS,'<?php '."\n".'$GLOBALS[\'cb_smileys\'] = unserialize(\''.str_replace("'","\\'",serialize($smileys)).'\');'."\n".'?>');
	}

	/* Le formulaire de banques */
	function getBanksForm() {
		return utf8_encode(implode('',file($this->smiley_cbpath.'index.php?step=1')));
	}

	/* Le formulaire de composants */
	function getComponentsForm($bank_id,$ids) {
		$sids = '';
		if (!empty($ids))
			$sids = implode('-',$ids);
		return utf8_encode(implode('',file($this->smiley_cbpath.'index.php?step=2&bank='.$bank_id.((!empty($sids))?'&sids='.$sids:''))));
	}

	/* Les ids sélectionnés */
	function getSelectedComponents () {
		//les couches
		$layer_0=array('layer_id'=>0,'layer_type'=>'radio','layer_name'=>'object','layer_title'=>'pa_sm_layer_object');
		$layer_1=array('layer_id'=>1,'layer_type'=>'radio','layer_name'=>'skin','layer_title'=>'pa_sm_layer_skin');
		$layer_2=array('layer_id'=>2,'layer_type'=>'radio','layer_name'=>'cap','layer_title'=>'pa_sm_layer_cap');
		$layer_3=array('layer_id'=>3,'layer_type'=>'checkbox','layer_name'=>'addskin','layer_title'=>'pa_sm_layer_addskin');
		$layer_4=array('layer_id'=>4,'layer_type'=>'radio','layer_name'=>'eyes','layer_title'=>'pa_sm_layer_eyes');
		$layer_5=array('layer_id'=>5,'layer_type'=>'radio','layer_name'=>'nose','layer_title'=>'pa_sm_layer_nose');
		$layer_6=array('layer_id'=>6,'layer_type'=>'radio','layer_name'=>'mouth','layer_title'=>'pa_sm_layer_mouth');
		$layer_7=array('layer_id'=>7,'layer_type'=>'checkbox','layer_name'=>'accessory','layer_title'=>'pa_sm_layer_accessory');

		//on récupère les ids sélectionnés
		$ids=array();
		for($i=0;$i<8;$i++) {
			if($i==0 && !empty($_POST['sm_layer0'])) {
				$ids[]=$_POST['sm_layer0'];
			}
			else {
				if(${'layer_'.$i}['layer_type']=='radio' && !empty($_POST['sm_layer'.$i]) && empty($_POST['sm_layer0']))
					$ids[]=$_POST['sm_layer'.$i];
				elseif(${'layer_'.$i}['layer_type']=='checkbox') {
					foreach ($_POST as $key => $value) {
						if (preg_match('#^sm_layer'.$i.'_([0-9]+)$#',$key)) {
							if(!empty($value) && empty($_POST['sm_layer0']))
								$ids[]=$value;
						}
					}
				}
			}
		}
		return $ids;
	}

	/* Pour récupérer le nom du fichier image sur le serveur de CB */
	function getSmileyFileName ($bank_id,$ids) {
		return $this->smiley_cbpath.'index.php?step=3&bank='.$bank_id.'&sids='.implode('-',$ids);
	}

	/* $filename_server peut est du type "dossiersparents/filename.gif" avec autant de dossiers parents désirés */
	function smiley_save($filename_server,$filename_orig) {
		if (file_exists($filename_server)) {
			trigger_error(lang('pa_sm_create_error_24').' [file name='.$this->smiley_dir.$filename_server.'.'.$this->smiley_temptype.']',E_USER_WARNING);
			return false;
		}

		//crée les dossiers parents si ceux-ci n'existent pas
		mkdirs($this->smiley_dir.dirname($filename_server));

		if (!copy($filename_orig,$this->smiley_dir.$filename_server)) {
			trigger_error(lang('pa_sm_create_error_25').' [filename='.$this->smiley_dir.$filename_server.']',E_USER_WARNING);
			return false;
		}
		
		error_reporting(0);
		chmod($this->smiley_dir.$filename_server,0755);
		error_reporting(E_ALL);
		
		return true;
	}

	/*
	* Supprime le fichier mentionné
	* si le(s) dossier(s) contenant(s) est/sont vide(s), il(s) sera/seront supprimé(s) aussi
	* filename peut être du type "dossierparent/dossierparentparent/image.gif" avec autant de dossiers parents désirés
	*/
	function smiley_delete($filename) {
		if (file_exists($this->smiley_dir.$filename)) {
			unlink($this->smiley_dir.$filename)
				or trigger_error(lang('pa_sm_create_error_28'),E_USER_ERROR);
		}
		//tente de supprimer le dossier
		rmdirs(dirname($this->smiley_dir.$filename),$this->smiley_dir.$this->smiley_librariesdir);
	}
}
?>