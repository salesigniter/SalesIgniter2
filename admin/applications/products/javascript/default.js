$(document).ready(function () {
	var productTypes = [];
	productTypes.push(['standard', 'Standard']);
	productTypes.push(['package', 'Package']);

	var $PageGrid = $('.gridContainer');
	$PageGrid.newGrid('option', 'buttons', [
		{
			selector          : '.newButton',
			disableIfNone     : false,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				$('#typeSelectPopup').remove();

				var optionsHtml = '';
				$.each(productTypes, function () {
					optionsHtml += '<option value="' + this[0] + '">' + this[1] + '</option>';
				});
				var PopupBlock = $('<div id="typeSelectPopup"></div>')
					.addClass('ui-widget ui-widget-content ui-corner-all')
					.html('<span style="position:absolute;top:.2em;right:.2em;" class="ui-icon ui-icon-closethick"></span>Please Select A Type<br><select><option selected="selected">Please Select</option>' + optionsHtml + '</select>')
					.css({
						position   : 'absolute',
						background : '#cccccc',
						boxShadow  : '0px 3px 4px 0px #CCC',
						padding    : '1em',
						top        : $(this).offset().top + $(this).height(),
						left       : $(this).offset().left
					}).appendTo(document.body);

				if ((PopupBlock.offset().left + PopupBlock.width()) >= $(window).width()){
					PopupBlock.css('left', $(this).offset().left - PopupBlock.width() + $(this).width());
				}

				PopupBlock.find('select').change(function () {
					js_redirect(GridClass.buildAppRedirect('products', 'new_product', null, ['productType=' + $(this).val()]));
				});

				PopupBlock.find('.ui-icon-closethick').click(function () {
					PopupBlock.remove();
				});
			}
		},
		{
			selector          : '.editButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildAppRedirect('products', 'new_product', null, [GridClass.getDataKey() + '=' + GridClass.getSelectedData()]));
			}
		},
		{
			selector          : '.copyButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildAppRedirect('products', 'default', null, ['action=copyProduct', GridClass.getDataKey() + '=' + GridClass.getSelectedData()]));
			}
		},
		{
			selector          : '.invButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				js_redirect(GridClass.buildAppRedirect('products', 'new_product', null, [GridClass.getDataKey() + '=' + GridClass.getSelectedData()]) + '#tab_pricing');
			}
		},
		'delete'
	]);

	$('.setExpander').click(function () {
		if ($(this).hasClass('ui-icon-triangle-1-s')){
			$(this).removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-e');
			$('tr[box_id=' + $(this).parent().parent().attr('infobox_id') + ']').hide();
		}
		else {
			$(this).removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-s');
			$('tr[box_id=' + $(this).parent().parent().attr('infobox_id') + ']').show();
		}
	});
});