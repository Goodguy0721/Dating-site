DROP TABLE IF EXISTS `[prefix]chats`;
CREATE TABLE `[prefix]chats` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`gid` VARCHAR(255) NOT NULL,
	`active` TINYINT(1) NOT NULL DEFAULT '0',
	`installed` TINYINT(1) NOT NULL DEFAULT '0',
	`activities` SET('own_page','include') NOT NULL,
	`settings` TEXT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `name` (`gid`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]pg_videochats`;
CREATE TABLE `[prefix]pg_videochats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invite_user_id` int(3) NOT NULL DEFAULT '0',
  `invited_user_id` int(3) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'send',
  `date_time` datetime NOT NULL,
  `date_time_start` varchar(25) NOT NULL,
  `duration` int(11) NOT NULL,
  `amount` float NOT NULL,
  `chat_key` varchar(20) NOT NULL DEFAULT '',
  `last_change_date_time_user_id` int(11) NOT NULL,
  `invite_message_id` int(11) NOT NULL DEFAULT '0',
  `inviter_is_online` tinyint(1) NOT NULL DEFAULT '0',
  `invited_is_online` tinyint(1) NOT NULL DEFAULT '0',
  `inviter_is_paused` tinyint(1) NOT NULL DEFAULT '0',
  `invited_is_paused` tinyint(1) NOT NULL DEFAULT '0',
  `inviter_peer_id` varchar(50) NOT NULL,
  `invited_peer_id` varchar(50) NOT NULL,
  `is_notified` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]pg_videochats_messages`;
CREATE TABLE `[prefix]pg_videochats_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_chat` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_chat` (`id_chat`),
  KEY `id_user_id_chat` (`id_user`,`id_chat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]oovoochats`;
CREATE TABLE `[prefix]oovoochats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invite_user_id` int(3) NOT NULL DEFAULT '0',
  `invited_user_id` int(3) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'send',
  `date_time` datetime NOT NULL,
  `date_time_start` varchar(25) NOT NULL,
  `duration` int(11) NOT NULL,
  `amount` float NOT NULL,
  `chat_key` varchar(20) NOT NULL DEFAULT '',
  `last_change_date_time_user_id` int(11) NOT NULL,
  `invite_message_id` int(11) NOT NULL DEFAULT '0',
  `inviter_is_online` tinyint(1) NOT NULL DEFAULT '0',
  `invited_is_online` tinyint(1) NOT NULL DEFAULT '0',
  `inviter_is_paused` tinyint(1) NOT NULL DEFAULT '0',
  `invited_is_paused` tinyint(1) NOT NULL DEFAULT '0',
  `session_token` text NULL,
  `is_notified` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
