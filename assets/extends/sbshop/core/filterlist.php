<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
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
			 * Разбиваем список фильтров
			 */
			$aFilterRaws = explode(';', $aQueries['filter']);
			/**
			 * Обрабатываем каждый фильтр
			 */
			foreach($aFilterRaws as $sFilterRaw) {
				list($sFilterId, $sFilterValue) = explode('::', $sFilterRaw);
				/**
				 * Если такой фильтр есть в настройках
				 */
				if(isset($this->aFilterList['general'][$sFilterId])) {
					/**
					 * Данные фильтра
					 */
					$aFilter = $this->aFilterList['general'][$sFilterId];
					$sFilterSrc = 'general';
				} elseif (isset($this->aFilterList['extended'][$sFilterId])) {
					$aFilter = $this->aFilterList['extended'][$sFilterId];
					$sFilterSrc = 'extended';
				} else {
					continue;
				}
				/**
				 * Если тип фильтра "eqv"
				 */
				if($aFilter['type'] === 'eqv' or $aFilter['type'] === 'rng') {
					/**
					 * Если значение существует
					 */
					if(isset($aFilter['values'][$sFilterValue])) {
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
							$aFilters[$sFilterSrc][$sFilterId] = $aFilter['values'][$sFilterValue];
							$aFilters[$sFilterSrc][$sFilterId]['type'] = $aFilter['type'];
						}
					} else {
						continue;
					}
				} elseif($aFilter['type'] === 'vrng') {
					/**
					 * Получаем ключ первого значения
					 */
					$aFilterKeys = array_keys($aFilter['values']);
					/**
					 * Первый ключ
					 */
					$sFilterKey = $aFilterKeys[0];
					/**
					 * Разбиваем значение на минимум-максимум
					 */
					list($sMinValue, $sMaxValue) = explode('-', $sFilterValue);
					/**
					 * Проверяем минимальное значение
					 */
					if(intval($sMinValue) < $aFilter['values'][$sFilterKey]['min']) {
						$iMinValue = $aFilter['values'][$sFilterKey]['min'];
					} else {
						$iMinValue = intval($sMinValue);
					}
					/**
					 * Проверяем максимальное значение
					 */
					if(intval($sMaxValue) > intval($aFilter['values'][$sFilterKey]['max'])) {
						$iMaxValue = $aFilter['values'][$sFilterKey]['max'];
					} else {
						$iMaxValue = intval($sMaxValue);
					}
					/**
					 * Если минимальное значение больше максимального
					 */
					if($iMinValue > $iMaxValue) {
						$iTmp = $iMinValue;
						$iMinValue = $iMaxValue;
						$iMaxValue = $iTmp;
					}
					/**
					 * Если указан компактный режим
					 */
					if($bCompact) {
						/**
						 * Не указываем тип фильтра
						 */
						$aFilters[$sFilterId] = $iMinValue . '-' . $iMaxValue;
					} else {
						/**
						 * Указываем в результате тип фильтра
						 */
						$aFilters[$sFilterSrc][$sFilterId] = array(
							'min' => $iMinValue,
							'max' => $iMaxValue,
							'type' => $aFilter['type']
						);
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
