<?php
/*
 *      reply_code.php
 *      
 *      Adamanta - A PHP IRC BOT
 *      
 *      Handles Server Codes
 *      
 */

	function got_ping($from,$msg)
	{
		util_fixcolon($msg);
		sock_puts("PONG :{$msg}");
	}

	function got_pong($from,$msg)
	{
		util_fixcolon($msg);
		$stat=&config_get_default('current|server|lag',0);
		if(($stat=time()-$msg)<99999)
			$stat=0;
	}
	
	
	function got_error($from,$msg)
	{
		util_fixcolon($msg);
		logger(LOGGER_ERR,"Error Message: {$msg}");
		sock_close();
	}
	
	function got_privmsg($from,$msg)
	{
		$ctcp_count=0;
		util_split($to,$msg);
		util_fixcolon($msg);
		$uhost=$from;
		util_splitnick($nick,$uhost);
		$ctcp_reply='';
		while(($p=strpos($msg,chr(1)))!==FALSE)
		{
			$q=++$p;
			if(($p=strpos($msg,chr(1),$q))!==FALSE)
				$ctcp=substr($msg,$q,$p-$q);
			$msg=substr($msg,++$p);
			if(!(strncmp($ctcp,' ACTION',7)))
			{
				$chan=chan_get($to);
				if(!empty($chan))
				{
					util_splitnick($nick,$from);
					logger(LOGGER_PUBLIC,"{$chan['name']}:{$nick} {$ctcp}");
				}
			} elseif($ctcp_count<3) {
				$ctcp_reply.=got_ctcp($from,$to,$ctcp);
			}
			$ctcp_count++;
		}
		if(!empty($ctcp_reply))
			sock_puts("NOTICE {$nick} :{$ctcp_reply}");
		if(empty($msg))
			return;
		if(strpos('#&+',$to[0])!==FALSE)  // channel message
			got_public($from,$to,$msg);
		elseif($to[0]=='$' || (strpos($to,'.')!==FALSE))
			logger(LOGGER_MSGS|LOGGER_SERV."[{$nick}!{$uhost} to {$to}] {$msg}");
		else
			got_cmd($nick,$uhost,$msg);
		
	}
	
	function got_notice($from,$msg)
	{
		util_split($to,$msg);
		util_fixcolon($msg);
		$ctcp_reply='';
		while(($p=strpos($msg,chr(1)))!==FALSE)
		{
			$q=++$p;
			if(($p=strpos($msg,chr(1),$q))!==FALSE)
				$ctcp=substr($msg,$q,$p-$q);
			$msg=substr($msg,++$p);
			$ctcp_reply=got_ctcpreply($from,$to,$ctcp);
		}
		if(empty($msg))
			return;
		if(strpos('#&+',$to[0])!==FALSE)  // channel message
			got_publicnotice($from,$to,$msg);
		elseif($to[0]=='$' || (strpos($to,'.')!==FALSE))
		{
			util_splitnick($nick,$from);
			logger(LOGGER_MSGS|LOGGER_SERV,"-{$nick} ({$from}) to {$to}- {$msg}");
		} else {
			if(strpos($from,'!')!==FALSE)
				util_splitnick($nick,$from);
			if(!empty($from)||!empty($nick))
      {
        $str=$msg;
        util_split($code,$str);
				logger(LOGGER_MSGS,"-NOTICE- $msg");
      } 
			else {
        if(cmds_check(CMDS_NOTICE,$code,$nick,$from,NULL,$str)===FALSE)
          return;
				logger(LOGGER_MISC,"-{$nick}({$from}) - {$msg}");
      }
		}
	}
	
	function got_join($from,$chname)
	{
		util_fixcolon($chname);
		$mode=0;
		util_splitnick($nick,$from);
		$name=&config_get('current|names|name');
		if(($p=strpos($chname,chr(7)))!==FALSE)
		{
			$mode=substr($chname,$p+1);
			$chname=substr($chname,0,$p);
		}
		if($nick!=$name)
		{
			chan_user_add($chname,$nick,$mode);
		} else {
			$chan=chan_my($chname);
			if(empty($chan))
			{
    			logger(LOGGER_MISC,"joined {$chname} but didnt want to");
    			sock_puts("PART {$chname}");
    			return;
    		}
    		chan_add($chname);
		}   	
	}
	
	function got_public($from,$to,$msg)
	{
		if(($chan=chan_find($to))===FALSE)
			return;
		util_splitnick($nick,$from);
		logger(LOGGER_PUBLIC,"<{$to}:{$nick}> {$msg}");
	}
	
	function got_publicnotice($from,$to,$msg)
	{
		if(($chan=chan_find($to))===FALSE)
			return;
		util_splitnick($nick,$from);
		logger(LOGGER_PUBLIC,"-{$to}:{$nick}- {$msg}");
	}
	
	// Private msgs
	function got_cmd($nick,$uhost,$msg)
	{
		$from="{$nick}!{$uhost}";
		$str=$msg;
		util_rmws($str);
		util_split($code,$str);
		$code=strtoupper($code);
		if(cmds_check(CMDS_PRIVATE,$code,$nick,$from,NULL,$str)===FALSE)
			return;
		logger(LOGGER_MSGS,"[{$from}] {$msg}");
	}
	
	function got_part($from,$chname)
	{
		util_fixcolon($chname);
		if(empty($chname))
			return;
		$chan=chan_get($chname);
		util_splitnick($nick,$from);
		chan_user_del($chname,$nick);
		logger(LOGGER_JOIN,"{$nick} ({$from}) left {$chname}.");
		if(strcasecmp($nick,config_get('current|names|name')))
		{
			chan_user_del($chname,$nick,0);
		} elseif(!chan_members($chname)) {
			chan_del($chname);
			sock_puts("PART {$chname}");
			sock_puts("JOIN {$chname} {$chan['key']}");
		}
	}
	
	function got_kick($from,$msg)
	{
		util_split($chname,$msg);
		if(($chan=&chan_get($chname))===FALSE)
			return;
		util_splitnick($nick,$msg);
		util_fixcolon($msg);
		util_splitnick($whodid,$from);
		if(strcasecmp($nick,config_get('currents|names|name'))===0)
		{
			chan_del($chname);
			sock_puts("JOIN {$chname} {$chan['key']}");
		} else
			chan_user_del($chname,$nick);
		logger(LOGGER_JOIN,"{$nick} was kicked by {$whodid} ({$from}) from {$chname}");
	}
	
	function got_nick($ffrom,$msg)
	{
		$from=$ffrom;
		util_fixfrom($from);
		$s=$from;
		util_splitnick($nick,$from);
		util_fixcolon($msg);
		if(($uid=user_find($nick))!==FALSE)
		{
			$userlist=&config_get('current|userlist');
			$userlist[$i]=$msg;
			logger(LOGGER_JOIN,"Nick change {$nick} -> {$msg}");
		}
	}
	
	function got_wall($from,$msg)
	{
		util_fixcolon($msg);
		if(($p=strpos($from,'!'))!==FALSE && ($p==strrpos($from,'!')))
		{
			util_splitnick($nick,$from);
			logger(LOGGER_WALL,"!{$nick}({$from})! {$msg}");
		} else {
			logger(LOGGER_WALL,"!{$from}! {$msg}");
		}
	}
	
	function got_quit($from,$msg)
	{
		$split=FALSE;
		util_splitnick($nick,$from);
		util_fixcolon($msg);
		if(($p=strpos($msg,' '))!==FALSE && ($p==strrpos($msg,' ')))
		{
			$z1=strpos($msg,'.',$p+1);
			$z2=strpos($msg,'.');
			if(($z1!==FALSE) && ($z2!==FALSE) && ($z2<$p))
			{
				$split=TRUE;
			}
		}
		$channels=config_get('current|chanlist');
		foreach($channels as $chan)
		{
			$removed=chan_user_del($chan,$nick);
			if($split)
				logger(LOGGER_JOIN,"{$nick} ({$from}) got netsplit.");
			else
				logger(LOGGER_JOIN,"{$nick} ({$from}) has left irc: {$msg}");
		}
	}
