<?php

/**
 * @author Mukharev Maxim
 * @version 0.1a
 * 
 * @desription
 * 
 * Электронный магазин для MODx
 * 
 * Модуль электронного магазина
 */

if(!defined('MODX_BASE_PATH')) {
	die('What are you doing? Get out here!');
}

/**
 * Параметры работы модуля
 */
$iModId = intval($_REQUEST['id']);
$iManAction = $_REQUEST['a'];
$sMode = (isset($_REQUEST['mode']) and $_REQUEST['mode'] != '') ? $_REQUEST['mode'] : 'home';
$sAct = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
$sModuleLink = MODX_MANAGER_URL . 'index.php?a=' . $iManAction . '&id=' . $iModId;
/**
 * Подключаем необходимые файлы
 */
include MODX_BASE_PATH . 'assets/extends/sbshop/core/core.php';

$modx->sbshop = new SBShop();

/**
 * Режим дебагинга
 */
if($modx->sbshop->config['debug']) {
	echo '<pre><h2>Список режимов</h2><code>';
	var_dump('Debug. Режим: ' . $sMode . ', действие: ' . $sAct);
	echo '</code><h2>Полученные данные GET</h2><code>';
	var_dump($_GET);
	echo '</code><h2>Полученные данные POST</h2><code>';
	var_dump($_POST);
	echo '</code></pre>';
}

echo '<html>
<head>
<link rel="stylesheet" type="text/css" href="media/style/MODxCarbon/style.css" />
<link rel="stylesheet" type="text/css" href="' . MODX_SITE_URL . 'assets/libs/javascript/css/ui-lightness/jquery-ui-1.8.4.custom.css" />
<link rel="stylesheet" type="text/css" href="' . MODX_SITE_URL . 'assets/extends/sbshop/modules/templates/css/style.css" />
<script type="text/javascript" src="media/script/tabpane.js"></script>

<style>
	.error {
		color:#990000;
		font-weight:bold;
		padding: 5px 10px;
		margin: 0 10px 10px 10px;
		border: 1px solid #990000;
		background-color:#FFEEEE;
	}
</style>
</head>
<body>

<script type="text/javascript">
var modid = ' . $iModId . ';
var modurl = "' . MODX_SITE_URL . 'assets/libs/treebuilder/treebuilder.frame.php?conf=sbshop/tree.inc.php&modid=' . $iModId . '";
if(top.tree.location != modurl) {
	top.tree.ca = "open";
	top.tree.location = modurl;
} else {
	top.tree.treeRebuild();
}
</script>';

/**
 * Переключатель управляющих действий модуля
 * Проверяем существование экшена и если он есть, то передаем ему выполнение
 */
$sModePath = MODX_BASE_PATH . 'assets/extends/sbshop/modules/modes/' . $sMode . '.mode.php';
if(is_file($sModePath)) {
	include $sModePath;
	$sModeName = $sMode . '_mode';
        $oMode = new $sModeName($sModuleLink,$sMode,$sAct);
}

echo '</body>
</html>';

?>