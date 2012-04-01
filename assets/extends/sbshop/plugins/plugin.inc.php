<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 * 
 * Плагин интернет-магазина
 */

if(!defined('MODX_BASE_PATH')) {
	die('What are you doing? Get out here!');
}

/**
 * Если ядро SBShop еще не запущено
 */
if(!isset($modx->sbshop)) {
	/**
	 * Подключаем необходимые файлы
	 */
	include MODX_BASE_PATH . 'assets/extends/sbshop/core/core.php';
	/**
	 * Делаем доступ к объекту магазина через системный $modx
	 */
	$modx->sbshop = new SBShop();
	/**
	 * Запускаем инициализацию
	 */
	$modx->sbshop->initialise();
	// Запуск плагинов после инициализации
	$modx->invokeEvent('OnSBShopInit');
}

if(!function_exists('SBShop_OnPageNotFound')) {
	/**
	 * Функция для обработки события OnPageNotFound
	 * @param unknown_type $doc_start
	 */
	function SBShop_OnPageNotFound($doc_start) {
		global $modx;
		/**
		 * Если флаг 404 не установлен
		 */
		if($modx->sbshop->baseRedirect()) {
			/**
			 * Делаем режирект на базовую страницу каталога
			 */
			$modx->sendForward($doc_start);
			/**
			 * Выход, чтобы случайно не сработал повторный редирект
			 */
			exit();
		}
		
	}

	/**
	 * Функция для обработки события OnLoadWebDocument
	 */
	function SBShop_OnLoadWebDocument() {
		global $modx;
		/**
		 * Делаем установку основного шаблона
		 */
		$modx->sbshop->setBaseTemplate();
	}
}

/**
 * Переключатель выбирающий обработчик по запрошенному событию
 */
switch ($modx->event->name) {
    case 'OnPageNotFound':
        /**
    	 * Страница не существует
    	 */
    	SBShop_OnPageNotFound($doc_start);
	    break;
	case 'OnLoadWebDocument':
		/**
		 * Загрузка документа
		 */
		SBShop_OnLoadWebDocument();
		break;
	case 'OnLoadWebPageCache':
		/**
		 * Загрузка документа
		 */
		SBShop_OnLoadWebDocument();
		break;
}

?>