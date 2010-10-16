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
class rssflow {
	var $contents;	 // Contenu du RSS

	function rssflow($chan_title,$chan_link,$chan_desc,$chan_cat) {
		$this->contents =
			'<?xml version="1.0" encoding="utf-8"?>'."\n".
			'<rss version="2.0">'."\n".
			'<channel>'."\n".
			'<title>'.strip_tags($chan_title).'</title>'."\n".
			'<link>'.$chan_link.'</link>'."\n".
			'<description>'.strip_tags($chan_desc).'</description>'."\n".
			'<pubDate>'.date('r',time()).'</pubDate>'."\n".
			'<lastBuildDate>'.date('r',time()).'</lastBuildDate>'."\n".
			'<generator>Connectix Boards RSS Generator</generator>'."\n".
			'<category>'.strip_tags($chan_cat).'</category>'."\n";
	}

	function addItem ($item_title,$item_link,$item_desc,$item_timestamp,$item_author) {
		$this->contents.=
			'<item>'."\n".
			"\t".'<title>'.strip_tags($item_title).'</title>'."\n".
			"\t".'<link>'.$item_link.'</link>'."\n".
			"\t".'<description>'.strip_tags($item_desc).'</description>'."\n".
			"\t".'<pubDate>'.date('r',$item_timestamp).'</pubDate>'."\n".
			"\t".'<author>'.strip_tags($item_author).'</author>'."\n".
			'</item>'."\n";
	}

	function sendFlow () {
		header('Content-Type: text/xml; charset=UTF-8');

		$this->contents.=
			'</channel>'."\n".
			'</rss>'."\n";
		
		echo $this->contents;
	}
}
?>