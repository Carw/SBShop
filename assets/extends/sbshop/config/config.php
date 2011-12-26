<?php

/**
 * @author Mukharev Maxim
 * 
 * @desription
 * 
 * Электронный магазин для MODx
 * 
 * Конфигурация
 */

$config = array(
	/**
	 * Режим дебагинга
	 */
	'debug' => false,
	/**
	 * Название магазина
	 */
	'shop_name' => 'Мой магазин',
	/**
	 * Название организации
	 */
	'organization' => 'ООО «Моя компания»',
	/**
	 * Адрес почты, куда будут приходить оповещения о новых заказах
	 */
	'notice_email' => 'info@example.com',
	/**
	 * Настройка списка системных режимов работы магазина
	 */
	'route_pages' => array(
		'checkout', // корзина
		'yml',
		'price'
	),
	/**
	 * Сопоставление шаблонов для режимов
	 */
	'template_modes' => array(
		'main' => 5,
		'categories' => 5,
		'product' => 6,
		'breadcrumbs' => 5,
		'cart' => 5,
		'checkout' => 7,
		'yml' => 8,
		'price' => 12
	),
	/**
	 * Настройка списка возможных действий
	 */
	'snippet_params' => array(
		'page' => '/^page(\d+)$/i', // это выделит экшен для номера страницы
		'pagelist' => '/^page(\d+)-(\d+)$/i', // это выделит экшен для номера страницы
	),
	/**
	 * Список стандартных режимов работы
	 */
	'default_modes' => array(
		'categories',
		'products',
	),
	/**
	 * Станартный уровень вложенности для дерева категорий
	 */
	'cattree_level' => 3,
	/**
	 * Суфикс для URL
	 */
	'url_suffix' => '.html',
	/**
	 * Название домешней страницы для "хлебных крошек"
	 */
	'breadcrumbs_home_title' => 'Каталог',
	/**
	 * Количество товаров на страницу
	 */
	'product_per_page' => 5,
	/**
	 * Количество выводимых товаров во вложенной категории
	 */
	'innercat_products' => 3,
	/**
	 * Количество заказов на страницу
	 */
	'order_per_page' => 5,
	/**
	 * Количество товаров в ряду при выводе раздела. Используется для группировки.
	 * 0 - отсутствие разбивки на ряды
	 */
	'category_columns' => 3,
	/**
	 * Показывать товары, которых нет в наличии existence
	 */
	'show_not_existence' => true,
	/**
	 * Параметры ресайза изображений
	 */
	'image_resizes' => array(
		array(
			'mode' => 'x',
			'w' => 480,
			'h' => 'N',
			'quality' => 100,
			'key' => 'x480'
		),
		array(
			'mode' => 'x',
			'w' => 228,
			'h' => 'N',
			'quality' => 100,
			'key' => 'x228'
		),
		array(
			'mode' => 'x',
			'w' => 104,
			'h' => 'N',
			'quality' => 100,
			'key' => 'x104'
		),
	),
	/**
	 * Базовая директория размещения изображений
	 */
	'image_base_dir' => MODX_BASE_PATH . 'assets/images/sbshop/',
	/**
	 * Базовый URL
	 */
	'image_base_url' => '[(site_url)]assets/images/sbshop/',
	/**
	 * Настройки генерации псевдонима
	 */
	'transalias_table_name' => 'russian',
	'transalias_remove_periods' => 'No',
	'transalias_char_restrict' => 'legal characters',
	'transalias_word_separator' => 'dash',
	/**
	 * Управления статусами в заказе. Список доступных статусов на странице заказа
	 */
	'status_manage' => array(
		10,
		20,
		30,
		-30
	),
	/**
	 * Время после которого корзина считается брошенной
	 */
	'order_timeout' => 60 * 60 * 24,
    /**
     * Форматирование даты в сприске заказов
     */
    'order_date_format' => 'd.m - H:m',
	/**
	 * Скрыть указанные значения опции из выбора
	 */
	'hide_option_values' => array(
		16
	),
	/**
	 * Разделитель опции и ее значения
	 */
	'option_separator' => ':',
	/**
	 * Основные поля фильтра
	 */
	'filter_general' => array(
		'price',
		'vendor',
		'existence'
	),
	/**
	 * До какого знака округлять стоимость
	 */
	'price_round' => array(
		'precision' => -1, // число знаков для округления
		'decimals' => 0, // число знаков после запятой
		'dec_point' => ',', // разделитель дробной части
		'thousands_sep' => ' ' // разделитель тысяч
	),

);


?>