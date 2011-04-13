<?php

/**
 * @name SBShop
 * @author Mukharev Maxim
 * @version 0.1a
 * 
 * @desription
 * 
 * Электронный магазин для MODx
 * 
 * Экшен модуля электронного магазина: Управление категориями
 * 
 */

class cat_mode {
	
	protected $sModuleLink;
	protected $sMode;
	protected $sAct;
	protected $oCategory;
	protected $oOldCategory;
	protected $bIsNewCategory;
	protected $aTemplates;
	protected $sError;
	protected $oParentCategory;
	
	/**
	 * Конструктор
	 * @param $sModuleLink Ссылка на модуль
	 * @param $sMode Режим работы модуля
	 * @param $sAct Выполняемое действие
	 */
	public function __construct($sModuleLink, $sMode, $sAct = '') {
		global $modx;
		/**
		 * Записываем служебную информацию модуля, чтобы делать разные ссылки
		 */
		$this->sModuleLink = $sModuleLink;
		$this->sMode = $sMode;
		$this->sAct = $sAct;
		/**
		 * Создаем экземляр категории
		 */
		$this->oCategory = new SBCategory();
		/**
		 * И старой категории
		 */
		$this->oOldCategory = new SBCategory();
		/**
		 * Экземляр родительской категории
		 */
		$this->oParentCategory = new SBCategory();
		/**
		 * Устанавливаем шаблон
		 */
		$this->aTemplates = $modx->sbshop->getModuleTemplate($sMode);
		/**
		 * Обнуляем содержимое информации об ошибках
		 */
		$this->sError = '';
		/**
		 * Делаем вызов конкретных методов в зависимости от заданного действия
		 */
		switch ($this->sAct) {
			case 'new':
				/**
				 * Создание новой категории
				 */
				/**
				 * Устанавливаем флаг новой категории
				 */
				$this->bIsNewCategory = true;
				/**
				 * Проверка отправки данных
				 */
				if(isset($_POST['ok'])) {
					/**
					 * Сохраняем
					 */
					if($this->saveCategory()) {
						$modx->sbshop->alertWait($this->sModuleLink);
					}
				} else {
					/**
					 * Устанавливаем родителя, для обработки вложенности
					 */
					$iParId = intval($_REQUEST['parid']);
					$this->oCategory->setAttribute('parent',$iParId);
					/**
					 * Выводим форму для создания категории
					 */
					$this->newCategory();
				}
				break;
			case 'edit':
				/**
				 * Редактирование категории
				 */
				/**
				 * Устанавливаем флаг новой категории
				 */
				$this->bIsNewCategory = false;
				/**
				 * Проверка отправки данных
				 */
				if(isset($_POST['ok'])) {
					/**
					 * Сохраняем
					 */
					if($this->saveCategory()) {
						$modx->sbshop->alertWait($this->sModuleLink);
					}
				} else {
					/**
					 * Делаем загрузку информации о категории
					 */
					$iCatId = intval($_REQUEST['catid']);
					$this->oCategory->load($iCatId, true);
					$this->editCategory();
				}
				break;
			case 'pub':
				/**
				 * Публикация категории
				 */
				$iCatId = intval($_REQUEST['catid']);
				$this->publicCategory($iCatId);
				$modx->sbshop->alertWait($this->sModuleLink);
				break;
			case 'unpub':
				/**
				 * Снятие публикации категории
				 */
				$iCatId = intval($_REQUEST['catid']);
				$this->unpublicCategory($iCatId);
				$modx->sbshop->alertWait($this->sModuleLink);
				break;
			case 'del':
				/**
				 * Удаление категории
				 */
				$iCatId = intval($_REQUEST['catid']);
				$this->delCategory($iCatId);
				$modx->sbshop->alertWait($this->sModuleLink);
				break;
			case 'undel':
				/**
				 * Восстановление категории
				 */
				$iCatId = intval($_REQUEST['catid']);
				$this->undelCategory($iCatId);
				$modx->sbshop->alertWait($this->sModuleLink);
				break;
		}
	}
	
