<?php
/**
 * @author Mukharev Maxim
 * @version 0.1a
 *
 * @desription
 *
 * Электронный магазин для MODx
 *
 * Класс для управления списком фильтров
 */
 
class SBFilterList {
	/**
	 * Массив фильтров
	 */
	protected $aFilterList;

	public function __construct() {
		/**
		 * Инициализация
		 */
		$this->aFilterList = array(
			'general' => array(),
			'extended' => array(),
		);
	}

	/**
	 * Добавить фильтр
	 * @param  $aOption
	 * @param array $aValues
	 * @return bool
	 */
	public function add($aFilter, $aValues = array()) {
		/**
		 * Если не задан идентификатор или массив значений
		 */
		if($aFilter['id'] === '' || !is_array($aValues)) {
			return false;
		}
		/**
		 * Добавляем значения
		 */
		$aFilter['values'] = $aValues;
		/**
		 * Если это идентификатор не числовой, значит фильтр делает по основному полю
		 */
		if(!is_numeric($aFilter['id'])) {
			/**
			 * Добавляем новую запись в основные поля
			 */
			$this->aFilterList['general'][$aFilter['id']] = $aFilter;
		} else {
			/**
			 * Добавляем новую запись в дополнительные поля
			 */
			$this->aFilterList['extended'][$aFilter['id']] = $aFilter;
		}
	}

	/**
	 * Получить настройку фильтра по его идентификатору
	 * @return void
	 */
	public function getFilterById($sFilterId) {
		if(isset($this->aFilterList['general'][$sFilterId])) {
			return $this->aFilterList['general'][$sFilterId];
		} elseif(isset($this->aFilterList['extended'][$sFilterId])) {
			return $this->aFilterList['extended'][$sFilterId];
		} else {
			return false;
		}
	}

	/**
	 * Получение списка идентификаторов полей фильтров
	 * @return array
	 */
	public function getFilterIds() {
		if(isset($this->aFilterList['general'])) {
			$aGeneralIds = array_keys($this->aFilterList['general']);
		} else {
			$aGeneralIds = array();
		}
		if(isset($this->aFilterList['extended'])) {
			$aExtendedIds = array_keys($this->aFilterList['extended']);
		} else {
			$aExtendedIds = array();
		}
		return array_merge($aGeneralIds, $aExtendedIds);
	}

	/**
	 * Получение списка идентификаторов расширенных параметров
	 * @return void
	 */
	public function getGeneralFilterIds() {
		return array_keys($this->aFilterList['general']);
	}

	/**
	 * Получение списка идентификаторов расширенных параметров
	 * @return void
	 */
	public function getExtendedFilterIds() {
		return array_keys($this->aFilterList['extended']);
	}

	/**
	 * Получение списка названий полей фильтров
	 * @return void
	 */
	public function getFilterNames() {
		global $modx;
		/**
		 * Массив названий
		 */
		$aFilterNames = array();
		/**
		 * Основные параметры
		 */
		foreach($this->aFilterList['general'] as $sFilterId => $aFilter) {
			$aFilterNames[$sFilterId] = $modx->sbshop->lang['category_filter_name_' . $sFilterId];
		}
		/**
		 * Добавляем расширенные параметры
		 */
		if(isset($this->aFilterList['extended'])) {
			$aFilterExtendNames = SBAttributeCollection::getAttributeNamesByIds(array_keys($this->aFilterList['extended']));
			$aFilterNames = $aFilterNames + $aFilterExtendNames;
		}
		/**
		 * Возвращаем
		 */
		return $aFilterNames;
	}

	/**
	 * Получение данных выбора фильтров
	 * @return bool
	 */
	public function getFilterSelected($bCompact = false) {
		global $modx;
		/**
		 * Получаем все переданные параметры
		 */
		$aQueries = $modx->sbshop->getQueries();
		/**
		 * Если переданы данные по фильтру
		 */
		if(isset($aQueries['filter'])) {
			/**
			 * Массив параметров фильтров
			 */
			$aFilters = array();
			/**
			 * Разбиваем фильтр на массив значений
			 */
			$aFilterRaws = explode(';',$aQueries['filter']);
			foreach($aFilterRaws as $sFilterRaw) {
				list($sFilterId,$sFilterValue) = explode('::',$sFilterRaw);
				/**
				 * Если такой фильтр есть в настройках
				 */
				if(isset($this->aFilterList['general'][$sFilterId]['values'][$sFilterValue])) {
					/**
					 * Если указан компактный режим
					 */
					if($bCompact) {
						/**
						 * Не указываем тип фильтра
						 */
						$aFilters[$sFilterId] = $sFilterValue;
					} else {
						/**
						 * Указываем в результате тип фильтра
						 */
						$aFilters['general'][$sFilterId] = $this->aFilterList['general'][$sFilterId]['values'][$sFilterValue];
						$aFilters['general'][$sFilterId]['type'] = $this->aFilterList['general'][$sFilterId]['type'];
					}
				} elseif(isset($this->aFilterList['extended'][$sFilterId]['values'][$sFilterValue])) {
					/**
					 * Если указан компактный режим
					 */
					if($bCompact) {
						/**
						 * Не указываем тип фильтра
						 */
						$aFilters[$sFilterId] = $sFilterValue;
					} else {
						/**
						 * Указываем в результате тип фильтра
						 */
						$aFilters['extended'][$sFilterId] = $this->aFilterList['extended'][$sFilterId]['values'][$sFilterValue];
						$aFilters['extended'][$sFilterId]['type'] = $this->aFilterList['extended'][$sFilterId]['type'];
					}
				}
			}
			return $aFilters;
		} else {
			return false;
		}
	}

	/**
	 * Сериализация имеющихся фильтров
	 * @param <type> $sOptions
	 */
	public function serializeFilters() {
		/**
		 * Возвращаем результат
		 */
		return base64_encode(serialize($this->aFilterList));
	}

	/**
	 * Десериализация данных фильтров
	 * @param  $sFilters
	 * @return mixed
	 */
	public function unserializeFilters($sFilters) {
		/**
		 * Если строка пуста
		 */
		if(!$sFilters) {
			/**
			 * Выходим
			 */
			return;
		}
		/**
		 * Десериализуем
		 */
		$aFilters = unserialize(base64_decode($sFilters));
		/**
		 * Запоминаем
		 */
		$this->aFilterList = $aFilters;
		/**
		 * Возвращаем результат
		 */
		return $aFilters;
	}


}
