$(document).ready(function () {
	var $PageGrid = $('.gridContainer');
	$PageGrid.newGrid('option', 'buttons', ['new','edit','delete']);
	$PageGrid.newGrid('option', 'onRowDblClick', function (e, GridClass){
		GridClass.GridButtonElement.find('.editButton').click();
	});

	$(document).on('click', '#checkAll', function () {
		var self = this;
		$(this).parent().parent().parent().find('.appBox').each(function () {
			this.checked = self.checked;
		});
		$(this).parent().parent().parent().find('.pageBox').each(function () {
			this.checked = self.checked;
		});
		$(this).parent().parent().parent().find('.extensionBox').each(function () {
			this.checked = self.checked;
		});

		if (self.checked){
			$('#checkAllText').html('Uncheck All Elements');
		}
		else {
			$('#checkAllText').html('Check All Elements');
		}
	});
	$(document).on('click', '.checkAllPages', function () {
		var self = this;
		$(self).parent().parent().find('.pageBox').each(function () {
			this.checked = self.checked;
		});
	});

	$(document).on('click', '.checkAllApps', function () {
		var self = this;
		$(self).parent().parent().find('.appBox').each(function () {
			this.checked = self.checked;
		});
		$(self).parent().parent().find('.pageBox').each(function () {
			this.checked = self.checked;
		});
	});
});