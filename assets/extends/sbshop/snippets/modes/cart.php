<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 * 
 * Экшен сниппета: Вывод краткой информации корзины
 * 
 */

class cart_mode {
	
	protected $aTemplates; // Массив с набором шаблонов
	
	/**
	 * Конструктор
	 */
	public function __construct() {
		global $modx;
		/**
		 * Если передан запрос на переход к оформлению
		 */
		if(isset($_POST['sb_order_next'])) {
			/**
			 * Редиректим
			 */
			$this->redirectNext();
		}
		/**
		 * Если передана информация на удаление товара
		 */
		if(isset($_POST['sb_order_remove'])) {
			/**
			 * Удаляем
			 */
			$this->deleteCart();
		}
		/**
		 * Если пришла информация через POST для добавления в корзину
		 */
		if(isset($_POST['sb_order_add'])) {
			/**
			 * Передаем на сохранение нового заказа
			 */
			$this->saveCart();
		} elseif(isset($_POST['sb_order_clear'])) {
			/**
			 * Очищаем заказ
			 */
			$this->clearCart();
		}
		/**
		 * Получаем шаблоны
		 */
		$this->setTemplates();
		/**
		 * Вывод корзины товаров
		 */
		$modx->setPlaceholder('sb.cart',$this->outputCart());
	}
	
	/**
	 * Обработка полученной ифнормации для заказа
	 */
	public function saveCart() {
		global $modx;
		/**
		 * Проверяем все данные на безопасность
		 */
		if($this->checkData()) {
			/**
			 * Сохраняем результат
			 */
			$modx->sbshop->oOrder->save();
		}
		
	}

	/**
	 * Очищаем корзину
	 */
	public function clearCart() {
		global $modx;
		$modx->sbshop->oOrder->clear();
	}

	/**
	 * Проверка полученных данных.
	 */
	public function checkData() {
		global $modx;
		/**
		 * Флаг указывающий на ошибку
		 */
		$bError = false;
		/**
		 * Проверяем идентификаторо заказываемого товара
		 */
		if(isset($_POST['sb_order_add'])) {
			/**
			 * Если идентификатор соответствует определенному формату
			 */
			if(preg_match('/^\d+(:[\d\.]+\d+)*$/', $_POST['sb_order_add'])) {
				/**
				 * Используем его
				 */
				$iProductId = $_POST['sb_order_add'];
			} else {
				/**
				 * Выделяем идентификатор как целое число
				 */
				$iProductId = intval($_POST['sb_order_add']);
			}
			/**
			 * Обрабатываем полученные параметры выбора товара. Это должен быть массив.
			 */
			if(isset($_POST['sbprod']) and is_array($_POST['sbprod'])) {
				/**
				 * Массив значений для добавления
				 */
				$aParams = array();
				/**
				 * Если задана комплектация
				 */
				if(isset($_POST['sbprod']['bundle'])) {
					$sVal = $_POST['sbprod']['bundle'];
					/**
					 * Если установлена индивидиуальная комплектация
					 */
					if($sVal === 'personal') {
						/**
						 * Если есть опции из индивидуальной комплектации
						 */
						if(is_array($_POST['sbprod']['personaloptions']) and count($_POST['sbprod']['personaloptions']) > 0) {
							/**
							 * Обрабатываем опции
							 */
							foreach ($_POST['sbprod']['personaloptions'] as $sOptKey => $sOptVal) {
								/**
								 * Заносим в общий массив значений переводя все значения в числа
								 */
								$aParams['options'][intval($sOptKey)] = intval($sOptVal);
							}
						} else {
							$sVal = 'base';
						}
					} elseif($sVal !== 'base' and $sVal !== 'personal') {
						$sVal = intval($sVal);
					}
					$aParams['bundle'] = $sVal;
				} else {
					$aParams['bundle'] = 'base';
				}
				/**
				 * Если установлены опции
				 */
				if($_POST['sbprod']['options']) {
					/**
					 * Обрабатываем опции
					 */
					foreach ($_POST['sbprod']['options'] as $sOptKey => $sOptVal) {
						/**
						 * Заносим в общий массив значений переводя все значения в числа
						 */
						$aParams['options'][intval($sOptKey)] = intval($sOptVal);
					}
				}
				/**
				 * Если задано количество
				 */
				if($_POST['sbprod']['quantity']) {
					/**
					 * Устанавливаем количество
					 */
					$aParams['quantity'] = intval($_POST['sbprod']['quantity']);
				} else {
					/**
					 * Устанавливаем 1 по умолчанию
					 */
					$aParams['quantity'] = 1;
				}
				/**
				 * Вызов плагинов перед обработкой добавляемого товара
				 */
				$PlOut = $modx->invokeEvent('OnSBShopCartBeforeProductAdd', array(
					'oOrder' => $modx->sbshop->oOrder,
					'iProductId' => $iProductId,
					'aProductParams' => $aParams
				));
				/**
				 * Берем результат работы первого плагина, если это массив.
				 */
				if (is_array($PlOut[0])) {
					$aParams = $PlOut[0];
				}
				/**
				 * Заносим значения в заказ
				 */
				$sSet = $modx->sbshop->oOrder->addProduct($iProductId, $aParams);
				/**
				 * Вызов плагинов после обработки добавляемого товара
				 */
				$modx->invokeEvent('OnSBShopCartAfterProductAdd', array(
					'oOrder' => $modx->sbshop->oOrder,
					'sProductId' => $iProductId,
					'aProductParams' => $aParams,
					'sSet' => $sSet
				));
			} else {
				$bError = true;
			}
		} else {
			$bError = true;
		}
		return !$bError;
	}

