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
* Contient toutes les fonctions relatives au formulaire interactif de rédaction de messages
* de Connectix Boards.
* 
* Ce fichier nécessite l'inclusion préalable de cb_base.js !!!!
*/

/* FONCTION D'AJOUT DE SMILEYS */
function emoticon(smiley,taid) {
	insert(taid," " + smiley + " ","");
}

/* FONCTIONS DE BBCODE */
// Insertion de n'importe quoi (smileys, bbcodes, ...) dans le textarea taid
function insert(taid,tag_begin,tag_end,tag_content) {
	checkRange(taid);
	if (promptOpened) removePrompt();
	
	var textarea = document.getElementById(taid);
	var scroll = textarea.scrollTop;
	if (tag_content == undefined) tag_content = "";
	var sel_remember = isTextSelected(taid);
	
	if (Mozilla) {
		var sel_newcursorpos = tag_begin.length + textarea.selectionEnd + (sel_remember?tag_end.length:0) + tag_content.length;
		var sel_start = textarea.selectionStart + (tag_content.length > 0?tag_begin.length:0);
		
		textarea.value = textarea.value.substring(0 , textarea.selectionStart) 
			+ tag_begin
			+ ( sel_remember ? textarea.value.substring(textarea.selectionStart ,textarea.selectionEnd) : tag_content )
			+ tag_end
			+ textarea.value.substring(textarea.selectionEnd , textarea.value.length);
		
		textarea.setSelectionRange( (sel_remember || tag_content.length > 0) ? sel_start : sel_newcursorpos,sel_newcursorpos);
	} else if (document.selection) {
		textarea.focus();
		
		var range = document.selection.createRange();
		var sel_length = (sel_remember?range.text.length:tag_content.length) + tag_begin.length + tag_end.length;
		
		range.text = tag_begin + (sel_remember?range.text:tag_content) + tag_end;
		if (sel_remember)
			range.moveStart('character',-sel_length);
		else if (tag_content.length > 0) {
			range.moveStart('character',-sel_length+tag_begin.length);
			range.moveEnd('character',-tag_end.length);
		} else
			range.moveEnd('character',-tag_end.length);
		
		range.select();
	} else {
		textarea.value += tag_begin + tag_content + tag_end;
	}
	
	textarea.focus();
	textarea.scrollTop = scroll;
}
// Détermine s'il y a du texte sélectionné dans le textarea taid
function isTextSelected (taid) {
	var textarea = document.getElementById(taid);
	if (Mozilla && (textarea.selectionEnd - textarea.selectionStart) > 0)
		return true;
	else if (Mozilla)
		return false;
	else if (rememberRange != null && rememberRange.text.length >0)
		return true;
	else if (rememberRange != null)
		return false;
	else if (document.selection.createRange().text.length > 0)
		return true;
	else
		return false;
}
// Gestion d'un tag normal
function tag (tag, taid) {
	insert(taid,"[" + tag + "]","[/" + tag + "]");
}
// Gestion du tag d'url
function tag_url (taid) {
	cbPrompt(taid, "tag_url_end", lang['form_inserturl'], "http://");
}
function tag_url_end (taid,input_url) {
	var content = "";
	if (!isTextSelected(taid))
		content = lang['form_link'];
	insert(taid,"[url="+input_url+"]","[/url]",content);
}
// Gestion du tag d'image
function tag_image (taid) {
	if (!isTextSelected(taid))
		cbPrompt(taid, "tag_image_end", lang['form_insertimg'], "http://");
	else
		insert(taid,"[img]","[/img]");
}
function tag_image_end (taid,input_url) {
	insert(taid,"[img]"+input_url,"[/img]");
}
// Gestion du tag d'e-mail
function tag_email (taid) {
	cbPrompt(taid, "tag_email_end", lang['form_insertmail'], "me@provider.com");
}
function tag_email_end (taid,email) {
	insert(taid,"[email="+email+"]","[/email]");
}
// Gestion des tag de sélection
function tag_select (select,taid,tag_name) {
	var tag_value =  select.options[select.options.selectedIndex].value;

	select.options[0].selected = true;
	
	if (tag_value && tag_value != 'none')
		insert(taid,"["+tag_name+"="+tag_value+"]","[/"+tag_name+"]");
}
// Gestion du tag liste
function tag_list (select,taid) {
	var tag_value =  select.options[select.options.selectedIndex].value;
	
	select.options[0].selected = true;
	
	if (tag_value && tag_value > 0 && tag_value < 9) {
		var tag_end = "";
		for (var i=1; i<tag_value; i++) {
			tag_end += "\n[*]";
		}
		insert(taid,"[list]\n[*]",tag_end+"\n[/list]");
	}
}

