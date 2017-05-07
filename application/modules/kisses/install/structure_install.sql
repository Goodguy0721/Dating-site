DROP TABLE IF EXISTS `[prefix]kisses`;
CREATE TABLE IF NOT EXISTS `[prefix]kisses` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`image` varchar(150) NOT NULL COMMENT 'name image',
	`sorter` INT(4) UNSIGNED NOT NULL COMMENT 'sorter',
	`date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]kisses_users`;
CREATE TABLE IF NOT EXISTS `[prefix]kisses_users` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`image` varchar(150) NOT NULL COMMENT 'name image',
	`user_to` INT(10) UNSIGNED NOT NULL COMMENT 'user to',
	`user_from` INT(10) UNSIGNED NOT NULL COMMENT 'user from',
	`message` text NOT NULL COMMENT 'kiss message',
	`date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`mark` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'read on not to read',
	PRIMARY KEY (`id`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `[prefix]kisses_users` (`id`, `image`, `user_to`, `user_from`, `message`, `date_created`, `mark`) VALUES
(null, '9e342f461c.png', 1, 15, 'Hey there', '2016-07-29 11:52:35', 0),
(null, '773e6bd9eb.png', 11, 16, '', '2016-07-29 11:53:09', 0),
(null, '8090190c35.png', 16, 9, 'Hope to receive a message from you', '2016-07-29 11:53:58', 0),
(null, '1f5033805a.png', 14, 13, '', '2016-07-29 11:54:38', 0),
(null, 'b343a728f6.png', 2, 3, '', '2016-07-29 11:55:19', 0),
(null, '7a12bec573.png', 12, 7, 'You’re cool', '2016-07-29 11:56:14', 0),
(null, '55c0fe7d8b.png', 14, 5, 'You look incredible', '2016-07-29 11:56:56', 0);