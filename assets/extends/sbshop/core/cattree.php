<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 *
 * Класс для дерева разделов
 */

class SBCatTree {
	/**
	 * @var SBCategory
	 */
	protected $oParentCategory; // Родительский раздел
	protected $iLevel; // Уровень вложенности дерева
	protected $aCatTree; // Массив дерева категории
	protected $aCatTreeChildren; // Массив дерева разделов
	protected $aCatTreeLevels; // Массив дерева разделов

	public function  __construct($oParentCategory = false,$iLevel = false, $bDeleted = false) {
		global $modx;
		/**
		 * Инициализируем основной массив
		 */
		$this->aCatTree = array();
		/**
		 * Устанавливаем уровень
		 */
		if(!$iLevel) {
			$iLevel = $modx->sbshop->config['cattree_level'];
		}
		/**
		 * если родительская категория не передана, то создаем экземпляр новой
		 */
		if(!$oParentCategory) {
			$oParentCategory = new SBCategory();
		}
		/**
		 * Получаем путь
		 */
		$sPath = $oParentCategory->getAttribute('path');
		/**
		 * Если путь не установлен
		 */
		if(!$sPath) {
			/**
			 * То устанавливаем корневую категорию
			 */
			$sPath = '0';
		}
		/**
		 * Записываем идентификатор родительской категории
		 */
		$this->oParentCategory = $oParentCategory;
		/**
		 * Делаем загрузку дерева категорий
		 */
		$this->load($sPath,$iLevel,$bDeleted);
	}

	/**
	 * Загрузка дерева категорий
	 */
	public function load($sPath, $iLevel, $bDeleted = false) {
		global $modx;
		/**
		 * Текущий уровень родительской категории
		 */
		$iStartLevel = $this->oParentCategory->getAttribute('level');
		/**
		 * Вычисляем конечный уровень
		 */
		$iEndLevel = $iLevel + $iStartLevel;
		/**
		 * Добавляем к пути необходимую маску для поиска дочерних элементов
		 */
		$sPath .= '.%';
		if(!$bDeleted) {
			$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_categories'),' category_deleted = 0 AND category_published = 1 AND category_path like "' . $sPath . '" AND category_level < ' . $iEndLevel,'category_order');
		} else {
			$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_categories'),' category_path like "' . $sPath . '" AND category_level < ' . $iEndLevel,'category_order');
		}
		$aRaws = $modx->db->makeArray($rs);
		/**
		 * Обрабатываем все записи
		 */
		foreach ($aRaws as $aRaw) {
			/**
			 * Создаем новый объект категории с идентификатором в качестве ключа
			 */
			$this->aCatTree[$aRaw['category_id']] = new SBCategory($aRaw);
			/**
			 * Отдельно выделяем информацию о дочерних категориях 
			 */
			$this->aCatTreeChildren[$aRaw['category_parent']][] = $aRaw['category_id'];
			/**
			 * Устанавливаем уровни вложенности
			 */
			$this->aCatTreeLevels[$aRaw['category_level']][] = $aRaw['category_id'];
		}
	}
	
	/**
	 * Получение информации об уровнях вложенности
	 */
	public function getCatTreeLevels() {
		return $this->aCatTreeLevels;
	}

	public function getChildrenById($iCategoryId) {
		return $this->aCatTreeChildren[$iCategoryId];
	}

	/**
	 * Получение массива всех активных разделов
	 */
	public function getCategories() {
		/**
		 * Массив с результатом
		 */
		$aResult = array();
		/**
		 * Разбираем каждый раздел
		 */
		foreach ($this->aCatTree as $sKey => $oCategory) {
			/**
			 * Если раздел опубликован и не удален
			 */
			if($oCategory->getAttribute('published') and !$oCategory->getAttribute('deleted')) {
				/**
				 * Получаем список родителей
				 */
				$bActive = $this->isActive($oCategory->getAttribute('id'));
				if($bActive) {
					$aResult[$sKey] = $oCategory;
				}
			}
		}
		return $aResult;
	}

	/**
	 * Получение массива всех разделов не зависимо от статуса
	 */
	public function getAllCategories() {
		return $this->aCatTree;
	}

	/**
	 * Получение раздела по идентификатору
	 */
	public function getCategoryById($iCategoryId) {
		return $this->aCatTree[$iCategoryId];
	}

	/**
	 * Получение списка параметров категории по идентификатору
	 * @param unknown_type $iId
	 */
	public function getAttributesById($iCategoryId) {
		if(isset($this->aCatTree[$iCategoryId])) {
			return $this->aCatTree[$iCategoryId]->getAttributes();
		}
	}

	/**
	 * Определение является ли заданный раздел активным (не удален и опубликован) с учетом родителей
	 */
	public function isActive($iCategoryId) {
		/**
		 * Если не такого раздела
		 */
		if(!isset($this->aCatTree[$iCategoryId])) {
			/**
			 * Выходим с false
			 */
			return false;
		}
		/**
		 * Получаем путь заданного раздела
		 */
		$sPath = $this->aCatTree[$iCategoryId]->getAttribute('path');
		/**
		 * Разбиваем путь на массив идентификаторов
		 */
		$aPath = explode('.', $sPath);
		/**
		 * Обрабатываем каждый идентификатор
		 */
		foreach ($aPath as $iId) {
			$iId = intval($iId);
			/**
			 * Если идентификатор отсутствует в списке
			 */
			if($iId != 0 and !isset($this->aCatTree[$iId])) {
				/**
				 * Значит раздел скрыт
				 */
				return false;
			} elseif ($iId != 0) {
				/**
				 * Получаем раздел
				 */
				$oCategory = $this->aCatTree[$iId];
				/**
				 * Проверяем на опубликованность и удаление
				 */
				if(!$oCategory->getAttribute('published') or $oCategory->getAttribute('deleted')) {
					return false;
				}
			}
		}
		/**
		 * Если проверка прошла успешно возвращаем true
		 */
		return true;
	}
}

?>
