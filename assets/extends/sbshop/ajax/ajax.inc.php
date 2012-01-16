<?php
/**
 * @author Mukharev Maxim
 * @version 0.1a
 *
 * @desription
 *
 * Электронный магазин для MODx
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
		if(method_exists($this,$sMethodName)) {
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
			$this->aResult['title'] = $oTip->getAttribute('title');
			$this->aResult['description'] = $oTip->getAttribute('description');
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

	public function result() {
		return $this->aResult;
	}

}

?>
