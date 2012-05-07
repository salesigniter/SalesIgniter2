$(document).ready(function () {
	var $PageGrid = $('.gridContainer');
	$PageGrid.newGrid('option', 'buttons', [
		{
			selector          : '.detailsButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildAppRedirect('orders', 'details', null, [GridClass.getDataKey() + '=' + GridClass.getSelectedData()]));
			}
		},
		{
			selector          : '.deleteButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				$.ajax({
					cache    : false,
					dataType : 'json',
					url      : GridClass.buildActionLink('getDeleteOptions', [GridClass.getDataKey() + '=' + GridClass.getSelectedData()]),
					success  : function (data) {
						if (data.success == true){
							$('<div></div>').html(data.html).attr('title', 'Delete').dialog({
								resizable  : false,
								allowClose : false,
								modal      : true,
								buttons    : {
									'Confirm' : function () {
										$.ajax({
											cache    : false,
											dataType : 'json',
											type     : 'post',
											data     : $(this).find('*').serialize(),
											url      : GridClass.buildActionLink('deleteConfirm'),
											success  : function (data) {
												js_redirect(GridClass.buildAppRedirect('orders', 'default'));
											}
										});
									},
									'Cancel'  : function () {
										$(this).dialog('close').remove();
									}
								}
							});
						}

					}
				});
			}
		},
		{
			selector          : '.cancelButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				var message = 'Are you sure you want to cancel this order?';
				if (GridClass.getSelectedRows().size() > 1){
					message = 'Are you sure you want to cancel these orders?';
				}

				GridClass.showConfirmDialog({
					title     : 'Confirm Order Cancellation',
					content   : message,
					onConfirm : function (e, GridClass) {
						$.ajax({
							cache    : false,
							dataType : 'json',
							url      : GridClass.buildActionLink('cancelOrder', [GridClass.getDataKey() + '=' + GridClass.getSelectedData()]),
							success  : function (data) {
								js_redirect(GridClass.buildAppRedirect('orders', 'default'));
							}
						});
					}
				});
			}
		},
		{
			selector          : '.invoiceButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildAppRedirect('orders', 'invoice', null, [GridClass.getDataKey() + '=' + GridClass.getSelectedData()]));
			}
		},
		{
			selector          : '.pdfInvoiceButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				window.open(GridClass.buildAppRedirect('generate_pdf', 'default', 'pdfPrinter', [GridClass.getDataKey() + '=' + GridClass.getSelectedData()]));
			}
		},
		{
			selector          : '.newButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildAppRedirect('default', 'new', 'orderCreator'));
			}
		},
		{
			selector          : '.editButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildAppRedirect('default', 'new', 'orderCreator', [GridClass.getDataKey() + '=' + GridClass.getSelectedData()]));
			}
		},
		{
			selector          : '.packingSlipButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildAppRedirect('orders', 'packingslip', null, [GridClass.getDataKey() + '=' + GridClass.getSelectedData()]));
			}
		}
	]);
});

$(document).ready(function () {
	$('.makeDatepicker').datepicker({
		dateFormat : 'yy-mm-dd'
	});
	$('#start_date').datepicker({
		dateFormat : 'yy-mm-dd'
	});
	$('#end_date').datepicker({
		dateFormat : 'yy-mm-dd'
	});

	$('.gridButtonBar').find('.deleteButton').live('click', function () {
		var orders = $('.gridContainer').newGrid('getSelectedData', 'order_id');
		var $self = $(this);
		showAjaxLoader($self, 'x-large');
		$.ajax({
			cache    : false,
			dataType : 'json',
			url      : js_app_link('app=orders&appPage=default&action=getDeleteOptions&oID=' + orders),
			success  : function (data) {
				removeAjaxLoader($self);
				if (data.success == true){
					$('<div></div>').html(data.html).attr('title', 'Delete').dialog({
						resizable  : false,
						allowClose : false,
						modal      : true,
						buttons    : {
							'Confirm' : function () {
								$.ajax({
									cache    : false,
									dataType : 'json',
									type     : 'post',
									data     : $(this).find('*').serialize(),
									url      : js_app_link('app=orders&appPage=default&action=deleteConfirm'),
									success  : function (data) {
										js_redirect(js_app_link('app=orders&appPage=default'));
									}
								});
							},
							'Cancel'  : function () {
								$(this).dialog('close').remove();
							}
						}
					});
				}

			}
		});
	});
});