/* PROMPT PERSONNALISE POUR FORMULAIRE DE MESSAGES */
var promptOpened = false;
var rememberRange = null;
function cbPrompt (taid , endfunction , fmessage , value) {
	if (document.selection && rememberRange == null) {
		document.getElementById(taid).focus();
		rememberRange = document.selection.createRange();
	}
	
	if (promptOpened) removePrompt();
	
	var cbprompt = document.createElement('div');
	cbprompt.id = 'cbprompt';
	cbprompt.innerHTML = 
		'<form onsubmit="return false;">'+
		'<p id="prompt_message">'+fmessage+'</p>'+
		'<p id="prompt_form">'+
			'<input type="text" name="prompt_value" id="prompt_value" value="'+value+'" onkeyup="if(event.keyCode==13) managePrompt(true,\''+taid+'\',\''+endfunction+'\');" />'+
		'</p>'+
		'<p id="prompt_submit">'+
			'<input type="button" name="prompt_ok" id="prompt_ok" value="'+lang['confirm']+'" onclick="managePrompt(true,\''+taid+'\',\''+endfunction+'\');" />'+
			'<input type="button" name="prompt_cancel" id="prompt_cancel" value="'+lang['cancel']+'" onclick="managePrompt(false,\''+taid+'\',\''+endfunction+'\');" />'+
		'</p>'+
		'</form>';
	
	document.getElementsByTagName('body')[0].appendChild(cbprompt);
	
	cbprompt.focus();
	selectAll(document.getElementById('prompt_value'));
	promptOpened = true;
	document.getElementById(taid).disabled = true;
}
function managePrompt (ok,taid,endfunc) {
	if (ok) 
		eval(endfunc+'("'+taid+'","'+document.getElementById('prompt_value').value+'");');
	
	checkRange(taid);
	if (promptOpened) removePrompt();
}
function removePrompt() {
	var cbprompt = document.getElementById('cbprompt');
	cbprompt.style.display = "none";
	cbprompt.innerHTML = "";
	cbprompt.id = null;
	cbprompt = null;
	promptOpened = false;
}

// Fonction nécessaire pour se permettre quelques fantaisies avec IE
function checkRange(taid) {
	var textarea = document.getElementById(taid);
	textarea.disabled = false;
	if (rememberRange != null) {
		textarea.focus();
		rememberRange.select();
		rememberRange = null;
	}
}

/* RACCOURCIS CLAVIER */
function manage_shortcuts(e) {
	e = e || window.event;
	
	var taid = '';
	if (e.target) taid = e.target.id;
	if (e.srcElement) taid = e.srcElement.id;
	
	if (e.keyCode) code = e.keyCode;
	else if (e.which) code = e.which;
	var c = String.fromCharCode(code).toLowerCase();
	
	if(e.altKey && e.ctrlKey)  {
		if (c == 'q')	   tag('quote',taid);
		else if (c == 'b') tag('b',taid);
		else if (c == 'i') tag('i',taid);
		else if (c == 's') tag('s',taid);
		else if (c == 'u') tag('u',taid);
		else if (c == 'd') tag('code',taid);
		else if (c == 'p') tag('spoil',taid);
		else if (c == 'm') tag_email(taid);
		else if (c == 'g') tag_image(taid);
		else if (c == 'l') tag_url(taid);
		else if (c == 'f') tag('left',taid);
		else if (c == 'c') tag('center',taid);
		else if (c == 'r') tag('right',taid);
		else if (c == 'j') tag('justified',taid);
	}
}