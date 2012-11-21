<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 *
 * Экшен модуля: Очистка корзины
 */

class clearbin_mode {

	/**
	 * @todo
	 * Удаление всех подразделов у удаленного раздела
	 * Удаление всех товаров у удаленных разделов
	 * Обновление количества товаров у разделов
	 */

	/**
	 * Конструктор
	 * @param unknown_type $sModuleLink
	 * @param unknown_type $sMode
	 * @param unknown_type $sAct
	 */
	public function __construct($sModuleLink, $sMode, $sAct = '') {
		global $modx;
		/**
		 * Получаем удаленные разделы
		 */
		$rs = $modx->db->select('category_id, category_path', $modx->getFullTableName('sbshop_categories'), 'category_deleted = 1');
		/**
		 * Массив идентификаторов удаляемых разделов
		 */
		$aCatIds = array();
		/**
		 * Массив путей удаляемых разделов
		 */
		$aCatPaths = array();
		/**
		 * обрабатываем каждую запись
		 */
		while($aRow = $modx->db->getRow($rs)) {
			/**
			 * Добавляем идентификатор раздела
			 */
			$aCatIds[] = $aRow['category_id'];
			/**
			 * Правило для запроса
			 */
			$aCatPaths[] = 'category_path LIKE "' . $aRow['category_path'] . '.' . $aRow['category_id'] . '.%"';
		}
		/**
		 * Если есть удаляемые разделы
		 */
		if(count($aCatIds) > 0) {
			/**
			 * Получаем дочерние разделы
			 */
			$rs = $modx->db->select('category_id', $modx->getFullTableName('sbshop_categories'), implode(' OR ', $aCatPaths));
			/**
			 * Полный список удаляемых разделов
			 */
			$aCatIds = array_merge($aCatIds, $modx->db->getColumn('category_id', $rs));
			/**
			 * Удаляем разделы
			 */
			$modx->db->delete($modx->getFullTableName('sbshop_categories'), 'category_id in (' . implode(',', $aCatIds) . ')');
			/**
			 * Удаляем товары
			 */
			$modx->db->delete($modx->getFullTableName('sbshop_products'), 'product_deleted = 1 OR product_category in (' . implode(',', $aCatIds) . ')');
		} else {
			/**
			 * Удаляем товары
			 */
			$modx->db->delete($modx->getFullTableName('sbshop_products'), 'product_deleted = 1');
		}
		/**
		 * Возвращаемся на главную
		 */
		$modx->sbshop->alertWait($sModuleLink);
	}
}