<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 * 
 * Класс управления списком товаров
 */

class SBProductList {
	
	protected $aProductList;
	
	/**
	 * Конструктор
	 * @param $iCatId Категория для списка
	 */
	public function __construct($iCatIds = false, $aProductIds = false, $iLimit = false) {
		/**
		 * Инициализируем основной массив 
		 */
		$this->aProductList = array();
		/**
		 * Если задан список
		 */
		if(is_array($aProductIds) and (count($aProductIds) > 0)) {
			/**
			 * Делаем загрузку списка товаров по заданному массиву идентификаторов
			 */
			$this->loadListByIds($aProductIds, $iLimit);
		} elseif(is_array ($iCatIds)) {
			/**
			 * Делаем загрузку списка товаров по списку категорий
			 */
			$this->loadListByCategoryIds($iCatIds, $iLimit);
		} elseif($iCatIds !== false) {
			/**
			 * Делаем загрузку списка товаров по категории
			 */
			$this->loadListByCategoryId($iCatIds, $iLimit);
		}
	}

	/**
	 * Получение количества товара по идентификаторам разделов
	 */
	public function getCountByCatIds($aCatIds) {
		global $modx;
		/**
		 * Если массив пустой
		 */
		if(!is_array($aCatIds) or count($aCatIds) == 0) {
			return false;
		}
		/**
		 * Список идентификаторов для запроса
		 */
		$sCatIds = implode(',', $aCatIds);
		/**
		 * Делаем запрос
		 */
		$rs = $modx->db->select('count(*)', $modx->getFullTableName('sbshop_products'), '  product_deleted = 0 AND product_published = 1 AND product_category in (' . $sCatIds . ')');
		/**
		 * Получаем количество
		 */
		$iCount = $modx->db->getValue($rs);
		/**
		 * Возвращаем результат
		 */
		return $iCount;
	}
	
	/**
	 * Загрузка списка товаров в заданной категории
	 * @param unknown_type $iCatId
	 */
	public function loadListByCategoryId($iCatId,$iLimit = false) {
		global $modx;
		/**
		 * Если категория не определена, то просто выходим
		 */
		if(!$iCatId) {
			$iCatId = 0;
		}
		/**
		 * Количество товаров на страницу
		 * @todo разобраться с постраничной разбивкой
		 */
		//$ProductPerPage = $modx->sbshop->config['product_per_page'];
		/**
		 * Получаем информацию из базы
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_products'),' product_deleted = 0 AND product_published = 1 AND product_category = ' . $iCatId,'product_order',$iLimit);
		$aRaws = $modx->db->makeArray($rs);
		/**
		 * Устанавливаем список
		 */
		$this->setList($aRaws);
	}

	/**
	 * Загрузка полного списка товаров в заданной категории
	 * @param unknown_type $iCatId
	 */
	public function loadFullListByCategoryId($iCatId,$iLimit = false) {
		global $modx;
		/**
		 * Если категория не определена, то просто выходим
		 */
		if(!$iCatId) {
			$iCatId = 0;
		}
		/**
		 * Количество товаров на страницу
		 * @todo разобраться с постраничной разбивкой
		 */
		//$ProductPerPage = $modx->sbshop->config['product_per_page'];
		/**
		 * Получаем информацию из базы
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_products'),' product_category = ' . $iCatId,'product_order',$iLimit);
		$aRaws = $modx->db->makeArray($rs);
		/**
		 * Устанавливаем список
		 */
		$this->setList($aRaws);
	}

