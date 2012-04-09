$(document).ready(function (){
	$('#tab_container').tabs();
	$('#tab_container').bind('tabsshow', function(event, ui) {
		$('.makeFCK', ui.panel).each(function (){
			if ($(this).is(':hidden')) return;

			CKEDITOR.replace(this);
		});
	});
});