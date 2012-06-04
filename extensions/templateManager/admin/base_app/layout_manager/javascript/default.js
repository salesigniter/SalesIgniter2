$(document).ready(function () {
	var $PageGrid = $('.gridContainer');
	$PageGrid.newGrid('option', 'buttons', [
		{
			selector          : '.layoutsButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildAppRedirect('layout_manager', 'layouts', 'templateManager', [GridClass.getDataKey() + '=' + GridClass.getSelectedData()]));
			}
		},
		{
			selector          : '.stylesheetButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				GridClass.showWindow({
					buttonEl   : this,
					contentUrl : GridClass.buildActionWindowLink('editTemplateStylesheet'),
					buttons    : ['cancel', {
						type: 'save',
						click: GridClass.windowButtonEvent('save', {
							actionName: 'saveTemplateStylesheet'
						})
					}]
				});
			}
		},
		{
			selector          : '.newButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				GridClass.showWindow({
					buttonEl   : this,
					contentUrl : GridClass.buildActionWindowLink('newTemplate'),
					buttons    : ['cancel', {
						type: 'save',
						click: GridClass.windowButtonEvent('save', {
							actionName: 'createTemplate'
						})
					}]
				});
			}
		},
		{
			selector          : '.copyButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				GridClass.showWindow({
					buttonEl   : this,
					contentUrl : GridClass.buildActionWindowLink('copyTemplate', true),
					buttons    : ['cancel', {
						type: 'save',
						click: GridClass.windowButtonEvent('save', {
							actionName: 'copyTemplate'
						})
					}]
				});
			}
		},
		{
			selector          : '.importButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				GridClass.showWindow({
					buttonEl   : this,
					contentUrl : GridClass.buildActionWindowLink('importTemplate', true),
					buttons    : ['cancel', {
						type: 'save',
						click: GridClass.windowButtonEvent('save', {
							actionName: 'importTemplate'
						})
					}]
				});
			}
		},
		{
			selector          : '.importButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				$.ajax({
					cache: false,
					url: GridClass.buildActionLink('exportTemplate'),
					dataType: 'json',
					success: function (data) {
						alert('Template exported successfully, you can download it from the templates directory.');
					}
				});
			}
		},
		{
			selector          : '.configureButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				GridClass.showWindow({
					buttonEl   : this,
					contentUrl : GridClass.buildActionWindowLink('configureTemplate', true),
					buttons    : ['cancel', {
						type: 'save',
						click: GridClass.windowButtonEvent('save', {
							actionName: 'saveTemplate'
						})
					}]
				});
			}
		},
		'delete'
	]);
});
