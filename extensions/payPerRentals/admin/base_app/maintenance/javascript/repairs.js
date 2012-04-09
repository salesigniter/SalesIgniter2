
$(document).ready(function (){
	$('#maintenance_selectbox').change(function(){
		if($(this).val() != '-1'){
			window.location = js_app_link('appExt=payPerRentals&app=maintenance&appPage=default&type='+$(this).val());
		}else{
			window.location = js_app_link('appExt=payPerRentals&app=maintenance&appPage=repairs');
		}
	});

	$('.makeFCK').each(function (){
		CKEDITOR.replace(this, {
			toolbar : 'Simple'
		});
	});
	$('.deleteIconHidden').live('click', function (){
		$(this).parent().parent().remove();
	});

	$(this).find('.insertIconHidden').click(function () {
		var nextId = $(this).parent().parent().parent().parent().parent().attr('data-next_id');
		var langId = $(this).parent().parent().parent().parent().parent().attr('language_id');
		$(this).parent().parent().parent().parent().parent().attr('data-next_id', parseInt(nextId) + 1);


		var $td2 = $('<div style="float:left;width:100px;"></div>').attr('align', 'center').append('<input class="ui-widget-content part_name" size="15" type="text" name="parts[' + nextId + '][part_name]">');
		var $td5 = $('<div style="float:left;width:100px;"></div>').attr('align', 'center').append('<input class="ui-widget-content" size="15" type="text" name="parts[' + nextId + '][part_price]">');
		var $td9 = $('<div style="float:left;width:40px;"></div>').attr('align', 'center').append('<a class="ui-icon ui-icon-closethick deleteIconHidden"></a>');
		var $newTr = $('<li style="list-style:none"></li>').append($td2).append($td5).append($td9).append('<br style="clear:both;"/>');//<input type="hidden" name="sortvprice[]">
		$(this).parent().parent().parent().parent().parent().find('.hiddenList').append($newTr);


	});



});