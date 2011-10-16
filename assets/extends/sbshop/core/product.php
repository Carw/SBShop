<?php 

/**
 * @author Mukharev Maxim
 * @version 0.1a
 * 
 * @desription
 * 
 * Электронный магазин для MODx
 * 
 * Объект товара
 */

class SBProduct {
	
	protected $aProductData; // Основные параметры товара
	protected $aProductDataKeys; // Ключи для основного списка параметров
	protected $oProductExtendData; // Дополнительные параметры товара
	protected $oOptions; // Опции товара
	protected $aImages; // массив изображений
	protected $aBaseBundle; // Базовая комплектация
	protected $oBundles; // Дополнительные комплектации


	public function __construct($aParam = false) {
		global $modx;
		/**
		 * Стандартный массив параметров товара
		 */
		$this->aProductData = array(
			'id' => null, // идентификатор
			'category' => null, // раздел в котором находится товар
			'date_add' => null, // дата добавления
			'date_edit' => null, // дата редактирования
			'title' => null, // заголовок
			'longtitle' => null, // расширенный заголовок
			'introtext' => null, // аннотация
			'description' => null, // расширенное описание
			'images' => null, // изображения
			'attributes' => null, // параметры товаров (динамические)
			'viewed' => null, // количество просмотров
			'published' => null, // товар опубликован
			'deleted' => null, // товар удален
			'order' => null, // позиция товара
			'alias' => null, // псевдоним URL
			'url' => null, // URL товара
			'sku' => null, // артикул
			'price' => null, // цена
			'options' => null, // список опций
			'vendor' => null, // производитель
			'model' => null, // модель товара
			'bundles' => null, // комплектации
			'existence' => null, // наличие товара
			'base_bundle' => null, // опции для базовой комплектации
		);
		/**
		 * Массив расширенных параметров товара
		 */
		$this->oProductExtendData = new SBAttributeList();
		/**
		 * Создаем список ключей основных параметров товара
		 */
		$this->aProductDataKeys = array_keys($this->aProductData);
		/**
		 * Опции
		 */
		$this->oOptions = new SBOptionList();
		/**
		 * Комплектации
		 */
		$this->oBundles = new SBBundleList();
		/**
		 * Устанавливаем параметры товара по переданному массиву
		 */
		$this->setAttributes($aParam);
	}

	/**
	 * Установка набора параметров товара
	 * @param $aParam Массив параметров для установки
	 * @return unknown_type
	 */
	public function setAttributes($aParam = false) {
		if(is_array($aParam)) {
			foreach ($aParam as $sKey => $sVal) {
				/**
				 * Удаляем префикс product_ у ключа
				 */
				$sKey = str_replace('product_','',$sKey);
				/**
				 * Попадает ли параметр в основной список ключей
				 */
				if(in_array($sKey,$this->aProductDataKeys)) {
					/**
					 * Дополнительная обработка ключей
					 */
					switch ($sKey) {
						case 'images':
							$this->unserializeImages($sVal);
						break;
						case 'options':
							$this->oOptions->unserializeOptions($sVal);
						break;
						case 'attributes':
							$this->oProductExtendData->unserializeAttributes($sVal);
						break;
						case 'bundles':
							$this->oBundles->unserialize($sVal);
						break;
						case 'base_bundle':
							$this->unserializeBaseBundle($sVal);
						break;
					}
					/**
					 * Заносим основной параметр
					 */
					$this->aProductData[$sKey] = $sVal;
				}
			}
		}
	}
	
	/**
	 * Установка параметра товара
	 * @param $sParamName
	 * @param $sParamValue
	 * @return unknown_type
	 */
	public function setAttribute($sParamName, $sParamValue) {
		return $this->setAttributes(array($sParamName => $sParamValue));
	}
	
	/**
	 * Получение параметров товара
	 * @param $aParams
	 * @return unknown_type
	 */
	public function getAttributes($aParams = false) {
		/**
		 * Массив с параметрами
		 */
		$aProductData = $this->aProductData;
		/**
		 * Если параметры не заданы, возвращаем весь массив параметров
		 */
		if($aParams == false) {
			return $aProductData;
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
			if(isset($aProductData[$sParam])) {
				$aResult[$sParam] = $aProductData[$sParam];
			}
		}
		return $aResult;
	}
	
	/**
	 * Получение параметра товара
	 * @param $sParamName
	 * @return unknown_type
	 */
	public function getAttribute($sParamName) {
		return array_pop($this->getAttributes($sParamName));
	}
	
	/**
	 * Получаем все основные параметры товара
	 * @return unknown_type
	 */
	public function getGeneralAttributes() {
		return $this->aProductData;
	}

	/**
	 * Установка дополнительных параметров
	 */
	public function setExtendAttributes($aParams) {
		return $this->oProductExtendData->setAttributes($aParams);
	}

	/**
	 * Получение расширенных параметров
	 */
	public function getExtendAttributes() {
		return $this->oProductExtendData->getAttributes();
	}

