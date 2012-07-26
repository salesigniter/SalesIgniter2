$(document).ready(function () {
	var $PageGrid = $('.gridContainer');
	$PageGrid.newGrid('option', 'buttons', ['new', 'edit', 'delete']);

	$('.gridButtonBar').find('.emailButton').click(function () {
		var getVars = [];
		getVars.push('app=coupons');
		getVars.push('appPage=default');
		getVars.push('action=getActionWindow');
		getVars.push('window=emailCoupon');
		getVars.push('cID=' + $('.gridBodyRow.state-active').attr('data-coupon_id'));

		gridWindow({
			buttonEl   : this,
			gridEl     : $('.gridContainer'),
			contentUrl : js_app_link(getVars.join('&')),
			onShow     : function () {
				var self = this;

				$(self).find('.cancelButton').click(function () {
					$(self).effect('fade', {
						mode : 'hide'
					}, function () {
						$('.gridContainer').effect('fade', {
							mode : 'show'
						}, function () {
							$(self).remove();
						});
					});
				});

				$(self).find('.sendButton').click(function () {
					var getVars = [];
					getVars.push('app=coupons');
					getVars.push('appPage=default');
					getVars.push('action=sendCouponEmail');
					getVars.push('cID=' + $('.gridBodyRow.state-active').attr('data-coupon_id'));

					$.ajax({
						cache    : false,
						url      : js_app_link(getVars.join('&')),
						dataType : 'json',
						data     : $(self).find('*').serialize(),
						type     : 'post',
						success  : function (data) {
							if (data.success){
								alert(data.sentTo);
								$(self).effect('fade', {
									mode : 'hide'
								}, function () {
									$('.gridContainer').effect('fade', {
										mode : 'show'
									}, function () {
										$(self).remove();
									});
								});
							}
						}
					});
				});
			}
		});
	});

	$('.gridButtonBar').find('.reportButton').click(function () {
		var getVars = [];
		getVars.push('app=coupons');
		getVars.push('appPage=default');
		getVars.push('action=getActionWindow');
		getVars.push('window=report');
		getVars.push('cID=' + $('.gridBodyRow.state-active').attr('data-coupon_id'));

		gridWindow({
			buttonEl   : this,
			gridEl     : $('.gridContainer'),
			contentUrl : js_app_link(getVars.join('&')),
			onShow     : function () {
				var self = this;

				$(self).find('.backButton').click(function () {
					$(self).effect('fade', {
						mode : 'hide'
					}, function () {
						$('.gridContainer').effect('fade', {
							mode : 'show'
						}, function () {
							$(self).remove();
						});
					});
				});
			}
		});
	});
});