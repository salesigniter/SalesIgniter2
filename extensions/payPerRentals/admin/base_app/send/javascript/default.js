function sendReservations(GridClass) {
	$.ajax({
		cache    : false,
		dataType : 'json',
		data     : GridClass.getDataKey() + '=' + GridClass.getSelectedData('reservation_id'),
		type     : 'post',
		//beforeSend: showAjaxLoader,
		//complete: removeAjaxLoader,
		url      : js_app_link('appExt=payPerRentals&app=send&appPage=default&action=sendReservations'),
		success  : function (data) {
			$.each(data.Arr, function (){
				$('tr[data-reservation_id=' + this + ']').remove();
			});

			if (GridClass.getSelectedRows().size() > 0){
				alert('Highlighted reservations could not be sent.');
			}else{
				alert('Reservations successfully sent out.');
			}
		}
	});
}

$(document).ready(function () {
	var $PageGrid = $('.gridContainer');
	$PageGrid.find('.makeDatepicker').datepicker({
		firstDay   : 0,
		dateFormat : jsLanguage.getDateFormat('short')
	});

	$PageGrid.newGrid('option', 'buttons', [
		{
			selector          : '.updateReservationsButton',
			disableIfNone     : false,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				$.ajax({
					cache      : false,
					url        : js_app_link('appExt=payPerRentals&app=send&appPage=default&action=statusReservations'),
					dataType   : 'json',
					data       : $PageGrid.find('input, select, textarea').serialize(),
					type       : 'post',
					beforeSend : function () {
						showAjaxLoader(GridClass.GridElement, 'xlarge');
					},
					complete   : function () {
						removeAjaxLoader(GridClass.GridElement);
					},
					success    : function (data) {

					}
				});
			}
		},
		{
			selector          : '.payReservationsButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				$.ajax({
					cache      : false,
					url        : js_app_link('appExt=payPerRentals&app=send&appPage=default&action=payReservations'),
					dataType   : 'json',
					data       : GridClass.getDataKey() + '=' + GridClass.getSelectedData('reservation_id'),
					type       : 'post',
					beforeSend : function () {
						showAjaxLoader(GridClass.GridElement, 'xlarge');
					},
					complete   : function () {
						removeAjaxLoader(GridClass.GridElement);
					},
					success    : function (data) {

					}
				});
			}
		},
		{
			selector          : '.sendReservationsButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				$.ajax({
					cache      : false,
					url        : js_app_link('appExt=payPerRentals&app=send&appPage=default&action=checkReservationsForMaintenance'),
					dataType   : 'json',
					data       : GridClass.getDataKey() + '=' + GridClass.getSelectedData('reservation_id'),
					type       : 'post',
					beforeSend : function () {
						showAjaxLoader(GridClass.GridElement, 'xlarge');
					},
					complete   : function () {
						removeAjaxLoader(GridClass.GridElement);
					},
					success    : function (data) {
						$('<div id="dialog-mesage" title="Pre hire check">' + data.popupContent + '</div>').dialog({
							modal   : true,
							width   : 800,
							height  : 700,
							close   : function (e, ui) {
								$(this).dialog('destroy').remove();
							},
							open    : function (e, ui) {
								//instance.hide();

								$('button, a[type="button"]').button();
								$('.gridBody > .gridBodyRow').click(function () {
									if ($(this).hasClass('state-active')){
										return;
									}

									$('.gridButtonBar').find('button').button('enable');
								});

								if ($('#lastBarcode').val() != ''){
									$('.gridBody > .gridBodyRow[data-barcode_id="' + $('#lastBarcode').val() + '"]').trigger('click');
								}

								$('.editButton').click(function () {

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
										buttonEl   : this,
										gridEl     : $('.gridContainer'),
										contentUrl : js_app_link(getVars.join('&')),
										onShow     : function () {
											var self = this;

											$(self).find('.cancelButton').click(function () {
												var instance = CKEDITOR.instances['commentID'];
												if (instance){
													instance.setData('');
													instance.destroy();
												}
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

											$(self).find('.saveButton').click(function () {
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
													cache    : false,
													url      : js_app_link(getVars.join('&')),
													dataType : 'json',
													data     : $(self).find('*').serialize(),
													type     : 'post',
													success  : function (data) {
														$(self).effect('fade', {
															mode : 'hide'
														}, function () {
															$('.gridContainer').effect('fade', {
																mode : 'show'
															}, function () {
																$(self).remove();
															});
														});
														$('.gridBody > .gridBodyRow').each(function () {
															if ($(this).attr('data-barcode_id') == data.removed){
																$(this).remove();
															}
														});
														if ($('.reservations').filter(':checked').size() == 1){

															sendReservations();
															if ($('#dialog-mesage')){
																$('#dialog-mesage').dialog('destroy').remove();
															}

														}
														else {
															alert('Rental successfully passed maintenance. Now press "Send Reservation" button or check another item.');
														}
													}
												});
											});

											//instance.hide();
											$('#commentID').hide();
											$('.isB').click(function () {
												CKEDITOR.replace('commentID', {
													toolbar : 'Simple'
												});
												//disable save button
												$('.saveButton').attr('disabled', 'disabled');
												//ajax check of mID current_type..if is before send return a dropdown with available barcodes..if select from dropdown enable save button
												var $myForm = $('.newWindowContainer');
												showAjaxLoader($myForm, 'large');
												$.ajax({
													cache    : false,
													dataType : 'json',
													data     : 'mID=' + $('#mid').attr('mid'),
													type     : 'get',
													url      : js_app_link('appExt=payPerRentals&app=maintenance&appPage=default&action=checkBeforeSend'),
													success  : function (data) {
														removeAjaxLoader($myForm);
														if (data.isBefore){
															$myForm.find('.ui-dialog-content').append(data.dropDown);
															$('#availBarcodes').change(function () {
																if ($(this).val() != '0'){
																	$('.saveButton').removeAttr('disabled');
																}
																else {
																	$('.saveButton').attr('disabled', 'disabled');
																}
															});
														}
														else {
															$('.saveButton').removeAttr('disabled');
														}
													}
												});
											});
											$('.isG').click(function () {

												var instance = CKEDITOR.instances['commentID'];
												if (instance){
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
							buttons : {
								SendReservations : function () {
									var instance = CKEDITOR.instances['commentID'];
									if (instance){
										instance.setData('');
										instance.destroy();
									}
									sendReservations(GridClass);
									$(this).dialog('destroy').remove();
								}
							}
						});

					}
				});
			}
		}
	]);
});