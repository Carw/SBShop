<?php

/**
 * @author Mukharev Maxim
 * @version 0.1a
 * 
 * @desription
 * 
 * Электронный магазин для MODx
 *
 */

class SBShop {
	
	/**
	 * XXX лучше всего закрыть все переменные и сделать доступ через соответствующие методы
	 */
	public $config = array(); // массив с настройками
	public $lang = array(); // массив языковых данных
	public $iBaseDocId; // идентификатор документа в котором располагается каталог
	public $sBaseUrl; // Это основной адрес для документа с магазином
	protected $bError404; // Флаг ошибки 404
	protected $aModes; // массив со списком рабочих режимов
	protected $aUrl; // массив с разобранным URL
	protected $aUrlParams; // массив с набором выделенных параметров в URL
	protected $aUrlQueries; // массив с набором выделенных параметров в URL
	public $bInsideCategory; // Флаг указывающий внутри категории мы или на другой странице
	public $oGeneralCategory; // Это основная категория
	public $oGeneralProduct; // Это основной товар
	public $oOrder; // Заказ
	public $oCustomer; // Клиент
	public $bManager; // Режим менеджера


	/**
	 * Конструктор
	 */
	public function __construct($iDocStart = false) {
		global $modx;
		/**
		 * Подключаем необходимые файлы
		 */
		// конфиги
		include MODX_BASE_PATH . 'assets/extends/sbshop/config/config.php';
		/**
		 * Записываем настройки
		 */
		$this->config = $config;
		// Управление параметрами
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/attributelist.php';
		// Управление товарами
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/product.php';
		// Управление категориями
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/category.php';
		// Управление фильтрами
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/filterlist.php';
		// Управление коллекцией параметров
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/attributecollection.php';
		// Управление набором опций
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/optionlist.php';
		// Управление деревом категорий
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/cattree.php';
		// Управление "хлебными крошками"
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/breadcrumbs.php';
		// Управление списком товаров
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/productlist.php';
		// Управление заказами
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/order.php';
		// Управление списком заказов
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/orderlist.php';
		// Управление данными клиента
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/customer.php';
		// Управление данными клиента
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/image.php';
		// Управление списками с отметкой времени
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/timelist.php';
		// Управление списками комплектаций
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/bundlelist.php';
		// Управление подсказками
		include MODX_BASE_PATH . 'assets/extends/sbshop/core/tip.php';
		/**
		 * Подключаем языковой файл
		 */
		$langname = strtolower($modx->config['manager_language']);
		$langpath = MODX_BASE_PATH . 'assets/extends/sbshop/lang/' . $langname . '.inc.php';
		/**
		 * Если указанный языковой файл существует
		 */
		if(is_file($langpath)) {
			$lang = include $langpath;
		} else {
			$lang = include MODX_BASE_PATH . 'assets/extends/sbshop/lang/russian-utf8.inc.php';
		}
		$this->lang = $lang;
		/**
		 * Если это не админка
		 */
		if(!$modx->insideManager()) {
			/**
			 * Экземпляр основной категории
			 */
			$this->oGeneralCategory = new SBCategory();
			/**
			 * Экземпляр основного товара
			 */
			$this->oGeneralProduct = new SBProduct();
			/**
			 * Экземпляр заказа
			 */
			$this->oOrder = new SBOrder();
			/**
			 * Экземпляр данных клиента
			 */
			$this->oCustomer = new SBCustomer($this->oOrder->getAttribute('user'));
			/**
			 * Флаг ошибки по умолчанию включаем.
			 */
			$this->bError404 = true;
			/**
			 * Инициализируем массив Url
			 */
			$this->aUrl = array();
			/**
			 * Массив параметров Url
			 */
			$this->aUrlParams = array();
			/**
			 * Инициализируем флаг, указывая по умолчанию, что мы не в категории
			 */
			$this->bInsideCategory = false;
			/**
			 * Инициализируем массив режимов
			 */
			$this->aModes = array();
			/**
			 * Записываем базовый идентификатор документа
			 */
			$this->iBaseDocId = $iDocStart;
			/**
			 * Устанавливаем режим менеджера
			 */
			$this->bManager = false;
			/**
			 * Делаем анализ URL
			 */
			$this->analiseUrl();
		}
	}
	
