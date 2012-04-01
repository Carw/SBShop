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
		$aFilterSelected = $this->oCategory->oFilterList->getFilterSelected(true);
		/**
		 * Записываем основной контейнер
		 */
		$sOutput = $this->aTemplates['filter_outer'];
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
			$aFilterValues = array('all' => array('title' => $modx->sbshop->lang['category_filter_name_all'])) + $aFilterValues;
			/**
			 * Обрабатываем каждое значение
			 */
			$aValueRows = array();
			foreach($aFilterValues as $aFilterValueId => $aFilterValue) {
				/**
				 * Если есть выбранные значения
				 */
				if(is_array($aFilterSelected) and count($aFilterSelected) > 0) {
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
							$aFilters[] = $sFilterKey . '::' . urlencode($sFilterValue);
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
					'[+sb.link+]' => $modx->sbshop->getFullUrl() . $sFilterLink,
					'[+sb.filter.id+]' => $sFilterId
				);
				if($aFilterValueId == 'all' and !isset($aFilterSelected[$sFilterId])) {
					$aRepl['[+sb.style+]'] = 'active';
				} elseif($aFilterSelected[$sFilterId] == $aFilterValueId) {
					$aRepl['[+sb.style+]'] = 'active';
				}
				$aValueRows[] = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['filter_value']);
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
			$aFilterRows[] = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['filter_row']);
		}
		/**
		 * Вставляем фильтры в общий контейнер
		 */
		$sOutput = str_replace('[+sb.wrapper+]',implode($aFilterRows),$sOutput);
		/**
		 * Возвращаем результат
		 */
		return $sOutput;

	}

}
