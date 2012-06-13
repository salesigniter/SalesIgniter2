$(document).ready(function (){
	$('.orderCustomField').blur(function (){
		$.ajax({
			cache: false,
			url: js_app_link('appExt=orderCreator&app=default&appPage=new&action=updateOrderCustomFields'),
			data: $('.orderCustomField').serialize(),
			type: 'post',
			success: function (data){
			}
		});
	});
});