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
	 * Установка обобщения параметров раздела
	 * @param SBCategory $oCategory
	 * @param $aNewAttributes
	 * @param $aOldAttributes
	 * @return void
	 */
	public function attributeCategoryGeneralization($oCategory, $aNewAttributes, $aOldAttributes) {
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
		 * Если старых параметров нет
		 */
		if(count($aOldAttributes) == 0) {
			/**
			 * Если есть новые
			 */
			if(count($aNewAttributeIds) > 0) {
				/**
				 * Собираем нужные значения для запроса
				 */
				$aAttrIds = array();
				foreach ($aNewAttributeIds as $iId) {
					$aAttrIds[] = "({$oCategory->getAttribute('id')}, $iId, 0, '{$aNewAttributes[$iId]['measure']}', '{$aNewAttributes[$iId]['type']}')";
				}
				$sAttrIds = implode(',',$aAttrIds);
				$sql = 'INSERT INTO ' . $modx->getFullTableName('sbshop_category_attributes') . ' (`category_id`,`attribute_id`, `attribute_count`,`attribute_measure`,`attribute_type`) VALUES ' . $sAttrIds;
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
			$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_category_attributes'),'category_id = ' . $oCategory->getAttribute('id'));
			$aBinds = $modx->db->makeArray($rs);
			/**
			 * Обрабатываем все связи и получаем список имеющихся параметров
			 * @todo добавить проверку единицы измерения
			 */
			$aAttrExisted = array();
			foreach ($aBinds as $aBind) {
				$aAttrExisted[$aBind['attribute_id']] = $aBind;
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
					 * Нужно проверить не изменилась ли единица измерения или тип
					 */
					if($aAttrExisted[$iId]['attribute_measure'] != $aNewAttributes[$iId]['measure'] || $aAttrExisted[$iId]['attribute_type'] != $aNewAttributes[$iId]['type']) {
						$aUpd = array(
							'attribute_measure'=>$aNewAttributes[$iId]['measure'],
							'attribute_type'=>$aNewAttributes[$iId]['type']
						);
						/**
						 * Значения не совпадают, поэтому их нужно обновить
						 */
						$modx->db->update($aUpd, $modx->getFullTableName('sbshop_category_attributes'),'category_id = ' . $oCategory->getAttribute('id') . ' AND attribute_id = ' . $iId);
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
					$aAttrIds[] = "({$oCategory->getAttribute('id')}, $iId, 0, '{$aNewAttributes[$iId]['measure']}', '{$aNewAttributes[$iId]['type']})";
				}
				$sAttrIds = implode(',',$aAttrIds);
				$sql = 'INSERT INTO ' . $modx->getFullTableName('sbshop_category_attributes') . ' (`category_id`,`attribute_id`,`attribute_count`, `attribute_measure`, `attribute_type`) VALUES ' . $sAttrIds;
				$modx->db->query($sql);
			}
		}
	}

	/**
	 * Установка обобщения параметров товаров
	 * @param SBProduct $oProduct
	 * @param SBCategory $oCategory
	 * @param unknown_type $aNewAttributes
	 * @param unknown_type $aOldAttributes
	 */
	public function attributeProductGeneralization($oProduct, $oCategory, $aNewAttributes, $aOldAttributes) {
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
					$aAttrIds[] = "({$oProduct->getAttribute('id')}, $iId, '{$aNewAttributes[$iId]['value']}', '{$aNewAttributes[$iId]['measure']}')";
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
			$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_product_attributes'),'product_id = ' . $oProduct->getAttribute('id'));
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
						$modx->db->update(array('attribute_value'=>$aNewAttributes[$iId]['value']),$modx->getFullTableName('sbshop_product_attributes'),'product_id = ' . $oProduct->getAttribute('id') . ' AND attribute_id = ' . $iId);
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
					$aAttrIds[] = "({$oProduct->getAttribute('id')}, $iId, '{$aNewAttributes[$iId]['value']}', '{$aNewAttributes[$iId]['measure']}')";
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
				$modx->db->delete($modx->getFullTableName('sbshop_product_attributes'),'product_id = ' . $oProduct->getAttribute('id') . ' AND attribute_id in (' . $sAttrDel . ')');
			}
		}
		/**
		 * Если ничего обновлять не требуется
		 */
		if(count($aAttrIns) == 0 and count($aAttrRemoved) == 0) {
			/**
			 * Просто выходим
			 */
			return;
		}
		/**
		 * задаем расширенные параметры для раздела
		 */
		$oCategory->setExtendAttributes($aNewAttributes);
		/**
		 * Совмещаем список параметров для последующего запроса в базу
		 */
		$aAttrIds = implode(',',array_merge($aAttrIns, $aAttrRemoved));
		/**
		 * Получаем имеющиеся данные об обобщенных параметрах
		 */
		$rs = $modx->db->select('*',$modx->getFullTableName('sbshop_category_attributes'),'category_id = ' . $oCategory->getAttribute('id') . ' and attribute_id in (' . $aAttrIds . ')');
		$cnt = $modx->db->getRecordCount($rs);
		/**
		 * Формируем массив имеющихся параметров
		 */
		for($i=0; $i<$cnt; $i++) {
			$aRow = $modx->db->getRow($rs);
			$aAttrExisted[$aRow['attribute_id']] = $aRow;
		}
		/**
		 * Обрабатываем каждый добавленный параметр
		 */
		foreach($aAttrIns as $iAttrId) {
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
				$modx->db->update($aUpd, $modx->getFullTableName('sbshop_category_attributes'), 'category_id = ' . $oCategory->getAttribute('id') . ' and attribute_id = ' . $iAttrId);
			} else {
				/**
				 * Добавляем новый параметр
				 */
				$aIns = array(
					'category_id' => $oCategory->getAttribute('id'),
					'attribute_id' => $iAttrId,
					'attribute_count' => 1,
					'attribute_measure' => $aNewAttributes[$iAttrId]['measure'],
					'attribute_type' => $aNewAttributes[$iAttrId]['type']
				);
				$modx->db->insert($aIns, $modx->getFullTableName('sbshop_category_attributes'));
			}
		}
		/**
		 * Обрабатываем все удаляемые параметры
		 */
		foreach($aAttrRemoved as $iAttrId) {
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
				$modx->db->update($aUpd, $modx->getFullTableName('sbshop_category_attributes'), 'category_id = ' . $oCategory->getAttribute('id') . ' and attribute_id = ' . $iAttrId);
			} else {
				/**
				 * Удаляем параметр полностью
				 */
				$modx->db->delete($modx->getFullTableName('sbshop_category_attributes'), 'category_id = ' . $oCategory->getAttribute('id') . ' and attribute_id = ' . $iAttrId);
			}
		}
	}
	
	/**
	 * Получить список типичных параметров для указанной категории, отсортированный по частоте использования
	 * @param unknown_type $oCategory
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