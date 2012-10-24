<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 * 
 * Класс управления заказом
 */

class SBOrder {
	
	protected $aOrderData; // основные данные заказа
	protected $aOrderDataKeys; // ключи для основных данных заказа
	protected $aProducts; // информация о купленных товарах / количестве / конфигурации и т.д.
	protected $aOptions; // настраиваемые параметры заказа, определяемые процедурой оформления
	public $oComments; // комментарии к заказу
	/**
	 * @var SBProductList
	 */
	protected $oProductList; // список товаров, которые лежат в корзине
	
	public function __construct($aParam = false) {
		/**
		 * Стандартный массив данных заказа
		 */
		$this->aOrderData = array(
			'id' => null,
			'user' => null,
			'date_add' => null,
			'date_edit' => null,
			'date_next' => null,
			'ip' => null,
			'status' => null,
			'price' => null,
			'products' => null,
			'options' => null,
			'comments' => null,
		);
		/**
		 * Ключи стандартных данных
		 */
		$this->aOrderDataKeys = array_keys($this->aOrderData);
		/**
		 * Инициализация массива параметров товаров в заказе
		 */
		$this->aProducts = array();
		/**
		 * Настройки заказа
		 */
		$this->aOptions = array();
		/**
		 * инициализация объекта управления комментариями
		 */
		$this->oComments = new SBTimeList();
		/**
		 * Список товаров в заказе
		 */
		$this->oProductList = new SBProductList();
		/**
		 * Если переданы параметры
		 */
		if(is_array($aParam)) {
			/**
			 * Устанавливаем параметры заказа по переданному массиву
			 */
			$this->setAttributes($aParam);
		}
	}
	
	/**
	 * Получение текущих данных корзины
	 */
	public function load() {
		/**
		 * Если есть ифнормация о заказе в сесии
		 */
		if(isset($_SESSION['sb.order'])) {
			/**
			 * Записываем ее
			 */
			$this->setAttributes($_SESSION['sb.order']);
		}
	}

	/**
	 * Загрузка заказа по указанному идентификатору
	 * @global <type> $modx
	 * @param <type> $iOrderId
	 * @return <type>
	 */
	public function loadById($iOrderId) {
		global $modx;
		/**
		 * Если идентификатор неправильный
		 */
		if($iOrderId == 0) {
			return false;
		}
		/**
		 * Делаем запрос
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_orders'),'order_id = ' . $iOrderId);
		$aRows = $modx->db->makeArray($rs);
		/**
		 * Устанавливаем значение по первой записи
		 */
		$this->setAttributes($aRows[0]);
		/**
		 * Загружаем список заказанных товаров
		 */
		$this->oProductList = new SBProductList('',$this->getProductIds());
	}

	/**
	 * Установка заданного параметра заказа
	 * @param unknown_type $sParamName
	 * @param unknown_type $sParamValue
	 */
	public function setAttribute($sParamName, $sParamValue) {
		$this->setAttributes(array($sParamName => $sParamValue));
	}
	