	/**
	 * Получение набора шаблонов
	 */
	public function setTemplates() {
		global $modx;
		/**
		 * Загружаем стандартный файл с шаблонами
		 */
		$this->aTemplates = $modx->sbshop->getSnippetTemplate('cart');
	}

	/**
	 * Вывод корзины
	 */
	public function outputCart() {
		global $modx;
		/**
		 * Инициализируем переменную для вывода результата
		 */
		$sOutput = '';
		/**
		 * Получаем набор сетов
		 */
		$aIds = $modx->sbshop->oOrder->getProductSetIds();
		/**
		 * Если нет товаров в корзине
		 */
		if(count($aIds) == 0) {
			/**
			 * Выводим шаблон пустой корзины
			 */
			$sOutput = $this->aTemplates['cart_empty'];
		} else {
			/**
			 * Инициализируем массив товаров в корзине
			 */
			$aRows = array();
			/**
			 * Общее количество товара в заказе
			 */
			$iQuantity = 0;
			/**
			 * Обрабатываем товары
			 */
			foreach ($aIds as $sSetId) {
				/**
				 * Получаем товар из списка заказа
				 */
				$oProduct = $modx->sbshop->oOrder->getProduct($sSetId);
				/**
				 * Получаем параметры товара
				 */
				$aProduct = $oProduct->getAttributes();
				/**
				 * Плейсхолдеры параметров товара
				 */
				$aRowData = $aProduct;
				/**
				 * Получаем информацию о количестве и прочих условиях заказа товара
				 */
				$aOrderInfo = $modx->sbshop->oOrder->getOrderSetInfo($sSetId);
				/**
				 * Прибавляем количество товара
				 */
				$iQuantity += $aOrderInfo['quantity'];
				/**
				 * Делаем рассчет цены товара
				 */
				$aOrderInfo['price'] = $modx->sbshop->oOrder->getProductPriceBySetId($sSetId);
				/**
				 * Добавляем плейсхолдеры информации заказа
				 */
				$aRowData = array_merge($aRowData, $aOrderInfo);
				/**
				 * Идентификатор набора товара
				 */
				$aRowData['set_id'] = $sSetId;
				/**
				 * Если установлены опции в товаре
				 */
				$aOptions = array();
				if(isset($aOrderInfo['options']) and count($aOrderInfo['options']) > 0) {
					foreach ($aOrderInfo['options'] as $sOptKeyId => $sOptValId) {
						/**
						 * Получаем название опции и значения по идентификаторам
						 */
						$aOptionData = $oProduct->oOptions->getNamesByNameIdAndValId($sOptKeyId,$sOptValId);
						/**
						 * Убираем переносы строки у названия
						 */
						$aOptionData['title'] = str_replace('<br>', '', $aOptionData['title']);
						/**
						 * Готовим плейсхолдеры
						 */
						$aOptRepl = $modx->sbshop->arrayToPlaceholders($aOptionData);
						/**
						 * Разделитель между опцией и значением
						 */
						$aOptRepl['[+sb.separator+]'] = $modx->sbshop->config['option_separator'];
						/**
						 * Если значение находится в списке скрываемых
						 */
						if(in_array($sOptValId, $modx->sbshop->config['hide_option_values'])) {
							/**
							 * Очищаем разделитель и значение
							 */
							$aOptRepl['[+sb.value+]'] = '';
							$aOptRepl['[+sb.separator+]'] = '';
						}
						/**
						 * Вставляем в шаблон и добавляем ряд
						 */
						$aOptions[] = str_replace(array_keys($aOptRepl), array_values($aOptRepl), $this->aTemplates['option_row']);
					}
					/**
					 * Объединяем ряды и вставляем в контейнер
					 */
					$sOptions = str_replace('[+sb.wrapper+]', implode($this->aTemplates['option_separator'], $aOptions), $this->aTemplates['option_outer']);
					$aRowData['options'] = $sOptions;
				} else {
					$aRowData['options'] = '';
				}
				/**
				 * Запускаем плагины перед вставкой данных по товару в корзину
				 */
				$PlOut = $modx->invokeEvent('OnSBShopCartProductPrerender', array(
					'sSetId' => $sSetId,
					'aRowData' => $aRowData
				));
				/**
				 * Берем результат работы первого плагина, если это массив.
				 */
				if (is_array($PlOut[0])) {
					$aRowData = $PlOut[0];
				}
				/**
				 * Готовим плейсхолдеры для товара
				 */
				$aRepl = $modx->sbshop->arrayToPlaceholders($aRowData);
				/**
				 * Вставляем данные в шаблон
				 */
				$aRows[] = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['cart_row']);
			}
			/**
			 * Вставляем ряды в контейнер списка товаров
			 */
			$sOutput = str_replace('[+sb.wrapper+]', implode('', $aRows), $this->aTemplates['cart_outer']);
			/**
			 * Данные заказа
			 */
			$aOrderData = $modx->sbshop->oOrder->getAttributes();
			/**
			 * Добавляем количество товара
			 */
			$aOrderData['quantity'] = $iQuantity;
			/**
			 * Добавляем ссылку на оформление заказа
			 */
			$aOrderData['link_checkout'] = $modx->sbshop->sBaseUrl . 'checkout' . $modx->sbshop->config['url_suffix'];
			/**
			 * Добавляем сформированные данные
			 */
			$aOrderData['wrapper'] = $sOutput;
			/**
			 * Запускаем плагины перед вставкой данных по заказу в корзину
			 */
			$PlOut = $modx->invokeEvent('OnSBShopCartOrderPrerender', array(
				'oOrder' => $modx->sbshop->oOrder,
				'aOrderData' => $aOrderData
			));
			/**
			 * Берем результат работы первого плагина, если это массив.
			 */
			if (is_array($PlOut[0])) {
				$aOrderData = $PlOut[0];
			}
			/**
			 * Подготавливаем плейсхолдеры для общего контейнера корзины
			 */
			$aRepl = $modx->sbshop->arrayToPlaceholders($aOrderData);
			/**
			 * Вставляем список товаров в контейнер корзины
			 */
			$sOutput = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['cart_filled']);
		}
		return $sOutput;
	}

	/**
	 * Редирект на следующий шаг
	 */
	public function redirectNext() {
		global $modx;
		/**
		 * Адрес редиректа
		 */
		$sRedirect = $modx->sbshop->sBaseUrl . 'checkout' . $modx->sbshop->config['url_suffix'];
		/**
		 * Отправляем
		 */
		$modx->sendRedirect($sRedirect);
		exit;
	}

	/**
	 * Удаление товаров из заказа
	 */
	public function deleteCart() {
		global $modx;
		/**
		 * Вызов плагинов до очистки корзины
		 */
		$modx->invokeEvent('OnSBShopCartBeforeClear', array(
			'oOrder' => $modx->sbshop->oOrder,
		));
		/**
		 * Удаляем выбранные товары
		 */
		$modx->sbshop->oOrder->deleteProducts($_POST['sb_order_remove']);
		/**
		 * Вызов плагинов после очистки корзины
		 */
		$modx->invokeEvent('OnSBShopCartAfterClear', array(
			'oOrder' => $modx->sbshop->oOrder,
		));
		/**
		 * Сохраняем результат
		 */
		$modx->sbshop->oOrder->save();
	}
}

?>