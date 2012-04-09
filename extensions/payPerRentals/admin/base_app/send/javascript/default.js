function showAjaxLoader1(){
	$('#ajaxLoader').dialog({
		modal: true,
		resizable: false,
		draggable: false,
		position: 'center'
	}).show();
}

function removeAjaxLoader1(){
	$('#ajaxLoader').dialog('close');
}
function updateRes(valType){
	var dataArr = new Object();
	dataArr.start_date = $('#start_date').val();
	dataArr.end_date = $('#end_date').val();
	dataArr.highlightOID = $('#highlight').val();
	dataArr.filter_status = $('#filterStatus').val();
	dataArr.filter_pay = $('#filterPay').val();
	if(valType == 'e'){
		if($('#eventSort').length){
			dataArr.eventSort = $('#eventSort').attr('type');
		}
	}
	if(valType == 'g'){
		if($('#gateSort').length){
			dataArr.gateSort = $('#gateSort').attr('type');
		}
	}
	$.ajax({
		cache: false,
		dataType: 'html',
		data: dataArr,
		beforeSend: showAjaxLoader1,
		complete: removeAjaxLoader1,
		url: js_app_link('appExt=payPerRentals&app=send&appPage=default&action=getReservations'),
		success: function (data){
			$('tbody', $('#reservationsTable')).html(data);
			$('.barcodeReplacement').keyup(function() {

				var link = js_app_link('appExt=payPerRentals&app=send&appPage=default&action=getBarcodes');
				var $barInput = $(this);
				$(this).autocomplete({
					source: function(request, response) {
						$.ajax({
							url: link,
							data: 'resid='+$barInput.attr('resid')+'&term='+request.term,
							dataType: 'json',
							type: 'POST',
							success: function(data){
								response(data);
							}
						});
					},
					minLength: 0,
					select: function(event, ui) {
						$barInput.val(ui.item.label);
						$barInput.attr('barid', ui.item.value);
						return false;
					}
				});
			});

			$('.barcodeReplacement').focus(function(){
				if($(this).val() == ''){
					$(this).keyup().autocomplete("search", "");
				}
			});
			if($('.reservations').filter(':checked').size() > 0){
				submitRes();
			}

		}
	});
}

function sendReser(){
	$.ajax({
		cache: false,
		dataType: 'json',
		data: $('#reservationsTable *').serialize(),
		type:'post',
		//beforeSend: showAjaxLoader,
		//complete: removeAjaxLoader,
		url: js_app_link('appExt=payPerRentals&app=send&appPage=default&action=sendReservations'),
		success: function (data){
			$('.reservations', $('#reservationsTable')).each(function (){
				if (this.checked){
					if (!jQuery.inArray($(this).val(), data.Arr)){
						$(this).parent().parent().remove();
						alert('Reservations successuful checked out.');
					}else{
						alert('Checked reservations could not be sent.');
					}
				}
			});
		}
	});
}

