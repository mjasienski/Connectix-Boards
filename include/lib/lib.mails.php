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

//// Fonctions d'envoi de mail ////

// Envoi d'un mail
function sendMail($mailaddress,$mailsubject,$mailmsg) {
	$mailaddress = trim(str_replace(array("\r","\n","\0"),'',$mailaddress));
	$mailsubject = trim(str_replace(array("\r","\n","\0"),'',$mailsubject));
	$mailmsg = trim(str_replace("\0",'',$mailmsg));

	$sep="\r\n";
	if (utf8_strpos($mailmsg,'<br />') === false) $sep='<br />'.$sep;
	else $mailmsg = str_replace('<br />','<br />'."\r\n",$mailmsg);
	
	$mailmsg=str_replace("\n",$sep,str_replace("\r", "\n", str_replace("\r\n", "\n", $mailmsg)));
	$mailmsg=html_entity_decode($mailmsg,ENT_QUOTES);
	
	$sep="\n";
	if (utf8_strtoupper(utf8_substr(PHP_OS, 0, 3) == 'WIN')) $sep = "\r\n";
	elseif (utf8_strtoupper(utf8_substr(PHP_OS, 0, 3) == 'MAC')) $sep = "\r";
	
	$headers =
		'From: "'.$GLOBALS['cb_cfg']->config['forumname'].'" <'.$GLOBALS['cb_cfg']->config['supportmail'].'>'.$sep.
		'Reply-To: "'.$GLOBALS['cb_cfg']->config['forumname'].'" <'.$GLOBALS['cb_cfg']->config['supportmail'].'>'.$sep.
		'Date: '.date('r').$sep.
		'MIME-Version: 1.0'.$sep. 
		'Content-Type: text/html; charset=utf-8'.$sep.
		'Content-transfer-encoding: 8bit'.$sep.
		'X-Mailer: Connectix Boards Mailer - PHP/'.phpversion().$sep.$sep;
	
	$mailmsg = '<html>
		<head>
			<title>'.$mailsubject.'</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<link rel="stylesheet" media="screen" type="text/css" title="Design" href="http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/admin/design/mail.css" />
		</head>
		<body>
			<div id="mailcontent">
				'.$mailmsg.'
			</div>
		</body>
		</html>';

	return mail($mailaddress,$mailsubject,$mailmsg,$headers);
}
?>