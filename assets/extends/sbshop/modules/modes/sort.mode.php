<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 *
 * Экшен модуля: Режим сортировки разделов / товаров
 */

class sort_mode {

	protected $sModuleLink;
	protected $sMode;
	protected $sAct;
	protected $aTemplates;

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
		$this->aTemplates = $modx->sbshop->getModuleTemplate($this->sMode);
		/**
		 * Выбираем действие
		 */
		switch ($_GET['act']) {
			case 'cat':
				/**
				 * Если данные не были получены
				 */
				if(!isset($_POST['sb_sort'])) {
					$this->outputCategorySort();
				} else {
					/**
					 * Сохраняем
					 */
					if($this->saveCategorySort()) {
						$modx->sbshop->alertWait($this->sModuleLink);
					}
				}
			break;
			case 'prod':
				/**
				 * Если данные не были получены
				 */
				if(!isset($_POST['sb_sort'])) {
					$this->outputProductSort();
				} else {
					/**
					 * Сохраняем
					 */
					if($this->saveProductSort()) {
						$modx->sbshop->alertWait($this->sModuleLink);
					}
				}
			break;
		}
		
	}

	/**
	 * Выводим данные для сортировки разделов
	 */
	protected function outputCategorySort() {
		global $modx, $_lang;
		/**
		 * Объединяем системный и модульный языковой массив
		 */
		$aLang = array_merge($_lang, $modx->sbshop->lang);
		/**
		 * Подготавливаем языковые плейсхолдеры
		 */
		$phModule = $modx->sbshop->arrayToPlaceholders($aLang,'lang.');
		/**
		 * Служебные плейсхолдеры для модуля
		 */
		$phModule['[+site.url+]'] = MODX_BASE_URL;
		$phModule['[+module.link+]'] = $this->sModuleLink;
		$phModule['[+module.act+]'] = $this->sAct;
		/**
		 * Получаем информацию о выбранном разделе
		 */
		$oCategory = new SBCategory();
		$oCategory->load(intval($_GET['catid']),true);
		/**
		 * Получаем родителя раздела
		 */
		$iParent = $oCategory->getAttribute('parent');
		/**
		 * Загружаем информацию о родительском разделе
		 */
		$oParentCategory = new SBCategory();
		$oParentCategory->load($iParent);
		/**
		 * Загружаем список разделов
		 */
		$oCatTree = new SBCatTree($oParentCategory, 2, true);
		/**
		 * Получаем список разделов
		 */
		$aCategories = $oCatTree->getChildrenById($iParent);
		/**
		 * Переменная для списка сортировки
		 */
		$sRows = '';
		/**
		 * Обрабатываем каждый раздел
		 */
		$cnt = count($aCategories);
		for($i=0; $i<$cnt; $i++) {
			/**
			 * Получаем информацию о разделе
			 */
			$aCategory = $oCatTree->getAttributesById($aCategories[$i]);
			/**
			 * Формируем плейсхолдеры
			 */
			$aRepl = $modx->sbshop->arrayToPlaceholders($aCategory);
			/**
			 * Добавляем новый ряд
			 */
			$sRows .= str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['sort_row']);
		}
		/**
		 * Данные для контейнера
		 */
		$phModule['[+sb.wrapper+]'] = $sRows;
		/**
		 * Делаем вставку в контейнер
		 */
		$sOutput = str_replace(array_keys($phModule), array_values($phModule), $this->aTemplates['sort_outer']);

		echo $sOutput;
	}

	/**
	 * Сохраняем результат сортировки разделов
	 */
	protected function saveCategorySort() {
		/**
		 * Список с новым порядком сортировки
		 */
		$aOrder = $_POST['sort'];
		/**
		 * Обрабатываем каждую запись
		 */
		$cnt = count($aOrder);
		for($i=0; $i<$cnt; $i++) {
			/**
			 * Загружаем раздел по идентификатору
			 */
			$oCategory = new SBCategory();
			$oCategory->load(intval($aOrder[$i]), true);
			/**
			 * Устанавливаем новый порядок
			 */
			$oCategory->setAttribute('order', $i);
			/**
			 * Сохраняем
			 */
			$oCategory->save();
		}
		return true;
	}

	/**
	 * Выводим данные для сортировки товаров
	 */
	protected function outputProductSort() {
		global $modx, $_lang;
		/**
		 * Объединяем системный и модульный языковой массив
		 */
		$aLang = array_merge($_lang, $modx->sbshop->lang);
		/**
		 * Подготавливаем языковые плейсхолдеры
		 */
		$phModule = $modx->sbshop->arrayToPlaceholders($aLang,'lang.');
		/**
		 * Служебные плейсхолдеры для модуля
		 */
		$phModule['[+site.url+]'] = MODX_BASE_URL;
		$phModule['[+module.link+]'] = $this->sModuleLink;
		$phModule['[+module.act+]'] = $this->sAct;
		/**
		 * Получаем информацию о выбранном товаре
		 */
		$oProduct = new SBProduct();
		$oProduct->load(intval($_GET['prodid']),true);
		/**
		 * Получаем родителя раздела
		 */
		$iParent = $oProduct->getAttribute('category');
		/**
		 * Получаем родителя товара
		 */
		$oProducts = new SBProductList();
		/**
		 * Загружаем список товаров
		 */
		$oProducts->loadFullListByCategoryId($iParent);
		/**
		 * Массив товаров
		 */
		$aProducts = $oProducts->getProductList();
		/**
		 * Переменная для списка сортировки
		 */
		$sRows = '';
		/**
		 * Обрабатываем каждый товар
		 */
		foreach ($aProducts as $oProduct) {
			/**
			 * Получаем параметры
			 */
			$aProduct = $oProduct->getAttributes();
			/**
			 * Делаем плейсхолдеры
			 */
			$aRepl = $modx->sbshop->arrayToPlaceholders($aProduct);
			/**
			 * Добавляем новый ряд
			 */
			$sRows .= str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['sort_row']);

		}
		/**
		 * Данные для контейнера
		 */
		$phModule['[+sb.wrapper+]'] = $sRows;
		/**
		 * Добавляем языковые данные
		 */
		/**
		 * Делаем вставку в контейнер
		 */
		$sOutput = str_replace(array_keys($phModule), array_values($phModule), $this->aTemplates['sort_outer']);

		echo $sOutput;
	}

	/**
	 * Сохраняем результат сортировки товаров
	 */
	protected function saveProductSort() {
		/**
		 * Список с новым порядком сортировки
		 */
		$aOrder = $_POST['sort'];
		/**
		 * Обрабатываем каждую запись
		 */
		$cnt = count($aOrder);
		for($i=0; $i<$cnt; $i++) {
			/**
			 * Загружаем товар по идентификатору
			 */
			$oProduct = new SBProduct();
			$oProduct->load(intval($aOrder[$i]), true);
			/**
			 * Устанавливаем новый порядок
			 */
			$oProduct->setAttribute('order', $i);
			/**
			 * Сохраняем
			 */
			$oProduct->save();
		}
		return true;
	}

}
?>
