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

//// Gestion du BBcode et des smileys ////

// Ajoute les smileys dans $message
function addSmileys($message,$opt = 0) {
	require_once(CB_CACHE_SMILEYS);
	$message = ' '.$message.' ';
	$message = str_replace("\n"," \n ",$message);
	foreach ($GLOBALS['cb_smileys'] as $smiley)
		$message=str_replace(' '.$smiley['symbol'].' ',' <img src="'.(($opt & STR_PUTLONGURL)?'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']):'').'smileys/'.$smiley['filename'].'" alt="'.$smiley['symbol'].'" class="smiley" /> ',$message);
	$message = utf8_substr($message,1);
	$message = utf8_substr($message,0,-1);
	$message = str_replace(" \n ","\n",$message);
	return $message;
}
// Retire les smileys de $message
function removeSmileys($message) {
	require_once(CB_CACHE_SMILEYS);
	foreach ($GLOBALS['cb_smileys'] as $smiley) 
		$message = str_replace(array(
				'<img src="smileys/'.$smiley['filename'].'" alt="'.$smiley['symbol'].'" class="smiley" />',
				'<img src="http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'smileys/'.$smiley['filename'].'" alt="'.$smiley['symbol'].'" class="smiley" />',
			), $smiley['symbol'], $message);
	return $message;
}
// Met des liens automatiques dans les messages
function autoLinks ($text) {
	$pattern[]='`((https?|ftp)://[^<\t\n\r ]+)`si';
	$replace[]='<a href="$1">$1</a>';
	
	$pattern[]='`([^/])(www\.[^<\t\n\r ]+)`si';
	$replace[]='$1<a href="http://$2">$2</a>';

	return preg_replace($pattern,$replace,$text);
}
// Retire les liens automatiques
function autoLinksReverse ($text) {
	return preg_replace('#\<a href="(.+?)">(.+?)</a>#si','$2',$text);
}

/* Tout ce qui est bbcode... */
// Structure de données: queue
class queue {
	var $tab;
	var $position;
	var $left;

	function queue() {
		$this->tab=array();
		$this->position=0;
		$this->left=0;
	}
	function push($object) {
		$this->tab[$this->position++]=$object;
	}
	function pop() {
		$r = $this->tab[$this->left];
		unset($this->tab[$this->left]);
		$this->left++;
		return $r;
	}
	function isEmpty() {
		return $this->position <= $this->left;
	}
	function size() {
		return $this->position-$this->left;
	}
}
// Structure de données: pile
class stack {
	var $arr;
	var $pos;

	function stack() {
		$this->arr = array();
		$this->pos = 0;
	}
	function push($object) {
		$this->arr[$this->pos++]=$object;
	}
	function pop() {
		$r = $this->arr[$this->pos-1];
		unset($this->arr[$this->pos-1]);
		$this->pos--;
		return $r;
	}
	function head() {
		if ($this->isEmpty()) return null;
		else return $this->arr[$this->pos-1];
	}
	function isEmpty() {
		return $this->pos == 0;
	}
	function size() {
		return $this->pos;
	}
}
// Un objet de bbcode
class bbitem {
	var $type;
	var $name;
	var $contents;
	var $properties=array();
	var $tag_arg;
	var $functions=array();
	var $long_url;

