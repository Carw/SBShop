<?php
/**
 * @author Mukharev Maxim
 * @version 0.1a
 *
 * @desription
 *
 * Электронный магазин для MODx
 *
 * Класс управляющий доступными методами Ajax
 */

class SBAjax {

	protected $sMethod;
	protected $aParams;
	protected $aResult;

	public function  __construct($sMethod,$aParams) {
		/**
		 * Записываем метод
		 */
		$this->sMethod = $sMethod;
		/**
		 * Полученные параметры
		 */
		$this->aParams = $aParams;
		/**
		 * Собираем название метода
		 */
		$sMethodName = $sMethod . 'Ajax';
		/**
		 * Если такой метод есть в классе
		 */
		if(method_exists($this,$sMethodName)) {
			/**
			 * Вызываем
			 */
			$this->$sMethodName();
		}
	}

	protected function tipAjax() {
		global $modx;

		$iTipId = intval($this->aParams['tid']);
		
		if($iTipId) {
			/**
			 * Подключаем класс для работы с подсказками
			 */
			include_once MODX_BASE_PATH . 'assets/extends/sbshop/core/tip.php';
			/**
			 * Создаем объект
			 */
			$oTip = new SBTip();
			/**
			 * Загружаем подсказку
			 */
			$oTip->load($iTipId);
			/**
			 * Устанавливаем данные
			 */
			$this->aResult['title'] = $oTip->getAttribute('title');
			$this->aResult['description'] = $oTip->getAttribute('description');
		}
	}

	public function result() {
		return $this->aResult;
	}

}

?>
