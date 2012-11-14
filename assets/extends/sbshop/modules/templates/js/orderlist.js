/**
 * @name SBShop
 * @author Mukharev Maxim
 *
 * @desription
 *
 * SBShop - Интернет-магазин на MODx
 *
 * Скрипт для списка заказов
 */

var curOrder; // Текущий заказ с которым мы работаем

$(document).ready(function(){

	$('.opener').click(function(){
		var order = $(this).parents('.order');
		if(order.hasClass('opened')) {
			$('.order').removeClass('opened');
		} else {
			$('.order').removeClass('opened');
			order.addClass('opened');
		}
	});

	$('.sb_date_next').dynDateTime({
		showsTime: true,
		timeFormat: 24,
		ifFormat: "%H,%M,%m,%d,%Y",
		daFormat: "%l;%M %p, %e %m,  %Y",
		flat: ".next()"
	});

	popupActions();

	$('#opacity').click(function(){
		$('#popup').removeClass('active');
		$('#opacity').removeClass('active');
	});

	$('.order_edit').click(function(){
		/**
		 * Разбираем данные для отображения
		 */
		var orderId = $(this).attr('data');

		var OutPut = '<input type="hidden" class="ordId" value="' + orderId + '" />';

		$('.order-' + orderId + ' .setid').each(function(){

			OutPut += '<div class="order">';

			/**
			 * Идентификатор сета
			 */
			var setId = $(this).val();

			var order = OrderData[setId];
			/**
			 * Заголовок
			 */
			OutPut += '<input type="hidden" class="setId" value="' + setId + '" />' +
				'<div class="title"><input type="hidden" class="ptitle" value="' + order.title + '" />' +
				order.title +
				'<a href="#" class="product_del">Удалить</a>' +
				'<div class="quantity">Количество: <input type="text" class="pquantity" value="' + order.quantity + '" /></div>' +
				'<div class="price">Цена: <input type="text" class="pprice" value="' + order.price + '" /></div>' +
			'</div>';
			/**
			 * Опции
			 */
			OutPut += '<div class="options">' +
                '<div class="option_add">Добавить опцию: <input type="text" class="ins" value="" /></div>';
			/**
			 * Выводим расширенные опции
			 */
			if(order.options && order.options.ext) {
				for(optionId in order.options.ext) {
					var option = order.options.ext[optionId];
					OutPut += '<div class="option">' +
							'<input type="hidden" class="oid" value="' + optionId + '" />' +
							'<input type="hidden" class="vid" value="' + option.valuid + '" />' +
							'<input type="hidden" class="oprice" value="' + option.price + '" />' +
							'<span class="otitle">' + option.title + '</span>' +
							'<a href="#" class="option_del">Удалить</a>' +
						'</div>';
				}
			}
			/**
			 * Выводим опции добавленные вручную
			 */
			if(order.options && order.options.man) {
				for(optionId in order.options.man) {
					var option = order.options.man[optionId];
					OutPut += '<div class="option">' +
						'<span class="otitle">' + option.title + '</span>' +
						'<a href="#" class="option_del">Удалить</a>' +
						'</div>';
				}
			}
			OutPut += '</div></div>';
		});

		OutPut += '<div class="order_actions">' +
				'<input class="close" type="button" value="Отмена" />' +
				'<input class="save" type="button" value="Сохранить" />' +
			'<div>';

		$('#popup .info').html(OutPut);
		$('#popup .h').html('Редактирование заказа №' + orderId);

		popupActions();
		/**
		 * Обработчики для редактирования
		 */
		editActions();

		$('#popup').addClass('active');
		$('#opacity').addClass('active');
	});

	$('.bundletitle').click(function(){
		$(this).parent().find('.bundleoptions').toggle('active');
	});

});

function popupActions() {
	$('#popup .close').click(function(){
		$('#popup').removeClass('active');
		$('#opacity').removeClass('active');
	});
}

