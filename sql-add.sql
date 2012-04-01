ALTER TABLE  `modx_sbshop_products` ADD  `product_price_add` VARCHAR( 10 ) NOT NULL AFTER  `product_price`;

ALTER TABLE  `modx_sbshop_orders` CHANGE  `order_date_add`  `order_date_add` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE  `modx_sbshop_products` CHANGE  `product_images`  `product_images` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE  `modx_sbshop_products` ADD  `product_files` TEXT NOT NULL AFTER  `product_images`;

INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopInit', '9', 'SBShop - core');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopModeInit', '9', 'SBShop - core');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopModeComplete', '9', 'SBShop - core');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCartBeforeClear', '9', 'SBShop - cart');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCartAfterClear', '9', 'SBShop - cart');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCartBeforeAddProduct', '9', 'SBShop - cart');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCartAfterAddProduct', '9', 'SBShop - cart');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCartProductPrerender', '9', 'SBShop - cart');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCartOrderPrerender', '9', 'SBShop - cart');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCheckoutProductPrerender', '9', 'SBShop - checkout');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCheckoutOrderPrerender', '9', 'SBShop - checkout');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCheckoutBeforeClear', '9', 'SBShop - checkout');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCheckoutAfterClear', '9', 'SBShop - checkout');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCheckoutBeforeMailSend', '9', 'SBShop - checkout');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCheckoutBeforeClientAdd', '9', 'SBShop - checkout');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCheckoutBeforeOrderComplete', '9', 'SBShop - checkout');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCheckoutBeforeProducsDelete', '9', 'SBShop - checkout');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCheckoutBeforeQuantityChange', '9', 'SBShop - checkout');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCategorySubcategoryPrerender', '9', 'SBShop - category');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopCategoryProductPrerender', '9', 'SBShop - category');
INSERT INTO `modx_system_eventnames` (`name`, `service`, `groupname`) VALUES ('OnSBShopProductPrerender', '9', 'SBShop - product');