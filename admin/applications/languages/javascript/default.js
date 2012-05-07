$(document).ready(function () {
	var $PageGrid = $('.gridContainer');
	$PageGrid.newGrid('option', 'onRowClick', function (e, GridClass) {
		if ($(this).data('is_default') == true){
			GridClass.disableButton('.deleteButton');
		}
	});
	$PageGrid.newGrid('option', 'buttons', [
		'new',
		'edit',
		'delete',
		{
			selector          : '.defineButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildCurrentAppRedirect('defines', [GridClass.getDataKey() + '=' + GridClass.getSelectedData()]));
			}
		}
	]);

	$(document).on('click', '.selectAll', function () {
		var allBoxChecked = this.checked;
		$('input[name="translate_model[]"]').each(function () {
			this.checked = allBoxChecked;
		});
	});
});
