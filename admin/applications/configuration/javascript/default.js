function setConfirmUnload(on) {
	window.onbeforeunload = (on) ? unloadMessage : null;
	if (on === true){
		$('.saveButton').show();
	}
	else {
		$('.saveButton').hide();
	}
}

function unloadMessage() {
	return 'Navigating away will cause all changes to be lost, are you sure?';
}

$(document).ready(function () {
	$('.makeTabs').tabs();

	var fieldNameError = false;
	$('input, select, textarea').each(function () {
		if ($(this).attr('name') == 'configuration_value'){
			fieldNameError = true;
			$(this).addClass('error').attr('disabled', 'disabled');
			return;
		}
		$(this).click(function () {
				this.origVal = $(this).val()
			}).blur(function () {
				if (this.origVal != $(this).val()){
					$(this).removeClass('notEdited').addClass('edited');
					setConfirmUnload(true);
				}
			});
	});

	if (fieldNameError === true){
		alert('Editing of some fields has been disabled due to an input naming error, please notify the cart administrator.');
	}

	$('.saveButton').click(function () {
		showAjaxLoader($('.edited'), 'small');
		$.post(js_app_link('app=configuration&appPage=default&key=' + CONFIGURATION_GROUP_KEY + '&action=save'), $('.edited').serialize(), function (data, textStatus, jqXHR) {
			if (data.success === true){
				removeAjaxLoader($('.edited'));
				$('.edited').removeClass('edited').addClass('notEdited');
				setConfirmUnload(false);
			}
		}, 'json');
	});
});