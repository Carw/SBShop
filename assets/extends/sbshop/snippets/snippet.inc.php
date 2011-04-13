<?php

/**
 * @author Mukharev Maxim
 * @version 0.1a
 * 
 * @desription
 * 
 * Электронный магазин для MODx
 * 
 * Сниппет электронного магазина
 *
 */

if(!defined('MODX_BASE_PATH')) {
	die('What are you doing? Get out here!');
}
/**
 * Если режим задан.
 */
if(isset($mode)) {
	/**
	 * Добавляем указанные режимы
	 */
	$modx->sbshop->addModes(explode(',',$mode));
}
/**
 * Получаем список режимов
 */
$aModes = $modx->sbshop->getModes();
/**
 * Информация о режимах
 */
if($modx->sbshop->config['debug']) {
	echo '<pre><h2>Список режимов</h2><code>';
	var_dump($aModes);
	echo '</code><h2>Полученные данные POST</h2><code>';
	var_dump($_POST);
	echo '</code></pre>';
}
/**
 * Если есть хоть один режим
 */
if(count($aModes) > 0) {
	/**
	 * обрабатываем каждый режим
	 */
	foreach ($aModes as $sMode) {
		/**
		 * Переключатель управляющих действий сниппета
		 * Проверяем существование экшена и если он есть, то передаем ему выполнение
		 */
		$sActionPath = MODX_BASE_PATH . 'assets/extends/sbshop/snippets/modes/' . $sMode . '.php';
		/**
		 * Если файл для выбранного режима есть
		 */
		if(is_file($sActionPath)) {
			/**
			 * Включаем файл
			 */
			include $sActionPath;
			$sModeName = $sMode . '_mode';
			/**
			 * Создаем экземпляр класса режима
			 */
			$oMode = new $sModeName($sMode);
		}
	}
}

?>