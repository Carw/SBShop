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

function showDate(date) {
    alert(date);
}

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

	$(".sb_date_next").dynDateTime({
		showsTime: true,
		timeFormat: 24,
		ifFormat: "%H,%M,%m,%d,%Y",
		daFormat: "%l;%M %p, %e %m,  %Y",
		flat: ".next()"
	});

});