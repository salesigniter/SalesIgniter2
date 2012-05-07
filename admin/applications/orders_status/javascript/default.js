$(document).ready(function () {
	var $PageGrid = $('.gridContainer');
	$PageGrid.newGrid('option', 'onRowClick', function (e, GridClass){
		var disableButton = false;
		GridClass.getSelectedRows().each(function (){
			if ($(this).attr('data-can_delete') == 'false'){
				disableButton = true;
			}
		});

		if (disableButton === true){
			GridClass.disableButton('.deleteButton');
		}
	});
	$PageGrid.newGrid('option', 'buttons', ['new', 'edit', 'delete']);
});