function uploadWindow(o) {
	var intervalTimer;
	var noFileApi = false;
	var dialogHtml = '<div title="' + o.title + '">' +
		'<form id="form1" enctype="multipart/form-data" method="post" action="' + o.action + '">' +
			'<div style="margin-bottom: 10px;">' +
				'<label for="fileToUpload">Select a File to Upload</label><br />' +
				'<input type="file" name="fileToUpload" id="fileToUpload" style="width: 378px;"/>' +
			'</div>' +
			'<div id="fileInfo" style="font-size: 10pt;font-style: italic;margin-top: 10px;">' +
				'<div id="fileName"></div>' +
				'<div id="fileSize"></div>' +
				'<div id="fileType"></div>' +
			'</div>' +
			'<div style="margin-bottom: 10px;"></div>' +
			'<div id="progressIndicator" style="font-size: 10pt;">' +
				'<div id="progressBar"></div>' +
				'<div>' +
					'<div id="transferSpeedInfo" style="float: left;width: 80px;">&nbsp;</div>' +
					'<div id="timeRemainingInfo" style="float: left;margin-left: 10px;">&nbsp;</div>' +
					'<div id="transferBytesInfo" style="float: right;text-align: right;">&nbsp;</div>' +
					'<div style="clear: both;"></div>' +
				'</div>' +
				'<div id="uploadResponse" style="margin-top: 10px;padding: 20px;overflow: hidden;display: none;border-radius:10px;-moz-border-radius: 10px;border: 1px solid #ccc;box-shadow: 0 0 5px #ccc;background-image: -moz-linear-gradient(top, #ff9900, #c77801);background-image: -webkit-gradient(linear, left top, left bottom, from(#ff9900), to(#c77801));"></div>' +
			'</div>' +
		'</form>' +
	'</div>';

	var checkExtension = function (val, allowed){
		var returnVal = false;
		$.each(allowed, function (){
			var extCheck = val.substr(val.lastIndexOf('.') + 1);
			if (extCheck == this){
				returnVal = true;
				return;
			}
		});
		return returnVal;
	};

	$(dialogHtml).dialog({
		minHeight: 300,
		minWidth: 430,
		resizable: true,
		open: function (event, ui){
			$('#fileToUpload').change(function (){
				if (o.allowedTypes && $.isArray(o.allowedTypes) && checkExtension($(this).val(), o.allowedTypes) === false){
					alert('Invalid File Type' + "\n" + 'Allowed Types: ' + o.allowedTypes.join(', '));
					$(this).val('');
					return false;
				}else{
					if (this.files){
						var file = this.files[0];
						var fileSize = 0;
						if (file.size > 1024 * 1024){
							fileSize = (Math.round(file.size * 100 / (1024 * 1024)) / 100).toString() + 'MB';
						}else{
							fileSize = (Math.round(file.size * 100 / 1024) / 100).toString() + 'KB';
						}

						$('#fileInfo').show();
						$('#fileName').html('Name: ' + file.name);
						$('#fileSize').html('Size: ' + fileSize);
						$('#fileType').html('Type: ' + file.type);
					}else{
						noFileApi = true;
					}
				}
			});
		},
		buttons: {
			'Upload File': function (){
				if (noFileApi === true){
					$(this).find('form').submit();
					return;
				}
				previousBytesLoaded = 0;
				$('#uploadResponse').hide();
				$('#progressNumber').html('');

				$("#progressBar").progressbar({
					value: 0
				});

				function secondsToString(seconds) {
					var h = Math.floor(seconds / 3600);
					var m = Math.floor(seconds % 3600 / 60);
					var s = Math.floor(seconds % 3600 % 60);
					return ((h > 0 ? h + ":" : "") + (m > 0 ? (h > 0 && m < 10 ? "0" : "") + m + ":" : "0:") + (s < 10 ? "0" : "") + s);
				}

				$('#fileToUpload').each(function (){
					var fd = new FormData();
					fd.append("fileToUpload", this.files[0]);

					var xhr = new XMLHttpRequest();
					xhr.upload.addEventListener("progress", function (evt){
						if (evt.lengthComputable) {
							bytesUploaded = evt.loaded;
							bytesTotal = evt.total;
							var percentComplete = Math.round(evt.loaded * 100 / evt.total);
							var bytesTransfered = '';
							if (bytesUploaded > 1024*1024)
								bytesTransfered = (Math.round(bytesUploaded * 100/(1024*1024))/100).toString() + 'MB';
							else if (bytesUploaded > 1024)
								bytesTransfered = (Math.round(bytesUploaded * 100/1024)/100).toString() + 'KB';
							else
								bytesTransfered = (Math.round(bytesUploaded * 100)/100).toString() + 'Bytes';

							$("#progressBar").progressbar('value', percentComplete);
							$('#progressBar .ui-progressbar-value').html(percentComplete.toString() + '%');
							$('#transferBytesInfo').html(bytesTransfered);
							if (percentComplete == 100) {
								$('#progressInfo').hide();
								$('#uploadResponse')
									.show()
									.html('Processing Csv Contents, Please Wait...');
							}
						}
						else {
							$('#progressBar').html('unable to compute');
						}
					}, false);
					xhr.addEventListener("load", function (evt){
						evt = $.parseJSON(evt.target.responseText);
						clearInterval(intervalTimer);
						$('#uploadResponse')
							.show()
							.html(evt.message);
					}, false);
					xhr.addEventListener("error", function (evt){
						clearInterval(intervalTimer);
						evt = $.parseJSON(evt.target.responseText);
						alert(evt.message);
					}, false);
					xhr.addEventListener("abort", function (){
						clearInterval(intervalTimer);
						alert("The upload has been canceled by the user or the browser dropped the connection.");
					}, false);

					var sep = '?';
					if (o.action.indexOf('?') > -1){
						sep = '&';
					}

					xhr.open("POST", o.action + sep + 'rType=ajax');
					xhr.send(fd);

					intervalTimer = setInterval(function (){
						var currentBytes = bytesUploaded;
						var bytesDiff = currentBytes - previousBytesLoaded;
						if (bytesDiff == 0) return;
						previousBytesLoaded = currentBytes;
						bytesDiff = bytesDiff * 2;
						var bytesRemaining = bytesTotal - previousBytesLoaded;
						var secondsRemaining = bytesRemaining / bytesDiff;

						var speed = "";
						if (bytesDiff > 1024 * 1024)
							speed = (Math.round(bytesDiff * 100/(1024*1024))/100).toString() + 'MBps';
						else if (bytesDiff > 1024)
							speed =  (Math.round(bytesDiff * 100/1024)/100).toString() + 'KBps';
						else
							speed = bytesDiff.toString() + 'Bps';
						$('#transferSpeedInfo').html(speed);
						$('#timeRemainingInfo').html('| ' + secondsToString(secondsRemaining));

					}, 500);
				});
			}
		}
	});
}

