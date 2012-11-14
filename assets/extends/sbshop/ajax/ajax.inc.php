<?php
/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 *
 * Класс управляющий доступными методами Ajax
 */

class SBAjax {

	protected $sMethod;
	protected $aParams;
	protected $aResult;

	public function  __construct($sMethod,$aParams) {
		/**
		 * Записываем метод
		 */
		$this->sMethod = $sMethod;
		/**
		 * Полученные параметры
		 */
		$this->aParams = $aParams;
		/**
		 * Собираем название метода
		 */
		$sMethodName = $sMethod . 'Ajax';
		/**
		 * Если такой метод есть в классе
		 */
		if(method_exists($this, $sMethodName)) {
			/**
			 * Вызываем
			 */
			$this->$sMethodName();
		}
	}

	protected function tipAjax() {
		/**
		 * Идентификатор подсказки
		 */
		$iTipId = intval($this->aParams['tid']);
		
		if($iTipId) {
			/**
			 * Подключаем класс для работы с подсказками
			 */
			include_once MODX_BASE_PATH . 'assets/extends/sbshop/core/tip.php';
			/**
			 * Создаем объект
			 */
			$oTip = new SBTip();
			/**
			 * Загружаем подсказку
			 */
			$oTip->load($iTipId);
			/**
			 * Устанавливаем данные
			 */
			$this->aResult['title'] = htmlspecialchars_decode($oTip->getAttribute('title'), ENT_QUOTES);
			$this->aResult['description'] = htmlspecialchars_decode($oTip->getAttribute('description'), ENT_QUOTES);
		}
	}

	/**
	 * Загрузка изображений на сервер через Ajax
	 * @return bool
	 */
	protected function imgUplAjax() {
		global $modx;
		/**
		 * Идентификатор товара
		 */
		$iProductId = intval($this->aParams['prodid']);
		/**
		 * Конфиг ресайза миниатюры для админки
		 */
		$aResizePreviewConfig = array(
			'mode' => 'xy',
			'w' => 96,
			'h' => 96,
			'quality' => 85,
			'key' => '-prv'
		);
		/**
		 * Если идентификатор товара не ноль
		 */
		if ($iProductId > 0) {
			/**
			 * Формируем путь для изображения
			 */
			$sBasePath = $modx->sbshop->config['image_base_dir'] . $iProductId . '/';
			$sBaseUrl = $modx->sbshop->config['image_base_url'] . $iProductId . '/';
			/**
			 * Определяем метод загрузки
			 */
			if (isset($_GET['qqfile'])) {
				/**
				 * Считываем данные с потока
				 */
				$sInput = fopen("php://input", "r");
				/**
				 * Создаем временный файл
				 */
		        $fTmpName = tempnam($sBasePath, 'tmp');
				$fTmpFile = fopen($fTmpName, "w");
				/**
				 * Копируем
				 */
		        $iRealSize = stream_copy_to_stream($sInput, $fTmpFile);
				/**
				 * Закрываем файл
				 */
		        fclose($sInput);
				/**
				 * На всякий случай сверяем размер
				 */
				if ($iRealSize != $_SERVER["CONTENT_LENGTH"]){
					/**
					 * Ошибка. Размер не совпадает!
					 */
					$this->aResult['error'] = 'Ошибка при загрузке файла.';
		        }
				/**
				 * Конфигурация для ресайза
				 */
				$aResizeConfig = $modx->sbshop->config['image_resizes'];
				/**
				 * Добавляем конфигурацию для привьюшки
				 */
				$aResizeConfig[] = $aResizePreviewConfig;
				/**
				 * Делаем ресайз
				 */
				$sImgs = SBImage::imageResize($fTmpName, $sBasePath, $aResizeConfig);
				/**
				 * Закрываем и удаляем временный файл
				 */
				fclose($fTmpFile);
				unlink($fTmpName);
			} elseif (isset($_FILES['qqfile'])) {
				/**
				 * Конфигурация для ресайза
				 */
				$aResizeConfig = $modx->sbshop->config['image_resizes'];
				/**
				 * Добавляем конфигурацию для привьюшки
				 */
				$aResizeConfig[] = $aResizePreviewConfig;
				/**
				 * Делаем ресайз
				 */
				$sImgs = SBImage::imageResize($_FILES['qqfile']['tmp_name'], $sBasePath, $aResizeConfig);
			} else {
				/**
				 * Ошибка. Файла нет!
				 */
				$this->aResult['error'] = 'Файла нет.';
				return false;
			}
			/**
			 * Загружаем шаблоны
			 */
			$aTemplates = $modx->sbshop->getModuleTemplate('prod');
			/**
			 * Устанавливаем результат
			 */
			$this->aResult['success'] = true;
			/**
			 * Название файла
			 */
			$this->aResult['id'] = $sImgs;
			$this->aResult['filename'] = $sBaseUrl . $sImgs . '-prv.jpg';
			/**
			 * Готовим плейсхолдеры
			 */
			$aRepl = array(
				'id' => $sImgs,
				'image' => $this->aResult['filename']
			);
			$aRepl = $modx->sbshop->arrayToPlaceholders($aRepl);
			/**
			 * Конечный результат
			 */
			$this->aResult['html'] = str_replace(array_keys($aRepl), array_values($aRepl), $aTemplates['image_row']);
		} else {
			/**
			 * Вероятно товар новый.
			 * @todo Сделать возможность загружать картинки для нового товара
			 */
		}
	}

