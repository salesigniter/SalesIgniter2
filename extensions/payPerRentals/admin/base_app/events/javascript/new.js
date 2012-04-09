function makeAutocomplete(el){
	el.each(function (){
		var self = this;
		$(this).autocomplete({
			source: js_app_link('appExt=payPerRentals&app=events&appPage=new&action=getModels'),
			minLength: 0,
			select: function (event, ui){
				$.ajax({
					cache: false,
					url: js_app_link('appExt=payPerRentals&app=events&appPage=new&action=getProductInventory&model=' + ui.item.label),
					dataType: 'json',
					success: function (data){
						$(self).parentsUntil('tbody').last().find('.availableInventory').html(data.inventory);
					}
				})
			}
		});
	});
}

$(document).ready(function (){
	$('#countryDrop').change(function (){
		var $stateColumn = $('#stateCol');
		showAjaxLoader($stateColumn, 'icon', 'append');

		$.ajax({
			cache: true,
			url: js_app_link('appExt=payPerRentals&app=events&appPage=default&rType=ajax&action=getCountryZones'),
			data: 'cID=' + $(this).val() + '&zName='+$('#ezone').val(),
			dataType: 'html',
			success: function (data){
				removeAjaxLoader($stateColumn);
				$('#stateCol').html(data);
			}
		});
	});
	$('#countryDrop').val('223').trigger('change');
	$('#tab_container').tabs();
	$('#events_date').datepicker({dateFormat: 'yy-mm-dd'});
	$('.makeFCK').each(function (){
		CKEDITOR.replace(this);
	});

	$('.deleteIconHidden').live('click', function (){
		$(this).parentsUntil('tbody').last().remove();
	});

	$(this).find('.insertIcon').click(function () {
		var nextId = $('.EventsProductsTable').data('next_id');
		var langId = $('.EventsProductsTable').attr('language_id');
		$('.EventsProductsTable').data('next_id', parseInt(nextId) + 1);

		var $newTr = $('<tr>' +
			'<td>' +
			'<input class="prod_model" size="15" type="text" name="event_products[' + nextId + '][products_model]">' +
			'</td>' +
			'<td align="center">' +
			'<input class="ui-widget-content" size="15" type="text" name="event_products[' + nextId + '][qty]">' +
			'</td>' +
			'<td align="center" class="availableInventory">' +
			'</td>' +
			'<td align="right">' +
			'<a class="ui-icon ui-icon-closethick deleteIconHidden"></a>' +
			'</td>' +
			'</tr>');

		$('.EventsProductsTable').find('tbody').append($newTr);

		makeAutocomplete($newTr.find('.prod_model'));
	});

	makeAutocomplete($('.EventsProductsTable').find('.prod_model'));
});