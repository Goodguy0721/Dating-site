DROP TABLE IF EXISTS `[prefix]associations`;
CREATE TABLE IF NOT EXISTS `[prefix]associations` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_user` int(10) NOT NULL,
  `img` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `[prefix]associations_users`;
CREATE TABLE IF NOT EXISTS `[prefix]associations_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_user` int(10) NOT NULL,
  `id_profile` int(10) NOT NULL,
  `img` varchar(255) NOT NULL,
  `answer` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;