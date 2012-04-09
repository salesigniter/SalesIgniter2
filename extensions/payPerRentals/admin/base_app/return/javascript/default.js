var dayShortNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];


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

	$.ajax({
		cache: false,
		dataType: 'html',
		data: dataArr,
		beforeSend: showAjaxLoader1,
		complete: removeAjaxLoader1,
		url: js_app_link('appExt=payPerRentals&app=return&appPage=default&action=getReservations'),
		success: function (data){
			$('tbody', $('#reservationsTable')).html(data);

		}
	});
}

$(document).ready(function (){
	$('#DP_startDate').datepicker({
		dateFormat: 'yy-mm-dd',
		gotoCurrent: true,
		altField: '#start_date',
		dayNamesMin: dayShortNames
	});
	$('#ajaxLoader').hide();

	$('#DP_endDate').datepicker({
		dateFormat: 'yy-mm-dd',
		gotoCurrent: true,
		altField: '#end_date',
		dayNamesMin: dayShortNames
	});

	$('#filter_apply').click(function(){
		updateRes();
		return false;
	});

	$('.returnButton').click(function(){

			$.ajax({
				cache: false,
				dataType: 'json',
				data: $('#reservationsTable *').serialize(),
				type:'post',

				url: js_app_link('appExt=payPerRentals&app=return&appPage=default&action=return'),
				success: function (data){
					//on both send and return I have to check if  use maintenance is used...I will put a js variable
					alert('Reservation Returned. Please check Maintenance');
					$.ajax({
						cache: false,
						dataType: 'json',
						data: $('#reservationsTable *').serialize(),
						type:'post',
						url: js_app_link('appExt=payPerRentals&app=return_barcode&appPage=default&action=checkReservationsForMaintenance&lastBarcode='+data.lastBarcode),//js_app_link('appExt=payPerRentals&app=send&appPage=default&action=sendReservations'),
						success: function (data){
							$( '<div id="dialog-mesage" title="Return hire check">'+data.popupContent+'</div>' ).dialog({
								modal: true,
								width:800,
								height:700,
								close: function (e, ui){
									js_redirect(js_app_link('appExt=payPerRentals&app=return&appPage=default'));
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

															alert('Maintenance for items was done. Click Close to finish');

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
									Close: function() {
										var instance = CKEDITOR.instances['commentID'];
										if(instance){
											instance.setData('');
											instance.destroy();
										}
										js_redirect(js_app_link('appExt=payPerRentals&app=return&appPage=default'));
										$(this).dialog('destroy').remove();
									}
								}
							});

						}
					});
				}
			});
			return false;
		})



});
