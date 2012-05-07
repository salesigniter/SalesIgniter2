$(document).ready(function () {
	var $PageGrid = $('.gridContainer');

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
			selector          : '.newChildButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildCurrentAppRedirect('new', ['parent_id=' + GridClass.getSelectedData()]));
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
		'delete'
	]);

	$PageGrid.newGrid('option', 'onRowDblClick', function (e, GridClass) {
		var path = [];
		if ($_GET['cPath']){
			$.each($_GET['cPath'].split('_'), function () {
				path.push(this);
			});
		}
		path.push(GridClass.getSelectedData('category_id'));
		js_redirect(GridClass.buildCurrentAppRedirect('default', ['cPath=' + path.join('_')]));
	});
});
