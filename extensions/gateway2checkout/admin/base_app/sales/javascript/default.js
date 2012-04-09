$(document).ready(function (){
	$('.gridBody > .gridBodyRow').click(function (){
		if ($(this).hasClass('state-active')) return;

		$('.gridButtonBar').find('button').button('enable');
	});

	$('.gridButtonBar').find('.detailsButton').click(function (){
		var saleId = $('.gridBodyRow.state-active').attr('data-sale_id');
		js_redirect(js_app_link('appExt=gateway2checkout&app=sales&appPage=details&saleId=' + saleId));
	});
});