	/**
	 * Инициализация нужных настроек
	 */
	public function initialise() {
		/**
		 * Делаем роутинг
		 */
		$this->route();
		/**
		 * Загрузка корзины
		 */
		$this->oOrder->load();
	}

	/**
	 * Роутер. Определение режима работы и действий
	 */
	public function route() {
		global $modx;
		/**
		 * Определяем $sURL
		 */
		$sUrl = $this->getInnerUrl();
		/**
		 * Если мы внутри каталога
		 */
		if($this->bInsideCategory) {
			/**
			 * Если Url пустой
			 */
			if($sUrl == '') {
				/**
				 * Добавляем режим главной страницы и каталога
				 */
				$this->addModes(array('main','categories'));
				/**
				 * Флаг 404 выключаем
				 */
				$this->bError404 = false;
			} elseif($this->oGeneralCategory->searchCategoryByURL($sUrl)) {
				/**
				 * Директория найдена по адресу. Добавляем режим категории
				 */
				$this->addModes('categories');
				/**
				 * Флаг 404 выключаем
				 */
				$this->bError404 = false;
			} elseif($this->oGeneralProduct->searchProductByURL($sUrl)) {
				/**
				 * Загружаем основную категорию
				 */
				$this->oGeneralCategory->load($this->oGeneralProduct->getAttribute('category'));
				/**
				 * Товар обнаружен по адресу. Добавляем режим товара
				 */
				$this->addModes(array('product','categories'));
				/**
				 * Флаг 404 выключаем
				 */
				$this->bError404 = false;
			}
		}
	}
	
	/**
	 * Загрузка расширений ядра
	 * XXX Нужно довести до ума. пока не работает
	 */
	/*protected function loadCoreExtensions() {
		$sDir = MODX_BASE_PATH . 'assets/extends/sbshop/core';
		$hDir = opendir($sDir);
		while ($sFile = readdir($hDir)) {
			$sFilePathFull=$sDir.'/'.$sFile;
			if ($sFile !='core.php' and $sFile !='.' and $sFile !='..' and is_file($sFilePathFull)) {
				include $sFilePathFull;
			}
		}
		closedir($hDir);
	}*/
	
	/**
	 * Установка языковых данных
	 * @param unknown_type $aLang
	 */
	public function setLang($aLang) {
		$this->lang = $aLang;
	}
	
	/**
	 * Установка основной категории по URL
	 * @param $sURL
	 */
	public function setGeneralCategory($sUrl) {
		global $modx;
		/**
		 * Ищем категорию по URL
		 */
		return $this->oGeneralCategory->searchCategoryByURL($sUrl);
	}
	
	/**
	 * Установка основного товара
	 * @global <type> $modx
	 * @param <type> $sUrl
	 * @return <type>
	 */
	public function setGeneralProduct($sUrl) {
		global $modx;
		/**
		 * Ищем товар
		 */
		return $this->oGeneralProduct->searchProductByURL($sUrl);
	}

	/**
	 * Добавить в список режимов дополнительные режимы
	 * @param array $aModes
	 */
	public function addModes($aModes = array()) {
		/**
		 * Если передан не массив
		 */
		if(!is_array($aModes)) {
			/**
			 * Делаем массив из одного элемента
			 */
			$aModes = array($aModes);
		}
		/**
		 * Объединяем текущий массив режимов с дополнительным
		 */
		$this->aModes = array_merge($this->aModes,$aModes);
	}

	/**
	 * Набор общесистемных функций, необходимых для работы, которых нехватает в MODx
	 */
	
