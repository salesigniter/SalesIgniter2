$(document).ready(function () {
	var $PageGrid = $('.gridContainer');

	var newEditButtonClick = function (e, GridClass) {
		if ($(this).hasClass('newButton')){
			GridClass.clearSelected();
		}

		GridClass.showWindow({
			buttonEl   : this,
			contentUrl : GridClass.buildActionWindowLink('newTaxClass', $(this).hasClass('editButton')),
			buttons    : [
				{
					type  : 'cancel',
					click : GridClass.windowButtonEvent('cancel')
				},
				{
					type  : 'save',
					click : GridClass.windowButtonEvent('save', {
						actionName : 'saveTaxClass'
					})
				}
			]
		});
	};

	$PageGrid.newGrid('option', 'dataKey', 'class_id');
	$PageGrid.newGrid('option', 'allowMultiple', true);
	$PageGrid.newGrid('option', 'buttons', [
		{
			selector          : '.newButton',
			disableIfMultiple : false,
			click             : newEditButtonClick
		},
		{
			selector          : '.editButton',
			disableIfMultiple : true,
			click             : newEditButtonClick
		},
		{
			selector          : '.deleteButton',
			disableIfMultiple : false,
			click             : $PageGrid.newGrid('deleteDialog', {
				messageSingle   : 'Are you sure you want to delete this class?',
				messageMultiple : 'Are you sure you want to delete these classes?',
				confirmUrl      : $PageGrid.newGrid('linkBuilder.action', 'deleteConfirm'),
				onSuccess       : $PageGrid.newGrid('redirectBuilder.currentApp', 'default')
			})
		}
	]);
});