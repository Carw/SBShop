<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 *
 * Сниппет для вывода определенного раздела
 */

if(!defined('MODX_BASE_PATH')) {
	die('What are you doing? Get out here!');
}

include_once MODX_BASE_PATH . 'assets/extends/sbshop/snippets/modes/categories.php';

$oCatMode = new categories_mode('category',false,$catId);

echo $oCatMode->outputInnerCat();

?>
