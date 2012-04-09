$(document).ready(function (){
	$('.gridBody > .gridBodyRow').click(function (){
		if ($(this).hasClass('state-active')) return;

		$('.gridButtonBar').find('button').button('enable');
	});

	var productTypes = [];
	productTypes.push(['standard', 'Standard']);
	productTypes.push(['package', 'Package']);

	$('.gridButtonBar').find('.newButton').click(function (){
		$('#typeSelectPopup').remove();

		var optionsHtml = '';
		$.each(productTypes, function (){
			optionsHtml += '<option value="' + this[0] + '">' + this[1] + '</option>';
		});
		var PopupBlock = $('<div id="typeSelectPopup"></div>')
			.addClass('ui-widget ui-widget-content ui-corner-all')
			.html('<span style="position:absolute;top:.2em;right:.2em;" class="ui-icon ui-icon-closethick"></span>Please Select A Type<br><select><option selected="selected">Please Select</option>' + optionsHtml + '</select>')
			.css({
				position: 'absolute',
				background: '#cccccc',
				boxShadow: '0px 3px 4px 0px #CCC',
				padding: '1em',
				top: $(this).offset().top + $(this).height(),
				left: $(this).offset().left
			}).appendTo(document.body);

		if ((PopupBlock.offset().left + PopupBlock.width()) >= $(window).width()){
			PopupBlock.css('left', $(this).offset().left - PopupBlock.width() + $(this).width());
		}

		PopupBlock.find('select').change(function (){
			js_redirect(js_app_link('app=products&appPage=new_product&productType=' + $(this).val()));
		});

		PopupBlock.find('.ui-icon-closethick').click(function (){
			PopupBlock.remove();
		});
	});

	$('.gridButtonBar').find('.editButton').click(function (){
		var productId = $('.gridBodyRow.state-active').attr('data-product_id');
		js_redirect(js_app_link('app=products&appPage=new_product&pID=' + productId));
	});

	$('.gridButtonBar').find('.copyButton').click(function (){
		var productId = $('.gridBodyRow.state-active').attr('data-product_id');
		js_redirect(js_app_link('app=products&appPage=default&action=copyProduct&products_id=' + productId));
	});

	$('.gridButtonBar').find('.invButton').click(function (){
		var productId = $('.gridBodyRow.state-active').attr('data-product_id');
		js_redirect(js_app_link('app=products&appPage=new_product&pID=' + productId) + '#tab_pricing');
	});

	$('.gridButtonBar').find('.deleteButton').click(function (){
		var productId = $('.gridBodyRow.state-active').attr('data-product_id');

		confirmDialog({
			confirmUrl: js_app_link('app=products&appPage=default&action=deleteProductConfirm&products_id=' + productId),
			title: 'Confirm Delete',
			content: 'Are you sure you want to delete this product?',
			success: function (){
				js_redirect(js_app_link('app=products&appPage=default'));
			}
		});
	});

	$('.setExpander').click(function (){
		if ($(this).hasClass('ui-icon-triangle-1-s')){
			$(this).removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-e');
			$('tr[box_id=' + $(this).parent().parent().attr('infobox_id') + ']').hide();
		}else{
			$(this).removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-s');
			$('tr[box_id=' + $(this).parent().parent().attr('infobox_id') + ']').show();
		}
	});
});