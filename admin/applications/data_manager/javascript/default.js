function showHideDivs(selector) {
	if ($(selector + ':visible').size() > 0){
		$(selector).hide();
	}
	else {
		$(selector).show();
	}
}
;

$(document).ready(function () {
	$('#epTabs').tabs();
	
	$('select[name=module]').change(function (){
		var FormatSelect = $('select[name=module_format]');
		FormatSelect.find('option').remove();
		if ($(this).val() == ''){
			FormatSelect.append('<option value="">Please Select A Module</option>');
		}else{
			var Data = $(this).find('option:selected').attr('data-supported_formats');
			var SupportedFormats = $.parseJSON(Data);
			$.each(SupportedFormats, function (k, v){
				FormatSelect.append('<option value="' + k + '">' + v + '</option>');
			});
		}


		var ActionSelect = $('select[name=module_action]');
		ActionSelect.find('option').remove();
		if ($(this).val() == ''){
			ActionSelect.append('<option value="">Please Select A Module</option>');
		}else{
			var Data = $(this).find('option:selected').attr('data-supported_actions');
			var SupportedActions = $.parseJSON(Data);
			$.each(SupportedActions, function (k, v){
				ActionSelect.append('<option value="' + k + '">' + v + '</option>');
			});
		}
	});
});
