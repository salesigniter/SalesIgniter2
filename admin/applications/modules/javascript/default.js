$(document).ready(function () {
	var $PageGrid = $('.gridContainer');
	$PageGrid.newGrid('option', 'onRowClick', function (e, GridClass) {
		if ($(this).attr('data-installed') == 'false'){
			GridClass.disableButton('.uninstallButton');
			GridClass.disableButton('.editButton');
		}
		else {
			GridClass.disableButton('.installButton');
		}
	});
	$PageGrid.newGrid('option', 'buttons', [
		{
			selector          : '.installButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildCurrentAppRedirect('default', [
					'action=install',
					GridClass.getDataKey() + '=' + GridClass.getSelectedData(),
					'moduleType=' + GridClass.getSelectedData('module_type'),
					'modulePath=' + GridClass.getSelectedData('module_path')
				]));
			}
		},
		{
			selector          : '.editButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				GridClass.showConfigurationWindow({
					buttonEl      : this,
					contentUrl    : GridClass.buildActionWindowLink('edit', true, [
						'moduleType=' + GridClass.getSelectedData('module_type'),
						'modulePath=' + GridClass.getSelectedData('module_path')
					]),
					saveUrl       : GridClass.buildActionLink('save', [
						GridClass.getDataKey() + '=' + GridClass.getSelectedData(),
						'moduleType=' + GridClass.getSelectedData('module_type'),
						'modulePath=' + GridClass.getSelectedData('module_path')
					]),
					onSaveSuccess : function () {
						js_redirect(GridClass.buildCurrentAppRedirect('default', ['moduleType=' + GridClass.getSelectedData('module_type')]));
					}
				});
			}
		},
		{
			selector          : '.uninstallButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				var message = 'Are you sure you want to uninstall this module?';
				if (GridClass.getSelectedRows().size() > 1){
					message = 'Are you sure you want to uninstall these modules?';
				}

				GridClass.showConfirmDialog({
					title     : 'Confirm Module Uninstall',
					content   : message,
					onConfirm : function (e, GridClass) {
						js_redirect(GridClass.buildActionLink('uninstall', [
							GridClass.getDataKey() + '=' + GridClass.getSelectedData(),
							'moduleType=' + GridClass.getSelectedData('module_type'),
							'modulePath=' + GridClass.getSelectedData('module_path')
						]));
					}
				});
			}
		}
	]);

	/*
	 * Global function for javascript tables in the windows
	 */
	$('.deleteIcon').live('click', function () {
		$(this).parent().parent().remove();
	});
});
