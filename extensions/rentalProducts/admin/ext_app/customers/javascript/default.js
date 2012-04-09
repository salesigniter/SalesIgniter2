$(document).ready(function (){
	$('.gridBody > .gridBodyRow').click(function (){
		if ($(this).hasClass('state-active')) return;

		if ($(this).attr('data-has_queue') == 'false'){
			$('.gridButtonBar').find('.rentalQueueButton').button('disable');
		}
	});

	$('.gridButtonBar').find('.rentalQueueButton').click(function (){
		var customerId = $('.gridBodyRow.state-active').attr('data-customer_id');
		js_redirect(js_app_link('appExt=rentalProducts&app=rentalQueue&appPage=default&cID=' + customerId));
	});
});