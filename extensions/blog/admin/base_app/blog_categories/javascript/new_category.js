$(document).ready(function (){
	$('#page-2').tabs();
	$('#tab_container').tabs();

	$('.makeFCK').each(function (){
			CKEDITOR.replace(this);
	});
	$('input[name=categories_image]').filemanager();
});
