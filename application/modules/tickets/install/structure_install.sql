DROP TABLE IF EXISTS `[prefix]tickets`;
CREATE TABLE IF NOT EXISTS `[prefix]tickets` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `mails` text NOT NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT INTO `[prefix]tickets` VALUES(1, 'a:2:{i:0;s:13:"test@test.com";i:1;s:13:"mail@test.com";}', '2011-05-10 08:47:01');
INSERT INTO `[prefix]tickets` VALUES(2, 'a:1:{i:0;s:13:"mail@test.com";}', '2011-05-10 09:12:57');
INSERT INTO `[prefix]tickets` VALUES(3, 'a:1:{i:0;s:13:"test@test.com";}', '2011-05-10 09:23:06');

DROP TABLE IF EXISTS `[prefix]tickets_users`;
CREATE TABLE IF NOT EXISTS `[prefix]tickets_users` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `id_user` int(3) NOT NULL,
  `admin_new` tinyint(4) NOT NULL,
  `admin_last` tinyint(1) NOT NULL,
  `message` text NOT NULL,
  `date_created` datetime NOT NULL,
  `is_new` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]tickets_messages`;
CREATE TABLE IF NOT EXISTS `[prefix]tickets_messages` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `id_user` int(3) NOT NULL,
  `is_admin_sender` tinyint(1) NOT NULL,
  `message` text NOT NULL,
  `is_new` tinyint(1) NOT NULL,
  `date_created` datetime NOT NULL,
  `notified` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
