function getLinkParams(addVars) {
	var getVars = [];
	getVars.push('rType=ajax');
	getVars.push('app=modules');
	getVars.push('appPage=default');
	getVars.push('module=' + $('.gridBodyRow.state-active').data('module_code'));
	getVars.push('moduleType=' + $('.gridBodyRow.state-active').data('module_type'));
	getVars.push('modulePath=' + $('.gridBodyRow.state-active').data('module_path'));
	if (addVars){
		for(var i = 0; i < addVars.length; i++){
			getVars.push(addVars[i]);
		}
	}

	return getVars.join('&');
}

function setConfirmUnload() {
	window.onbeforeunload = ($('.edited').size() > 0) ? function (){ return jsLanguage.get('TEXT_INFO_LOST_CHANGES') } : null;
}

$(document).ready(function () {
	$('.gridBody > .gridBodyRow').click(function (e, isRefresh) {
		if ($(this).hasClass('state-active') && !isRefresh){
			return;
		}

		$('.gridButtonBar').find('button').button('enable');
		if ($(this).data('installed') === false){
			$('.gridButtonBar').find('.uninstallButton').button('disable');
			$('.gridButtonBar').find('.editButton').button('disable');
		}
		else {
			$('.gridButtonBar').find('.installButton').button('disable');
		}
	});

	$('.editButton').click(function () {
		configurationGridWindow({
			buttonEl: this,
			gridEl: $('.gridContainer'),
			contentUrl: js_app_link(getLinkParams(['action=getActionWindow', 'window=edit'])),
			saveUrl: js_app_link(getLinkParams(['action=save'])),
			onSaveSuccess: function (){
				js_redirect(js_app_link('app=modules&appPage=default&moduleType=' + $('.gridBodyRow.state-active').data('module_type')));
			}
		});
	});

	$('.installButton').click(function () {
		var $gridRow = $('.gridBodyRow.state-active');
		var getVars = getLinkParams(['action=install']);

		showAjaxLoader($gridRow, 'small');
		$.get(js_app_link(getVars, true), function (){
			removeAjaxLoader($gridRow);
			$gridRow.data('installed', true);
			$gridRow
				.find('.installedIcon')
				.removeClass('ui-icon-circle-close')
				.addClass('ui-icon-circle-check');
			$gridRow.trigger('refresh');
		});
	});

	$('.uninstallButton').click(function () {
		var $gridRow = $('.gridBodyRow.state-active');
		var getVars = getLinkParams(['action=remove']);

		showAjaxLoader($gridRow, 'small');
		confirmDialog({
			confirmUrl : js_app_link(getVars),
			title : 'Confirm Module Uninstall',
			content : 'Are you sure you want to uninstall this module?',
			errorMessage : 'This module could not be uninstalled.',
			success : function () {
				removeAjaxLoader($gridRow);
				$gridRow
					.find('.installedIcon')
					.removeClass('ui-icon-circle-check')
					.addClass('ui-icon-circle-close');
				$gridRow.data('installed', false);
				$gridRow.trigger('refresh');
			}
		});
	});

	/*
	 * Global function for javascript tables in the windows
	 */
	$('.deleteIcon').live('click', function () {
		$(this).parent().parent().remove();
	});
});