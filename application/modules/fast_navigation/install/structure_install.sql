DROP TABLE IF EXISTS `[prefix]fast_navigation`;
CREATE TABLE IF NOT EXISTS `[prefix]fast_navigation` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `module` varchar(25) NOT NULL,
  `url`  TEXT NOT NULL,
  `keywords` TEXT NOT NULL,
  `lang_code` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;