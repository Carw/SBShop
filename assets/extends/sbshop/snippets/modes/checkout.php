<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 *
 * Экшен сниппета: Оформление заказа
 *
 */

class checkout_mode {

	protected $aTemplates; // набор шаблонов
	protected $sEvent; // Заданное действие
	protected $aError; // Информация об ошибке
	protected $sError; // Информация об ошибке

	/**
	 * Конструктор
	 */
	public function  __construct() {
		global $modx;
		/**
		 * Массив шаблонов
		 */
		$this->aTemplates = array();
		/**
		 * Инициализируем информацию об ошибках
		 */
		$this->aError = array();
		/**
		 * Если текущее действие задано
		 */
		if($modx->sbshop->getEvent(1)) {
			/**
			 * Записываем его
			 */
			$this->sEvent = $modx->sbshop->getEvent(1);
		} else {
			/**
			 * Ставим по умолчанию "cart"
			 */
			$this->sEvent = 'cart';
		}
		/**
		 * Устанавливаем набор шаблонов
		 */
		$this->setTemplates();
		/**
		 * Распределяем вызов конкретных методов в зависимости от действия
		 */
		switch ($this->sEvent) {
			case 'cart':
				/**
				 * Если послан переход на следующий шаг
				 */
				if(isset($_POST['sb_order_next'])) {
					$sRedirect = $modx->sbshop->sBaseUrl . 'checkout/register' . $modx->sbshop->config['url_suffix'];
					$modx->sendRedirect($sRedirect);
				}
				/**
				 * Если была нажата кнопка обновить
				 */
				if(isset($_POST['sb_cart_update'])) {
					$this->updateCart();
				}

				/**
				 * Если нажата кнопка "очистить корзину"
				 */
				if(isset($_POST['sb_order_clear'])) {
					$this->clearCart();
				}
				/**
				 * Если нажата кнопка далее
				 */
				if(isset($_POST['sb_customer_submit'])) {
					$this->saveRegisterCheckout();
				}
				/**
				 * Вывод информации в корзине
				 */
				$this->cartCheckout();
			break;
			case 'ok':
				/**
				 * Выводим завершающую страницу
				 */
				$this->okCheckout();
			break;
		}
	}

	/**
	 * Устанавливаем набор шаблонов
	 */
	public function setTemplates() {
		global $modx;
		/**
		 * Загружаем стандартный файл с шаблонами
		 */
		$this->aTemplates = $modx->sbshop->getSnippetTemplate('checkout_' . $this->sEvent);
	}

	/**
	 * Обновление данных корзины
	 */
	public function updateCart() {
		global $modx;
		/**
		 * Требуется ли сохранение
		 */
		$bSave = false;
		/**
		 * Если выбраны товары на удаление
		 */
		if(isset($_POST['sb_order_remove'])) {
			/**
			 * Вызов плагинов до удаления товара
			 */
			$modx->invokeEvent('OnSBShopCheckoutBeforeProducsDelete', array(
				'aProductIds' => $_POST['sb_order_remove'],
			));
			/**
			 * Удаляем выбранные товары
			 */
			$modx->sbshop->oOrder->deleteProducts($_POST['sb_order_remove']);
			/**
			 * Флаг сохранения
			 */
			$bSave = true;
		}
		/**
		 * Обрабатываем изменения количества товара
		 */
		if(isset($_POST['sb_product_quantity'])) {
			/**
			 * Вызов плагинов до изменения количества товара
			 */
			$modx->invokeEvent('OnSBShopCheckoutBeforeQuantityChange', array(
				'aProductIds' => $_POST['sb_product_quantity'],
			));
			/**
			 * Обрабатываем каждое значение
			 */
			foreach ($_POST['sb_product_quantity'] as $sKey => $sVal) {
				/**
				 * Устанавливаем новое значение
				 */
				$modx->sbshop->oOrder->setProduct($sKey, array('quantity' => intval($sVal)));
			}
			/**
			 * Флаг сохранения
			 */
			$bSave = true;
		}
		/**
		 * Если требуется сохранение
		 */
		if($bSave) {
			/**
			 * Сохраняем информацию
			 */
			$modx->sbshop->oOrder->save();
		}
	}

