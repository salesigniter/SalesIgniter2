//------------------------- BOX set begin block -----------------------------//
function show_box_panel() {
	if (document.getElementById('products_in_box').selectedIndex == 1){
		document.getElementById('box_panel').style.display = 'inline';
	}
	else {
		document.getElementById('box_panel').style.display = 'none';
	}
}
//------------------------- BOX set end block -----------------------------//

function doRound(x, places) {
	return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}

function getTaxRate(priceId) {
	var parameterVal = $('#tax_class_id_' + priceId.replace('products_price_', '')).val();

	if ((parameterVal > 0) && (tax_rates[parameterVal] > 0)){
		return tax_rates[parameterVal];
	}
	else {
		return 0;
	}
}

function applyRowOverlay($thisRow, html, callBack) {
	$('<div>').attr('id', 'overlay').css({
		position   : 'absolute',
		display    : 'none',
		top        : $thisRow.offset().top,
		left       : $thisRow.offset().left,
		width      : $thisRow.width(),
		height     : $thisRow.height(),
		background : '#000000',
		color      : '#FFFFFF',
		textAlign  : 'center'
	}).html(html).show().appendTo(document.body).fadeTo('fast', .6, callBack);
}

function removeRowOverlay($thisRow, removeRow) {
	$('#overlay').remove();
	if (removeRow == true){
		$thisRow.remove();
	}
	else {
		$thisRow.fadeTo('fast', 1);
	}
}

function addPackageProduct() {
	var $thisRow = $(this).parent().parent();
	if ($('#packageProductName', $thisRow).val() == ''){
		alert('Must enter a product.');
		return false;
	}

	/*if ($('#packageProductType').val() != 'reservation'){
	 alert('Only "Pay Per Rental" products can be added to this package.');
	 return false;
	 }*/
	var $tabDiv = $thisRow.parent().parent().parent();

	$thisRow.fadeTo('fast', .3, function () {
		applyRowOverlay($thisRow, 'Adding Product To Package, Please Wait', function () {
			var urlVars = $('*', $thisRow).serialize();
			$.ajax({
				cache    : false,
				url      : js_app_link('app=products&appPage=new_product&action=addPackageProduct&packageParentID=' + $_GET['product_id'] + '&packageProductID=' + $('#packageProductName').attr('selectedProduct') + '&' + urlVars),
				dataType : 'json',
				success  : function (data) {
					if (typeof data.errorMsg == 'undefined'){
						var $newRow = $(data.tableRow);
						$newRow.appendTo($('#packageProducts', $tabDiv));

						$('.deletePackageProduct', $newRow).click(deletePackageProduct);
						$('.updatePackageProduct', $newRow).click(updatePackageProduct);
						if ($newRow.prev().hasClass('rowEven')){
							$newRow.addClass('rowOdd');
						}
						else {
							$newRow.addClass('rowEven');
						}
					}
					else {
						alert(data.errorMsg);
					}
					removeRowOverlay($thisRow, false);
				}
			});
		});
	});
}

function deletePackageProduct() {
	var $thisRow = $(this).parent().parent();

	$thisRow.fadeTo('fast', .3, function () {
		applyRowOverlay($thisRow, 'Deleting Product From Package, Please Wait', function () {
			$.ajax({
				cache    : false,
				url      : js_app_link('app=products&appPage=new_product&action=deletePackageProduct'),
				data     : $('*', $thisRow).serialize(),
				dataType : 'json',
				success  : function (data) {
					var removeRow = false;
					if (typeof data.errorMsg == 'undefined'){
						$thisRow.nextAll().each(function () {
							if ($(this).hasClass('rowOdd')){
								$(this).removeClass('rowOdd').addClass('rowEven');
							}
							else {
								$(this).removeClass('rowEven').addClass('rowOdd');
							}
						});
						removeRow = true;
					}
					else {
						alert(data.errorMsg);
					}
					removeRowOverlay($thisRow, removeRow);
				}
			});
		});
	});
}

function updatePackageProduct() {
	var $thisRow = $(this).parent().parent();

	$thisRow.fadeTo('fast', .3, function () {
		applyRowOverlay($thisRow, 'Updating Product In Package, Please Wait', function () {
			$.ajax({
				cache    : false,
				url      : js_app_link('app=products&appPage=new_product&action=updatePackageProduct'),
				data     : $('*', $thisRow).serialize(),
				dataType : 'json',
				success  : function (data) {
					if (typeof data.errorMsg != 'undefined'){
						alert(data.errorMsg);
					}
					removeRowOverlay($thisRow, false);
				}
			});
		});
	});
}