$(document).ready(function () {
	$('.gridBody > .gridBodyRow')
		.die('click')
		.die('mouseover')
		.die('mouseout');

	$('.ui-icon-cancel').click(function (){
		$(this).parent().find('input').val('');
		$(this).parent().find('select').val('');
		$('.applyFilterButton').click();
	});

	$('.applyFilterButton').click(function (){
		var getVars = [];
		var ignoreParams = ['action'];
		$(this).parent().parent().find('input, select').each(function (){
			if ($(this).val() != ''){
				getVars.push($(this).attr('name') + '=' + $(this).val());
			}
			ignoreParams.push($(this).attr('name'));
		});
		js_redirect(js_app_link(js_get_all_get_params(ignoreParams) + getVars.join('&')));
	});

	$('.csvButton').click(function () {
		uploadWindow({
			title: 'Upload Csv File',
			action: js_app_link('appExt=multiStore&app=inventory&appPage=default&action=processCsvFile'),
			allowedTypes: ['csv', 'xls']
		});
	});

	$('.receiveShipmentButton').click(function (){
		var html = '<div title="Receive Shipment">' +
			'<div><b><u>Find Shipment By</u></b></div>' +
			'<div><table>' +
			'<tr>' +
			'<td>UPS ARS:</td>' +
			'<td><input type="text" name="by_tracking"></td>' +
			'</tr>' +
			'<tr>' +
			'<td colspan="2" align="center">OR</td>' +
			'</tr>' +
			'<tr>' +
			'<td>Barcode:</td>' +
			'<td><input type="text" name="by_barcode"></td>' +
			'</tr>' +
			'</table></div>' +
			'<div id="shipmentItems"></div>' +
			'</div>';
		$(html).dialog({
			height: 500,
			minWidth: 500,
			buttons: {
				'Find Shipment': function (){
					var self = this;
					var findBy = ($('input[name=by_barcode]').val() != '' ? 'Barcode' : 'Tracking');
					var value;
					if (findBy == 'Barcode'){
						value = $('input[name=by_barcode]').val();
					}else{
						value = $('input[name=by_tracking]').val();
					}
					$.getJSON(js_app_link('appExt=multiStore&app=inventory&appPage=default&action=findShipmentBy' + findBy + '&value=' + value), function (data){
						var tableRows = '';
						$.each(data.barcodes, function (){
							tableRows = tableRows + '<tr>' +
								'<td>' + this.barcode + '</td>' +
								'<td>' + this.tracking_number + '</td>' +
								'<td>' + this.status + '</td>' +
								'<td><button class="receive" data-transfer_id="' + this.transfer_id + '" data-barcode="' + this.barcode + '"><span>Receive</span></button></td>' +
								'</tr>';
						});
						$(self).find('#shipmentItems').html('<br><div style="text-align: center;font-size: 0.75em;">Handheld Scanner Click Field Below And Begin Scanning Barcodes To Receive Items<br><input type="text" name="barcode_scanned"></div><table style="width:450px;">' +
							'<thead>' +
							'<tr>' +
							'<th align="left">Barcode</th>' +
							'<th align="left">Tracking Number</th>' +
							'<th align="left">Status</th>' +
							'<th align="left"><button class="receiveAll"><span>Receive All</span></button></th>' +
							'</tr>' +
							'</thead>' +
							'<tbody>' +
							tableRows +
							'</tbody>' +
							'</table>');

						$(self).find('.receive').click(function (){
							var $itemRow = $(this).parent().parent();
							showAjaxLoader($itemRow, 'small');
							$.getJSON(js_app_link('appExt=multiStore&app=inventory&appPage=default&action=receiveTransfer&tID=' + $(this).data('transfer_id')), function (){
								$itemRow.find('td:eq(2)').html('R');
								$itemRow.find('button').remove();
								removeAjaxLoader($itemRow);
							});
						});

						$(self).find('.receiveAll').click(function (){
							$(self).find('.receive').click();
						});

						$(self).find('button').css('font-size', '.8em').button();

						$(self).find('input[name=barcode_scanned]').keypress(function (e){
							if (e.which == 13){
								$('button[data-barcode="' + $(this).val() + '"]').click();
								$(this).val('');
							}
						});
					});
				}
			}
		});
	});

	function validateBarcode(barcode, $row){
		$row.append('<span class="validating"> - <span class="ui-ajax-loader ui-ajax-loader-small" style="display:inline-block;"></span>Validating Barcode</span>');
		$.getJSON(js_app_link('appExt=multiStore&app=inventory&appPage=default&action=validateBarcode&code=' + barcode), function (data){
			$row.find('.validating').remove();
			if (data.isValid === false){
				$row.append('<span style="color:red;"> - Invalid Barcode Please Remove</span>');
			}
		});
	}

	$('.createShipmentButton').click(function (){
		var html = '<div title="Create Shipment">' +
			'<table>' +
			'<tr>' +
			'<td>UPS ARS:</td>' +
			'<td><input type="text" name="tracking_number"></td>' +
			'</tr>' +
			'<tr>' +
			'<td>From Store:</td>' +
			'<td>' + getStoreMenu('from_store_id') + '</td>' +
			'</tr>' +
			'<tr>' +
			'<td>To Store:</td>' +
			'<td>' + getStoreMenu('to_store_id') + '</td>' +
			'</tr>' +
			'</table>' +
			'<br>' +
			'<div style="text-align: center;font-size: 0.75em;">' +
			'<b>Handheld Scanner:</b> Click Field Below And Begin Scanning Barcodes To Add Items<br>' +
			'<b>By Hand:</b> Click Field Below, Enter Barcode And Hit Enter Button To Add Items<br>' +
			'<input type="text" name="barcode_scanned">' +
			'</div>' +
			'<div id="shipmentItems"></div>' +
			'</div>';
		var $dialogWindow = $(html).dialog({
			height: 500,
			minWidth: 500,
			buttons: {
				'Create Shipment': function (){
					var self = this;
					$.post(
						js_app_link('appExt=multiStore&app=inventory&appPage=default&action=createShipment'),
						$(self).find('input, select').serialize(),
						function (data){
							if (data.hasErrors == true){
								alert(data.errorMessage);
							}else{
								alert('Shipment Created');
								$(self).dialog('close').remove();
								js_redirect(js_app_link('appExt=multiStore&app=inventory&appPage=default'));
							}
						},
						'json'
					);
				}
			}
		});

		$dialogWindow.find('input[name=barcode_scanned]').keypress(function (e){
			if (e.which == 13){
				var enteredBarcode = $(this).val();
				var newRow = $('<div>' +
					'<span class="ui-icon ui-icon-close" style="display:inline-block;" tooltip="Remove From Shipment"></span>' +
					$(this).val() +
					'<input type="hidden" name="items[]" value="' + enteredBarcode + '">' +
					'</div>');
				newRow.find('.ui-icon-close').click(function (){
					$(this).parent().remove();
				});
				$dialogWindow.find('#shipmentItems').append(newRow);
				$(this).val('');

				validateBarcode(enteredBarcode, newRow);
			}
		});
	});
});