	/**
	 * Очистка корзины
	 */
	public function clearCart() {
		global $modx;
		/**
		 * Вызов плагинов до очистки корзины
		 */
		$modx->invokeEvent('OnSBShopCheckoutBeforeClear', array(
			'oOrder' => $modx->sbshop->oOrder,
		));
		/**
		 * Делаем очистку корзины
		 */
		$modx->sbshop->oOrder->clear();
		/**
		 * Вызов плагинов после очистки корзины
		 */
		$modx->invokeEvent('OnSBShopCheckoutAfterClear', array(
			'oOrder' => $modx->sbshop->oOrder,
		));
	}

	/**
	 * Вывод полной корзины с заказанным товаром
	 */
	public function cartCheckout() {
		global $modx;
		/**
		 * Заголовок
		 */
		$modx->setPlaceholder('sb.longtitle',$modx->sbshop->lang['checkout_cart_title']);
		/**
		 * Инициализируем переменную для вывода результата
		 */
		$sOutput = '';
		/**
		 * Если нет товаров в корзине
		 */
		$aIds = $modx->sbshop->oOrder->getProductSetIds();
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
			 * Обрабатываем товары
			 */
			foreach ($aIds as $iSetId) {
				/**
				 * Получаем товар из списка заказа
				 */
				$oProduct = $modx->sbshop->oOrder->getProduct($iSetId);
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
				$aOrderInfo = $modx->sbshop->oOrder->getOrderSetInfo($iSetId);
				/**
				 * Делаем рассчет цены товара
				 */
				$aOrderInfo['price'] = $modx->sbshop->oOrder->getProductPriceBySetId($iSetId);
				/**
				 * Добавляем плейсхолдеры информации заказа
				 */
				$aRowData = array_merge($aRowData, $aOrderInfo);
				/**
				 * Идентификатор набора товара
				 */
				$aRowData['set_id'] = $iSetId;
				/**
				 * Готовим изображения
				 */
				$aThumbsImages = $oProduct->getImagesByKey('x104');
				/**
				 * Добавляем для вывода первое изображение
				 */
				$aRowData['image.1'] = $aThumbsImages[0];
				/**
				 * Если установлена комплектация
				 */
				if(!isset($aOrderInfo['bundle']) or ($aOrderInfo['bundle'] === 'base')) {
					/**
					 * Получаем набор базовых комплектаций
					 */
					$aBaseBundle = $oProduct->getBaseBundle();
					/**
					 * Если есть опции
					 */
					if(count($aBaseBundle) > 0) {
						/**
						 * Обрабатываем каждую опцию
						 */
						$aBaseBundleOptions = array();
						foreach ($aBaseBundle as $iOptKey => $iOptVal) {
							/**
							 * Плейсхолдеры для опций в коплектации
							 */
							$aOptionRepl = $modx->sbshop->arrayToPlaceholders($oProduct->oOptions->getNamesByNameIdAndValId($iOptKey, $iOptVal));
							/**
							 * Разделитель между опцией и значением
							 */
							$aOptionRepl['[+sb.separator+]'] = $modx->sbshop->config['option_separator'];
							/**
							 * Если значение находится в списке скрываемых
							 */
							if(in_array($iOptVal, $modx->sbshop->config['hide_option_values'])) {
								/**
								 * Очищаем разделитель и значение
								 */
								$aOptionRepl['[+sb.value+]'] = '';
								$aOptionRepl['[+sb.separator+]'] = '';
							}
							/**
							 * Создаем ряд с опцией
							 */
							$aBaseBundleOptions[] = str_replace(array_keys($aOptionRepl), array_values($aOptionRepl), $this->aTemplates['bundle_option_row']);
						}
						/**
						 * Плейсхолдеры
						 */
						$aRepl = array(
							'[+sb.wrapper+]' => implode('', $aBaseBundleOptions),
							'[+sb.title+]' => $modx->sbshop->lang['order_base_bundle']
						);

						/**
						 * Делаем вставку в контенер для опций
						 */
						$sBaseBundleRepl = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['bundle_option_outer']);
					} else {
						$sBaseBundleRepl = '';
					}
					/**
					 * Записываем плейсхолдер
					 */
					$aRowData['bundle'] = $sBaseBundleRepl;
				} elseif($aOrderInfo['bundle'] === 'personal') {
					/**
					 * Получаем список индивидуальных комплектующих
					 */
					$sPersonalOptions = $oProduct->getAttribute('personal_bundle');
					$aPersonalOptions = explode(',', $sPersonalOptions);
					/**
					 * Получаем набор базовых комплектаций
					 */
					$aBaseBundle = $oProduct->getBaseBundle();
					/**
					 * Если есть опции
					 */
					if(count($aBaseBundle) > 0) {
						/**
						 * Обрабатываем каждую опцию
						 */
						$aBaseBundleOptions = array();
						foreach ($aBaseBundle as $iOptKey => $iOptVal) {
							/**
							 * Плейсхолдеры для опций в коплектации
							 */
							$aOptionRepl = $modx->sbshop->arrayToPlaceholders($oProduct->oOptions->getNamesByNameIdAndValId($iOptKey, $iOptVal));
							/**
							 * Разделитель между опцией и значением
							 */
							$aOptionRepl['[+sb.separator+]'] = $modx->sbshop->config['option_separator'];
							/**
							 * Если значение находится в списке скрываемых
							 */
							if(in_array($iOptVal, $modx->sbshop->config['hide_option_values'])) {
								/**
								 * Очищаем разделитель и значение
								 */
								$aOptionRepl['[+sb.value+]'] = '';
								$aOptionRepl['[+sb.separator+]'] = '';
							}
							/**
							 * Создаем ряд с опцией
							 */
							$aBaseBundleOptions[] = str_replace(array_keys($aOptionRepl), array_values($aOptionRepl), $this->aTemplates['bundle_option_row']);
						}
					} else {
						$aBaseBundleOptions = array();
					}
					/**
					 * Обрабатываем каждую индивидуальную опцию
					 */
					$aPersonalBundleOptions = array();
					foreach($aPersonalOptions as $sPersonalOption) {
						/**
						 * Если эта опция была выбрана
						 */
						if(isset($aOrderInfo['options']['ext'][intval($sPersonalOption)])) {
							/**
							 * Название опции
							 */
							$iOptKey = intval($sPersonalOption);
							/**
							 * Значение опции
							 */
							$iOptVal = $aOrderInfo['options']['ext'][intval($sPersonalOption)]['value_id'];
							/**
							 * Удаляем опцию из массива выбранных
							 */
							unset($aOrderInfo['options']['ext'][intval($sPersonalOption)]);
							/**
							 * Плейсхолдеры для опций в коплектации
							 */
							$aOptionRepl = $modx->sbshop->arrayToPlaceholders($oProduct->oOptions->getNamesByNameIdAndValId($iOptKey, $iOptVal));
							/**
							 * Разделитель между опцией и значением
							 */
							$aOptionRepl['[+sb.separator+]'] = $modx->sbshop->config['option_separator'];
							/**
							 * Если значение находится в списке скрываемых
							 */
							if(in_array($iOptVal, $modx->sbshop->config['hide_option_values'])) {
								/**
								 * Очищаем разделитель и значение
								 */
								$aOptionRepl['[+sb.value+]'] = '';
								$aOptionRepl['[+sb.separator+]'] = '';
							}
							/**
							 * Создаем ряд с опцией
							 */
							$aPersonalBundleOptions[] = str_replace(array_keys($aOptionRepl), array_values($aOptionRepl), $this->aTemplates['bundle_option_row']);
						}
					}
					/**
					 * Если индивидуальные комплектующие утасновлены
					 */
					if(count($aPersonalBundleOptions) > 0) {
						/**
						 * Объединяем базовые и индивидуальные комплектующие
						 */
						$aPersonalBundleOptions = array_merge($aBaseBundleOptions, $aPersonalBundleOptions);
						/**
						 * Плейсхолдеры
						 */
						$aRepl = array(
							'[+sb.wrapper+]' => implode('', $aPersonalBundleOptions),
							'[+sb.title+]' => $modx->sbshop->lang['order_personal_bundle']
						);
						/**
						 * Делаем вставку в контенер для опций
						 */
						$sPersonalBundleRepl = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['bundle_option_outer']);
					} else {
						$sPersonalBundleRepl = '';
					}
					/**
					 * Записываем плейсхолдер
					 */
					$aRowData['bundle'] = $sPersonalBundleRepl;
				} else {
					/**
					 * Получаем данные по комплектации
					 */
					$aBundle = $oProduct->getBundleById($aOrderInfo['bundle']['id']);
					/**
					 * Массив опций в комплектации
					 */
					$aBundleOptions = array();
					/**
					 * Если опции есть
					 */
					if(count($aBundle['options']) > 0) {
						/**
						 * Обрабатываем список опций в комплектации
						 */
						foreach ($aBundle['options'] as $iOptKey => $iOptVal) {
							/**
							 * Удаляем опцию из массива выбранных
							 */
							unset($aOrderInfo['options']['ext'][$iOptKey]);
							/**
							 * Плейсхолдеры для опций в коплектации
							 */
							$aOptionRepl = $modx->sbshop->arrayToPlaceholders($oProduct->oOptions->getNamesByNameIdAndValId($iOptKey, $iOptVal));
							/**
							 * Разделитель между опцией и значением
							 */
							$aOptionRepl['[+sb.separator+]'] = $modx->sbshop->config['option_separator'];
							/**
							 * Если значение находится в списке скрываемых
							 */
							if(in_array($iOptVal, $modx->sbshop->config['hide_option_values'])) {
								/**
								 * Очищаем разделитель и значение
								 */
								$aOptionRepl['[+sb.value+]'] = '';
								$aOptionRepl['[+sb.separator+]'] = '';
							}
							/**
							 * Создаем ряд
							 */
							$aBundleOptions[] = str_replace(array_keys($aOptionRepl), array_values($aOptionRepl), $this->aTemplates['bundle_option_row']);
						}
					}
					/**
					 * Если индивидуальные комплектующие утасновлены
					 */
					if(count($aBundleOptions) > 0) {
						/**
						 * Плейсхолдеры
						 */
						$aRepl = array(
							'[+sb.wrapper+]' => implode('', $aBundleOptions),
							'[+sb.title+]' => str_replace('[+sb.title+]', $aBundle['title'], $modx->sbshop->lang['order_ready_bundle'])
						);
						/**
						 * Делаем вставку в контенер для опций
						 */
						$sReadyBundleRepl = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['bundle_option_outer']);
					} else {
						$sReadyBundleRepl = '';
					}
					/**
					 * Записываем плейсхолдер
					 */
					$aRowData['bundle'] = $sReadyBundleRepl;
				}
				/**
				 * Если установлены опции в товаре
				 */
				$aOptions = array();
				if(isset($aOrderInfo['options']['ext']) and count($aOrderInfo['options']['ext']) > 0) {
					foreach ($aOrderInfo['options']['ext'] as $sOptKeyId => $aOption) {
						/**
						 * Готовим плейсхолдеры
						 */
						$aOptRepl = $modx->sbshop->arrayToPlaceholders($aOption);
						/**
						 * Вставляем в шаблон и добавляем ряд
						 */
						$aOptions[] = str_replace(array_keys($aOptRepl), array_values($aOptRepl), $this->aTemplates['option_row']);
					}
					/**
					 * Объединяем ряды и вставляем в контейнер
					 */
					$sOptions = str_replace('[+sb.wrapper+]', implode($this->aTemplates['option_separator'],$aOptions), $this->aTemplates['option_outer']);
					$aRowData['options'] = $sOptions;
				} else {
					$aRowData['options'] = '';
				}
				/**
				 * Запускаем плагины перед вставкой данных по товару в корзину
				 */
				$PlOut = $modx->invokeEvent('OnSBShopCheckoutProductPrerender', array(
					'iSetId' => $iSetId,
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
			 * Данные заказа
			 */
			$aOrderData = $modx->sbshop->oOrder->getAttributes();
			/**
			 * Добавляем ссылку для текущего режима
			 */
			$aOrderData['link_mode'] = $modx->sbshop->sBaseUrl;
			/**
			 * Информация о суффиксе в ссылке
			 */
			$aOrderData['link_suffix'] = $modx->sbshop->config['url_suffix'];
			/**
			 * Следующий шаг - оформление
			 */
			$aOrderData['link_userform'] = $modx->sbshop->sBaseUrl . 'checkout/userinfo' . $modx->sbshop->config['url_suffix'];
			/**
			 * Добавляем сформированные данные
			 */
			$aOrderData['wrapper'] = implode('', $aRows);
			/**
			 * Запускаем плагины перед вставкой данных по заказу в корзину
			 */
			$PlOut = $modx->invokeEvent('OnSBShopCheckoutOrderPrerender', array(
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
			 * Готовим набор данных пользователя
			 */
			$aCustomer = $modx->sbshop->oCustomer->getAttributes();
			/**
			 * Город по умолчанию
			 */
			if($aCustomer['city'] == '' and $modx->sbshop->config['default_city'] != '') {
				$aCustomer['city'] = $modx->sbshop->config['default_city'];
			}
			/**
			 * Готовим набор плесхолдеров
			 */
			$aRepl = array_merge($aRepl, $modx->sbshop->arrayToPlaceholders($aCustomer));
			/**
			 * Если есть информация об ошибках
			 */
			if(count($this->aError) > 0) {
				/**
				 * Обрабатываем каждую ошибку
				 */
				$sErrorRows = '';
				foreach ($this->aError as $sErrKey => $sErrVal) {
					/**
					 * Добавляем класс ошибки
					 */
					$aRepl['[+error_' . $sErrKey . '+]'] = 'error';
					/**
					 * Добавляем информацию об ошибке в шаблон
					 */
					$sErrorRows .= str_replace('[+sb.row+]', $sErrVal, $this->aTemplates['error_row']);
				}
				/**
				 * Добавляем информацию в контейнер
				 */
				$aRepl['[+sb.error+]'] = str_replace('[+sb.wrapper+]', $sErrorRows, $this->aTemplates['error_outer']);
			}
			/**
			 * Комментарий
			 */
			$aRepl['[+sb.comment+]'] = $modx->sbshop->oOrder->oComments->getFirst();
			/**
			 * Вставляем список товаров в контейнер корзины
			 */
			$sOutput = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['cart_filled']);
		}
		echo $sOutput;
	}

	/**
	 * Сохранение информации полученной от пользователя
	 */
	public function saveRegisterCheckout() {
		global $modx;
		/**
		 * Флаг для определения ошибки
		 */
		$bError = false;
		/**
		 * Проверяем ФИО
		 */
		if(!$_POST['sb_customer_fullname']) {
			$this->aError['sb_customer_fullname'] = $modx->sbshop->lang['customer_error_fullname'];
			$bError = true;
		} else {
			/**
			 * Устанавливаем параметр имени
			 */
			$modx->sbshop->oCustomer->setAttribute('fullname', htmlspecialchars($_POST['sb_customer_fullname'], ENT_QUOTES));
		}
		/**
		 * Проверяем телефон
		 */
		if(!$_POST['sb_customer_phone']) {
			$this->aError['sb_customer_phone'] = $modx->sbshop->lang['customer_error_phone'];
			$bError = true;
		} else {
			/**
			 * Устанавливаем параметр имени
			 */
			$modx->sbshop->oCustomer->setAttribute('phone', htmlspecialchars($_POST['sb_customer_phone'], ENT_QUOTES));
		}
		/**
		 * Проверяем телефон
		 */
		if($_POST['sb_customer_email'] and !$modx->sbshop->check('email',$_POST['sb_customer_email'])) {
			$this->aError['sb_customer_email'] = $modx->sbshop->lang['customer_error_email'];
			$bError = true;
		} else {
			/**
			 * Устанавливаем параметр имени
			 */
			$modx->sbshop->oCustomer->setAttribute('email', htmlspecialchars($_POST['sb_customer_email'], ENT_QUOTES));
		}
		/**
		 * Проверяем телефон
		 */
		if(!$_POST['sb_customer_city']) {
			$this->aError['sb_customer_city'] = $modx->sbshop->lang['customer_error_city'];
			$bError = true;
		} else {
			/**
			 * Устанавливаем параметр имени
			 */
			$modx->sbshop->oCustomer->setAttribute('city', htmlspecialchars($_POST['sb_customer_city'], ENT_QUOTES));
		}
		/**
		 * Проверяем телефон
		 */
		if(!$_POST['sb_customer_address']) {
			$this->aError['sb_customer_address'] = $modx->sbshop->lang['customer_error_address'];
			$bError = true;
		} else {
			/**
			 * Устанавливаем параметр имени
			 */
			$modx->sbshop->oCustomer->setAttribute('address', htmlspecialchars($_POST['sb_customer_address'], ENT_QUOTES));
		}
		/**
		 * Проверяем комментарий
		 * @todo перенести эскейп на сохранение!
		 */
		if($_POST['sb_order_comment']) {
			$modx->sbshop->oOrder->oComments->add(htmlspecialchars($_POST['sb_order_comment'], ENT_QUOTES));
		}
		/**
		 * Если ошибок не обнаружено
		 */
		if(!$bError) {
			/**
			 * Вызов плагинов до сохранения информации о клиенте
			 */
			$modx->invokeEvent('OnSBShopCheckoutBeforeClientAdd', array(
				'oCustomer' => $modx->sbshop->oCustomer
			));
			/**
			 * Сохраняем информацию клиента
			 */
			$modx->sbshop->oCustomer->save();
			/**
			 * Устанавливаем идентификатор клиента в заказе
			 */
			$modx->sbshop->oOrder->setAttribute('user',$modx->sbshop->oCustomer->getAttribute('id'));
			/**
			 * Сохраняем данные заказа
			 */
			$modx->sbshop->oOrder->save();
			/**
			 * Редирект на следующий шаг
			 */
			$sUrl = $modx->sbshop->sBaseUrl . 'checkout/ok' . $modx->sbshop->config['url_suffix'];
			header('Location: ' . $sUrl);
		}
	}

	/**
	 * Завершение оформления заказа
	 */
	public function okCheckout() {
		global $modx;
		/**
		 * Заголовок
		 */
		$modx->setPlaceholder('sb.longtitle',$modx->sbshop->lang['checkout_cart_title']);
		/**
		 * Если данные пользователя установлены
		 */
		if($modx->sbshop->oOrder->getAttribute('user')) {
			/**
			 * Загружаем информацию о клиенте
			 */
			$modx->sbshop->oCustomer->loadById($modx->sbshop->oOrder->getAttribute('user'));
			/**
			 * Устанавливаем статус заказа
			 */
			$modx->sbshop->oOrder->setAttribute('status','10');
			/**
			 * Сохраняем информацию о заказе
			 */
			$modx->sbshop->oOrder->save();
			/**
			 * Формируем письмо
			 */
			$aTemplates = $modx->sbshop->getSnippetTemplate('checkout_notice');
			/**
			 * Набор плейсхолдеров
			 */
			$aRepl = $modx->sbshop->arrayToPlaceholders($modx->sbshop->oOrder->getAttributes());
			/**
			 * Данные заказчика
			 */
			$aRepl = array_merge($aRepl,$modx->sbshop->arrayToPlaceholders($modx->sbshop->oCustomer->getAttributes()));
			/**
			 * Сообщение
			 */
			$sMessage = str_replace(array_keys($aRepl), array_values($aRepl), $aTemplates['admin_notice']);
			/**
			 * Подключаем класс почты
			 */
			include_once MODX_BASE_PATH . "manager/includes/controls/class.phpmailer.php";
			/**
			 * Настраиваем отправку
			 */
			$mail = new PHPMailer();
			$mail->IsMail();
			$mail->CharSet = $modx->config['modx_charset'];
			$mail->IsHTML(true);
			$mail->From = $modx->config['emailsender'];
			$mail->FromName	= $modx->config['site_name'];
			$mail->Subject = $modx->sbshop->lang['notice_subject'];
			$mail->Body = $sMessage;
			$mail->AddAddress($modx->sbshop->config['notice_email']);
			/**
			 * Вызов плагинов после очистки корзины
			 */
			$PlOut = $modx->invokeEvent('OnSBShopCheckoutBeforeMailSend', array(
				'oOrder' => $modx->sbshop->oOrder,
				'oCustomer' => $modx->sbshop->oCustomer,
				'oMail' => $mail
			));
			/**
			 * Берем результат работы первого плагина, если это массив.
			 */
			if (is_object($PlOut[0])) {
				$mail = $PlOut[0];
			}
			/**
			 * Отправляем письмо
			 */
			$mail->send();
			/**
			 * Вызов плагинов до очистки корзины после завершения оформления
			 */
			$modx->invokeEvent('OnSBShopCheckoutBeforeOrderComplete', array(
				'oOrder' => $modx->sbshop->oOrder,
				'oCustomer' => $modx->sbshop->oCustomer
			));
			/**
			 * Очищаем заказ
			 */
			$modx->sbshop->oOrder->reset();
			/**
			 * Инициализируем переменную для вывода результата
			 */
			$sOutput = $this->aTemplates['register_ok'];
			/**
			 * Выводим
			 */
			echo $sOutput;
		} else {
			/**
			 * Пользователь явно попал куда-то не туда, отправляем на главную
			 */
			header('Location: ' . MODX_SITE_URL);
		}

	}

}

?>
