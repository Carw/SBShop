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
		}
	});

	if($('#tabFilter').css('display') == 'block') {
		filter_init();
		$('#filter').css('visibility', 'visible');
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
		attr = $(this).parents('div.attribute_group').find('div.attribute_group_outer');
		/**
		 * Клонируем шаблон параметра
		 */
		a = $(".attribute_template").clone();
		/**
		 * Добавляем шаблон параметра в контейнер
		 */
		attr.append(a);

		a.removeClass('attribute_template');
		a.css('display','block');
		
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

});