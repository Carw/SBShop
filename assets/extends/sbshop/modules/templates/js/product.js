/**
 * Инициализация параметров
 */
attributes_init = function(){
	var stop = false;
	$("#attributes h3").click(function(event) {
		if (stop) {
			event.stopImmediatePropagation();
			event.preventDefault();
			stop = false;
		}
	});

	$("#attributes").accordion({
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

	$("#attributes input.attribute_name").keyup(function(event){
		$(this).parents('div').children('h3').children('a').text($(this).val());
	});
};

// Реинициализация аккордиона
attributes_reinit = function() {
	$("#attributes").accordion('option','active',false);
	$("#attributes").accordion('destroy');
	attributes_init();
};

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

option_value_init = function() {

};

// Инициализация комплектаций
bundle_init = function(){
	var stop = false;
	$("#bundles h3").click(function(event) {
		if (stop) {
			event.stopImmediatePropagation();
			event.preventDefault();
			stop = false;
		}
	});

	$("#bundles").accordion({
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

	$("#bundles input.bundle_name").keyup(function(event){
		$(this).parents('div').children('h3').children('a').text($(this).val());
	});
};

// Реинициализация аккордиона
option_reinit = function() {
	$("#options").accordion('option','active',false);
	$("#options").accordion('destroy');
	option_init();
};

// Реинициализация аккордиона
bundle_reinit = function() {
	$("#bundles").accordion('option','active',false);
	$("#bundles").accordion('destroy');
	bundle_init();
};

$(document).ready(function(){
	// задаем кнопки
	$('button').button();

	// обработка клика на кнопку добавления новой опции
	$("#new_option_add").click(function(){
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
		a.find('input.option_del').click(function(){
			$(this).parents('div.option').remove();
			option_reinit();
			return false;
		});
		option_reinit();
		return false;
	});

	$('.option_del').click(function(){
		$(this).parents('div.option').remove();
		option_reinit();
		return false;
	});

	$('.option_extend').click(function(){
		var parent = $(this).parents('div.option');
		if(parent.find('.extend').hasClass('visible')) {
			parent.find('.extend').removeClass('visible');
		} else {
			parent.find('.extend').addClass('visible');
			parent.find('div.values .extend').removeClass('visible');
		}
		return false;
	});

	$('button.option_value_add').click(function(){
		id = $(this).val();
		a = $('.value_template').clone();
		a.appendTo('#values_' + id);
		a.removeClass('value_template');
		/**
		 * идентификатор в скрытом поле
		 */
		a.find('input.option_value_ids').attr('name','option_value_ids[' + id + '][]').removeClass('option_value_ids');
		a.find('input.option_values_title').attr('name','option_values_title[' + id + '][]').removeClass('option_values_title');
		a.find('input.option_values_value').attr('name','option_values_value[' + id + '][]').removeClass('option_values_value');
		a.find('input.option_values_class').attr('name','option_values_class[' + id + '][]').removeClass('option_values_class');
		a.find('input.option_values_image').attr('name','option_values_image[' + id + '][]').removeClass('option_values_image');
		a.find('input.option_value_del').click(function(){
			$(this).parents('div.values').remove();
			return false;
		});
		a.find('input.option_value_extend').click(function(){
			if($(this).parents('div.values').find('.extend').hasClass('visible')) {
				$(this).parents('div.values').find('.extend').removeClass('visible');
			} else {
				$(this).parents('div.values').find('.extend').addClass('visible');
			}
			return false;
		});
		return false;
	});

	$('.option_value_del').click(function(){
		$(this).parents('div.values').remove();
		return false;
	});

	$('.option_value_extend').click(function(){
		if($(this).parents('div.values').find('.extend').hasClass('visible')) {
			$(this).parents('div.values').find('.extend').removeClass('visible');
		} else {
			$(this).parents('div.values').find('.extend').addClass('visible');
		}
		return false;
	});

	// обработка клика на кнопку добавления новой комплектации
	$("#new_bundle_add").click(function(){
		/**
		 * Клонируем шаблон комплектации
		 */
		a = $(".bundle_template").clone();
		a.appendTo('#bundles');
		a.removeClass('bundle_template');
		a.css('display','block');
		/**
		 * Изменяем заголовок на указанный
		 */
		if($('#new_bundle_name').val() != '') {
			title = $('#new_bundle_name').val();
			$('#new_bundle_name').val('');
		} else {
			title = bundle_name;
		}
		a.find(".ui-accordion-header").children().filter("a").text(title);
		a.find('.bundle_name').val(title);
		bundle_reinit();
		return false;
	});

	$('.bundle_del').click(function(){
		$(this).parents('div.bundle').remove();
		bundle_reinit();
		return false;
	});

	$('#new_attribute_add').click(function(){
		/**
		 * Клонируем шаблон параметра
		 */
		a = $(".attribute_template").clone();
		a.appendTo('#attributes');
		a.removeClass('attribute_template');
		a.css('display','block');
		/**
		 * Изменяем заголовок на указанный
		 */
		if($('#new_attribute_name').val() != '') {
			title = $('#new_attribute_name').val();
			$('#new_attribute_name').val('');
		} else {
			title = attribute_name;
		}
		a.find(".ui-accordion-header").children().filter("a").text(title);
		a.find('.attribute_name').val(title);
		a.find('input.attribute_del').click(function(){
			$(this).parents('div.attribute').remove();
			attributes_reinit();
			return false;
		});
		attributes_reinit();
		return false;
	});

	$('.attribute_del').click(function(){
		$(this).parents('div.attribute').remove();
		attributes_reinit();
		return false;
	});

	$('#docManagerPane .tab').click(function(){
		if($('#tabAttributes').css('display') == 'block') {
			attributes_reinit();
			$('#attributes').css('visibility', 'visible');
		} else if($('#tabOptions').css('display') == 'block') {
			option_reinit();
			$('#options').css('visibility', 'visible');
		} else if ($('#tabBundles').css('display') == 'block') {
			bundle_reinit();
			$('#bundles').css('visibility', 'visible');
		}
	});

	if($('#tabAttributes').css('display') == 'block') {
		attributes_init();
		$('#attributes').css('visibility', 'visible');
	} else if($('#tabOptions').css('display') == 'block') {
		option_init();
		$('#options').css('visibility', 'visible');
	} else if ($('#tabBundles').css('display') == 'block') {
		bundle_init();
		$('#bundles').css('visibility', 'visible');
	}

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