function popupWindowComments(url, barcodeId, w, h) {
	$('<div id="commentsWindow"></div>').dialog({
		autoOpen : true,
		width    : w,
		height   : h,
		close    : function (e, ui) {
			$(this).dialog('destroy').remove();
		},
		open     : function (e, ui) {
			$(e.target).html('<textarea id="commentFCK" rows="30" cols="10" name="commentBarcode"></textarea>');
			showAjaxLoader($('#commentsWindow'), 'xlarge');
			var urlGet = [].concat(url);
			urlGet.push("barcode_id=" + barcodeId);
			urlGet.push("action=getBarcodeComment");
			$.ajax({
				cache    : false,
				url      : js_app_link(urlGet.join("&")),
				dataType : 'json',
				success  : function (data) {
					hideAjaxLoader($('#commentsWindow'));
					$('#commentFCK').val(data.html);
					var instance = CKEDITOR.instances['commentFCK'];
					if (instance){
						CKEDITOR.remove(instance);
					}
					CKEDITOR.replace('commentFCK');
				}
			});
		},
		buttons  : {
			'Save' : function () {
				//ajax call to save comment on success
				dialog = $(this);
				showAjaxLoader($('#commentsWindow'), 'xlarge');
				var instance = CKEDITOR.instances['commentFCK'];
				var urlSave = [].concat(url);
				urlSave.push("action=saveBarcodeComment");
				$.ajax({
					cache    : false,
					url      : js_app_link(urlSave.join("&")),
					data     : "barcode_id=" + barcodeId + "&comments=" + instance.getData(),
					type     : 'post',
					dataType : 'json',
					success  : function (data) {
						hideAjaxLoader($('#commentsWindow'));
						dialog.dialog('close');
					}
				});
			},
			Cancel : function () {
				$(this).dialog('close');
			}
		}
	});
	return false;
}

var distance = 10;
var time = 250;
var hideDelay = 500;

var hideDelayTimer = null;
var currentTd = null;

// tracker
var beingShown = false;
var shown = false;

var trigger = $(this);
var popup = $('.events ul', this).css('opacity', 0);

