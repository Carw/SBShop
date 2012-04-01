<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 *
 * Экшен модуля: Режим массового управления ценами
 */

class pricing_mode {

	protected $sModuleLink;
	protected $sMode;
	protected $sAct;
	protected $aTemplates;

	/**
	 * Конструктор
	 * @param $sModuleLink
	 * @param $sMode
	 * @param string $sAct
	 */
	public function __construct($sModuleLink, $sMode, $sAct = '') {
		global $modx;
		/**
		 * Записываем служебную информацию модуля, чтобы делать разные ссылки
		 */
		$this->sModuleLink = $sModuleLink;
		$this->sMode = $sMode;
		$this->sAct = $sAct;
		/**
		 * Устанавливаем шаблон
		 */
		$this->aTemplates = $modx->sbshop->getModuleTemplate($sMode);
		/**
		 * Если полученны данные по изменению надбавки
		 */
		if($_POST['sb_submit_pricing']) {
			/**
			 * Изменяем надбавку
			 */
			echo $this->setPricing();
		}
		/**
		 * Выводим форму
		 */
		echo $this->editPricing();
	}

	/**
	 * Вывод страницы для управления надбавками
	 * @return void
	 */
	public function editPricing() {
		global $modx;
		/**
		 * Служебные плейсхолдеры для модуля
		 */
		$aModuleRepl['[+site.url+]'] = MODX_BASE_URL;
		$aModuleRepl['[+module.link+]'] = $this->sModuleLink;
		$aModuleRepl['[+module.act+]'] = $this->sAct;
		/**
		 * Получение дерева разделов
		 */
		$oCatTree = new SBCatTree();
		/**
		 * Получаем массив разделов из дерева
		 */
		$aCategories = $oCatTree->getAllCategories();
		/**
		 * Обрабатываем каждый раздел
		 */
		$aCategoryRows = array();
		foreach ($aCategories as $oCategory) {
			/**
			 * Плейсхолдеры для замены
			 */
			$aCatRepl = $modx->sbshop->arrayToPlaceholders($oCategory->getAttributes());

			$aCategoryRows[] = str_replace(array_keys($aCatRepl), array_values($aCatRepl), $this->aTemplates['pricing_category_row']);
		}
		/**
		 * Добавляем плейсхолдер для списка разделов
		 */
		$aModuleRepl['[+sb.categories+]'] = implode('', $aCategoryRows);
		/**
		 * Получаем список производителей
		 */
		$rs = $modx->db->select('distinct product_vendor as title', $modx->getFullTableName('sbshop_products'), '', 'product_vendor');
		/**
		 * Массив производителей
		 */
		$aVendors = $modx->db->getColumn('title', $rs);
		$aVendorRows = array();
		foreach ($aVendors as $sVendor) {
			/**
			 * Плейсхолдеры для замены
			 */
			$aVendorRows[] = str_replace('[+sb.title+]', $sVendor, $this->aTemplates['pricing_vendor_row']);
		}
		/**
		 * Добавляем плейсхолдер для списка разделов
		 */
		$aModuleRepl['[+sb.vendors+]'] = implode('', $aVendorRows);
		/**
		 * Вставляем данные в основной шаблон
		 */
		return str_replace(array_keys($aModuleRepl), array_values($aModuleRepl), $this->aTemplates['pricing_outer']);
	}

