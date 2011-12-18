<?php
/*
 *      util.php
 *
 *      Adamanta - A PHP IRC BOT
 *
 *      Various utility functions
 *
 */

	/**
	 * Generate a 10-15 character random password type string
	 */
	function util_makepass()
	{
		$length=10+rand(0,5);
		$str='';
		for($i=0;$i<$length;$i++)
		{
			$str.=(chr(($v=rand(0,35))<10?48+$v:($v+87)));
		}
	}


	/**
	 * Split a string into two parts by delimeter (default space)
	 *
	 * @param string $arg1 primary part of string (this will be overwritten)
	 * @param string $arg2 secondary part of string (This is the full string - and will contain the remainder of the string)
	 * @param string $del delimeter
	 */
	function util_split(&$arg1,&$arg2,$del=' ')
	{
		if(!empty($arg2))
		{
			if(strpos($arg2,$del)!==FALSE)
				list($arg1,$arg2)=explode($del,$arg2,2);
			else {
				$arg1=$arg2;
				$arg2=NULL;
			}
		}
	}

	/**
	 * split a nick!username@host into two parts, from hostmask
	 * @param string $nick
	 * @param string $hostmask
	 */
	function util_splitnick(&$arg1,&$arg2)
	{
		if(strpos($arg2,'!')!==FALSE)
			util_split($arg1,$arg2,'!');
	}

	/**
	 * remove empty elements from an array
	 *
	 * @param array $arr
	 * @return array
	 */
	function util_strip_empties($arr)
	{
		if(!is_array($arr))
			return $arr;
		foreach($arr as $key=>$val)
		{
			if(empty($val) && $val!==0) unset($arr[$key]);
		}
		return $arr;
	}

	/**
	 * fix the nick!username@hostmask removing any non essential characters from username
	 * @param string $from
	 */
	function util_fixfrom(&$str)
	{
		//$strict_host=config_get_default('server|strict-host',FALSE);
		if(/*$strict_host ||*/ empty($str) || ($s=strpos($str,'@')===FALSE))
			return;
		$from=$str;
		util_splitnick($nick,$from);

		if(strchr('-+~^=',$from[0]))
		{
			$from=substr($from,1);
			$str="{$nick}!{$from}";
		}
	}

    /**
     * Much like array_combine, however this is bases on the keys of both arrays.
     * if a key in $keys does not exist in $vals, NULL is used as default
     * if a key in $vals does not exist in $keys, it is ignored
     * @param array $keys
     * @param array $vals
     * @return string|Ambigous <multitype:, string, unknown>
     */
    function util_makearray($keys,$vals)
    {
        if(!is_array($keys))
            return FALSE;
        $arr=array();
        foreach($keys as $idx=>$key)
        {
            $arr[$key]=(isset($vals[$idx])?$vals[$idx]:NULL);
        }
        return $arr;
	}


	/**
	 * Parses server responses ($in) into $from,$code,$params
	 * @param string $in Server resonse
	 * @param string $from From who
	 * @param string $code server response code
	 * @param string $params remaining parameters
	 *
	 */
	function util_parse($in,&$from,&$code,&$params)
	{
		$from=$code=$params='';

		if($in[0]==':')
		{
			$in=substr($in,1);
			if(($p=strpos($in,' '))===FALSE)
			{
				$code=$in;
				return;
			}
			$from=substr($in,0,$p);
			$in=$params=substr($in,$p+1);
		}
		if(($p=strpos($in,' '))===FALSE)
		{
			$code=$in;
			$params='';
			return;
		}
		$code=substr($in,0,$p);
		$params=substr($in,$p+1);
	}

	/**
	 * Logs into an irc server
	 *
	 * @param string $pass
	 */
	function util_login($pass)
	{
		$bot=config_get('bot');
        sock_puts("NICK {$bot['nick']}");
        if(!empty($pass))
            sock_puts("PASS {$bot['pass']}");
        sock_puts("USER {$bot['username']} {$bot['host']} {$bot['servername']} :{$bot['realname']}");

	}
	/**
	 *  Connect and login into an irc $server on $port with $pass
	 * @param string $server
	 * @param int $port
	 * @param string $pass
	 * @return bool
	 */
	function util_connect($server,$port,$pass)
	{
        if($handle=sock_open($server,$port))
        {
        	util_login($pass);
            return TRUE;
        }
		return FALSE;
	}

	/**
	 * Removes initial colon, and removes anything after a space
	 * i.e.:  :comand args
	 * would shorten the string to: command
	 * @param string_type $str
	 */
	function util_fixcolon(&$str)
	{
		if($str[0]==':')
			$str=substr($str,1);
		else
			util_split($str,$rest);
	}

	/**
	 * Removes whitespaces from a string
	 * @param string $str
	 */
	function util_rmws(&$str)
	{
		$str=str_replace(array("\t","\r","\n",'  ','  '),' ',$str);
	}

	/**
	 * Support routine for util_stringify
	 * changes $val into a human readable version type
	 * @param mixed $val
	 */
	function util_stringify_type(&$val)
	{
		$val=(is_string($val)?"'{$val}'":
			(is_bool($val)?($val?'TRUE':'FALSE'):
			(is_null($val)?'NULL':(string)$val)));
	}

	/**
	 * Works like print_r. takes an array, and shows it's contents as a string.
	 * Extended to support NULL/TRUE/FALSE
	 *
	 * @param array $args
	 * @return string
	 */
	function util_stringify($args)
	{
		$str=array();;
		if(empty($args))
			$str[]="(NULL)";
		else
		foreach($args as $idx=>$val)
		{
			util_stringify_type($idx);
			if(is_array($val))
			{

				$str[]= "{$idx}=>(". (empty($val)?'NULL':util_stringify($val)) . ')';
			} else {
				util_stringify_type($val);
				$str[]="{$idx} = {$val}";
			}
		}
		return implode(',',$str);
	}
