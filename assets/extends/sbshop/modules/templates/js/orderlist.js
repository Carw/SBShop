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

});