	/**
	 * Установка новых значений надбавки
	 */
	public function setPricing() {
		global $modx;
		/**
		 * Если правило для надбавки установлено
		 */
		if($_POST['pricing_add']) {
			/**
			 * Условие выбора раздела
			 */
			if($_POST['pricing_category'] !== 'all') {
				$sqlCat = 'and product_category = ' . intval($_POST['pricing_category']);
			} else {
				$sqlCat = '';
			}
			/**
			 * Условие выбора производителя
			 */
			if($_POST['pricing_vendor'] !== 'all') {
				$sqlVend = 'and product_vendor = "' . $modx->db->escape($_POST['pricing_vendor']) . '"';
			} else {
				$sqlVend = '';
			}
			/**
			 * Массив идентификаторов товаров
			 */
			$aProductIds = array();
			/**
			 * Если список идентификаторов не пуст
			 */
			if($_POST['pricing_ids'] != '') {
				/**
				 * Разбираем список идентификаторов
				 */
				$aIds = explode(',', $_POST['pricing_ids']);
				/**
				 * Массив правил
				 */
				$aRules = array();
				/**
				 * Обрабатываем каждую запись
				 */
				$iCnt = count($aIds);
				for ($i = 0; $i < $iCnt; $i++) {
					/**
					 * Если есть "." в сете, значит указан идентификатор
					 */
					if(strpos($aIds[$i], '.') !== false) {
						list($iProductId, $sOptionSet) = explode('.', $aIds[$i]);
						$iProductId = intval($iProductId);
					} elseif(strpos($aIds[$i], ':') !== false) {
						/**
						 * Есть ":" в сете, значит указано значение
						 * Идентификатор не указан и мы считаем, что нужны все товары
						 */
						$iProductId = '*';
						$sOptionSet = $aIds[$i];
					} else {
						/**
						 * Это явно просто товар
						 */
						$iProductId = intval($aIds[$i]);
						$sOptionSet = '*';
					}
					/**
					 * Добавляем идентификатор товара в массив
					 */
					$aProductIds[$iProductId] = $iProductId;
					/**
					 * Если есть ":" в сете, значит указан идентификатор опции и значение
					 */
					if(strpos($sOptionSet, ':') !== false) {
						list($iOptionId, $iOptionValue) = explode(':', $sOptionSet);
						$iOptionId = intval($iOptionId);
						$iOptionValue = intval($iOptionValue);
					} elseif($sOptionSet == '*') {
						$iOptionId = '*';
						$iOptionValue = '*';
					} else {
						$iOptionId = intval($sOptionSet);
						$iOptionValue = '*';
					}
					/**
					 * Добавляем правило в массив
					 */
					$aRules[$iProductId] = array(
						$iOptionId => $iOptionValue
					);
				}
			}
			/**
			 * Если идентификаторы указаны
			 */
			$iCnt = count($aProductIds);
			if($iCnt > 0 and !in_array('*', $aProductIds)) {
				/**
				 * На всякий случай убираем дублирующиеся значения
				 */
				array_unique($aProductIds);
				/**
				 * Обрабатываем каждый идентификатор
				 */
				for ($i = 0; $i < $iCnt; $i++) {
				    $aProductIds[$i] = intval($aProductIds[$i]);
				}
				/**
				 * Готовим запрос
				 */
				$sqlProd = 'and product_id in (' . implode(',', $aProductIds) . ')';
			} else {
				/**
				 * Готовим запрос
				 */
				$sqlProd = '';
			}
			/**
			 * Разбираем правило надбавки
			 */
			preg_match_all('/([\+\-=]?)([\d,\.]*)([%]?)/', $_POST['pricing_add'], $aPriceAdd);
			/**
			 * Выделяем нужные данные: вид операции, число, тип операции
			 */
			$sAddOperation = $aPriceAdd[1][0];
			$sAddCost = $aPriceAdd[2][0];
			$sAddType = $aPriceAdd[3][0];
			/**
			 * Пересобираем правило
			 */
			$sPriceAdd = $sAddOperation . $sAddCost . $sAddType;
			/**
			 * Делаем выборку товаров
			 */
			$rs = $modx->db->select('product_id', $modx->getFullTableName('sbshop_products'), "1 $sqlCat $sqlVend $sqlProd");
			$aProductIds = $modx->db->getColumn('product_id', $rs);
			/**
			 * Загружаем список товаров
			 */
			$oProductList = new SBProductList(false, $aProductIds);
			/**
			 * Получаем список товаров
			 */
			$aProducts = $oProductList->getProductList();
			/**
			 * Обрабатываем каждый товар
			 */
			foreach ($aProducts as $oProduct) {
				/**
				 * Если есть правило изменения надбавки для самого товара
				 */
				if($aRules[$oProduct->getAttribute('id')]['*'] === '*') {
					/**
					 * Устанавливаем надбавку
					 */
					$oProduct->setAttribute('price_add', $sPriceAdd);
				}
				/**
				 * Получаем список опций
				 */
				$aOptionNames = $oProduct->oOptions->getOptionNames();
				/**
				 * Обрабатываем каждую опцию
				 */
				foreach ($aOptionNames as $sOptionKey => $aOptionName) {
					/**
					 * Получаем список значений
					 */
					$aValues = $oProduct->oOptions->getValuesByOptionName($sOptionKey);
					/**
					 * Если опции установлены
					 */
					if(count($aValues) > 0) {
						/**
						 * Обрабатываем каждое значение
						 */
						foreach ($aValues as $aValue) {
							/**
							 * Если есть правила для этой опции и значения
							 */
							if(
								$aRules[$oProduct->getAttribute('id')][$aOptionName['id']] == $aValue['id']
								or
								$aRules['*'][$aOptionName['id']] == $aValue['id']
								or
								($aRules[$oProduct->getAttribute('id')][$aOptionName['id']] == '*' and intval($aValue['value']) > 0)
							) {
								/**
								 * Получаем список значений
								 */
								$aValues = $oProduct->oOptions->getValuesByOptionName($sOptionKey);
								/**
								 * Устанавливаем надбавку в массиве значения
								 */
								$aValue['price_add'] = $sPriceAdd;
								/**
								 * Устанавливаем надбавку для значения опции в товаре
								 */
								$oProduct->oOptions->setValueByNameIdAndValId($aOptionName['id'],$aValue['id'], $aValue);
								/**
								 * Выводим информацию
								 */
								echo '<div class="ok">Изменена опция ' . $aOptionName['id'] . ':' . $aValue['id'] . ' у товара ' . $oProduct->getAttribute('title') . ' (' . $oProduct->getAttribute('id') . ')</div>';
							}
						}
					}
				}
				/**
				 * Сохраняем товар
				 */
				$oProduct->save();
			}
		}
	}
}