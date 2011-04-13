<?php

/**
 * @author Mukharev Maxim
 * @version 0.1a
 *
 * @desription
 *
 * Электронный магазин для MODx
 *
 * Объект список комплектаций
 */

class SBBundleList {

	/**
	 * @todo
	 * - название комплектации
	 * - цена комплектации
	 * - набор включенных опций
	 */

	protected $aBundleList;

	/**
	 * Конструктор
	 */
	public function  __construct() {
		/**
		 * Инициализация массива комлпектаций
		 */
		$this->aBundleList = array();
	}

	/**
	 * Сериализация данных
	 * @return <type>
	 */
	public function serialize() {
		return base64_encode(serialize($this->aBundleList));
	}

	/**
	 * Десериализация данных
	 * @param <type> $sParams
	 * @return <type>
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
		$this->aBundleList = unserialize(base64_decode($sParams));
		/**
		 * Возвращаем результат
		 */
		return $this->aBundleList;
	}
	
	/**
	 * Добавить комплектацию в список
	 * @param <type> $sName
	 * @param <type> $sOptions
	 * @param <type> $fPrice
	 * @param <type> $sDescription
	 * @param <type> $sId
	 * @return <type>
	 */
	public function add($sName, $sOptions, $fPrice = false, $sDescription = '', $sId = false) {
		/**
		 * Если не указан заголовок, или пуст список опций
		 */
		if($sName == '' or $sOptions == '') {
			/**
			 * Выходим
			 */
			return false;
		}
		/**
		 * Массив опций
		 */
		$aOptions = $this->parse($sOptions);
		/**
		 * Если есть настройки опций
		 */
		if(count($aOptions) > 0) {
			/**
			 * Если установлен идентификатор
			 */
			if($sId) {
				/**
				 * делаем запись с заданным идентификатором
				 */
				$this->aBundleList[$sId] = array(
					'title' => $sName,
					'price' => $fPrice,
					'options' => $aOptions,
					'description' => $sDescription
				);
			} else {
				/**
				 * делаем запись автоматическим идентификатором
				 */
				$this->aBundleList[] = array(
					'title' => $sName,
					'price' => $fPrice,
					'options' => $aOptions,
					'description' => $sDescription
				);
			}
		}
	}

	/**
	 * Получить список опций
	 */
	public function getList() {
		return $this->aBundleList;
	}

	/**
	 * Получить комплектацию по идентификатору
	 */
	public function getById($iId) {
		return $this->aBundleList[$iId];
	}

	/**
	 * Получить список опций в комплектации по идентификатору
	 * @param <type> $iId
	 * @return <type>
	 */
	public function getOptionsById($iId) {
		return $this->aBundleList[$iId]['options'];
	}

	/**
	 * Парсинг значений
	 * @param <type> $sParams
	 */
	public function parse($sOptions) {
		/**
		 * Массив опций
		 */
		$aOptions = array();
		/**
		 * Разбиваем список опций
		 */
		$aOptionRows = explode(',', $sOptions);
		/**
		 * Разбираем ряды
		 */
		foreach ($aOptionRows as $sRow) {
			/**
			 * Разбиваем на название и занчение опции
			 */
			list($sOptionName,$sOptionVal) = explode(':', $sRow);
			/**
			 * Добавляем в массив, преобразуя в числа
			 */
			$aOptions[intval($sOptionName)] = intval($sOptionVal);
		}
		return $aOptions;
	}

}

?>