	/**
	 * Получение конкретного расширенного параметра
	 */
	public function getExtendAttribute($sParamName) {
		return $this->oProductExtendData->getAttribute($sParamName);
	}

	/**
	 * Получение видимых параметров
	 */
	public function getExtendVisibleAttributes() {
		return $this->oProductExtendData->getVisibleAttributes();
	}

	/**
	 * Получение видимых параметров
	 */
	public function getExtendPrimaryAttributes() {
		return $this->oProductExtendData->getPrimaryAttributes();
	}

	/**
	 * Получение видимых параметров
	 */
	public function getExtendHiddenAttributes() {
		return $this->oProductExtendData->getHiddenAttributes();
	}

	/**
	 * Получение первого изображения товара
	 */
	public function getFirstImage() {
		if(count($this->aImages) > 0) {
			return array_shift($this->aImages);
		} else {
			return array();
		}
	}

	/**
	 * Получение всех изображений товара
	 */
	public function getAllImages() {
		/**
		 * Отдаем весь массив
		 */
		return $this->aImages;
	}

	public function getImagesByKey($sKey) {
		/**
		 * Массив с нужными изображениями
		 */
		$aImages = array();
		/**
		 * Если изображения есть
		 */
		if(count($this->aImages) > 0) {
			/**
			 * Обрабатываем все изображения
			 */
			foreach ($this->aImages as $aImage) {
				/**
				 * Добавляем информацию об экземпляре изображения по ключу
				 */
				$aImages[] = $aImage[$sKey];
			}
		}
		/**
		 * Возвращаем результат
		 */
		return $aImages;
	}

	/**
	 * Получение списка опций
	 */
	public function getOptionNames() {
		return $this->oOptions->getOptionNames();
	}

	/**
	 * Получение списка значений опций
	 */
	public function getValuesByOptionName($sName) {
		return $this->oOptions->getValuesByOptionName($sName);
	}

	/**
	 * Опция является скрытой
	 * @param <type> $iNameId
	 * @return <type>
	 */
	public function isOptionHidden($iNameId) {
		return $this->oOptions->isOptionHidden($iNameId);
	}

	/**
	 * Получить список опций в комплектации
	 * @param <type> $iBundleId
	 */
	public function getBundleOptions($iBundleId) {
		return $this->oBundles->getOptionsById($iBundleId);
	}

	/**
	 * Получение значения опции по идентификаторам
	 * @param <type> $iNameId
	 * @param <type> $iValueId
	 * @return <type>
	 */
	public function getValueByNameIdAndValId($iNameId,$iValueId) {
		return $this->oOptions->getValueByNameIdAndValId($iNameId,$iValueId);
	}

	/**
	 * Получение названия опции и значения по идентификаторам
	 * @param <type> $iNameId
	 * @param <type> $iValueId
	 * @return <type>
	 */
	public function getNamesByNameIdAndValId($iNameId,$iValueId) {
		return $this->oOptions->getNamesByNameIdAndValId($iNameId,$iValueId);
	}

	/**
	 * Рассчет стоимости опций по переданному массиву
	 * @param <type> $aOptions
	 */
	public function getPriceByOptions($aOptions = array()) {
		$iPrice = 0;
		/**
		 * Если не передано опций
		 */
		if(!is_array($aOptions) or count($aOptions) == 0) {
			/**
			 * Возвращаем стоимость 0
			 */
			return 0;
		}
		/**
		 * Обрабатываем каждую опцию
		 */
		foreach ($aOptions as $iKey => $iVal) {
			/**
			 * Получаем значение стоимости
			 */
			$iPrice += $this->getValueByNameIdAndValId($iKey, $iVal);
		}
		return $iPrice;
	}

	/**
	 * Добавление комплектации
	 * @param <type> $sName
	 * @param <type> $sOptions
	 * @param <type> $fPrice
	 * @param <type> $sDescription
	 * @param <type> $sId
	 */
	public function addBundle($sName, $sOptions, $fPrice = false, $sDescription = '', $sId = false) {
		$this->oBundles->add($sName, $sOptions, $fPrice, $sDescription, $sId);
	}

	/**
	 * Получение списка компектаций
	 * @return <type>
	 */
	public function getBundleList() {
		return $this->oBundles->getList();
	}

	/**
	 * Получение комплектации по идентификатору
	 */
	public function getBundleById($iBundleId) {
		return $this->oBundles->getById($iBundleId);
	}

	public function parseBundleOptions($sParams) {
		return $this->oBundles->parse($sParams);
	}

	public function getBaseBundle() {
		return $this->aBaseBundle;
	}

	/**
	 * Десериализация параметров товара
	 * @param unknown_type $sParams
	 */
	public function unserializeAttributes($sParams) {
		return $this->oProductExtendData->unserializeAttributes($sParams);
	}

