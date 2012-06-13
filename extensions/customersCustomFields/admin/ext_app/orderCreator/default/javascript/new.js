$(document).ready(function (){
	$('.customerCustomField').blur(function (){
		$.ajax({
			cache: false,
			url: js_app_link('appExt=orderCreator&app=default&appPage=new&action=updateCustomerCustomFields'),
			data: $('.customerCustomField').serialize(),
			type: 'post',
			success: function (data){
			}
		});
	});
});