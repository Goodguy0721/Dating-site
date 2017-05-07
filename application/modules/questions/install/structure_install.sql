DROP TABLE IF EXISTS `[prefix]questions`;
CREATE TABLE `[prefix]questions` (
  `id` int(10) NOT NULL AUTO_INCREMENT, 
  `id_user` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `[prefix]questions_answers`;
CREATE TABLE `[prefix]questions_answers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `own_question` int(1) NOT NULL,
  `id_user` int(10) NOT NULL,
  `id_user_to` int(10) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `answer` varchar(255) NOT NULL DEFAULT '',
  `is_new` tinyint(3) NOT NULL DEFAULT '1',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

