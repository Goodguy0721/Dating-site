DROP TABLE IF EXISTS `[prefix]unregistered`;
CREATE TABLE IF NOT EXISTS `[prefix]unregistered` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL,
  `nickname` varchar(20) NOT NULL,
  `user_type` varchar(50) NOT NULL,
  `looking_user_type` varchar(50) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `sname` varchar(100) NOT NULL,
  `lang_id` int(3) NOT NULL,
  `group_id` int(3) NOT NULL,
  `user_logo` varchar(100) NOT NULL,
  `id_country` char(2) NOT NULL,
  `id_region` int(3) NOT NULL,
  `id_city` int(3) NOT NULL,
  `birth_date` date NOT NULL,
  `ip` varchar(15) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;