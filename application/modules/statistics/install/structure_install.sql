DROP TABLE IF EXISTS `[prefix]statistics`;
CREATE TABLE IF NOT EXISTS `[prefix]statistics` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `module` varchar(100) NOT NULL,
  `model` varchar(255) NOT NULL,
  `cb_create` varchar(255) NOT NULL,
  `cb_drop` varchar(255) NOT NULL,
  `cb_process` varchar(255) NOT NULL,
  `stat_points` text,
  `scheduler` datetime NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE `module` (`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
