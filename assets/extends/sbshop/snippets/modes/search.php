<?php

/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 *
 * Экшен сниппета: Поиск товара
 *
 */

class search_mode {

	public $aCategories; // массив категорий
	public $aProducts; // массив категорий

	/**
	 * Конструктор
	 */
	public function  __construct() {
		global $modx;
		/**
		 * Получаем шаблон
		 */
		$aTemplates = $modx->sbshop->getSnippetTemplate('search');
        /**
		 * Получаем список всех категорий
		 */
		$oCategories = new SBCatTree(0, 10);
		/**
		 * Массив уровней
		 */
		$aCategories = $oCategories->getCategories();
		/**
		 * Список рядов
		 */
		$sRows = '';
		/**
		 * Загружаем товары
		 */
		$oProducts = new SBProductList();
		$oProducts->loadAllList();
		$aProducts = $oProducts->getProductList();
		/**
		 * Количество разделов
		 */
		$iCategoryCnt = count($aCategories);
		/**
		 * Количество товаров
		 */
		$iProductCnt = count($aProducts);
		/**
		 * Счетчик комплектаций
		 */
		$iBundleCnt = 0;
		/**
		 * Счетчик опций
		 */
		$iOptionCnt = 0;
		/**
		 * Подоготовленнные списки товаров
		 */
		$aProductLists = array();
		/**
		 * Обрабатываем весь массив товаров
		 */
		foreach ($aProducts as $oProduct) {
			$aProductLists[$oProduct->getAttribute('category')][$oProduct->getAttribute('id')] = $oProduct;
		}
		/**
		 * Удаляем массив товаров
		 */
		unset($aProducts);
		/**
		 * Обрабатываем каждую категорию
		 */
		foreach ($aCategories as $oCategory) {
			/**
			 * Установка заголовков в виде глобальных плейсхолдеров
			 */
			$modx->setPlaceholder('sb.title', $modx->sbshop->lang['price_title']);
			/**
			 * Набор плейсхолдеров
			 */
			$aCatRepl = $modx->sbshop->arrayToPlaceholders($oCategory->getAttributes());
			/**
			 * Добавляем информацию о разделе
			 */
			$sCategory = str_replace(array_keys($aCatRepl), array_values($aCatRepl), $aTemplates['category_row']);
			/**
			 * Обрабатываем товары
			 */
			$sProducts = '';
			if(count($aProductLists[$oCategory->getAttribute('id')]) > 0) {
				foreach ($aProductLists[$oCategory->getAttribute('id')] as $oProduct) {
					/**
					 * Готовим плейсхолдеры
					 */
					$aProdRepl = $modx->sbshop->arrayToPlaceholders($oProduct->getAttributes());
					if($oProduct->getAttribute('longtitle') == '') {
						$aProdRepl['[+sb.longtitle+]'] = $oProduct->getAttribute('title');
					}
					/**
					 * Наличие товара
					 */
					if($oProduct->getAttribute('existence')) {
						/**
						 * Устанавливаем заголовок "есть в наличии" из языкового файла
						 */
						$aProdRepl['[+sb.existence+]'] = $modx->sbshop->lang['product_existence_title'];
					} else {
						/**
						 * Устанавливаем заголовок "нет в наличии" из языкового файла
						 */
						$aProdRepl['[+sb.existence+]'] = $modx->sbshop->lang['product_notexistence_title'];
					}
					$aImages = $oProduct->getImagesByKey('x104');
					$aImgRepl = $modx->sbshop->arrayToPlaceholders($aImages, 'sb.image.');
					$aProdRepl = array_merge($aProdRepl, $aImgRepl);
					/**
					 * Получаем список комплектаций
					 */
					$sBundlRows = '';
					$aBundles = $oProduct->getBundleList();
					/**
					 * Увеличиваем счетчик
					 */
					$iBundleCnt += count($aBundles);
					if(count($aBundles) > 0) {
						foreach ($aBundles as $iBundleId => $aBundle) {
							if($iBundleId !== 'personal' && $iBundleId !== 0 && $aBundle['title'] !== 'personal') {
								/**
								 * Если стоимость пустая
								 */
								if($aBundle['price'] === '') {
									/**
									 * Определяем стоимость по факту - товар + опции
									 */
									$aBundle['price'] = $oProduct->getAttribute('price') + $oProduct->getPriceByOptions($aBundle['options']);
								}
								$aBundlRepl = $modx->sbshop->arrayToPlaceholders($aBundle);
								$sBundlRows .= str_replace(array_keys($aBundlRepl), array_values($aBundlRepl), $aTemplates['bundle_row']);
							}
						}
					}
					$aProdRepl['[+sb.bundles+]'] = $sBundlRows;
					/**
					 * Обрабатываем все опции
					 */
					$aOptions = $oProduct->oOptions->getOptionNames();
					$sOptionRows = '';
					/**
					 * Обрабатываем каждую опцию
					 */
					foreach ($aOptions as $aOption) {
						/**
						 * Если опция не является скрытой
						 */
						if(!$aOption['hidden']) {
							/**
							 * Массив значений
							 */
							$aValues = $oProduct->oOptions->getValuesByOptionName($aOption['title']);
							/**
							 * Обрабатываем каждое значение
							 */
							foreach ($aValues as $aValue) {
								if(!in_array($aValue['id'], $modx->sbshop->config['hide_option_values']) && $aValue['value'] !== 'null') {

									$iOptionCnt++;

									$aValue['title'] = $aOption['title'] . ': ' . $aValue['title'];
									/**
									 * Создаем плейсхолдеры
									 */
									$aReplVal = $modx->sbshop->arrayToPlaceholders($aValue);
									/**
									 * Добавляем значение
									 */
									$sOptionRows .= str_replace(array_keys($aReplVal), array_values($aReplVal), $aTemplates['option_row']);
								}
							}
						}
					}
					$aProdRepl['[+sb.options+]'] = $sOptionRows;
					/**
					 * Добавляем информацию о товаре
					 */
					$sProduct = str_replace(array_keys($aProdRepl), array_values($aProdRepl), $aTemplates['product_row']);
					$sProducts .= $sProduct;
				}
			}
			$aCatRepl['[+sb.wrapper+]'] = $sProducts;
			/**
			 * 	Добавляем строку
			 */
			$sCategories = str_replace(array_keys($aCatRepl), array_values($aCatRepl), $aTemplates['category_row']);

			$sRows .= $sCategories;
			
		}
		$aRepl = array(
			'[+sb.cnt.categories+]' => $iCategoryCnt,
			'[+sb.cnt.products+]' => $iProductCnt,
			'[+sb.cnt.bundles+]' => $iBundleCnt,
			'[+sb.cnt.options+]' => $iOptionCnt,
			'[+sb.cnt.all+]' => $iCategoryCnt + $iProductCnt + $iBundleCnt + $iOptionCnt,
			'[+sb.wrapper+]' => $sRows,
		);
		/**
		 * Вставляем информацию в контейнер
		 */
		$sOutput = str_replace(array_keys($aRepl), array_values($aRepl), $aTemplates['outer']);
		/**
		 * Выводим
		 */
		$modx->setPlaceholder('sb.price', $sOutput);
	}

}

?>
