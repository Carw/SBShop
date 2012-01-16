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
 * Экшен модуля электронного магазина: Управление товарами
 * 
 */

class prod_mode {
	
	protected $sModuleLink;
	protected $sMode;
	protected $sAct;
	protected $oProduct;
	protected $oOldProduct;
	protected $oCategory;
	protected $bIsNewProduct;
	protected $aTemplate;
	protected $sError;
	
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
		 * Создаем экземляр товара
		 */
		$this->oProduct = new SBProduct();
		/**
		 * Экземпляр старого товара
		 */
		$this->oOldProduct = new SBProduct();
		/**
		 * Раздел
		 */
		$this->oCategory = new SBCategory();
		/**
		 * Обнуляем содержимое информации об ошибках
		 */
		$this->sError = '';
		/**
		 * Обрабатываем заданное действие
		 */
		switch ($this->sAct) {
			case 'new':
				/**
				 * Создание нового продукта
				 * Устанавливаем флаг нового продукта
				 */
				$this->bIsNewProduct = true;
				/**
				 * Проверка отправки данных
				 */
				if(isset($_POST['ok'])) {
					/**
					 * Сохраняем
					 */
					if($this->saveProduct()) {
						$modx->sbshop->alertWait($this->sModuleLink);
					}
				} else {
					/**
					 * Устанавливаем категорию куда будет помещен товар
					 */
					$iCatId = intval($_REQUEST['catid']);
					$this->oCategory->load($iCatId, true);
					$this->oProduct->setAttribute('category',$iCatId);
					/**
					 * Выводим форму для создания категории
					 */
					$this->newProduct();
				}
				break;
			case 'edit':
				/**
				 * Редактирование товара
				 * Устанавливаем флаг нового товара
				 */
				$this->bIsNewProduct = false;
				/**
				 * Проверка отправки данных
				 */
				if(isset($_POST['ok'])) {
					/**
					 * Сохраняем
					 */
					if($this->saveProduct()) {
						$modx->sbshop->alertWait($this->sModuleLink);
					}
				} else {
					/**
					 * Делаем загрузку информации о товаре
					 */
					$iProdId = intval($_REQUEST['prodid']);
					$this->oProduct->load($iProdId, true);
					$this->editProduct();
				}
				break;
			case 'copy':
				/**
				 * Копируем товар
				 */
				$this->copyProduct();
				$modx->sbshop->alertWait($this->sModuleLink);
				break;
			case 'pub':
				/**
				 * Публикация товара
				 */
				$iProdId = intval($_REQUEST['prodid']);
				$this->publicProduct($iProdId);
				$modx->sbshop->alertWait($this->sModuleLink);
				break;
			case 'unpub':
				/**
				 * Снятие публикации товара
				 */
				$iProdId = intval($_REQUEST['prodid']);
				$this->unpublicProduct($iProdId);
				$modx->sbshop->alertWait($this->sModuleLink);
				break;
			case 'del':
				/**
				 * Удаление товара
				 */
				$iProdId = intval($_REQUEST['prodid']);
				$this->delProduct($iProdId);
				$modx->sbshop->alertWait($this->sModuleLink);
				break;
			case 'undel':
				/**
				 * Восстановление товара
				 */
				$iProdId = intval($_REQUEST['prodid']);
				$this->undelProduct($iProdId);
				$modx->sbshop->alertWait($this->sModuleLink);
				break;
		}
	}
	
	/**
	 * Создание продукта. Псевдоним для editProduct()
	 */
	public function newProduct() {
		$this->editProduct();
	}
	
	/**
	 * Подготовка информации для редактирования
	 */
	public function editProduct() {
		global $modx, $_style, $_lang;
		/**
		 * Получаем набор шаблонов
		 */
		$this->aTemplate = $modx->sbshop->getModuleTemplate($this->sMode);
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
		 * Подготавливаем плейсхолдеры данных продукта
		 */
		$aModule = $this->oProduct->getGeneralAttributes();
		$phModule = $modx->sbshop->arrayToPlaceholders($aModule,'product.');
		/**
		 * Специально устанавливаем плейсхолдер для галочки опубликованности
		 */
		if($this->oProduct->getAttribute('published') == 1) {
			$phModule['[+product.published+]'] = 'checked="checked"';
		} else {
			$phModule['[+product.published+]'] = '';
		}
		/**
		 * Настройка "есть в наличии"
		 */
		if($this->oProduct->getAttribute('existence') == 1) {
			$phModule['[+product.existence+]'] = 'checked="checked"';
		} else {
			$phModule['[+product.existence+]'] = '';
		}
		/**
		 * Если есть информация об ошибках, то выводим через плейсхолдер [+product.error+]
		 */
		if($this->sError) {
			$phModule['[+product.error+]'] = '<div class="error">' . $this->sError . '</div>';
		} else {
			$phModule['[+product.error+]'] = '';
		}
		/**
		 * Служебные плейсхолдеры для модуля 
		 */
		$phModule['[+site.url+]'] = MODX_BASE_URL;
		$phModule['[+module.link+]'] = $this->sModuleLink;
		$phModule['[+module.act+]'] = $this->sAct;
		/**
		 * Дополнительные параметры
		 */
		$aAttributes = $this->oProduct->getExtendAttributes();
		/**
		 * Если дополнительных параметров нет, то получаем шаблон от раздела
		 */
		if(count($aAttributes) == 0) {
			$aAttributes = $this->oCategory->getExtendAttributes();
		}
		/**
		 * Обрабатываем каждый параметр
		 */
		$phModule['[+attributes+]'] = '';
		foreach($aAttributes as $aAttribute) {
			$aRepl = $modx->sbshop->arrayToPlaceholders($aAttribute,'attribute.');
			/**
			 * Добавляем плейсхолдер для различных типов параметров
			 */
			if($aAttribute['type'] == 'p') {
				$aRepl['[+attribute.type.primary+]'] = 'selected="selected"';
			} elseif ($aAttribute['type'] == 'h') {
				$aRepl['[+attribute.type.hidden+]'] = 'selected="selected"';
			} else {
				$aRepl['[+attribute.type.normal+]'] = 'selected="selected"';
			}
			/**
			 * Вставляем данные параметра в шаблон
			 */
			$phModule['[+attributes+]'] .= str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplate['attribute_outer']);
		}
		/**
		 * Массив изображений
		 */
		$aImageList = $this->oProduct->getAllImages();
		/**
		 * Массив для рядов изображений
		 */
		$aImages = array();
		/**
		 * Если изображения есть
		 */
		if(count($aImageList) > 0) {
			/**
			 * Обрабатываем каждое изображение
			 */
			foreach ($aImageList as $sKey => $aImage) {
				/**
				 * Массив значений
				 */
				$aRepl = array(
					'id' => $sKey,
					'image' => $modx->sbshop->config['image_base_url'] . $this->oProduct->getAttribute('id') . '/' . $sKey . '-prv.jpg'
				);
				/**
				 * Готовим плейсхолдеры
				 */
				$aRepl = $modx->sbshop->arrayToPlaceholders($aRepl);
				/**
				 * Делаем вставку в шаблон изображения
				 */
				$aImages[] = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplate['image_row']);
			}
		}
		$phModule['[+images+]'] = implode('', $aImages);
		/**
		 * Массив для рядов опций
		 */
		$aOptions = array();
		/**
		 * Получаем список опций
		 */
		$aOptionList = $this->oProduct->getOptionNames();
		/**
		 * Если опции есть
		 */
		if ($aOptionList) {
			/**
			 * Обрабатываем каждую опцию
			 */
			foreach ($aOptionList as $aOption) {
				/**
				 * Массив для значений опций
				 */
				$aValues = array();
				/**
				 * Получаем значение опции
				 */
				$aValuesList = $this->oProduct->getValuesByOptionName($aOption['title']);
				/**
				 * Если есть значения
				 */
				if ($aValuesList) {
					/**
					 * Обрабатываем каждое значение
					 */
					foreach ($aValuesList as $aValue) {
						/**
						 * Подготавливаем плейсхолдеры
						 */
						$aRepl = $modx->sbshop->arrayToPlaceholders($aValue, 'value.');
						$aRepl['[+option.id+]'] = $aOption['id'];
						/**
						 * Временное решение по очистке новых плейсхолдеров
						 */
						if (!isset($aValue['class'])) {
							$aRepl['[+value.class+]'] = '';
						}
						if (!isset($aValue['image'])) {
							$aRepl['[+value.image+]'] = '';
						}
						/**
						 * Делаем вставку в шаблон
						 */
						$aValues[] = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplate['option_row']);
					}
				}
				/**
				 * Готовим плейсхолдеры
				 */
				$aRepl = $modx->sbshop->arrayToPlaceholders($aOption, 'option.');
				/**
				 * Если исключение из комплектаций выбрано
				 */
				if ($aOption['notbundle']) {
					/**
					 * Настройка исключения из комплектаций
					 */
					$aRepl['[+option.notbundle.checked+]'] = 'checked="checked"';
				}
				/**
				 * Если исключение из комплектаций выбрано
				 */
				if ($aOption['hidden']) {
					/**
					 * Настройка скрытия опции
					 */
					$aRepl['[+option.hidden.checked+]'] = 'checked="checked"';
				}
				/**
				 * Если установлена подсказка
				 */
				if ($aOption['tip']) {
					$oTip = new SBTip();
					$oTip->load($aOption['tip']);
					$aRepl = array_merge($aRepl, $modx->sbshop->arrayToPlaceholders($oTip->getAttributes(), 'option.tip.'));
				}
				/**
				 * Вставляем ряды
				 */
				$aRepl['[+sb.wrapper+]'] = implode('', $aValues);
				/**
				 * Вставляем в шаблон
				 */
				$aOptions[] = str_replace(array_keys($aRepl), array_values($aRepl), $this->aTemplate['option_outer']);
			}
		}
		$phModule['[+options+]'] = implode('', $aOptions);
		/**
		 * Массив для рядов комлпектаций
		 */
		$aBundleRows = array();
		/**
		 * Получаем массив комплектаций
		 */
		$aBundleList = $this->oProduct->getBundleList();
		/**
		 * Обрабатываем каждую комплектацию
		 */
		foreach ($aBundleList as $aBundle) {
			/**
			 * Если это не персональная комплектация
			 */
			if($aBundle['title'] !== 'personal') {
				/**
				 * Обрабатываем опции
				 */
				$aOptionRows = array();
				foreach ($aBundle['options'] as $sOptName => $sOptVal) {
					$aOptionRows[] = $sOptName . ':' . $sOptVal;
				}
				/**
				 * Массив замен
				 * @todo нужно привести везде название комплектации к одному виду. Сейчас используется name и title в разных местах.
				 */
				$aBundleRepl = array(
					'[+bundle_name+]' => $aBundle['title'],
					'[+bundle_price+]' => $aBundle['price'],
					'[+bundle_price_add+]' => $aBundle['price_add'],
					'[+bundle_settings+]' => implode(',', $aOptionRows),
					'[+bundle_description+]' => $aBundle['description']
				);
				/**
				 * Вставляем данные в шаблон
				 */
				$aBundleRows[] = str_replace(array_keys($aBundleRepl), array_values($aBundleRepl), $this->aTemplate['bundles']);
			} else {
				$phModule['[+bundle.personal.checked+]'] = 'checked="checked"';
			}
		}
		/**
		 * Объединяем все плейсхолдеры
		 */
		$phData = array_merge($phStyle,$phModule);
		/**
		 * Добавляем комлпектации
		 */
		$sPageTpl = str_replace('[+bundles+]', implode('', $aBundleRows), $this->aTemplate['product']);
		/**
		 * Делаем замену плейсхолдеров
		 */
		$sOutput = str_replace(array_keys($phData),array_values($phData),$sPageTpl);
		/**
		 * Замена языковых плейсхолдеров
		 */
		$sOutput = str_replace(array_keys($phLang), array_values($phLang), $sOutput);
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

	public function copyProduct() {
		global $modx;
		/**
		 * Делаем загрузку информации о товаре
		 */
		$iProdId = intval($_REQUEST['prodid']);
		$this->oProduct->load($iProdId, true);
		/**
		 * Загружаем раздел
		 */
		$this->oCategory->load($this->oProduct->getAttribute('category'),true);
		/**
		 * Удаляем идентификатор товара (делаем его новым)
		 */
		$this->oProduct->setAttribute('id', null);
		/**
		 * Изменяем alias
		 */
		$sAlias = $modx->sbshop->lang['product_copy_alias_prefix'] . $this->oProduct->getAttribute('alias');
		$this->oProduct->setAttribute('alias', $sAlias);
		/**
		 * Формируем URL с учетов части из категории
		 */
		$sUrl = $this->oCategory->getAttribute('url') . '/' . $sAlias;
		$this->oProduct->setAttribute('url',$sUrl);
		/**
		 * Исправляем название
		 */
		$sTitle = $modx->sbshop->lang['product_copy_title_prefix'] . $this->oProduct->getAttribute('title');
		$this->oProduct->setAttribute('title', $sTitle);
		/**
		 * Сохраняем товар
		 */
		$this->oProduct->save();
		/**
		 * Делаем обобщение параметров для товара
		 */
		SBAttributeCollection::attributeProductGeneralization($this->oProduct, $this->oCategory, $this->oProduct->getExtendAttributes(), $this->oOldProduct->getExtendAttributes());
	}

	/**
	 * Публикация товара
	 * @param unknown_type $iProdId
	 */
	public function publicProduct($iProdId = 0) {
		/**
		 * Если идентификатор неверный, то выходим
		 */
		if($iProdId == 0) {
			return false;
		}
		// Загружаем информацию о товаре
		$this->oProduct->load($iProdId, true);
		/**
		 * Если товар не опубликован
		 */
		if($this->oProduct->getAttribute('published') == 0) {
			/**
			 * Устанавливаем значение опубликованности
			 */
			$this->oProduct->setAttribute('published',1);
			/**
			 * Задаем дату модификации
			 */
			$this->oProduct->setAttribute('date_edit',date('Y-m-d G:i:s'));
			/**
			 * Сохраняем результат
			 */
			$this->oProduct->save();
		}
	}
	
	/**
	 * Отмена публикация товара
	 * @param unknown_type $iProdId
	 */
	public function unpublicProduct($iProdId = 0) {
		/**
		 * Если идентификатор неверный, то выходим
		 */
		if($iProdId == 0) {
			return false;
		}
		// Загружаем информацию о товаре
		$this->oProduct->load($iProdId, true);
		/**
		 * Если товар опубликован
		 */
		if($this->oProduct->getAttribute('published') == 1) {
			/**
			 * Снимаем значение опубликованности
			 */
			$this->oProduct->setAttribute('published',0);
			/**
			 * Задаем дату модификации
			 */
			$this->oProduct->setAttribute('date_edit',date('Y-m-d G:i:s'));
			/**
			 * Сохраняем результат
			 */
			$this->oProduct->save();
		}
	}
	
	/**
	 * Удаление товара в корзину
	 * @param $iProdId
	 */
	public function delProduct($iProdId = 0) {
		/**
		 * Если идентификатор неверный, то выходим
		 */
		if($iProdId == 0) {
			return false;
		}
		// Загружаем информацию о товаре
		$this->oProduct->load($iProdId, true);
		/**
		 * Если товар не удален
		 */
		if($this->oProduct->getAttribute('deleted') == 0) {
			/**
			 * Помечаем на удаление
			 */
			$this->oProduct->setAttribute('deleted',1);
			/**
			 * Задаем дату модификации
			 */
			$this->oProduct->setAttribute('date_edit',date('Y-m-d G:i:s'));
			/**
			 * Сохраняем результат
			 */
			$this->oProduct->save();
		}
	}
	
	/**
	 * Восстановление товара из корзины
	 * @param $iProdId
	 */
	public function undelProduct($iProdId) {
		/**
		 * Если идентификатор неверный, то выходим
		 */
		if($iProdId == 0) {
			return false;
		}
		// Загружаем информацию о товаре
		$this->oProduct->load($iProdId, true);
		/**
		 * Если товар удален
		 */
		if($this->oProduct->getAttribute('deleted') == 1) {
			/**
			 * Убираем пометку на удаление
			 */
			$this->oProduct->setAttribute('deleted',0);
			/**
			 * Задаем дату модификации
			 */
			$this->oProduct->setAttribute('date_edit',date('Y-m-d G:i:s'));
			/**
			 * Сохраняем результат
			 */
			$this->oProduct->save();
		}
	}
	
	/**
	 * Обработка полученной информации и сохранение
	 */
	public function saveProduct() {
		global $modx;
		/**
		 * Делаем проверку значений и устанавливаем для текущего товара
		 */
		if($this->checkData()) {
			/**
			 * Загружаем старые данные товара
			 */
			if(!$this->bIsNewProduct) {
				$this->oOldProduct->load($this->oProduct->getAttribute('id'), true);
			}
			/**
			 * Проверка прошла успешно и объект содержит все нужные данные. Просто сохраняем их.
			 */
			$this->oProduct->save();
			/**
			 * Если товар новый, то нужно еще установить URL
			 * Делается это после сохранения, так как нам нужен идентификатор на случай, если псевдоним не установлен
			 */
			if($this->bIsNewProduct) {
				$sAlias = $this->oProduct->getAttribute('alias');
				if(!$sAlias) {
					$sAlias = $this->oProduct->getAttribute('id');
				}
				/**
				 * Формируем URL с учетов части из категории
				 */
				$sUrl = $this->oCategory->getAttribute('url') . '/' . $sAlias;
				$this->oProduct->setAttribute('url',$sUrl);
			} else {
				/**
				 * А если старая, то необходимо добавить дату редактирования
				 */
				$this->oProduct->setAttribute('date_edit',date('Y-m-d G:i:s'));
			}
			/**
			 * Снова сохраняем.
			 */
			$this->oProduct->save();
			/**
			 * Делаем обобщение параметров
			 */
			SBAttributeCollection::attributeProductGeneralization($this->oProduct, $this->oCategory, $this->oProduct->getExtendAttributes(), $this->oOldProduct->getExtendAttributes());
			/**
			 * Сохраняем раздел
			 */
			$this->oCategory->save();
			return true;
		} else {
			/**
			 * Что-то при проверке пошло не так, поэтому снова выводим форму
			 */
			$this->editProduct();
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
		 * Установка идентификатора
		 */
		if(intval($_POST['prodid']) > 0) {
			$this->oProduct->setAttribute('id',intval($_POST['prodid']));
			/**
			 * Указываем флаг, что товар не новый, а редактируется
			 */
			$this->bIsNewProduct = false;
		} else {
			/**
			 * Товар новый, нужно установить флаг
			 */
			$this->bIsNewProduct = true;
		}
		/**
		 * Установка идентификатора категории
		 */
		$iCategoryId = intval($_POST['catid']);
		if($iCategoryId > 0) {
			/**
			 * Устанавливаем идентификатор категории
			 */
			$this->oProduct->setAttribute('category',$iCategoryId);
			/**
			 * Загружаем информацию о категории
			 */
			$oCategory = new SBCategory();
			$oCategory->load($iCategoryId,true);
			/**
			 * Добавляем информацию о категории для текущего товара
			 */
			$this->oCategory = $oCategory;
		} else {
			$this->sError = $modx->sbshop->lang['product_error_category'];
			$bError = true;
		}
		/**
		 * Проверяем псевдоним. Он должен быть стандартным.
		 */
		if($_POST['alias'] == '' || preg_match('/^[\w\-\_]+$/i',$_POST['alias'])) {
			$this->oProduct->setAttribute('alias',$_POST['alias']);
			/**
			 * Для дальнейшей установки URL выделим переменную
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
				$this->oProduct->setAttribute('alias',$_POST['alias']);
				$sAlias = $_POST['alias'];
			}
			$this->oProduct->setAttribute('alias',$sAlias);
		} else {
			$this->sError = $modx->sbshop->lang['product_error_alias'];
			$bError = true;
		}
		/**
		 * Устанавливаем URL товара с учетом URL категории.
		 * Для нового товара здесь не получится установить URL, так как идентификатор не известен
		 */
		if(!$this->bIsNewProduct) {
			/**
			 * Это не новый товар, можно смело формировать URL
			 */
			$sUrl = $oCategory->getAttribute('url') . '/' . $sAlias;
			/**
			 * Устанавливаем параметр URL
			 */
			$this->oProduct->setAttribute('url',$sUrl);
		}
		/**
		 * Проверяем заголовок. Он должен быть.
		 */
		if(strlen($modx->db->escape($_POST['title'])) > 0) {
			$sTitle = $modx->db->escape($_POST['title']);
			$this->oProduct->setAttribute('title',$sTitle);
		} else {
			$this->sError = $modx->sbshop->lang['product_error_title'];
			$bError = true;
		}
		/**
		 * Устанавливаем расширенный заголовок
		 */
		$this->oProduct->setAttribute('longtitle',$modx->db->escape($_POST['longtitle']));
		/**
		 * Проверяем артикул
		 */
		if(strlen($modx->db->escape($_POST['sku'])) > 0) {
			$this->oProduct->setAttribute('sku',$modx->db->escape($_POST['sku']));
		}
		/**
		 * Устанавливаем цену, заменяя предварительно запятую на точку
		 */
		$sPrice = $_POST['price'];
		$sPrice = str_replace(',', '.', $sPrice);
		$this->oProduct->setAttribute('price',floatval($sPrice));
		/**
		 * Устанавливаем цену, заменяя предварительно запятую на точку
		 */
		$sPriceAdd = $_POST['price_add'];
		$sPriceAdd = str_replace(',', '.', $sPriceAdd);
		$this->oProduct->setAttribute('price_add',$modx->db->escape(trim($sPriceAdd)));
		/**
		 * Товар опубликован?
		 */
		if($_POST['published'] == 1) {
			$this->oProduct->setAttribute('published',1);
		} else {
			$this->oProduct->setAttribute('published',0);
		}
		/**
		 * Есть в наличии?
		 */
		if($_POST['existence'] == 1) {
			$this->oProduct->setAttribute('existence',1);
		} else {
			$this->oProduct->setAttribute('existence',0);
		}
		/**
		 * Установка модели
		 */
		$this->oProduct->setAttribute('model',$_POST['model']);
		/**
		 * Установка производителя
		 */
		$this->oProduct->setAttribute('vendor',$_POST['vendor']);
		/**
		 * Устанавливаем краткое описание
		 */
		$this->oProduct->setAttribute('introtext',$_POST['introtext']);
		/**
		 * Устанавливаем расширенное описание
		 */
		$this->oProduct->setAttribute('description',$_POST['description']);
		/**
		 * Разбираем параметры
		 */
		$aAttributes = array();
		if($_POST['attribute_name']) {
			/**
			 * Разбираем каждый параметр
			 */
			$cntAttributes = count($_POST['attribute_name']);
			for($i=0;$i<$cntAttributes;$i++) {
				/**
				 * Если название параметра не пусто
				 */
				if($_POST['attribute_name'][$i] !== '') {
					if($_POST['attribute_type'][$i] == 'p') {
						$sType = 'p';
					} elseif ($_POST['attribute_type'][$i] == 'h') {
						$sType = 'h';
					} else {
						$sType = 'n';
					}
					$aAttribute = array(
						'title' => $_POST['attribute_name'][$i],
						'value' => $_POST['attribute_value'][$i],
						'measure' => $_POST['attribute_measure'][$i],
						'type' => $sType,
					);
					$aAttributes[$_POST['attribute_name'][$i]] = $aAttribute;
				}
			}
		}
		/**
		 * Устанавливаем дополнительные параметры
		 */
		$this->oProduct->setExtendAttributes($aAttributes);
		/**
		 * Актуализируем коллекцию параметров. Передаем только названия
		 */
		SBAttributeCollection::setAttributeCollection(array_keys($this->oProduct->getExtendAttributes()));
		/**
		 * Если есть изображения
		 */
		if ($_POST['img']) {
			/**
			 * Обрабатываем каждый полученный файл
			 */
			foreach ($_POST['img'] as $sImageId) {
				/**
				 * Добавляем изображение в файл
				 */
				$this->oProduct->addImage(trim($sImageId));
			}
		}
		/**
		 * Если установлены опции
		 */
		if($_POST['option_id'] != '') {
			/**
			 * Объект управления опциями
			 */
			$oOptions = new SBOptionList();
			/**
			 * Обрабатываем каждую полученную запись
			 */
			$cntOptions = count($_POST['option_id']);
			for($i=0;$i<$cntOptions;$i++) {
				/**
				 * Идентификатор опции для дальнейшего разбора
				 */
				$iOptionId = intval($_POST['option_id'][$i]);
				/**
				 * Не состоит в комплектации
				 */
				$iNotbundle = isset($_POST['option_notbundle'][$iOptionId]) ? true : false;
				/**
				 * Опция скрыта
				 */
				$iHidden = isset($_POST['option_hidden'][$iOptionId]) ? true : false;
				/**
				 * Создаем объект подсказки
				 */
				$oTip = new SBTip();
				/**
				 * Если в содержимом заметки первый символ ">"
				 */
				if(substr($_POST['option_tip_description'][$iOptionId], 0, 1) !== '>') {
					/**
					 * Данные заметки
					 */
					$aTipData = array(
						'id' => intval($_POST['option_tip_id'][$iOptionId]),
						'title' => $_POST['option_tip_title'][$iOptionId],
						'description' => $_POST['option_tip_description'][$iOptionId]
					);
					/**
					 * Устанавливаем данные
					 */
					$oTip->setAttributes($aTipData);
					/**
					 * Сохраняем заметку
					 */
					$oTip->save();
				} else {
					/**
					 * Дальше идет идентификатор
					 */
					$iTipId = intval(substr($_POST['option_tip_description'][$iOptionId], 1));
					/**
					 * Устанавливаем указанный идентификатор. Мы верим, что менеджер вменяемый и ничего проверять не будем.
					 */
					$oTip->setAttribute('id', $iTipId);
				}
				/**
				 * Данные опции
				 */
				$aOptionData = array(
					'title' => $_POST['option_name'][$i],
					'longtitle' => $_POST['option_longname'][$iOptionId],
					'notbundle' => $iNotbundle,
					'hidden' => $iHidden,
					'class' => $_POST['option_class'][$iOptionId],
					'image' => $_POST['option_image'][$iOptionId],
					/**
					 * Записываем в опцию идентификатор подсказки
					 */
					'tip' => $oTip->getAttribute('id'),
				);
				/**
				 * Массив значений
				 */
				$aValues = array();
				/**
				 * Если есть значения
				 */
				if($_POST['option_value_ids'] != '') {
					/**
					 * Обрабатываем каждую запись значения для каждой опции
					 */
					$cntValues = count($_POST['option_value_ids'][$iOptionId]);
					for($k=0;$k<$cntValues;$k++) {
						/**
						 * Идентификатор
						 */
						$iValueId = intval($_POST['option_value_ids'][$iOptionId][$k]);
						/**
						 * Подготавливаем значения
						 */
						if($_POST['option_values_title'][$iOptionId][$k] != '') {
							$aValues[] = array(
								'title' => $_POST['option_values_title'][$iOptionId][$k],
								'price_add' => $_POST['option_values_add'][$iOptionId][$k],
								'value' => $_POST['option_values_value'][$iOptionId][$k],
								'class' => $_POST['option_values_class'][$iOptionId][$k],
								'image' => $_POST['option_values_image'][$iOptionId][$k],
							);
						}
					}
				}
				/**
				 * Добавляем опцию со значениями
				 */
				$oOptions->add($aOptionData,$aValues);
			}
			/**
			 * Делаем обобщение значений
			 */
			$oOptions->optionGeneralization();
			/**
			 * Сериализуем
			 */
			$sOptions = $oOptions->serializeOptions();
			/**
			 * Устанавливаем строку для товара
			 */
			$this->oProduct->setAttribute('options',$sOptions);
		}
		/**
		 * Если есть информация о базовой комплектации
		 */
		if($_POST['bundle_base_settings']) {
			/**
			 * Добавляем информацию о базовой комплектации
			 */
			$this->oProduct->setAttribute('base_bundle', $modx->db->escape($_POST['bundle_base_settings']));
		}
		/**
		 * Установка комплектаций
		 */
		if(count($_POST['bundle_name'] > 0)) {
			/**
			 * Обрабатываем каждую запись
			 */
			$cntBundle = count($_POST['bundle_name']);
			for ($i=0; $i<$cntBundle; $i++) {
				$this->oProduct->addBundle($modx->db->escape($_POST['bundle_name'][$i]), $_POST['bundle_settings'][$i], $_POST['bundle_price'][$i], $_POST['bundle_description'][$i], false, $modx->db->escape($_POST['bundle_price_add'][$i]));
			}
			/**
			 * Если включена индивидуальная комплектация
			 */
			if($_POST['bundle_personal']) {
				/**
				 * Добавляем персональную комплектацию к основному списку
				 */
				$this->oProduct->addBundle('personal', '0:0', false, '', 'personal');
			}
		}

		return !$bError;
	}
}

?>