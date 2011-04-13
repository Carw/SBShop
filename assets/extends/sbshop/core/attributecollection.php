<?php

/**
 * @author Mukharev Maxim
 * @version 0.1a
 * 
 * @desription
 * 
 * Электронный магазин для MODx
 * 
 * Класс для управления общей коллекцией параметров
 */


class SBAttributeCollection {
	
	
	/**
	 * Сохранение параметров в общей коллекции 
	 * @param unknown_type $aAttribute
	 * @return Массив добавленных в коллекцию параметров
	 */
	public function setAttributeCollection($aAttributes) {
		global $modx;
		
		/**
		 * Если параметров нет, то просто выходим
		 */
		if(!$aAttributes) {
			return array();
		}
		/**
		 * Подготавливаем параметры к запросу в базу
		 */
		$aAttr = array();
		foreach ($aAttributes as $sAttribute) {
			if($sAttribute != '') {
				$aAttr[] = '\'' . $modx->db->escape($sAttribute) . '\''; 
			}
		}
		/**
		 * Объединяем параметры
		 */
		$sAttr = implode(',',$aAttr);
		/**
		 * Запрашиваем информацию из базы
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_attributes'),'attribute_name in (' . $sAttr . ')');
		$rsAttrs = $modx->db->makeArray($rs);
		/**
		 * Формируем массив имеющихся в базе параметров
		 */
		$aAttributesSetted = array();
		foreach ($rsAttrs as $rsAttr) {
			$aAttributesSetted[] = $rsAttr['attribute_name'];
		}
		/**
		 * Вычисляем параметры, которых нет в коллекции
		 */
		$aAddAttr = array_diff($aAttributes,$aAttributesSetted);
		/**
		 * если есть новые параметры
		 */
		if(count($aAddAttr) > 0) {
			/**
			 * Добавляем новые параметры в коллекцию
			 */ 
			$sql = 'INSERT INTO ' . $modx->getFullTableName('sbshop_attributes') . ' (`attribute_name`) VALUES ("' . implode('"),("',$aAddAttr) . '")';
			$modx->db->query($sql);
		}
	}
	
	/**
	 * Получение идентификаторов параметров по массиву имен
	 * @param unknown_type $aAttributes
	 */
	public function getAttributeIdsByNames($aAttributes = false) {
		global $modx;
		/**
		 * Если нет названий, то возвращаем пустой массив
		 */
		if($aAttributes == false) {
			return array();
		}
		/**
		 * Подготавливаем параметры к запросу
		 */
		$aAttr = array();
		foreach ($aAttributes as $sAttribute) {
			if($sAttribute != '') {
				$aAttr[] = '\'' . $modx->db->escape($sAttribute) . '\'';
			}
		}
		/**
		 * Объединяем параметры
		 */
		$sAttr = implode(',',$aAttr);
		/**
		 * Запрашиваем информацию из базы
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_attributes'),'attribute_name in (' . $sAttr . ')');
		$rsAttrs = $modx->db->makeArray($rs);
		/**
		 * Формируем массив идентификаторов
		 */
		$aIds = array();
		foreach ($rsAttrs as $rsAttr) {
			$aIds[$rsAttr['attribute_name']] = $rsAttr['attribute_id'];
		}
		
		return $aIds;
	}

	/**
	 * Получение идентификаторов параметров по массиву имен
	 * @param unknown_type $aAttributes
	 */
	public function getAttributeNamesByIds($aIds = false) {
		global $modx;
		/**
		 * Если нет названий, то возвращаем пустой массив
		 */
		if($aIds == false) {
			return array();
		}
		/**
		 * Подготавливаем параметры к запросу
		 */
		$aAttr = array();
		foreach ($aIds as $sId) {
			if($sId != '') {
				$aAttr[] = $modx->db->escape($sId);
			}
		}
		/**
		 * Объединяем параметры
		 */
		$sAttr = implode(',',$aAttr);
		/**
		 * Запрашиваем информацию из базы
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_attributes'),'attribute_id in (' . $sAttr . ')');
		$rsAttrs = $modx->db->makeArray($rs);
		/**
		 * Формируем массив идентификаторов
		 */
		$aIds = array();
		foreach ($rsAttrs as $rsAttr) {
			$aIds[$rsAttr['attribute_id']] = $rsAttr['attribute_name'];
		}

		return $aIds;
	}

