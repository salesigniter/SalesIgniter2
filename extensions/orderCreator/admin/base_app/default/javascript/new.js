function removeFromCart(options){
	$.ajax({
		cache : false,
		dataType : 'json',
		url : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=removeProductRow&id=' + options.id),
		beforeSend : options.beforeSend || function () {
		},
		success : options.onSuccess || function (data) {
		}
	});
}

function recheckInventory(id){
	var $Row = $('.productsTable').find('tr[data-id="' + id + '"]');
	$.ajax({
		cache : false,
		dataType : 'json',
		url : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=checkInventory&id=' + id),
		beforeSend : function () {
			showAjaxLoader($Row, 'small');
		},
		success : function (data) {
			removeAjaxLoader($Row);
			if (data.hasNoInv.length > 0){
				$.each(data.hasNoInv, function (){
					$Row.addClass('noInventory');
				});
				if (confirm(data.confirmMessage) === false){
					removeFromCart({
						id: id,
						onSuccess: function (){
							$Row.remove();
						}
					});
				}
			}
		}
	});
}

$(document).ready(function () {
	$.addslashes = function (str) {
		return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
	};

	$(document).delegate('select[name=payment_method]', 'change', function () {
		var $self = $(this);
		showAjaxLoader($self, 'small');

		$.ajax({
			url : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=getPaymentEntryTable'),
			cache : false,
			dataType : 'json',
			data : 'payment_method=' + $self.val(),
			type : 'post',
			success : function (data) {
				if (data.success == true){
					$('#paymentFields').html(data.table);
					$('#paymentQueue').show();
				}
				removeAjaxLoader($self);
			}
		});
	});

	$(document).delegate('.addPaymentQueueButton', 'click', function () {
		var k = $('.processQueue tbody tr').size();

		var Row = '<tr>';
		Row += '<td>' + $('select[name=payment_method] option:selected').html() + '<input type="hidden" name="paymentQueue[' + k + '][payment_method]" value="' + $('select[name=payment_method]').val() + '"></td>';
		Row += '<td>' + $('input[name=payment_amount]').val() + '<input type="hidden" name="paymentQueue[' + k + '][payment_amount]" value="' + $('input[name=payment_amount]').val() + '"></td>';
		if ($('select[name=cardType]').size() > 0){
			Row += '<td>' + $('select[name=cardType] option:selected').html() + '<input type="hidden" name="paymentQueue[' + k + '][cardType]" value="' + $('select[name=cardType]').val() + '"></td>';
		}
		else {
			Row += '<td>&nbsp;</td>';
		}
		if ($('input[name=cardNumber]').size() > 0){
			Row += '<td>' + $('input[name=cardNumber]').val() + '<input type="hidden" name="paymentQueue[' + k + '][cardNumber]" value="' + $('input[name=cardNumber]').val() + '"></td>';
		}
		else {
			Row += '<td>&nbsp;</td>';
		}
		if ($('select[name=cardExpMonth]').size() > 0){
			Row += '<td>' + $('select[name=cardExpMonth] option:selected').html() + ' / ' + $('select[name=cardExpYear] option:selected').html() + '<input type="hidden" name="paymentQueue[' + k + '][cardExpMonth]" value="' + $('select[name=cardExpMonth]').val() + '"><input type="hidden" name="paymentQueue[' + k + '][cardExpYear]" value="' + $('select[name=cardExpYear]').val() + '"></td>';
		}
		else {
			Row += '<td>&nbsp;</td>';
		}
		if ($('input[name=cardCvvNumber]').size() > 0){
			Row += '<td>' + $('input[name=cardCvvNumber]').val() + '<input type="hidden" name="paymentQueue[' + k + '][cardCvvNumber]" value="' + $('input[name=cardCvvNumber]').val() + '"></td>';
		}
		else {
			Row += '<td>&nbsp;</td>';
		}
		Row += '<td>' + $('textarea[name=payment_comments]').val() + '<input type="hidden" name="paymentQueue[' + k + '][payment_comments]" value="' + $.addslashes($('textarea[name=payment_comments]').val()) + '"></td>';
		Row += '<td><span class="ui-icon ui-icon-closethick" tooltip="Remove From Queue"></span></td>';
		Row += '</tr>';

		var $Row = $(Row);
		$('.processQueue tbody').append($Row);
		$Row.find('.ui-icon-closethick').click(function (){
			$(this).parent().parent().remove();
		});
	});

	$('.resReports').click(function () {
		var newwindow = window.open(js_app_link('appExt=payPerRentals&app=reservations_reports&appPage=default'), 'name', 'height=700,width=960');
		if (window.focus){
			newwindow.focus()
		}
		return false;
	});

	$('#emailEstimate').click(function () {
		$.ajax({
			url : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=sendEstimateEmail'),
			cache : false,
			dataType : 'json',
			data : 'email=' + $('#emailInput').val() + '&oID=' + getVars['oID'],
			type : 'post',
			success : function (data) {
				if (data.success == true){
					alert('Estimate Sent!');
				}
				else {
					alert('Email not sent. Please Check email address');
				}
			}
		});
		return false;
	});

	$('input[name=customer_search]').autocomplete({
		html : true,
		source : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=findCustomer'),
		select : function (e, ui) {
			if (ui.item.value == 'no-select'){
				return false;
			}
			if (ui.item.value == 'disabled'){
				alert(ui.item.reason);
				return false;
			}

			showAjaxLoader($('.addressTable'), 'xlarge');
			$.ajax({
				cache : false,
				dataType : 'json',
				url : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=loadCustomerInfo&cID=' + ui.item.value),
				success : function (data) {
					removeAjaxLoader($('.addressTable'));

					$('.customerAddress').html(data.customer);
					$('.billingAddress').html(data.billing);
					$('.deliveryAddress').html(data.delivery);
					$('.pickupAddress').html(data.pickup);
					$.each(data.field_values, function (k, v) {
						$('*[name=' + k + ']').val(v);
					});
					$('input[name=account_password]').attr('disabled', 'disabled');
					$('input[name=member_number]').attr('disabled', 'disabled');

					$('.productSection, .totalSection, .paymentSection, .commentSection, .statusSection, .trackingSection').show();

					if (data.productTable){
						$('.productTable').replaceWith(data.productTable);
					}

					if (data.orderTotalTable){
						$('.orderTotalTable').replaceWith(data.orderTotalTable);
					}

					if (data.paymentsTable){
						$('.paymentsTable').replaceWith(data.paymentsTable);
					}
					//$('.purchaseType').trigger('change');
				}
			});
			var Label = $('<div>' + ui.item.label + '</div>');
			$(this).val(Label.find('span').eq(2).html() + ' ' + Label.find('span').eq(3).html());
			return false;
		}
	});

	$('.customerSearchReset').click(function () {
		$('.addressTable').find('input').val('');
		$('.addressTable').find('select').val('');
		$('input[name=customer_search]').val('');
		$('input[name=email]').val('');
		$('input[name=telephone]').val('');
		$('input[name=account_password]').removeAttr('disabled');

		$('.productSection, .totalSection, .paymentSection, .commentSection, .statusSection, .trackingSection').hide();
	});

	$('.purchaseType').live('change', function () {
		var self = this;
		var $Row = $(self).parentsUntil('tbody').last();
		var prType = $(self).val();

		showAjaxLoader($Row, 'normal');
		$.ajax({
			url : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=updateOrderProduct&id=' + $Row.attr('data-id') + '&purchase_type=' + $(this).val()),
			cache : false,
			dataType : 'json',
			success : function (data) {
				if (data.hasError == true){
					alert(data.errorMessage);
					$(self).val('');
				}
				else {
					$Row.find('td:eq(1)').html(data.name);
					$Row.find('td:eq(2)').html(data.barcodes);
					$Row.find('td:eq(2)').find('select').combobox();

					$Row.find('.priceEx').val(data.price).trigger('keyup');
					var isEvent = false;
					if ($('.eventf').size() > 0){
						isEvent = true;
					}
					if (prType == 'reservation' && isEvent == false){
						$('.productQty').attr('readonly', 'readonly');
					}
					if (isEvent && $Row.find('.eventf').val() != '0'){
						$('.reservationShipping').trigger('change');
					}
				}
				removeAjaxLoader($Row);
			}
		})
	});

	$('.taxRate').live('keyup', function () {
		var $Row = $(this).parent().parent();
		var Quantity = parseFloat($Row.find('.productQty').val());
		var TaxRate = parseFloat($(this).val());
		var Price = parseFloat($Row.find('.priceEx').val());

		$Row.find('.priceIn').html(jsCurrencies.format(Price + (Price * (TaxRate / 100))));
		$Row.find('.priceInTotal').html(jsCurrencies.format(((Price * Quantity) + ((Price * Quantity) * (TaxRate / 100)))));

		var $TotalRow = null;
		var total = 0;
		var subtotal = 0;
		var tax = 0;
		$('.priceEx').each(function () {
			var Quantity = parseFloat($(this).parent().parent().find('.productQty').val());
			var Price = parseFloat($(this).val());

			subtotal += Price * Quantity;
			tax += (Price * Quantity) * (parseFloat($(this).parent().parent().find('.taxRate').val()) / 100);
		});

		var passedSubtotal = false;
		$('.orderTotalTable  .grid > tbody > tr').each(function () {
			var $Row = $(this);
			switch($Row.find('.orderTotalType').val()){
				case 'subtotal':
					$Row.find('.orderTotalValue').val(number_format(subtotal));
					total += subtotal;
					passedSubtotal = true;
					break;
				case 'tax':
					$Row.find('.orderTotalValue').val(number_format(tax));
					total += tax;
					break;
				case 'total':
					$TotalRow = $(this);
					break;
				default:
					var isTotalRow = false;
					if ($Row.find('.orderTotalType').is('div')){
						if ($Row.find('.orderTotalType > input:hidden').val() == 'total'){
							$TotalRow = $Row;
							isTotalRow = true;
						}
					}

					if (isTotalRow === false){
						var orderTotalValue = 0;
						if ($Row.find('.orderTotalValue').is('div')){
							orderTotalValue = parseFloat($Row.find('.orderTotalValue > input:hidden').val());
						}
						else {
							orderTotalValue = parseFloat($Row.find('.orderTotalValue').val());
						}

						if (passedSubtotal === false){
							subtotal += orderTotalValue;
						}
						else {
							total += orderTotalValue;
						}
					}
					break;
			}
		});

		if ($TotalRow){
			if ($TotalRow.find('.orderTotalValue').is('div')){
				$TotalRow.find('.orderTotalValue span').html(jsCurrencies.format(total));
				$TotalRow.find('.orderTotalValue input').val(number_format(total));
			}else{
				$TotalRow.find('.orderTotalValue').val(number_format(total));
			}
		}
	})

	$('.priceEx').live('keyup', function () {
		var $Row = $(this).parent().parent();
		var Quantity = parseFloat($Row.find('.productQty').val());
		var TaxRate = parseFloat($Row.find('.taxRate').val());
		var Price = parseFloat($(this).val());

		$Row.find('.priceExTotal').html(jsCurrencies.format(Price * Quantity));
		$Row.find('.taxRate').trigger('keyup');
	})

	$('.productQty').live('keyup', function () {
		var $Row = $(this).parent().parent();
		$Row.find('.priceEx').trigger('keyup');
	});

	function updateOrderTotalSortOrder(){
		var newSort = 0;
		$('.totalSortOrder').each(function (k, el){
			$(el).val(++newSort);
		});
	}

	function getTotalRow(type) {
		var $totalRow = null;
		$('.orderTotalType').each(function(){
			if ($(this).val() == type){
				$totalRow = $(this).parentsUntil('tbody').last();
			}
		});
		return $totalRow;
	}

	var TotalsGrid = $('.orderTotalTable');
	TotalsGrid.newGrid('option', 'buttons', [
		{
			selector          : '.addOrderTotalButton',
			disableIfNone     : false,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				var $selectBox = GridClass.GridElement.find('.orderTotalType:first');

				var count = parseInt(GridClass.GridElement.attr('data-next_id'));
				GridClass.GridElement.attr('data-next_id', count + 1);

				GridClass.addBodyRow({
					prepend: true,
					rowAttr: {
						'data-count': count
					},
					columns: [
						{
							text: '<input class="ui-widget-content" type="text" name="order_total[' + count + '][title]" value="">'
						},
						{
							text: '<input class="ui-widget-content orderTotalValue" type="text" size="10" name="order_total[' + count + '][value]" value="0"><input type="hidden" name="order_total[' + count + '][sort_order]" class="totalSortOrder">'
						},
						{
							text: '<select name="order_total[' + count + '][type]" class="orderTotalType">' + $selectBox.html() + '</select>'
						}
					]
				});
				updateOrderTotalSortOrder();
			}
		},
		{
			selector          : '.deleteOrderTotalButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				GridClass.getSelectedRows().each(function (){
					$(this).remove();
				});
				updateOrderTotalSortOrder();
			}
		},
		{
			selector          : '.moveOrderTotalButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				var $Rows = GridClass.getSelectedRows();
				var direction = $(this).data('direction');
				if (direction == 'up'){
					$Rows.insertBefore($Rows.prev());
				}else{
					$Rows.insertAfter($Rows.next());
				}
				updateOrderTotalSortOrder();
			}
		}
	]);

	$('.orderTotalType').live('change', function () {
		var $self = $(this);
		if ($self.val() == 'shipping'){
			showAjaxLoader($self.parent().parent(), 'small');
			$.ajax({
				cache : false,
				dataType : 'html',
				url : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=getShippingQuotes&totalCount=' + $self.parent().parent().attr('data-count')),
				success : function (data) {
					$self.parent().parent().find('td:eq(0)').html(data);
					removeAjaxLoader($self.parent().parent());
				}
			});
		}
		else {
			$self.parent().parent().find('td:eq(0)').html('<input class="ui-widget-content" type="text" style="width:98%;" name="order_total[' + $self.parent().parent().attr('data-count') + '][title]" value="">');
		}
	});

	$('.orderTotalValue').live('keyup', function () {
		var total = 0;
		$('.orderTotalValue').each(function () {
			var elName = $(this).parent().parent().find('.orderTotalType').val();
			if (elName == 'total'){
				return;
			}

			if ($(this).is('div')){
				total += parseFloat($(this).find('input:hidden').val());
			}
			else {
				total += parseFloat($(this).val());
			}
		});
		var $totalRow = getTotalRow('total');
		if ($totalRow.find('.orderTotalValue').is('div')){
			$totalRow.find('.orderTotalValue span').html(total);
			$totalRow.find('.orderTotalValue input').val(total);
		}else{
			$totalRow.find('.orderTotalValue').val(total);
		}
	});

	$('.deleteIcon').live('click', function () {
		if (this.Tooltip){
			this.Tooltip.remove();
		}
		$(this).parent().parent().remove();
	});

	$('select.country').live('change', function () {
		var $self = $(this);
		showAjaxLoader($self, 'small');
		$.ajax({
			url : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=getCountryZones&addressType=' + $self.attr('data-address_type') + '&country=' + $self.val()),
			cache : false,
			dataType : 'html',
			success : function (html) {
				$self.parent().parent().parent().find('.stateCol').html(html);
				removeAjaxLoader($self);
			}
		});
	});

	/*
	 Theres no reason to trigger these, as the correct information is already displayed when the order is loaded,
	 and for new orders this is triggered using the live method
	 */
	//$('select[name=payment_method]').trigger('change');
	//$('.purchaseType').trigger('change');
	//$('select.barcode').combobox();

	$('.paymentSection .gridBodyRow').click(function (){
		if ($(this).hasClass('state-active')) return;

		var $ButtonBar = $(this).parentsUntil('.paymentSection').last().find('.gridButtonBar');
		if ($(this).data('can_refund') == true){
			$ButtonBar.find('.paymentRefundButton').button('enable');
		}

		if ($(this).data('can_void') == true){
			$ButtonBar.find('.paymentVoidButton').button('enable');
		}
	});

	$('.paymentRefundButton').click(function () {
		var $self = $(this);
		showAjaxLoader($self, 'small');

		$('<div id="popupRefund"></div>').dialog({
			autoOpen : true,
			width : 300,
			height : 150,
			close : function (e, ui) {
				$(this).dialog('destroy').remove();
				removeAjaxLoader($self);
			},
			open : function (e, ui) {
				$(e.target).html('Refund Amount: <input id="refundedAmount" name="refundedAmount">');

			},
			buttons : {
				'Save' : function () {
					//ajax call to save comment on success
					var dialog = $(this);
					var $SelectedRow = $('.paymentSection .gridBodyRow.state-active');
					$.ajax({
						url : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=refundPayment'),
						cache : false,
						dataType : 'json',
						data : 'payment_module=' + $SelectedRow.data('payment_module') + '&payment_history_id=' + $SelectedRow.data('payment_history_id') + '&amount=' + $('#refundedAmount').val(),
						type : 'post',
						success : function (data) {
							if (data.success == true){
								$('.paymentsTable tbody').append(data.tableRow);
							}
							else {
								if (typeof data.success == 'object'){
									alert(data.success.error_message);
								}
								else {
									alert('Payment Failed');
								}
							}
							removeAjaxLoader($self);
							dialog.dialog('close');
						}
					});
				},
				Cancel : function () {
					$(this).dialog('close');
					removeAjaxLoader($self);
				}
			}
		});

	});

	$('.saveAddressButton').click(function () {
		showAjaxLoader($('.customerSection'), 'xlarge');
		$.ajax({
			cache : false,
			dataType : 'html',
			url : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=saveCustomerInfo'),
			data : $('.customerSection *').serialize(),
			type : 'post',
			success : function (data) {
				$('.productSection, .totalSection, .paymentSection, .commentSection, .statusSection, .trackingSection').show();
				removeAjaxLoader($('.customerSection'));
			}
		});
	});

	$('.addressCopyButton').live('click', function () {
		var copyFrom = $(this).data('copy_from');
		var copyTo = $(this).data('copy_to');

		$('input[name="address[' + copyTo + '][entry_name]"]').val($('input[name="address[' + copyFrom + '][entry_name]"]').val());
		$('input[name="address[' + copyTo + '][entry_company]"]').val($('input[name="address[' + copyFrom + '][entry_company]"]').val());
		$('input[name="address[' + copyTo + '][entry_street_address]"]').val($('input[name="address[' + copyFrom + '][entry_street_address]"]').val());
		$('input[name="address[' + copyTo + '][entry_suburb]"]').val($('input[name="address[' + copyFrom + '][entry_suburb]"]').val());
		$('input[name="address[' + copyTo + '][entry_city]"]').val($('input[name="address[' + copyFrom + '][entry_city]"]').val());
		$('input[name="address[' + copyTo + '][entry_postcode]"]').val($('input[name="address[' + copyFrom + '][entry_postcode]"]').val());
		$('select[name="address[' + copyTo + '][entry_country]"]').val($('select[name="address[' + copyFrom + '][entry_country]"]').val());

		if ($('input[name="address[' + copyFrom + '][entry_state]"]').size() > 0){
			$('input[name="address[' + copyTo + '][entry_state]"]').val($('input[name="address[' + copyFrom + '][entry_state]"]').val());
		}
		else {
			if ($('select[name="address[' + copyFrom + '][entry_state]"]').size() > 0){
				var stateCopyTo = $('select[name="address[' + copyFrom + '][entry_state]"]').clone(true);
				stateCopyTo.attr('name', 'address[' + copyTo + '][entry_state]');
				stateCopyTo.val($('select[name="address[' + copyFrom + '][entry_state]"]').val());

				if ($('input[name="address[' + copyTo + '][entry_state]"]').size() > 0){
					$('input[name="address[' + copyTo + '][entry_state]"]').replaceWith(stateCopyTo);
				}
				else {
					if ($('select[name="address[' + copyTo + '][entry_state]"]').size() > 0){
						$('select[name="address[' + copyTo + '][entry_state]"]').replaceWith(stateCopyTo);
					}
				}
			}
		}
	});

	if (!$_GET['error'] && !$_GET['sale_id']){
		//$('.productSection, .totalSection, .paymentSection, .commentSection, .statusSection, .trackingSection').hide();
	}

	$('button[name=print]').click(function (e){
		e.preventDefault();
		window.open(js_app_link('appExt=orderCreator&app=default&appPage=new&action=print&type=' + $(this).val() + '&' + $(this).data('print_vars')));
	});

	$('.loadRevision').change(function (){
		js_redirect(js_app_link(js_get_all_get_params() + '&rev=' + $(this).val()));
	});
});