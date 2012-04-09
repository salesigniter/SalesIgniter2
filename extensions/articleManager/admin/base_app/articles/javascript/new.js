$(document).ready(function (){
	$('.makeFCK').each(function (){
		CKEDITOR.replace(this);
	});
	$('.useDatepicker').datepicker();
	$('#page-2').tabs();
	$('#tab_container').tabs();
});