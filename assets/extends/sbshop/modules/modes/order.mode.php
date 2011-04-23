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
 * Экшен модуля электронного магазина: Режим управления заказами
 *
 */

class order_mode {

	protected $sModuleLink;
	protected $sMode;
	protected $sAct;

	/**
	 * Конструктор
	 * @global <type> $modx
	 * @param <type> $sModuleLink
	 * @param <type> $sMode
	 * @param <type> $sAct
	 */
	public function __construct($sModuleLink, $sMode, $sAct = '') {
		global $modx;
		/**
		 * Устанавливаем режим менеджера
		 */
		$modx->sbshop->bManager = true;
		/**
		 * Записываем служебную информацию модуля, чтобы делать разные ссылки
		 */
		$this->sModuleLink = $sModuleLink;
		$this->sMode = $sMode;
		$this->sAct = $sAct;
		/**
		 * Обрабатываем заданное действие
		 */
		switch ($this->sAct) {
			case 'arch':
				/**
				 * Показать полную информацию о заказе
				 */
				$this->archivList();
			break;
			case 'trash':
				/**
				 * Показать полную информацию о заказе
				 */
				$this->dropedList();
			break;
			default:
				/**
				 * Вывод рабочего листа
				 */
				$this->workList();
			break;
		}
		
	}

	/**
	 * Основной список текущих заказов
	 */
	public function workList() {
		global $modx;
		/**
		 * Переменная для вывода
		 */
		$sOutput = '';
		/**
		 * Загружаем список полученных заказов
		 */
		$oOrderList = new SBOrderList(array(10,20));
		/**
		 * Получаем массив заказов
		 */
		$aOrderList = $oOrderList->getOrderList();
		/**
		 * Выводим
		 */
		$sOutput .= $this->outputList($aOrderList);

		echo $sOutput;
	}

	/**
	 * Список заказов в архиве
	 */
	public function archivList() {
		global $modx;
		/**
		 * Переменная для вывода
		 */
		$sOutput = '<h1>' . $modx->sbshop->lang['order_list_title'] . '</h1>';
		/**
		 * Загружаем список полученных заказов
		 */
		$oOrderList = new SBOrderList(array(30,-10,-20,-30));
		/**
		 * Получаем массив заказов
		 */
		$aOrderList = $oOrderList->getOrderList();
		/**
		 * Выводим
		 */
		$sOutput .= $this->outputList($aOrderList);

		echo $sOutput;
	}

	/**
	 * Список брошенных корзин
	 */
	public function dropedList() {
		global $modx;
		/**
		 * Переменная для вывода
		 */
		$sOutput = '<h1>' . $modx->sbshop->lang['order_list_title'] . '</h1>';
		/**
		 * Проверочная дата позже которой корзина считается брошенной. Сутки.
		 */
		$sCheckTime = date('Y-m-d H:i:s', time() - $modx->sbshop->config['order_timeout']);
		/**
		 * Загружаем список полученных заказов
		 */
		$oOrderList = new SBOrderList(array(0),'order_date_edit <= "' . $sCheckTime . '"');
		/**
		 * Получаем массив заказов
		 */
		$aOrderList = $oOrderList->getOrderList();
		/**
		 * Выводим
		 */
		$sOutput .= $this->outputList($aOrderList);

		echo $sOutput;
	}

