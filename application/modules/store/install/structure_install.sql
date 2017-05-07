DROP TABLE IF EXISTS `[prefix]store_bestsellers`;
CREATE TABLE IF NOT EXISTS `[prefix]store_bestsellers` (
  `id_category` int(3) NOT NULL,
  `id_product` int(3) NOT NULL,
  `priority` int(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]store_cart`;
CREATE TABLE IF NOT EXISTS `[prefix]store_cart` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `id_user` int(3) NOT NULL,
  `products_count` tinyint(4) NOT NULL,
  `total` decimal(11,4) NOT NULL,
  `gid_currency` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `[prefix]store_cart_products`;
CREATE TABLE IF NOT EXISTS `[prefix]store_cart_products` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `id_cart` int(3) NOT NULL,
  `id_recipient` int(3) NOT NULL,
  `id_product` int(3) NOT NULL,
  `price` decimal(11,4) NOT NULL,
  `gid_currency` varchar(20) NOT NULL,
  `count` tinyint(4) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `[prefix]store_categories`;
CREATE TABLE IF NOT EXISTS `[prefix]store_categories` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `gid` varchar(40) NOT NULL,
  `id_parent` int(3) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `product_active_count` int(3) NOT NULL,
  `product_inactive_count` int(3) NOT NULL,
  `bestsellers_count` int(3) NOT NULL,
  `priority` int(3) NOT NULL,
  `options_data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `[prefix]store_options`;
CREATE TABLE IF NOT EXISTS `[prefix]store_options` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `type` enum('one','multi') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `[prefix]store_orders`;
CREATE TABLE IF NOT EXISTS `[prefix]store_orders` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `id_customer` int(3) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `id_user` int(3) NOT NULL,
  `user` varchar(255) NOT NULL,
  `total` decimal(11,4) NOT NULL,
  `gid_currency` varchar(20) NOT NULL,
  `products_count` tinyint(4) NOT NULL,
  `id_shipping` int(3) NOT NULL,
  `shipping_name` varchar(100) NOT NULL,
  `shipping_country` varchar(100) NOT NULL,
  `shipping_region` varchar(100) NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_address` varchar(255) NOT NULL,
  `shipping_zip` varchar(20) NOT NULL,
  `contact_phone` varchar(20) NOT NULL,
  `comment` text NOT NULL,
  `status` varchar(30) NOT NULL,
  `is_formed` tinyint(1) NOT NULL,
  `is_alert` tinyint(1) NOT NULL,
  `is_archive` tinyint(1) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `[prefix]store_orders_log`;
CREATE TABLE IF NOT EXISTS `[prefix]store_orders_log` (
  `id_order` int(3) NOT NULL,
  `status` varchar(30) NOT NULL,
  `status_old` varchar(50) NOT NULL,
  `status_new` varchar(50) NOT NULL,
  `comment` text NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]store_orders_products`;
CREATE TABLE IF NOT EXISTS `[prefix]store_orders_products` (
  `id_order` int(3) NOT NULL,
  `id_product` int(3) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `price` decimal(11,4) NOT NULL,
  `gid_currency` varchar(20) NOT NULL,
  `count` smallint(3) NOT NULL,
  `options` text NOT NULL,
  `options_text` text NOT NULL,
  `description` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]store_products`;
CREATE TABLE IF NOT EXISTS `[prefix]store_products` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `gid` varchar(40) NOT NULL,
  `id_user` int(3) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `price` decimal(11,4) NOT NULL,
  `price_reduced` decimal(11,4) NOT NULL,
  `gid_currency` varchar(20) NOT NULL,
  `price_sorting` int(3) NOT NULL,
  `is_bestseller` tinyint(1) NOT NULL,
  `priority` int(3) NOT NULL,
  `search_data` text NOT NULL,
  `photo` text NOT NULL,
  `photo_count` smallint(5) NOT NULL,
  `video` varchar(255) NOT NULL,
  `video_image` varchar(255) NOT NULL,
  `video_data` text NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT `search_data` (`search_data`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `[prefix]store_products_categories`;
CREATE TABLE IF NOT EXISTS `[prefix]store_products_categories` (
  `id_category` int(3) NOT NULL,
  `id_product` int(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]store_shippings`;
CREATE TABLE IF NOT EXISTS `[prefix]store_shippings` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `price` decimal(11,4) NOT NULL,
  `gid_currency` varchar(20) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `[prefix]store_shippings_countries`;
CREATE TABLE IF NOT EXISTS `[prefix]store_shippings_countries` (
  `id_shippings` int(3) NOT NULL,
  `id_country` char(2) NOT NULL,
  `id_region` int(3) NOT NULL,
  `id_city` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `[prefix]store_statistics_products`;
CREATE TABLE IF NOT EXISTS `[prefix]store_statistics_products` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `id_product` int(3) NOT NULL,
  `orders_products` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `[prefix]store_statistics_recipients`;
CREATE TABLE IF NOT EXISTS `[prefix]store_statistics_recipients` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `id_user` int(3) NOT NULL,
  `id_recipient` int(3) NOT NULL,
  `total_orders` smallint(5) NOT NULL,
  `total_price` decimal(11,4) NOT NULL,
  `total_count` smallint(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `[prefix]store_users_shippings`;
CREATE TABLE IF NOT EXISTS `[prefix]store_users_shippings` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `id_user` int(3) NOT NULL,
  `country` char(2) NOT NULL,
  `region` int(3) NOT NULL,
  `city` int(3) NOT NULL,
  `address` varchar(255) NOT NULL,
  `zip` varchar(20) NOT NULL,
  `phone` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;