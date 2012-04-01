<?php

/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 *
 * Экшен сниппета: Вывод данных в виде YML для Яндекс.Маркет
 *
 */

class yml_mode {

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
		$aTemplates = $modx->sbshop->getSnippetTemplate('yml');
		/**
		 * Формирование плейсхолдеров
		 */
		$aRepl = array(
			'[+sb.shop.name+]' => $modx->sbshop->config['shop_name'],
			'[+sb.shop.organization+]' => $modx->sbshop->config['organization'],
		);
		/**
		 * Получаем список всех категорий
		 */
		$oCategories = new SBCatTree(0, 10);
		/**
		 * Массив уровней
		 */
		$aCategories = $oCategories->getCategories();
		/**
		 * Список категорий
		 */
		$sCategories = '';
		/**
		 * Обрабатываем каждую категорию
		 */
		foreach ($aCategories as $oCategory) {
			/**
			 * Набор плейсхолдеров
			 */
			$aCatRepl = $modx->sbshop->arrayToPlaceholders($oCategory->getAttributes());
			/**
			 * Добавляем строку
			 */
			$sCategories .= str_replace(array_keys($aCatRepl), array_values($aCatRepl), $aTemplates['yml_category']);
		}
		/**
		 * Добавляем в список основных плейсхолдеров информацию о категориях
		 */
		$aRepl['[+sb.categories+]'] = $sCategories;
		/**
		 * Загружаем товары
		 */
		$oProducts = new SBProductList();
		$oProducts->loadAllList();
		$aProducts = $oProducts->getProductList();
		/**
		 * Переменная с готовыми товарами
		 */
		$sProducts = '';
		/**
		 * Обрабатываем каждый товар
		 */
		foreach ($aProducts as $oProduct) {
			/**
			 * Проверяем активность раздела в котором находится товар
			 */
			if($oCategories->isActive($oProduct->getAttribute('category'))) {
				/**
				 * Плейсхолдеры товара
				 */
				$aProdRepl = $modx->sbshop->arrayToPlaceholders($oProduct->getAttributes());
				/**
				 * Наличие товара
				 */
				if($oProduct->getAttribute('existence')) {
					$aProdRepl['[+sb.existence+]'] = 'true';
				} else {
					$aProdRepl['[+sb.existence+]'] = 'false';
				}
				/**
				 * Получаем список видимых характеристик товара
				 */
				$aParams = $oProduct->getExtendVisibleAttributes();
				/**
				 * Список параметров
				 */
				$sParams = '';
				/**
				 * Обрабатываем каждую характеристику
				 * XXX необходимо переделать задачу с мерами
				 */
				foreach ($aParams as $aVal) {
					/**
					 * Плейсхолдер для товара
					 */
					$aProdRepl['[+sb.param.' . mb_strtolower($aVal['title'],'UTF-8') . '+]'] = $aVal['value'];
					/**
					 * Плейсхолдеры параметра
					 */
					$aParamRepl = $modx->sbshop->arrayToPlaceholders($aVal);
					/**
					 * Добавляем
					 */
					$sParams .= str_replace(array_keys($aParamRepl), array_values($aParamRepl), $aTemplates['yml_param']);
				}
				/**
				 * Добавляем параметры в плейсхолдеры товара
				 */
				$aProdRepl['[+sb.params+]'] = $sParams;
				/**
				 * Список скрытых параметров
				 */
				$aParams = $oProduct->getExtendHiddenAttributes();
				/**
				 * Обрабатываем каждый параметр
				 */
				foreach ($aParams as $aVal) {
					/**
					 * Плейсхолдер параметра для товара
					 */
					$aProdRepl['[+sb.param.' . mb_strtolower($aVal['title'],'UTF-8') . '+]'] = $aVal['value'];
				}
				/**
				 * Добавляем изображения
				 */
				$aProdRepl = array_merge($aProdRepl,$modx->sbshop->multiarrayToPlaceholders($oProduct->getAllImages(),'num','sb.image.'));
				/**
				 * Добавляем товар
				 */
				$sProducts .= str_replace(array_keys($aProdRepl), array_values($aProdRepl), $aTemplates['yml_offer']);
			}
		}
		/**
		 * Добавляем плейсхолдер с товарами
		 */
		$aRepl['[+sb.offers+]'] = $sProducts;
		/**
		 * Дата генерации YML
		 */
		$aRepl['[+sb.date+]'] = date('Y-m-d H:i');
		/**
		 * Подготавливаем к выводу
		 */
		$sOutput = str_replace(array_keys($aRepl), array_values($aRepl), $aTemplates['yml_outer']);
		/**
		 * Выводим
		 */
		echo $sOutput;
	}

}

?>
