$(document).ready(function (){
	$('#date_from').datepicker({
		altField: 'input[name=date_from]',
		dateFormat: 'yy-mm-dd'
	});
	$('#date_to').datepicker({
		altField: 'input[name=date_to]',
		dateFormat: 'yy-mm-dd'
	});

	$('.genInvoice').click(function (){
		var $button = $(this);
		var $Row = $button.parentsUntil('tbody').last().next();

		var owed = {
			royalty: parseFloat($Row.find('.feeOwedRoyalty').data('float_val')),
			management: parseFloat($Row.find('.feeOwedManagement').data('float_val')),
			marketing: parseFloat($Row.find('.feeOwedMarketing').data('float_val')),
			labor: parseFloat($Row.find('.feeOwedLabor').data('float_val')),
			parts: parseFloat($Row.find('.feeOwedParts').data('float_val'))
		};

		$('<div></div>').dialog({
			title: 'Generate Invoice',
			minWidth: 625,
			open: function (){
				$(this).html('<table>' +
					'<tr>' +
						'<td valign="top" width="300">' +
							'<table width="100%">' +
								'<tr>' +
									'<td colspan="2" align="center"><b>Owed</b></td>' +
								'</tr>' +
								'<tr>' +
									'<td>Royalty: </td>' +
									'<td>' + $Row.find('.feeOwedRoyalty').html() + '</td>' +
								'</tr>' +
								'<tr>' +
									'<td>Management: </td>' +
									'<td>' + $Row.find('.feeOwedManagement').html() + '</td>' +
								'</tr>' +
								'<tr>' +
									'<td>Marketing: </td>' +
									'<td>' + $Row.find('.feeOwedMarketing').html() + '</td>' +
								'</tr>' +
								'<tr>' +
									'<td>Labor: </td>' +
									'<td>' + $Row.find('.feeOwedLabor').html() + '</td>' +
								'</tr>' +
								'<tr>' +
									'<td>Parts: </td>' +
									'<td>' + $Row.find('.feeOwedParts').html() + '</td>' +
								'</tr>' +
							'</table>' +
						'</td>' +
						'<td valign="top" width="300">' +
							'<table width="100%">' +
								'<tr>' +
									'<td colspan="2" align="center">' +
										'<b>Invoice</b>' +
										'<input type="hidden" name="store_id" value="' + $button.data('store_id') + '">' +
									'</td>' +
								'</tr>' +
								'<tr>' +
									'<td valign="top">Royalty: </td>' +
									'<td>' +
										'<input type="text" name="royalty_billed" value="' + owed.royalty + '">' +
										'<input type="hidden" name="royalty_owed" value="' + owed.royalty + '">' +
										'<div class="alertMessage" style="font-size:.8em;display:none;">' +
											'<input type="checkbox" name="royalty_billed_discount" value="1">Apply Discount For Diff' +
										'</div>' +
									'</td>' +
								'</tr>' +
								'<tr>' +
									'<td valign="top">Management: </td>' +
									'<td>' +
										'<input type="text" name="management_billed" value="' + owed.management + '">' +
										'<input type="hidden" name="management_owed" value="' + owed.management + '">' +
										'<div class="alertMessage" style="font-size:.8em;display:none;">' +
											'<input type="checkbox" name="management_billed_discount" value="1">Apply Discount For Diff' +
										'</div>' +
									'</td>' +
								'</tr>' +
								'<tr>' +
									'<td valign="top">Marketing: </td>' +
									'<td>' +
										'<input type="text" name="marketing_billed" value="' + owed.marketing + '">' +
										'<input type="hidden" name="marketing_owed" value="' + owed.marketing + '">' +
										'<div class="alertMessage" style="font-size:.8em;display:none;">' +
											'<input type="checkbox" name="marketing_billed_discount" value="1">Apply Discount For Diff' +
										'</div>' +
									'</td>' +
								'</tr>' +
								'<tr>' +
									'<td valign="top">Labor: </td>' +
									'<td>' +
										'<input type="text" name="labor_billed" value="' + owed.labor + '">' +
										'<input type="hidden" name="labor_owed" value="' + owed.labor + '">' +
										'<div class="alertMessage" style="font-size:.8em;display:none;">' +
											'<input type="checkbox" name="labor_billed_discount" value="1">Apply Discount For Diff' +
										'</div>' +
									'</td>' +
								'</tr>' +
								'<tr>' +
									'<td valign="top">Parts: </td>' +
									'<td>' +
										'<input type="text" name="parts_billed" value="' + owed.parts + '">' +
										'<input type="hidden" name="parts_owed" value="' + owed.parts + '">' +
										'<div class="alertMessage" style="font-size:.8em;display:none;">' +
											'<input type="checkbox" name="parts_billed_discount" value="1">Apply Discount For Diff' +
										'</div>' +
									'</td>' +
								'</tr>' +
							'</table>' +
						'</td>' +
					'</tr>' +
				'</table>');

				$(this).find('input[type=text]').blur(function (){
					if ($(this).val() < owed[$(this).attr('name').replace('_billed', '')]){
						$(this).parent().find('.alertMessage').show();
					}else{
						$(this).parent().find('.alertMessage').hide();
					}
				});
			},
			buttons: {
				'Generate': function (){
					$.ajax({
						cache: false,
						url: js_app_link('appExt=multiStore&app=fees_report&appPage=default&action=genInvoice'),
						dataType: 'json',
						data: $(this).find(':text, :hidden, :checkbox:checked').serialize(),
						type: 'post',
						success: function (){
							js_redirect(js_app_link('appExt=multiStore&app=fees_report&appPage=default'));
						}
					});
				},
				'Cancel': function (){

				}
			}
		});
	});

	$('.addPayment').click(function (){
		var $button = $(this);
		var $Row = $button.parentsUntil('tbody').last().next();

		$('<div></div>').dialog({
			title: 'Pay Invoice',
			minWidth: 625,
			open: function (){
				var self = this;
				$.ajax({
					cache: false,
					url: js_app_link('appExt=multiStore&app=fees_report&appPage=default&action=getUnpaidInvoices&sID=' + $button.data('store_id')),
					dataType: 'json',
					success: function (data){
						var invoicesTable = $('<table cellpadding=3 cellspacing=3>' +
								'<thead>' +
									'<tr>' +
										'<th>Pay</th>' +
										'<th>Date Billed</th>' +
										'<th>Total Billed</th>' +
									'</tr>' +
								'</thead>' +
								'<tbody></tbody>' +
							'</table>');

						$.each(data.invoices, function (){
							invoicesTable.find('tbody').append('<tr>' +
									'<td><input type="checkbox" name="invoice[]" value="' + this.id + '"></td>' +
									'<td>' + this.date_added + '</td>' +
									'<td>' + this.total + '</td>' +
								'</tr>');
						});

						$(self).html(invoicesTable);
					}
				});
			},
			buttons: {
				'Pay Selected': function (){
					$.ajax({
						cache: false,
						url: js_app_link('appExt=multiStore&app=fees_report&appPage=default&action=payInvoices'),
						dataType: 'json',
						data: $(this).find(':checkbox:checked').serialize(),
						type: 'post',
						success: function (){
							js_redirect(js_app_link('appExt=multiStore&app=fees_report&appPage=default'));
						}
					});
				},
				'Cancel': function (){

				}
			}
		});
	});
});