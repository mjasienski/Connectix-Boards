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

var cb_stylecookiename = "cb_style";
var cb_hiddenfidscookiename = "cb_hidfids";
var Mozilla = (navigator.userAgent.toLowerCase().indexOf('gecko')!=-1) ? true : false;

/* LECTURE ET ECRITURE DE COOKIES */
function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	} else expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}
function readCookie(name,tag) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0)
			return c.substring(nameEQ.length,c.length);
	}
	return null;
}

/* AJOUT DE FONCTIONS DE SUPPRESSION ET DE VERIFICATION D'APPARTENANCE A LA CLASSE ARRAY */
Array.prototype.remove = function(s){
	for (i=0;i<this.length;i++){
		if(s==this[i]) this.splice(i, 1);
	}
}
Array.prototype.inArray = function(s){
	for (i=0;i<this.length;i++){
		if (s==this[i]) return true;
	}
	return false;
}

/* POUR RECUPERER TOUS LES ELEMENTS D'UNE MEME CLASSE CSS */
document.getElementsByClassName = function(cl) {
	var retnode = [];
	var elem = this.getElementsByTagName('*');
	for (var i = 0; i < elem.length; i++) {
		if (elem[i].className == cl) retnode.push(elem[i]);
	}
	return retnode;
};
document.getElementsByClassFormat = function(cl) {
	var retnode = [];
	var elem = this.getElementsByTagName('*');
	var match = new RegExp(cl,'g');
	for (var i = 0; i < elem.length; i++) {
		if (match.test(elem[i].className)) retnode.push(elem[i]);
	}
	return retnode;
};

/* POUR INSERER UN NOEUD APRES UN AUTRE NOEUD */
function insertAfter (node, refNode) {
	if (refNode.parentNode.lastChild == refNode)
		refNode.parentNode.appendChild(node);
	else 
		refNode.parentNode.insertBefore(node,refNode.nextSibling);
}

/* FONCTION POUR MONTRER OU CACHER DES ELEMENTS (par id) */
function hideAndShow(field) {
	var elem = document.getElementById(field);
	elem.style.display = (elem.style.display == 'none') ? '' : 'none';
}

/* FONCTION POUR MONTRER OU CACHER DES ELEMENTS (par class) */
function hideAndShowC(field) {
	var class_elems = document.getElementsByClassName(field);
	for (var i=0; i<class_elems.length; i++)
		class_elems[i].style.display = (class_elems[i].style.display == 'none') ? '' : 'none';
}

/* FONCTIONS POUR AFFICHER OU CACHER DES FORUMS (avec mémoire par cookies) */
var hiddenfids = (hiddencookie = readCookie(cb_hiddenfidscookiename)) ? hiddencookie.split(',') : new Array();
function hideAndShowF(fid) {
	hideAndShow('forum'+fid+'_tb');
	hideAndShow('forum'+fid+'_th');
	hideAndShow('forum'+fid+'_tf');
	
	var state = document.getElementById('forum'+fid+'_tb').style.display;
	if (state == 'none') hiddenfids.push(fid);
	else hiddenfids.remove(fid);
}
function checkF (fid) {
	if (hiddenfids.inArray(fid)) {
		hideAndShow('forum'+fid+'_tb');
		hideAndShow('forum'+fid+'_th');
		hideAndShow('forum'+fid+'_tf');
	}
}

/* FONCTION DE CONFIRMATION AUTOMATIQUE DE FORMULAIRE */
function fast_list (select_goal) {
	if (select_goal == 'showtopicgroup') {
		box = document.getElementById('showtopicgroup');
		location.href = document.getElementById('quick_redirect_form').action+'?showtopicgroup='+box.options[box.selectedIndex].value;
	} else if (select_goal == 'mod_disp') {
		document.getElementById('tg_displace').click();
	} else if (select_goal == 'skin') {
		document.getElementById('skin_select_submit').click();
	}
}

/* FONCTION QUI SELECTIONNE TOUT LE CONTENU D'UN CHAMP */
function selectAll (field) {
	field.focus();
	if (Mozilla) {
		field.setSelectionRange(0,field.value.length);
	} else if (document.selection) {
		var range = document.selection.createRange();
		range.moveStart('character',-range.offsetLeft);
		range.moveEnd('character',field.value.length);
		range.select();
	}
	field.focus();
}

/* FONCTION ADMIN POUR LA SELECTION DES DROITS DES GROUPES */
function authfunc ( type , tgid ) {
	if (document.getElementById(type+'_'+tgid).checked) {
		if (type == 'see' || type=='reply') document.getElementById('create_'+tgid).checked = true;
		if (type == 'see') document.getElementById('reply_'+tgid).checked = true;
	} else {
		if (type == 'create' || type=='reply') document.getElementById('see_'+tgid).checked = false;
		if (type == 'create') document.getElementById('reply_'+tgid).checked = false;
	}
}

