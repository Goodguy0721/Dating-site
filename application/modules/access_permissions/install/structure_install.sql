DROP TABLE IF EXISTS `[prefix]access_permissions_modules`;
CREATE TABLE IF NOT EXISTS `[prefix]access_permissions_modules` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `module_gid` varchar(25) NOT NULL,
  `controller` varchar(25) NOT NULL,
  `method` varchar(100) NULL,
  `access` tinyint(3) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module_gid` (`module_gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]access_permissions_group_period`;
CREATE TABLE IF NOT EXISTS `[prefix]access_permissions_group_period` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `period` smallint(5) NOT NULL,
  `period_type` enum('years','months','days','hours') NOT NULL  DEFAULT 'days',
  `pay_type` enum('account','account_and_direct','direct') NOT NULL  DEFAULT 'account_and_direct',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]access_permissions_users`;
CREATE TABLE IF NOT EXISTS `[prefix]access_permissions_users` (
   `id` int(3) NOT NULL AUTO_INCREMENT,
  `id_user` int(3) NOT NULL,
  `group_gid` varchar(100) NOT NULL,
  `id_period` int(3) NOT NULL,
  `data` text NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `date_activated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_expired` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;