	/**
	 * Вывод информации о списке заказов
	 * @global <type> $modx
	 * @param <type> $aOrderList
	 */
	public function outputList($aOrderList) {
		global $modx;
		/**
		 * Получаем шаблон вывода списока заказов
		 */
		$aTemplates = $modx->sbshop->getModuleTemplate('orderlist');
		/**
		 * Если статус изменен
		 */
		if(isset($_POST['sb_set_status'])) {
			/**
			 * Получаем номер заказа и комментарий
			 */
			foreach($_POST['sb_comment'] as $sKey => $sComment) {
				/**
				 * Если статус изменился
				 */
				if($aOrderList[$sKey]->getAttribute('status') != $_POST['sb_status_list'][$sKey]) {
					/**
					 * Устанавливаем новый статус
					 */
					$aOrderList[$sKey]->setAttribute('status', intval($_POST['sb_status_list'][$sKey]));
				}
				/**
				 * Добавляем комментарий
				 */
				$aOrderList[$sKey]->addComment($modx->db->escape($sComment));
				/**
				 * Сохраняем
				 */
				$aOrderList[$sKey]->save();
			}
		}
		/**
		 * Составляем список
		 */
		$aOrderRows = array();
		foreach ($aOrderList as $oOrder) {
			/**
			 * Готовим набор плейсхолдеров
			 */
			$aOrderRepl = $modx->sbshop->arrayToPlaceholders($oOrder->getAttributes());
			/**
			 * Ссылка на заказ
			 */
			$aOrderRepl['[+sb.link+]'] = $this->sModuleLink . '&mode=order&act=show&orderid=' . $oOrder->getAttribute('id');
			/**
			 * Статус товара
			 */
			$aOrderRepl['[+sb.status+]'] = $modx->sbshop->lang['order_status_' . $oOrder->getAttribute('status')];
			/**
			 * Идентификатор заказчика
			 */
			$iCustomerId = $oOrder->getAttribute('user');
			/**
			 * Загружаем данные пользователя
			 */
			$oCustomer = new SBCustomer($iCustomerId);
			/**
			 * Добавляем данные заказчика заказчика
			 */
			$aOrderRepl = array_merge($aOrderRepl, $modx->sbshop->arrayToPlaceholders($oCustomer->getAttributes(), 'sb.customer.'));
			/**
			 * Комментарии
			 */
			$aComments = $oOrder->getComments();
			/**
			 * Комментарии
			 */
			$sComments = '';
			/**
			 * Обрабатываем каждый комментарий
			 */
			foreach ($aComments as $iTime => $sVal) {
				/**
				 * Плейсхолдеры ряда
				 */
				$aCommRepl = array(
					'[+sb.time+]' => date('d.m.y - H:i:s', $iTime),
					'[+sb.comment+]' => $sVal
				);
				/**
				 * Добавляем ряд
				 */
				$sComments .= str_replace(array_keys($aCommRepl), array_values($aCommRepl), $aTemplates['comment_row']);
			}
			/**
			 * Добавляем плейсхолдер комментариев
			 */
			$aOrderRepl['[+sb.comments+]'] = str_replace('[+sb.wrapper+]', $sComments, $aTemplates['comment_outer']);
			/**
			 * Инициализируем массив товаров в корзине
			 */
			$aProductRows = array();
			/**
			 * Получаем список позиций
			 */
			$aIds = $oOrder->getProductSetIds();
			/**
			 * Обрабатываем товары
			 */
			foreach ($aIds as $iSetId) {
				/**
				 * Получаем товар из списка заказа
				 */
				$oProduct = $oOrder->getProduct($iSetId);
				/**
				 * Получаем параметры товара
				 */
				$aProduct = $oProduct->getAttributes();
				/**
				 * Плесхолдеры параметров товара
				 */
				$aProductRepl = $modx->sbshop->arrayToPlaceholders($aProduct);
				/**
				 * Получаем информацию о количестве и прочих условиях заказа товара
				 */
				$aOrderInfo = $oOrder->getOrderSetInfo($iSetId);
				/**
				 * Добавляем плейсхолдеры информации заказа
				 */
				$aProductRepl = array_merge($aProductRepl,$modx->sbshop->arrayToPlaceholders($aOrderInfo));
				/**
				 * Делаем рассчет цены товара
				 */
				$aProductRepl['[+sb.price+]'] = $oOrder->getProductPriceBySetId($iSetId);
				/**
				 * Общая сумма за товар
				 */
				$aProductRepl['[+sb.summ+]'] = $oOrder->getProductSummBySetId($iSetId);
				/**
				 * Если установлена комплектация
				 */
				if(isset($aOrderInfo['bundle'])) {
					switch ($aOrderInfo['bundle']) {
						case 'base':
							/**
							 * Записываем в плейсхолдер пометку базовой комплектации
							 */
							$aProductRepl['[+sb.bundle.title+]'] = $modx->sbshop->lang['order_base_bundle'];
						break;
						case 'personal':
							/**
							 * Записываем в плейсхолдер пометку персональной комплектации
							 */
							$aProductRepl['[+sb.bundle.title+]'] = $modx->sbshop->lang['order_personal_bundle'];
						break;
						default:
							/**
							 * Получаем комплектацию по идентификатору
							 */
							$aBundle = $oProduct->getBundleById($aOrderInfo['bundle']);
							/**
							 * Записываем название комплектации в плейсхолдер
							 */
							$aProductRepl['[+sb.bundle.title+]'] = $aBundle['title'];
						break;
					}
				} else {
					/**
					 * Записываем в плейсхолдер пометку базовой комплектации
					 */
					$aProductRepl['[+sb.bundle.title+]'] = $modx->sbshop->lang['order_base_bundle'];
				}
				/**
				 * Идентификатор набора товара
				 */
				$aProductRepl['[+sb.set_id+]'] = $iSetId;
				/**
				 * Если установлены опции в товаре
				 */
				$aOptions = array();
				if(isset($aOrderInfo['sboptions']) and count($aOrderInfo['sboptions']) > 0) {
					foreach ($aOrderInfo['sboptions'] as $sOptKeyId => $sOptValId) {
						/**
						 * Создаем плейсхолдеры
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
						$aOptions[] = str_replace(array_keys($aOptRepl), array_values($aOptRepl), $aTemplates['product_option_row']);
					}
					/**
					 * Объединяем ряды и вставляем в контейнер
					 */
					$sOptions = str_replace('[+sb.wrapper+]', implode($aTemplates['product_option_separator'], $aOptions), $aTemplates['product_option_outer']);
					$aProductRepl['[+sb.options+]'] = $sOptions;
				} else {
					$aProductRepl['[+sb.options+]'] = '';
				}
				/**
				 * Вставляем данные в шаблон
				 */
				$aProductRows[] = str_replace(array_keys($aProductRepl), array_values($aProductRepl), $aTemplates['product_row']);
			}
			/**
			 * Полная информация о заказанных товарах
			 */
			$aOrderRepl['[+sb.products+]'] = str_replace('[+sb.wrapper+]', implode($aProductRows), $aTemplates['product_outer']);
			/**
			 * Доступные для управления статусы
			 */
			$aStatuses = $modx->sbshop->config['status_manage'];
			/**
			 * Если текущий статус заказа входит в список
			 */
			if(in_array($oOrder->getAttribute('status'), $aStatuses)) {
				/**
				 * Список
				 */
				$aStatRows = array();
				/**
				 * Обрабатываем статусы
				 */
				foreach ($aStatuses as $iStatusId) {
					$aStRepl = array(
						'[+sb.value+]' => $iStatusId,
						'[+sb.title+]' => $modx->sbshop->lang['order_status_' . $iStatusId]
					);
					/**
					 * Если текущий статус выделен
					 */
					if($oOrder->getAttribute('status') == $iStatusId) {
						$aStatRows[] = str_replace(array_keys($aStRepl), array_values($aStRepl), $aTemplates['action_option_selected']);
					} else {
						$aStatRows[] = str_replace(array_keys($aStRepl), array_values($aStRepl), $aTemplates['action_option']);
					}
				}
				/**
				 * Заносим в контейнер и делаем плейсхолдер
				 */
				$aOrderRepl['[+sb.action+]'] = str_replace('[+sb.wrapper+]', implode($aStatRows), $aTemplates['action_outer']);
			} else {
				/**
				 * Делаем плейсхолдер управления пустым
				 */
				$aOrderRepl['[+sb.action+]'] = '';
			}
			/**
			 * Делаем замену
			 */
			$aOrderRows[] = str_replace(array_keys($aOrderRepl), array_values($aOrderRepl), $aTemplates['order_row']);
		}
		/**
		 * Плейсхолдеры для вставки в контенер
		 */
		$phModule = array(
			'[+sb.wrapper+]' => implode('', $aOrderRows),
			/**
			 * Служебные плейсхолдеры для модуля
			 */
			'[+site.url+]' => MODX_BASE_URL,
			'[+module.link+]' => $this->sModuleLink,
			'[+module.act+]' => $this->sAct,
		);
		/**
		 * Подготавливаем языковые плейсхолдеры
		 */
		$phLang = $modx->sbshop->arrayToPlaceholders($modx->sbshop->lang,'lang.');
		/**
		 * Объединяем плейсхолдеры с языковыми
		 */
		$phModule = array_merge($phModule,$phLang);
		/**
		 * Вставляем в контейнер
		 */
		$sOutput = str_replace(array_keys($phModule), array_values($phModule),  $aTemplates['order_outer']);
		/**
		 * Выводим
		 */
		return $sOutput;
	}

}

?>