	/**
	 * Обработка URL для выделения необходимой информации
	 */
	public function analiseUrl() {
		global $modx;
		/**
		 * Получаем базовый URL каталога
		 */
		$sBaseUrl = str_replace($this->config['url_suffix'],'',$modx->makeUrl($this->iBaseDocId));
		/**
		 * Записываем базовый URL со слешем на конце
		 */
		$this->sBaseUrl = $sBaseUrl . '/';
		/**
		 * Текущий URL со всеми параметрами
		 */
		$aCurrentUrl = parse_url($_SERVER['REQUEST_URI']);
		/**
		 * Параметры запроса
		 */
		parse_str($aCurrentUrl['query'],$this->aUrlQueries);
		/**
		 * Текущий адрес
		 */
		$sCurrentUrl = str_replace($this->config['url_suffix'],'',$aCurrentUrl['path']);
		/**
		 * Системные значения требующие включения режима
		 */
		$aSetRoute = $this->config['route_pages'];
		/**
		 * Набор правил обработки параметров URL
		 */
		$aSetParams = $this->config['snippet_params'];
		/**
		 * Если текущий URL включаем базовый
		 */
		if(strpos($sCurrentUrl,$sBaseUrl) === 0) {
			/**
			 * Включаем флаг "мы в категории"
			 */
			$this->bInsideCategory = true;
			/**
			 * Удаляем лишнюю часть пути
			 */
			/**
			 * Если это корневая категория
			 */
			if($sCurrentUrl == $sBaseUrl) {
				/**
				 * Добавляем в Url значение 0
				 * XXX не очень хороший вариант + еще нюанс с пустым первым элементом
				 */
				$aUrl = array();
			} else {
				/**
				 * Убираем стартовую часть адреса, чтобы она не обрабатывалась далее
				 */
				$sCurrentUrl = str_replace($sBaseUrl,'',$sCurrentUrl);
				/**
				 * Разбираем URL на параметры
				 */
				$aUrl = explode('/',$sCurrentUrl);
			}
			/**
			 * Конечный массив для обработанного URL
			 */
			$aOutUrl = array();
			/**
			 * Конечный массив с параметрами
			 */
			$aOutParams = array();
			/**
			 * Обрабатываем параметры
			 */
			foreach ($aUrl as $sKey => $sUrl) {
				/**
				 * Если значение не пусто
				 */
				if($sUrl != '') {
					/**
					 * если это первый элемент
					 * XXX Нужно переделать.
					 * Здесь сложность в том, что URL приходит с начальным слешем, а значит первый элемент пустой.
					 */
					if($sKey == 1) {
						/**
						 * Ищем в массиве определенных режимов
						 */
						if(array_search($sUrl,$aSetRoute) !== false) {
							/**
							 * Найденный режим добавляем в список используемых режимов
							 */
							$this->aModes[] = $sUrl;
							/**
							 * Флаг ошибки выключаем
							 */
							$this->bError404 = false;
						}
					}
					/**
					 * Флаг "параметр распознан". По умолчанию false
					 */
					$bIsParam = false;
					/**
					 * Обрабатываем значение для идентификации заданных параметров
					 */
					foreach ($aSetParams as $sParamKey => $sParamReg) {
						/**
						 * Если текущий параметр подходит текущему шаблону
						 */
						if(preg_match($sParamReg,$sUrl,$aRaw)) {
							/**
							 * Записываем определенное значение в массив параметров
							 */
							$aOutParams[$sParamKey] = array_slice($aRaw,1);
							/**
							 * Параметр найден. Ставим флаг.
							 */
							$bIsParam = true;
						}
					}
					/**
					 * Если параметра в значении не нашлось, то добавляем его в URL
					 */
					if(!$bIsParam) {
						$aOutUrl[] = $sUrl;
					}
				}
			}
			/**
			 * Записываем массив URL
			 */
			$this->aUrl = $aOutUrl;
			/**
			 * Записываем массив параметров
			 */
			$this->aUrlParams = $aOutParams;
		} else {
			/**
			 * Мы находимся не внутри категории, поэтому флаг ошибки надо выключить
			 */
			$this->bError404 = false;
		}
	}
	
	/**
	 * Получение внутренней части Url каталога (категории/товара)
	 * @return string
	 */
	public function getInnerUrl() {
		if(count($this->aUrl) > 0) {
			return implode('/',$this->aUrl);
		} else {
			return '';
		}
	}

	/**
	 * Получение заданной части URL в каталоге. Значения не включают базовый URL
	 * @param int $iNum
	 */
	public function getEvent($iNum) {
		if(isset ($this->aUrl[$iNum])) {
			return $this->aUrl[$iNum];
		} else {
			return false;
		}
	}

	/**
	 * Получение всей части URL, исключая 0 параметр, который указывает на режим.
	 * @param int $iNum
	 */
	public function getEvents() {
		if(count($this->aUrl) > 1) {
			return array_slice($this->aUrl,1);
		} else {
			return false;
		}
	}

	/**
	 * Получение базового URL магазина
	 */
	public function getBaseUrl() {
		return $this->sBaseUrl;
	}