function editActions() {
	delOption();

	$('.product_del').click(function(){
		$(this).closest('.order').remove();
	});

	$('.save').click(function(){
		/**
		 * Получаем список заказов
		 */
		var orderId = $('#popup').find('.ordId').val();

		var products = {};
		/**
		 * Обрабатываем каждый заказ
		 */
		$('#popup').find('.order').each(function(e){

			var setId = $(this).find('.setId').val();

			var pTitle = $(this).find('.ptitle').val();
			var pPrice = $(this).find('.pprice').val();
			var pQuantity = $(this).find('.pquantity').val();

			var options = {};

			$(this).find('.option').each(function(){
				var oId = $(this).find('.oid').val();
				var vId = $(this).find('.vid').val();
				var oPr = $(this).find('.oprice').val();
				var oTitle = $(this).find('.otitle').text();

				if(!oId) {
					oId = 'n' + Object.keys(options).length;
				}

				var option = {
					'value_id': vId,
					'title': oTitle,
					'price': oPr
				};

				options[oId] = option;
			});

			products[setId] = {
				'title': pTitle,
				'price': pPrice,
				'quantity': pQuantity,
				'options': options
			};
		});

		var saveData = {'orderId': orderId,	'products': products};

		var ajaxData = $.ajax({
			type: 'POST',
			url: '/assets/extends/sbshop/ajax/ajax.module.php',
			data: {
				'm': 'ordEd',
				'order': saveData
			},
			success: function(result) {
				/**
				 * Здесь должна быть обработка полученной обновленной инормации
				 */
				window.location.reload();
			}
		});

	});

	$('#popup .option_add .ins').focus(function(){
		curOrder = $(this).closest('.order');
	});

	$('#popup .option_add .ins').keyup(function(e){
		if(e.keyCode == 13) {
			var sRes = '<div class="option">' +
				'<span class="otitle">' + $(this).val() + '</span>' +
				'<a href="#" class="option_del">Удалить</a>' +
				'</div>';
			curOrder.find('.options').append(sRes);
			$('#popup .option_add .ins').val('');
			delOption();
		}
	});

	$("#popup .option_add .ins").each(function(){
		var pId = parseInt($(this).closest('.order').find('.setId').val());
		/**
		 * Обрабатываем каждую опцию
		 */
		var data = [];
		/**
		 * Разбираем каждую опцию
		 */
		for(oId in ProdData[pId].options) {
			var option = ProdData[pId].options[oId];
			/**
			 * Разбираем каждое значение
			 */
			for(vId in option) {
				var value = option[vId];
				data[data.length] = [value.title, value.price, pId, oId, vId];
			}
		}

		$(this).autocompleteArray(data,	{
			delay:10,
			minChars:1,
			matchSubset:1,
			autoFill:true,
			matchContains:1,
			cacheLength:10,
			selectFirst:true,
			formatItem:liFormat,
			maxItemsToShow:10,
			onItemSelect:selectItem
		});
	});
}

function delOption() {
	$('#popup').find('.option_del').click(function(){
		var options = $(this).closest('.options');
		var summ = $(this).closest('.order').find('.pprice');
		var option = $(this).closest('.option');
		var price = option.find('.oprice');
		if(price.length) {
			summ.val(parsePrice(summ.val()) - parsePrice(price.val()));
		}
		option.remove();
	});
}

function liFormat (row, i, num) {
	var result = row[0] + '<p class=qnt>' + row[1] + ' </p>';
	return result;
}

function selectItem(li) {
	if( li == null ) var sValue = 'А ничего не выбрано!';
	if( !!li.extra ) {
		var pId = li.extra[1];
		var oId = li.extra[2];
		var vId = li.extra[3];
		var option = ProdData[pId]['options'][oId][vId];
		var sRes = '<div class="option">' +
			'<input type="hidden" class="oid" value="' + oId + '" />' +
			'<input type="hidden" class="vid" value="' + vId + '" />' +
			'<input type="hidden" class="oprice" value="' + option.price + '" />' +
			'<span class="otitle">' + option.title + '</span>' +
			'<a href="#" class="option_del">Удалить</a>' +
			'</div>';
		curOrder.find('.options').append(sRes);
		delOption();
		var newPrice = parsePrice(curOrder.find('.pprice').val()) + parsePrice(option.price);
		curOrder.find('.pprice').val(newPrice);
		$('#popup .option_add .ins').val('');
	}
}

function parsePrice(price) {
	return parseInt(price.toString().replace(' ', ''));
}