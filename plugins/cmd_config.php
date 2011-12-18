<?php
	cmds_init(CMDS_PRIVATE,'CONFIG','cmd_config',CMDS_P_NICK|CMDS_P_MSG);

	function cmd_config_show($to,$arr,$basekey)
	{
		$ret=array();
		if(empty($basekey))
			$basekey='config';
		elseif(strncmp('config',$basekey,6))
			$basekey='config|'.$basekey;
		foreach($arr as $idx=>$val)
		{
			if(is_array($val))
			{
				cmd_config_show($val,"{$basekey}|{$idx}",$prefix);
			} else {
				util_stringify_type($val);
        privmsg($to,"[$basekey|$idx] = $val");
				// sock_puts("{$prefix}[{$basekey}|{$idx}] = {$val}");
			}
		}
	}
	
	function cmd_config($nick,$msg)
	{
		util_split($code,$msg);
		$code=strtolower($code);
		switch($code)
		{
			case 'show':
				util_split($key,$msg);
				$config=&config_get($key);
				$display=cmd_config_show($config,$key,$nick);
				break;
			case 'save':
				$config=&config_get();
				$configd=serialize($config);
				file_put_contents("{$config['bot']['nick']}.config",$configd);
				break;
			case 'load':
				$config=&config_get();
				$config=array();
				$config=file_get_contents("{$config['bot']['nick']}.config");
				break;
		}
		return FALSE;
	}
	