	/**
	 * Удаление изображения
	 */
	protected function imgDelAjax() {
		global $modx;
		/**
		 * Идентификатор товара
		 */
		$iProductId = intval($this->aParams['prodid']);
		/**
		 * Идентификатор файла
		 */
		$sFileId = trim($this->aParams['imgid']);
		/**
		 * Путь до папки с изображениями товара
		 */
		$sBasePath = $modx->sbshop->config['image_base_dir'] . $iProductId . '/';
		/**
		 * Получаем конфигурации для ресайза
		 */
		$aResizeConfig = $modx->sbshop->config['image_resizes'];
		/**
		 * Добавляем конфигурацию для привьюшки. Нам достаточно ключа.
		 */
		$aResizeConfig[] = array('key' => '-prv');
		/**
		 * Ошибка в процессе работы
		 */
		$bError = false;
		/**
		 * Обрабатываем каждую конфигурацию
		 */
		foreach ($aResizeConfig as $aConfig) {
			$sFilePath = $sBasePath . $sFileId . $aConfig['key'] . '.jpg';
			/**
			 * Если файл существует
			 */
			if (is_file($sFilePath)) {
				/**
				 * Удаляем файл
				 */
				unlink($sFilePath);
			} else {
				$bError = true;
			}
		}
		/**
		 * Если нет ошибки
		 */
		if(!$bError) {
			/**
			 * Все отлично!
			 */
			$this->aResult['success'] = true;
		} else {
			/**
			 * Отправляем ошибку
			 */
			$this->aResult['error'] = $modx->sbshop->lang['product_image_delete_error'];
		}
	}

	/**
	 * Загрузка изображений на сервер через Ajax
	 * @return bool
	 */
	protected function fileUplAjax() {
		global $modx;
		/**
		 * Идентификатор товара
		 */
		$iProductId = intval($this->aParams['prodid']);
		/**
		 * Если идентификатор товара не ноль
		 */
		if ($iProductId > 0) {
			/**
			 * Формируем путь для изображения
			 */
			$sBasePath = $modx->sbshop->config['image_base_dir'] . $iProductId . '/';
			$sBaseUrl = $modx->sbshop->config['image_base_url'] . $iProductId . '/';
			/**
			 * Определяем метод загрузки
			 */
			if (isset($_GET['qqfile'])) {
				/**
				 * Выделяем разрешение
				 */
				$sFileExtension = mb_strtolower(substr($_SERVER['HTTP_X_FILE_NAME'], strrpos($_SERVER['HTTP_X_FILE_NAME'], '.') + 1));
				/**
				 * Если это разрешение доступно
				 */
				if(in_array($sFileExtension, $modx->sbshop->config['file_allowed_extensions'])) {
					/**
					 * Имя файла
					 */
					$sFile = mb_strtolower($_SERVER['HTTP_X_FILE_NAME']);
					/**
					 * Считываем данные с потока
					 */
					$sInput = fopen("php://input", "r");
					/**
					 * Путь к файлу
					 */
					$fTmpName = $sBasePath . $sFile;
					$fTmpFile = fopen($fTmpName, "w");
					/**
					 * Копируем
					 */
					$iRealSize = stream_copy_to_stream($sInput, $fTmpFile);
					/**
					 * Закрываем файл
					 */
					fclose($sInput);
					/**
					 * На всякий случай сверяем размер
					 */
					if ($iRealSize != $_SERVER["CONTENT_LENGTH"]){
						/**
						 * Ошибка. Размер не совпадает!
						 */
						$this->aResult['error'] = 'Ошибка при загрузке файла.';
						return false;
					}
					/**
					 * Закрываем и удаляем временный файл
					 */
					fclose($fTmpFile);
				} else {
					/**
					 * Расширение файла не находится в списке разрешенных
					 */
					$this->aResult['error'] = 'Файлы такого типа не разрешены для загрузки.';
					return false;
				}
			} elseif (isset($_FILES['qqfile'])) {
				/**
				 * Имя файла
				 */
				$sFile = mb_strtolower($_FILES['qqfile']['name']);
				/**
				 * Выделяем разрешение
				 */
				$sFileExtension = substr($sFile, strrpos($sFile, '.') + 1);
				/**
				 * Если это разрешение доступно
				 */
				if(in_array($sFileExtension, $modx->sbshop->config['file_allowed_extensions'])) {
					/**
					 * Путь к файлу
					 */
					$fTmpName = $sBasePath . $sFile;
					/**
					 * Переносим загруженный файл
					 */
					move_uploaded_file($_FILES['qqfile']['tmp_name'], $fTmpName);
				} else {
					/**
					 * Расширение файла не находится в списке разрешенных
					 */
					$this->aResult['error'] = 'Файлы такого типа не разрешены для загрузки.';
					return false;
				}

			} else {
				/**
				 * Ошибка. Файла нет!
				 */
				$this->aResult['error'] = 'Файла нет.';
				return false;
			}
			/**
			 * Загружаем шаблоны
			 */
			$aTemplates = $modx->sbshop->getModuleTemplate('prod');
			/**
			 * Устанавливаем результат
			 */
			$this->aResult['success'] = true;
			/**
			 * Название файла
			 */
			$this->aResult['id'] = $sFile;
			$this->aResult['filename'] = $sBaseUrl . $sFile;
			/**
			 * Готовим плейсхолдеры
			 */
			$aRepl = array(
				'id' => $sFile,
				'name' => $sFile,
				'file' => $this->aResult['filename'],
				'type' => $sFileExtension
			);
			$aRepl = $modx->sbshop->arrayToPlaceholders($aRepl);
			/**
			 * Конечный результат
			 */
			$this->aResult['html'] = str_replace(array_keys($aRepl), array_values($aRepl), $aTemplates['file_row']);
		} else {
			/**
			 * Вероятно товар новый.
			 * @todo Сделать возможность загружать картинки для нового товара
			 */
		}
	}

