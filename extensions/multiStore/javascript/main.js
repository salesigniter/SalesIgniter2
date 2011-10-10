$(document).ready(function (){
	$('#storeSelect').dropdownchecklist({
		icon: {
			placement: 'right',
			toOpen: 'ui-icon-triangle-1-s',
			toClose: 'ui-icon-triangle-1-n'
		},
		firstItemChecksAll: true,
		maxDropHeight: 300
	});
});