<?php
 /*
 *      log.php
 *      
 *      Adamanta - A PHP IRC BOT
 *      
 *      Logger & Debuuger
 *      
 */

	// Message Types Supported
    define('LOGGER_MSGS',   0x00001);  
    define('LOGGER_PUBLIC', 0x00002);
    define('LOGGER_JOIN',   0x00004);
    define('LOGGER_MODES',  0x00008);
    define('LOGGER_CMDS',   0x00010);
    define('LOGGER_MISC',   0x00020);
    define('LOGGER_BOTS',   0x00040);
    define('LOGGER_RAW',    0x00080);
    define('LOGGER_FILES',  0x00100);
    define('LOGGER_SERV',   0x00200);
    define('LOGGER_WALL',   0x00400);
    define('LOGGER_ERR',    0x00800);
    define('LOGGER_RCLNT',  0x08000);
    define('LOGGER_RSERV',  0x10000);
    define('LOGGER_DEBUG',  0x20000);
    define('LOGGER_CERR',   0x40000);
    define('LOGGER_TIME',   0x80000);  // Log Timestamp
    define('LOGGER_ALL',    0xfffff);

    
    $log_types=array(
        // MessageType => array( LogIdentifier,code)
    	LOGGER_MSGS=>array(  '-M-','m'), // Private msgs/ctcp to the bot
    	LOGGER_PUBLIC=>array('-P-','p'), // public chatter on channel
		LOGGER_JOIN=>array(  '-J-','j'), // join/parts/netsplits
    	LOGGER_MODES=>array( '-K-','k'), // Kicks/bans/mode changes
    	LOGGER_CMDS=>array(  '-C-','c'), // Commads ppl use
    	LOGGER_MISC=>array(  '---','o'), // Other: misc info
    	LOGGER_BOTS=>array(  '-B-','b'), // bot net 
    	LOGGER_SERV=>array(  '-s-','s'), // Server msgs
    	LOGGER_WALL=>array(  'WAL','w'), // wallops: msgs between IRCops
    	LOGGER_ERR=>array(   '###','e'), // Server Error Messages
    	LOGGER_RCLNT=>array( '<<<','y'), // Raw msgs from bot
    	LOGGER_RSERV=>array( '>>>','x'), // Raw msgs to bot
    	LOGGER_CERR=>array(  'EEE','d'), // Client Errors
    	LOGGER_DEBUG=>array( 'DBG','d'), // Debug Info
    	LOGGER_TIME=>array(   NULL,'t'), // Enable timestamping
    );
    
    
	/**
	 *  Logs a message of $type
	 *  
	 * @param int $type
	 * @param string $str
	 */
	function logger($type,$str)
	{
    	global $log_types,$log_events;
        if((!($log_events & $type)) || ($type&LOGGER_TIME))
        	return;
        $timestamp=($log_events&LOGGER_TIME)?date('[ymd/His] '):'';
      	$prefix='UNK';
        foreach($log_types as $ti=>$lt)
        {
        	if($ti&$type)
        	{
        		$prefix=$lt[0];
        		break;
        	}
        }
        
        $msg="{$timestamp}{$prefix} {$str}".PHP_EOL;
        echo $msg;
        if($type & (LOGGER_ERR))
        	echo logger_backtrace().PHP_EOL;
	}
    
	/**
	 * Creates a formatted log style backtrace
	 * @return string
	 */
	function logger_backtrace()
	{
		$trace=debug_backtrace();
		$cwd=getcwd();
		$cwdl=strlen($cwd);
		foreach($trace as $line=>$info)
		{
			if(($stn=strncmp($info['file'],$cwd,$cwdl))==0)
				$info['file']=substr($info['file'],$cwdl+1);
			$args=util_stringify($info['args']);
			$str[]="  #({$info['file']}:".
				"{$info['line']}) ".
				"{$info['function']}".
				"({$args}))";
		}
		return implode(PHP_EOL,$str);
		
	}
	/**
	 * Handles PHP errors
	 * 
	 * @param int $no error number
	 * @param string $str error message
	 * @param string $file file error occured in
	 * @param int $line line error occured in
	 * @param string $context
	 * @return string
	 */
	function logger_errhandler($no,$str,$file,$line,$context)
	{
		static$errs=array(
			'ERROR'=>256,
			'WARNING'=>514,
			'NOTICE'=>1032,
		);
		$errcode='UNKNOWN';
		foreach($errs as $code=>$mask)
		{
				if($no&$mask)
					$errcode=$code;
		}
		
		logger(LOGGER_ERR,"-{$errcode}- [{$no}] ({$file}:{$line}) {$str}",TRUE);
		
		if($errcode=='ERROR')
			exit($no);
		return true; // don't execut PHP internal error handler
	}
	
	set_error_handler('logger_errhandler');