	/**
	 * Удаление файла
	 */
	protected function fileDelAjax() {
		global $modx;
		/**
		 * Идентификатор товара
		 */
		$iProductId = intval($this->aParams['prodid']);
		/**
		 * Идентификатор файла
		 */
		$sFileId = trim($this->aParams['fileid']);
		/**
		 * Путь до папки с изображениями товара
		 */
		$sBasePath = $modx->sbshop->config['image_base_dir'] . $iProductId . '/';
		/**
		 * Ошибка в процессе работы
		 */
		$bError = false;
		/**
		 * Путь к файлу
		 */
		$sFilePath = $sBasePath . $sFileId;
		/**
		 * Если файл существует
		 */
		if (is_file($sFilePath)) {
			/**
			 * Удаляем файл
			 */
			unlink($sFilePath);
		} else {
			$bError = true;
		}
		/**
		 * Если нет ошибки
		 */
		if(!$bError) {
			/**
			 * Все отлично!
			 */
			$this->aResult['success'] = true;
		} else {
			/**
			 * Отправляем ошибку
			 */
			$this->aResult['error'] = $modx->sbshop->lang['product_image_delete_error'];
		}
	}

	protected function ordEdAjax() {
		/**
		 * Данные заказа
		 */
		$aOrderData = $_POST['order'];
		/**
		 * Используемые сеты
		 */
		$aSetIdsNew = array_keys($aOrderData['products']);
		/**
		 * Загружаем заказ
		 */
		$iOrderId = intval($aOrderData['orderId']);
		$oOrder = new SBOrder();
		$oOrder->loadById($iOrderId);
		/**
		 * Получение списка товаров в заказе
		 */
		$aSetIds = $oOrder->getProductSetIds();
		/**
		 * Обрабатываем каждый сет
		 */
		foreach($aSetIds as $sSetId) {
			/**
			 * Если сет есть в новом наборе
			 */
			if(in_array($sSetId, $aSetIdsNew)) {
				/**
				 * Готовим данные для редактирования
				 */
				$aEditProduct = $aOrderData['products'][$sSetId];
				$aEditOptions = $aEditProduct['options'];
				/**
				 * Форматируем стоимость
				 */
				$aEditProduct['full_price'] = str_replace(' ', '', $aEditProduct['full_price']);
				unset($aEditProduct['options']);
				/**
				 * Редактируем товар
				 */
				$oOrder->editProduct($sSetId, $aEditProduct, $aEditOptions);
			} else {
				/**
				 * Удаляем товар из заказа
				 */
				$oOrder->deleteProduct($sSetId);
			}
		}
		/**
		 * Сохраняем
		 */
		$oOrder->save();
		/**
		 * Здесь нужно будет приготовить измененный заказ и убрать перезагрузку страницы
		 */
		//$this->aResult['html'] = '!!!';
		$this->aResult['success'] = true;
	}

	public function result() {
		return $this->aResult;
	}

}

?>
