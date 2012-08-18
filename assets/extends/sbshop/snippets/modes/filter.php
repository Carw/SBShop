<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 *
 * Экшен сниппета: Вывод фильтра
 *
 */

class filter_mode {

	protected $oCategory;

	/**
	 * Конструктор
	 * @param bool $iCategoryId
	 * @param bool $toPlaceholder
	 */
	public function __construct($sMode = false, $iCategoryId = false, $toPlaceholder = true) {
		global $modx;

		/**
		 * Если передан идентификатор раздела
		 */
		if($iCategoryId) {
			$this->oCategory = new SBCategory();
			$this->oCategory->load($iCategoryId);
		} else {
			$this->oCategory = $modx->sbshop->oGeneralCategory;
		}
		/**
		 * Задаем набор шаблонов
		 */
		$this->aTemplates = $modx->sbshop->getSnippetTemplate('filter');
		/**
		 * Если указан вывод в плейсхолдер
		 */
		if($toPlaceholder) {
			$modx->setPlaceholder('sb.filter',$this->outputFilter());
		} else {
			echo $this->outputFilter();
		}
	}

	/**
	 * Выводим список фильтров
	 * @return void
	 */
	public function outputFilter() {
		global $modx;
		/**
		 * Данные фильтра
		 */
		$aFilterSelected = $this->oCategory->oFilterList->getFilterSelected();
		/**
		 * Записываем основной контейнер
		 */
		$sOutput = '';
		/**
		 * Массив для фильтров
		 */
		$aFilterRows = array();
		/**
		 * Получаем список фильтров
		 */
		$aFilterIds = $this->oCategory->oFilterList->getFilterIds();
		/**
		 * Получаем список названий фильтров
		 */
		$aFilterNames = $this->oCategory->oFilterList->getFilterNames();
		/**
		 * Обрабатываем каждый фильтр
		 */
		foreach($aFilterIds as $sFilterId) {
			/**
			 * Получаем данные фильтра
			 */
			$aFilter = $this->oCategory->oFilterList->getFilterById($sFilterId);
			/**
			 * Значения
			 */
			$aFilterValues = $aFilter['values'];
			/**
			 * Добавляем системное значение 'all'
			 */
//			if($aFilter['type'] !== 'vrng') {
//				$aFilterValues = array('all' => array('title' => $modx->sbshop->lang['category_filter_name_all'])) + $aFilterValues;
//			}
			/**
			 * Обрабатываем каждое значение
			 */
			$aValueRows = array();
			foreach($aFilterValues as $aFilterValueId => $aFilterValue) {
				/**
				 * Если есть выбранные значения
				 */
				if(count($aFilterSelected) > 0 and is_array($aFilterSelected)) {
					/**
					 * Создаем новый массив значений для URL, который включает выделенные значения и текущее
					 */
					$aFilterURL = $aFilterSelected;
					if($aFilterValueId === 'all') {
						unset($aFilterURL[$sFilterId]);
					} else {
						$aFilterURL[$sFilterId] = $aFilterValueId;
					}
					/**
					 * Сортируем по ключам
					 */
					ksort($aFilterURL);
					/**
					 * Если значения есть
					 */
					if(count($aFilterURL) > 0) {
						/**
						 * Формируем ссылку для каждого параметра фильтра
						 */
						$aFilters = array();
						foreach($aFilterURL as $sFilterKey => $sFilterValue) {
							if(is_array($sFilterValue)) {
								$aFilters[] = $sFilterKey . '::' . urlencode(implode('|', $sFilterValue));
							} else {
								$aFilters[] = $sFilterKey . '::' . urlencode($sFilterValue);
							}
						}
						$sFilterLink = '?filter=' . implode(';', $aFilters);
					} else {
						$sFilterLink = '';
					}
				} else {
					if($aFilterValueId === 'all') {
						$sFilterLink = '';
					} else {
						$sFilterLink = '?filter=' . $sFilterId . '::' . urlencode($aFilterValueId);
					}
				}
				/**
				 * Готовим плейсхолдеры для текущего значения фильтра
				 */
				$aRepl = array(
					'[+sb.id+]' => $aFilterValueId,
					'[+sb.title+]' => $aFilterValue['title'],
					'[+sb.min+]' => $aFilterValue['min'],
					'[+sb.max+]' => $aFilterValue['max'],
					'[+sb.min.default+]' => $aFilterValue['min'],
					'[+sb.max.default+]' => $aFilterValue['max'],
					'[+sb.link+]' => $modx->sbshop->getFullUrl() . $sFilterLink,
					'[+sb.filter.id+]' => $sFilterId
				);
				/**
				 * Если тип "vrng"
				 */
				if($aFilter['type'] === 'vrng') {
					if(isset($aFilterSelected[$sFilterId]['min'])) {
						$aRepl['[+sb.min+]'] = $aFilterSelected[$sFilterId]['min'];
					}
					if(isset($aFilterSelected[$sFilterId]['max'])) {
						$aRepl['[+sb.max+]'] = $aFilterSelected[$sFilterId]['max'];
					}
				}
				/**
				 * Добавляем выделение для активного значения
				 */
				if($aFilterValueId == 'all' and !isset($aFilterSelected[$sFilterId])) {
					$aRepl['[+sb.class+]'] = 'active';
					$aRepl['[+sb.checked+]'] = 'checked="checked"';
					$aRepl['[+sb.selected+]'] = 'selected="selected"';
				} elseif((is_array($aFilterSelected[$sFilterId]) and in_array($aFilterValueId, $aFilterSelected[$sFilterId]))) {
					$aRepl['[+sb.class+]'] = 'active';
					$aRepl['[+sb.checked+]'] = 'checked="checked"';
					$aRepl['[+sb.selected+]'] = 'selected="selected"';
				}
				$aValueRows[] = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['filter_' . $aFilter['type'] . '_value']);
			}
			/**
			 * Готовим плейсхолдеры
			 */
			$aRepl = array(
				'[+sb.wrapper+]' => implode($this->aTemplates['filter_value_separator'], $aValueRows),
				'[+sb.title+]' => $aFilterNames[$sFilterId]
			);
			/**
			 * Добавляем фильтр
			 */
			$aFilterRows[] = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['filter_' . $aFilter['type'] . '_row']);
		}
		/**
		 * Если фильтры есть
		 */
		if(count($aFilterRows) > 0) {
			/**
			 * Вставляем фильтры в общий контейнер
			 */
			$sOutput = str_replace('[+sb.wrapper+]', implode($aFilterRows), $this->aTemplates['filter_outer']);
		}
		/**
		 * Возвращаем результат
		 */
		return $sOutput;

	}
}
