<?php

/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 * 
 * Экшен сниппета: Вывод хлебных крошек
 * 
 */

class breadcrumbs_mode {
	
	protected $oBreadcrumbs; // Хлебные крошки
	protected $aTemplates; // Массив с набором шаблонов
	
	/**
	 * Конструктор
	 */
	public function __construct() {
		global $modx;
		/**
		 * Задаем набор шаблонов
		 */
		$this->aTemplates = $modx->sbshop->getSnippetTemplate('breadcrumbs');
		/**
		 * Получаем "хлебные крошки"
		 */
		$this->oBreadcrumbs = new SBBreadcrumbs();
		/**
		 * Вывод "хлебных крошек" в плейсхолдер [+sb.innercat+]
		 */
		$modx->setPlaceholder('sb.breadcrumbs',$this->outputBreadcrumbs());
	}
	
	/**
	 * Вывод "хлебных крошек" для навигации
	 */
	public function outputBreadcrumbs() {
		global $modx;
		/**
		 * Записываем в содержимое основной контейнер
		 */
		$sOutput = $this->aTemplates['breadcrumbs_outer'];
		/**
		 * Инициализируем временный массив для рядов
		 */
		$aRows = array();
		/**
		 * Получаем список пунктов
		 */
		$aBreadcrumbs = $this->oBreadcrumbs->getBreadcrumbs();
		/**
		 * Обрабатываем каждый пункт
		 */
		$iCnt = count($aBreadcrumbs);
		for($i=0;$i<$iCnt;$i++) {
			/**
			 * Подготавливаем информацию для вставки в шаблон
			 */
			$aRepl = $modx->sbshop->arrayToPlaceholders($aBreadcrumbs[$i]);
			/**
			 * Если последний элемент
			 */
			if($i == ($iCnt - 1)) {
				$aRows[] = str_replace(array_keys($aRepl),array_values($aRepl),$this->aTemplates['breadcrumbs_lastrow']);
			} else {
				$aRows[] = str_replace(array_keys($aRepl),array_values($aRepl),$this->aTemplates['breadcrumbs_row']);
			}
		}
		/**
		 * Добавляем разделитель
		 */
		$sRepl = implode($this->aTemplates['breadcrumbs_separator'],$aRows);
		/**
		 * Делаем вставку в контейнер
		 */
		$sOutput = str_replace('[+sb.wrapper+]',$sRepl,$sOutput);
		/**
		 * Отдаем результат
		 */
		return $sOutput;
	}
	
}

?>