/* FONCTIONS ADMIN POUR LA GESTION DES DROITS DE MODERATION */
function groupCl (grid,tgid) {
	var newstate = (document.getElementById("gr" + tgid + "-" + grid).checked) ? true : false;
	var checkbox = document.getElementsByClassName("u" + tgid + "-" + grid);
	for (var i=0; i<checkbox.length; i++) checkbox[i].checked = newstate;
	setColors(grid,tgid,newstate);
}
function userCl (uid,grid,tgid) {
	if (!document.getElementById("u" + tgid + "-" + grid + "-" + uid).checked) {
		document.getElementById("gr" + tgid + "-" + grid).checked = false;
		setColors(grid,tgid,false);
	}
}
function setColors (grid,tgid,checked) {
	document.getElementById("cr" + tgid + "-" + grid).className = (checked)?'modgroupch':'modgroup';
	var checkbox = document.getElementsByClassName("u" + tgid + "-" + grid);
	for (var i=0; i<checkbox.length; i++) {
		document.getElementById(checkbox[i].id.replace('u','c')).className = (checked)?'modusergr':'';
	}
}

/* INVERSE LA SELECTION POUR UN TABLEAU DE CHECKBOX */
function invertselection (field) {
	var checkbox = document.getElementsByName(field);
	for (var i=0; i<checkbox.length;i++) {
		if (checkbox[i].type == 'checkbox') { 
			checkbox[i].checked = (checkbox[i].checked) ? false : true;
		}
	}
}

/* MESSAGE D'AVERTISSEMENT PERSONNALISE */
var alertOpened = false;
function cbAlert (fmessage,finput) {
	if (alertOpened) removeAlert();
	
	var cbalert = document.createElement('div');
	cbalert.id = 'cbalert';
	cbalert.innerHTML = 
		'<form onsubmit="return false;">'+
		'<p id="alert_message">'+fmessage+'</p>'+
		(finput?'<p id="alert_input"><input type="text" id="alert_field" name="alert_field" value="'+finput+'" onkeyup="if(event.keyCode==13) removeAlert();" /></p>':'')+
		'<p id="alert_submit">'+
			'<input type="button" name="prompt_ok" id="prompt_ok" value="'+lang['ok']+'" onclick="removeAlert();" onkeyup="if(event.keyCode==13) removeAlert();" />'+
		'</p>'+
		'</form>';
	
	document.getElementsByTagName('body')[0].appendChild(cbalert);
	
	cbalert.focus();
	document.getElementById('prompt_ok').focus();
	if (finput)
		selectAll(document.getElementById('alert_field'));
	
	alertOpened = true;
}
function removeAlert() {
	var cbalert = document.getElementById('cbalert');
	cbalert.style.display = "none";
	cbalert.innerHTML = "";
	cbalert.id = null;
	cbalert = null;
	alertOpened = false;
}

