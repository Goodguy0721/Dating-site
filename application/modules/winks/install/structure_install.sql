DROP TABLE IF EXISTS `[prefix]winks`;
CREATE TABLE IF NOT EXISTS `[prefix]winks` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_from` INT(10) UNSIGNED NOT NULL COMMENT '@[prefix]users:id',
	`id_to` INT(10) UNSIGNED NOT NULL COMMENT '@[prefix]users:id',
	`type` ENUM('new','replied','ignored') NOT NULL DEFAULT 'new',
	`date` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `users` (`id_from`, `id_to`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `[prefix]winks` (`id`, `id_from`, `id_to`, `type`, `date`) VALUES
(null, 16, 15, 'new', '2016-07-29 11:44:45'),
(null, 4, 8, 'new', '2016-07-29 11:45:35'),
(null, 10, 7, 'new', '2016-07-29 11:47:02'),
(null, 6, 1, 'new', '2016-07-29 11:47:33'),
(null, 4, 16, 'new', '2016-07-29 11:47:33');