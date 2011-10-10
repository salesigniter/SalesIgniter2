function getParams(add){
	var getParams = [];
	getParams.push('appExt=rentalProducts');
	getParams.push('app=rental_report');
	getParams.push('appPage=default');

	if (add && add.length > 0){
		$.each(add, function (){
			getParams.push(this);
		});
	}
	return getParams.join('&');
}

$(document).ready(function (){
	$('tbody > .ui-grid-row')
		.die('click')
		.die('mouseover')
		.die('mouseout');
	
	$('.makeDatepicker').datepicker();
	
	$('.ui-icon-cancel').click(function (){
		$(this).parent().find('input').val('');
		$(this).parent().find('select').val('');
		$('.applyFilterButton').click();
	});
	
	$('.applyFilterButton').click(function (){
		var getVars = [];
		var ignoreParams = ['action'];
		$(this).parent().parent().find('input, select').each(function (){
			if ($(this).val() != ''){
				getVars.push($(this).attr('name') + '=' + $(this).val());
			}
			ignoreParams.push($(this).attr('name'));
		});
		js_redirect(js_app_link(js_get_all_get_params(ignoreParams) + getVars.join('&')));
	});
	
	$('.sendButton').live('click', function (){
		var self = this;
		liveMessage(jsLanguage.get('TEXT_INFO_SENDING_RENTAL'));
		$.getJSON(js_app_link(getParams(['action=send'])), {
			'orders_products_rentals_id': $(this).parent().parent().data('orders_products_rentals_id')
		}, function (Resp){
			liveMessage(Resp.statusMsg);
			$(self).parentsUntil('tbody').last().find('.column-rental_state').html(Resp.rental_state);
			$(self).parentsUntil('tbody').last().find('.column-date_shipped').html(Resp.date_shipped);
			$(self).remove();
		});
	});
	
	$('.returnButton').live('click', function (){
		var self = this;
		liveMessage(jsLanguage.get('TEXT_INFO_RETURNING_RENTAL'));
		$.getJSON(js_app_link(getParams(['action=return'])), {
			'orders_products_rentals_id': $(this).parentsUntil('tbody').last().data('orders_products_rentals_id')
		}, function (Resp){
			liveMessage(Resp.statusMsg);
			$(self).parentsUntil('tbody').last().find('.column-rental_state').html(Resp.rental_state);
			$(self).parentsUntil('tbody').last().find('.column-date_returned').html(Resp.date_returned);
			$(self).remove();
		});
	});

	var $returnDialog;
	function updateDialogClose(){
		if ($returnDialog.find('input[name="items[]"]').size() > 0){
			$returnDialog.dialog('option', 'allowClose', false);
		}else{
			$returnDialog.dialog('option', 'allowClose', true);
		}
	}

	function validateBarcode(barcode, $row){
		$row.append('<span class="validating"> - <span class="ui-ajax-loader ui-ajax-loader-small" style="display:inline-block;"></span>Validating Barcode</span>');
		$.getJSON(js_app_link(getParams(['action=validateBarcode', 'code=' + barcode])), function (data){
			$row.find('.validating').remove();
			if (data.isValid === false){
				$row.append('<span style="color:red;"> - Barcode Not Currently Out, Please Remove</span>');
			}else{
				$row.find('input[name="items[]"]').val(data.oprId);
			}
		});
	}

	$('.returnBarcodeButton').click(function (){
		var html = '<div title="Return Products">' +
			'<div style="text-align: center;font-size: 0.75em;">' +
			'<b>Handheld Scanner:</b> Click Field Below And Begin Scanning Barcodes To Add Items<br>' +
			'<b>By Hand:</b> Click Field Below, Enter Barcode And Hit Enter Button To Add Items<br>' +
			'<input type="text" name="barcode_scanned">' +
			'</div>' +
			'<div id="returnItems"></div>' +
			'</div>';
		$returnDialog = $(html).dialog({
			height: 500,
			minWidth: 500,
			buttons: {
				'Process Returns': function (){
					var self = this;
					$.post(
						js_app_link(getParams(['action=processReturns'])),
						$(self).find('input, select').serialize(),
						function (data){
							if (data.hasErrors == true){
								alert(data.errorMessage);
							}else{
								alert('Returns Processed');
								$(self).dialog('close').remove();
								js_redirect(js_app_link(getParams()));
							}
						},
						'json'
					);
				}
			}
		});

		$returnDialog.find('input[name=barcode_scanned]').keypress(function (e){
			if (e.which == 13){
				var enteredBarcode = $(this).val();
				var newRow = $('<div>' +
					'<span class="ui-icon ui-icon-close" style="display:inline-block;" tooltip="Remove From List"></span>' +
					$(this).val() +
					'<input type="hidden" name="items[]" value="' + enteredBarcode + '">' +
					'</div>');
				newRow.find('.ui-icon-close').click(function (){
					$(this).parent().remove();
					updateDialogClose();
				});
				$returnDialog.find('#returnItems').append(newRow);
				$(this).val('');

				updateDialogClose();
				validateBarcode(enteredBarcode, newRow);
			}
		});
	});
});