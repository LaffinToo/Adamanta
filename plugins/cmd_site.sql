# 
# Table: 'bot_hostmasks'
# 
CREATE TABLE `bot_hostmasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `buid` int(10) unsigned NOT NULL DEFAULT '0',
  `hostmask` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_bothm_hostmasks` (`hostmask`)
) ; 

# Table: 'bot_users'
# 
CREATE TABLE `bot_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ; 

