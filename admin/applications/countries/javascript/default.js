$(document).ready(function () {
	var $PageGrid = $('.gridContainer');
	$PageGrid.newGrid('option', 'buttons', ['new', 'edit', 'delete']);

	$('.insertIcon').live('click', function () {
		var $td1 = $('<td></td>').append('<input type="text" name="new_zone_name[]">');
		var $td2 = $('<td></td>').append('<input type="text" name="new_zone_code[]">');
		var $td3 = $('<td></td>').attr('align', 'right').append('<a class="ui-icon ui-icon-closethick deleteIcon"></a>');
		var $newTr = $('<tr></tr>').append($td1).append($td2).append($td3);
		$(this).parent().parent().parent().parent().find('tbody').prepend($newTr);
	});

	$('.deleteIcon').live('click', function () {
		$(this).parent().parent().remove();
	});
});
