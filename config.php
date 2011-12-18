<?php
/*
 *      config.php
 *      
 *      Adamanta - A PHP IRC BOT
 *      
 *      Our configuration
 *      
 */

// Default Config

$config=array(
     // the Bot array contains info on how to login
	'bot'=>array(
		'nick'=>'Adamanta',
		'altnick'=>'Terrisiam',
		'username'=>'adamanta',
		'realname'=>'Smile :) This might hurt',
		'host' => 'localhost',
		'servername'=>'arkum',),
     // network
	'network'=>array(
        // servers is an array, you can specify more than 1 server
        // format: pass@server:port
		'servers'=>array('irc.phpfreaks.com'),
		),
	'channels'=>array(
		// channels to join
		array('name'=>'#help'),
		// Add more
		//array('name'=>'#chat'),
		),
	);