/* GESTION DU STYLESWITCHER */
function setActiveStyleSheet(title) {
	var i, a, main;
	for (i=0; (a = document.getElementsByTagName("link")[i]); i++) {
		if (a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title"))
			a.disabled = (a.getAttribute("title") == title) ? false : true;
	}
}

function getActiveStyleSheet() {
	var i, a;
	for (i=0; (a = document.getElementsByTagName("link")[i]); i++) {
		if (a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title") && !a.disabled) 
			return a.getAttribute("title");
	}
	return null;
}

function getPreferredStyleSheet() {
	var i, a;
	for (i=0; (a = document.getElementsByTagName("link")[i]); i++) {
		if (a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("rel").indexOf("alt") == -1 && a.getAttribute("title")) 
			return a.getAttribute("title");
	}
	return null;
}

/* FONCTIONS POUR LE FORMULAIRE DE CONNEXION RAPIDE */
var fc_username_clicked = false;
var fc_password_clicked = false;
function fc_username () {
	if (!fc_username_clicked) {
		document.getElementById("fcf_login").value = "";
		fc_username_clicked = true;
	}
}
function fc_password () {
	if (!fc_password_clicked) {
		document.getElementById("fcf_password").value = "";
		fc_password_clicked = true;
	}
}

/* NAVIGATION DANS LA PAGE, AU CLAVIER */
var navigation = [];
var navigation_at = 0;
var navigation_prev = null;
var navigation_class = null;
function navigateEventHandler(e) {
	e = e || window.event;
	
	var tagname = '';
	if (e.target) tagname = e.target.tagName;
	if (e.srcElement) tagname = e.srcElement.tagName;
	
	if (tagname.toLowerCase() != 'html' && tagname.toLowerCase() != 'body') return;
	if (e.altKey || e.ctrlKey) return;
	
	if (e.keyCode) code = e.keyCode;
	else if (e.which) code = e.which;
	var c = String.fromCharCode(code).toLowerCase();
	
	if (c == 'e') 
		navigate('up');
	else if (c == 'd')
		navigate('down');
	else if (c == 'f')
		navigate('forward');
	else if (c == 's')
		navigate('back');
	else if (c == 'x')
		navigate('bottom');
	else if (c == 'z')
		navigate('top');
	else if (c == 'c')
		navigate('nextpage');
	else if (c == 'w')
		navigate('prevpage');
}
function navigate(cmd) {
	if (cmd == 'back') {
		var backlink = document.getElementsByClassName('backlink');
		if (backlink.length > 0) window.location = backlink[0].href;
	} else if (cmd == 'nextpage') {
		var next = document.getElementsByClassFormat("nav_nextpage");
		if (next.length > 0) window.location = next[0].href;
	} else if (cmd == 'prevpage') {
		var prev = document.getElementsByClassFormat("nav_prevpage");
		if (prev.length > 0) window.location = prev[0].href;
	} else if (navigation.length == 0 && (cmd == 'up' || cmd == 'down' || cmd == 'back' || cmd == 'bottom' || cmd == 'top')) {
		navigation = document.getElementsByClassFormat("navitem");
		if (navigation.length > 0) { 
			if (cmd == 'bottom') navigation_at = navigation.length-1;
			performNavigate();
		} else navigation = [];
	} else if (cmd == 'up' && navigation_at > 0) {
		navigation_at--;
		performNavigate();
	} else if (cmd == 'down' && navigation_at < navigation.length-1) {
		navigation_at++;
		performNavigate();
	} else if (cmd == 'forward' && navigation_prev != null && document.getElementById("mainlink_"+navigation_prev.id)) {
		window.location = document.getElementById("mainlink_"+navigation_prev.id).href;
	} else if (cmd == 'bottom') {
		navigation_at = navigation.length-1;
		performNavigate();
	} else if (cmd == 'top') {
		navigation_at = 0;
		performNavigate();
	}
}
function performNavigate() {
	var item = navigation[navigation_at];
	if (navigation_prev != null) {
		navigation_prev.className = navigation_class;
	}
	navigation_class = item.className;
	navigation_prev = item;
	item.className = "navigation_selected";
	scroll(item,true);
}
document.onkeyup = navigateEventHandler;

/* CHOSES A FAIRE AU CHARGEMENT DE LA PAGE */
window.onload = function (e) {
	var stylecookie = readCookie(cb_stylecookiename);
	var size = document.getElementsByTagName("link").length;
	var title = getPreferredStyleSheet();
	for (j=0; j<size ; j++) {
		if (document.getElementsByTagName("link")[j].getAttribute("title") == stylecookie)
			title = stylecookie;
	}
	initAnchors();
	if (Mozilla) setActiveStyleSheet(title);
}

/* CHOSES A FAIRE AU DECHARGEMENT DE LA PAGE */
window.onunload = function (e) {
	createCookie(cb_stylecookiename, getActiveStyleSheet(), 365);
	createCookie(cb_hiddenfidscookiename, hiddenfids.join(','), 365);
}

/* FONCTIONS D'ENCODAGE */
function utf8_encode ( argString ) {
	// Encodes an ISO-8859-1 string to UTF-8 
	//
	// version: 909.322
	// discuss at: http://phpjs.org/functions/utf8_encode
	// +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +   improved by: sowberry
	// +	tweaked by: Jack
	// +   bugfixed by: Onno Marsman
	// +   improved by: Yves Sucaet
	// +   bugfixed by: Onno Marsman
	// +   bugfixed by: Ulrich
	// *	 example 1: utf8_encode('Kevin van Zonneveld');
	// *	 returns 1: 'Kevin van Zonneveld'
	var string = (argString+''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
 
	var utftext = "";
	var start, end;
	var stringl = 0;
 
	start = end = 0;
	stringl = string.length;
	for (var n = 0; n < stringl; n++) {
		var c1 = string.charCodeAt(n);
		var enc = null;
 
		if (c1 < 128) {
			end++;
		} else if (c1 > 127 && c1 < 2048) {
			enc = String.fromCharCode((c1 >> 6) | 192) + String.fromCharCode((c1 & 63) | 128);
		} else {
			enc = String.fromCharCode((c1 >> 12) | 224) + String.fromCharCode(((c1 >> 6) & 63) | 128) + String.fromCharCode((c1 & 63) | 128);
		}
		if (enc !== null) {
			if (end > start) {
				utftext += string.substring(start, end);
			}
			utftext += enc;
			start = end = n+1;
		}
	}
 
	if (end > start) {
		utftext += string.substring(start, string.length);
	}
 
	return utftext;
}