	/**
	 * Десериализация изображений
	 */
	public function unserializeImages($sParams) {
		global $modx;
		if($sParams == '') {
			return false;
		}
		/**
		 * Разбиваем список изображений на массив
		 */
		$aImageNames = explode('||', $sParams);
		/**
		 * Получаем правила ресайза
		 */
		$aImageParams = $modx->sbshop->config['image_resizes'];
		/**
		 * Обрабатываем каждое изображение
		 */
		foreach ($aImageNames as $sImageName) {
			/**
			 * Массив результирующих изображений
			 */
			$aImageLinks = array();
			/**
			 * Обрабатываем правила ресайза
			 */
			foreach ($aImageParams as $aImageParam) {
				$aImageLinks[$aImageParam['key']] = $modx->sbshop->config['image_base_url'] . $this->aProductData['id'] . '/' . $sImageName . $aImageParam['key'] . '.jpg';
			}
			/**
			 * Закидываем информацию
			 */
			$this->aImages[$sImageName] = $aImageLinks;
		}
	}

	/**
	 * 
	 * @param <type> $sParams
	 * @return <type>
	 */
	public function unserializeBaseBundle($sParams) {
		if($sParams == '') {
			return false;
		}
		$this->aBaseBundle = $this->oBundles->parse($sParams);
	}

	/**
	 * Загрузка информации о товаре
	 * @return unknown_type
	 */
	public function load($iProductId = false,$bDeleted = false) {
		global $modx;
		/**
		 * Делаем проверку на передачу численного значения 
		 */
		if(!$iProductId) {
			return false;
		}
		/**
		 * Включать удаленные товары
		 */
		if($bDeleted) {
			$sDeleted = '';
		} else {
			$sDeleted = 'product_deleted = 0 AND product_published = 1 AND ';
		}
		/**
		 * Получаем информацию о товаре по ID
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_products'),$sDeleted . 'product_id=' . intval($iProductId));
		$aData = $modx->db->makeArray($rs);
		/**
		 * Заносим дополнительные параметры в массив
		 */
		$this->oProductExtendData->unserializeAttributes($aData[0]['product_attributes']);
		/**
		 * Десериализуем дополнительные опции
		 */
		$this->oOptions->unserializeOptions($aData[0]['product_options']);
		/**
		 * Десериализация комплектаций
		 */
		$this->oBundles->unserialize($aData[0]['product_bundles']);
		/**
		 * Подготавливаем основные параметры и заносим в массив
		 */
		if(count($aData[0]) > 0) {
			foreach ($aData[0] as $sKey => $sVal) {
				$sKey = str_replace('product_','',$sKey);
				$this->aProductData[$sKey] = $sVal;
			}
		}
		unset($aData);
		/**
		 * Десериализуем изображения
		 */
		$this->unserializeImages($this->aProductData['images']);
	}
	
	/**
	 * Сохранение информации о товаре
	 * @return unknown_type
	 */
	public function save() {
		global $modx;
		$iProductId = $this->getAttribute('id');
		/**
		 * Подготавливаем основные параметры товара для сохранения
		 * Добавляем префикс 
		 */
		$aKeys = $this->aProductDataKeys;
		$aData = array();
		foreach ($aKeys as $sKey) {
			if($this->aProductData[$sKey] !== null) {
				$aData['product_' . $sKey] = $this->aProductData[$sKey];
			}
		}
		/**
		 * Подготавливаем дополнительные параметры для сохранения
		 */
		$aData['product_attributes'] = $this->oProductExtendData->serializeAttributes();
		/**
		 * Подготавливаем комплектации для для хранения
		 */
		$aData['product_bundles'] = $this->oBundles->serialize();
		/**
		 * Если ID есть, то делаем обновление информации
		 */
		if($iProductId) {
			$modx->db->update($aData,$modx->getFullTableName('sbshop_products'),'product_id=' . $iProductId);
		} else {
			/**
			 * Чтобы не возникало всяких фокусов, полностью исключаем параметр product_id
			 */
			unset($aData['product_id']);
			/**
			 * Добавляем новый товар
			 */
			$modx->db->insert($aData,$modx->getFullTableName('sbshop_products'));
			$this->setAttribute('id',$modx->db->getInsertId());
		}
	}
	
	/**
	 * Поиск товара по URL
	 * Возвращает true если товар найден или false, если поиск не дал результата
	 * @param unknown_type $sUrl
	 */
	public function searchProductByURL($sUrl = '') {
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
		 * Проверяем товары на наличие заданного пути
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_products'),'product_deleted = 0 AND product_published = 1 AND product_url = "' . $sUrl . '"');
		$aData = $modx->db->makeArray($rs);
		/**
		 * Если запись найдена среди товаров
		 */
		if(count($aData) == 1) {
			/**
			 * Устанавливаем текущий товар
			 */
			$this->setAttributes($aData[0]);
			/**
			 * Товар найден
			 */
			$bResult = true;
		} else {
			$bResult = false;
		}
		return $bResult;
	}
	
}

?>