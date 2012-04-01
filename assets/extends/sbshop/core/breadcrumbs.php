<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 *
 * Класс для реализации "хлебных крошек"
 */

class SBBreadcrumbs {
	
	/**
	 * @var SBCategory
	 */
	protected $oCategory;
	/**
	 * @var SBProduct
	 */
	protected $oProduct;
	protected $aBreadcrumbs;
	
	public function __construct() {
		global $modx;
		/**
		 * Если если мы не внутри каталога, то выходим
		 */
		if(!$modx->sbshop->insideCategory()) {
			return;
		}
		/**
		 * Получаем стартовый адрес
		 */
		$sStartUrl = $modx->sbshop->sBaseUrl;
		/**
		 * Устанавливаем суфикс
		 * @todo Разобраться в его использовании
		 */
		$sSuffix = $modx->sbshop->config['url_suffix'];
		/**
		 * Название ссылки на домашнюю страницу
		 */
		$sHomeTitle = $modx->sbshop->config['breadcrumbs_home_title'];
		/**
		 * Инициализируем основной массив
		 */
		$this->aBreadcrumbs = array();
		/**
		 * Устанавливаем первое звено "хлебных крошек"
		 */
		$this->aBreadcrumbs[] = array(
			'title' => $sHomeTitle,
			'longtitle' => $sHomeTitle,
			'url' => $sStartUrl
		);
		/**
		 * Получаем путь категории
		 */
		$sPath = $modx->sbshop->oGeneralCategory->getAttribute('path');
		/**
		 * Разбиваем путь на набор идентификаторов
		 */
		$aIds = explode('.',$sPath);
		/**
		 * Если в пути есть документы
		 */
		if(count($aIds) > 1) {
			/**
			 * Делаем запрос на информацию о категориях
			 */
			$rs = $modx->db->select('category_title, category_longtitle, category_url',$modx->getFullTableName('sbshop_categories'),'category_id in (' . implode(',',$aIds) . ')');
			/**
			 * Переводим результат в массив
			 */
			$aRaw = $modx->db->makeArray($rs);
			/**
			 * Обрабатываем каждую ссылку
			 */
			foreach ($aRaw as $aItem) {
				/**
				 * Если расширенный заголовок не установлен
				 */
				if($aItem['category_longtitle'] == '') {
					/**
					 * Устанавливаем основной
					 */
					$aItem['category_longtitle'] = $aItem['category_title'];
				}
				/**
				 * Записываем информацию о категории
				 */
				$this->aBreadcrumbs[] = array(
					'title' => $aItem['category_title'],
					'longtitle' => $aItem['category_longtitle'],
					'url' => $aItem['category_url']
				);
			}
			/**
			 * Если передан экземпляр товара, то добавляем информацию о нем
			 */
			if($modx->sbshop->oGeneralProduct->getAttribute('id') != null) {
				/**
				 * Получаем заголовки
				 */
				$sTitle = $modx->sbshop->oGeneralProduct->getAttribute('title');
				$sLongTitle = $modx->sbshop->oGeneralProduct->getAttribute('longtitle');
				/**
				 * Если расширенный заголовок не указан
				 */
				if($sLongTitle == '') {
					$sLongTitle = $sTitle;
				}
				/**
				 * Записываем информацию о товаре
				 */
				$this->aBreadcrumbs[] = array(
					'title' => $sTitle,
					'longtitle' => $sLongTitle,
					'url' => $modx->sbshop->oGeneralProduct->getAttribute('url')
				);
			}
		}
	}
	
	/**
	 * Отдает список всех звеньев "хлебных крошек"
	 */
	public function getBreadcrumbs() {
		return $this->aBreadcrumbs;
	}
	
}

?>