	function bbitem($bbtype,$bbname,$bbcontents,$bbtagarg,$opt = 0) {
		$this->type = $bbtype;
		$this->name = $bbname.((utf8_strlen($bbtagarg) > 0)?'=':'');
		$this->contents = $bbcontents;
		$this->tag_arg = $bbtagarg;
		$this->long_url = ($opt & STR_PUTLONGURL);
		if ($bbtype=='tag') {
			$h=fopen(CB_PATH.'bbcode/'.$this->name.'.bb','r');
			$numline=1;
			while(!feof($h)) {
				$line=str_replace(array("\n","\r"),'',fgets($h));
				switch($numline) {
					case 1:
						$this->properties['mask']=$line;
						break;
					case 2:
						$this->properties['noparse'] = (utf8_strpos($line,'-noparse-')!==false);
						$this->properties['addsize'] = (utf8_strpos($line,'-addsize-')!==false);
						$this->properties['noautolink'] = (utf8_strpos($line,'-noautolink-')!==false);
						break;
					case 3:
						$this->functions['contents']=create_function('$c',$line.'return $c;');
						break;
					case 4:
						$this->functions['args']=create_function('$a',$line.'return $a;');
						break;
				}
				$numline++;
			}
			fclose($h);
			if (!isset($this->properties['noparse'])) $this->properties['noparse']=false;
			if (!isset($this->properties['addsize'])) $this->properties['addsize']=false;
			if (!isset($this->properties['noautolink'])) $this->properties['noautolink']=false;
		} else $this->properties=null;
	}
	function parse () {
		if ($this->type == 'text')
			if (!$this->properties['noautolink'])
				return addSmileys(autoLinks($this->contents),$this->long_url);
			else
				return addSmileys($this->contents,$this->long_url);
		else {
			$contents='';
			$size='';
			
			while (!$this->contents->isEmpty()) {
				$bbit=$this->contents->pop();
				$bbit->properties['noautolink'] = ($bbit->properties['noautolink'] || $this->properties['noautolink']);
				if ($this->properties['noparse']) $contents.=$bbit->getText();
				else $contents.=$bbit->parse();
			}

			// On exécute les fonctions contenues dans le fichier .bb
			if (isset($this->functions['contents']) && !empty($this->functions['contents']))
				$contents=$this->functions['contents']($contents);
			if (isset($this->functions['args']) && !empty($this->functions['args']))
				$this->tag_arg = $this->functions['args']($this->tag_arg);

			// On s'occupe de la taille du fichier considéré
			if ($this->properties['addsize'])
				$size = ($s = @getimagesize($contents)) ? $s[3] : 'width="100" height="100"';

			return '<!--'.$this->name.'-->'.
				str_replace(	array('{--CONTENTS--}','{--ARGS--}','{--SIZE--}'),
								array($contents,$this->tag_arg,$size),
								$this->properties['mask']).
					'<!--/'.$this->name.'-->';
		}
	}
	function getText() {
		if ($this->type == 'text') return $this->contents;
		else {
			$newtext='['.$this->name.$this->tag_arg.']';
			while (!$this->contents->isEmpty()) {
				$item = $this->contents->pop();
				$newtext.=$item->getText();
			}
			$newtext.='[/'.((utf8_substr($this->name,-1)=='=')?utf8_substr($this->name,0,-1):$this->name).']';
			return $newtext;
		}
	}
}

// Structure de données intermédiaire
class varitem {
	var $tag;
	var $arg;
	var $text;

	function varitem($itag,$iarg,$itext) {
		$this->tag = $itag;
		$this->arg = $iarg;
		$this->text = $itext;
	}
}

