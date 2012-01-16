ALTER TABLE  `modx_sbshop_products` ADD  `product_price_add` VARCHAR( 10 ) NOT NULL AFTER  `product_price`;

ALTER TABLE  `modx_sbshop_orders` CHANGE  `order_date_add`  `order_date_add` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE  `modx_sbshop_products` CHANGE  `product_images`  `product_images` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;