	/**
	 * Получение полного URL для текущей страницы
	 * @return <type>
	 */
	public function getFullUrl() {
		if(count($this->aUrl) > 0) {
			$sUrl = implode('/',$this->aUrl);
		} else {
			$sUrl = '';
		}
		$sUrl = $this->sBaseUrl . $sUrl . $this->config['url_suffix'];
		$this->config['link_action'] = $sUrl;
		return $sUrl;  
	}

	public function getModes() {
		return $this->aModes;
	}

	/**
	 * Получение параметров запроса
	 */
	public function getQueries($aName = false) {
		if($aName) {
			return $this->aUrlQueries[$aName];
		} else {
			return $this->aUrlQueries;
		}
	}

	/**
	 * Получение статуса флага "внутри каталога"
	 */
	public function insideCategory() {
		return $this->bInsideCategory;
	}

	public function baseRedirect() {
		if(!$this->bError404 and $this->bInsideCategory) {
			return true;
		}
		return false;
	}

	/**
	 * Установка базового шаблона
	 */
	public function setBaseTemplate() {
		global $modx;
		/**
		 * Ищем соответствующий режиму шаблон в конфиге
		 */
		if(isset($this->config['template_modes'][$this->aModes[0]])) {
			/**
			 * Загружаем шаблон из базы
			 */
			$rs = $modx->db->select('content',$modx->getFullTableName('site_templates'),'id = ' . $this->config['template_modes'][$this->aModes[0]]);
			$aRaw = $modx->db->makeArray($rs);
			if($aRaw) {
				/**
				 * Устанавливаем шаблон
				 */
				$modx->documentContent = $aRaw[0]['content'];
			}
		}
	}
	
	/**
	 * Перевод массива с данными в набор плейсхолдеров с заданным префиксом
	 * @param $aData Массив с данными
	 * @param $sPrefix Префикс для плейсхолдеров
	 * @param $sSuffix Суфикс для плейсхолдеров
	 */
	public function arrayToPlaceholders($aData = false, $sPrefix = 'sb.',$sSuffix = '') {
		global $modx;
		/**
		 * Если содержимое не массив, то на выход
		 */
		if(!is_array($aData)) {
			return array();
		}
		/**
		 * Массив для плейсхолдеров
		 */
		$phData = array();
		/**
		 * Обрабатываем каждый элемент
		 */
		foreach ($aData as $sKey => $sVal) {
			/**
			 * Если обработка происходит не в админке
			 */
			if(!$modx->insideManager()) {
				/**
				 * Преобразование некоторых типов параметров для правильного вывода в шаблон
				 */
				switch ($sKey) {
					case 'url':
						/**
						 * Если адрес совпадает с базовым
						 */
						if($sVal == $this->sBaseUrl) {
							/**
							 * Мы слегка его корректируем
							 */
							$sVal = $modx->makeUrl($modx->documentIdentifier);
						} else {
							/**
							 * Добавляем стартовый адрес и суффикс
							 */
							if(mb_substr($this->sBaseUrl, 0, 1) == '/') {
								$sVal = '[(site_url)]' . mb_substr($this->sBaseUrl, 1) . $sVal . $this->config['url_suffix'];
							} else {
								$sVal = MODX_BASE_URL . $this->sBaseUrl . $sVal . $this->config['url_suffix'];
							}
						}
					break;
					case 'price':
						$sVal = intval($sVal);
					break;
				}
			}
			/**
			 * Добавляем значение к массиву
			 */
			$phData['[+' . $sPrefix . $sKey . $sSuffix . '+]'] = $sVal;
		}
		return $phData;
	}

	/**
	 * Перевод трехмерного массива в набор плейсхолдеров
	 */
	public function multiarrayToPlaceholders($aData = false, $sType = 'num', $sPrefix = 'sb.') {
		/**
		 * Результирующий массив плейсхолдеров
		 */
		$phData = array();
		/**
		 * Если нет содержимого в массиве
		 */
		if(!$aData) {
			/**
			 * Отдаем пустой массив
			 */
			return $phData;
		}
		/**
		 * Обрабатываем каждый элемент массива
		 */
		$i = 0;
		foreach ($aData as $sKey => $aVal) {
			if($sType == 'key') {
				$phData = array_merge($phData,$this->arrayToPlaceholders($aVal, $sPrefix, '.' . $sKey));
			} elseif($sType == 'num') {
				$i++;
				if(is_array($aVal)) {
					$phData = array_merge($phData,$this->arrayToPlaceholders($aVal, $sPrefix, '.' . $i));
				} else {
					$aVal = array($i => $aVal);
					$phData = array_merge($phData,$this->arrayToPlaceholders($aVal, $sPrefix));
				}
			}
		}
		return $phData;
	}

