<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maxim
 * Date: 07.01.12
 * Time: 1:37
 * To change this template use File | Settings | File Templates.
 */


/**
 * Подключаем необходимые файлы
 */
require_once '../../../../manager/includes/protect.inc.php';
include_once ('../../../../manager/includes/config.inc.php');

/**
 * Запускаем сессию
 */
if (isset($_REQUEST[$site_sessionname])) {
	session_id($_REQUEST[$site_sessionname]);
}
startCMSSession();

/**
 * Если менеджер не авторизован
 */
if (!$_SESSION['mgrValidated']) {
	/**
	 * Убиваем процесс
	 */
	die('Unauthorized access');
}

include_once (MODX_BASE_PATH.'manager/includes/document.parser.class.inc.php');
include_once MODX_BASE_PATH . 'assets/extends/sbshop/ajax/ajax.inc.php';
include_once MODX_BASE_PATH . 'assets/extends/sbshop/core/core.php';

$aParams = array();

/**
 * Обрабатываем полученные данные
 */
$aData = array_merge($_GET, $_POST);
foreach ($aData as $sKey => $sVal) {
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
$modx = new DocumentParser;
$modx->getSettings();
/**
 * Подключаем ядро SBShop
 */
$modx->sbshop = new SBShop();
/**
 * Создаем объект для работы с Ajax
 */
$oAjax = new SBAjax($sMethod, $aParams);
/**
 * Выводим результат
 */
echo htmlspecialchars(json_encode($oAjax->result()), ENT_NOQUOTES);