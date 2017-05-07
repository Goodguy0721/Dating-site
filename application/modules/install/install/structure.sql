DROP TABLE IF EXISTS `[prefix]languages`;
CREATE TABLE IF NOT EXISTS `[prefix]languages` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` tinyint(3) NOT NULL,
  `rtl` enum('rtl','ltr') NOT NULL,
  `is_default` tinyint(3) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `code` (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]lang_dedicate_modules`;
CREATE TABLE IF NOT EXISTS `[prefix]lang_dedicate_modules` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `module` varchar(25) NOT NULL,
  `model` varchar(100) NOT NULL,
  `method_add` varchar(100) NOT NULL,
  `method_delete` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module` (`module`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `[prefix]lang_dedicate_modules` VALUES(NULL, '', 'pg_theme', 'lang_dedicate_module_callback_add', 'lang_dedicate_module_callback_delete', '2010-11-19 13:14:00');
INSERT INTO `[prefix]lang_dedicate_modules` VALUES(NULL, '', 'pg_seo', 'lang_dedicate_module_callback_add', 'lang_dedicate_module_callback_delete', '2010-11-19 13:14:00');

DROP TABLE IF EXISTS `[prefix]lang_ds`;
CREATE TABLE IF NOT EXISTS `[prefix]lang_ds` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `module_gid` varchar(100) NOT NULL,
  `gid` varchar(100) NOT NULL,
  `option_gid` varchar(50) NOT NULL default '',
  `type` enum('header','option') NOT NULL,
  `sorter` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module_gid` (`module_gid`),
  KEY `sorter` (`sorter`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]lang_pages`;
CREATE TABLE IF NOT EXISTS `[prefix]lang_pages` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `module_gid` varchar(100) NOT NULL,
  `gid` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module_gid` (`module_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]libraries`;
CREATE TABLE `[prefix]libraries` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `gid` VARCHAR( 25 ) NOT NULL ,
  `version` FLOAT NOT NULL,
  `name` VARCHAR( 100 ) NOT NULL ,
  `date_add` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]modules`;
CREATE TABLE IF NOT EXISTS `[prefix]modules` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `module_gid` varchar(25) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `module_description` text NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `version` FLOAT NOT NULL,
  `is_disabled` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_update` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module_gid` (`module_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]modules_config`;
CREATE TABLE IF NOT EXISTS `[prefix]modules_config` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `module_gid` varchar(25) NOT NULL,
  `config_gid` varchar(100) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module_gid` (`module_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]modules_methods`;
CREATE TABLE IF NOT EXISTS `[prefix]modules_methods` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `module_gid` varchar(25) NOT NULL,
  `controller` varchar(35) NOT NULL,
  `method` varchar(100) NOT NULL,
  `access` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module_gid` (`module_gid`),
  KEY `module_gid_2` (`module_gid`,`controller`,`method`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]sessions`;
CREATE TABLE IF NOT EXISTS `[prefix]sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_address` bigint(19) DEFAULT '0',
  `user_agent` varchar(50) NOT NULL,
  `user_data` text,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]themes`;
CREATE TABLE IF NOT EXISTS `[prefix]themes` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `theme` varchar(100) NOT NULL,
  `theme_type` enum('admin','user') NOT NULL DEFAULT 'user',
  `scheme` varchar(100) NOT NULL,
  `active` tinyint(3) NOT NULL DEFAULT '0',
  `theme_name` varchar(255) NOT NULL,
  `theme_description` varchar(255) NOT NULL,
  `setable` tinyint(3) NOT NULL,
  `logo_width` int(3) NOT NULL,
  `logo_height` int(3) NOT NULL,
  `logo_default` varchar(255) NOT NULL,
  `mini_logo_width` int(3) NOT NULL,
  `mini_logo_height` int(3) NOT NULL,
  `mini_logo_default` varchar(255) NOT NULL,
  `mobile_logo_width` int(3) NOT NULL,
  `mobile_logo_height` int(3) NOT NULL,
  `mobile_logo_default` varchar(255) NOT NULL,
  `template_engine` enum('templateLite', 'twig') NOT NULL DEFAULT 'twig',
  PRIMARY KEY (`id`),
  KEY `default` (`active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]themes_colorsets`;
CREATE TABLE IF NOT EXISTS `[prefix]themes_colorsets` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `set_name` varchar(255) NOT NULL,
  `set_gid` varchar(100) NOT NULL,
  `id_theme` int(3) NOT NULL,
  `color_settings` text NOT NULL,
  `active` tinyint(3) NOT NULL,
  `scheme_type` varchar(5) NOT NULL,
  `preset` varchar(50) NOT NULL DEFAULT 'default',
  `is_generated` tinyint(3) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_theme` (`id_theme`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]seo_modules`;
CREATE TABLE IF NOT EXISTS `[prefix]seo_modules` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `module_gid` varchar(25) NOT NULL,
  `model_name` varchar(50) NOT NULL,
  `get_settings_method` varchar(100) NOT NULL,
  `get_rewrite_vars_method` varchar(100) NOT NULL,
  `get_sitemap_urls_method` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module_gid` (`module_gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]seo_settings`;
CREATE TABLE IF NOT EXISTS `[prefix]seo_settings` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `controller` enum('user','admin','custom') NOT NULL,
  `module_gid` varchar(25) NOT NULL,
  `method` varchar(50) NOT NULL,
  `noindex` tinyint(3) NOT NULL,
  `url_template` varchar(255) NOT NULL,
  `lang_in_url` tinyint(1) NOT NULL,
  `priority` decimal(1,1) NOT NULL DEFAULT '0.5',
  PRIMARY KEY (`id`),
  KEY `model` (`module_gid`),
  KEY `controller` (`controller`),
  KEY `method` (`method`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]acl`;
CREATE TABLE IF NOT EXISTS `[prefix]acl` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `module_gid` varchar(50) NOT NULL DEFAULT '',
  `caller_type` varchar(50) NOT NULL DEFAULT '',
  `caller_id` int(3) NOT NULL DEFAULT 0,
  `role` varchar(50) NOT NULL DEFAULT '',
  `type` varchar(50) NOT NULL DEFAULT '',
  `action` varchar(50) NOT NULL DEFAULT '',
  `resource_type` varchar(255) NOT NULL DEFAULT '',
  `resource_id` int(3) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `caller_type` (`caller_type`),
  KEY `role` (`role`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
