CREATE TABLE `modx_sbshop_attributes` (
  `attribute_id` int(11) unsigned NOT NULL auto_increment,
  `attribute_name` varchar(255) NOT NULL,
  PRIMARY KEY  (`attribute_id`),
  UNIQUE KEY `attribute_name` (`attribute_name`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `modx_sbshop_categories` (
  `category_id` int(11) unsigned NOT NULL auto_increment,
  `category_date_add` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `category_date_edit` timestamp NOT NULL default '0000-00-00 00:00:00',
  `category_title` varchar(255) NOT NULL,
  `category_description` text NOT NULL,
  `category_images` varchar(255) NOT NULL,
  `category_attributes` mediumtext NOT NULL,
  `category_views` int(11) unsigned NOT NULL,
  `category_published` tinyint(1) unsigned NOT NULL default '0',
  `category_deleted` tinyint(1) unsigned NOT NULL default '0',
  `category_order` int(3) unsigned NOT NULL,
  `category_parent` int(11) unsigned NOT NULL default '0',
  `category_alias` varchar(255) NOT NULL,
  `category_path` varchar(255) NOT NULL default '0',
  `category_level` tinyint(2) unsigned NOT NULL default '0',
  `category_url` varchar(255) NOT NULL,
  PRIMARY KEY  (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `modx_sbshop_category_attributes` (
  `category_id` int(11) unsigned NOT NULL,
  `attribute_id` int(11) unsigned NOT NULL,
  `attribute_count` int(11) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `modx_sbshop_customers` (
  `customer_id` int(11) unsigned NOT NULL auto_increment,
  `customer_internalKey` int(11) unsigned NOT NULL default '0',
  `customer_fullname` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(100) NOT NULL,
  `customer_city` varchar(100) NOT NULL,
  `customer_address` varchar(255) NOT NULL,
  PRIMARY KEY  (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `modx_sbshop_option_values` (
  `value_id` int(11) unsigned NOT NULL auto_increment,
  `value_name` varchar(255) NOT NULL,
  PRIMARY KEY  (`value_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `modx_sbshop_options` (
  `option_id` int(11) unsigned NOT NULL auto_increment,
  `option_name` varchar(255) NOT NULL,
  PRIMARY KEY  (`option_id`),
  UNIQUE KEY `option_name` (`option_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `modx_sbshop_orders` (
  `order_id` int(11) unsigned NOT NULL auto_increment,
  `order_user` int(11) unsigned NOT NULL,
  `order_date_add` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `order_date_edit` timestamp NOT NULL default '0000-00-00 00:00:00',
  `order_ip` varchar(15) NOT NULL,
  `order_status` tinyint(3) NOT NULL,
  `order_price` float(10,2) unsigned NOT NULL,
  `order_products` text NOT NULL,
  `order_options` text NOT NULL,
  PRIMARY KEY  (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `modx_sbshop_product_attributes` (
  `product_id` int(11) unsigned NOT NULL,
  `attribute_id` int(11) unsigned NOT NULL,
  `attribute_value` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `modx_sbshop_products` (
  `product_id` int(11) unsigned NOT NULL auto_increment,
  `product_category` int(11) unsigned NOT NULL default '0',
  `product_date_add` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `product_date_edit` timestamp NOT NULL default '0000-00-00 00:00:00',
  `product_title` varchar(255) NOT NULL,
  `product_description` text NOT NULL,
  `product_images` varchar(255) NOT NULL,
  `product_attributes` mediumtext NOT NULL,
  `product_downloads` varchar(255) NOT NULL,
  `product_viewed` int(11) default NULL,
  `product_published` tinyint(1) unsigned NOT NULL default '0',
  `product_deleted` tinyint(1) unsigned NOT NULL default '0',
  `product_order` int(3) unsigned NOT NULL,
  `product_alias` varchar(255) NOT NULL,
  `product_url` varchar(255) NOT NULL,
  `product_sku` varchar(100) NOT NULL,
  `product_price` float(15,2) unsigned NOT NULL,
  `product_quantity` smallint(5) unsigned NOT NULL,
  `product_options` mediumtext NOT NULL,
  PRIMARY KEY  (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=1;