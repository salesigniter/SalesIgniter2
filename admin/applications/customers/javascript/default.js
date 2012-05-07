$(document).ready(function () {
	var $PageGrid = $('.gridContainer');
	$PageGrid.newGrid('option', 'onRowClick', function (e, GridClass) {
		if ($(this).data('has_orders') == false){
			GridClass.disableButton('.ordersButton');
		}
	});
	$PageGrid.newGrid('option', 'buttons', [
		{
			selector          : '.newButton',
			disableIfNone     : false,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildCurrentAppRedirect('new'));
			}
		},
		{
			selector          : '.editButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildCurrentAppRedirect('new', [GridClass.getDataKey() + '=' + GridClass.getSelectedData()]));
			}
		},
		{
			selector          : '.ordersButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildAppRedirect('orders', 'decault', [GridClass.getDataKey() + '=' + GridClass.getSelectedData()]));
			}
		},
		{
			selector          : '.emailButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildAppRedirect('mail', 'default', ['customer=' + GridClass.getSelectedData('customer_email')]));
			}
		},
		{
			selector          : '.loginAsCustomerButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildAppRedirect('customers', 'default', ['action=loginAd', GridClass.getDataKey() + '=' + GridClass.getSelectedData()]));
			}
		},
		'delete',
		'export'
	]);
});