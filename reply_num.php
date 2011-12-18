<?php
/*
 *      reply_code.php
 *      
 *      Adamanta - A PHP IRC BOT
 *      
 *      Handles Server Numeric Codes
 *      
 */
	define('BOT_F_NOJOINS'  ,0x0001); // Nick Taken :(
	define('BOT_F_RECONNECT',0x0002); // Nick Taken :(
	define('BOT_F_NOTOP    ',0x0004); // Nick Taken :(
	
	define('CHAN_F_IGNORE',0x0001); // IGNORE CHANNEL

	
	/**
	 * 403 - Error
	 * channel :No such channel
	 * 
	 * @param unknown_type $from
	 * @param unknown_type $code
	 * @param unknown_type $param
	 */
	function got_403($from,$param)
	{
		util_split($chan,$param);
		logger(LOGGER_ERR,"No such channel ({$chan}) - Ignoring");
	}
	
	/**
	 * 404 - Error
	 * channel :Cannot send to channel 
	 * @param unknown_type $from
	 * @param unknown_type $code
	 * @param unknown_type $param
	 */
	function got_404($from,$param)
	{
		util_split($chan,$param);
		logger(LOGGER_ERR,"Cannot send to channel ({$chan}) - Ignoring");
	}
	
	/**
	 * 405 - Error
	 * channel :You have joined too many channels
	 * @param unknown_type $from
	 * @param unknown_type $code
	 * @param unknown_type $param
	 */
	function got_405($from,$param)
	{
		util_split($chan,$param);
		logger(LOGGER_ERR,"In too many channels - DISABLING Joins");
	}
	
	/**
	 * Nick Failire
	 * 
	 * @param original error message
	 */
	function got_nickfail($nick,$param)
	{
		$badnicks=&config_get_default('current|names|bad',array());
		$triednicks=&config_get_default('current|names|tried',array());
		$orignick=&config_get('bot|nick');
		$altnick=&config_get('bot|altnick');
		if(!empty($altnick) && (!in_array($altnick,$badnicks) || !in_array($altnick,$triednicks)))
		{
			$new=$altnick;
		} elseif(!in_array($orignick,$badnicks))
		{
			$lo=strlen($orignick);
			$lc=strlen($nick);
			if($lo<$lc)
				$num=substr($nick,$lo);
			else
				$num=0;
			$num++;
			$new=$orignick.$num;
		} else
			$new=util_makepass();
		config_set('current|names|name',$new);
		
		logger(LOGGER_MISC,"Bad Nick ({$nick}): {$param}");
		sock_puts("NICK {$new}");
	}

	/**
	 * 432 - Nickname Invalid  Error
	 * nick :<reason>
	 * @param $from
	 * @param $param
	 */
	function got_432($from,$param)
	{
		util_splitnick($nick,$param);
		$badnicks=&$config_get_defaullt('current|names|badlist',array());
		$badnicks[]=$nick;
		got_nickfail($nick,$param);
	}
	
	/**
	 * 433 - Error Nickname in use
	 * nick :Nickname in use
	 * @param $from
	 * @param $code
	 * @param $param
	 */
	function got_433($from,$param)
	{
		util_splitnick($nick,$param);
		$triednicks=&config_get_default('current|names|tried',array());
		$triednicks[]=$nick;
		got_nickfail($nick,$param);
	}
	
	/**
	 * 436 - Error
	 * nick :Nickname collision KILL
	 * @param $from
	 * @param $code
	 * @param $param
	 */
	function got_436($from,$param)
	{
		util_nicksplit($nick,$param);
		$triednicks=&$config_get_defaullt('current|names|tried',array());
		$triednicks[]=$nick;
		got_nickfail($param);
	}
	
	/**
	 * 441 - Error
	 * <nick> <channel> :They aren't on that channel
	 * @param $from
	 * @param $code
	 * @param $param
	 */
	function got_441($from,$param)
	{
		util_split($nick,$param);
		util_split($channel,$param);
		sock_puts("NAMES {$channel}");
		logger(LOGGER_ERR,"Ooops! I lost a user rebuilding channel ({$channel}) list");
	}
	
	/**
	 * 443 - Error
	 * <user> <channel> :is already on channel"
	 * @param $from
	 * @param $code
	 * @param $param
	 */
	function got_443($from,$param)
	{
		util_split($nick,$param);
		util_split($channel,$param);
		logger(LOGGER_ERR,"Ooops! didnt see ({$nick}) here, rebuilding channel ({$channel}) list");
	}
	
	/**
	 * 451 - Error
	 * :You have not registered
	 * @param $from
	 * @param $code
	 * @param $param
	 */
	function got_451($from,$param)
	{
		util_login();
		logger(LOGGER_ERR,"Ooops! Not even logged in and im trying to do stuff here");	
	}
	
	/**
	 * 464 - Error
	 * :Password incorrect
	 * @param $from
	 * @param $code
	 * @param $param
	 */
	function got_464($from,$param)
	{
		util_login();
		config_set('bot|flags',config_get_default('bot|flags',0)&~(BOT_F_RECONNECT));
		sock_puts("QUIT :Leaving!");
		logger(LOGGER_ERR,"Crap! the password is wrong");	
	}
	
	/**
	 * 465 - Error
	 * :You are banned from this server
	 * @param $from
	 * @param $code
	 * @param $param
	 */
	function got_465($from,$param)
	{
		util_login();
		config_set('bot|flags',config_get_default('bot|flags',0)&~(BOT_F_RECONNECT));
		sock_puts("QUIT :Leaving!");
		logger(LOGGER_ERR,"Crap! The dog did it");	
	}
	
	/**
	 * 471 - Error
	 * channel :Cannot join channel full (+l)
	 * 
	 * @param unknown_type $from
	 * @param unknown_type $code
	 * @param unknown_type $param
	 */
	function got_471($from,$param)
	{
		util_split($chan,$param);
		logger(LOGGER_ERR,"Channel is full ({$chan}) - Ignoring");
	}
	
	/**
	 * 473 - Error
	 * channel :Cannot join channel invite (+i)
	 * 
	 * @param unknown_type $from
	 * @param unknown_type $code
	 * @param unknown_type $param
	 */
	function got_473($from,$param)
	{
		util_split($chan,$param);
		logger(LOGGER_ERR,"Channel is by invite ({$chan}) - Ignoring");
	}
	
	/**
	 * 474 - Error
	 * channel :Cannot join channel banned (+b)
	 * 
	 * @param unknown_type $from
	 * @param unknown_type $code
	 * @param unknown_type $param
	 */
	function got_474($from,$param)
	{
		util_split($chan,$param);
		logger(LOGGER_ERR,"Banned from ({$chan}) - Ignoring");
	}
	
	/**
	 * 474 - Error
	 * <channel> :Cannot join channel has a key(+k)
	 * 
	 * @param unknown_type $from
	 * @param unknown_type $code
	 * @param unknown_type $param
	 */
	function got_475($from,$param)
	{
		util_split($chan,$param);
		logger(LOGGER_ERR,"I need a key for ({$chan}) - Ignoring");
	}
	
	/**
	 * 482 - Error
	 * channel> :You're not channel operator
	 * 
	 * @param unknown_type $from
	 * @param unknown_type $code
	 * @param unknown_type $param
	 */
	function got_482($from,$param)
	{
		util_split($chan,$param);
		logger(LOGGER_ERR,"I need a ops for ({$chan})");
	}
	

	
	/**
	 * 001 - Welcome
	 * Welcome to the Internet Relay Network <nick>!<user>@<host>
	 * 
	 * @param unknown_type $from
	 * @param unknown_type $param
	 */
	function got_001($from,$param)
	{
		$uhost=substr($param,strrpos($param,' ')+1);
		util_splitnick($nick,$uhost);
		config_set('current|network|server',$from);
		config_set('current|names|name',$nick);
		$channels=config_get('channels');
		foreach($channels as $channel)
		{
			$chans[]=$channel['name'];
			$keys[]=(isset($channel['key'])?$channel['key']:'');
		}
		logger(LOGGER_SERV,"Got my nick ({$nick}) and userhost({$uhost}");
		if(!empty($chans))
			sock_puts("JOIN ". implode(',',$chans) .' '. implode(' ',$keys));
	}
	
	/**
	 * 353 RPL_NAMREPLY
	 * <channel> :[[@|+]<nick> [[@|+]<nick> [...]]]
	 * 
	 * @param unknown_type $from
	 * @param unknown_type $param
	 */
	function got_353($from,$param)
	{
		util_split($to,$param);
		util_split($chname,$param);
		$name=config_get('current|names|name');
		if(strpos('=*@',$chname[0])!==FALSE)
			util_split($chname,$param);
		util_fixcolon($param);
		$nicks=explode(' ',$param);
		foreach($nicks as $nick)
		{
			if((strpos('+&@',$nick[0]))!==FALSE)
			{
				$mode=$nick[0];
				$nick=substr($nick,1);	
			} else
				$mode=0;
			if($nick==$name) continue;
			chan_user_add($chname,$nick,$mode);
		}
	}
