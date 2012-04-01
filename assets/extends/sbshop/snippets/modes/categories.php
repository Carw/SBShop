<?php

/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 * 
 * Экшен сниппета: Вывод разделов
 * 
 */

class categories_mode {
	protected $sMode; // Рабочий режим
	protected $oCatTree; // Дерево вложенных категорий
	protected $oCategory; // Текущий раздел
	protected $oProductList; // Список товаров
	protected $aTemplates; // Массив с набором шаблонов

	/**
	 * Конструктор
	 * @param <type> $sMode
	 */
	public function __construct($sMode = false, $toPlaceholder = true, $iCatId = false) {
		global $modx;
		/**
		 * Экземпляр
		 */
		$this->oCategory = new SBCategory();
		/**
		 * Если передана категория
		 */
		if($iCatId) {
			/**
			 * Загружаем по идентификатору
			 */
			$this->oCategory->load($iCatId);
		} else {
			/**
			 * Берем базовую категорию
			 */
			$this->oCategory = $modx->sbshop->oGeneralCategory;
		}
		/**
		 * Список текущих режимов
		 */
		$aModes = $modx->sbshop->getModes();
		/**
		 * Если основной режим не 'product'
		 */
		if($aModes[0] !== 'product')  {
			/**
			 * Получаем дерево категорий
			 */
			$this->oCatTree = new SBCatTree($this->oCategory);
			/**
			 * Получаем список товаров
			 */
			$this->oProductList = new SBProductList();
			$this->oProductList->loadFilteredListByCategoryId($this->oCategory->getAttribute('id'), false, $this->oCategory->oFilterList->getFilterSelected());
		}
		/**
		 * Устанавливаем шаблоны
		 */
		$this->setTemplates();
		if($toPlaceholder) {
			/**
			 * Вывод информации о вложенных категория в плейсхолдер [+sb.innercat+]
			 */
			$modx->setPlaceholder('sb.innercat',$this->outputInnerCat());
			/**
			 * Вывод списка товаров
			 */
			$modx->setPlaceholder('sb.productlist',$this->outputProducts());
		}
	}
	
	/**
	 * Формирование набора стандартных шаблонов
	 * @param unknown_type $sTemplate
	 */
	public function setTemplates($sTemplate = false) {
		global $modx;
		/**
		 * Загружаем стандартный файл с шаблонами
		 */
		$this->aTemplates = array_merge($modx->sbshop->getSnippetTemplate('categories'), $modx->sbshop->getSnippetTemplate('productlist'));
	}
	