	/**
	 * Загрузка списка товаров в заданной категории
	 * @param unknown_type $iCatId
	 */
	public function loadFilteredListByCategoryId($iCatId,$iLimit = false,$aFilterList = false) {
		global $modx;
		/**
		 * Если категория не определена, то просто выходим
		 */
		if(!$iCatId) {
			return false;
		}
		/**
		 * Переменная для условий фильтра
		 */
		$aFilterGeneralParams = array();
		/**
		 * Для фильтров по дополнительным параметрам
		 */
		$aFilterExtendedParams = array();
		/**
		 * Если есть фильтры по основным параметрам
		 */
		if($aFilterList['general']) {
			/**
			 * Если есть дополнительные параметры вводим ключ для таблицы
			 */
			$sTableKey = '';
			if($aFilterList['extended']) {
				$sTableKey = 'a.';
			}
			/**
			 * Обрабатываем основные фильтры
			 */
			foreach($aFilterList['general'] as $sFilterKey => $aFilter) {
				/**
				 * Если есть параметр на соответствие
				 */
				if($aFilter['type'] == 'eqv') {
					/**
					 * Добавляем условие на равенство
					 */
					$aFilterGeneralParams[] = $sTableKey . 'product_' . $sFilterKey . '="' . $aFilter['eqv'] .  '"';
				} elseif($aFilter['type'] == 'rng') {
					/**
					 * Если установлено min и max значение
					 */
					if(isset($aFilter['min']) and isset($aFilter['max'])) {
						$aFilterGeneralParams[] = $sTableKey . 'product_' . $sFilterKey . ' BETWEEN ' . $aFilter['min'] .  ' and ' . $aFilter['max'];
					} elseif (isset($aFilter['min'])) {
						$aFilterGeneralParams[] = $sTableKey . 'product_' . $sFilterKey . ' >= ' . $aFilter['min'];
					} elseif (isset($aFilter['max'])) {
						$aFilterGeneralParams[] = $sTableKey . 'product_' . $sFilterKey . ' <= ' . $aFilter['max'];
					}

				}
			}
		}
		/**
		 * Счетчик количества фильтров на дополнительные параметры
		 */
		$cntExtendedFilters = 0;
		/**
		 * Если есть фильтры на дополнительные параметры
		 */
		if($aFilterList['extended']) {
			/**
			 * Обрабатываем основные фильтры
			 */
			foreach($aFilterList['extended'] as $sFilterKey => $aFilter) {
				/**
				 * Если есть параметр на соответствие
				 */
				if($aFilter['type'] == 'eqv') {
					/**
					 * Добавляем условие на равенство
					 */
					$aFilterExtendedParams[] = 'b.attribute_id =' . $sFilterKey . ' and b.attribute_value="' . $aFilter['eqv'] .  '"';
					/**
					 * Счетчик
					 */
					$cntExtendedFilters++;
				} elseif($aFilter['type'] == 'rng') {
					/**
					 * Если установлено min и max значение
					 */
					if(isset($aFilter['min']) and isset($aFilter['max'])) {
						$aFilterExtendedParams[] = 'b.attribute_id =' . $sFilterKey . ' and b.attribute_value BETWEEN ' . $aFilter['min'] .  ' and ' . $aFilter['max'];
					} elseif (isset($aFilter['min'])) {
						$aFilterExtendedParams[] = 'b.attribute_id =' . $sFilterKey . ' and b.attribute_value >= ' . $aFilter['min'];
					} elseif (isset($aFilter['max'])) {
						$aFilterExtendedParams[] = 'b.attribute_id =' . $sFilterKey . ' and b.attribute_value <= ' . $aFilter['max'];
					}
					/**
					 * Счетчик
					 */
					$cntExtendedFilters++;
				}
			}
		}
		/**
		 * Если не установлены фильтры на расширенные параметры
		 */
		if(!$aFilterList['extended']) {
			/**
			 * Если параметры фильтров есть
			 */
			$sFilter = '';
			if(count($aFilterGeneralParams) > 0) {
				$sFilter = ' and ' . implode(' and ', $aFilterGeneralParams);
			}
			/**
			 * Получаем информацию из базы с учетом фильтров только по основным параметрам
			 */
			$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_products'),' product_deleted = 0 AND product_published = 1 AND product_category = ' . $iCatId . $sFilter,'product_order',$iLimit);
			$aRaws = $modx->db->makeArray($rs);
		} else {
			/**
			 * Основные параметры
			 */
			if(count($aFilterGeneralParams) > 0) {
				$sGeneralFilter = ' and ' . implode(' and ', $aFilterGeneralParams);
			} else {
				$sGeneralFilter = '';
			}
			/**
			 * Дополнительные параметры
			 */
			$sExtendedFilter = '(' . implode(') or (',  $aFilterExtendedParams) . ')';
			/**
			 * Получаем информацию из базы с учетом фильтров по основным и дополнительным параметрам
			 */
			$sql = 'SELECT a.* FROM ' . $modx->getFullTableName('sbshop_products') . ' as a WHERE a.product_deleted = 0 AND a.product_published = 1 AND a.product_category = ' . $iCatId . $sGeneralFilter . ' and a.product_id in (SELECT b.product_id FROM ' . $modx->getFullTableName('sbshop_product_attributes') . ' as b WHERE ' . $sExtendedFilter . ' GROUP BY b.product_id HAVING count(*) = ' . $cntExtendedFilters . ') ORDER BY a.product_order';
			$rs = $modx->db->query($sql);
			$aRaws = $modx->db->makeArray($rs);
		}
		/**
		 * Устанавливаем список
		 */
		$this->setList($aRaws);
	}

	/**
	 * Загрузка списка товаров в заданной категории
	 * @param unknown_type $iCatId
	 */
	public function loadListByCategoryIds($aCatIds,$iLimit = false) {
		global $modx;
		/**
		 * Если категория не определена, то просто выходим
		 */
		if(!$aCatIds) {
			$aCatIds = 0;
		}
		/**
		 * Список разделов
		 */
		if(is_array($aCatIds)) {
			$sCatIds = implode(',', $aCatIds);
		} else {
			$sCatIds = $aCatIds;
		}
		/**
		 * Получаем информацию из базы
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_products'),' product_deleted = 0 AND product_published = 1 AND product_category in (' . $sCatIds . ')','product_order',$iLimit);
		$aRaws = $modx->db->makeArray($rs);
		/**
		 * Устанавливаем список
		 */
		$this->setList($aRaws);
	}

