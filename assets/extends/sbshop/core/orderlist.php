<?php

/**
 * @author Mukharev Maxim
 * @version 0.1a
 *
 * @desription
 *
 * Электронный магазин для MODx
 *
 * Объект список товаров
 */

class SBOrderList {

	protected $aOrderList;

	/**
	 * Конструктор
	 */
	public function  __construct($aStatusIds = 10, $sWhere = '') {
		/**
		 * Инициализируем массив заказов
		 */
		$this->aOrderList = array();
		/**
		 * Если передано одно значение статуса
		 */
		if(!is_array($aStatusIds)) {
			$aStatusIds = array($aStatusIds);
		}
		/**
		 * Загружаем список по заданному статусу
		 */
		$this->loadOrdersByStatusIds($aStatusIds,$sWhere);
	}

	/**
	 * Загрузка списка заказов по указанному статусу
	 * @param <type> $iStatus
	 */
	public function loadOrdersByStatusId($iStatus = 10, $sWhere = '', $sSort='date_edit') {
		return $this->loadOrdersByStatusIds(array($iStatus), $sWhere = '', $sSort);
	}

	/**
	 * Загрузка списка заказов по указанному статусу
	 * @param <type> $iStatus
	 */
	public function loadOrdersByStatusIds($iStatusIds = array(), $sWhere = '', $sSort='date_edit') {
		global $modx;
		/**
		 * Если нет идентификаторов
		 */
		if(count($iStatusIds) == 0) {
			return false;
		}
		/**
		 * Количество заказов на страницу
		 */
		$OrderPerPage = $modx->sbshop->config['order_per_page'];
		/**
		 * Если указаны дополнительные условия Where
		 */
		if($sWhere) {
			$sWhere = ' AND ' . $sWhere;
		}
		/**
		 * Делаем запрос
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_orders'),'order_status in (' . implode(',',$iStatusIds) . ')' . $sWhere,'order_' . $sSort);
		$aRaws = $modx->db->makeArray($rs);
		/**
		 * Устанавливаем список заказов
		 */
		$this->setList($aRaws);
	}

	/**
	 * Установка данных списка
	 * @param <type> $aOrders
	 */
	public function setList($aOrders) {
		/**
		 * Если найдены товары
		 */
		if(count($aOrders) > 0) {
			/**
			 * Обрабатываем каждую запись
			 */
			foreach($aOrders as $aOrder) {
				/**
				 * Добавляем в основной массив экземпляр товара
				 */
				$this->aOrderList[$aOrder['order_id']] = new SBOrder($aOrder);
			}
		}
	}

	/**
	 * Получение списка заказов
	 */
	public function getOrderList() {
		return $this->aOrderList;
	}

}

?>