// Renvoie la chaine parsée
function subparse($text,$permitted,$opt = 0) {
	$tags = array_unique(array_map('_subparse',$permitted));
	
	$found = new stack;
	$textst = new stack;
	$items = new queue;

	while (utf8_strlen($text)>0) {
		$matches = array();
		if (preg_match('#^(.*)\[(/?)('.implode('|',$tags).')(=.*)?\](.*)$#isU',$text,$matches)) {
			// Terme du début
			if (utf8_strlen($matches[1]) > 0) {
				if ($found->isEmpty()) $items->push(new bbitem('text','text',$matches[1],'',$opt));
				else $textst->push(new varitem(null,null,new bbitem('text','text',$matches[1],'',$opt)));
			}

			// Tag
			if (utf8_strlen($matches[2]) > 0 && utf8_strlen($matches[4]) > 0) {
				// Tag non-valide
				if ($found->isEmpty()) $items->push(new bbitem('text','text','[/'.$matches[3].$matches[4].']','',$opt));
				else $textst->push(new varitem(null,null,new bbitem('text','text','[/'.$matches[3].$matches[4].']','',$opt)));
			} else {
				if (utf8_strlen($matches[2]) > 0 || in_array(utf8_strtolower($matches[3]).((utf8_strlen($matches[4]) > 0)?'=':''),$permitted)) {
					$matches[3] = utf8_strtolower($matches[3]);
					if (utf8_strlen($matches[2]) == 0) {
						// Tag ouvrant
						$found->push($matches[3]);
						$textst->push(new varitem($matches[3],utf8_substr($matches[4],1),null));
					} else {
						// Tag fermant
						if ($found->head() == $matches[3]) {
							$found->pop();
							$contents = new stack;
							$elem = null;
							$break = false;
							while (!$textst->isEmpty() && !$break) {
								$elem = $textst->pop();
								if (utf8_strlen($elem->tag) > 0)
									$break=true;
								if (isset($elem->text->contents) && (is_object($elem->text->contents) || utf8_strlen($elem->text->contents) > 0))
									$contents->push($elem->text);
							}

							if ($found->isEmpty()) $items->push(new bbitem('tag',$elem->tag,$contents,$elem->arg,$opt));
							else $textst->push(new varitem(null,null,new bbitem('tag',$elem->tag,$contents,$elem->arg,$opt)));
						} else {
							// Mauvaise imbrication !!!
							// On teste si cette balise ferme une autre balise que la dernière ouverte
							if (in_array($matches[3],$found->arr)) {
								// Tag précédent à fermer
								$contents = new stack;
								$elem = null;
								$break = false;

								while (!$textst->isEmpty() && !$break) {
									$elem = $textst->pop();
									if ($elem->tag == $matches[3])
										$break=true;
									else {
										if (utf8_strlen($elem->tag) > 0)
											$contents->push(new bbitem('text','text','['.$elem->tag.((!empty($elem->arg))?'='.$elem->arg:'').']','',$opt));
										if (isset($elem->text->contents) && (is_object($elem->text->contents) || utf8_strlen($elem->text->contents) > 0))
											$contents->push($elem->text);
									}
								}

								while (!$found->isEmpty() && $found->head() != $matches[3])
									$found->pop();
								if (!$found->isEmpty()) $found->pop();

								if ($found->isEmpty()) $items->push(new bbitem('tag',$elem->tag,$contents,$elem->arg,$opt));
								else $textst->push(new varitem(null,null,new bbitem('tag',$elem->tag,$contents,$elem->arg,$opt)));
							} else {
								// Tag fermant seul (qui n'a pas été ouvert)
								if ($found->isEmpty()) $items->push(new bbitem('text','text','[/'.$matches[3].']','',$opt));
								else $textst->push(new varitem(null,null,new bbitem('text','text','[/'.$matches[3].']','',$opt)));
							}
						}
					}
				} else {
					// Tag pas permis
					if ($found->isEmpty()) $items->push(new bbitem('text','text','['.$matches[2].$matches[3].$matches[4].']','',$opt));
					else $textst->push(new varitem(null,null,new bbitem('text','text','['.$matches[2].$matches[3].$matches[4].']','',$opt)));
				}
			}

			// Terme de la fin
			$text = $matches[5];
		} else {
			$textst->push(new varitem(null,null,new bbitem('text','text',$text,'',$opt)));
			$text = '';
		}
	}
	// On vide ce qu'il reste s'il le faut
	$contents = new stack;
	while (!$textst->isEmpty()) {
		$elem = $textst->pop();
		if (utf8_strlen($elem->tag) > 0)
			$contents->push(new bbitem('text','text','['.$elem->tag.((!empty($elem->arg))?'='.$elem->arg:'').']','',$opt));
		if (isset($elem->text->contents) && (is_object($elem->text->contents) || utf8_strlen($elem->text->contents) > 0))
			$contents->push($elem->text);
	}
	while (!$contents->isEmpty())
		$items->push($contents->pop());

	return $items;
}
// Parseur de BBCode
function parse_bb ($text,$opt = 0) {
	$codes=array();
	$sign_forbidden = (($opt & STR_SIGNATURE) ? explode('|',$GLOBALS['cb_cfg']->config['bb_sign_forbidden']) : array() );

	$h = opendir(CB_PATH.'bbcode/');
	while ($file = readdir ($h)) {
		if ($file != '.' && $file != '..' && $file != 'index.html' && utf8_substr($file,-3) == '.bb' && !in_array(utf8_substr($file,0,-3),$sign_forbidden))
			$codes[]=utf8_substr($file,0,-3);
	}
	closedir($h);

	$items=subparse($text,$codes,$opt);

	$newtext='';
	$max=$items->size();
	for ($i = 0 ; $i < $max; $i++) {
		$bbit=$items->pop();
		$newtext.=$bbit->parse();
	}

	// Listes à puces
	$newtext = str_replace('[CB_BBCODE_LIST_STAR]','[*]',$newtext);

	return $newtext;
}
// Pour 'déparser' les messages
function unparse_bb ($text='') {
	$pattern=array();
	$replace=array();
	$pattern_args=array();
	$replace_args=array();

	$h = opendir(CB_PATH.'bbcode/');
	while ($file = readdir ($h)) {
		if ($file != '.' && $file != '..' && utf8_substr($file,-3) == '.bb') {
			$tag=utf8_substr($file,0,-3);

			$hf=fopen(CB_PATH.'bbcode/'.$file,'r');
			$mask=str_replace(array("\n","\r"),'',fgets($hf));
			fclose($hf);

			$metacharsorig=array('{--CONTENTS--}','{--ARGS--}','\\'  ,'^' ,'|' ,'?' ,'*' ,'+' ,'{' ,'}' ,'[' ,']' ,'(' ,')' ,'.' ,'-' ,'!' );//,'<' ,'>' ,'=' ,':' );
			$metacharsrepl=array('<CONTENTS>'	,'<ARGS>'	,'\\\\','\^','\|','\?','\*','\+','\{','\}','\[','\]','\(','\)','\.','\-','\!');// ,'\<' ,'\>' ,'\=' ,'\:' );
			$mask = str_replace($metacharsorig,$metacharsrepl,$mask);

			$pos_cont = utf8_strpos($mask,'<CONTENTS>');
			$pos_args = utf8_strpos($mask,'<ARGS>');
			$var_cont = '$1';
			$var_args = '';
			if ($pos_args !== false) {
				$var_cont = ($pos_cont < $pos_args) ? '$1' : '$'.(utf8_substr_count(utf8_substr($mask,0,$pos_cont),'<ARGS>')+1);
				$var_args = ($pos_cont > $pos_args) ? '$1' : '$'.(utf8_substr_count(utf8_substr($mask,0,$pos_args),'<CONTENTS>')+1);
			}
			
			$pattern[]='`<!--'.$tag.'-->'.str_replace(array('<CONTENTS>','<ARGS>'),'(.+?)',$mask).'<!--/'.$tag.'-->`si';
			$replace[]='['.((utf8_substr($tag,-1)=='=')?$tag.$var_args:$tag).']'.$var_cont.'[/'.((utf8_substr($tag,-1)=='=')?utf8_substr($tag,0,-1):$tag).']';
		}
	}
	closedir($h);

	for ($i=0;$i<count($pattern);$i++)
		$text = preg_replace($pattern[$i],$replace[$i],$text);

	// Liens automatiques
	$text = autoLinksReverse ($text);

	return $text;
}
// Fonction de callback pour faire le tri des tags
function _subparse($tag) {
	return (utf8_substr($tag,-1) == '=')?utf8_substr($tag,0,-1):$tag;
}
?>