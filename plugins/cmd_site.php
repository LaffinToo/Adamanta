<?php
   // Create a Private Message command
   cmds_init(CMDS_PRIVATE,'SITE','cmd_site',CMDS_P_NICK|CMDS_P_FROM|CMDS_P_MSG);
   // Create a channel command, notice the optional last parameter this is yer Command Char in channel
   cmds_init(CMDS_PUBLIC,'SITE','cmd_site',CMDS_P_NICK|CMDS_P_FROM|CMDS_P_MSG,'!');

   // Connect to the database
   mysql_connect('localhost','user','password');
   mysql_select_db('database');

  function cmd_site($nick,$from,$msg)
  {
   util_split($code,$msg);
    util_split($un,$from,'@');
    $from=mysql_real_escape_string($from);
    $code=strtolower($code);
    switch($code)
    {
      // /notice <botnick> site link <site-username> <site-password>
      // Links a irc user hostmask to site user
      case 'link':
        util_split($username,$msg);
        util_split($pass,$msg);
        $username=mysql_real_escape_string($username);
        if(mysql_num_rows(mysql_query("SELECT * FROM bot_hostmasks WHERE hostmask='$from'")))
        {
          sock_puts("NOTICE $nick :Already have an account linked ($from)");
        }elseif(mysql_num_rows($res=mysql_query("SELECT id,username,passhash,secret FROM users WHERE username='$username'"))==1)
        {
          $row=mysql_fetch_assoc($res);
          if(md5($row['secret'].$pass.$row['secret'])==$row['passhash'])
          {
            if(mysql_num_rows($res=mysql_query("SELECT id FROM bot_users WHERE uid=$row[id]")))
            {
              $buid=mysql_result($res,0);
            } else {
              mysql_query('INSERT INTO bot_users SET uid='.$row['id']);
              $buid=mysql_insert_id();
            }
            mysql_query("INSERT INTO bot_hostmasks (buid,hostmask) VALUES($buid,'$from');");
            sock_puts('NOTICE '.$nick.' :Account Linked');
          } else {
            sock_puts('NOTICE '.$nick.' :Could not verify account');
          }
        }
        break;
      case 'stats':
        if(!mysql_num_rows($res=mysql_query("SELECT u.* FROM bot_hostmasks AS bh JOIN bot_users AS bu ON bu.id=bh.buid JOIN users AS u ON u.id=bu.uid WHERE bh.hostmask='$from';")))
        {
          sock_puts('NOTICE '.$nick.' :I don\'t know you');
        } else {
          $row=mysql_fetch_assoc($res);
          sock_puts("NOTICE $nick :Username: $row[username] / Class: $row[class] / Uploaded: $row[uploaded] / Downloaded $row[downloaded]");
        }
    }
  }
