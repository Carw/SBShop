<?php

/**
 * @author Mukharev Maxim
 * @version 0.1a
 * 
 * @desription
 * 
 * Электронный магазин для MODx
 * 
 * Класс для категории электронного магазина
 */

class SBCategory {
	
	protected $aCategoryData;
	protected $aCategoryDataKeys;
	protected $oCategoryExtendData;
	protected $oFilterList;
	
	/**
	 * Конструктор
	 * @return unknown_type
	 */
	public function __construct($aParam = false) {
		/**
		 * Стандартный массив параметров категории
		 */
		$this->aCategoryData = array(
			'id' => null, // идентификатор
			'date_add' => null, // дата добавления
			'date_edit' => null, // дата редактирования
			'title' => null, // заголовок
			'longtitle' => null, // расширенный заголовок
			'description' => null, // описание
			'images' => null, // изображения
			'attributes' => null, // параметры раздела
			'filters' => null, // фильтры раздела
			'views' => null, // счетчик просмотров
			'published' => null, // раздел опубликован
			'deleted' => null, // раздел удален
			'order' => null, // позиция раздела
			'parent' => null, // родительский раздел
			'alias' => null, // псевдоним
			'path' => null, // путь раздела
			'level' => null, // уровень вложенности
			'url' => null, // URL раздела
		);
		/**
		 * Устанавливаем ключи параметров категории
		 */
		$this->aCategoryDataKeys = array_keys($this->aCategoryData);
		/**
		 * Инициализация расширенных значений
		 */
		$this->oCategoryExtendData = new SBAttributeList();
		/**
		 * Инициализация фильтров
		 */
		$this->oFilterList = new SBFilterList();
		/**
		 * Устанавливаем параметры товара по переданному массиву
		 */
		$this->setAttributes($aParam);
	}
	
	/**
	 * Установка набора параметров категории
	 * @param $aParam Массив параметров для установки
	 * @return unknown_type
	 */
	public function setAttributes($aParam = false) {
		if(is_array($aParam)) {
			foreach ($aParam as $sKey => $sVal) {
				/**
				 * Удаляем префикс category_ у ключа
				 */
				$sKey = str_replace('category_','',$sKey);
				/**
				 * Отсекаем лишние параметры
				 */
				if(in_array($sKey,$this->aCategoryDataKeys)) {
					
					$this->aCategoryData[$sKey] = $sVal;
				}
				if($sKey == 'attributes') {
					$this->unserializeAttributes($sVal);
				}
				if($sKey == 'filters') {
					$this->unserializeFilters($sVal);
				}
			}
		}
	}
	
	/**
	 * Установка параметра категории
	 * @param $sParamName
	 * @param $sParamValue
	 * @return unknown_type
	 */
	public function setAttribute($sParamName, $sParamValue) {
		return $this->setAttributes(array($sParamName => $sParamValue));
	}
	
	/**
	 * Установка расширенных параметров
	 * @param unknown_type $aParam
	 */
	public function setExtendAttributes($aParam = false) {
		return $this->oCategoryExtendData->setAttributes($aParam);
	}

	public function addFilter($aFilter, $aValues) {
		return $this->oFilterList->add($aFilter, $aValues);
	}

	/**
	 * Получение заданного параметра категории
	 * @param $sParamName
	 * @return unknown_type
	 */
	public function getAttribute($sParamName) {
		return array_pop($this->getAttributes($sParamName));
	}
	
