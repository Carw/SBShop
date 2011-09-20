// Инициализация опций
option_init = function(){
	$("#options input.option_name").keyup(function(event){
		$(this).parents('tr.content').prev('tr.option_header').find('h3.title').text($(this).val());
	});
};

// Реинициализация аккордиона
option_reinit = function() {
	option_init();
};

filter_init = function() {
}

filter_reinit = function() {
};

$(document).ready(function(){

	$('#docManagerPane .tab').click(function(){
		if($('#tabFilter').css('display') == 'block') {
			filter_reinit();
			$('#filters').css('visibility', 'visible');
		} else if($('#tabOptions').css('display') == 'block') {
			option_reinit();
			$('#options').css('visibility', 'visible');
		}
	});

	if($('#tabFilter').css('display') == 'block') {
		filter_init();
		$('#filters').css('visibility', 'visible');
	} else if($('#tabOptions').css('display') == 'block') {
		option_init();
		$('#options').css('visibility', 'visible');
	}

	$('button.option_value_add').click(function(){
		id = $(this).val();
		a = $('#tabFilters .value_template').clone();
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

	// обработка клика на кнопку добавления новой опции
	$("#new_option_add").click(function(){
		/**
		 * Клонируем шаблон опции
		 */
		a = $(".option_template .option").clone();
		a.appendTo('#options .fastlist');
		/**
		 * Изменяем заголовок на указанный
		 */
		if($('#new_option_name').val() != '') {
			title = $('#new_option_name').val();
			$('#new_option_name').val('');
		} else {
			title = option_name;
		}
		a.find("h3.title").text(title);
		a.find('.option_name').val(title);
		a.find('input.option_del').click(function(){
			$(this).parents('tr.option').remove();
			option_reinit();
			return false;
		});
		a.find('h3.title').click(function(){
			elem = $(this).parents('tr.option_header').next();
			if(elem.hasClass('visible')) {
				elem.removeClass('visible');
			} else {
				elem.addClass('visible');
			}
		});
		$('#options .sorttable').tableDnD({
			dragHandle: "dragHandle"
		});
		option_reinit();
		return false;
	});

	$('.option_del').click(function(){
		/**
		 * Основное элемент
		 */
		$(this).parents('tr.option').remove();
		option_reinit();
		return false;
	});

	$('.filter_value_del').click(function(){
		$(this).parents('div.values').remove();
		return false;
	});

	$('.filter_type').change(function(){
		a = $(this).parents('#filters .content');
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

	$('#options .option_header h3').click(function(){
		elem = $(this).parents('tr.option_header').next();
		if(elem.hasClass('visible')) {
			elem.removeClass('visible');
		} else {
			elem.addClass('visible');
		}
	});

	$('#filters .filter_header h3').click(function(){
		elem = $(this).parents('tr.filter_header').next();
		if(elem.hasClass('visible')) {
			elem.removeClass('visible');
		} else {
			elem.addClass('visible');
		}
	});

	$('.sorttable').tableDnD({
		dragHandle: "dragHandle"
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