<?php

/**
 * @author Mukharev Maxim
 * @version 0.1a
 *
 * @desription
 *
 * Электронный магазин для MODx
 *
 * Объект списка опций
 */

class SBOptionList {

	protected $aOptionList; // Список опций для товара

	/**
	 * Конструктор
	 */
	public function  __construct() {
		/**
		 * Инициализируем массив
		 */
		$this->aOptionList = array();
	}

	public function getOptionNames() {
		$aNames = array();
		if(count($this->aOptionList) > 0) {
			foreach ($this->aOptionList as $aOption) {
				unset($aOption['values']);
				$aNames[$aOption['title']] = $aOption;
			}
		}
		return $aNames;
	}

	public function getValuesByOptionName($sName) {
		/**
		 * разбираем каждую опцию
		 */
		foreach ($this->aOptionList as $aOption) {
			if($aOption['title'] == $sName) {
				return $aOption['values'];
			}
		}
	}

	/**
	 * Является ли опция скрытой
	 * @param <type> $iNameId
	 */
	public function isOptionHidden($iNameId) {
		return $this->aOptionList[$iNameId]['hidden'];
	}

	/**
	 * Получение значения по набору идентификаторов опции и значения
	 * @param <type> $iNameId
	 * @param <type> $iValueId
	 * @return <type>
	 */
	public function getValueByNameIdAndValId($iNameId,$iValueId) {
		/**
		 * Возвращаем значение
		 */
		return $this->aOptionList[$iNameId]['values'][$iValueId]['value'];
	}

	/**
	 * 
	 * @param <type> $iNameId
	 * @param <type> $iValueId
	 */
	public function getNamesByNameIdAndValId($iNameId,$iValueId) {
		/**
		 * Результат
		 */
		$aResult = array (
			'title' => $this->aOptionList[$iNameId]['title'],
			'value' => $this->aOptionList[$iNameId]['values'][$iValueId]['title'],
		);
		/**
		 * Возвращаем результат
		 */
		return $aResult;
	}

	/**
	 * Сериализация имеющихся опций
	 * @param <type> $sOptions
	 */
	public function serializeOptions() {
		/**
		 * Возвращаем результат
		 */
		return base64_encode(serialize($this->aOptionList));
	}

	public function unserializeOptions($sOptions) {
		/**
		 * Если строка пуста
		 */
		if(!$sOptions) {
			/**
			 * Возвращаем false
			 */
			return;
		}
		/**
		 * Десериализуем
		 */
		$aOptions = unserialize(base64_decode($sOptions));
		/**
		 * Запоминаем
		 */
		$this->aOptionList = $aOptions;
		/**
		 * Возвращаем результат
		 */
		return $aOptions;
	}

	/**
	 * Добавить опцию
	 * @param <type> $title
	 * @param <type> $aValues
	 */
	public function add($aOption, $aValues = array()) {
		/**
		 * Если не задан заголовок или опций
		 */
		if($aOption['title'] == '' || !is_array($aValues)) {
			return false;
		}
		/**
		 * Добавляем новую запись
		 */
		$this->aOptionList[] = array_merge($aOption,array('values' => $aValues));
	}

