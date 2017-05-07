DROP TABLE IF EXISTS `[prefix]dashboard`;
CREATE TABLE `[prefix]dashboard` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `module` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `fk_object_id` int(11) NOT NULL,
  `data` text NULL,
  `status` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL,
  `date_modified` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

