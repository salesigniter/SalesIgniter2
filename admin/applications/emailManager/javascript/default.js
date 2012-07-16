$(document).ready(function () {
	function afterShow() {
		var self = this;
		$(self).find('select[name=module_event_key]').change(function () {
			$(self).find('#when_to_send_desc').html($(this).find('option:selected').data('description'));
			$(self).find('.eventSettings').hide();
			$(self).find('.eventSettings input, .eventSettings select, .eventSettings textarea').attr('disabled', 'disabled');

			var showingDiv = $(self).find('#event_settings_' + $(this).val());
			showingDiv.show();
			showingDiv.find('input, select, textarea').removeAttr('disabled');
		}).trigger('change');
	}

	var $PageGrid = $('.gridContainer');
	$PageGrid.newGrid('option', 'buttons', [
		{
			selector          : '.newButton',
			disableIfNone     : false,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				GridClass.clearSelected();

				GridClass.showWindow({
					buttonEl    : this,
					contentUrl  : GridClass.buildActionWindowLink('new', false, [
						'module=' + $_GET['module']
					]),
					onAfterShow : afterShow,
					buttons     : ['cancel', {
						type  : 'save',
						click : GridClass.windowButtonEvent('save', {
							onSuccess : function (GridClass, data, o) {
								removeAjaxLoader(o.buttonEl);
								if (data.success){
									js_redirect(GridClass.buildCurrentAppRedirect(null, [
										'module=' + $_GET['module']
									]));
								}
								else {
									var ErrorMessage = 'An Unknown Error Occured!';
									if (data.error){
										ErrorMessage = data.error.message;
									}
									GridClass.newWindow.find('#messageStack').html(ErrorMessage);
								}
							}
						})
					}]
				});
			}
		},
		{
			selector          : '.editButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				GridClass.showWindow({
					buttonEl    : this,
					contentUrl  : GridClass.buildActionWindowLink('new', true, [
						'module=' + $_GET['module']
					]),
					onAfterShow : afterShow,
					buttons     : ['cancel', {
						type  : 'save',
						click : GridClass.windowButtonEvent('save', {
							onSuccess : function (GridClass, data, o) {
								removeAjaxLoader(o.buttonEl);
								if (data.success){
									js_redirect(GridClass.buildCurrentAppRedirect(null, [
										'module=' + $_GET['module']
									]));
								}
								else {
									var ErrorMessage = 'An Unknown Error Occured!';
									if (data.error){
										ErrorMessage = data.error.message;
									}
									GridClass.newWindow.find('#messageStack').html(ErrorMessage);
								}
							}
						})
					}]
				});
			}
		},
		{
			selector          : '.deleteButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				GridClass.showDeleteDialog({
					onSuccess : function () {
						js_redirect(GridClass.buildCurrentAppRedirect('default', [
							'module=' + $_GET['module']
						]));
					}
				});
			}
		},
		{
			selector          : '.testEmailButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				GridClass.showConfirmDialog({
					title             : 'Enter Email Details',
					content           : 'Email Address: <input type="text" name="email_address">',
					confirmButtonText : 'Send Email',
					onConfirm         : function () {
						var self = this;
						$.post(js_app_link('app=emailManager&appPage=default&action=sendTestEmail'), {
							template_id : GridClass.getSelectedData(),
							send_to     : $(self).find('input[name=email_address]').val()
						}, function (data) {
							alert('Test Email Sent!');
							$(self).dialog('close').remove();
						}, 'json');
					},
					onCancel          : function () {
						$(this).dialog('close').remove();
					}
				});
			}
		}
	]);
});