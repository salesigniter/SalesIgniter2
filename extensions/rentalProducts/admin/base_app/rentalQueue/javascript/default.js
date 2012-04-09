function getParams(add) {
	var getParams = [];
	getParams.push('appExt=rentalProducts');
	getParams.push('app=rentalQueue');
	getParams.push('appPage=default');

	if (add && add.length > 0){
		$.each(add, function () {
			getParams.push(this);
		});
	}
	return getParams.join('&');
}

$(document).ready(function () {
	$('tbody > .ui-grid-row').die('click').die('mouseover').die('mouseout');

	$('.selectAll').click(function () {
		var self = this;
		$('input[name="queueItem[]"]').each(function () {
			this.checked = self.checked;
		});
	});

	$('.sendButton').live('click', function () {
		var self = this;
		liveMessage(jsLanguage.get('TEXT_INFO_SENDING_RENTAL'));
		$.getJSON(js_app_link(getParams(['action=send'])), {
			'orders_products_rentals_id' : $(this).parent().parent().data('orders_products_rentals_id')
		}, function (Resp) {
			liveMessage(Resp.statusMsg);
			$(self).parentsUntil('tbody').last().find('.column-rental_state').html(Resp.rental_state);
			$(self).parentsUntil('tbody').last().find('.column-date_shipped').html(Resp.date_shipped);
			$(self).remove();
		});
	});

	$('.printLabelsButton').labelPrinter({
		printUrl   : js_app_link('appExt=rentalProducts&app=rentalQueue&appPage=default&action=printLabels&cID=' + customerId),
		getData    : function () {
			return $('input[name="queueItem[]"]:checked, select.barcodeMenu').serialize();
		},
		beforeShow : function () {
			if ($('input[name="queueItem[]"]:checked').size() <= 0){
				alert('Please select rentals to print using the checkboxes on the left of the table rows');
				return false;
			}
			return true;
		}
	});
});
