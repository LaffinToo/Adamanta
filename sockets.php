<?php
/*
 *      reply_code.php
 *      
 *      Adamanta - A PHP IRC BOT
 *      
 *      Handles Socket Communications
 *      
 */
	
	/**
	 * Sockets Main handler
	 * 
	 * @param string $function (open|close|gets|puts|handle)
	 * @param array $args parameters for function
	 * @return mixed
	 */
	function __sock($function,$args=array())
	{
		static $handle;
		$ret=FALSE;
		$sockh=($foriegn=(isset($args[0]) && is_resource($args[0])))?$args[0]:$handle;
		//logger(LOGGER_DEBUG,"function sock_{$function}('". implode('\',\'',$args) . '\') '. ($sockh?'TRUE':'FALSE'));
		switch($function)
		{
			case 'open':
				if(($ret=call_user_func_array('fsockopen',$args))!==FALSE)
					$handle=$ret;
				break;
			case 'close':
				if(!$foriegn) $handle=FALSE;
				$ret=fclose($sockh);
				break;
			case 'gets':
				if(!$sockh)
					return FALSE;
				if(!$foriegn) 
					array_unshift($args,$sockh);
				stream_set_timeout($sockh,0,30000);
				$ret=trim(fgets($sockh));
				if(strlen($ret))
					logger(LOGGER_RSERV,$ret);
				if($ret!==FALSE)
					$ret=trim($ret);
				elseif(!feof($sockh))
					$ret=NULL;
				else
					$handle=FALSE;
				break;
			case 'puts':
				if(!$sockh)
					return FALSE;
				$msg=$foriegn?$args[1]:$args[0];
				$msg=trim($msg);
				if(!strlen($msg))
					return NULL;
				logger(LOGGER_RCLNT,$msg);
				$ret=fputs($sockh,"{$msg}\n");
				if($ret===FALSE && feof($sockh))
					$handle=FALSE;
				break;
			case 'handle':
				if($foriegn)
				{
					$handle=$sockh;
					$ret=TRUE;
				} elseif (!count($args)) {
					$ret=$handle;
				}
		}
		return $ret;
	}
	
	/**
	 * Get/Set socket handle
	 * Set Socket handle if passed as parameter otherwise return the socket handle
	 * @param resource socket handle
	 * @return resource
	 */
	function sock_handle()
	{
		$args=func_get_args();
		return __sock('handle',$args);
	}
	/**
	 * Opens a socket
	 * @param string server
	 * @param int port
	 * @return resource
	 */
	function sock_open()
	{
		$args=func_get_args();
		return __sock('open',$args);
	}
	/**
	 * Close current socket
	 * if param specified, closes that handle. otherwise close current handle
	 * @param resource
	 * @return 
	 */
	function sock_close()
	{
		$args=func_get_args();
		return __sock('close',$args);
	}
	/**
	 * Outputs a string to socket
	 * @param resource soocket handle (optional)
	 * @param string string to send to socket
	 * @return mixed
	 */
	function sock_puts()
	{
		$args=func_get_args();
		return __sock('puts',$args);
	}
	/**
	 * Gets a string from current/specified socket
	 * @param resource socket handle
	 * @return Ambigous <mixed, string, NULL>
	 */
	function sock_gets()
	{
		$args=func_get_args();
		return __sock('gets',$args);
	}
	
