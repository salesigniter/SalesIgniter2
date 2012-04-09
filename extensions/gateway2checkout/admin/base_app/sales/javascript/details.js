$(document).ready(function (){
	$('.addCommentButton').click(function (){
		var self = this;

		$('<div style="overflow:hidden;"></div>').dialog({
			title: 'Add Comment To Order',
			open: function (){
				$(this).html('<textarea rows="5" cols="30"></textarea>' +
					'<br>' +
					'<input type="checkbox" name="notify_vendor">Notify Vendor Via Email' +
					'<br>' +
					'<input type="checkbox" name="notify_customer">Notify Customer Via Email');
			},
			buttons: {
				'Cancel': function (){
					$(this).dialog('close').remove();
				},
				'Add Comment': function (){
					var dialog = this;

					showAjaxLoader($(dialog), 'large');

					var postData = [];
					postData.push('sale_id=' + $(self).data('sale_id'));
					postData.push('comment=' + $(dialog).find('textarea').val());
					postData.push('notify_vendor=' + ($(dialog).find('input[name=notify_vendor]').is(':checked') > 0 ? '1' : '0'));
					postData.push('notify_customer=' + ($(dialog).find('input[name=notify_customer]').is(':checked') > 0 ? '1' : '0'));

					$.ajax({
						url: js_app_link('appExt=gateway2checkout&app=sales&appPage=details&action=addComment'),
						cache: false,
						dataType: 'json',
						type: 'post',
						data: postData.join('&'),
						success: function (data){
							$('.commentsTable > tbody').prepend('<tr>' +
								'<td>' + data.newComment.Date + '</td>' +
								'<td>' + data.newComment.Who + '</td>' +
								'<td>' + data.newComment.Ip + '</td>' +
								'<td>' + data.newComment.Comment + '</td>' +
								'</tr>');

							removeAjaxLoader($(dialog));
							liveMessage(data.apiResponse);

							$(dialog).dialog('close').remove();
						}
					});
				}
			}
		});
	});

	$('.invoicePartialRefundButton').click(function (){
		var self = this;

		$('<div></div>').dialog({
			title: 'Issue Partial Refund',
			width: 400,
			open: function (){
				$(this).html('<table>' +
					'<tr>' +
					'<td>Refund Amount:</td>' +
					'<td><input type="text" size="6" value=""></td>' +
					'</tr>' +
					'<tr>' +
					'<td>Reason:</td>' +
					'<td><select>' +
					'<option value="1">Did not receive order</option>' +
					'<option value="2">Did not like item</option>' +
					'<option value="3">Item(s) not as described</option>' +
					'<option value="4">Fraud</option>' +
					'<option value="5">Other</option>' +
					'<option value="6">Item not available</option>' +
					'<option value="8">No response</option>' +
					'<option value="9">Recurring last installment</option>' +
					'<option value="10">Cancellation</option>' +
					'<option value="11">Billed in error</option>' +
					'<option value="12">Prohibited product</option>' +
					'<option value="13">Service refunded at sellers request</option>' +
					'<option value="14">Nondelivery</option>' +
					'<option value="15">Not as described</option>' +
					'<option value="16">Out of stock</option>' +
					'<option value="17">Duplicate</option>' +
					'</select></td>' +
					'</tr>' +
					'<tr>' +
					'<td valign="top">Comments:</td>' +
					'<td><textarea rows="5" cols="30"></textarea></td>' +
					'</tr>' +
					'</table>');
			},
			buttons: {
				'Cancel': function (){
					$(this).dialog('close').remove();
				},
				'Issue Refund': function (){
					var dialog = this;

					showAjaxLoader($(dialog), 'large');

					var postData = [];
					postData.push('invoice_id=' + $(self).data('invoice_id'));
					postData.push('amount=' + $(dialog).find('input:text').val());
					postData.push('category=' + $(dialog).find('select').val());
					postData.push('comment=' + $(dialog).find('textarea').val());

					$.ajax({
						url: js_app_link('appExt=gateway2checkout&app=sales&appPage=details&action=refundSale'),
						cache: false,
						dataType: 'json',
						type: 'post',
						data: postData.join('&'),
						success: function (data){
							removeAjaxLoader($(dialog));
							liveMessage(data.apiResponse);

							$(dialog).dialog('close').remove();
							js_redirect(js_app_link('appExt=gateway2checkout&app=sales&appPage=details&saleId=' + $(self).data('sale_id')));
						}
					});
				}
			}
		});
	});

	$('.refundSaleButton').click(function (){
		var self = this;

		$('<div></div>').dialog({
			title: 'Issue Refund',
			width: 400,
			open: function (){
				$(this).html('<table>' +
					'<tr>' +
					'<td>Refund Amount:</td>' +
					'<td>Remaining Sale Balance</td>' +
					'</tr>' +
					'<tr>' +
					'<td>Reason:</td>' +
					'<td><select>' +
					'<option value="1">Did not receive order</option>' +
					'<option value="2">Did not like item</option>' +
					'<option value="3">Item(s) not as described</option>' +
					'<option value="4">Fraud</option>' +
					'<option value="5">Other</option>' +
					'<option value="6">Item not available</option>' +
					'<option value="8">No response</option>' +
					'<option value="9">Recurring last installment</option>' +
					'<option value="10">Cancellation</option>' +
					'<option value="11">Billed in error</option>' +
					'<option value="12">Prohibited product</option>' +
					'<option value="13">Service refunded at sellers request</option>' +
					'<option value="14">Nondelivery</option>' +
					'<option value="15">Not as described</option>' +
					'<option value="16">Out of stock</option>' +
					'<option value="17">Duplicate</option>' +
					'</select></td>' +
					'</tr>' +
					'<tr>' +
					'<td valign="top">Comments:</td>' +
					'<td><textarea rows="5" cols="30"></textarea></td>' +
					'</tr>' +
					'</table>');
			},
			buttons: {
				'Cancel': function (){
					$(this).dialog('close').remove();
				},
				'Issue Refund': function (){
					var dialog = this;

					showAjaxLoader($(dialog), 'large');

					var postData = [];
					postData.push('sale_id=' + $(self).data('sale_id'));
					postData.push('amount=' + $(self).data('remaining_total'));
					postData.push('category=' + $(dialog).find('select').val());
					postData.push('comment=' + $(dialog).find('textarea').val());

					$.ajax({
						url: js_app_link('appExt=gateway2checkout&app=sales&appPage=details&action=refundSale'),
						cache: false,
						dataType: 'json',
						type: 'post',
						data: postData.join('&'),
						success: function (data){
							removeAjaxLoader($(dialog));
							liveMessage(data.apiResponse);

							$(dialog).dialog('close').remove();
							js_redirect(js_app_link('appExt=gateway2checkout&app=sales&appPage=details&saleId=' + $(self).data('sale_id')));
						}
					});
				}
			}
		});
	});
});