	/**
	 * Установка обобщения параметров категории
	 * @param unknown_type $aNewAttributes
	 * @param unknown_type $aOldAttributes
	 */
	public function setAttributeCategoryGeneralization($iCatId,$aInsAttributes,$aRemAttributes) {
		global $modx;
		/**
		 * Если ничего обновлять не требуется
		 */
		if(count($aInsAttributes) == 0 and count($aRemAttributes) == 0) {
			/**
			 * Просто выходим
			 */
			return;
		}
		/**
		 * Совмещаем список параметров для последующего запроса в базу
		 */
		$aAttrIds = implode(',',array_merge($aInsAttributes, $aRemAttributes));
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_category_attributes'),'category_id = ' . $iCatId . ' and attribute_id in (' . $aAttrIds . ')');
		$cnt = $modx->db->getRecordCount($rs);
		for($i=0; $i<$cnt; $i++) {
			$aRow = $modx->db->getRow($rs);
			$aAttrExisted[$aRow['attribute_id']] = $aRow;
		}
		/**
		 * Обрабатываем все добавляемые параметры
		 */
		foreach($aInsAttributes as $iAttrId) {
			/**
			 * Если такой параметр уже есть в разделе
			 */
			if(isset($aAttrExisted[$iAttrId])) {
				/**
				 * Плюсуем счетчик
				 */
				$aUpd = array(
					'attribute_count' => $aAttrExisted[$iAttrId]['attribute_count'] + 1
				);
				$modx->db->update($aUpd, $modx->getFullTableName('sbshop_category_attributes'), 'category_id = ' . $iCatId . ' and attribute_id = ' . $iAttrId);
			} else {
				/**
				 * Добавляем новый параметр
				 */
				$aIns = array(
					'category_id' => $iCatId,
					'attribute_id' => $iAttrId,
					'attribute_count' => 1
				);
				$modx->db->insert($aIns, $modx->getFullTableName('sbshop_category_attributes'));
			}
		}
		/**
		 * Обрабатываем все удаляемые параметры
		 */
		foreach($aRemAttributes as $iAttrId) {
			/**
			 * Если такой параметр уже есть в разделе
			 */
			if(isset($aAttrExisted[$iAttrId]) and $aAttrExisted[$iAttrId]['attribute_count'] > 1) {
				/**
				 * Минусуем счетчик
				 */
				$aUpd = array(
					'attribute_count' => $aAttrExisted[$iAttrId]['attribute_count'] - 1
				);
				$modx->db->update($aUpd, $modx->getFullTableName('sbshop_category_attributes'), 'category_id = ' . $iCatId . ' and attribute_id = ' . $iAttrId);
			} else {
				/**
				 * Удаляем параметр полностью
				 */
				$modx->db->delete($modx->getFullTableName('sbshop_category_attributes'), 'category_id = ' . $iCatId . ' and attribute_id = ' . $iAttrId);
			}
		}
	}
	