	/**
	 * Получить полный список товаров
	 */
	public function loadAllList() {
		global $modx;
		/**
		 * Количество товаров на страницу
		 * @todo разобраться с постраничной разбивкой
		 */
		//$ProductPerPage = $modx->sbshop->config['product_per_page'];
		/**
		 * Получаем информацию из базы
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_products'),' product_deleted = 0 AND product_published = 1','product_order',$iLimit);
		$aRaws = $modx->db->makeArray($rs);
		/**
		 * Устанавливаем список
		 */
		$this->setList($aRaws);
	}

	/**
	 * Загрузка списка товаров по заданному набору идентификаторов
	 */
	public function loadListByIds($aProductIds = false, $iLimit = false) {
		global $modx;
		/**
		 * Если список не передан, то заканчиваем
		 */
		if(!$aProductIds) {
			return false;
		}
		/**
		 * Если передано одно значение
		 */
		if(!is_array($aProductIds)) {
			/**
			 * Делаем массив с одним значением
			 */
			$aProductIds = array($aProductIds);
		}
		/**
		 * Объединяем список идентификаторов
		 */
		$sProductIds = implode(',',$aProductIds);
		/**
		 * Количество товаров на страницу
		 * @todo разобраться с постраничной разбивкой
		 */
		//$ProductPerPage = $modx->sbshop->config['product_per_page'];
		/**
		 * Получаем информацию из базы
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_products'),' product_id in(' . $sProductIds . ')','',$iLimit);
		$aRaws = $modx->db->makeArray($rs);
		/**
		 * Устанавливаем список
		 */
		$this->setList($aRaws);
	}
	
	/**
	 * Установка списка по переданному массиву информации о товарах
	 * @param unknown_type $aProducts
	 */
	public function setList($aProducts) {
		/**
		 * Если найдены товары
		 */
		if(count($aProducts) > 0) {
			/**
			 * Обрабатываем каждую запись
			 */
			foreach($aProducts as $aProduct) {
				/**
				 * Добавляем в основной массив экземпляр товара
				 */
				$this->aProductList[$aProduct['product_id']] = new SBProduct($aProduct);
			}
		}
	}

	/**
	 * Добавление переданного товара в список
	 * @param SBProduct $oProduct Товар
	 */
	public function addProduct($oProduct) {
		$this->aProductList[$oProduct->getAttribute('id')] = $oProduct;
	}

	/**
	 * Получение списка товаров
	 */
	public function getProductList() {
		return $this->aProductList;
	}

	/**
	 * Получение  массива заданного параметра для всех товаров
	 * @param mixed $aParams
	 */
	public function getAttributes($aParams) {
		/**
		 * Если параметры не заданы, возвращаем весь массив параметров
		 */
		if($aParams == false) {
			return $this->aProductList;
		}
		/**
		 * Если передана строка, то делаем массив
		 */
		if(!is_array($aParams)) {
			$aParams = array($aParams);
		}
		/**
		 * Выбираем заданные параметры из массива
		 */
		$aResult = array();

		/**
		 * Обрабатываем каждый товар
		 * @var SBProduct
		 */
		foreach ($this->aProductList as $sKey => $oProduct) {
			$aResult[$sKey] = $oProduct->getAttributes($aParams);
		}
		return $aResult;
	}

	/**
	 * Получить данные товара из списка по идентификатору
	 * @param <type> $iId
	 */
	public function getProductAttributesById($iProductId) {
		if(isset ($this->aProductList[$iProductId])) {
			return $this->aProductList[$iProductId]->getAttributes();
		}
	}

	/**
	 * Получить товар из списка по идентификатору
	 * @param <type> $iProductId
	 * @return <type>
	 */
	public function getProductById($iProductId) {
		if(isset ($this->aProductList[$iProductId])) {
			return $this->aProductList[$iProductId];
		}
	}

	/**
	 * Удаление указанного товара по идентификатору
	 * @param <type> $iProductId
	 */
	public function deleteProduct($iProductId) {
		$this->deleteProducts(array($iProductId));
	}

	/**
	 * Удаление указанных товаров из списка
	 * @param <type> $aProductIds
	 */
	public function deleteProducts($aProductIds) {
		/**
		 * Для каждого идентификатора в массиве
		 */
		foreach ($aProductIds as $iProductId) {
			/**
			 * Если товар есть в списке
			 */
			if(isset($this->aProductList[$iProductId])) {
				/**
				 * Удаляем
				 */
				unset($this->aProductList[$iProductId]);
			}
		}
	}
}


?>