	/**
	 * Обобщение информации об опциях и значениях
	 */
	public function optionGeneralization() {
		/**
		 * Массив названий опций
		 */
		$aOptionNames = array();
		/**
		 * Массив названий значений
		 */
		$aValueNames = array();
		/**
		 * Обрабатываем каждую опцию
		 */
		foreach ($this->aOptionList as $aOption) {
			/**
			 * Добавляем имя опции
			 */
			$aOptionNames[$aOption['title']] = $aOption['id'];
			/**
			 * Обрабатываем каждое значение
			 */
			foreach ($aOption['values'] as $aValue) {
				$aValueNames[$aValue['title']] = $aValue['id'];
			}
		}
		/**
		 * Отправляем массив названий опций на обобщение
		 */
		$aOptionNames = $this->optionNamesGeneralization($aOptionNames);
		/**
		 * Отправляем массив названий значений на обобщение
		 */
		$aValueNames = $this->optionValuesGeneralization($aValueNames);
		/**
		 * Исправленный массив опций
		 */
		$aNewOptions = array();
		/**
		 * Обрабатываем вновь каждую опцию
		 */
		foreach ($this->aOptionList as $aOption) {
			/**
			 * Обновленные значения
			 */
			$aNewValues = array();
			/**
			 * Обрабатываем каждое значение
			 */
			foreach ($aOption['values'] as $aValue) {
				$aNewValues[$aValueNames[$aValue['title']]] = $aValue;
				/**
				 * Исправляем идентификатор
				 */
				$aNewValues[$aValueNames[$aValue['title']]]['id'] = $aValueNames[$aValue['title']];
			}
			/**
			 * Добавляем новое значение опции
			 */
			$aNewOptions[$aOptionNames[$aOption['title']]] = $aOption;
			/**
			 * Исправляем идентификатор
			 */
			$aNewOptions[$aOptionNames[$aOption['title']]]['id'] = $aOptionNames[$aOption['title']];
			/**
			 * Исправляем значения
			 */
			$aNewOptions[$aOptionNames[$aOption['title']]]['values'] = $aNewValues;
		}
		/**
		 * Сохраняем результат обработки
		 */
		$this->aOptionList = $aNewOptions;
	}

	/**
	 * Обобщение информации о названиях опций
	 * @param <type> $aOptions
	 */
	protected function optionNamesGeneralization($aOptionNames) {
		global $modx;
		/**
		 * Если массив значений пуст
		 */
		if(!$aOptionNames) {
			return;
		}
		/**
		 * Запрос в базу с коллекцией опций
		 */
		$sOptionNames = '"' . implode('","', array_keys($aOptionNames)) . '"';
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_options'),'option_name in (' . $sOptionNames . ')');
		$aRows = $modx->db->makeArray($rs);
		/**
		 * Обрабатываем записи
		 */
		$aOldOptions = array();
		foreach ($aRows as $aRow) {
			$aOldOptions[$aRow['option_name']] = $aRow['option_id'];
		}
		/**
		 * Вычисляем новые опции
		 */
		$aNewOptions = array_diff_key($aOptionNames, $aOldOptions);
		/**
		 * Если есть новые опции
		 */
		if(count($aNewOptions) > 0) {
			/**
			 * Обрабатываем каждую запись
			 */
			foreach ($aNewOptions as $sKey => $aVal) {
				/**
				 * Заносим в базу
				 */
				$rs = $modx->db->insert(array('option_name' => $sKey),$modx->getFullTableName('sbshop_options'));
				/**
				 * Добавляем информацию в массив старых опций
				 */
				$aOldOptions[$sKey] = $modx->db->getInsertId();
			}
		}
		/**
		 * Возвращаем общий массив
		 */
		return $aOldOptions;
	}

	/**
	 * Обобщение информации о названиях опций
	 * @param <type> $aOptions
	 */
	public function optionValuesGeneralization($aOptionValues) {
		global $modx;
		/**
		 * Если массив значений пуст
		 */
		if(!$aOptionValues) {
			return;
		}
		/**
		 * Запрос в базу с коллекцией опций
		 */
		$sOptionValues = '"' . implode('","', array_keys($aOptionValues)) . '"';
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_option_values'),'value_name in (' . $sOptionValues . ')');
		$aRows = $modx->db->makeArray($rs);
		/**
		 * Обрабатываем записи
		 */
		$aOldValues = array();
		foreach ($aRows as $aRow) {
			$aOldValues[$aRow['value_name']] = $aRow['value_id'];
		}
		/**
		 * Вычисляем новые опции
		 */
		$aNewValues = array_diff_key($aOptionValues, $aOldValues);
		/**
		 * Если есть новые опции
		 */
		if(count($aNewValues) > 0) {
			/**
			 * Обрабатываем каждую запись
			 */
			foreach ($aNewValues as $sKey => $aVal) {
				/**
				 * Заносим в базу
				 */
				$rs = $modx->db->insert(array('value_name' => $sKey),$modx->getFullTableName('sbshop_option_values'));
				/**
				 * Добавляем информацию в массив старых опций
				 */
				$aOldValues[$sKey] = $modx->db->getInsertId();
			}
		}
		/**
		 * Возвращаем общий массив
		 */
		return $aOldValues;
	}


}

?>
