<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 * @version 0.1a
 *
 * @desription
 *
 * Электронный магазин для MODx
 *
 * Экшен сниппета электронного магазина: Оформление заказа
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
				 * Вывод информации в корзине
				 */
				$this->cartCheckout();
			break;
			case 'register':
				/**
				 * Если нажата кнопка далее
				 */
				if(isset($_POST['sb_customer_submit'])) {
					$this->saveRegisterCheckout();
				}
				/**
				 * Вывод информации в регистрации пользователя
				 */
				$this->registerCheckout();
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
		 * Делаем очистку корзины
		 */
		$modx->sbshop->oOrder->clear();
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
				 * Плесхолдеры параметров товара
				 */
				$aRepl = $modx->sbshop->arrayToPlaceholders($aProduct);
				/**
				 * Получаем информацию о количестве и прочих условиях заказа товара
				 */
				$aOrderInfo = $modx->sbshop->oOrder->getOrderSetInfo($iSetId);
				/**
				 * Добавляем плейсхолдеры информации заказа
				 */
				$aRepl = array_merge($aRepl,$modx->sbshop->arrayToPlaceholders($aOrderInfo));
				/**
				 * Делаем рассчет цены товара
				 */
				$aRepl['[+sb.price+]'] = $modx->sbshop->oOrder->getProductPriceBySetId($iSetId);
				/**
				 * Идентификатор набора товара
				 */
				$aRepl['[+sb.set_id+]'] = $iSetId;
				/**
				 * Если установлены опции в товаре
				 */
				$aOptions = array();
				if(isset($aOrderInfo['sboptions']) and count($aOrderInfo['sboptions']) > 0) {
					foreach ($aOrderInfo['sboptions'] as $sOptKeyId => $sOptValId) {
						/**
						 * Создаем плейсхолдеры для значений
						 */
						$aOptRepl = $modx->sbshop->arrayToPlaceholders($oProduct->getNamesByNameIdAndValId($sOptKeyId,$sOptValId));
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
						$aOptions[] = str_replace(array_keys($aOptRepl), array_values($aOptRepl), $this->aTemplates['option_row']);
					}
					/**
					 * Объединяем ряды и вставляем в контейнер
					 */
					$sOptions = str_replace('[+sb.wrapper+]', implode($this->aTemplates['option_separator'],$aOptions), $this->aTemplates['option_outer']);
					$aRepl['[+sb.options+]'] = $sOptions;
				} else {
					$aRepl['[+sb.options+]'] = '';
				}
				/**
				 * Вставляем данные в шаблон
				 */
				$aRows[] = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['cart_row']);
			}
			/**
			 * Подготавливаем плейсхолдеры для общего контейнера корзины
			 */
			$aRepl = $modx->sbshop->arrayToPlaceholders($modx->sbshop->oOrder->getAttributes());
			/**
			 * Добавляем ссылку для текущего режима
			 */
			$aRepl['[+sb.link_mode+]'] = $modx->sbshop->sBaseUrl;
			/**
			 * Информация о суффиксе в ссылке
			 */
			$aRepl['[+sb.link_suffix+]'] = $modx->sbshop->config['url_suffix'];
			/**
			 * Следующий шаг - оформление
			 */
			$aRepl['[+sb.link_userform+]'] = $modx->sbshop->sBaseUrl . 'checkout/userinfo' . $modx->sbshop->config['url_suffix'];
			/**
			 * Добавляем сформированные данные
			 */
			$aRepl['[+sb.wrapper+]'] = implode($aRows);
			/**
			 * Вставляем список товаров в контейнер корзины
			 */
			$sOutput = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['cart_filled']);
		}
		echo $sOutput;
	}

	/**
	 * Получение регистрационной информации от пользователя
	 */
	public function registerCheckout() {
		global $modx;
		/**
		 * Заголовок
		 */
		$modx->setPlaceholder('sb.longtitle',$modx->sbshop->lang['checkout_register_title']);
		/**
		 * Инициализируем переменную для вывода результата
		 */
		$sOutput = '';
		/**
		 * Готовим набор данных пользователя
		 */
		$aCustomer = $modx->sbshop->oCustomer->getAttributes();
		/**
		 * Добавляем шаблон для вывода
		 */
		$sOutput = $this->aTemplates['register_form'];
		/**
		 * Подготавливаем языковые данные
		 */
		$aLang = $modx->sbshop->arrayToPlaceholders($modx->sbshop->lang,'lang.');
		/**
		 * Готовим набор плесхолдеров
		 */
		$aRepl = $modx->sbshop->arrayToPlaceholders($aCustomer);
		/**
		 * Объединяем плейсхолдеры с информацией о клиенте и языковой информацией
		 */
		$aRepl = array_merge($aRepl,$aLang);
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
		$aRepl['[+sb.comment+]'] = $modx->sbshop->oOrder->getFirstComment();
		/**
		 * Делаем замену
		 */
		$sOutput = str_replace(array_keys($aRepl), array_values($aRepl), $sOutput);

		echo $sOutput;
	}

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
			$modx->sbshop->oCustomer->setAttribute('fullname',$modx->db->escape($_POST['sb_customer_fullname']));
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
			$modx->sbshop->oCustomer->setAttribute('phone',$modx->db->escape($_POST['sb_customer_phone']));
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
			$modx->sbshop->oCustomer->setAttribute('email',$modx->db->escape($_POST['sb_customer_email']));
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
			$modx->sbshop->oCustomer->setAttribute('city',$modx->db->escape($_POST['sb_customer_city']));
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
			$modx->sbshop->oCustomer->setAttribute('address',$modx->db->escape($_POST['sb_customer_address']));
		}
		/**
		 * Проверяем комментарий
		 */
		if($_POST['sb_order_comment']) {
			$modx->sbshop->oOrder->addComment($modx->db->escape($_POST['sb_order_comment']));
		}
		/**
		 * Если ошибок не обнаружено
		 */
		if(!$bError) {
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
			 * Отправляем письмо
			 */
			$mail->send();
			/**
			 * Устанавливаем статистику
			 */
			$this->setStat();
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

	/**
	 * @todo Нужно будет убрать отсюда это безобразие и вынести в плагин
	 * @return void
	 */
	protected function setStat() {
		global $modx;
		/**
		 * Готовим статистику для Google Analytics
		 */
		$modx->sbshop->oCustomer->loadById($modx->sbshop->oOrder->getAttribute('user'));
		$sStatOut = "
			_gaq.push(['_addTrans',
				'{$modx->sbshop->oOrder->getAttribute('id')}',           // order ID - required
				'',  // affiliation or store name
				'{$modx->sbshop->oOrder->getFullPrice()}',          // total - required
				'',           // tax
				'',              // shipping
				'{$modx->sbshop->oCustomer->getAttribute('city')}',       // city
				'Свердловская область',     // state or province
				'Россия'             // country
			]);";
		/**
		 * Получаем список позиций
		 */
		$aIds = $modx->sbshop->oOrder->getProductSetIds();
		foreach($aIds as $iSetId) {
			/**
			 * Получаем товар из списка заказа
			 */
			$oProduct = $modx->sbshop->oOrder->getProduct($iSetId);
			/**
			 * Получаем информацию о количестве и прочих условиях заказа товара
			 */
			$aOrderInfo = $modx->sbshop->oOrder->getOrderSetInfo($iSetId);
			/**
			 * Если установлены опции
			 */
			/**
			 * Разделитель между опцией и значением
			 */
			$sTitle = $oProduct->getAttribute('title');
			if(isset($aOrderInfo['sboptions']) and count($aOrderInfo['sboptions']) > 0) {
				foreach ($aOrderInfo['sboptions'] as $sOptKeyId => $sOptValId) {
					$aOpt = $oProduct->getNamesByNameIdAndValId($sOptKeyId,$sOptValId);
					/**
					 * Разделитель между опцией и значением
					 */
					$aOpt['separator'] = $modx->sbshop->config['option_separator'];
					/**
					 * Если значение находится в списке скрываемых
					 */
					if(in_array($sOptValId, $modx->sbshop->config['hide_option_values'])) {
						/**
						 * Очищаем разделитель и значение
						 */
						$aOpt['value'] = '';
						$aOpt['separator'] = '';
					}
					/**
					 * Добавляем опцию к заголовку
					 */
					$sTitle .= ' + ' . $aOpt['title'] . $aOpt['separator'] . $aOpt['value'];
				}
			}
			$aProdCat = $oProduct->getExtendAttribute('Группа');
			$sStatOut .= "
				_gaq.push(['_addItem',
					'{$modx->sbshop->oOrder->getAttribute('id')}',           // order ID - required
					'{$iSetId}',           // SKU/code - required
					'{$sTitle}',        // product name
					'{$aProdCat['value']}',   // category or variation
					'{$modx->sbshop->oOrder->getProductPriceBySetId($iSetId)}',          // unit price - required
					'{$aOrderInfo['quantity']}'               // quantity - required
				  ]);";

		}
		/**
		 * Заключительная часть кода
		 */
		$sStatOut .= "\n_gaq.push(['_trackTrans']);";
		/**
		 * Устанавливаем глобальный плейсхолдер
		 */
		$modx->setPlaceholder('sb.stat', $sStatOut);
	}

}

?>
