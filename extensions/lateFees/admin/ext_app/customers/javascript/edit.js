$(document).ready(function () {
	$('#lateFeesTab').find('.gridBody > .gridBodyRow').click(function () {
		if ($(this).hasClass('state-active')){
			return;
		}

		if ($(this).attr('data-is_open') == 'true'){
			$('#lateFeesTab').find('.gridButtonBar button').button('enable');
		}else{
			$('#lateFeesTab').find('.gridButtonBar button').button('disable');
		}
	});

	$('#lateFeesTab').find('.voidButton').click(function (){
		$.ajax({
			cache: false,
			url: js_app_link('app=customers&appPage=edit&action=voidLateFee&fee_id=' + $('#lateFeesTab .gridBodyRow.state-active').attr('data-fee_id')),
			dataType: 'json',
			success: function (Resp){
				$('#lateFeesTab .gridBodyRow.state-active')
					.attr('data-is_paid', 'true')
					.data('is_paid', 'true')
					.attr('data-is_open', 'false')
					.data('is_open', 'false');

				$('#lateFeesTab .gridBodyRow.state-active .ui-icon-circle-close')
					.removeClass('.ui-icon-circle-close')
					.addClass('ui-icon-circle-check');

				$('#lateFeesTab .feesTotal')
					.html(Resp.feesTotal);
			}
		});
	});
});