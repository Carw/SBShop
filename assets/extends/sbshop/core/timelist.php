<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 *
 * Класс списка информации с отметкой времени
 */

class SBTimeList {
	
	protected $aTimeList; // Массив информации, где ключ - отметка времени

	/**
	 * Конструктор
	 */
	public function  __construct() {
		/**
		 * Инициализация массива с данными
		 */
		$this->aTimeList = array();
	}

	/**
	 * Сериализация данных
	 */
	public function serialize() {
		return serialize($this->aTimeList);
	}

	/**
	 * Десериализация данных
	 */
	public function unserialize($sParams = '') {
		/**
		 * Если строка пуста
		 */
		if(!$sParams) {
			/**
			 * Возвращаем false
			 */
			return;
		}
		/**
		 * Десериализуем
		 */
		$this->aTimeList = unserialize($sParams);
		/**
		 * Возвращаем результат
		 */
		return $this->aTimeList;
	}

	/**
	 * Добавляем строку с данными
	 * @param string $sData
	 * @return timestamp
	 */
	public function add($sData = false) {
		/**
		 * Если значение не передано
		 */
		if(!$sData) {
			return false;
		}
		/**
		 * Метка времени
		 */
		$iTime = time();
		/**
		 * Массив замен в тексте комментария
		 */
		$aRepl = array(
			'\r\n' => '<br>',
			'\"' => '&quot;',
			"\'" => '&quot;',
		);
		/**
		 * Делаем замену
		 */
		$sData = str_replace(array_keys($aRepl), array_values($aRepl), $sData);
		/**
		 * Добавляем значение
		 */
		$this->aTimeList[$iTime] = $sData;
		/**
		 * Возвращаем метку времени
		 */
		return $iTime;
	}

	/**
	 * Получение первого значения
	 * @return <type>
	 */
	public function getFirst() {
		return array_shift($this->aTimeList);
	}

	/**
	 * Получение полного списка записей
	 */
	public function getAll() {
		return $this->aTimeList;
	}

}

?>