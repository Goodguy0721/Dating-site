DROP TABLE IF EXISTS `[prefix]send_vip`;
CREATE TABLE `[prefix]send_vip` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(10) NOT NULL DEFAULT '0',
  `id_sender` int(10) NOT NULL DEFAULT '0',
  `id_membership` float DEFAULT NULL,
  `status` enum('approved','declined','waiting') NOT NULL DEFAULT 'waiting',
  `declined_by_sender` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `transfer_fee` float DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
