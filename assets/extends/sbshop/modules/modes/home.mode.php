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
 * Экшен модуля электронного магазина: Стартовая страница модуля
 * 
 */


class home_mode {
	
	/**
	 * Конструктор
	 * @param unknown_type $sModuleLink
	 * @param unknown_type $sMode
	 * @param unknown_type $sAct
	 */
	public function __construct($sModuleLink, $sMode, $sAct = '') {
		global $modx;
		echo '<div class="sectionHeader"><div class="breadcrumbs">Электронный магазин</div></div>';
		echo '<div class="sectionBody">';
		echo '<p><a href="' . $sModuleLink . '&mode=order' . '">Текущие заказы</a></p>';
		echo '<p><a href="' . $sModuleLink . '&mode=order&act=arch' . '">Архив заказов</a></p>';
		echo '<p><a href="' . $sModuleLink . '&mode=order&act=trash' . '">Брошенные заказы</a></p>';
		echo '<p><a href="' . $sModuleLink . '&mode=pricing' . '">Управление ценами</a></p>';
		echo '<p><a href="' . $sModuleLink . '&mode=update' . '">Обновление</a> (нажимать нежно!)</p>';
		echo '<p><img src="' . MODX_BASE_URL . 'assets/cache/sbshop/week.png" /></p>';
		echo '</div>';

		/**
		 * Рисуем график
		 */
		$this->getWeekGraph();
	}
	
	/**
	 * Получение графика активности в магазине за неделю
	 */
	protected function getWeekGraph() {
		global $modx;
		/**
		 * Подключаем библиотеки для рисования диаграмм
		 */
		include(MODX_BASE_PATH . 'assets/libs/pchart/pdata.class');
		include(MODX_BASE_PATH . 'assets/libs/pchart/pchart.class');
		/**
		 * Сегодняшняя дата
		 */
		$sToday = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d"), date("Y")));
		/**
		 * Путь до недельного гарфика
		 */
		$sWeekGraphPath = MODX_BASE_PATH . 'assets/cache/sbshop/week.png';
		/**
		 * Если есть график в кеше
		 */
		if(is_file($sWeekGraphPath)) {
			/**
			 * Получаем дату
			 */
			$sGraphDate = date ('Y-m-d', filemtime($sWeekGraphPath));
		}
		/**
		 * Если дата графика не совпадает с сегодняшней датой
		 */
		if($sGraphDate != $sToday) {
			/**
			 * Данные
			 */
			$oDataSet = new pData;
			/**
			 * Массив брошенных заказов
			 */
			$aOrdersDropped = $this->getOrderDataByStatuses(array(0));
			/**
			 * Если такие заказы есть
			 */
			if(count($aOrdersDropped) > 0) {
				$oDataSet->AddPoint($aOrdersDropped,"Serie1");
				$oDataSet->AddSerie("Serie1");
				$oDataSet->SetSerieName("Брошенные заказы","Serie1");
			}
			/**
			 * Массив полученных заказов
			 */
			$aOrdersWell = $this->getOrderDataByStatuses(array(10,20));
			/**
			 * Если такие заказы есть
			 */
			if(count($aOrdersWell) > 0) {
				$oDataSet->AddPoint($aOrdersWell,"Serie2");
				$oDataSet->AddSerie("Serie2");
				$oDataSet->SetSerieName("Полученные заказы","Serie2");
			}
			/**
			 * Массив полученных заказов
			 */
			$aOrdersReady = $this->getOrderDataByStatuses(array(30));
			/**
			 * Если такие заказы есть
			 */
			if(count($aOrdersReady) > 0) {
				$oDataSet->AddPoint($aOrdersReady,"Serie3");
				$oDataSet->AddSerie("Serie3");
				$oDataSet->SetSerieName("Исполненные заказы","Serie3");
			}
			/**
			 * Массив отклоненных заказов
			 */
			$aOrdersDeclined = $this->getOrderDataByStatuses(array(-30));
			/**
			 * Если такие заказы есть
			 */
			if(count($aOrdersDeclined) > 0) {
				$oDataSet->AddPoint($aOrdersDeclined,"Serie4");
				$oDataSet->AddSerie("Serie4");
				$oDataSet->SetSerieName("Отклоненные заказы","Serie4");
			}
			/**
			 * Массив отклоненных заказов
			 */
			$aOrdersCleared = $this->getOrderDataByStatuses(array(-10));
			/**
			 * Если такие заказы есть
			 */
			if(count($aOrdersCleared) > 0) {
				$oDataSet->AddPoint($aOrdersCleared,"Serie5");
				$oDataSet->AddSerie("Serie5");
				$oDataSet->SetSerieName("Очищенные корзины","Serie5");
			}
			$oDataSet->AddPoint(array_keys($aOrdersDropped),"Datas");
			$oDataSet->SetAbsciseLabelSerie("Datas");
			$oDataSet->SetYAxisName("Количество");
			$oDataSet->SetXAxisName("Дата");
			/**
			 * Объект
			 */
			$oChart = new pChart(600,250);
			/**
			 * Шрифт
			 */
			$oChart->setFontProperties(MODX_BASE_PATH . "assets/libs/pchart/fonts/tahoma.ttf",8);
			/**
			 * Площадка для графика
			 */
			$oChart->setGraphArea(70,30,580,200);

			$oChart->drawFilledRoundedRectangle(7,7,593,243,5,240,240,240);
			$oChart->drawRoundedRectangle(5,5,595,245,5,230,230,230);
			$oChart->drawGraphArea(255,255,255,TRUE);
			$oChart->drawScale($oDataSet->GetData(),$oDataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);
			$oChart->drawGrid(4,TRUE,230,230,230,50);
			$oChart->setFontProperties(MODX_BASE_PATH . "assets/libs/pchart/fonts/tahoma.ttf",6);
			$oChart->drawTreshold(0,143,55,72,TRUE,TRUE);
			/**
			 * Рисуем сами графики
			 */
			$oChart->drawLineGraph($oDataSet->GetData(),$oDataSet->GetDataDescription());
			$oChart->drawPlotGraph($oDataSet->GetData(),$oDataSet->GetDataDescription(),3,2,255,255,255);
			/**
			 * Завершаем отрисовку
			 */
			$oChart->setFontProperties(MODX_BASE_PATH . "assets/libs/pchart/fonts/tahoma.ttf",8);
			$oChart->drawLegend(75,35,$oDataSet->GetDataDescription(),255,255,255);
			$oChart->setFontProperties(MODX_BASE_PATH . "assets/libs/pchart/fonts/tahoma.ttf",10);
			$oChart->drawTitle(60,22,"Активность в магазине за неделю",50,50,50,585);
			/**
			 * Директория для вывода графика
			 */
			$sDirOut = MODX_BASE_PATH . "assets/cache/sbshop/";
			/**
			 * Если директории нет
			 */
			if(!is_dir($sDirOut)) {
				/**
				 * Создаем директорию
				 */
				mkdir($sDirOut);
			}
			/**
			 * Записываем
			 */
			$oChart->Render(MODX_BASE_PATH . "assets/cache/sbshop/week.png");
		}
	}

