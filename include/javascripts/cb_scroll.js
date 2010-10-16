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

/* Toutes les fonctions de gestion de la scrollbar */

var cb_scroll_speed = 5;		// Vitesse de déplacement
var cb_scroll_interval = null;	// Contiendra l'intervalle d'interpolation
var cb_scroll_check = null;		// Variable nécessaire entre les intervalles

/* 
* Effectue un scroll sur l'élément 'item'.
* Si middle (optionnel) vaut true, l'élément sera centré verticalement.
*/
function scroll(item, middle) {
	var yValue = yOffset(item);
	var screenHeight = window.innerHeight || document.documentElement.clientHeight;
	var docHeight = document.body.scrollHeight;
	
	if (middle) {
		if (item.offsetHeight <= screenHeight) {
			yValue -= screenHeight/2;
			yValue += item.offsetHeight/2;
		}
		
		if (yValue < 0) yValue = 0;
	}
	
	if (yValue + screenHeight >= docHeight) {
		yValue = docHeight - screenHeight - 1;
	}
	
	window.clearInterval(cb_scroll_interval);
	cb_scroll_interval = window.setInterval('scrollInterval('+yValue+')',10);
}
/* Fonction nécessaire à la précédente. Effectue un incrément de scroll. */
function scrollInterval(d) {
	var so = scrollOffset()
	a = Math.round(so + (d-so)/cb_scroll_speed);
	
	window.scrollTo(0,a);
  	if (a==d || cb_scroll_check==a) {
  		window.clearInterval(cb_scroll_interval);
  		cb_scroll_check = null;
  	}
  	cb_scroll_check = a;
}

/* Position en Y d'un élément. */
function yOffset(item) {
	yoff = item.offsetTop;
	while (item = item.offsetParent) yoff += item.offsetTop;
	return yoff;
}

/* Position du scroll. */
function scrollOffset() {
	body=document.body;
	d=document.documentElement;
	if (body && body.scrollTop) return body.scrollTop;
	if (d && d.scrollTop) return d.scrollTop;
	if (window.pageYOffset) return window.pageYOffset;
	return 0;
}

/* Désactivation d'un évènement */
function killEvent(e) {
	if (window.event) {
		window.event.cancelBubble = true;
		window.event.returnValue = false;
	 	return;
   	}
	if (e.preventDefault && e.stopPropagation) {
		e.preventDefault();
		e.stopPropagation();
	}
}

/* Clic sur une ancre interne à la page. */
function initAnchors() {
	var curloc = window.location.href;
	if (curloc.indexOf('#') != -1)
		curloc = curloc.substring(0,curloc.indexOf('#'));
	
	var a = document.getElementsByTagName('a');
	for (i=0; i<a.length; i++) {
		if (a[i].href && a[i].href.indexOf('#') != -1 && a[i].href.substring(0,a[i].href.indexOf('#')) == curloc) {
			a[i].onclick = scrollAnchor;
		}
	}
}

/* 
* Scroll vers une ancre particulière à partir d'un lien interne.
* Gestionnaire d'évènement à utiliser sur le lien en question (onclick).
*/
function scrollAnchor(e) {
	e = e || window.event;
	var target = null;
	if (e.target) target = e.target;
	if (e.srcElement) target = e.srcElement;
	
	var match = target.href ? target.href.substring(target.href.indexOf('#')+1) : 0;
	var a = document.getElementsByTagName('*');
	for (i=0; i<a.length; i++) {
		if (match == a[i].name || match == a[i].id) {
			scroll(a[i]);
			killEvent(e);
			return;
		}
	}
}