	/**
	 * Вывод информации для вложенных категорий
	 */
	public function outputInnerCat() {
		global $modx;
		/**
		 * Список текущих режимов
		 */
		$aModes = $modx->sbshop->getModes();
		/**
		 * Если первый режим - main
		 */
		if($aModes[0] == 'main') {
			/**
			 * Заговловок главной страницы каталога
			 */
			$modx->setPlaceholder('sb.category.title', $modx->sbshop->lang['shop_title']);
			$modx->setPlaceholder('sb.category.longtitle', $modx->sbshop->lang['shop_longtitle']);
		} else {
			/**
			 * Устанавливаем глобальный плейсхолдер для заголовка
			 */
			$sTitle = $modx->sbshop->oGeneralCategory->getAttribute('title');
			$sLongTitle = $modx->sbshop->oGeneralCategory->getAttribute('longtitle');

			$modx->setPlaceholder('sb.category.title', $sTitle);
			/**
			 * Если расширенный заголовок не установлен
			 */
			if($sLongTitle == '') {
				/**
				 * Устанавливаем стандартный заголовок
				 */
				$sLongTitle = $sTitle;
			}
			/**
			 * Устанавливаем глобальный плейсхолдер для заголовка
			 */
			$modx->setPlaceholder('sb.category.longtitle', $sLongTitle);
		}
		/**
		 * Если основной режим работы не 'product'
		 */
		if($aModes[0] !== 'product') {
			/**
			 * Получаем набор уровней
			 */
			$aLevels = $this->oCatTree->getCatTreeLevels();
			/**
			 * Если нет данных, то на выход
			 */
			if(count($aLevels) == 0) {
				return;
			}
			/**
			 * Записываем в содержимое основной контейнер
			 */
			$sOutput = '[+sb.wrapper+]';
			/**
			 * Счетчик уровня вложенности
			 */
			$iLevel = 0;
			foreach ($aLevels as $aLevel) {
				/**
				 * Увеличиваем счетчик
				 */
				$iLevel++;
				/**
				 * Данные с пунктами
				 */
				$sRows = '';
				/**
				 * Набор плейсхолдеров рядов для вставки
				 */
				$aRepl = array();
				/**
				 * Идентификатор враппера
				 */
				$iParentId = 0;
				/**
				 * Обрабатываем каждый вложенный пункт
				 */
				foreach ($aLevel as $iCatId) {
					/**
					 * Получаем раздел
					 */
					$oCategory = $this->oCatTree->getCategoryById($iCatId);
					/**
					 * Получаем массив параметров раздела
					 */
					$aAttributes = $oCategory->getAttributes();
					/**
					 * Получаем информацию о вложенных товарах и добавляем в массив
					 */
					$aAttributes['products'] = $this->outputInnerProducts($oCategory);
					/**
					 * Вызов плагинов до вставки данных по разделу
					 */
					$PlOut = $modx->invokeEvent('OnSBShopCategorySubcategoryPrerender', array(
						'oCategory' => $oCategory,
						'aRowData' => $aAttributes
					));
					/**
					 * Берем результат работы первого плагина, если это массив.
					 */
					if (is_array($PlOut[0])) {
						$aAttributes = $PlOut[0];
					}
					/**
					 * Получаем список плейсхолдеров
					 */
					$aPlaceholders = $modx->sbshop->arrayToPlaceholders($aAttributes);
					/**
					 * Заголовок маленькими буквами
					 */
					$aPlaceholders['[+sb.title.l+]'] = mb_strtolower($aAttributes['title'],'UTF-8');
					/**
					 * Плейсхолдер для вложенных пунктов по идентификатору
					 */
					$aPlaceholders['[+sb.wrapper+]'] = '[+sb.wrapper.' . $aAttributes['id'] . '+]';
					/**
					 * Устанавливаем идентификатор родителя раздела
					 */
					$iParentId = $aAttributes['parent'];
					/**
					 * Первый уровень обрабатывается отдельно
					 */
					if($iLevel == 1) {
						/**
						 * Делаем вставку данных в шаблон row
						 */
						$sRows = str_replace(array_keys($aPlaceholders),array_values($aPlaceholders),$this->aTemplates['category_row']);
					} else {
						/**
						 * Делаем вставку в шаблон innerrow
						 */
						$sRows = str_replace(array_keys($aPlaceholders),array_values($aPlaceholders),$this->aTemplates['category_innerrow']);
					}
					$sWrapper = '[+sb.wrapper+]';
					/**
					 * Делаем вставку в контейнер если это не первый уровень
					 */
					if($iLevel != 1) {
						$sRows = str_replace('[+sb.wrapper+]',$sRows,$this->aTemplates['category_inner']);
						$sWrapper = '[+sb.wrapper.' . $iParentId . '+]';
					}
					$aRepl[$sWrapper] .= $sRows;
				}

				/**
				 * Вставляем подготовленную информацию в основное содержимое
				 */
				$sOutput = str_replace(array_keys($aRepl), array_values($aRepl),$sOutput);
			}
			/**
			 * Если основной режим не 'main'
			 */
			if($aModes[0] !== 'main') {
				/**
				 * Подготавливаем данные для текущего раздела
				 */
				$aCategory = $modx->sbshop->oGeneralCategory->getAttributes();
				if($aCategory['longtitle'] === '') {
					$aCategory['longtitle'] = $aCategory['title'];
				}
				$aRepl = $modx->sbshop->arrayToPlaceholders($aCategory);
			} else {
				/**
				 * Берем ограниченные данные из языкового файла
				 */
				$aRepl = array(
					'[+sb.title+]' => $modx->sbshop->lang['shop_title'],
					'[+sb.longtitle+]' => $modx->sbshop->lang['shop_longtitle'],
				);
			}
			$aRepl['[+sb.wrapper+]'] = $sOutput;
			/**
			 * Делаем вставку
			 */
			$sOutput = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['category_outer']);
			/**
			 * Отдаем результат
			 */
			return $sOutput;
		}
	}
	
	/**
	 * Вывод списка товаров для текущей категории
	 */
	public function outputProducts() {
		global $modx;
		/**
		 * Список текущих режимов
		 */
		$aModes = $modx->sbshop->getModes();
		/**
		 * Если основной режим работы не 'product'
		 */
		if($aModes[0] !== 'product') {
			/**
			 * Инициализируем переменную для вывода результата
			 */
			$sOutput = '';
			/**
			 * Получение списка товаров
			 */
			$aProducts = $this->oProductList->getProductList();
			/**
			 * Формируем список товаров
			 */
			$sOutput = $this->getProductList($aProducts);
			/**
			 * Отдаем результат
			 */
			return $sOutput;
		}
	}

	/**
	 * Вывод вложенных в категорию товаров
	 * @param <type> $iProductId
	 */
	public function outputInnerProducts($oCategory) {
		global $modx;
		/**
		 * Получаем лимит количества товаров на категорию
		 */
		$iLimit = $modx->sbshop->config['innercat_products'];
		/**
		 * Если лимит равен 0
		 */
		if($iLimit == 0) {
			/**
			 * Возвращаем пустоту
			 */
			return '';
		}
		/**
		 * Переменная для вывода
		 */
		$sOutput = '';
		/**
		 * Получаем список товаров
		 */
		$oProducts = new SBProductList($oCategory->getAttribute('id'), false, $iLimit);
		/**
		 * Получение списка товаров
		 */
		$aProducts = $oProducts->getProductList();
		/**
		 * Формируем список товаров
		 */
		$sOutput = $this->getProductList($aProducts, $oCategory);
		/**
		 * Отдаем результат
		 */
		return $sOutput;
	}

	/**
	 * Формирование списка товаров
	 */
	public function getProductList($aProducts, $oCategory = false) {
		global $modx;
		/**
		 * Если есть записи
		 */
		if(count($aProducts) > 0) {
			/**
			 * Если используется группировка
			 */
			if($modx->sbshop->config['category_columns'] > 0) {
				/**
				 * Разбиваем массив товаров на группы
				 */
				$aProductGroups = array_chunk($aProducts, $modx->sbshop->config['category_columns'], true);
			} else {
				/**
				 * Делаем одну группу, где содержатся все товары
				 */
				$aProductGroups = array($aProducts);
			}
			/**
			 * Получаем набор ключей параметров раздела
			 */
			if($oCategory) {
				$aGeneralAttributes = array_keys($oCategory->getExtendAttributes());
			} else {
				$aGeneralAttributes = array_keys($modx->sbshop->oGeneralCategory->getExtendAttributes());
			}
			/**
			 * Массив групп
			 */
			$aGroupRows = array();
			/**
			 * Обрабатываем каждую группу
			 */
			foreach($aProductGroups as $aGroup) {
				/**
				 * Переменная для сбора информации о рядах
				 */
				$aRows = array();
				/**
				 * Обрабатываем каждую запись для вывода
				 */
				foreach ($aGroup as $oProduct) {
					/**
					 * Подготавливаем информацию для вставки в шаблон
					 */
					$aProductData = $oProduct->getAttributes();
					/**
					 * Если установлен параметр "Есть в наличии"
					 */
					if($oProduct->getAttribute('existence')) {
						/**
						 * Устанавливаем заголовок "есть в наличии" из языкового файла
						 */
						$aProductData['existence'] = $modx->sbshop->lang['product_existence_title'];
					} else {
						/**
						 * Устанавливаем заголовок "нет в наличии" из языкового файла
						 */
						$aProductData['existence'] = $modx->sbshop->lang['product_notexistence_title'];
					}
					/**
					 * Получаем набор параметров товара
					 */
					$aAttributes = $oProduct->getExtendPrimaryAttributes();
					/**
					 * Массив отсортированных параметров
					 */
					$aGeneralOrderAttrubutes = array();
					/**
					 * Обрабатываем каждый параметр раздела
					 */
					foreach ($aGeneralAttributes as $sKey) {
						/**
						 * Если параметр входит в список раздела
						 */
						if(isset($aAttributes[$sKey])) {
							$aGeneralOrderAttrubutes[$sKey] = $aAttributes[$sKey];
						}
					}
					/**
					 * Обновляем массив параметров
					 */
					$aAttributes = $aGeneralOrderAttrubutes;
					/**
					 * Ряды значений
					 */
					$sAttrRows = '';
					/**
					 * Обрабатываем каждый параметр
					 */
					foreach ($aAttributes as $aAttrVal) {
						/**
						 * Плейсхолдеры для параметра
						 */
						$aAttrRepl = $modx->sbshop->arrayToPlaceholders($aAttrVal);
						/**
						 * Формируем ряд
						 */
						$sAttrRows .= str_replace(array_keys($aAttrRepl), array_values($aAttrRepl), $this->aTemplates['attribute_row']);
					}
					/**
					 * Вставляем параметры в контейнер
					 */
					$aProductData['attributes'] = str_replace('[+sb.wrapper+]', $sAttrRows, $this->aTemplates['attribute_outer']);
					/**
					 * Вызов плагинов до вставки данных по товару
					 */
					$PlOut = $modx->invokeEvent('OnSBShopCategoryProductPrerender', array(
						'oProduct' => $oProduct,
						'aProductData' => $aProductData
					));
					/**
					 * Берем результат работы первого плагина, если это массив.
					 */
					if (is_array($PlOut[0])) {
					    $aProductData = $PlOut[0];
					}
					/**
					 * Готовим плейсхолдеры для вставки данных
					 */
					$aRepl = $modx->sbshop->arrayToPlaceholders($aProductData);
					/**
					 * Добавляем изображения
					 */
					$aRepl = array_merge($aRepl,$modx->sbshop->multiarrayToPlaceholders($oProduct->getAllImages(),'num','sb.image.'));
					/**
					 * Если товар в наличии
					 */
					if($oProduct->getAttribute('existence')) {
						$aRows[] = str_replace(array_keys($aRepl),array_values($aRepl),$this->aTemplates['product_row']);
					} else {
						$aRows[] = str_replace(array_keys($aRepl),array_values($aRepl),$this->aTemplates['product_absent_row']);
					}
				}
				/**
				 * Вставляем ряды в шаблон группы
				 */
				$aGroupRows[] = str_replace('[+sb.wrapper+]', implode('', $aRows), $this->aTemplates['category_group']);
			}
			/**
			 * Информация о текущей категории
			 */
			$aCategory = $modx->sbshop->oGeneralCategory->getAttributes();
			/**
			 * Если расширенный заголовок не установлен
			 */
			if($aCategory['longtitle'] == '') {
				/**
				 * Используем обычный
				 */
				$aCategory['longtitle'] = $aCategory['title'];
			}
			/**
			 * Готовим плейсхолдеры
			 */
			$aRepl = $modx->sbshop->arrayToPlaceholders($aCategory);
			$aRepl['[+sb.wrapper+]'] = implode('', $aGroupRows);
			/**
			 * Делаем замену плейсхолдеров в контейнере
			 */
			$sOutput = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['product_list']);
		} elseif($this->oCategory->oFilterList->getFilterSelected()) {
			$sOutput = $this->aTemplates['products_absent'];
		}
		return $sOutput;
	}

}

?>