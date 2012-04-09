$(document).ready(function (){
	$('#page-2').tabs();
	$('#tab_container').tabs();
	$('#tab_container').bind('tabsshow', function(event, ui) {
		$('.makeFCK', ui.panel).each(function (){
			if ($(this).is(':hidden')) return;

			$(this).ckeditor();
		});
	});
	$('.fileManager').filemanager();
});