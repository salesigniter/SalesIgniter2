$(document).ready(function () {
	$('.makeTabs').tabs();

	var fieldNameError = false;
	var origValues = [];
	$('input, select, textarea').each(function () {
		var inputName = $(this).attr('name');
		if (inputName == 'configuration_value'){
			fieldNameError = true;
			$(this).addClass('error').attr('disabled', 'disabled');
			return;
		}

		if (!origValues[inputName]){
			if ($(this).attr('type') == 'checkbox'){
				origValues[inputName] = []
			}
			else {
				origValues[inputName] = '';
			}
		}

		var clickFnc = false;
		if ($(this).attr('type') == 'checkbox'){
			if (this.checked){
				origValues[inputName].push($(this).val());
			}
			clickFnc = true;
		} else if ($(this).attr('type') == 'radio'){
			if (this.checked){
				origValues[inputName] = $(this).val();
			}
			clickFnc = true;
		}
		else {
			origValues[inputName] = $(this).val();
		}

		var processChange = function () {
			var edited = false;
			if (typeof origValues[inputName] == 'object'){
				if ($.inArray($(this).val(), origValues[inputName]) == -1){
					edited = true;
				}
			} else if (origValues[inputName] != $(this).val()){
				edited = true;
			}

			if (edited === true){
				$('[name="' + inputName + '"]').removeClass('notEdited').addClass('edited');
				$(this).parentsUntil('tbody').last().find('.ui-icon-alert').show();
			}
			else {
				$('[name="' + inputName + '"]').removeClass('edited').addClass('notEdited');
				$(this).parentsUntil('tbody').last().find('.ui-icon-alert').hide();
			}
		};

		if (clickFnc){
			$(this).click(processChange);
		}
		else {
			$(this).blur(processChange);
		}
	});

	if (fieldNameError === true){
		alert('Editing of some fields has been disabled due to an input naming error, please notify the cart administrator.');
	}

	$('.fileManager').filemanager();

	$('.saveButton').click(function () {
		showAjaxLoader($('.edited'), 'small');
		$.post(js_app_link('app=configuration&appPage=default&key=' + CONFIGURATION_GROUP_KEY + '&action=save'), $('.edited').serialize(), function (data, textStatus, jqXHR) {
			if (data.success === true){
				removeAjaxLoader($('.edited'));
				$('.edited').removeClass('edited').addClass('notEdited');
			}
		}, 'json');
	});
});