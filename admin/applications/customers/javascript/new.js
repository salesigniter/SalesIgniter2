$(document).ready(function () {
	$('.makeDatepicker').datepicker({
		dateFormat : 'mm/dd/yy',
		yearRange  : '-100:-5',
		changeYear : true
	});

	$('#orderHistoryTab').find('.gridBody > .gridBodyRow').click(function () {
		if ($(this).hasClass('state-active')){
			return;
		}

		$('#orderHistoryTab').find('.gridButtonBar button').button('enable');
	});

	$('#orderHistoryTab').find('.gridButtonBar .detailsButton').click(function () {
		var saleId = $('#orderHistoryTab').find('.gridBodyRow.state-active').attr('data-sale_id');
		js_redirect(js_app_link('app=accounts_receivable&appPage=details&sale_id=' + saleId));
	});

	$('#orderHistoryTab').find('.gridButtonBar .invoiceButton').click(function () {
		var orderId = $('#orderHistoryTab').find('.gridBodyRow.state-active').attr('data-order_id');
		js_redirect(js_app_link('app=orders&appPage=invoice&oID=' + orderId));
	});

	$('#orderHistoryTab').find('.gridButtonBar .packingSlipButton').click(function () {
		var orderId = $('#orderHistoryTab').find('.gridBodyRow.state-active').attr('data-customer_id');
		js_redirect(js_app_link('app=orders&appPage=packingslip&oID=' + orderId));
	});
	$('select[name="activate"]').live('change', function () {
		fnClicked();
	});
	$('input[name="make_member"]').live('change', function () {
		if ($('input[name="make_member"]').is(':checked') == true){
			$('select[name="activate"]').removeAttr('disabled');
			$('select[name="activate"]').val('Y');
		}
		else {
			$('select[name="activate"]').attr('disabled', 'disabled');
			$('select[name="activate"]').val('N');

		}
		fnClicked();
	});
	$('select[name="payment_method"]').trigger('change');
	$('select[name="activate"]').trigger('change');
	$('select[name=country]').live('change', function () {

		var stateType = 'state';
		var $stateColumn = $('#' + stateType);
		if ($stateColumn.size() > 0){
			showAjaxLoader($stateColumn, 'large');
			$.ajax({
				url      : js_app_link('app=customers&appPage=new&action=getCountryZones'),
				cache    : false,
				dataType : 'html',
				data     : 'cID=' + $(this).val() + '&state_type=' + stateType + '&state=' + $stateColumn.val(),
				success  : function (data) {
					removeAjaxLoader($stateColumn);
					$('#' + stateType).replaceWith(data);
				}
			});
		}
	});
	$('select[name=country]').trigger('change');
});
