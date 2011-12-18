<?php

	cmds_init(CMDS_PRIVATE,'DO','cmd_do',CMDS_P_NICK|CMDS_P_MSG);

	function cmd_do($nick,$msg)
	{
		util_split($code,$msg);
		$code=strtoupper($code);
		sock_puts("$code $msg");
		return FALSE; // Tell caller we did sumfin, so can safely bypass further processing
	}
	
?>