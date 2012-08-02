<?php

$_3b_treebuilder = array(
	// заголовок дерева (отображается как корень)
	'treeName'			=> 'Список товаров',

	// имя функции, возращающей массив категорий
	'getCats'			=> 'SBShopGetFolders',

	// имя функции, возращающей массив файлов 
	'getItems'			=> 'SBShopGetFiles',

	// имя функции, информирующей о существовании элементов в корзине
	'deletedExists'		=> 'issetDeleted',

	// изображение закрытой папки (если пусто - используется стандартное modx'овское)
	'folderImage'		=> '',

	// изображение открытой папки (если пусто - используется стандартное modx'овское)
	'folderImageOpn'	=> '',

	// javascript -код, выполняемый при клике на файл (элемент - не папку) modid - переменная, содержащая id Вашего модуля
	'onItemClick'		=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=prod&act=edit&prodid=' + itemToChange",

	// аналогично onItemClick только для папок
	'onFolderClick'		=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=cat&act=edit&catid=' + itemToChange",

	// аналогично onItemClick только происходит при нажатии на "очистить корзину" в верхнем меню
	'onBinClear'		=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=clearbin';",

	// пункты верхнего меню
	'treeMenu'			=> array( // по-умолчанию уже есть пункты "Раскрыть все", "Свернуть все", "Обновить", "Очистить корзину"
		'NewCat'			=> array(
			'onclick'			=> "top.main.location.href = 'index.php?a=112&mode=cat&act=new&id=' + modid;",
			'title'				=> 'Добавить категорию',
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/folder_page_add.png'			
		)
	),

	// всплывающее меню папок
	'folderPopupMenu'	=> array(
		'openFolder'		=> array(
			'text'				=> 'Редактировать',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=cat&act=edit&catid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/save.png'
		),
		'separator1'		=> array('text' => '-'),
		'createSubFolder'	=> array(
			'text'				=> 'Создать подкатегорию',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=cat&act=new&parid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/folder_page_add.png'
		),
		'createSubFile'		=> array(
			'text'				=> 'Создать товар',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=prod&act=new&catid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/page_white_add.png'
		),
		'separator2'		=> array('text' => '-'),
		'publishFolder'		=> array(
			'text'				=> 'Опубликовать',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=cat&act=pub&catid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/clock_play.png'
		),
		'unpublishFolder'		=> array(
			'text'				=> 'Отменить публикацию',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=cat&act=unpub&catid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/clock_stop.png'
		),
		'separator3'		=> array('text' => '-'),
		'moveFolder'		=> array(
			'text'				=> 'Перенести',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=cat&act=move&catid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/page_white_go.png'
		),
		'deleteFolder'		=> array(
			'text'				=> 'Удалить',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=cat&act=del&catid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/delete.png'
		),
		'restoreFolder'		=> array(
			'text'				=> 'Восстановить',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=cat&act=undel&catid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/b092.gif'
		),
		'separator4'		=> array('text' => '-'),
		'sortFolder'    	=> array(
			'text'				=> 'Сортировать',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=sort&act=cat&catid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/sort.png'
		),
	),

	// всплывающее меню товара
	'itemPopupMenu'		=> array(
		'openFile'			=> array(
			'text'				=> 'Редактировать',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=prod&act=edit&prodid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/save.png'
		),
		'separator1'		=> array('text' => '-'),
		'copyFile'			=> array(
			'text'				=> 'Сделать копию',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=prod&act=copy&prodid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/page_white_copy.png'
		),
		'publishFile'		=> array(
			'text'				=> 'Опубликовать',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=prod&act=pub&prodid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/clock_play.png'
		),
		'unpublishFile'		=> array(
			'text'				=> 'Отменить публикацию',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=prod&act=unpub&prodid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/clock_stop.png'
		),
		'separator2'		=> array('text' => '-'),
		'deleteFile'		=> array(
			'text'				=> 'Удалить',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=prod&act=del&prodid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/delete.png'
		),
		'restoreFile'		=> array(
			'text'				=> 'Восстановить',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=prod&act=undel&prodid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/b092.gif'
		),
		'separator3'		=> array('text' => '-'),
		'sortFile'		=> array(
			'text'				=> 'Сортировать',
			'onclick'			=> "top.main.location.href = 'index.php?a=112&id=' + modid + '&mode=sort&act=prod&prodid=' + itemToChange",
			'image'				=> 'manager/media/style/MODxCarbon/images/icons/sort.png'
		),
	)
);


/**
 * функция, возращающая массив папок. формат массива:
 * array(
 * 	int ID => array(
 * 		'title'		=> string
 * 		'tooltip'	=> string
 * 		'deleted'	=> bool (int: 0 = false || !0 = true)
 * 		'published'	=> bool (int: 0 = false || !0 = true)
 * 	)
 * )
 * @param $parent
 * @return unknown_type
 */
function SBShopGetFolders($parent) {
	global $modx;
	
	$sql = "SELECT * FROM " . $modx->getFullTableName('sbshop_categories') . " WHERE `category_parent` = '{$parent}' ORDER BY `category_order`";
	$cats = $modx->dbQuery($sql);
	$return = array();
	
	while($cat = $modx->fetchRow($cats)) {
		$return[$cat['category_id']] = array(
			'title' => $cat['category_title'],
			'tooltip' => 'Псевдоним: ' . $cat['category_alias'],
			'deleted' => $cat['category_deleted'],
			'published' => $cat['category_published'],
		);
	}
	
	return $return;
}

/**
 * функция, возращающая массив файлов. формат массива:
 * array(
 * 	int ID => array(
 * 		'title'		=> string
 * 		'tooltip'	=> string
 * 		'deleted'	=> bool (int: 0 = false || !0 = true)
 * 		'published'	=> bool (int: 0 = false || !0 = true)
 * 		'image'		=> string // если не задано или задана пустая строка - будет использоваться стандартная иконка документа modx
 * 	)
 * )
 */
function SBShopGetFiles($folder) {
	global $modx;
	
	$sql = "SELECT * FROM " . $modx->getFullTableName('sbshop_products') . " WHERE `product_category` = '{$folder}' ORDER BY `product_order`";
	$files = $modx->dbQuery($sql);
	$return = array();
	
	while($file = $modx->fetchRow($files)) {
		$return[$file['product_id']] = array(
			'title' => $file['product_title'],
			'tooltip' => 'Псевдоним: ' . $file['product_alias'],
			'deleted' => $file['product_deleted'],
			'published' => $file['product_published'],
			'image' => 'manager/media/style/MODxCarbon/images/tree/application_html.png'
		);
	}
	return $return;
}

/**
 * @return: bool (есть ли категории / элементы, обозначенный как удаленные)
 */
function issetDeleted() {
	return true;
}

?>