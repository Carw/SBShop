ALTER TABLE  `modx_sbshop_products` ADD  `product_personal_bundle` VARCHAR( 255 ) NOT NULL AFTER  `product_base_bundle`;

ALTER TABLE  `modx_sbshop_categories` ADD  `category_count` INT( 5 ) UNSIGNED NOT NULL AFTER  `category_options`;
