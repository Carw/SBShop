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
	protected $aFilterSelected;

	public function __construct() {
		/**
		 * Инициализация
		 */
		$this->aFilterList = array(
			'general' => array(),
			'extended' => array(),
		);
		/**
		 * Массив активных фильтров
		 */
		$this->aFilterSelected = array();
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
	 * Получить полный список активных фильтров
	 * @return array
	 */
	public function getFilterSelected() {
		return $this->aFilterSelected;
	}

	/**
	 * Получить список основных фильтров
	 * @return array
	 */
	public function getGeneralSelectedList() {
		/**
		 * Массив основных фильтров
		 */
		$aFilterIds = array();
		/**
		 * Обрабатываем каждый ключ в массиве активных фильтров
		 */
		foreach(array_keys($this->aFilterSelected) as $sFilterId) {
			if(isset($this->aFilterList['general'][$sFilterId])) {
				$aFilterIds[$sFilterId] = $this->aFilterSelected[$sFilterId];
			}
		}
		/**
		 * Возвращаем массив значений
		 */
		return $aFilterIds;
	}

	/**
	 * Список дополнительных фильтров
	 * @return array
	 */
	public function getExtendedSelectedList() {
		/**
		 * Массив основных фильтров
		 */
		$aFilterIds = array();
		/**
		 * Обрабатываем каждый ключ в массиве активных фильтров
		 */
		foreach(array_keys($this->aFilterSelected) as $sFilterId) {
			if(isset($this->aFilterList['extended'][$sFilterId])) {
				$aFilterIds[$sFilterId] = $this->aFilterSelected[$sFilterId];
			}
		}
		/**
		 * Возвращаем массив значений
		 */
		return $aFilterIds;
	}

	/**
	 * Установка информации об активных фильтрах
	 * @return bool
	 */
	public function setFilterSelected() {
		global $modx;
		/**
		 * Получаем все переданные параметры
		 */
		$aQueries = $modx->sbshop->getQueries();
		/**
		 * Переменная для актуального URL с активными фильтрами
		 */
		$aParamURL = array();
		/**
		 * Редирект на ссылку с установленным фильтром
		 */
		$sRedirect = false;
		/**
		 * Массив параметров фильтров
		 */
		$aFilters = array();
		/**
		 * Если передано значение через POST
		 */
		if($_POST['filter']) {
			/**
			 * Разбираем каждый фильтр
			 */
			foreach($_POST['filter'] as $sFilterId => $aFilterValue) {
				/**
				 * Если такой фильтр есть в настройках
				 */
				if(isset($this->aFilterList['general'][$sFilterId])) {
					/**
					 * Данные фильтра
					 */
					$aFilter = $this->aFilterList['general'][$sFilterId];
				} elseif (isset($this->aFilterList['extended'][$sFilterId])) {
					$aFilter = $this->aFilterList['extended'][$sFilterId];
				} else {
					continue;
				}
				/**
				 * Если тип фильтра "eqv" или "rng"
				 */
				if($aFilter['type'] === 'eqv' or $aFilter['type'] === 'rng') {
					/**
					 * Если значения переданы не в виде массива, то преобразуем в массив
					 */
					if(!is_array($aFilterValue['val'])) {
						$aFilterValue['val'] = array($aFilterValue['val']);
					}
					/**
					 * Обрабатываем каждое значение
					 */
					foreach($aFilterValue['val'] as $sFilterValue) {
						/**
						 * Добавляем значение
						 */
						$aFilters[$sFilterId][] = $sFilterValue;
					}
					/**
					 * Параметр в URL
					 */
					$aParamURL[$sFilterId] = $sFilterId . '::' . implode('|', $aFilters[$sFilterId]);
				} elseif ($aFilter['type'] === 'vrng') {
					/**
					 * Переданное минимальное значение
					 */
					$iMinValue = intval(str_replace(' ', '', $aFilterValue['min']));
					/**
					 * Переданное максимальное значение
					 */
					$iMaxValue = intval(str_replace(' ', '', $aFilterValue['max']));
					/**
					 * Установленные значения фильтра
					 */
					$aFilterValues = array_shift($aFilter['values']);
					/**
					 * Проверяем минимальное значение
					 */
					if($iMinValue < $aFilterValues['min']) {
						$iMinValue = intval($aFilterValues['min']);
					} elseif($iMinValue > $aFilterValues['max']) {
						$iMinValue = intval($aFilterValues['min']);
					}
					/**
					 * Проверяем максимальное значение
					 */
					if($iMaxValue > intval($aFilterValues['max'])) {
						$iMaxValue = intval($aFilterValues['max']);
					} elseif($iMaxValue < intval($aFilterValues['min'])) {
						$iMaxValue = intval($aFilterValues['max']);
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
					 * Проверяем пограничные значения если они равны, то пропускаем фильтр
					 */
					if($iMinValue == $aFilterValues['min'] and $iMaxValue == intval($aFilterValues['max'])) {
						continue;
					}
					/**
					 * Устанавливаем минимальное значение
					 */
					$aFilters[$sFilterId]['min'] = $iMinValue;
					/**
					 * Устанавливаем максимальное значение
					 */
					$aFilters[$sFilterId]['max'] = $iMaxValue;
					/**
					 * Параметр в URL
					 */
					$aParamURL[$sFilterId] = $sFilterId . '::' . $iMinValue . '-' . $iMaxValue;
				}
				/**
				 * Удаляем дубли значений
				 */
				$aFilters[$sFilterId] = array_unique($aFilters[$sFilterId]);
			}
			/**
			 * Устанавливаем редирект
			 */
			if(count($aParamURL) > 0) {
				$sRedirect = implode(';', $aParamURL);
			} else {
				$sRedirect = '';
			}
		} elseif(isset($aQueries['filter'])) {
			/**
			 * Разбиваем список фильтров
			 */
			$aFilterRaws = explode(';', $aQueries['filter']);
			/**
			 * Обрабатываем каждый фильтр
			 */
			foreach($aFilterRaws as $sFilterRaw) {
				/**
				 * Разделяем идентификатор фильтра и значения
				 */
				list($sFilterId, $sFilterValue) = explode('::', $sFilterRaw);
				/**
				 * Массив значений
				 */
				$aFilterValues = explode('|', $sFilterValue);
				/**
				 * Если такой фильтр есть в настройках
				 */
				if(isset($this->aFilterList['general'][$sFilterId])) {
					/**
					 * Данные фильтра
					 */
					$aFilter = $this->aFilterList['general'][$sFilterId];
				} elseif (isset($this->aFilterList['extended'][$sFilterId])) {
					$aFilter = $this->aFilterList['extended'][$sFilterId];
				} else {
					continue;
				}
				/**
				 * Если тип фильтра "eqv" или "rng"
				 */
				if($aFilter['type'] === 'eqv' or $aFilter['type'] === 'rng') {
					/**
					 * Обрабатываем каждое значение
					 */
					foreach($aFilterValues as $sFilterValue) {
						/**
						 * Добавляем значение
						 */
						$aFilters[$sFilterId][] = $sFilterValue;
					}
					/**
					 * Параметр в URL
					 */
					$aParamURL[$sFilterId] = $sFilterId . '::' . implode('|', $aFilters[$sFilterId]);
				} elseif ($aFilter['type'] === 'vrng') {
					/**
					 * Переданные значения
					 */
					list($sMinValue, $sMaxValue) = explode('-', $sFilterValue);
					/**
					 * Установленные значения фильтра
					 */
					$aFilterValues = array_shift($aFilter['values']);
					/**
					 * Проверяем минимальное значение
					 */
					if(intval($sMinValue) < $aFilterValues['min']) {
						$iMinValue = $aFilterValues['min'];
					} elseif(intval($sMinValue) > $aFilterValues['max']) {
						$iMinValue = $aFilterValues['min'];
					} else {
						$iMinValue = intval($sMinValue);
					}
					/**
					 * Проверяем максимальное значение
					 */
					if(intval($sMaxValue) > intval($aFilterValues['max'])) {
						$iMaxValue = $aFilterValues['max'];
					} elseif(intval($sMaxValue) < intval($aFilterValues['min'])) {
						$iMaxValue = $aFilterValues['max'];
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
					 * Устанавливаем минимальное значение
					 */
					$aFilters[$sFilterId]['min'] = $iMinValue;
					/**
					 * Устанавливаем максимальное значение
					 */
					$aFilters[$sFilterId]['max'] = $iMaxValue;
					/**
					 * Параметр в URL
					 */
					$aParamURL[$sFilterId] = $sFilterId . '::' . $iMinValue . '-' . $iMaxValue;
				}
				/**
				 * Удаляем дубли значений
				 */
				$aFilters[$sFilterId] = array_unique($aFilters[$sFilterId]);
			}
		}
		/**
		 * Устанавливаем информацию об активных фильтрах
		 */
		$this->aFilterSelected = $aFilters;
		/**
		 * Возвращаем информацию о редиректе
		 */
		return $sRedirect;
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