$(document).ready(function () {
	$('.makeFCK').each(function () {
		$(this).data('editorInstance', CKEDITOR.replace(this));
	});

	$('.makeTabs').tabs();

	/*$('#tab_description').tabs();
	 $('.PurchaseTypeInventoryTabs_normal').tabs();
	 $('#purchaseTypeTabs .makeVerticalTabs').tabs();
	 $('#purchaseTypeTabs').tabs();
	 $('#inventory_tab_normal_tabs').tabs();
	 $('#inventory_tab_attribute_tabs').tabs();
	 $('#inventory_tabs').tabs();
	 $('#tabs_packages').tabs();
	 $('#tab_container').tabs();*/

	makeTabsVertical('#tab_container');

	$('.useGlobalPricing').click(function () {
		if ($(this).val() == '0'){
			$(this).parent().find('.pricingTable').show();
		}
		else {
			$(this).parent().find('.pricingTable').hide();
		}
	});

	$('.netPricing').keyup(function () {
		var taxRate = getTaxRate($(this).attr('id'));
		var grossValue = $(this).val();

		if (taxRate > 0){
			grossValue = grossValue * ((taxRate / 100) + 1);
		}
		$('#' + $(this).attr('id') + '_gross').val(doRound(grossValue, 4));
	});

	$('.grossPricing').keyup(function () {
		var id = $(this).attr('id');
		var taxRate = getTaxRate(id);
		var netValue = $(this).val();

		if (taxRate > 0){
			netValue = netValue / ((taxRate / 100) + 1);
		}
		$('#' + id.replace('_gross', '')).val(doRound(netValue, 4));
	});

	$('.taxClassId').change(
		function () {
			$('.netPricing').trigger('keyup');
		}).trigger('change');

	$('.printLabels').each(function () {
		var button = this;
		$(this).labelPrinter({
			labelTypes : ['8160-b'],
			printUrl   : js_app_link('app=products&appPage=new_product&action=genLabels'),
			getData    : function () {
				return $(button).parentsUntil('.ui-tabs-panel').last().find('input[name="barcodes[]"]:checked').serialize();
			},
			beforeShow : function () {
				if ($(button).parentsUntil('.ui-tabs-panel').last().find('input[name="barcodes[]"]:checked').size() <= 0){
					alert('Please select barcodes to print using the checkboxes on the left of the table rows');
					return false;
				}
				return true;
			}
		});
	});

	var productsArr = [];
	$('option', $('#productDropMenu')).each(function () {
		var productTypes = $(this).attr('productTypes');
		var obj = {
			id    : $(this).val(),
			text  : $(this).html(),
			types : productTypes
		};
		productsArr.push(obj);
	});

	if ($('#packageProductName').size() > 0){
		$('#packageProductName').autocomplete(productsArr, {
			formatItem   : function (row, i, max) {
				return row.text;
			},
			formatMatch  : function (row, i, max) {
				return row.text;
			},
			formatResult : function (row) {
				return row.text;
			}
		});
		$('#packageProductName').result(function (event, data, formatted) {
			$('option', $('#packageProductType')).remove();
			var productTypes = data.types.split(';');
			$(productTypes).each(function () {
				var tInfo = this.split(',');
				$('#packageProductType').append('<option value="' + tInfo[0] + '">' + tInfo[1] + '</option>');
			});
			$('#packageProductName').attr('selectedProduct', data.id);
		});
	}

	$('.serialsGrid').each(function (){
		var Grid = $(this);
		Grid.newGrid('option', 'buttons', [
			{
				selector          : '.addSerialButton',
				disableIfNone     : false,
				disableIfMultiple : false,
				click             : function (e, GridClass) {
					GridClass.showConfirmDialog({
						title             : 'Enter Serial Number',
						content           : '<input type="text" name="serial_number">',
						confirmButtonText : 'Save',
						onConfirm         : function () {
							var self = this;
							$.post(js_app_link('app=products&appPage=new_product&action=checkSerial'), {
								serial : $(self).find('input[name=serial_number]').val()
							}, function (data) {
								if (data.success === false){
									alert('Serial Number Exists Already, Please Try Another.');
								}else{
									GridClass.addBodyRow({
										columns: [
											{ text: data.serial_number },
											{ text: data.status_name }
										]
									});
									$(self).dialog('close').remove();
								}
							}, 'json');
						},
						onCancel          : function () {
							$(this).dialog('close').remove();
						}
					});
				}
			},
			{
				selector          : '.genSerialButton',
				disableIfNone     : false,
				disableIfMultiple : false,
				click             : function (e, GridClass) {
					var StatusesJson = $.parseJSON(urldecode(Grid.data('available_statuses')));
					GridClass.showConfirmDialog({
						title             : 'Enter Serial Number',
						content           : 'How Many: <input type="text" name="gen_total"><br><input type="checkbox" name="addToTotal">Add To Total Available',
						confirmButtonText : 'Generate',
						onConfirm         : function () {
							var self = this;
							$.post(js_app_link('app=products&appPage=new_product&action=genSerial'), {
								default_status : $(GridClass.element).data('default_status'),
								gen_total      : $(self).find('input[name=gen_total]').val()
							}, function (data) {
								var addToTotal = $(self).find('input[name=addToTotal]').get(0).checked;
								var inputName = 'inventory_serial';
								if ($(GridClass.element).data('purchase_type')){
									inputName += '[' + $(GridClass.element).data('purchase_type') + ']';
								}

								var StatusTotalInput = $(GridClass.element)
									.parentsUntil('.ui-tabs-panel')
									.last()
									.find('.inventoryQuantity_' + $(GridClass.element).data('default_status'));

								var totalAvail = parseInt(StatusTotalInput.val());

								$.each(data.serials, function (){
									totalAvail++;
									var serial = this;

									var SelectBox = $('<select></select>')
										.addClass('serialNumberStatus')
										.attr('name', inputName + '[status][]')
										.data('previous_status', $(GridClass.element).data('default_status'));
									$.each(StatusesJson, function (){
										var NewOption = $('<option></option>')
											.attr('value', this.id)
											.append(this.text);
										if (serial.status_name == this.text){
											NewOption.attr('selected', 'selected');
										}
										SelectBox.append(NewOption);
									});

									GridClass.addBodyRow({
										columns: [
											{ align: 'center', text: '<input type="hidden" name="' + inputName + '[number][]" value="' + serial.serial_number + '">' + serial.serial_number },
											{ align: 'center', text: SelectBox }
										]
									});
								});

								if (addToTotal){
									StatusTotalInput.val(totalAvail);
								}
								$(self).dialog('close').remove();
							}, 'json');
						},
						onCancel          : function () {
							$(this).dialog('close').remove();
						}
					});
				}
			},
			{
				selector          : '.deleteSerialButton',
				disableIfNone     : true,
				disableIfMultiple : false,
				click             : function (e, GridClass) {
					GridClass.getSelectedRows().remove();
				}
			}
		]);

		$(this).on('change', '.serialNumberStatus', function (){
			var QuantityGrid = $(this).parentsUntil('.ui-tabs-panel').last().find('.quantityGrid');

			var NewStatusId = $(this).val();
			var OldStatusId = $(this).data('previous_status');

			var OldStatusInput = QuantityGrid.find('.inventoryQuantity_' + OldStatusId);
			var NewStatusInput = QuantityGrid.find('.inventoryQuantity_' + NewStatusId);

			var OldStatusVal = parseInt(OldStatusInput.val());
			var NewStatusVal = parseInt(NewStatusInput.val());

			OldStatusInput.val(OldStatusVal - 1);
			NewStatusInput.val(NewStatusVal + 1);
			$(this).data('previous_status', NewStatusId);
		});
	});

	$('.addBarcode').live('click', function () {
		var $thisRow = $(this).parent().parent();
		if ($('.barcodeNumber', $thisRow).val() == '' && $('.autogen:checked', $thisRow).size() == 0){
			alert('Must enter a barcode number.');
			return false;
		} else if ($('.autogen:checked', $thisRow).size() == 1 && $('.autogenTotal', $thisRow).val() == ''){
			alert('Must enter an amount of barcode to auto generate.');
			return false;
		}
		var $tabDiv = $thisRow.parentsUntil('.ui-tabs-panel').last();

		var linkParams = [];
		linkParams.push('rType=ajax');
		linkParams.push('app=products');
		linkParams.push('appPage=new_product');
		linkParams.push('action=addBarcode');
		linkParams.push('product_id=' + $_GET['product_id']);

		if ($(this).data('purchase_type')){
			linkParams.push('purchase_type=' + $(this).data('purchase_type'));
		}

		if ($(this).attr('data-attribute_string')){
			linkParams.push('aID_string=' + $(this).attr('data-attribute_string'));
		}

		$thisRow.fadeTo('fast', .3, function () {
			applyRowOverlay($thisRow, 'Adding Barcode, Please Wait', function () {
				$.ajax({
					cache    : false,
					url      : js_app_link(linkParams.join('&')),
					dataType : 'json',
					type     : 'post',
					data     : $thisRow.parent().find('input, select').serialize(),
					success  : function (data) {
						if (typeof data.errorMsg == 'undefined'){
							var $newRow = $(data.tableRow);
							$tabDiv.find('.grid .gridBody').append($newRow);
						}
						else {
							alert(data.errorMsg);
						}
						removeRowOverlay($thisRow, false);
					}
				});
			});
		});
	});

	$('.deleteBarcode').live('click', function () {
		var $thisRow = $(this).parent().parent();
		var barcodeID = $(this).attr('data-barcode_id');

		confirmDialog({
			title     : 'Delete Barcode',
			content   : 'Are you sure you want to delete this barcode?',
			onConfirm : function () {
				var linkParams = [];
				linkParams.push('app=products');
				linkParams.push('appPage=new_product');
				linkParams.push('action=deleteBarcode');
				linkParams.push('bID=' + barcodeID);
				linkParams.push('product_id=' + $_GET['product_id']);

				if ($(this).attr('data-purchase_type')){
					linkParams.push('purchaseType=' + $(this).attr('data-purchase_type'));
				}

				if ($(this).attr('data-attribute_string')){
					linkParams.push('aID_string=' + $(this).attr('data-attribute_string'));
				}

				$thisRow.fadeTo('fast', .3, function () {
					applyRowOverlay($thisRow, 'Deleting Barcode, Please Wait', function () {
						$.ajax({
							cache    : false,
							url      : js_app_link(linkParams.join('&')),
							dataType : 'json',
							success  : function (data) {
								var removeRow = false;
								if (typeof data.errorMsg == 'undefined'){
									removeRow = true;
								}
								else {
									alert(data.errorMsg);
								}
								removeRowOverlay($thisRow, removeRow);
							}
						});
					});
				});
				$(this).dialog('close').remove();
			}
		});

	});

	$('.updateBarcode').live('click', function () {
		var $thisRow = $(this).parent().parent();

		var linkParams = [];
		linkParams.push('app=products');
		linkParams.push('appPage=new_product');
		linkParams.push('action=updateBarcode');
		linkParams.push('product_id=' + $_GET['product_id']);
		linkParams.push('barcode_id=' + $(this).attr('data-barcode_id'))

		if ($(this).attr('data-purchase_type')){
			linkParams.push('purchaseType=' + $(this).attr('data-purchase_type'));
		}

		if ($(this).attr('data-attribute_string')){
			linkParams.push('aID_string=' + $(this).attr('data-attribute_string'));
		}

		$thisRow.fadeTo('fast', .3, function () {
			applyRowOverlay($thisRow, 'Updating Barcode, Please Wait', function () {
				$.ajax({
					cache    : false,
					url      : js_app_link(linkParams.join('&')),
					data     : $('*', $thisRow).serialize(),
					dataType : 'json',
					success  : function (data) {
						if (typeof data.errorMsg != 'undefined'){
							alert(data.errorMsg);
						}
						removeRowOverlay($thisRow, false);
					}
				});
			});
		});
	});

	/*Edit comments popup*/

	$('.commentBarcode').live('click', function () {
		var linkParams = [];
		linkParams.push('app=products');
		linkParams.push('appPage=new_product');

		/*if ($(this).attr('data-purchase_type')){
		 linkParams.push('purchaseType=' + $(this).attr('data-purchase_type'));
		 }

		 if ($(this).attr('data-attribute_string')){
		 linkParams.push('aID_string=' + $(this).attr('data-attribute_string'));
		 }*/

		popupWindowComments(linkParams, $(this).attr('data-barcode_id'), 800, 500);

		return false;

	});

	$('.checkAll').live('click', function () {
		var className = 'barcode_' + $(this).val();
		var allChecked = this.checked;
		$(this).parent().parent().parent().parent().find('.' + className).each(function () {
			this.checked = allChecked;
		});
	});

	$('.addPackageProduct').click(addPackageProduct);
	$('.deletePackageProduct').click(deletePackageProduct);
	$('.updatePackageProduct').click(updatePackageProduct);

	$('.useDatepicker').datepicker({
		dateFormat : 'yy-mm-dd'
	});

	$('#productOnOrder').click(function () {
		var $calendar = $('#productOnOrderCal');
		if (this.checked){
			$calendar.show();
		}
		else {
			$calendar.hide();
		}
	});

	$('.autogen').live('click', function () {
		if (this.checked){
			$('.barcodeNumber', $(this).parent().parent()).attr('disabled', 'disabled').addClass('ui-state-disabled');
			$('.autogenTotal', $(this).parent()).removeAttr('disabled').removeClass('ui-state-disabled');
		}
		else {
			$('.barcodeNumber', $(this).parent().parent()).removeAttr('disabled').removeClass('ui-state-disabled');
			$('.autogenTotal', $(this).parent()).attr('disabled', 'disabled').addClass('ui-state-disabled');
		}
	});

	$('.ajaxSave').click(function () {
		showAjaxLoader($(document.body), 'xlarge');

		$('.makeFCK').each(function () {
			if ($(this).data('editorInstance')){
				var ckEditor = $(this).data('editorInstance');

				$(this).val(ckEditor.getData());
			}
		});

		$.ajax({
			cache    : false,
			type     : 'post',
			url      : js_app_link('app=products&appPage=new_product&action=saveProduct&rType=ajax' + ($_GET['product_id'] > 0 ? '&product_id=' + $_GET['product_id'] : '')),
			data     : $('form[name="new_product"]').serialize(),
			dataType : 'json',
			success  : function (data) {
				$('.programDisabled').removeAttr('disabled').removeClass('ui-state-disabled').removeClass('programDisabled');
				productID = data.product_id;

				var $form = $('form[name=new_product]');
				if ($('input[name=product_id]', $form).size() <= 0){
					$('<input type="hidden"></input>').attr('name', 'product_id').val(productID).appendTo($form);
				}
				else {
					$('input[name=product_id]', $form).val(productID);
				}

				$('#newProductMessage').remove();
				hideAjaxLoader($(document.body));
			}
		});
		return false;
	});

	$('.ui-state-disabled').each(function () {
		$('input', this).each(function () {
			if (!$(this).attr('disabled')){
				$(this).attr('disabled', 'disabled').addClass('programDisabled');
			}
		});
		$('.ui-button', this).addClass('ui-state-disabled').addClass('programDisabled');
		$(this).addClass('programDisabled');
	});

	$('.ui-icon-closethick').live('mouseover mouseout', function (event) {
		if ($(this).hasClass('ui-state-disabled')){
			return false;
		}
		switch(event.type){
			case 'mouseover':
				this.style.cursor = 'pointer';
				//$(this).addClass('ui-state-hover');
				break;
			case 'mouseout':
				this.style.cursor = 'default';
				//$(this).removeClass('ui-state-hover');
				break;
		}
	});

	$('.additionalImagesList').on('click', '.removeAdditionalImage', function (){
		if ($(this).hasClass('ui-state-disabled')){
			return false;
		}
		var self = $(this);
		self.addClass('ui-state-disabled');

		var $ImageBox = self.parent();
		$ImageBox.addClass('ui-state-disabled');
		$ImageBox.find('input').attr('disabled', 'disabled');

		$('<button icon="ui-icon-undo"><span>Changed My Mind</span></button>')
			.insertBefore($(this).parent())
			.css({
				position: 'absolute',
				zIndex: 99
			})
			.position({
				at: 'center center',
				my: 'center center',
				of: $ImageBox
			})
			.button()
			.click(function (){
				self.removeClass('ui-state-disabled');
				$ImageBox.removeClass('ui-state-disabled');
				$ImageBox.find('input').removeAttr('disabled');
				$(this).remove();
			});
	});

	$('input[name="additional_image[]"]').filemanager({
		onSelect: function (e, selected){
			$(this.inputElement).parent().find('img').attr('src', 'imagick_thumb.php?width=150&height=150&path=rel&imgSrc=' + selected);
		}
	});

	$('.addAdditionalImage').click(function (){
		var Value = $(this).parent().find('.fileManagerInput').val();
		if (Value == ''){
			alert('No Images To Add');
		}else{
			$.each(Value.split(','), function (){
				var $newBox = $('<div class="ui-widget ui-widget-content additionalImageBox" style="width:300px;display:inline-block;margin:10px;text-align:center;padding:5px;position: relative;vertical-align: top;height: 190px;"></div>')
					.html('<span class="ui-icon ui-icon-closethick removeAdditionalImage" style="position: absolute;top: -12px;right: -12px;"></span>' +
					'<div style="height:160px;"><img src="imagick_thumb.php?width=150&height=150&path=rel&imgSrc=' + this + '"></div>' +
					'<input class="fileManager" data-files_source="' + jsConfig.get('DIR_FS_CATALOG') + 'templates/" data-is_multiple="false" name="additional_image[]" value="' + this + '" style="width:95%;box-sizing:border-box;margin:0 5px;">' +
					'</div>');
				$('.additionalImagesList').prepend($newBox);

				$newBox.find('.fileManager').filemanager({
					onSelect: function (e, selected){
						$(this.inputElement).parent().find('img').attr('src', 'imagick_thumb.php?width=150&height=150&path=rel&imgSrc=' + selected);
					}
				});
			});
			$(this).parent().find('.fileManagerInput').val('');
		}
	});

	/*
	 var blockCache = [];
	 $('.productType').each(function (){
	 $(this).click(function (){
	 var $blocks = $('*[depends="productType_' + $(this).val() + '"]');
	 if (this.checked){
	 $blocks.show();
	 }else{
	 $blocks.hide();
	 }
	 });

	 var $blocks = $('*[depends="productType_' + $(this).val() + '"]');
	 if (this.checked){
	 $blocks.each(function (){
	 $(this).show();
	 });
	 }else{
	 $blocks.each(function (){
	 $(this).hide();
	 });
	 }
	 });
	 */

	$('.inventoryCalander').datepick({
		dayNamesMin : ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
		beforeShow  : function (input, inst) {
			alert($(inst.dpDiv).find('.datepick-days-cell').size());
			$(inst.dpDiv).find('.datepick-days-cell').each(function () {
				//if (!$(this).hasClass('datepick-other-month')){
				$(this).append('<div style="border:1px solid black;"></div>');
				//}
			});
		}
	});

	$('.calFilter').change(function () {
		var $calDiv = $('.htmlcal', $(this).parent().parent().parent().parent()).parent();
		var o = {
			el           : $calDiv,
			month        : $('select[name=cal_month]', $calDiv).val(),
			year         : $('select[name=cal_year]', $calDiv).val(),
			purchaseType : $('.htmlcal', $calDiv).attr('purchase_type')
		};

		changeCalDate(o);
	});

	$('.htmlcal .htmlcal-curmonthyear .ui-icon-circle-triangle-w').live('click', function () {
		var $calDiv = $(this).parent().parent().parent().parent().parent().parent();
		var o = {
			el           : $calDiv,
			month        : (parseInt($('select[name=cal_month]', $calDiv).val()) - 1),
			year         : $('select[name=cal_year]', $calDiv).val(),
			purchaseType : $('.htmlcal', $calDiv).attr('purchase_type')
		};

		changeCalDate(o);
	});

	$('.htmlcal .htmlcal-curmonthyear .ui-icon-circle-triangle-e').live('click', function () {
		var $calDiv = $(this).parent().parent().parent().parent().parent().parent();
		var o = {
			el           : $calDiv,
			month        : (parseInt($('select[name=cal_month]', $calDiv).val()) + 1),
			year         : $('select[name=cal_year]', $calDiv).val(),
			purchaseType : $('.htmlcal', $calDiv).attr('purchase_type')
		};

		changeCalDate(o);
	});

	$('select[name=cal_month], select[name=cal_year]').live('change', function () {
		var $calDiv = $(this).parent().parent().parent().parent().parent().parent().parent();
		var o = {
			el           : $calDiv,
			month        : $('select[name=cal_month]', $calDiv).val(),
			year         : $('select[name=cal_year]', $calDiv).val(),
			purchaseType : $('.htmlcal', $calDiv).attr('purchase_type')
		};

		changeCalDate(o);
	});

	$('.date_has_popup').live('mouseover mouseout', function (e) {
		popup = $('.events ul', this);
		if (e.type == 'mouseover'){
			currentTd = this;
			var windowWidth = $(window).width();
			popup.css({
				bottom : 30,
				left   : -76
			}).show();

			if ((popup.outerWidth() + popup.offset().left) > windowWidth){
				popup.css('left', -(popup.outerWidth() - 76));
			}
		}
		else {
			popup.hide();
		}
	});

	$('.htmlcal').each(function () {
		var $calDiv = $(this).parent();
		changeCalDate({
			el           : $calDiv,
			month        : $('select[name=cal_month]', $calDiv).val(),
			year         : $('select[name=cal_year]', $calDiv).val(),
			purchaseType : $(this).attr('purchase_type')
		});
	});

	$('.viewCal').live('click', function () {
		var $calDiv = $('.htmlcal', $(this).parent().parent().parent().parent().parent().parent()).parent();
		changeCalDate({
			el           : $calDiv,
			month        : $('select[name=cal_month]', $calDiv).val(),
			year         : $('select[name=cal_year]', $calDiv).val(),
			purchaseType : $('.htmlcal', $calDiv).attr('purchase_type'),
			barcodeId    : $(this).attr('barcode_id')
		});
	});
});

function changeCalDate(o) {
	showAjaxLoader(o.el, 'xlarge');
	if ($('.calFilter', o.el.parent().parent()).val() != 'all'){
		var filterString = '&' + $('.calFilter', o.el.parent().parent()).attr('name') + '=' + $('.calFilter', o.el.parent().parent()).val();
	}
	$.ajax({
		cache    : false,
		url      : js_app_link('app=products&appPage=new_product&action=loadCalendar&purchase_type=' + o.purchaseType + '&products_id=' + $_GET['product_id'] + '&month=' + o.month + '&year=' + o.year + (o.barcodeId ? '&barcode_id=' + o.barcodeId : '') + (filterString ? filterString : '')),
		dataType : 'html',
		success  : function (data) {
			removeAjaxLoader(o.el);
			o.el.html(data);
		}
	});
}