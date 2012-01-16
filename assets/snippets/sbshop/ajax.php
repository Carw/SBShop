<?php
/**
 * @author Mukharev Maxim
 * @version 0.1a
 *
 * @desription
 *
 * Электронный магазин для MODx
 *
 * Интерфейс для работы с Ajax
 */

if(!defined('MODX_BASE_PATH')) {
	die('What are you doing? Get out here!');
}

/**
 * Подключаем необходимый файл
 */
include_once MODX_BASE_PATH . 'assets/extends/sbshop/ajax/ajax.inc.php';

$aParams = array();

/**
 * Обрабатываем полученные данные
 */
foreach ($_POST as $sKey => $sVal) {
	switch ($sKey) {
		case 'q':
			break;
		case 'm':
			$sMethod = $sVal;
			break;
		default:
			$aParams[$sKey] = $sVal;
			break;
	}
}

/**
 * Подключаем ядро MODx
 */
include_once(MODX_BASE_PATH . 'manager/includes/document.parser.class.inc.php');
/**
 * Создаем объект MODx
 */
$modx = new DocumentParser;
/**
 * Создаем объект для работы с Ajax
 */
$oAjax = new SBAjax($sMethod, $aParams);
/**
 * Выводим результат
 */
echo htmlspecialchars(json_encode($oAjax->result()), ENT_NOQUOTES);

?>
