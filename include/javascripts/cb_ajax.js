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

/* Objet XmlHttpRequest. */
var ajax;
var ajaxLoaded = true;
if (window.XMLHttpRequest) 
	ajax = new XMLHttpRequest();
else if (window.ActiveXObject) 
	ajax = new ActiveXObject("Microsoft.XMLHTTP");
else
	ajaxLoaded = false;

/*  UTILITE GENERALE  */

/* Message invitant à la patience */
function startWaiting() {
	if (document.getElementById('cbwait'))
		return false;
	
	var cbwait = document.createElement('div');
	cbwait.id = 'cbwait';
	cbwait.innerHTML = '<p id="wait_message"><span id="wait_inside"><span>'+lang['gen_wait']+'</span></span></p>';
	
	document.getElementsByTagName('body')[0].appendChild(cbwait);
	
	cbwait.focus();
}
function stopWaiting() {
	var cbwait = document.getElementById('cbwait');
	cbwait.style.display = "none";
	cbwait.innerHTML = "";
	cbwait.id = null;
	cbwait = null;
}


/*  EDITION RAPIDE DE MESSAGES  */

var orig_msgs = {}; // Contient les messages originaux
var edit_msgs = {}; // Contient les formulaires de modification (pour ne pas demander plusieurs fois la même chose)

/* Fonction appelée lors du clic sur le bouton de modification du message msg_id */
function quickEdit(msg_id, link) {
	if (!ajaxLoaded)
		return false;
	
	link.removeAttribute('href');
	
	if (document.getElementById('edit_'+msg_id)) {
		// Clic sur 'modifier' alors que le formulaire est actif, cela annule l'opération
		quickEdit_cancelform(msg_id);
	} else {
		// Clic sur 'modifier', demandant affichage du formulaire
		if (edit_msgs[msg_id]) {
			// Ce formulaire a déja été demandé, on le réaffiche simplement
			quickEdit_showform(msg_id);
		} else {
			// Première fois que le formulaire est demandé, on le demande au serveur
			ajax.open('POST','ajax.php',true);
			ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			
			ajax.onreadystatechange = function() {
				if (ajax.readyState == 4) {
					if (ajax.responseText.charAt(0) == '0') {
						// Erreur de traitement
						cbAlert(ajax.responseText.substr(2));
						stopWaiting();
					} else {
						orig_msgs[msg_id] = document.getElementById('message_'+msg_id).innerHTML;
						edit_msgs[msg_id] = ajax.responseText;
						quickEdit_showform(msg_id);
						stopWaiting();
					}
				}
			}
			
			ajax.send('request=msg_unclean&value='+msg_id);
			
			startWaiting();
		}
	}
}

/* Affiche simplement le formulaire d'édition du message msg_id */
function quickEdit_showform (msg_id) {
	var node = document.createElement('div');
	node.id = 'edit_'+msg_id;
	node.className = 'quickeditform';
	node.innerHTML = edit_msgs[msg_id];
	
	var container = document.getElementById('message_'+msg_id);
	container.parentNode.replaceChild(node,container);
}

/* Confirmation du formulaire */
function quickEdit_sendform (msg_id) {
	if (!ajaxLoaded)
		return false;
	
	ajax.open('POST','ajax.php',true); 
	ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	
	ajax.onreadystatechange = function() {
		if (ajax.readyState == 4) {
			stopWaiting();
			
			if (ajax.responseText.charAt(0) == '0') {
				// Erreur de traitement
				cbAlert(ajax.responseText.substr(2));
			} else {
				// Le traitement du message a fonctionnÃ©, on affiche le nouveau message Ã  la place de l'ancien
				orig_msgs[msg_id] = ajax.responseText.substr(2);
				edit_msgs[msg_id] = null;
				
				quickEdit_cancelform(msg_id);
				
				if (document.getElementById('msg_edit_'+msg_id))
					document.getElementById('msg_edit_'+msg_id).innerHTML = lang['msg_edited'];
				else {
					var node = document.createElement('div');
					node.id = 'msg_edit_'+msg_id;
					node.className = 'messageedit';
					node.innerHTML = lang['msg_edited'];
					
					insertAfter(node,document.getElementById('message_'+msg_id));
				}
			}
		}
	}
	
	ajax.send('request=msg_edit&value='+msg_id+'&message='+escape(utf8_encode(document.getElementById('message_'+msg_id).value)));
	
	startWaiting();
}

/* Annulation du formulaire */
function quickEdit_cancelform (msg_id) {
	if (document.getElementById('edit_'+msg_id)) {
		var node = document.createElement('div');
		node.id = 'message_'+msg_id;
		node.className = 'messagecontent';
		node.innerHTML = orig_msgs[msg_id];
		
		var container = document.getElementById('edit_'+msg_id);
		container.parentNode.replaceChild(node,container);
	}
}

/* Demande de prévisualisation */
function quickEdit_previs (msg_id) {
	document.getElementById('message_'+msg_id).name = 'message';
	document.getElementById('form_'+msg_id).onsubmit = 'return true;';
	document.getElementById('form_'+msg_id).submit();
}

/*  FORMULAIRE D'INSCRIPTION  */