	/**
	 * Получение информации о заказах с определенным статусом
	 * @global <type> $modx
	 * @param <type> $aStatuses
	 * @param <type> $sWhere
	 * @return <type>
	 */
	public function getOrderDataByStatuses($aStatuses) {
		global $modx;
		/**
		 * Текущая дата
		 */
		$mkDateEnd = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		$iDateEnd = date('Y-m-d 00:00:00', $mkDateEnd);
		/**
		 * Начало недели
		 */
		$mkDateStart = mktime(0, 0, 0, date("m"), date("d") - 7, date("Y"));
		$iDateStart = date('Y-m-d 00:00:00', $mkDateStart);
		/**
		 * Ограничение по дате в условии поиска заказов
		 */
		$sWhere = 'order_date_add between "' . $iDateStart . '" AND "' . $iDateEnd . '"';
		/**
		 * Загружаем список заказов
		 */
		$oOrderList = new SBOrderList($aStatuses, $sWhere);
		/**
		 * Получаем массив заказов
		 */
		$aOrderList = $oOrderList->getOrderList();
		/**
		 * Промежуточный массив значений
		 */
		$aOrders = array();
		/**
		 * Набо конечных значений
		 */
		$aOutput = array();
		/**
		 * Обрабатываем каждую запись
		 */
		foreach ($aOrderList as $oOrder) {
			/**
			 * Получаем дату
			 */
			$sDate = $oOrder->getAttribute('date_add');
			/**
			 * Переводим в число.месяц
			 */
			$sDate = mktime(0, 0, 0, date('m',strtotime($sDate)), date('d',strtotime($sDate)), date('Y',strtotime($sDate)));
			/**
			 * Добавляем в массив
			 */
			$aOrders[$sDate]++;
		}
		/**
		 * Снова обрабатываем каждую запись и добавляем пропуски
		 */
		for($i=7; $i>=0; $i--) {
			/**
			 * Получаем дату
			 */
			$mkDate = mktime(0, 0, 0, date("m"), date("d") - $i, date('Y'));
			$iDate = date('m-d', $mkDate);
			/**
			 * Если в массиве значения нет
			 */
			if(!isset($aOrders[$mkDate])) {
				/**
				 * Добавляем ноль
				 */
				$aOutput[$iDate] = 0;
			} else {
				$aOutput[$iDate] = $aOrders[$mkDate];
			}
		}
		/**
		 * Возвращаем результат
		 */
		return $aOutput;
	}
}

?>