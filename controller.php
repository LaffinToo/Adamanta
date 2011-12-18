<?php
/*
 *      controller.php
 *
 *      Adamanta - A PHP IRC BOT
 *
 *      The main routine, connect to irc server, and process server messages
 *
 */

	/**
	 *  Connect to irc server cycling through them
	 */
	function con_connect()
	{
		$idx=&config_get_default('current|network|idx',0);
		$serverlist=&config_get('network|servers');
		if(!($idx<count($serverlist)))
			$idx=0;
		$server=&config_get("network|servers|{$idx}");

		if(strpos($server,'@')!==FALSE)
		{
			util_split($pass,$server);
		} else
			$pass='';
		if(strpos($server,':')!==FALSE)
		{
			$port=$server;
			util_split($server,$port);
		} else
			$port=6667;
		$idx++;
		return util_connect($server,$port,$pass);
	}

	/**
	 *  Connect to irc than continually process server messages
	 */
	function con_main()
	{
		logger(LOGGER_MISC,'Attempting to connect');
		for($i=0;$i<3;$i++)
		{
			if($connected=con_connect())
				break;
			sleep(3);
		}
		while(($connected=(($buffer=sock_gets()))!==FALSE))
		{
			if(empty($buffer))
				continue;
			util_parse($buffer,&$from,&$code,$msg);
			util_fixfrom($from);
			$fcode=strtolower($code);
				$func="got_{$fcode}";
				if(function_exists($func))
				{
					logger(LOGGER_DEBUG,"function {$func}('{$from}','{$msg}')");
					if(call_user_func($func,$from,$msg) === FALSE) break;
				}
		}
	}