	/**
	 * Создание категории. Псевдоним для editCategory().
	 */
	public function newCategory() {
		$this->editCategory();
	}
	
	/**
	 * Подготовка информации для редактирования
	 * @return unknown_type
	 */
	public function editCategory() {
		global $modx, $_style, $_lang;
		/**
		 * Объединяем системный и модульный языковой массив
		 */
		$aLang = array_merge($_lang, $modx->sbshop->lang);
		/**
		 * Подготавливаем языковые плейсхолдеры
		 */
		$phLang = $modx->sbshop->arrayToPlaceholders($aLang,'lang.');
		/**
		 * Подготавливаем стилевые плейсхолдеры
		 */
		$phStyle = $modx->sbshop->arrayToPlaceholders($_style,'style.');
		/**
		 * Подготавливаем плейсхолдеры данных модуля
		 */
		$aModule = $this->oCategory->getAttributes();
		$phModule = $modx->sbshop->arrayToPlaceholders($aModule,'category.');
		/**
		 * Специально устанавливаем плейсхолдер для галочки опубликованности
		 */
		if($this->oCategory->getAttribute('published') == 1) {
			$phModule['[+category.published+]'] = 'checked="checked"';
		} else {
			$phModule['[+category.published+]'] = '';
		}
		/**
		 * Если есть информация об ошибках, то выводим через плейсхолдер [+category.error+]
		 */
		if($this->sError) {
			$phModule['[+category.error+]'] = '<div class="error">' . $this->sError . '</div>';
		} else {
			$phModule['[+category.error+]'] = '';
		}
		/**
		 * Служебные плейсхолдеры для модуля 
		 */
		$phModule['[+site.url+]'] = MODX_BASE_URL;
		$phModule['[+module.link+]'] = $this->sModuleLink;
		$phModule['[+module.act+]'] = $this->sAct;
		/**
		 * Подготавливаем плейсхолдеры вспомогательного список параметров
		 * @todo: Предстоит переделать
		 */
		//$aAttrTip = SBAttributeCollection::getAttributeCategoryTip($this->oCategory->getAttribute('parent'));
		//$phModule['[+category.attribute_tips+]'] = 'Предлагаемые параметры: <ul><li>' . implode('</li><li>',$aAttrTip) . '</li></ul>';
		/**
		 * Получаем информацию об основных параметрах для фильтра
		 */
		$aFilterGeneral = array();
		foreach($modx->sbshop->config['filter_general'] as $sFilterName) {
			$aFilterGeneral[$modx->sbshop->lang['product_' . $sFilterName]] = $sFilterName;
		}
		/**
		 * Список основных параметров
		 */
		$aFilterAttributes = $this->oCategory->getAggregatedAttributes();
		/**
		 * Сливаем все типы фильтров
		 */
		$aFilterNames = array_merge($aFilterGeneral, $aFilterAttributes);
		/**
		 * Обрабатываем каждое значение
		 */
		foreach($aFilterNames as $sFilterName => $sFilterId) {
			/**
			 * Получаем сохраненные настройки фильтра
			 */
			$aFilterData = $this->oCategory->getFilterById($sFilterId);
			/**
			 * Если данных нет
			 */
			if(!$aFilterData) {
				$aRepl = array(
					'[+sb.id+]' => $sFilterId,
					'[+sb.title+]' => $sFilterName,
					'[+sb.values+]' => '',
				);
			} else {
				/**
				 * Обрабатываем имеющиеся значения
				 */
				$sFilterValues = '';
				foreach($aFilterData['values'] as $aFilterValue) {
					/**
					 * Готовим плейсхолдеры для значений
					 */
					$aRepl = $modx->sbshop->arrayToPlaceholders($aFilterValue,'sb.value.');
					$aRepl['[+sb.id+]'] = $sFilterId;
					/**
					 * Добавляем значений к списку
					 */
					$sFilterValues .= str_replace(array_keys($aRepl),array_values($aRepl),$this->aTemplates['filter_value']);
				}
				/**
				 * Плейсхолдеры для контейнера фильтра
				 */
				$aRepl = array(
					'[+sb.id+]' => $sFilterId,
					'[+sb.title+]' => $sFilterName,
					'[+sb.values+]' => $sFilterValues,
					'[+sb.on+]' => 'checked="checked"',
					'[+sb.' . $aFilterData['type'] . '+]' => 'selected="selected"',
					'[+sb.' . $aFilterData['type'] . '.visible+]' => 'visible'
				);
			}
			/**
			 * Вставляем данные в контейнер
			 */
			$phModule['[+sb.filter+]'] .= str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplates['filter_outer']);
		}
		/**
		 * Делаем замену плейсхолдеров блоков
		 */
		$sOutput = str_replace(array_keys($phModule),array_values($phModule),$this->aTemplates['category_form']);
		/**
		 * Объединяем все плейсхолдеры
		 */
		$phData = array_merge($phLang,$phStyle);
		/**
		 * Делаем замену плейсхолдеров языка и стилей
		 */
		$sOutput = str_replace(array_keys($phData),array_values($phData),$sOutput);
		/**
		 * Убираем неиспользованные плейсхолдеры перед выводом
		 */
		if (strpos($sOutput, '[+') > -1) {
			$sOutput = preg_replace('~\[\+(.*?)\+\]~', '', $sOutput);
		}
		/**
		 * Выводим информацию
		 */
		echo $sOutput;
	}
	
	/**
	 * Публикация категории
	 * @param unknown_type $iCatId
	 */
	public function publicCategory($iCatId = 0) {
		/**
		 * Если идентификатор неверный, то выходим
		 */
		if($iCatId == 0) {
			return false;
		}
		// Загружаем информацию о категории
		$this->oCategory->load($iCatId, true);
		/**
		 * Если категория не опубликована
		 */
		if($this->oCategory->getAttribute('published') == 0) {
			/**
			 * Устанавливаем значение опубликованности
			 */
			$this->oCategory->setAttribute('published',1);
			/**
			 * Задаем дату модификации
			 */
			$this->oCategory->setAttribute('date_edit',date('Y-m-d G:i:s'));
			/**
			 * Сохраняем результат
			 */
			$this->oCategory->save();
		}
	}
	
	/**
	 * Отмена публикации категории
	 * @param unknown_type $iCatId
	 */
	public function unpublicCategory($iCatId = 0) {
		/**
		 * Если идентификатор неверный, то выходим
		 */
		if($iCatId == 0) {
			return false;
		}
		// Загружаем информацию о категории
		$this->oCategory->load($iCatId, true);
		/**
		 * Если категория опубликована
		 */
		if($this->oCategory->getAttribute('published') == 1) {
			/**
			 * Снимаем значение опубликованности
			 */
			$this->oCategory->setAttribute('published',0);
			/**
			 * Задаем дату модификации
			 */
			$this->oCategory->setAttribute('date_edit',date('Y-m-d G:i:s'));
			/**
			 * Сохраняем результат
			 */
			$this->oCategory->save();
		}
	}
	
	/**
	 * Удаление категории в корзину
	 * @param $iCatId
	 */
	public function delCategory($iCatId) {
		/**
		 * Если идентификатор неверный, то выходим
		 */
		if($iCatId == 0) {
			return false;
		}
		// Загружаем информацию о категории
		$this->oCategory->load($iCatId, true);
		/**
		 * Если категория не удалена
		 */
		if($this->oCategory->getAttribute('deleted') == 0) {
			/**
			 * Помечаем на удаление
			 */
			$this->oCategory->setAttribute('deleted',1);
			/**
			 * Задаем дату модификации
			 */
			$this->oCategory->setAttribute('date_edit',date('Y-m-d G:i:s'));
			/**
			 * Сохраняем результат
			 */
			$this->oCategory->save();
		}
	}
	
	/**
	 * Восстановление категории из корзины
	 * @param $iCatId
	 */
	public function undelCategory($iCatId) {
		/**
		 * Если идентификатор неверный, то выходим
		 */
		if($iCatId == 0) {
			return false;
		}
		// Загружаем информацию о категории
		$this->oCategory->load($iCatId, true);
		/**
		 * Если категория удалена
		 */
		if($this->oCategory->getAttribute('deleted') == 1) {
			/**
			 * Убираем пометку на удаление
			 */
			$this->oCategory->setAttribute('deleted',0);
			/**
			 * Задаем дату модификации
			 */
			$this->oCategory->setAttribute('date_edit',date('Y-m-d G:i:s'));
			/**
			 * Сохраняем результат
			 */
			$this->oCategory->save();
		}
	}
	
	/**
	 * Обработка полученной информации и сохранение
	 * @return unknown_type
	 */
	protected function saveCategory() {
		global $modx;
		/**
		 * Делаем проверку значений и устанавливаем для текущей категории  
		 */
		if($this->checkData()) {
			/**
			 * Загружаем старые данные о категории, если она не новая, для возможности сравнения
			 */
			if(!$this->bIsNewCategory) {
				$this->oOldCategory->load($this->oCategory->getAttribute('id'), true);
			}
			/**
			 * Проверка прошла успешно и объект содержит все нужные данные. Просто сохраняем их.
			 */
			$this->oCategory->save();
			/**
			 * Если категория новая, то нужно еще установить URL
			 * Делается это после сохранения, так как нам нужен идентификатор на случай, если псевдоним не установлен
			 */
			if($this->bIsNewCategory) {
				$sAlias = $this->oCategory->getAttribute('alias');
				if(!$sAlias) {
					$sAlias = $this->oCategory->getAttribute('id');
				}
				
				if($this->oParentCategory->getAttribute('id') > 0) {
					/**
					 * Есть родитель, значит нужна обработка вложенности
					 */
					if($modx->config['friendly_urls'] == 1) {
						/**
						 * Вложенность учитывается, добавляем URL родителя
						 */
						$sUrl = $this->oParentCategory->getAttribute('url') . '/' . $sAlias;
					} else {
						/**
						 * Вложенности нет, поэтому учитываем только псевдоним
						 */
						$sUrl = $sAlias;
					}
				} else {
					/**
					 * Нет вложенности, учитываем только псевдоним
					 */
					$sUrl = $sAlias;
				}
				$this->oCategory->setAttribute('url',$sUrl);
				/**
				 * Рассчитываем путь для категории
				 */
				if(!$this->oParentCategory->getAttribute('path')) {
					$sPath = '0.' . $this->oCategory->getAttribute('id');
				} else {
					$sPath = $this->oParentCategory->getAttribute('path') . '.' . $this->oCategory->getAttribute('id');
				}
				$this->oCategory->setAttribute('path',$sPath);
			} else {
				/**
				 * А если старая, то необходимо добавить дату редактирования
				 */
				$this->oCategory->setAttribute('date_edit',date('Y-m-d G:i:s'));
			}
			/**
			 * Снова сохраняем.
			 */
			$this->oCategory->save();
			/**
			 * Делаем обобщение параметров
			 */
			SBAttributeCollection::setAttributeCategoryGeneralization($this->oCategory->getAttribute('id'), $this->oCategory->getExtendAttributes(), $this->oOldCategory->getExtendAttributes());
			return true;
		} else {
			/**
			 * Что-то при проверке пошло не так, поэтому снова выводим форму
			 */
			$this->editCategory();
			return false;
		}
		
	}
	
	/**
	 * Проверка полученных из формы данных
	 */
	protected function checkData() {
		global $modx;
		$bError = false;
		/**
		 * Если идентификатор категории передан
		 */
		if(intval($_POST['catid']) > 0) {
			/**
			 * Устанавливаем идентификатор категории
			 */
			$this->oCategory->setAttribute('id',intval($_POST['catid']));
			/**
			 * Указываем флаг, что категория не новая, а редактируется
			 */
			$this->bIsNewCategory = false;
		} else {
			/**
			 * Категория новая, нужно установить флаг
			 */
			$this->bIsNewCategory = true;
		}
		/**
		 * Установка идентификатора родителя
		 */
		$iParentId = intval($_POST['parid']);
		if($iParentId > 0) {
			/**
			 * Устанавливаем идентификатор родителя для категории
			 */
			$this->oCategory->setAttribute('parent',$iParentId);
			/**
			 * Загружаем информацию о родителе
			 */
			$oParentCategory = new SBCategory();
			$oParentCategory->load($iParentId, true);
			/**
			 * Определяем уровень вложенности
			 */
			$iLevel = $oParentCategory->getAttribute('level') + 1;
			$this->oCategory->setAttribute('level',$iLevel);
			/**
			 * Добавляем информацию о родителе для текущей категории
			 */
			$this->oParentCategory = $oParentCategory;
		}
		/**
		 * Проверяем псевдоним. Он должен быть стандартным.
		 */
		if($_POST['alias'] == '' || preg_match('/^[\w\-\_]+$/i',$_POST['alias'])) {
			/**
			 * Если алиас не передан
			 */
			if($_POST['alias'] == '') {
				/**
				 * Подключаем класс плагина TransAlias
				 */
				require_once MODX_BASE_PATH . 'assets/plugins/transalias/transalias.class.php';
				$oTrans = new TransAlias();
				$oTrans->loadTable($modx->sbshop->config['transalias_table_name'], $modx->sbshop->config['transalias_remove_periods']);
				/**
				 * Получаем алиас на основе заголовка
				 */
				$sAlias = $oTrans->stripAlias($_POST['title'],$modx->sbshop->config['transalias_char_restrict'],$modx->sbshop->config['transalias_word_separator']);
			} else {
				/**
				 * Псевдоним задан, его и берем
				 */
				$this->oCategory->setAttribute('alias',$_POST['alias']);
				$sAlias = $_POST['alias'];
			}
			$this->oCategory->setAttribute('alias',$sAlias);
		} else {
			$this->sError = $modx->sbshop->lang['category_error_alias'];
			$bError = true;
		}
		/**
		 * Устанавливаем URL с учетом вложенности и ее настройки в админке
		 * Правда для нового документа здесь не получится установить URL, так как идентификатор не известен
		 */
		if(!$this->bIsNewCategory) {
			/**
			 * Это не новая категория, можно смело задать URL
			 */
			if($iParentId > 0) {
				/**
				 * Есть родитель, значит нужна обработка вложенности
				 */
				if($modx->config['friendly_urls'] == 1) {
					/**
					 * Вложенность учитывается, добавляем URL родителя
					 */
					$sUrl = $oParentCategory->getAttribute('url') . '/' . $sAlias;
				} else {
					/**
					 * Вложенности нет, поэтому учитываем только псевдоним
					 */
					$sUrl = $sAlias;
				}
			} else {
				/**
				 * Нет вложенности, учитываем только псевдоним
				 */
				$sUrl = $sAlias;
			}
			$this->oCategory->setAttribute('url',$sUrl);
		}
		/**
		 * Проверяем заголовок. Он должен быть.
		 */
		if(strlen($modx->db->escape($_POST['title'])) > 0) {
			$this->oCategory->setAttribute('title',$modx->db->escape($_POST['title']));
		} else {
			$this->sError = $modx->sbshop->lang['category_error_title'];
			$bError = true;
		}
		/**
		 * Добавляем расширенный заголовок.
		 */
		$this->oCategory->setAttribute('longtitle',$modx->db->escape($_POST['longtitle']));
		/**
		 * Категория опубликована?
		 */
		if($_POST['published'] == 1) {
			$this->oCategory->setAttribute('published',1);
		} else {
			$this->oCategory->setAttribute('published',0);
		}
		/**
		 * Устанавливаем содержимое
		 */
		$this->oCategory->setAttribute('description',$_POST['description']);
		/**
		 * Установка расширенных параметров
		 */
		$this->oCategory->setAttribute('attributes',$_POST['attributes']);
		/**
		 * Разбираем параметры
		 */
		if($_POST['attributes'] != '') {
			/**
			 * Делаем десериализацию
			 */
			$this->oCategory->unserializeAttributes($_POST['attributes']);
			/**
			 * Актуализируем коллекцию параметров.
			 * Передаем только названия
			 */
			SBAttributeCollection::setAttributeCollection(array_keys($this->oCategory->getExtendAttributes()));
		}
		/**
		 * Обрабатываем включенные фильтры
		 */
		foreach(array_keys($_POST['filter_on']) as $sKey) {
			/**
			 * Если есть значения
			 */
			if(is_array($_POST['filter_value_title'][$sKey]) and count($_POST['filter_value_title'][$sKey]) > 0) {
				/**
				 * Массив значений фильтра
				 */
				$aValues = array();
				/**
				 * Обрабатываем каждое значение
				 */
				foreach($_POST['filter_value_title'][$sKey] as $iValueId => $sValueTitle) {
					/**
					 * Если заголовок значения фильтра не пуст
					 */
					if($sValueTitle != '') {
						/**
						 * Если указан вариант "диапазон значений"
						 */
						if($_POST['filter_type'][$sKey] == 'rng') {
							/**
							 * Если есть минимальное или максимальное значений
							 */
							if($_POST['filter_value_min'][$sKey][$iValueId] != '' and $_POST['filter_value_max'][$sKey][$iValueId] != '') {
								$aValues[intval($_POST['filter_value_min'][$sKey][$iValueId]) . '-' . intval($_POST['filter_value_max'][$sKey][$iValueId])] = array(
									'title' => $sValueTitle,
									'min' => intval($_POST['filter_value_min'][$sKey][$iValueId]),
									'max' => intval($_POST['filter_value_max'][$sKey][$iValueId]),
								);
							} elseif($_POST['filter_value_min'][$sKey][$iValueId] != '') {
								$aValues[intval($_POST['filter_value_min'][$sKey][$iValueId]) . '-'] = array(
									'title' => $sValueTitle,
									'min' => intval($_POST['filter_value_min'][$sKey][$iValueId]),
								);
							} elseif($_POST['filter_value_max'][$sKey][$iValueId] != '') {
								$aValues['-' . intval($_POST['filter_value_max'][$sKey][$iValueId])] = array(
									'title' => $sValueTitle,
									'max' => intval($_POST['filter_value_max'][$sKey][$iValueId]),
								);
							}
						} else {
							$aValues[mb_strtolower($_POST['filter_value_eqv'][$sKey][$iValueId])] = array(
								'title' => $sValueTitle,
								'eqv' => mb_strtolower($_POST['filter_value_eqv'][$sKey][$iValueId]),
							);
						}
					}
				}
				/**
				 * Данные фильтра
				 */
				$aFilter = array(
					'id' => $sKey,
					'type' => $_POST['filter_type'][$sKey],
				);
				/**
				 * Если идентификатор находится в списке основных полей или имеет числовой идентификатор
				 */
				if(in_array($sKey,$modx->sbshop->config['filter_general'])) {
					/**
					 * Добавляем фильтр
					 */
					$this->oCategory->addFilter($aFilter, $aValues);
				} elseif(intval($sKey) > 0) {
					/**
					 * делаем перевод в число
					 */
					$aFilter['id'] = intval($sKey);
					/**
					 * Добавляем фильтр
					 */
					$this->oCategory->addFilter($aFilter, $aValues);
				}

			}

		}
		/**
		 * Возвращаем результат проверки
		 */
		return !$bError;
	}
	
}


?>