	/**
	 * Установка обобщения параметров товаров 
	 * @param unknown_type $iProdId
	 * @param unknown_type $iCatId
	 * @param unknown_type $aNewAttributes
	 * @param unknown_type $aOldAttributes
	 */
	public function attributeProductGeneralization($iProdId,$iCatId,$aNewAttributes,$aOldAttributes) {
		global $modx;
		/**
		 * Получаем идентификаторы новых параметров 
		 */
		$aNewAttributeIds = SBAttributeCollection::getAttributeIdsByNames(array_keys($aNewAttributes));
		/**
		 * Массив добавляемых параметров
		 */
		$aAttrIns = array();
		/**
		 * Массив удаляемых параметров
		 */
		$aAttrRemoved = array();
		/**
		 * Запоминаем массив новых параметров
		 */
		$aNewAttributesBase = $aNewAttributes;
		/**
		 * Перерабатываем массив новых параметров для внесения идентификатора в качестве ключа
		 */
		$aNewAttributes = array();
		foreach($aNewAttributesBase as $aNewAttribute) {
			/**
			 * Добавляем идентификатор параметра
			 */
			$aNewAttribute['id'] = $aNewAttributeIds[$aNewAttribute['title']];
			$aNewAttributes[$aNewAttributeIds[$aNewAttribute['title']]] = $aNewAttribute;
		}
		/**
		 * Если товар новый
		 */
		if(count($aOldAttributes) == 0) {
			/**
			 * Это новый товар, нужно просто сохранить значения
			 */
			if(count($aNewAttributeIds) > 0) {
				/**
				 * Собираем нужные значения для запроса
				 */
				$aAttrIds = array();
				foreach ($aNewAttributeIds as $iId) {
					$aAttrIds[] = "($iProdId, $iId, '{$aNewAttributes[$iId]['value']}', '{$aNewAttributes[$iId]['measure']}')";
				}
				$sAttrIds = implode(',',$aAttrIds);
				$sql = 'INSERT INTO ' . $modx->getFullTableName('sbshop_product_attributes') . ' (`product_id`,`attribute_id`,`attribute_value`,`attribute_measure`) VALUES ' . $sAttrIds;
				$modx->db->query($sql);
				/**
				 * Задаем массив обновляемых параметров, куда попадают все имеющиеся параметры
				 */
				$aAttrIns = $aNewAttributeIds;
			}
		} else {
			/**
			 * Получение списка связей
			 */
			$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_product_attributes'),'product_id = ' . $iProdId);
			$aBinds = $modx->db->makeArray($rs);
			/**
			 * Обрабатываем все связи и получаем список имеющихся параметров
			 * @todo добавить проверку единицы измерения
			 */
			$aAttrExisted = array();
			foreach ($aBinds as $aBind) {
				$aAttrExisted[$aBind['attribute_id']] = $aBind['attribute_value'];
			}
			/**
			 * Выделяем из массива добавленных параметров идентификаторы, которые есть в базе
			 * Это даст нам массив для обновления
			 */
			$aAttrUpd = array_intersect($aNewAttributeIds,array_keys($aAttrExisted));
			/**
			 * Делаем обновление
			 */
			if(count($aAttrUpd) > 0) {
				/**
				 * Обрабатываем каждый элемент для обновления
				 */
				foreach ($aAttrUpd as $iId) {
					/**
					 * Нужно проверить не изменилось ли значение
					 */
					if($aAttrExisted[$iId] != $aNewAttributes[$iId]['value']) {
						/**
						 * Значения не совпадают, поэтому их нужно обновить
						 */
						$modx->db->update(array('attribute_value'=>$aNewAttributes[$iId]['value']),$modx->getFullTableName('sbshop_product_attributes'),'product_id = ' . $iProdId . ' AND attribute_id = ' . $iId);
					}
				}
			}
			/**
			 * Выделяем из добавленных параметров абсолютно новые, которых нет в связях
			 * Это даст нам массив для, которого нужно создать новые связи
			 */
			$aAttrIns = array_diff($aNewAttributeIds,array_keys($aAttrExisted));
			/**
			 * Добавляем связи
			 */
			if(count($aAttrIns) > 0) {
				$aAttrIds = array();
				foreach ($aAttrIns as $iId) {
					$aAttrIds[] = "($iProdId, $iId, '{$aNewAttributes[$iId]['value']}', '{$aNewAttributes[$iId]['measure']}')";
				}
				$sAttrIds = implode(',',$aAttrIds);
				$sql = 'INSERT INTO ' . $modx->getFullTableName('sbshop_product_attributes') . ' (`product_id`,`attribute_id`,`attribute_value`, `attribute_measure`) VALUES ' . $sAttrIds;
				$modx->db->query($sql);
			}
			/**
			 * Массив убранных параметров
			 */
			$aAttrRemoved = array_diff(array_keys($aAttrExisted),$aNewAttributeIds);
			/**
			 * подготавливаем на удаление
			 */
			if(count($aAttrRemoved) > 0) {
				$sAttrDel = implode(',',$aAttrRemoved);
				$modx->db->delete($modx->getFullTableName('sbshop_product_attributes'),'product_id = ' . $iProdId . ' AND attribute_id in (' . $sAttrDel . ')');
			}
		}
		/**
		 * Делаем обобщение для категории
		 */
		SBAttributeCollection::setAttributeCategoryGeneralization($iCatId,$aAttrIns,$aAttrRemoved);
	}
	
	/**
	 * Получить список типичных параметров для указанной категории, отсортированный по частоте использования
	 * @param unknown_type $iCatId
	 */
	public function getAttributeCategoryTip($iCatId) {
		global $modx;
		/**
		 * Получаем список параметров по связям
		 */
		$rs = $modx->db->select('b.attribute_name',$modx->getFullTableName('sbshop_category_attributes') . ' as a, ' . $modx->getFullTableName('sbshop_attributes') . 'as b','a.attribute_id = b.attribute_id AND a.category_id = ' . $iCatId,'a.attribute_count DESC');
		$aRaw = $modx->db->makeArray($rs);
		$aAttributes = array();
		foreach ($aRaw as $aItem) {
			$aAttributes[] = $aItem['attribute_name'];
		}
		return $aAttributes;
	}
}

?>