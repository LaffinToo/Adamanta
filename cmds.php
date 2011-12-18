<?php
/*
 *      cmds.php
 *      
 *      Adamanta - A PHP IRC BOT
 *      
 *      This file contains User module handler
 *      
 */

	define('CMDS_PRIVATE',1); // Private messages
	define('CMDS_PUBLIC',2);  // Public (Channels)
	define('CMDS_CTCP',3);    // ctcp
 	define('CMDS_NOTICE',4);
	
	define('CMDS_P_NICK',1);
	define('CMDS_P_FROM',2);
	define('CMDS_P_TO',  4);
	define('CMDS_P_MSG', 8);
	define('CMDS_P_ALL',0xFFFF);
	
	

	/**
	 * used to hook a user module into Adamanta
	 * 
	 * @param int $type     Command type (CMDS_PRIVATE|CMDS_PUBLIC|CMDS_CTCP|CMDS_NOTICE)
	 * @param string $code  Code recieved
	 * @param string_type $function function to call
	 * @param int $params parameters function takes (CMDS_P_NICK|CMDS_P_FROM|CMDS_P_TO|CMDS_P_MSG|CMDS_P_ALL)
	 */
	function cmds_init($type,$code,$function,$params)
	{
		$cmds=&config_get_default('cmds',array());
		
		$code=strtoupper($code);
		$cmds[$type][$code]=array($function,$params);
	}
	
	/**
	 * User module command handler.
	 * 
	 * @param int $type Command type (CMDS_PRIVATE|CMDS_PUBLIC|CMDS_CTCP|CMDS_NOTICE)
	 * @param string $code Code recieved
	 * @param string $nick From who in channel
	 * @param string $from user@hostmask
	 * @param string $to channel|nick
	 * @param string $msg rest of message
	 * @return FALSE if no further processing to be done
	 */
	function cmds_check($type,$code,$nick,$from,$to,$msg)
	{
		logger(LOGGER_DEBUG,"function cmds_check('{$type}','{$code}','{$nick}','{$from}','{$to}','{$msg}')");
		
		$cmd=&config_get_default('cmds',array());
    $code=strtoupper($code);
		if(!isset($cmd[$type][$code]))
			return;
    if(CMDS_PUBLIC && isset($cmd[4]))
    {
      if($code[1]!=$cmd[4])
        return;
      $code=substr($code,1);
    }
		$cmd=&$cmd[$type][$code];
		if(empty($cmd))
			return;
		if($cmd[1]&CMDS_P_NICK)
			$args[]=$nick;
		if($cmd[1]&CMDS_P_FROM)
			$args[]=$from;
		if($cmd[1]&CMDS_P_TO)
			$args[]=$to;
		if($cmd[1]&CMDS_P_MSG)
			$args[]=$msg;
		$sargs='\''. implode('\',\'',$args) .'\'';
		logger(LOGGER_DEBUG,"function {$cmd[0]}({$sargs})");
		return call_user_func_array($cmd[0],$args);
	}

  function privmsg($nick,$msg)
  {
    sock_puts("PRIVMSG $nick : $msg");
  }
  
 
  function notice($nick,$msg)
  {
    sock_puts("NOTICE $nick :$msg");
  }
  
  function ctcp($nick,$msg)
  {
    sock_puts("NOTICE $nick :\0x01$msg\0x01");
  }
  
  function join()
  {
    $argc=func_num_args();
    if(!$argc)
      return FALSE;
    $channels=func_get_arg(0);
    $keys=($argc==1?NULL:func_get_arg(1));
    if(is_string($channels);
    {
      $channels[]=array($channels);
      $keys[]=array($keys);
    } elseif(!is_array($channels) {
      return FALSE;
    }
    $channels=implode(',',$channels);
    $keys=implode(' ',$keys);
    sock_puts("JOIN $channels $keys");
    return TRUE;
  }
  
  function part($chname)
  {
    if(is_array($chname))
      $chname=implode(' ',$chname);
    sock_puts('PART '.$chname);
  }
