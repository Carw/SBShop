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
			 * @todo Здесь можно оптимизировать работу за счет счетчика товаров в разделе
			 */
			$this->oProductList = new SBProductList();
			$this->oProductList->loadFilteredListByCategoryId($this->oCategory->getAttribute('id'), false, $this->oCategory->oFilterList);
		}
		/**
		 * Устанавливаем шаблоны
		 */
		$this->aTemplates = array_merge($modx->sbshop->getSnippetTemplate('categories'), $modx->sbshop->getSnippetTemplate('productlist'));
		/**
		 * Если определена настройка "помещать в плейсхолдер"
		 */
		if($toPlaceholder) {
			/**
			 * Вывод информации о вложенных категория в плейсхолдер [+sb.innercat+]
			 */
			$modx->setPlaceholder('sb.innercat',$this->outputInnerCat());
			/**
			 * Вывод списка товаров
			 */
			$modx->setPlaceholder('sb.productlist',$this->outputProducts());
		} else {
			/**
			 * Вывод информации о вложенных категориях
			 */
			echo $this->outputInnerCat();
			/**
			 * Вывод списка товаров
			 */
			echo $this->outputProducts();
		}
	}

	/**
	 * Вывод информации для вложенных категорий
	 * @todo Нужно сделать учет вложенности по разделам еще, я его временно убил
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
			$iCount = $modx->sbshop->oGeneralCategory->getAttribute('count');

			$modx->setPlaceholder('sb.category.title', $sTitle);
			$modx->setPlaceholder('sb.category.count', $iCount);
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
		 * Если включен режим товара, то выходим
		 */
		if($aModes[0] == 'product') {
			return;
		}
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
		 * Конечный результат для вывода списка разделов
		 */
		$sOutput = '';
		/**
		 * Обрабатываем каждый уровень вложенности
		 */
		$iLevel = 0;
		foreach($aLevels as $aCatIds) {
			/**
			 * Делим список разделов на ряды, по заданным в конфигурации условиям
			 */
			if($iLevel == 0 and $modx->sbshop->config['category_columns'] > 0) {
				$aCatRows = array_chunk($aCatIds, $modx->sbshop->config['category_columns'], true);
			} else {
				$aCatRows = array($aCatIds);
			}
			/**
			 * Массив рядов
			 */
			$sCatRowsOut = '';
			/**
			 * Обрабатываем каждый ряд
			 */
			foreach($aCatRows as $aCatRow) {
				/**
				 * Массив разделов для ряда
				 */
				$sCatItems = '';
				/**
				 * Обрабатываем каждый раздел в ряду
				 */
				foreach($aCatRow as $iCatId) {
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
					$aAttributes['products'] = $this->outputInnerProducts($iCatId);
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
					 * Заголовок маленькими буквами
					 */
					$aAttributes['title.l'] = mb_strtolower($aAttributes['title'],'UTF-8');
					/**
					 * Плейсхолдер для вложенных пунктов по идентификатору
					 */
					$aAttributes['wrapper'] = '[+sb.wrapper.' . $aAttributes['id'] . '+]';
					/**
					 * Устанавливаем идентификатор родителя раздела
					 */
					$iParentId = $aAttributes['parent'];
					/**
					 * Получаем список плейсхолдеров
					 */
					$phAttr = $modx->sbshop->arrayToPlaceholders($aAttributes);
					/**
					 * Если уровень вложенности начальный
					 */
					if($iLevel == 0) {
						/**
						 * Добавляем основной раздел в ряд
						 */
						$sCatItems .= str_replace(array_keys($phAttr), array_values($phAttr), $this->aTemplates['category_item']);
					} else {
						/**
						 * Добавляем вложенный раздел в ряд
						 */
						$sCatItems .= str_replace(array_keys($phAttr), array_values($phAttr), $this->aTemplates['category_inneritem']);
					}

				}
				/**
				 * Если это начальный уровень вложенности
				 */
				if($iLevel == 0) {
					/**
					 * Добавляем ряд
					 */
					$sCatRowsOut .= str_replace('[+sb.wrapper+]', $sCatItems, $this->aTemplates['category_row']);
				} else {
					$sCatRowsOut = $sCatItems;
				}
			}
			/**
			 * Если это первый уровень
			 */
			if($iLevel == 0) {
				$sOutput = $sCatRowsOut;
			} else {
				/**
				 * Добавляем подразделы в контейнер
				 */
				$sCatRowsOut = str_replace('[+sb.wrapper+]', $sCatRowsOut, $this->aTemplates['category_inner']);
				/**
				 * Готовим необходимый враппер
				 */
				$sWrapper = '[+sb.wrapper.' . $iParentId . '+]';
				/**
				 * Вставляем подразделы в разделы
				 */
				$sOutput = str_replace($sWrapper, $sCatRowsOut, $sOutput);
			}
			/**
			 * Счетчик уровней.
			 * @todo Нужно исправить этот костыль (#36)
			 */
			$iLevel++;
		}
		/**
		 * Вставляем данные в общий контейнер
		 */
		$sOutput = str_replace('[+sb.wrapper+]', $sOutput, $this->aTemplates['category_outer']);
		/**
		 * Возвращаем результат
		 */
		return $sOutput;
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
	public function outputInnerProducts($iCatId) {
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
		 * Получаем список вложенных разделов
		 */
		$aCatIds = $this->oCatTree->getChildrenById($iCatId);
		/**
		 * Если вложенные разделы есть
		 */
		if($aCatIds) {
			/**
			 * Добавляем текущий раздел
			 */
			array_unshift($aCatIds, $iCatId);
		} else {
			$aCatIds = $iCatId;
		}
		/**
		 * Получаем список товаров
		 */
		$oProducts = new SBProductList($aCatIds, false, $iLimit);
		/**
		 * Получение списка товаров
		 */
		$aProducts = $oProducts->getProductList();
		/**
		 * Формируем список товаров
		 */
		$sOutput = $this->getProductList($aProducts, true);
		/**
		 * Отдаем результат
		 */
		return $sOutput;
	}

	/**
	 * Формирование списка товаров
	 */
	public function getProductList($aProducts, $bInnerList = false) {
		global $modx;
		/**
		 * Если есть записи
		 */
		if(count($aProducts) > 0) {
			/**
			 * Если используется группировка
			 */
			if($modx->sbshop->config['product_columns'] > 0) {
				/**
				 * Разбиваем массив товаров на группы
				 */
				$aProductGroups = array_chunk($aProducts, $modx->sbshop->config['product_columns'], true);
			} else {
				/**
				 * Делаем одну группу, где содержатся все товары
				 */
				$aProductGroups = array($aProducts);
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
					 * Идентификатор раздела
					 */
					$iCatId = $oProduct->getAttribute('category');
					/**
					 * Получаем раздел
					 */
					$oCategory = $this->oCatTree->getCategoryById($iCatId);
					/**
					 * Получаем набор ключей параметров раздела
					 */
					if($oCategory) {
						$aGeneralAttributes = array_keys($oCategory->getExtendAttributes());
					} else {
						$aGeneralAttributes = array_keys($modx->sbshop->oGeneralCategory->getExtendAttributes());
					}
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
						if($bInnerList) {
							$sAttrRows .= str_replace(array_keys($aAttrRepl), array_values($aAttrRepl), $this->aTemplates['attribute_inner_row']);
						} else {
							$sAttrRows .= str_replace(array_keys($aAttrRepl), array_values($aAttrRepl), $this->aTemplates['attribute_row']);
						}

					}
					/**
					 * Вставляем параметры в контейнер
					 */
					if($bInnerList) {
						$aProductData['attributes'] = str_replace('[+sb.wrapper+]', $sAttrRows, $this->aTemplates['attribute_inner_outer']);
					} {
						$aProductData['attributes'] = str_replace('[+sb.wrapper+]', $sAttrRows, $this->aTemplates['attribute_outer']);
					}
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
						if($bInnerList) {
							$aRows[] = str_replace(array_keys($aRepl),array_values($aRepl),$this->aTemplates['product_inner_item']);
						} else {
							$aRows[] = str_replace(array_keys($aRepl),array_values($aRepl),$this->aTemplates['product_item']);
						}
					} else {
						if($bInnerList) {
							$aRows[] = str_replace(array_keys($aRepl),array_values($aRepl),$this->aTemplates['product_inner_absent_item']);
						} else {
							$aRows[] = str_replace(array_keys($aRepl),array_values($aRepl),$this->aTemplates['product_absent_item']);
						}
					}
				}
				/**
				 * Вставляем ряды в шаблон группы
				 */
				if($bInnerList) {
					$aGroupRows[] = str_replace('[+sb.wrapper+]', implode('', $aRows), $this->aTemplates['product_inner_row']);
				} else {
					$aGroupRows[] = str_replace('[+sb.wrapper+]', implode('', $aRows), $this->aTemplates['product_row']);
				}
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
			if($bInnerList) {
				$sOutput = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['product_inner_list']);
			} {
				$sOutput = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['product_list']);
			}
		} elseif($this->oCategory->oFilterList->getFilterSelected()) {
			if($bInnerList) {
				$sOutput = $this->aTemplates['products_inner_absent'];
			} else {
				$sOutput = $this->aTemplates['products_absent'];
			}
		}
		return $sOutput;
	}

}

?>