	/**
	 * Получение параметров категории
	 * @param $aParams
	 * @return unknown_type
	 */
	public function getAttributes($aParams = false) {
		/**
		 * Если параметры не заданы, возвращаем весь массив
		 */
		if($aParams == false) {
			return $this->aCategoryData;
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
		foreach ($aParams as $sParam) {
			if(isset($this->aCategoryData[$sParam])) {
				$aResult[$sParam] = $this->aCategoryData[$sParam];
			}
		}
		return $aResult;
	}
	
	/**
	 * Получение списка расширенных параметров категории
	 * @param unknown_type $aParams
	 */
	public function getExtendAttributes($aParams = false) {
		return $this->oCategoryExtendData->getAttributes($aParams);
	}
	
	/**
	 * Получение ключей расширенных параметров 
	 */
	public function getExtendAttributeKeys() {
		return $this->oCategoryExtendData->getAttributeKeys();
	}

	/**
	 * Получение агрегированных параметров
	 */
	public function getAggregatedAttributes() {
		global $modx;
		/**
		 * Массив для результата
		 */
		$aOutput = array();
		/**
		 * Если указан идентификатор раздела
		 */
		if($this->getAttribute('id')) {
			/**
			 * Получаем данные из базы
			 */
			$rs = $modx->db->select('b.*',$modx->getFullTableName('sbshop_category_attributes') . ' as a, ' . $modx->getFullTableName('sbshop_attributes') . ' as b', 'a.category_id = ' . $this->getAttribute('id') . ' and a.attribute_id = b.attribute_id');
			$cnt = $modx->db->getRecordCount($rs);
			for ($i = 0; $i < $cnt; $i++) {
				$aRow = $modx->db->getRow($rs);
				$aOutput[$aRow['attribute_name']] = $aRow['attribute_id'];
			}
		}
		/**
		 * Возвращаем
		 */
		return $aOutput;
	}

	/**
	 * Получение фильтра по идентификатору
	 * @param  $sFilterId
	 * @return void
	 */
	public function getFilterById($sFilterId) {
		return $this->oFilterList->getFilterById($sFilterId);
	}

	/**
	 * Получение списка идентификаторов фильтров
	 * @return array
	 */
	public function getFilterIds() {
		return $this->oFilterList->getFilterIds();
	}

	/**
	 * Получение списка названий фильтров
	 * @return void
	 */
	public function getFilterNames() {
		return $this->oFilterList->getFilterNames();
	}

	public function getFilterSelected($bCompact = false) {
		return $this->oFilterList->getFilterSelected($bCompact);
	}

	/**
	 * Десериализация параметров категории
	 * @param unknown_type $sParams
	 */
	public function unserializeAttributes($sParams) {
		return $this->oCategoryExtendData->unserializeAttributes($sParams);
	}

	/**
	 * Десериализация фильтров
	 */
	public function unserializeFilters($sParams) {
		return $this->oFilterList->unserializeFilters($sParams);
	}

	/**
	 * Загрузка информации по указанной категории
	 * @param $iCategoryId
	 * @return unknown_type
	 */
	public function load($iCategoryId = false,$bDeleted = false) {
		global $modx;
		/**
		 * Делаем проверку на передачу численного значения 
		 */
		if(!$iCategoryId || $iCategoryId == 0) {
			return false;
		}
		/**
		 * Включать удаленные категории
		 */
		if($bDeleted) {
			$sDeleted = '';
		} else {
			$sDeleted = 'category_deleted = 0 AND category_published = 1 AND ';
		}
		/**
		 * Запрос информации из базы
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_categories'),$sDeleted . 'category_id='.$iCategoryId);
		$aData = $modx->db->makeArray($rs);
		/**
		 * Если есть такая категория
		 */
		if(count($aData[0]) > 0) {
			/**
			 * Подготавливаем основные параметры и заносим в массив
			 */
			foreach ($aData[0] as $sKey => $sVal) {
				$sKey = str_replace('category_','',$sKey);
				$this->aCategoryData[$sKey] = $sVal;
			}
			/**
			 * Подготавливаем дополнительные параметры
			 */
			$this->unserializeAttributes($this->aCategoryData['attributes']);
			/**
			 * Фильтры
			 */
			$this->unserializeFilters($this->aCategoryData['filters']);
			return true;
		} else {
			return false;
		}
	}

	public function loadByUrl($sCategoryUrl) {
		global $modx;
		/**
		 * Делаем проверку на передачу численного значения
		 */
		if(!$sCategoryUrl) {
			return false;
		}
		/**
		 * Запрос информации из базы
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_categories'),'category_url="'.$sCategoryUrl.'"');
		$aData = $modx->db->makeArray($rs);
		/**
		 * Если есть такая категория
		 */
		if(count($aData[0]) > 0) {
			/**
			 * Подготавливаем основные параметры и заносим в массив
			 */
			foreach ($aData[0] as $sKey => $sVal) {
				$sKey = str_replace('category_','',$sKey);
				$this->aCategoryData[$sKey] = $sVal;
			}
			/**
			 * Подготавливаем дополнительные параметры
			 */
			$this->unserializeAttributes($this->aCategoryData['attributes']);
			/**
			 * Фильтры
			 */
			$this->unserializeFilters($this->aCategoryData['filters']);
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Сохранение данных категории
	 */
	public function save() {
		global $modx;
		/**
		 * Подготавливаем основные параметры товара для сохранения
		 * Добавляем префикс 
		 */
		$aKeys = $this->aCategoryDataKeys;
		$aData = array();
		foreach ($aKeys as $sKey) {
			if($this->aCategoryData[$sKey] !== null) {
				$aData['category_' . $sKey] = $this->aCategoryData[$sKey];
			}
		}
		/**
		 * Подготавливаем дополнительные параметры для сохранения
		 */
		$aData['category_attributes'] = $this->oCategoryExtendData->serializeAttributes();
		/**
		 * Подготавливаем фильтры для сохранения
		 */
		$aData['category_filters'] = $this->oFilterList->serializeFilters();
		/**
		 * Если ID есть
		 */
		$iCategoryId = $this->getAttribute('id');
		if($iCategoryId) {
			/**
			 * Делаем обновление информации о категории
			 */
			$modx->db->update($aData,$modx->getFullTableName('sbshop_categories'),'category_id=' . $iCategoryId);
		} else {
			/**
			 * Чтобы не возникало всяких фокусов, полностью исключаем параметр category_id
			 */
			unset($aData['category_id']);
			/**
			 * Добавляем новую категорию
			 */
			$modx->db->insert($aData,$modx->getFullTableName('sbshop_categories'));
			$this->setAttribute('id',$modx->db->getInsertId());
		}
	}
	
	/**
	 * Поиск категории по заданному URL
	 * Если категория найдена, то возвращает true
	 * При неправильно результате возвращает false
	 * @param $sURL
	 */
	public function searchCategoryByURL($sUrl = '') {
		global $modx;
		/**
		 * Если адрес не передан или пустой
		 */
		if($sUrl == '') {
			/**
			 * Возвращает false
			 */
			return false;
		}
		/**
		 * Делаем запрос в базу
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_categories'),'category_deleted = 0 AND category_published = 1 AND category_url = "' . $sUrl . '"');
		$aData = $modx->db->makeArray($rs);
		/**
		 * Если запись найдена среди категорий
		 */
		if(count($aData) == 1) {
			/**
			 * Устанавливаем данные категории
			 */
			$this->setAttributes($aData[0]);
			/**
			 * Все отлично
			 */
			$bResult = true;
		} else {
			/**
			 * Такой категории нет
			 */
			$bResult = false;
		}
		return $bResult;
	}
}


?>