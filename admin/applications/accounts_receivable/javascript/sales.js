$(document).ready(function () {
	var $PageGrid = $('.gridContainer');
	$PageGrid.newGrid('option', 'buttons', [
		{
			selector          : '.detailsButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildCurrentAppRedirect('details', [
					GridClass.getDataKey() + '=' + GridClass.getSelectedData(),
					'sale_module=' + GridClass.getSelectedData('sale_module')
				]));
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
					url      : GridClass.buildActionLink('getDeleteOptions', [
						GridClass.getDataKey() + '=' + GridClass.getSelectedData(),
						'sale_module=' + GridClass.getSelectedData('sale_module')
					]),
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
												js_redirect(GridClass.buildCurrentAppRedirect('sales'));
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
							url      : GridClass.buildActionLink('cancelOrder', [
								GridClass.getDataKey() + '=' + GridClass.getSelectedData(),
								'sale_module=' + GridClass.getSelectedData('sale_module')
							]),
							success  : function (data) {
								js_redirect(GridClass.buildCurrentAppRedirect('sales'));
							}
						});
					}
				});
			}
		},
		{
			selector          : '.printButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				window.open(GridClass.buildAppRedirect('print', 'default', 'templateManager', [
					'action=sale',
					'print_type=default',
					'sale_id=' + GridClass.getSelectedData(),
					'sale_module=' + GridClass.getSelectedData('sale_module'),
					'revision=' + GridClass.getSelectedData('revision')
				]));
			}
		},
		{
			selector          : '.printPrepButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				window.open(GridClass.buildAppRedirect('print', 'default', 'templateManager', [
					'action=sale',
					'print_type=prepSheet',
					'sale_id=' + GridClass.getSelectedData(),
					'sale_module=' + GridClass.getSelectedData('sale_module'),
					'revision=' + GridClass.getSelectedData('revision')
				]));
			}
		},
		{
			selector          : '.printCarnetButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				window.open(GridClass.buildAppRedirect('print', 'default', 'templateManager', [
					'action=sale',
					'print_type=carnet',
					'sale_id=' + GridClass.getSelectedData(),
					'sale_module=' + GridClass.getSelectedData('sale_module'),
					'revision=' + GridClass.getSelectedData('revision')
				]));
			}
		},
		{
			selector          : '.invoiceButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildCurrentAppRedirect('invoice', [
					GridClass.getDataKey() + '=' + GridClass.getSelectedData(),
					'sale_module=' + GridClass.getSelectedData('sale_module')
				]));
			}
		},
		{
			selector          : '.pdfInvoiceButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				window.open(GridClass.buildAppRedirect('generate_pdf', 'default', 'pdfPrinter', [
					GridClass.getDataKey() + '=' + GridClass.getSelectedData(),
					'sale_module=' + GridClass.getSelectedData('sale_module')
				]));
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
				js_redirect(GridClass.buildAppRedirect('default', 'new', 'orderCreator', [
					GridClass.getDataKey() + '=' + GridClass.getSelectedData(),
					'sale_module=' + GridClass.getSelectedData('sale_module')
				]));
			}
		},
		{
			selector          : '.packingSlipButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildCurrentAppRedirect('packingslip', [
					GridClass.getDataKey() + '=' + GridClass.getSelectedData(),
					'sale_module=' + GridClass.getSelectedData('sale_module')
				]));
			}
		}
	]);

	$('.makeDatepicker').datepicker({
		dateFormat : 'yy-mm-dd'
	});
});