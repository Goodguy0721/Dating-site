DROP TABLE IF EXISTS `[prefix]guided_setup_menu`;
CREATE TABLE IF NOT EXISTS `[prefix]guided_setup_menu` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `gid` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `[prefix]guided_setup_menu` (`id`, `gid`) VALUES
(1, 'guided_pages');

DROP TABLE IF EXISTS `[prefix]guided_setup_pages`;
CREATE TABLE IF NOT EXISTS `[prefix]guided_setup_pages` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `guided_menu_id` int(3) NOT NULL,
  `url` varchar(255) NOT NULL,
  `sorter` int(4) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `is_configured` tinyint(1) DEFAULT 0,
  `is_new` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `[prefix]guided_setup_pages` (`id`, `guided_menu_id`, `url`, `sorter`, `is_active`) VALUES
(1, 1, 'admin/users/settings', 1, 1),
(2, 1, 'admin/properties/property/user_type', 2, 1),
(3, 1, 'admin/tickets/reasons', 3, 1),
(4, 1, 'admin/moderation/settings', 4, 1),
(5, 1, 'admin/payments/systems', 5, 1),
(6, 1, 'admin/payments/settings', 6, 1),
(7, 1, 'admin/services/index', 7, 1),
(8, 1, 'admin/packages/index', 8, 1),
(9, 1, 'admin/memberships/index', 9, 1),
(10, 1, 'admin/banners/index', 10, 1),
(11, 1, 'admin/themes/view_installed/[id_theme]', 11, 1),
(12, 1, 'admin/themes/edit_set/[id_theme]/[id_colorset]', 12, 1),
(13, 1, 'admin/countries/index', 13, 1),
(14, 1, 'admin/languages/langs', 14, 1),
(15, 1, 'admin/content/index', 15, 1),
(16, 1, 'admin/notifications/settings', 16, 1),
(17, 1, 'admin/news/feeds', 17, 1),
(18, 1, 'admin/horoscope/feeds', 17, 1),
(19, 1, 'admin/social_networking/services', 18, 1),
(20, 1, 'admin/seo/default_edit/user', 19, 1),
(21, 1, 'admin/seo_advanced/index', 20, 1),
(22, 1, 'admin/field_editor/sections', 21, 1),
(23, 1, 'admin/start/menu/add_ons_items', 22, 1);

INSERT INTO `[prefix]guided_setup_pages` (`id`, `guided_menu_id`, `url`, `sorter`, `is_active`) VALUES
(24, 2, 'http://www.datingpro.com/helpdesk/installation-instructions/', 1, 1),
(25, 2, 'http://www.datingpro.com/helpdesk/system-requirements/', 2, 1);




