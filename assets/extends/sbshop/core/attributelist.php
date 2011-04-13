<?php

/**
 * @author Mukharev Maxim
 * @version 0.1a
 * 
 * @desription
 * 
 * Электронный магазин для MODx
 * 
 * Класс для управления списком параметров
 */

class SBAttributeList {
	
	protected $aAttributes;
	
	/**
	 * Конструктор
	 */
	public function __construct() {
		/**
		 * Инициализируем
		 */
		$this->aAttributes = array();
		
	}
	
	/**
	 * Установка параметров
	 * @param unknown_type $aParam
	 */
	public function setAttributes($aParams = false) {
		if(is_array($aParams)) {
			foreach ($aParams as $sKey => $aVal) {
				if($sKey != '') {
					$this->aAttributes[$sKey] = $aVal;
				}
			}
		}
	}
	
	/**
	 * Установка параметра
	 * @param unknown_type $sKey
	 * @param unknown_type $aVal
	 */
	public function setAttribute($sKey,$aVal) {
		if($sKey != '') {
			$this->aAttributes[$sKey] = $aVal;
		}
	}
	
	/**
	 * Получение общего списка параметров
	 * @param unknown_type $aParams
	 */
	public function getAttributes($aParams = false, $sType = false) {
		/**
		 * Массив результата
		 */
		$aResult = array();
		/**
		 * Если параметры не заданы
		 */
		if($aParams == false) {
			/**
			 * Если ключ не задан
			 */
			if($sType == false) {
				/**
				 * Возвращаем все параметры
				 */
				return $this->aAttributes;
			} else {
				/**
				 * Обрабатываем каждую запись
				 */
				foreach ($this->aAttributes as $sKey => $aVal) {

					switch($sType) {
						case 'p':
						case 'primary':
							/**
							 * Если параметр имеет ключ 'p'
							 */
							if($aVal['type'] == 'p') {
								$aResult[$sKey] = $aVal;
							}
							break;
						case 'h':
						case 'hidden':
							/**
							 * Если параметр имеет ключ 'h'
							 */
							if($aVal['type'] == 'h') {
								$aResult[$sKey] = $aVal;
							}
							break;
						case 'n':
						case 'normal':
							/**
							 * Если параметр имеет ключ 'n'
							 */
							if($aVal['type'] == 'n') {
								$aResult[$sKey] = $aVal;
							}
							break;
						case 'v':
						case 'visible':
							/**
							 * Если параметр входит в видимый список и имеет ключ 'p' или 'n'
							 */
							if($aVal['type'] == 'p' or $aVal['type'] == 'n') {
								$aResult[$sKey] = $aVal;
							}
							break;
					}
				}
			}
		} else {
			/**
			 * Если передана строка, то делаем массив
			 */
			if(!is_array($aParams)) {
				$aParams = array($aParams);
			}
			/**
			 * Выбираем заданные параметры из массива
			 */
			foreach ($aParams as $sParam) {
				if(isset($this->aAttributes[$sParam])) {
					$aResult[$sParam] = $this->aAttributes[$sParam];
				}
			}
		}
		return $aResult;
	}
	
	/**
	 * Получение видимых параметров
	 * @param unknown_type $aParams
	 */
	public function getVisibleAttributes() {
		return $this->getAttributes(false, 'visible');
	}
	
	/**
	 * Получение выделенных параметров
	 * @param unknown_type $aParams
	 */
	public function getPrimaryAttributes() {
		return $this->getAttributes(false, 'primary');
	}

	/**
	 * Получение списка скрытых параметров
	 * @param unknown_type $aParams
	 */
	public function getHiddenAttributes() {
		return $this->getAttributes(false, 'hidden');
	}

	/**
	 * Получение заданного параметра
	 * @param $sParamName
	 * @return unknown_type
	 */
	public function getAttribute($sParamName) {
		return array_pop($this->getAttributes($sParamName));
	}
	
	/**
	 * Получение ключей параметров
	 */
	public function getAttributeKeys() {
		return array_keys($this->aAttributes);
	}

	/**
	 * Сериализация параметров в текстовую строку
	 */
	public function serializeAttributes() {
		/**
		 * Возвращаем результат
		 */
		return base64_encode(serialize($this->aAttributes));
	}
	
	/**
	 * десериализация с параметров с заполнением массива параметров
	 * @param unknown_type $sParams
	 */
	public function unserializeAttributes($sParams) {
		/**
		 * Если ничего не передали, выходим
		 */
		if(!$sParams) {
			return;
		}
		/**
		 * Разбиваем строку на отдельные ключи/значения
		 * @var unknown_type
		 */
		$aParams = unserialize(base64_decode($sParams));
		/**
		 * Устанавливаем
		 */
		$this->aAttributes = $aParams;
		/**
		 * Возвращаем результат
		 */
		return $aParams;
	}

}

?>