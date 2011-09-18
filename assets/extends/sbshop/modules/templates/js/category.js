// Инициализация опций
option_init = function(){
	var stop = false;
	$("#options h3").click(function(event) {
		if (stop) {
			event.stopImmediatePropagation();
			event.preventDefault();
			stop = false;
		}
	});

	$("#options").accordion({
		collapsible: true,
		active: false,
		autoHeight: true,
		header: "> div > h3"
	}).sortable({
		axis: "y",
		handle: "h3",
		stop: function(event, ui) {
			stop = true;
		}
	});

	$("#options input.option_name").keyup(function(event){
		$(this).parents('div').children('h3').children('a').text($(this).val());
	});
};

// Реинициализация аккордиона
option_reinit = function() {
	$("#options").accordion('option','active',false);
	$("#options").accordion('destroy');
	option_init();
};

filter_init = function() {
	$("#filter").accordion({
		collapsible: true,
		active: false,
		autoHeight: true,
		header: "> div > h3"
	}).sortable({
		axis: "y",
		handle: "h3",
		stop: function(event, ui) {
			stop = true;
		}
	});
}

filter_reinit = function() {
	$("#filter").accordion('option','active',false);
	$("#filter").accordion('destroy');
	filter_init();
};

$(document).ready(function(){
	// задаем кнопки
	//$('button').button();

	$('#docManagerPane .tab').click(function(){
		if($('#tabFilter').css('display') == 'block') {
			filter_reinit();
			$('#filter').css('visibility', 'visible');
		} else if($('#tabOptions').css('display') == 'block') {
			option_reinit();
			$('#options').css('visibility', 'visible');
		}
	});

	if($('#tabFilter').css('display') == 'block') {
		filter_init();
		$('#filter').css('visibility', 'visible');
	} else if($('#tabOptions').css('display') == 'block') {
		option_init();
		$('#options').css('visibility', 'visible');
	}

	$('button.option_value_add').click(function(){
		id = $(this).val();
		a = $('.value_template').clone();
		a.appendTo('#values_' + id);
		a.removeClass('value_template');
		
		a.find('input.filter_value_title').attr('name','filter_value_title[' + id + '][]').removeClass('filter_value_title');
		a.find('input.filter_value_min').attr('name','filter_value_min[' + id + '][]').removeClass('filter_value_min');
		a.find('input.filter_value_max').attr('name','filter_value_max[' + id + '][]').removeClass('filter_value_max');
		a.find('input.filter_value_eqv').attr('name','filter_value_eqv[' + id + '][]').removeClass('filter_value_eqv');
		a.find('input.filter_value_del').click(function(){
			$(this).parents('div.values').remove();
			return false;
		});
		if($('#filter_type_' + id).val() == 'rng') {
			a.find('.rng').addClass('visible');
		} else {
			a.find('.eqv').addClass('visible');
		}
		return false;
	});

	$('.new_attribute_add').click(function(){
		/**
		 * Определяем контейнер для параметров
		 */
		attr = $(this).parents('div.attribute_group').find('table.sorttable');
		/**
		 * Клонируем шаблон параметра
		 */
		a = $(".attribute_template").clone();
		/**
		 * Добавляем шаблон параметра в контейнер
		 */
		attr.append(a);

		a.removeClass('attribute_template');

		a.find('input.attribute_del').click(function(){
			$(this).parents('div.attribute').remove();
			$('.sorttable').tableDnD({
				dragHandle: "dragHandle"
			});
			return false;
		});

		$('.sorttable').tableDnD({
			dragHandle: "dragHandle"
		});

		return false;
	});

	$('.attribute_del').click(function(){
		$(this).parents('div.attribute').remove();
		return false;
	});

	$('.sorttable').tableDnD({
		dragHandle: "dragHandle"
	});

	$("#new_option_add").click(function(){
		/**
		 * Новый идентификатор
		 */
		id = $('#options .option').size() - 1;
		/**
		 * Клонируем шаблон опции
		 */
		a = $(".option_template").clone();
		a.appendTo('#options');
		a.removeClass('option_template');
		a.css('display','block');
		/**
		 * Изменяем заголовок на указанный
		 */
		if($('#new_option_name').val() != '') {
			title = $('#new_option_name').val();
			$('#new_option_name').val('');
		} else {
			title = option_name;
		}


		a.find(".ui-accordion-header").children().filter("a").text(title);
		a.find('.option_name').val(title);

		a.find('.option_id').attr('name', 'option_id[' + id + ']');
		a.find('.option_name').attr('name', 'option_name[' + id + ']');
		a.find('.option_class').attr('name', 'option_class[' + id + ']');
		a.find('.option_image').attr('name', 'option_image[' + id + ']');
		a.find('.option_image_button').click(function(){
			BrowseServer('option_image[' + id + ']');
		});

		a.find('input.option_del').click(function(){
			$(this).parents('div.option').remove();
			option_reinit();
			return false;
		});
		option_reinit();
		return false;
	});

	$('.filter_value_del').click(function(){
		$(this).parents('div.values').remove();
		return false;
	});

	$('.filter_type').change(function(){
		a = $(this).parents('div.filter');
		if($(this).val() == 'rng') {
			a.find('.eqv').removeClass('visible');
			a.find('.rng').addClass('visible');
		} else {
			a.find('.rng').removeClass('visible');
			a.find('.eqv').addClass('visible');
		}
	});
	
	/**
	 * Работа с подсказками
	 */
	$('button.tipclear').click(function(){
		tipId = $(this).attr('name');
		tipVal = $(this).text();
		$('#' + tipId).val('');
		$('#info_' + tipId).html(tipVal);
		return false;
	});

});

/**
 * Выбор изображения. Код взят из MODX от виджета Image
 */
var lastImageCtrl;
var lastFileCtrl;
function OpenServerBrowser(url, width, height ) {
	var iLeft = (screen.width  - width) / 2 ;
	var iTop  = (screen.height - height) / 2 ;

	var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes' ;
	sOptions += ',width=' + width ;
	sOptions += ',height=' + height ;
	sOptions += ',left=' + iLeft ;
	sOptions += ',top=' + iTop ;

	var oWindow = window.open( url, 'FCKBrowseWindow', sOptions ) ;
}
function BrowseServer(ctrl) {
	lastImageCtrl = ctrl;
	var w = screen.width * 0.7;
	var h = screen.height * 0.7;
	OpenServerBrowser('/manager/media/browser/mcpuk/browser.html?Type=images&Connector=/manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=/', w, h);
}

function BrowseFileServer(ctrl) {
	lastFileCtrl = ctrl;
	var w = screen.width * 0.7;
	var h = screen.height * 0.7;
	OpenServerBrowser('/manager/media/browser/mcpuk/browser.html?Type=files&Connector=/manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=/', w, h);
}

function SetUrl(url, width, height, alt){
	if(lastFileCtrl) {
		var c = document.mutate[lastFileCtrl];
		if(c) c.value = url;
		lastFileCtrl = '';
	} else if(lastImageCtrl) {
		var c = document.mutate[lastImageCtrl];
		if(c) c.value = url;
		lastImageCtrl = '';
	} else {
		return;
	}
}