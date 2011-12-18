<?php
/*
 *      manager.php
 *      
 *      Adamanta - A PHP IRC BOT
 *      
 *      Handles our config array (config/network/channels/users)
 *      
 */

	define('USER_M_VOICE', 0x001);
	define('USER_M_HALFOP',0x002);
	define('USER_M_OP',    0x002);
	
	/**
	 * Set config variable
	 * @param string $key config path & Key
	 * @param mixed $val value to set as
	 */
	function config_set($key,$val)
	{
		$config=&config_get_default($key,$val);
		$config=$val;
	}
	
    /**
     * Get config key with a default value
     * @param unknown_type $key config path & key
     * @param unknown_type $default default value if key does not exist
     * @return Ambigous <unknown, mixed>
     */
    function &config_get_default($key,$default)
    {
    	$ret=&config_get($key,$default);
    	return $ret;
    }
    
    /**
     * @param string $key config path & key
     * @param mixed $default (optional default value)
     * @return mixed
     */
    function &config_get($key=NULL)
    {
		$keys=(empty($key)?array():explode('|',$key));
        $info=&$GLOBALS['config'];
        $ok=TRUE;
        if(($have_default=(func_num_args()==2)))
        	$default=func_get_arg(1);
        foreach ($keys as $node)
        {
        	if(!isset($info[$node]))
        	{
        		if(!$have_default)
        		{
        			$ok=FALSE;
        			break;
        		}
        		$info[$node]=array();
        	}
        	$info=&$info[$node];
        }
        if(!$ok)
        {
			if ($have_default && is_array($info) && empty($info))        	
        		$info=$default;
        	else
				logger(LOGGER_CERR,"Unable to load config ({$key})");
        }
        return $info;
    }
    
    /** Is this channel in my authorized channel list?
     * @param string $chname channel name
     * @return FALSE|string FALSE=not in channel list | Channel name
     */
    function &chan_my($chname)
    {
    	$ret=FALSE;
    	$channels=&config_get_default('channels',array());
    	if(empty($channels))
    		return $ret;
    	foreach($channels as &$chan)
    	{
    		if(strcasecmp($chname,$chan['name'])===0)
    		{
    			return $chan;
    		}
    	}
    	return $ret;
    }

    /**
     *  Find a channel in our current (joined) channel list
     * @param string $chname channel name
     * @return array Empty array | channel info array
     */
    function chan_find($chname)
    {
      $chanlist=&config_get_default('current|chanlist',array());
      return array_search($chname,$chanlist);
    }
    
    /**
     * Return id of the channel from our current channel list
     * @param string $chname
     * @return FALSE|$id
     */
    function &chan_get($chname)
    {
    	$ret=FALSE;
      if(($cid=chan_find($chname))!==FALSE)		
        $ret=&config_get("current|channels|{$cid}");
    	return $ret;
    }

    /**
     * Return a count of users in a channel
     * @param string $chname channel name
     * @return FALSE|number
     */
    function chan_members($chname)
    {
    	if(($chan=chan_get($chname))===FALSE)
    		return FALSE;
    	$userlist=$chan['userlist'];
    	return count($userlist);
    }

    /**
     * Add a channel to our current channels
     * 
     * @param string $chname channel name
     * @return number
     */
    function chan_add($chname)
    {
    	if(($cid=chan_find($chname))===FALSE)
    	{
    		$chanlist=&config_get_default('current|chanlist',array());  		
    		$chanlist[]=$chname;
    		$chan=&config_get_default("current|channels|{$cid}",array());
    		$cid=array_search($chname,$chanlist);
    	}
    	return $cid;
    }
    
    /**
     * Remove a channel from current channels
     * @param string $chname channel name
     */
    function chan_del($chname)
    {
    	if(($cid=chan_find($chname))!==FALSE)
    	{
    		$chanlist=&config_get_default('current|chanlist');  		
    		unset($chanlist[$cid]);
    		$channels=&config_get("current|channels");
    		$culist=$channels[$cid]['users'];
    		foreach ($culist as $uid)
    		{
    			$deluser=TRUE;
    			foreach($chanlist as $clid)
    			{
    				$chul=$channels[$clid]['users'];
    				if(($uicid=array_search($uid,$chul)))
    					$deluser=FALSE;
    				
    			}
    			if($deluser)
    				user_del_id($uid);
    		}
    		unset($channels[$cid]);
    	}
    }

    /**
     * Find a user (any channel)
     * 
     * @param string $nick
     * @return FALSE|number
     */
    function user_find($nick)
    {
    	
		$userlist=&config_get_default('current|userlist',array());
		$uid=array_search($nick,$userlist);
		return $uid;	
    }
    
    /**
     * verify user in our userlist
     * 
     * @param string $nick
     * @return FALSE|string
     */
    function &user_get($nick)
    {
    	$ret=FALSE;
    	if(($uid=user_find($nick))!==FALSE)
    		$ret=&config_get("current|users|{$cid}");
    	return $ret;
    }
    
   	/**
   	 * Add a user to current userlist
   	 * @param string $nick
   	 * @return number userid from userlist
   	 */
   	function user_add($nick)
   	{
   		if(($mode=strpos('+&@~',$nick[0]))!==FALSE)
   		{
   			$mode=($mode==3)?0:$mode++;
   			$nick=substr($nick,1);
   		}
   		if(($uid=user_find($nick))===FALSE)
   		{
			$userlist=&config_get_default('current|userlist',array());
			$users=&config_get_default('current|users',array());
			$userlist[]=$nick;
			$uid=array_search($nick,$userlist);
			$users[$uid]=array();
   		}
   		return $uid;
   	}
   	/**
   	 * Remove user from current userlist by id
   	 * 
   	 * @param int $uid
   	 */
   	function user_del_id($uid)
   	{
   		$users=&config_get('current|users');
   		if(isset($users[$uid]))
   		{
   			$userlist=&config_get('current|userlist');
   			unset($users[$uid]);
   			unset($userlist[$uid]);
   		}
   		
   	}
   	
   	/**
   	 * Remove user from current userlist by nick
   	 * 
   	 * @param string $nick
   	 */
   	function user_del($nick)
   	{
   		if(($uid=user_find($nick))!==FALSE)
   			user_del_id($uid);
   	}
   	
  
    /**
     * verify user is in channel by id
     * 
     * @param int $cid
     * @param int $uid
     * 
     * @return FALSE|array 
     */
    function chan_user_id($cid,$uid)
    {
    	if(($ulist=config_get("current|channels|{$cid}|userlist"))==FALSE)
    		return $ulist;
    	if(($ret=array_search($uid,$ulist))!==FALSE)
    		$ret=array($cid,$uid);
    	return $ret;
    }
    

    /**
     * Verify user is in channel by name
     * @param string $chname channel name
     * @param string $nick  user nick
     * @return FALSE|array array(channelid,userid)
     */
    function chan_user($chname,$nick)
    {
    	if(($cid=chan_find($chname))===FALSE)
    		return FALSE;
    	if(($uid=user_find($nick))===FALSE)
    		return FALSE;
    	return chan_user_id($cid,$uid);
   	}
	   	
    /**
     * Verify user is in channel by name
     * @param string $chname channel name
     * @param string $nick user nick
     * @return boolean
     */
    function is_chan_user($chname,$nick)
    {
    	return (chan_user($chname,$nick)!==FALSE);
    }

    /**
     * Add a user to a channel
     * 
     * @param string $chname channel name
     * @param string $nick user nick
     * @param string $mode unused
     * @return bool always returns TRUE
     */
    function chan_user_add($chname,$nick,$mode)
   	{
   		$cid=chan_add($chname);
   		$uid=user_add($nick);
    	$ulist=&config_get_default("current|channels|{$cid}|userlist",array());
    	if(!in_array($uid,$ulist))
    		$ulist[]=$uid;
    	return TRUE;
   	}
   	
   	/**
   	 * Remove a user from a channel
   	 * 
   	 * @param string $chname channel name
   	 * @param string $nick user nick
   	 * @return boolean
   	 */
   	function chan_user_del($chname,$nick)
   	{
   		if(($ret=chan_user($chname,$nick))===FALSE)
   			return FALSE;
   		$cid=$ret[0];
   		$uid=$ret[1];
    	$ulist=&config_get("current|channels|{$cid}|userlist");
    	$upos=array_search($uid,$ulist);
    	unset($ulist[$upos]);
    	return TRUE;
   	}
