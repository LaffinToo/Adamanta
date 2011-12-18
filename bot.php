<?php
/*
 *      bot.php
 *      
 *      Adamanta - A PHP IRC BOT
 *      
 *      This file contains the startup sequence
 *      
 */

//Core Files
include('controller.php');
include('sockets.php');
include('util.php');
include('log.php');
include('manager.php');
include('reply_num.php');
include('reply_code.php');
include('ctcp.php');
include('cmds.php');
//include('db_sqlite.php');
//include('db.php');
//include('db_memory.php');


if(!defined(HAVE_CONFIG))
{
  include('config.php');
//  db_set_config();
}


//Plugins
include('plugins/cmd_do.php');
include('plugins/cmd_config.php');
include('plugins/cmd_chan.php');
//include('modules/cmd_site.php');


$log_events=LOGGER_ALL;
set_time_limit(0);
date_default_timezone_set('America/Los_Angeles');

con_main();