//I have to check if maintenance pre-hire exists and if not I shouldn't show this screen
function submitRes(){
	$.ajax({
		cache: false,
		dataType: 'json',
		data: $('#reservationsTable *').serialize(),
        type:'post',
		beforeSend: showAjaxLoader1,
		complete: removeAjaxLoader1,
		url: js_app_link('appExt=payPerRentals&app=send&appPage=default&action=checkReservationsForMaintenance'),//js_app_link('appExt=payPerRentals&app=send&appPage=default&action=sendReservations'),
		success: function (data){
			$( '<div id="dialog-mesage" title="Pre hire check">'+data.popupContent+'</div>' ).dialog({
				modal: true,
				width:800,
				height:700,
				close: function (e, ui){
					$(this).dialog('destroy').remove();
				},
				open: function (e, ui){
					//instance.hide();

					$('button, a[type="button"]').button();
					$('.gridBody > .gridBodyRow').click(function (){
						if ($(this).hasClass('state-active')) return;

						$('.gridButtonBar').find('button').button('enable');
					});

					if($('#lastBarcode').val()!= ''){
						$('.gridBody > .gridBodyRow[data-barcode_id="'+$('#lastBarcode').val()+'"]').trigger('click');
					}

					$('.editButton').click(function (){

						var getVars = [];
						getVars.push('appExt=payPerRentals');
						getVars.push('app=maintenance');
						getVars.push('appPage=default');
						getVars.push('action=getActionWindow');
						getVars.push('window=new');
						if ($('.gridBodyRow.state-active').size() > 0){
							getVars.push('mID=' + $('.gridBodyRow.state-active').attr('data-barcode_id'));
							getVars.push('type=' + $('.gridBodyRow.state-active').attr('data-type'));
						}

						gridWindow({
							buttonEl: this,
							gridEl: $('.gridContainer'),
							contentUrl: js_app_link(getVars.join('&')),
							onShow: function (){
								var self = this;



								$(self).find('.cancelButton').click(function (){
									var instance = CKEDITOR.instances['commentID'];
									if(instance){
										instance.setData('');
										instance.destroy();
									}
									$(self).effect('fade', {
										mode: 'hide'
									}, function (){
										$('.gridContainer').effect('fade', {
											mode: 'show'
										}, function (){
											$(self).remove();
										});
									});
								});

								$(self).find('.saveButton').click(function (){
									var getVars = [];
									getVars.push('appExt=payPerRentals');
									getVars.push('app=maintenance');
									getVars.push('appPage=default');
									getVars.push('action=save');
									if ($('.gridBodyRow.state-active').size() > 0){
										getVars.push('mID=' + $('.gridBodyRow.state-active').attr('data-barcode_id'));
										getVars.push('type=' + $('.gridBodyRow.state-active').attr('data-type'));
									}

									$.ajax({
										cache: false,
										url: js_app_link(getVars.join('&')),
										dataType: 'json',
										data: $(self).find('*').serialize(),
										type: 'post',
										success: function (data){
											$(self).effect('fade', {
												mode: 'hide'
											}, function (){
												$('.gridContainer').effect('fade', {
													mode: 'show'
												}, function (){
													$(self).remove();
												});
											});
											$('.gridBody > .gridBodyRow').each(function(){
												if($(this).attr('data-barcode_id') == data.removed){
													$(this).remove();
												}
											});
											if($('.reservations').filter(':checked').size() == 1){

												sendReser();
												if($('#dialog-mesage')){
													$('#dialog-mesage').dialog('destroy').remove();
												}

											}else{
												alert('Rental successfully passed maintenance. Now press "Send Reservation" button or check another item.');
											}
										}
									});
								});

								//instance.hide();
								$('#commentID').hide();
								$('.isB').click(function(){
									CKEDITOR.replace('commentID', {
										toolbar : 'Simple'
									});
									//disable save button
									$('.saveButton').attr('disabled', 'disabled');
									//ajax check of mID current_type..if is before send return a dropdown with available barcodes..if select from dropdown enable save button
									var $myForm = $('.newWindowContainer');
									showAjaxLoader($myForm, 'large');
									$.ajax({
										cache: false,
										dataType: 'json',
										data: 'mID='+$('#mid').attr('mid'),
										type:'get',
										url: js_app_link('appExt=payPerRentals&app=maintenance&appPage=default&action=checkBeforeSend'),
										success: function (data){
											removeAjaxLoader($myForm);
											if(data.isBefore){
												$myForm.find('.ui-dialog-content').append(data.dropDown);
												$('#availBarcodes').change(function(){
													if($(this).val() != '0'){
														$('.saveButton').removeAttr('disabled');
													}else{
														$('.saveButton').attr('disabled', 'disabled');
													}
												});
											}else{
												$('.saveButton').removeAttr('disabled');
											}
										}
									});
								});
								$('.isG').click(function(){

									var instance = CKEDITOR.instances['commentID'];
									if(instance){
										instance.setData('');
										instance.destroy();
									}
									$('#availBarcodes').remove();
									$('#commentID').hide();
								});
							}
						});
					});

				},
				buttons: {
					SendReservations: function() {
						var instance = CKEDITOR.instances['commentID'];
						if(instance){
							instance.setData('');
							instance.destroy();
						}
						sendReser();
						$(this).dialog('destroy').remove();
					}
				}
			});

		}
	});
}


/*function submitRes(){
	$.ajax({
		cache: false,
		dataType: 'json',
		data: $('#reservationsTable *').serialize(),
		type:'post',
		beforeSend: showAjaxLoader,
		complete: removeAjaxLoader,
		url: js_app_link('appExt=payPerRentals&app=send&appPage=default&action=sendReservations'),
		success: function (data){
			$('.reservations', $('#reservationsTable')).each(function (){
				 if (this.checked){
				    $(this).parent().parent().remove();
				 }
			 });
		}
	});
}
 */



function payRes(){

	$.ajax({
		cache: false,
		dataType: 'json',
		data: $('#reservationsTable *').serialize(),
		type:'post',
		beforeSend: showAjaxLoader,
		complete: removeAjaxLoader,
		url: js_app_link('appExt=payPerRentals&app=send&appPage=default&action=payReservations'),
		success: function (data){
			$('#errMsg').html(data.errMsg);
			updateRes();
		}
	});
}


function statusRes(){

	$.ajax({
		cache: false,
		dataType: 'json',
		data: $('#reservationsTable *').serialize(),
		type:'post',
		beforeSend: showAjaxLoader1,
		complete: removeAjaxLoader1,
		url: js_app_link('appExt=payPerRentals&app=send&appPage=default&action=statusReservations'),
		success: function (data){
			updateRes();
		}
	});
}


var dayShortNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

$(document).ready(function (){
	updateRes();
	$('#get_res').click(updateRes);
	$('#pay_res').click(payRes);
	$('#status_res').click(statusRes);
	$('#send').click(submitRes);

	$('#DP_startDate').datepicker({
		dateFormat: 'yy-mm-dd',
		gotoCurrent: true,
		altField: '#start_date',
		dayNamesMin: dayShortNames
	});

	$('#DP_endDate').datepicker({
		dateFormat: 'yy-mm-dd',
		gotoCurrent: true,
		altField: '#end_date',
		dayNamesMin: dayShortNames
	});

	$('#eventSort').css({'cursor':'pointer'});
	$('#gateSort').css({'cursor':'pointer'});

	$('#eventSort').click(function(){
		updateRes('e');
		if($(this).attr('type') == 'ASC'){
			$(this).attr('type','DESC');
		}else{
			$(this).attr('type','ASC');
		}
	});
	$('#gateSort').click(function(){
		updateRes('g');
		if($(this).attr('type') == 'ASC'){
			$(this).attr('type','DESC');
		}else{
			$(this).attr('type','ASC');
		}
	});


});