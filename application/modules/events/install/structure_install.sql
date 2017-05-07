DROP TABLE IF EXISTS `[prefix]events`;
CREATE TABLE IF NOT EXISTS `[prefix]events` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `fk_user_id` int(10) NOT NULL,
  `category` varchar(50) NOT NULL,
  `country_code` char(2) NOT NULL,
  `fk_region_id` int(10) NOT NULL,
  `fk_city_id` int(10) NOT NULL,
  `address` varchar(255) NOT NULL,
  `lat` decimal(11,7) NOT NULL,
  `lon` decimal(11,7) NOT NULL,
  `venue` varchar(100) NOT NULL,
  `img` varchar(255) NOT NULL,
  `max_participants` smallint(5) NOT NULL,  
  `date_started` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_ended` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deadline_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_active` tinyint(1) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `is_adult` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `date_created` timestamp NOT NULL,
  `album_id` int(10) NOT NULL,
  `comments_count` int(10) NOT NULL DEFAULT '0',
  `avatar_comments_count` int(10) NOT NULL DEFAULT '0',
  `search_data` text NOT NULL,
  `event_settings` text NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `search_data` (`search_data`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `[prefix]events_users`;
CREATE TABLE IF NOT EXISTS `[prefix]events_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `fk_event_id` int(10) NOT NULL,
  `fk_user_id` int(10) NOT NULL,
  `is_invite` tinyint(1) NOT NULL,
  `is_new` tinyint(3) NOT NULL DEFAULT '1',
  `status` enum('approved', 'pending', 'declined') NOT NULL,
  `response_date` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `[prefix]album_types` (`id`, `gid`, `gid_upload_type`, `file_count`, `gid_upload_video`, `video_count`) VALUES (NULL, 'events_type', 'events_image', '0', 'events_video', '0');