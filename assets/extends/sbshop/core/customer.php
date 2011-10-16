<?php
/**
 * @author Mukharev Maxim
 * @version 0.1a
 *
 * @desription
 *
 * Электронный магазин для MODx
 *
 * Класс для управления данными клиента
 */

class SBCustomer {

	protected $aCustomerData; // Массив данных покупателя
	protected $aCustomerKeys; // Массив ключей

	public function  __construct($iCustomerId = false,$aParams = false) {
		/**
		 * Стандартный массив данных покупателя
		 */
		$this->aCustomerData = array(
			'id' => null,
			'internalKey' => null,
			'fullname' => null,
			'email' => null,
			'phone' => null,
			'city' => null,
			'address' => null,
		);
		/**
		 * Устанавливаем основные ключи
		 */
		$this->aCustomerKeys = array_keys($this->aCustomerData);
		/**
		 * Если задан идентификатор клиента
		 */
		if($iCustomerId) {
			/**
			 * Делаем загрузку
			 */
			$this->loadById($iCustomerId);
		}
		/**
		 * Если переданы параметры
		 */
		if($aParams) {
			/**
			 * Устанавливаем переданные данные
			 */
			$this->setAttributes($aParams);
		}
	}

	/**
	 * Установка параметра клиента
	 * @param $sParamName
	 * @param $sParamValue
	 * @return unknown_type
	 */
	public function setAttribute($sParamName, $sParamValue) {
		return $this->setAttributes(array($sParamName => $sParamValue));
	}

	/**
	 * Установка набора параметров клиента
	 * @param $aParam Массив параметров для установки
	 * @return unknown_type
	 */
	public function setAttributes($aParams = false) {
		if(is_array($aParams)) {
			foreach ($aParams as $sKey => $sVal) {
				/**
				 * Удаляем префикс category_ у ключа
				 */
				$sKey = str_replace('customer_','',$sKey);
				/**
				 * Отсекаем лишние параметры
				 */
				if(in_array($sKey,$this->aCustomerKeys)) {
					/**
					 * Устанавливаем значение
					 */
					$this->aCustomerData[$sKey] = $sVal;
				}
			}
		}
	}

	/**
	 * Получение заданного параметра клиента
	 * @param $sParamName
	 * @return unknown_type
	 */
	public function getAttribute($sParamName) {
		return array_pop($this->getAttributes($sParamName));
	}

	/**
	 * Получение параметров клиента
	 * @param $aParams
	 * @return unknown_type
	 */
	public function getAttributes($aParams = false) {
		/**
		 * Если параметры не заданы, возвращаем весь массив
		 */
		if($aParams == false) {
			return $this->aCustomerData;
		}
		/**
		 * Если передана строка, то делаем массив
		 */
		if(!is_array($aParams)) {
			$aParams = array($aParams);
		}
		/**
		 * Выбираем заданные параметры из массива
		 */
		$aResult = array();
		foreach ($aParams as $sParam) {
			if(isset($this->aCustomerData[$sParam])) {
				$aResult[$sParam] = $this->aCustomerData[$sParam];
			}
		}
		return $aResult;
	}

	/**
	 * Загрузка данных клиента по указанному идентификатору
	 * @global <type> $modx
	 * @param <type> $iCustomerId
	 */
	public function loadById($iCustomerId = false) {
		global $modx;
		/**
		 * Делаем проверку на передачу численного значения
		 */
		if(!$iCustomerId || $iCustomerId == 0) {
			return false;
		}
		/**
		 * Делаем запрос
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_customers'),'customer_id = ' . $iCustomerId);
		/**
		 * Переводим данные в массив
		 */
		$aRaw = $modx->db->makeArray($rs);
		/**
		 * Устанавливаем данные
		 */
		$this->setAttributes($aRaw[0]);
	}

	/**
	 * Сохранение данных клиента
	 */
	public function save() {
		global $modx;
		/**
		 * Получаем список ключей для основных полей
		 */
		$aKeys = $this->aCustomerKeys;
		$aData = array();
		/**
		 * Обрабатываем каждый ключ
		 */
		foreach ($aKeys as $sKey) {
			if($this->aCustomerData[$sKey] !== null) {
				$aData['customer_' . $sKey] = $this->aCustomerData[$sKey];
			}
		}
		/**
		 * Получаем идентификатор
		 */
		$iCustomerId = $this->getAttribute('id');
		/**
		 * Если идентификатор установлен
		 */
		if($iCustomerId) {
			/**
			 * Обновляем данные в дополнительной таблице SBShop
			 */
			$modx->db->update($aData,$modx->getFullTableName('sbshop_customers'),'customer_id = ' . $iCustomerId);
		} else {
			/**
			 * Делаем вставку основных данных в БД
			 */
			$modx->db->insert($aData,$modx->getFullTableName('sbshop_customers'));
			/**
			 * Получаем установленный идентификатор
			 */
			$iCustomerId = $modx->db->getInsertId();
			/**
			 * Устанавливаем идентификатор
			 */
			$this->setAttributes(array('id' => $iCustomerId));
		}
	}
}


?>