	/**
	 * Установка основных параметров заказа
	 * @param unknown_type $aAttributes
	 */
	public function setAttributes($aAttributes = false) {
		/**
		 * Если передан массив значений
		 */
		if(is_array($aAttributes)) {
			/**
			 * Обрабатываем каждое значение
			 */
			foreach ($aAttributes as $sKey => $sVal) {
				/**
				 * Удаляем префикс category_ у ключа
				 */
				$sKey = str_replace('order_','',$sKey);
				/**
				 * Отсекаем лишние параметры
				 */
				if(in_array($sKey,$this->aOrderDataKeys)) {
					
					$this->aOrderData[$sKey] = $sVal;
				}
				/**
				 * Индивидуальная обработка
				 */
				switch ($sKey) {
					case 'products':
						$this->unserializeProducts($sVal);
					break;
					case 'options':
						$this->unserializeOptions($sVal);
					break;
					case 'comments':
						$this->unserializeComments($sVal);
					break;
				}
			}
		}
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
	 * Установка параметра заказа
	 * @param $sName
	 * @param $sValue
	 */
	public function setOption($sName, $sValue) {
		$this->aOptions[$sName] = $sValue;
	}

	/**
	 * Установка параметра заказа
	 * @param $sName
	 * @param $sValue
	 */
	public function getOption($sName) {
		return $this->aOptions[$sName];
	}

	/**
	 * Получение параметров заказа
	 * @param $aParams
	 * @return unknown_type
	 */
	public function getAttributes($aParams = false) {
		/**
		 * Если параметры не заданы, возвращаем весь массив
		 */
		if($aParams == false) {
			return $this->aOrderData;
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
			if(isset($this->aOrderData[$sParam])) {
				$aResult[$sParam] = $this->aOrderData[$sParam];
			}
		}
		return $aResult;
	}
	
	/**
	 * Добавление товара к заказу
	 *
	 * @param $iProductId
	 * @param bool $aParams
	 * @return string
	 */
	public function addProduct($iProductId, $aParams = false) {
		global $modx;
		/**
		 * Если передан массив значений
		 */
		if(is_array($aParams)) {
			/**
			 * Получаем из списка нужный товар
			 */
			$oProduct = $this->oProductList->getProductById($iProductId);
			/**
			 * Если товара нет в списке
			 */
			if(!$oProduct) {
				/**
				 * Загружаем
				 */
				$oProduct = new SBProduct();
				$oProduct->load($iProductId);
				/**
				 * Добавляем в список
				 */
				$this->oProductList->addProduct($oProduct);
			}
			/**
			 * Начальное значение сета - идентификатор товара
			 */
			$sSetId = $iProductId;
			/**
			 * Если такой товар уже есть в списке
			 */
			if(isset($this->aProducts[$sSetId])) {
				/**
				 * Просто прибавляем количество товара
				 */
				$this->aProducts[$sSetId]['quantity'] += $aParams['quantity'];
			} else {
				/**
				 * Опции, относящиеся к базовой комплектации
				 */
				if(isset($aParams['bundle'])) {
					/**
					 * Комплектация
					 */
					$aBundle = $oProduct->getBundleById($aParams['bundle']);
					/**
					 * Получаем базовый список опций
					 */
					$aBaseOptions = $oProduct->getBaseBundle();
					/**
					 * Если дополнительные опции установлены
					 */
					if(isset($aBundle['options'])) {
						/**
						 * Добавляем опции в общий список
						 */
						$aBaseOptions += $aBundle['options'];
					}
				} else {
					/**
					 * Берем базовые опции
					 */
					$aBaseOptions = $oProduct->getBaseBundle();
				}
				/**
				 * Массив опций товара
				 */
				$aOptions = array();
				/**
				 * Массив значений для сета
				 */
				$aOptionRows = array();
				/**
				 * Если опции есть
				 */
				if(isset($aParams['options'])) {
					/**
					 * Обрабатываем каждую опцию
					 */
					foreach($aParams['options'] as $sKey => $sVal) {
						/**
						 * Получаем информацию об опции
						 */
						$aOption = $oProduct->oOptions->getNamesByNameIdAndValId($sKey, $sVal);
						/**
						 * Добавляем в массив для заказа
						 */
						$aTmpOption = array(
							'value_id' => $sVal,
							'price' => $oProduct->getPriceByOptions(array($sKey => $sVal))
						);
						/**
						 * Если значение опции скрываемое
						 */
						if(in_array($sVal, $modx->sbshop->config['hide_option_values'])) {
							$aTmpOption['title'] = $aOption['title'];
						} else {
							$aTmpOption['title'] = $aOption['title'] . $modx->sbshop->config['option_separator'] . ' ' . $aOption['value'];
						}
						/**
						 * Если значение опции не равно 'null'
						 */
						if($oProduct->oOptions->getValueByNameIdAndValId($sKey, $sVal) !== 'null') {
							/**
							 * Если опция входит в базовую комплектацию
							 */
							if($aBaseOptions[$sKey] === $sVal) {
								/**
								 * Добавляем информацию о базовых опциях к заказу
								 */
								unset($aTmpOption['price']);
								$aOptions['base'][$sKey] = $aTmpOption;
							} else {
								/**
								 * Добавляем информацию о базовых опциях к заказу
								 */
								$aOptions['ext'][$sKey] = $aTmpOption;
							}
							/**
							 * Добавляем значение
							 */
							$aOptionRows[] = $sKey . ':' . $sVal;
						}
					}
					/**
					 * Формируем новый идентификатор сета
					 */
					$sSetId .= '.' . implode('.', $aOptionRows);
				}
				/**
				 * Массив значений товара для заказа
				 */
				$aParamsAdd = array(
					/**
					 * Заголовок
					 */
					'title' => $oProduct->getAttribute('title'),
					/**
					 * Количество
					 */
					'quantity' => $aParams['quantity'],
					/**
					 * Комплектация
					 */
					'bundle' => $aParams['bundle'],
					/**
					 * Опции
					 */
					'options' => $aOptions
				);
				/**
				 * Добавляем товар в список
				 */
				$this->aProducts[$sSetId] = $aParamsAdd;
				/**
				 * Полная стоимость
				 */
				$this->aProducts[$sSetId]['price'] = $this->getProductPriceBySetId($sSetId);
			}
			/**
			 * Возвращаем сет товара
			 */
			return $sSetId;
		}
	}

	/**
	 * Изменение параметров товара
	 * @param $sSetId
	 * @param $aProduct Новые данные товара
	 * @param $aOptions Новые данные опций
	 * @return bool
	 */
	public function editProduct($sSetId, $aProduct = false, $aOptionsNew = false) {
		/**
		 * Проверяем наличие сета
		 */
		if(!$this->isProductExist($sSetId)) {
			return false;
		}
		/**
		 * Если переданы данные на товар
		 */
		if($aProduct) {
			/**
			 * Если есть заголовок
			 */
			if(isset($aProduct['title'])) {
				$this->aProducts[$sSetId]['title'] = $aProduct['title'];
			}
			/**
			 * Если есть стоимость
			 */
			if(isset($aProduct['price'])) {
				$this->aProducts[$sSetId]['price'] = floatval($aProduct['price']);
			}
			/**
			 * Если есть количество
			 */
			if(isset($aProduct['quantity'])) {
				$this->aProducts[$sSetId]['quantity'] = intval($aProduct['quantity']);
			}
		}
		/**
		 * Если переданы данные на опции
		 */
		if($aOptionsNew) {
			/**
			 * Если есть расширенные опции
			 */
			if(isset($this->aProducts[$sSetId]['options']['ext'])) {
				/**
				 * Обрабатываем имеющиеся опции
				 */
				foreach($this->aProducts[$sSetId]['options']['ext'] as $sKey => $aOption) {
					/**
					 * Если опция есть в новом списке
					 */
					if(isset($aOptionsNew[$sKey])) {
						/**
						 * Если значение не совпадает
						 */
						if($aOption['value_id'] != $aOptionsNew[$sKey]['value_id']) {
							/**
							 * Заменяем значения
							 */
							$this->aProducts[$sSetId]['options']['ext'][$sKey] = array(
								'value_id' => intval($aOptionsNew['value_id']),
								'title' => $aOptionsNew['title'],
								'price' => $aOptionsNew['price']
							);
						}
						/**
						 * Убираем опцию из нового списка
						 */
						unset($aOptionsNew[$sKey]);
					} else {
						/**
						 * Удаляем опцию
						 */
						unset($this->aProducts[$sSetId]['options']['ext'][$sKey]);
					}
				}
			}
			/**
			 * Очищаем опции, установленные вручную ранее
			 */
			unset($this->aProducts[$sSetId]['options']['man']);
			/**
			 * Обрабатываем оставшиеся новые опции в списке
			 */
			foreach($aOptionsNew as $sKey => $aOption) {

				/**
				 * Если первая буква n
				 */
				if(substr($sKey, 0, 1) === 'n') {
					/**
					 * Добавляем новую опцию в ручной список
					 */
					$cnt = count($this->aProducts[$sSetId]['options']['man']);
					$this->aProducts[$sSetId]['options']['man'][$cnt]['title'] = $aOption['title'];
					/**
					 * Если установлена цена
					 */
					if(isset($aOption['price'])) {
						$this->aProducts[$sSetId]['options']['man'][$cnt]['price'] = $aOption['price'];
					}
				} else {
					/**
					 * Добавляем новую опцию
					 */
					$this->aProducts[$sSetId]['options']['ext'][$sKey] = array(
						'value_id' => $aOption['value_id'],
						'title' => $aOption['title'],
						'price' => $aOption['price']
					);
				}
			}
		} else {
			unset($this->aProducts[$sSetId]['options']);
		}
	}

	/**
	 * Установка параметров покупаемых товаров в заказе
	 * XXX нужно пересмотреть этот метод
	 * @param unknown_type $sSetId
	 * @param unknown_type $aProducts
	 */
	public function setProduct($sSetId, $aParams = false) {
		/**
		 * Если передан массив значений
		 */
		if(is_array($aParams)) {
			/**
			 * Если товар существует
			 */
			if($this->isProductExist($sSetId)) {
				/**
				 * Обрабатываем каждое значение
				 */
				foreach ($aParams as $sKey => $sVal) {

					/**
					 * Добавляем параметры
					 */
					$this->aProducts[$sSetId][$sKey] = $sVal;
				}
			}
			/**
			 * Если количество товара равно 0
			 */
			if($this->aProducts[$sSetId]['quantity'] == 0) {
				/**
				 * Удаляем товар из корзины
				 */
				$this->deleteProduct($sSetId);
			}
		}
	}

	public function isProductExist($iProductId) {
		if(isset($this->aProducts[$iProductId])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получить товар из корзины по идентификатору
	 * @param <type> $iId
	 */
	public function getProduct($sSetId) {
		/**
		 * Получаем чистый идентификатор без примесей опций
		 */
		$iProductId = $this->getProductIdBySetId($sSetId);
		/**
		 * Получаем товар из списка
		 */
		$oProduct = $this->oProductList->getProductById($iProductId);
		/**
		 * Если товара нет в списке
		 */
		if(!$this->oProductList->getProductById($iProductId)) {
			/**
			 * Загружаем товар по идентификатору
			 */
			$oProduct = new SBProduct();
			$oProduct->load($iProductId);
			/**
			 * Добавляем в список
			 */
			$this->oProductList->addProduct($oProduct);
		}
		/**
		 * Возвращаем товар
		 */
		return $oProduct;
	}

	/**
	 * Получение информации о товаре по SetId
	 */
	public function getOrderSetInfo($sSetId) {
		return $this->aProducts[$sSetId];
	}

	/**
	 * Получение информации о товарах в заказе
	 */
	public function getProducts() {
		return $this->aProducts;
	}

	/**
	 * Получение стоимости сета
	 * @param $sSetId
	 * @return float
	 */
	public function getProductPriceBySetId($sSetId) {
		global $modx;
		/**
		 * Получаем товар
		 */
		$oProduct = $this->getProduct($sSetId);
		/**
		 * Информация о заказе
		 */
		$aOrderInfo = $this->aProducts[$sSetId];
		/**
		 * Идентификатор комплектации
		 */
		$sBundleId = $aOrderInfo['bundle'];
		/**
		 * Массив опций
		 */
		$aOptions = $this->getOptionsBySetId($sSetId);
		/**
		 * Стоимость опций
		 */
		$iPriceOptions = $oProduct->getPriceByOptions($aOptions);
		/**
		 * Если не указана комплектация
		 */
		if(!isset($sBundleId) or $sBundleId === 'base') {
			/**
			 * Рассчитываем стоимость - сумма основной стоимости и стоимости опций
			 */
			$fPrice = $oProduct->getAttribute('price_full') + $iPriceOptions;
		} elseif ($sBundleId === 'personal') {
			/**
			 * Индивидуальная комплектация. Суммируем цену товара и опций
			 */
			$fPrice = $oProduct->getAttribute('price_full') + $iPriceOptions;
		} else {
			/**
			 * Любая другая комплектация. Берем стоимость комплектации
			 */
			$aBundle = $oProduct->getBundleById($sBundleId);
			/**
			 * Если стоимость пустая
			 */
			if($aBundle['price'] === '') {
				/**
				 * Определяем стоимость по факту - товар + опции
				 */
				$fPrice = $oProduct->getAttribute('price_full') + $oProduct->getPriceByOptions($aBundle['options']) + $iPriceOptions;
			} elseif(substr($aBundle['price'], 0, 1) === '+') {
				/**
				 * Если первый символ "+", суммируем стоимость самого товара, всех включенных и дополнительных опций.
				 */
				$fPrice = $oProduct->getAttribute('price_full') + substr($aBundle['price'], 1) + $iPriceOptions;
			} else {
				/**
				 * Суммируем стоимость
				 */
				$fPrice = $aBundle['price'] + $iPriceOptions;
			}
			/**
			 * Обработка надбавки
			 */
			if($aBundle['price_add'] != '') {
				$sPriceAdd = $aBundle['price_add'];
				/**
				 * Разбираем правило надбавки
				 */
				preg_match_all('/([\+\-=]?)([\d,\.]*)([%]?)/', $sPriceAdd, $aPriceAdd);
				/**
				 * Выделяем нужные данные: вид операции, число, тип операции
				 */
				$sAddOperation = $aPriceAdd[1][0];
				$sAddCost = $aPriceAdd[2][0];
				$sAddType = $aPriceAdd[3][0];

				/**
				 * Если тип операции - процент
				 */
				if($sAddType == '%') {
					/**
					 * Считаем стоимость с учетом процента и указанной опреации
					 */
					if($sAddOperation == '' or $sAddOperation == '+') {
						$fPrice = $fPrice * (1 + $sAddCost / 100);
					} elseif($sAddOperation == '-') {
						$fPrice = $fPrice * (1 - $sAddCost / 100);
					} elseif($sAddOperation == '=') {
						$fPrice = $fPrice * ($sAddCost / 100);
					}
				} else {
					/**
					 *Считаем стоимость с учетом указанного значения и операции
					 */
					if($sAddOperation == '' or $sAddOperation == '+') {
						$fPrice = $fPrice + $sAddCost;
					} elseif($sAddOperation == '-') {
						$fPrice = $fPrice - $sAddCost;
					} elseif($sAddOperation == '=') {
						$fPrice = $sAddCost;
					}
				}
				/**
				 * Округляем
				 */
				$fPrice = round($fPrice, $modx->sbshop->config['round_precision']);
			}
		}
		/**
		 * Возвращаем результат
		 */
		return $fPrice;
	}

	/**
	 * Получение количества товара в позиции по идентификатору
	 * @param  $sSetId
	 * @return
	 */
	public function getProductQuantityBySetId($sSetId) {
		return $this->aProducts[$sSetId]['quantity'];
	}

	/**
	 * Получение суммы позиции по идентификатору
	 * @param  $sSetId
	 * @return
	 */
	public function getProductSummBySetId($sSetId) {
		return $this->aProducts[$sSetId]['price'] * $this->aProducts[$sSetId]['quantity'];
	}

	/**
	 * Получение полной стоимости заказанных товаров
	 * @return float
	 */
	public function getFullPrice() {
		/**
		 * Список идентификаторов настроек товаров
		 */
		$aProductSetIds = $this->getProductSetIds();
		/**
		 * Если заказанных товаров нет
		 */
		if(count($aProductSetIds) == 0) {
			$fPrice = 0;
		} else {
			/**
			 * Загружаем список заказанных товаров
			 */
			//$this->oProductList = new SBProductList('', $aProductIds);
			/**
			 * Инициализируем массив для суммарной стоимости товаров
			 */
			$aResPrices = array();
			/**
			 * Обрабатываем каждую позицию из идентификаторов товара
			 */
			foreach ($aProductSetIds as $sSetId) {
				$aResPrices[$sSetId] = $this->getProductSummBySetId($sSetId);
			}
			$fPrice = array_sum($aResPrices);
		}
		return $fPrice;
	}

	/**
	 * Получение списка уникальный идентификаторов заказанных товаров
	 * @return array
	 */
	public function getProductIds() {
		/**
		 * Обрабатываем записи
		 */
		$aResult = array();
		foreach ($this->aProducts as $sKey => $sVal) {
			$aResult[] = intval($sKey);
		}
		return array_unique($aResult);
	}

	/**
	 * Получение списка идентификаторов товаров, включая настройку
	 */
	public function getProductSetIds() {
		if(is_array($this->aProducts)) {
			return array_keys($this->aProducts);
		}
	}

	/**
	 * Получение идентификатора товара в сете
	 * @param $sSetId
	 * @return int
	 */
	public function getProductIdBySetId($sSetId) {
		return intval($sSetId);
	}

	/**
	 * Получение идентификаторов опций из сета
	 * @param $sSetId
	 * @return array
	 */
	public function getOptionsBySetId($sSetId) {
		/**
		 * Конечный массив опций
		 */
		$aSetOptions = array();
		/**
		 * Строка с опциями
		 */
		$sSetOptions = substr($sSetId, strpos($sSetId, '.') + 1);
		/**
		 * Разделяем опции
		 */
		$aOptions = explode('.', $sSetOptions);
		/**
		 * Обрабатываем каждую опцию
		 */
		foreach($aOptions as $sOption) {
			list($sKey, $sValue) = explode(':', $sOption);
			$aSetOptions[$sKey] = $sValue;
		}
		/**
		 * Возвращаем массив опций
		 */
		return $aSetOptions;
	}

	/**
	 * Удаление товара по идентификатору сета
	 * @param <type> $iProductId
	 */
	public function deleteProduct($sSetId) {
		$this->deleteProducts(array($sSetId));
	}

	/**
	 * Удаление выбранных товаров из корзины
	 * @param <type> $aProductIds
	 */
	public function deleteProducts($sSetIds) {
		/**
		 * Обрабатываем каждый идентификатор
		 */
		foreach ($sSetIds as $sSetId) {
			/**
			 * Если указанный товар есть в корзине
			 */
			if(isset($this->aProducts[$sSetId])) {
				/**
				 * Удаляем информацию о товаре в заказе
				 */
				unset($this->aProducts[$sSetId]);
			}
		}
	}

	/**
	 * Очищаем корзину
	 */
	public function clear() {
		/**
		 * Если в корзине что-то есть
		 */
		if(isset($_SESSION['sb.order'])) {
			/**
			 * Устанавливаем статус "брошенная корзина" статус -10
			 */
			$this->setAttribute('status', '-10');
			/**
			 * Сохраняем информацию статуса
			 */
			$this->save();
			/**
			 * Убираем данные сессии
			 */
			$this->clearSession();
			/**
			 * Сбрасываем все параметры
			 */
			foreach ($this->aOrderDataKeys as $sKey) {
				$this->aOrderData[$sKey] = null;
			}
			/**
			 * Очищаем информацию о заказанных товарах
			 */
			$this->aProducts = array();
			/**
			 * Очищаем информацию о настройках заказа
			 */
			$this->aOptions = array();
		}
	}

	/**
	 * Стираем данные из корзины без сохранения информации
	 */
	public function reset() {
		/**
		 * Если в корзине что-то есть
		 */
		if(isset($_SESSION['sb.order'])) {
			/**
			 * Убираем данные сессии
			 */
			$this->clearSession();
			/**
			 * Сбрасываем все параметры
			 */
			foreach ($this->aOrderDataKeys as $sKey) {
				$this->aOrderData[$sKey] = null;
			}
			/**
			 * Очищаем информацию о заказанных товарах
			 */
			$this->aProducts = array();
			/**
			 * Очищаем информацию о настройках заказа
			 */
			$this->aOptions = array();
		}
	}

	/**
	 * Сохранение состояния заказа
	 */
	public function save() {
		global $modx;
		/**
		 * Сериализуем данные по товарам
		 */
		$this->serializeProducts();
		/**
		 * Сериализуем опции настройки заказов
		 */
		$this->serializeOptions();
		/**
		 * Сериализуем комментарии
		 */
		$this->serializeComments();
		/**
		 * Если это новый заказ
		 */
		if($this->getAttribute('id') === null) {
			/**
			 * Устанавливаем время создания заказа
			 */
			$this->setAttribute('date_add', date('Y-m-d G:i:s'));
			/**
			 * Определяем IP пользователя
			 */
			$this->setAttribute('ip', $modx->sbshop->GetIp());
			/**
			 * Установка статуса заказа "в процессе заказа" - статус 0
			 */
			$this->setAttribute('status', '0');
		} else {
			/**
			 * Устанавливаем время создания заказа
			 */
			$this->setAttribute('date_edit', date('Y-m-d G:i:s'));
		}
		/**
		 * Если есть заказанные товары
		 */
		$this->setAttribute('price', $this->getFullPrice());
		/**
		 * Подготавливаем основные параметры товара для сохранения
		 * Добавляем префикс 
		 */
		$aKeys = $this->aOrderDataKeys;
		$aData = array();
		foreach ($aKeys as $sKey) {
			if($this->aOrderData[$sKey] !== null) {
				$aData['order_' . $sKey] = $modx->db->escape($this->aOrderData[$sKey]);
			}
		}
		/**
		 * Если установлен идентификатор заказа
		 */
		if($this->getAttribute('id') == null) {
			/**
			 * Это новый заказ. Заносим в базу и если все получилось...
			 */
			if($modx->db->insert($aData, $modx->getFullTableName('sbshop_orders'))) {
				/**
				 * Устанавливаем идентификатор заказа
				 */
				$this->setAttribute('id', $modx->db->getInsertId());
			}
		} else {
			$modx->db->update($aData,$modx->getFullTableName('sbshop_orders'), 'order_id=' . $this->getAttribute('id'));
		}
		/**
		 * Заносим информацию в сессию
		 */
		$this->setSession();
	}
	
	/**
	 * Сериализация данных о заказанных товарах
	 */
	public function serializeProducts() {
		$this->aOrderData['products'] = base64_encode(serialize($this->aProducts));
		return $this->aOrderData['products'];
	}
	
	/**
	 * Десериализация данных о заказанных товарах
	 * @param unknown_type $sParams
	 */
	public function unserializeProducts($sParams = false) {
		/**
		 * Если значений нет, то переводить нечего
		 */
		if(!$sParams) {
			return;
		}
		/**
		 * десериализуем данные из JSON
		 */
		$this->aProducts = unserialize(base64_decode($sParams));
		/**
		 * Возвращаем результат
		 */
		return $this->aProducts;
	}
	
	/**
	 * Сериализация данных о параметрах заказа
	 */
	public function serializeOptions() {
		$this->aOrderData['options'] = json_encode($this->aOptions);
		return $this->aOrderData['options'];
	}
	
	/**
	 * Десериализация данных о параметрах заказа
	 * @param unknown_type $sParams
	 */
	public function unserializeOptions($sParams) {
		/**
		 * Если значений нет, то переводить нечего
		 */
		if(!$sParams) {
			return;
		}
		/**
		 * десериализуем данные из JSON
		 */
		$this->aOptions = json_decode($sParams, true);
		/**
		 * Возвращаем результат
		 */
		return $this->aOptions;
	}

	/**
	 * Сериализация комментариев к заказу
	 */
	public function serializeComments() {
		$this->aOrderData['comments'] = $this->oComments->serialize();
	}


	/**
	 * Десериализация комментариев к заказу
	 * @param <type> $sParams
	 */
	public function unserializeComments($sParams) {
		/**
		 * Если значений нет, то переводить нечего
		 */
		if(!$sParams) {
			return;
		}
		/**
		 * Выполняем десериализцию и возвращаем результат
		 */
		return $this->oComments->unserialize($sParams);
	}

	public function setSession() {
		global $modx;
		if(!$modx->sbshop->bManager) {
			$_SESSION['sb.order'] = $this->aOrderData;
		}
	}

	public function clearSession() {
		unset ($_SESSION['sb.order']);
	}
}

?>