<?php
/*
 *      ctcp.php
 *      
 *      Adamanta - A PHP IRC BOT
 *      
 *      Handling CTCP responses
 *      
 */

	// CTCP RESPONSE TYPES
	define('CTCP_IGNORE',1); // Ignore it
    define('CTCP_MESSAGE',2); // Send a Message
    define('CTCP_FUNCTION',3); // Function Handler
    
    // CTCP Codes
    $ctcp = array(
         // code => array(response_type,handler)
        'VERSION'=>array(CTCP_MESSAGE,'"Adamanta/V0.0.1" "Linux/2.6" "laffintoo@gmail.com"'),
        'PING'=>array(CTCP_FUNCTION,'ctcp_echo'),
        'CLIENTINFO'=>array(CTCP_FUNCTION,'ctcp_clientinfo'),
    	'ACTION'=>array(CTCP_IGNORE,NULL),
        'USERINFO'=>array(CTCP_MESSAGE,'Curio Version 0.0.1'),
    	'FINGER'=>array(CTCP_MESSAGE,'No fingering please'),
        'ECHO'=>array(CTCP_FUNCTION,'ctcp_echo'),
        'ERRMSG'=>array(CTCP_FUNCTION,'ctcp_echo'),
        'TIME'=>array(CTCP_FUNCTION,'ctcp_time'),
        );
        
    /**
     * ctcp general handler
     * 
     * @param string $from
     * @param string $to
     * @param string $msg
     * @return FALSE|mixed FALSE=no further processing
     */
    function got_ctcp($from,$to,$msg)
    {
    	global $ctcp;
    	
        if(empty($msg))
            return;
        
        $nick=$code=$ctcpreply='';
        util_split($code,$msg);
        util_splitnick($nick,$from);
        if(empty($code))
        {
            $code=$msg;
            $msg='';
        }
        $code=strtoupper($code);
        if((strpos($to,'.')!==FALSE) && (strpos('&#+',substr($to,0,1))!==FALSE))
        {
            logger(LOGGER_MISC,"CTCP {$code}: {$msg} from {$nick} ({$from}) to {$to}");
            return;
        }
        if(cmds_check(CMDS_CTCP,$nick,$from,$to,$code,$msg)===FALSE)
        	return;
        if(isset($ctcp[$code]))
        {
        	$ctcpev=$ctcp[$code];
        	$ctcpreply=($ctcpev[0]==CTCP_MESSAGE?
        	"{$code} {$ctcpev[1]}":	
        	($ctcpev[0]==CTCP_FUNCTION?call_user_func_array($ctcpev[1],array($code,$msg))
        	:NULL));
        	$ctcpreply=(!empty($ctcpreply))?
        		(chr(1). $ctcpreply .chr(1)):FALSE;	
        }
        $logger=(strpos('#&+',$to[0])!==FALSE)?LOGGER_PUBLIC:LOGGER_MSGS;
        if($code=='ACTION')
        	logger($logger,"Action to {$to}: {$nick} {$msg}");
        else
        	logger($logger,"CTCP {$code}: {$msg} from {$nick} ({$from}) to {$to}");
        if(empty($ctcpreply))
        	sock_puts("NOTICE {$nick} :What kind of ctcp code was that? I dun understand");
        return $ctcpreply;
    }
    
    /**
     * Got reply from one of our ctcp's sent
     * 
     * @param string $from
     * @param string $to
     * @param string $msg
     * @param bool $ignoring
     * @return 
     */
    function got_ctcpreply($from,$to,$msg,$ignoring)
    {
    	$ffrom=$ffrom;
    	util_split($code,$msg);
    	util_splitnick($nick,$from);
    	if(empty($code))
    	{
    		$code=$msg;
    		$msg='';
    	}
    	if(($to[0]=='$') || (strpos($to,'.')!==FALSE) &&
    		(strpos('&$+',$to[0])!==FALSE))
    	{
    		if(!$ignoring)
    			logger(LOGGER_PUBLIC,"CTCP reply {$code}: {$msg} from {$nick} ({$from}) to {$to}");
    		return;
    	}
    	if($ignoring)
    		return;
    	if(strpos('#&+',$to[0]))
    	{
    		$log=LOGGER_PUBLIC;
    		//update_idle($to,$nick);
    	} else {
    		$log=LOGGER_MSGS;
    	}
    	logger($log,"CTCP reply {$code}: {$msg} from {$nick} ({$from}) to {$to}");
    }
        
    /**
     * CTCP Client Info response
     * 
     * @param string $code
     * @param string $msg
     * @return string $response
     */
    function ctcp_clientinfo($code,$msg)
    {
        global $ctcp;
        
        $ctcp_cmds=array_keys($ctcp);
        $reply=NULL;
        if(empty($msg))
        {
            $reply=implode(' ',$ctcp_cmds) . ' : No Specific usage information';
        } else {
        	$rest=$msg;
        	util_split($p,$rest);
        	strtoupper($p);
            if(in_array($p,$ctcp_cmds)!==FALSE)
                $reply="Sorry I do not have usage info on {$p}";
            if(empty($reply))
                $reply="ERRMSG CLIENTINFO: {$msg} is not a valid commad";
            else
                $reply="CLIENTINFO {$reply}";
        }
       	return $reply;
    }
    /**
     * CTCP Echo what client has sent us
     * 
     * @param string $code
     * @param string $msg
     * @return string $response
     */
    function ctcp_echo($code,$msg)
    {
        return (strlen($msg)<=80?"{$code} {$msg}":FALSE);
    }
    /**
     * CTCP Time - send time
     * 
     * @param string $code
     * @param string $msg
     * @return string $response
     */
    function ctcp_time($code,$msg)
    {
        return ("{$code} ".date('D, d M y H:i:s T'));
    }
	