/* Simple vérification d'un champ */
function regFormCheck (field,value) {
	if (!ajaxLoaded)
		return false;
	
	if (value.length == 0)
		return false;
	
	ajax.open('POST','ajax.php',true);
	ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	
	ajax.onreadystatechange = function() {
		if (ajax.readyState == 4) {
			var node = document.createElement('span');
			node.className = 'reg_check';
			node.innerHTML = '<span class="reg_'+((ajax.responseText.charAt(0) == '1')?'valid':'error')+'"><span>'+ajax.responseText.substr(2)+'</span></span>';
			
			var container = document.getElementById(field);
			if (container.lastChild.className == 'reg_check')
				container.replaceChild(node,container.lastChild);
			else 
				container.appendChild(node);
		}
	}
	
	ajax.send('request='+escape(utf8_encode(field))+'&value='+escape(utf8_encode(value)));
}
/* Pour le mot de passe, c'est un peu différent, car il y a deux valeurs à checker, et deux champs de formulaire... */
function regFormPass () {
	if (!ajaxLoaded)
		return false;
	
	var pass1 = document.getElementById('field_pass1').value;
	var pass2 = document.getElementById('field_pass2').value;
	
	if (pass1.length == 0 || pass2.length == 0)
		return false;
	
	ajax.open('POST','ajax.php',true);
	ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	
	ajax.onreadystatechange = function() {
		if (ajax.readyState == 4) {
			var node = document.createElement('span');
			node.className = 'reg_check';
			node.innerHTML = '<span class="reg_'+((ajax.responseText.charAt(0) == '1')?'valid':'error')+'"><span>'+ajax.responseText.substr(2)+'</span></span>';
			
			nodeClone = node.cloneNode(true);
			
			var container = document.getElementById('reg_password');
			if (container.lastChild.className == 'reg_check')
				container.replaceChild(node,container.lastChild);
			else 
				container.appendChild(node);
			
			var container = document.getElementById('reg_password_confirm');
			if (container.lastChild.className == 'reg_check')
				container.replaceChild(nodeClone,container.lastChild);
			else 
				container.appendChild(nodeClone);
		}
	}
	
	ajax.send('request=reg_password&pass1='+escape(utf8_encode(pass1))+'&pass2='+escape(utf8_encode(pass2)));
}
/* Chargement d'un nouveau code captcha */
function newCaptcha () {
	if (!ajaxLoaded)
		return false;
	
	ajax.open('POST','ajax.php',true);
	ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	
	ajax.onreadystatechange = function() {
		if (ajax.readyState == 4) {
			stopWaiting();
			
			if (ajax.responseText.charAt(0) == '1') {
				var node = document.createElement('span');
				node.id = 'captcha_img';
				node.innerHTML = ajax.responseText.substr(2);
				
				var container = document.getElementById('captcha_code');
				var old = document.getElementById('captcha_img')
				if (old)
					container.replaceChild(node,old);
				
				document.getElementById('captcha_input').value = '';
			}
		}
	}
	
	ajax.send('request=new_captcha');
	
	startWaiting();
}

/* ACTIONS DANS TOPICS */

/* Mettre en favoris */
function quickBookmark (topic_id, type, link) {
	if (!ajaxLoaded)
		return false;
	
	// Determine bookmark
	var value = 0;
	if (link.className == "tt_" + type) {
		value = 1;
	}
	
	// Remove href attribute
	link.removeAttribute('href');
	
	ajax.open('POST','ajax.php',true);
	ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	
	ajax.onreadystatechange = function() {
		if (ajax.readyState == 4) {
			if (ajax.responseText.charAt(0) == '0') {
				// Erreur de traitement
				cbAlert(ajax.responseText.substr(2));
			} else {
				// Adapte les liens
				var links = document.getElementsByClassName(link.className);
				for (var i = 0; i < links.length; i++) {
					if (value)
						links[i].className = "tt_no" + type;
					else
						links[i].className = "tt_" + type;
					
					links[i].innerHTML = lang[links[i].className];
				}
				
				// Message
				cbAlert(ajax.responseText.substr(2));
			}
			
			stopWaiting();
		}
	}
	ajax.send('request=t_'+type+'&'+type+'='+value+'&topicid='+topic_id);
	startWaiting();
}

/* FORMULAIRE DE SMILIES EN POP-UP */

var smilieswindow_open = false;
function SmiliesExtendedForm (textarea) {
	if (!ajaxLoaded)
		return false;
	
	if (smilieswindow_open) {
		CloseSmiliesWindow();
		return false;
	}
	
	ajax.open('POST','ajax.php',true);
	ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	
	ajax.onreadystatechange = function() {
		if (ajax.readyState == 4) {
			var node = document.createElement('span');
			node.id = 'smilieswindow';
			node.innerHTML = '<span class="smilieswindow_menu"><span class="smilies"><span class="smilies_field">'+ajax.responseText.substr(2)+'</span></span><span class="cancel"><input type="button" value="'+lang['close']+'" onclick="CloseSmiliesWindow();" /></span></span>';
			
			var container = document.getElementById('main');
			container.appendChild(node);
			
			smilieswindow_open = true;
				
			stopWaiting();
		}
	}
	
	ajax.send('request=extendedsmilies&taid='+textarea);
	startWaiting();
}
function CloseSmiliesWindow () {
	var smwindow = document.getElementById('smilieswindow');
	smwindow.style.display = "none";
	smwindow.innerHTML = "";
	smwindow.id = null;
	smwindow = null;
	smilieswindow_open = false;
}