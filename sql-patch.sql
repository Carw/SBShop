ALTER TABLE  `modx_sbshop_products` ADD  `product_vendor` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE  `modx_sbshop_products` ADD  `product_model` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE  `modx_sbshop_products` ADD  `product_introtext` VARCHAR( 512 ) NOT NULL ;

ALTER TABLE  `modx_sbshop_orders` ADD  `order_comments` TEXT NOT NULL ;

ALTER TABLE  `modx_sbshop_products` ADD  `product_bundling` MEDIUMTEXT NOT NULL ;
ALTER TABLE  `modx_sbshop_products` ADD  `product_existence` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1';

CREATE TABLE `modx_sbshop_product_tips` (
  `tip_id` int(11) unsigned NOT NULL auto_increment,
  `tip_title` varchar(255) NOT NULL,
  `tip_description` mediumtext NOT NULL,
  PRIMARY KEY  (`tip_id`)
) ENGINE=MyISAM;

ALTER TABLE `modx_sbshop_products` DROP `product_downloads`, DROP `product_quantity`;
ALTER TABLE  `modx_sbshop_products` CHANGE `product_bundling` `product_bundles` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE  `modx_sbshop_products` ADD `product_base_bundle` VARCHAR( 255 ) NOT NULL AFTER `product_bundles`;

ALTER TABLE  `modx_sbshop_categories` ADD  `category_longtitle` VARCHAR( 255 ) NOT NULL AFTER  `category_title` ;
ALTER TABLE  `modx_sbshop_products` ADD  `product_longtitle` VARCHAR( 255 ) NOT NULL AFTER  `product_title` ;
ALTER TABLE  `modx_sbshop_product_attributes` ADD  `attribute_measure` VARCHAR( 10 ) NOT NULL ;
ALTER TABLE  `modx_sbshop_categories` ADD  `category_filters` MEDIUMTEXT NOT NULL AFTER  `category_attributes` ;