DROP TABLE IF EXISTS `[prefix]statistics_mailbox`;
CREATE TABLE IF NOT EXISTS `[prefix]statistics_mailbox` (
  `object_id` int(3) NOT NULL,
  `sent` int(11) NOT NULL,  
  `recieved` int(11) NOT NULL,
  `replyed` int(11) NOT NULL,
  UNIQUE `object_id` (`object_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
