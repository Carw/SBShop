<?php

/**
 * @author Mukharev Maxim
 * @version 0.1a
 *
 * @desription
 *
 * Электронный магазин для MODx
 *
 * Сниппет для вывода определенной категории
 */

if(!defined('MODX_BASE_PATH')) {
	die('What are you doing? Get out here!');
}

include_once MODX_BASE_PATH . 'assets/extends/sbshop/snippets/modes/categories.php';

$oCatMode = new categories_mode('category',false,$catId);

echo $oCatMode->outputInnerCat();

?>