	/**
	 * Получить шаблон для модуля по названию режима
	 * @param unknown_type $sMod
	 */
	public function getModuleTemplate($sMode) {
		$sPathTpl = MODX_BASE_PATH . 'assets/extends/sbshop/modules/templates/' . $sMode . '.tpl';
		if(is_file($sPathTpl)) {
			/**
			 * Получаем содержимое файла с шаблонами
			 */
			$sTemplates = file_get_contents($sPathTpl);
			/**
			 * Разбираем файл с шаблонами
			 */
			return $this->explodeTemplates($sTemplates);
		} else {
			return array();
		}
	}
	
	/**
	 * Получить шаблон для сниппета по названию режима
	 * @param string $sMode
	 */
	public function getSnippetTemplate($sMode) {
		$sPathTpl = MODX_BASE_PATH . 'assets/extends/sbshop/snippets/templates/' . $sMode . '.tpl';
		if(is_file($sPathTpl)) {
			/**
			 * Получаем содержимое файла с шаблонами
			 */
			$sTemplates = file_get_contents($sPathTpl);
			/**
			 * Разбираем файл с шаблонами
			 */
			return $this->explodeTemplates($sTemplates);
		} else {
			return array();
		}
	}

	/**
	 * Разбор файла с шаблонами
	 * @param <type> $sTemplates
	 */
	public function explodeTemplates($sTemplates) {
		/**
		 * Конечный массив с шаблонами
		 */
		$aTemplates = array();
		/**
		 * Ищем вхождения названий шаблонов
		 */
		$aTemplateNames = array();
		preg_match_all('/<!--#(.*)#-->/ui', $sTemplates, $aTemplateNames);
		/**
		 * Обрабатываем каждое название
		 */
		$cnt = count($aTemplateNames[1]);
		for ($i=0;$i<$cnt;$i++) {
			/**
			 * Разбиваем название используя ":" как разделитель. Все, что после ":" - комментарий
			 */
			list($sName,$sComment) = explode(':', $aTemplateNames[1][$i]);
			/**
			 * Очищаем название от лишних пробелов
			 */
			$sName = trim($sName);
			/**
			 * Определяем позицию названия шаблона
			 */
			$iNamePos = strpos($sTemplates,$aTemplateNames[0][$i]) + strlen($aTemplateNames[0][$i]);
			/**
			 * Если это последний шаблон
			 */
			if($i == ($cnt - 1)) {
				/**
				 * Добавляем шаблон в массив
				 */
				$aTemplates[$sName] = trim(substr($sTemplates, $iNamePos));
			} else {
				/**
				 * Определяем размер текущего шаблона
				 */
				$iTemplateLen = strpos($sTemplates,$aTemplateNames[0][$i+1]) - $iNamePos;
				/**
				 * Добавляем шаблон в массив
				 */
				$aTemplates[$sName] = trim(substr($sTemplates, $iNamePos, $iTemplateLen));
			}
		}
		/**
		 * Возвращаем результат
		 */
		return $aTemplates;
	}

	/**
	 * Функция вывода сообщения об успешном выполнении какого-то действия с последующим редиректом
	 */
	public function alertWait($sModuleLink) {
		global $_lang;
		
		echo '<h1>' . $_lang['cleaningup'] . '</h1>
		
		<div class="sectionBody">
			<p>' . $_lang['actioncomplete'] . '</p>
			<script type="text/javascript">
				function goHome() {
					document.location.href="' . $sModuleLink . '";
				}
				x=window.setTimeout("goHome()",2000);
			</script>
		</div>';
	}
	
	/**
	 * Получить реальный IP юзера
	 */
	public function GetIp() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	/**
	 * Проверка информации
	 */
	public function check($sType,$sVal) {
		$rs = false;
		switch ($sType) {
			case 'email':
				$rs = preg_match('/^[\.\-_A-Za-z0-9]+?@[\.\-A-Za-z0-9]+?\.[A-Za-z0-9]{2,6}$/', $sVal);
				break;
		}
		return $rs;
	}
}

?>