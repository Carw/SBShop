<?php
/**
 * @author Mukharev Maxim
 * @version 0.1a
 *
 * @desription
 *
 * Электронный магазин для MODx
 *
 * Класс для работы с подсказками
 */

class SBTip {

	protected $aTipData; // параметры подсказки
	protected $aTipDataKeys; // ключи

	public function  __construct() {
		/**
		 * Стандартный массив параметров товара
		 */
		$this->aTipData = array(
			'id' => null,
			'title' => null,
			'description' => null,
		);
		/**
		 * Создаем список ключей основных параметров
		 */
		$this->aTipDataKeys = array_keys($this->aTipData);
	}

	/**
	 * Установка набора параметров подсказки
	 * @param $aParam Массив параметров для установки
	 * @return unknown_type
	 */
	public function setAttributes($aParam = false) {
		if(is_array($aParam)) {
			foreach ($aParam as $sKey => $sVal) {
				/**
				 * Удаляем префикс product_ у ключа
				 */
				$sKey = str_replace('tip_','',$sKey);
				/**
				 * Попадает ли параметр в основной список ключей
				 */
				if(in_array($sKey,$this->aTipDataKeys) && ($sVal !== null) && ($sVal !== '')) {
					/**
					 * Заносим основной параметр
					 */
					$this->aTipData[$sKey] = $sVal;
				}
			}
		}
	}

	/**
	 * Установка параметра подсказки
	 * @param $sParamName
	 * @param $sParamValue
	 * @return unknown_type
	 */
	public function setAttribute($sParamName, $sParamValue) {
		return $this->setAttributes(array($sParamName => $sParamValue));
	}

	/**
	 * Получение параметров подсказки
	 * @param $aParams
	 * @return unknown_type
	 */
	public function getAttributes($aParams = false) {
		/**
		 * Если параметры не заданы, возвращаем весь массив параметров
		 */
		if($aParams == false) {
			return $this->aTipData;
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
			if(isset($this->aTipData[$sParam])) {
				$aResult[$sParam] = $this->aTipData[$sParam];
			}
		}
		return $aResult;
	}

	/**
	 * Получение параметра подсказки
	 * @param $sParamName
	 * @return unknown_type
	 */
	public function getAttribute($sParamName) {
		return array_pop($this->getAttributes($sParamName));
	}

	/**
	 * Загрузка подсказки по идентификатору
	 * @global <type> $modx
	 * @param <type> $iTipId
	 * @return <type>
	 */
	public function load($iTipId = false) {
		global $modx;
		/**
		 * Делаем проверку на передачу численного значения
		 */
		if(!$iTipId) {
			return false;
		}
		/**
		 * Получаем информацию о товаре по ID
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_product_tips'),'tip_id=' . intval($iTipId));
		$aData = $modx->db->makeArray($rs);
		/**
		 * Подготавливаем основные параметры и заносим в массив
		 */
		if(count($aData[0]) > 0) {
			foreach ($aData[0] as $sKey => $sVal) {
				$sKey = str_replace('tip_','',$sKey);
				$this->aTipData[$sKey] = $sVal;
			}
		}
		unset($aData);
	}

	/**
	 * Сохранение информации о подсказке
	 * @return unknown_type
	 */
	public function save() {
		global $modx;
		$iTipId = $this->getAttribute('id');
		/**
		 * Подготавливаем основные параметры подсказки для сохранения
		 * Добавляем префикс
		 */
		$aKeys = $this->aTipDataKeys;
		$aData = array();
		foreach ($aKeys as $sKey) {
			if($this->aTipData[$sKey] !== null) {
				$aData['tip_' . $sKey] = $this->aTipData[$sKey];
			}
		}
		/**
		 * Если ID есть, то делаем обновление информации
		 */
		if($iTipId) {
			$modx->db->update($aData,$modx->getFullTableName('sbshop_product_tips'),'tip_id=' . $iTipId);
		} else {
			/**
			 * Чтобы не возникало всяких фокусов, полностью исключаем идентификатор
			 */
			unset($aData['tip_id']);
			/**
			 * Если значения есть
			 */
			if(count($aData) > 0) {
				/**
				 * Добавляем новый товар
				 */
				$modx->db->insert($aData,$modx->getFullTableName('sbshop_product_tips'));
				$this->setAttribute('id',$modx->db->getInsertId());
			}
		}
	}

}

?>
