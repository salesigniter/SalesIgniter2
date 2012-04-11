<script>
	$(document).ready(function (){
		$('.insertProductIcon').live('click', function () {
			var $TableBody = $(this).parent().parent().parent().parent().find('tbody');
			var productInput = '';

			var loadProductRow = function (pID) {
				showAjaxLoader($Row, 'normal');
				$.ajax({
					cache : false,
					dataType : 'json',
					url : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=loadProductRow&pID=' + pID),
					success : function (data) {
						removeAjaxLoader($Row);
						if (data.hasError == true){
							alert(data.errorMessage);
						}
						else {
							var html = data.html;
							var $myRow = $(html).insertAfter($Row);
							$Row.remove();
							var va = $myRow.find('.purchaseType option:nth-child(2)').val();
							if ($myRow.find('.purchaseType option').size() == 2){
								$myRow.parent().find('.purchaseType').first().val(va);
								$myRow.parent().find('.purchaseType').first().trigger('change');
							}

						}
					}
				});
			};

			var $Row;
			if ($(this).data('product_entry_method') == 'autosuggest'){
				productInput = '<input class="productSearch" name="product_search" style="width:95%">';
			} else if ($(this).data('product_entry_method') == 'dropmenu'){
				showAjaxLoader($('.productSection'), 'xlarge');
				$.ajax({
					cache : false,
					url : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=getProductsDropBox'),
					dataType : 'html',
					success : function (data) {
						$Row.find('.productInput').html(data);
						$Row.find('.productSelectBox').change(function () {
							loadProductRow($(this).val());
						});
						removeAjaxLoader($('.productSection'));
					}
				});
			}
			$Row = $('<tr></tr>')
				.append('<td class="ui-widget-content" align="right" valign="top" style="border-top:none"></td>')
				.append('<td class="ui-widget-content productInput" valign="top" style="border-top:none;border-left:none">' + productInput + '</td>')
				.append('<td class="ui-widget-content" valign="top" style="border-top:none;border-left:none"></td>')
				.append('<td class="ui-widget-content" valign="top" style="border-top:none;border-left:none"></td>')
				.append('<td class="ui-widget-content" align="right" valign="top" style="border-top:none;border-left:none"></td>')
				.append('<td class="ui-widget-content" align="right" valign="top" style="border-top:none;border-left:none"></td>')
				.append('<td class="ui-widget-content" align="right" valign="top" style="border-top:none;border-left:none"></td>')
				.append('<td class="ui-widget-content" align="right" valign="top" style="border-top:none;border-left:none"></td>')
				.append('<td class="ui-widget-content" align="right" valign="top" style="border-top:none;border-left:none"></td>')
				.append('<td class="ui-widget-content" align="right" valign="top" style="border-top:none;border-left:none"><span class="ui-icon ui-icon-closethick deleteIcon"></span></td>');

			$TableBody.prepend($Row);

			if ($(this).data('product_entry_method') == 'autosuggest'){
				$Row.find('.productSearch').autocomplete({
					source : js_app_link('appExt=orderCreator&app=default&appPage=new&action=findProduct'),
					select : function (e, ui) {
						loadProductRow(ui.item.value);
					}
				});
			}
		});

		$('.deleteProductIcon').live('click', function () {
			var $Row = $(this).parent().parent();
			showAjaxLoader($Row, 'normal');
			$.ajax({
				cache : false,
				dataType : 'json',
				url : js_app_link('rType=ajax&appExt=orderCreator&app=default&appPage=new&action=removeProductRow&id=' + $Row.attr('data-id')),
				success : function (data) {
					removeAjaxLoader($Row);
					$Row.remove();
					$('.priceEx:eq(0)').trigger('keyup');
				}
			});
		});
	});
</script>
<?php
$productsTable = htmlBase::newElement('table')
	->setCellPadding(3)
	->setCellSpacing(0)
	->addClass('productTable')
	->css('width', '100%');

$buttonAdd = htmlBase::newElement('button')
	->addClass('insertProductIcon')
	->attr('data-product_entry_method', sysConfig::get('EXTENSION_ORDER_CREATOR_PRODUCT_FIND_METHOD'))
	->setText('Add Product To Order');

$productTableHeaderColumns = array(
	array(
		'colspan' => 2,
		'text'    => sysLanguage::get('TABLE_HEADING_PRODUCTS')
	),
	array('text' => 'Barcode'),
	array('text' => sysLanguage::get('TABLE_HEADING_PRODUCTS_MODEL')),
	array('text' => sysLanguage::get('TABLE_HEADING_TAX')),
	array('text' => sysLanguage::get('TABLE_HEADING_PRICE_EXCLUDING_TAX')),
	array('text' => sysLanguage::get('TABLE_HEADING_PRICE_INCLUDING_TAX')),
	array('text' => sysLanguage::get('TABLE_HEADING_TOTAL_EXCLUDING_TAX')),
	array('text' => sysLanguage::get('TABLE_HEADING_TOTAL_INCLUDING_TAX')),
	array('text' => $buttonAdd->draw())
);

foreach($productTableHeaderColumns as $i => $cInfo){
	$productTableHeaderColumns[$i]['addCls'] = 'main ui-widget-header';
	if ($i > 0){
		$productTableHeaderColumns[$i]['css'] = array(
			'border-left' => 'none'
		);
	}

	if ($i > 1){
		$productTableHeaderColumns[$i]['align'] = 'right';
	}
}

$productsTable->addHeaderRow(array(
	'columns' => $productTableHeaderColumns
));

foreach($Editor->ProductManager->getContents() as $orderedProduct){
	//$productsName = '<input type="text" style="width:90%" class="ui-widget-content" name="product[' . $orderedProductId . '][name]" value="' . $orderedProduct->getName() . '">';

	$bodyColumns = array(
		array(
			'align' => 'right',
			'text'  => $orderedProduct->getQuantityEdit()
		),
		array('text' => $orderedProduct->getNameEdit()),
		array('text' => $orderedProduct->getBarcodeEdit()),
		array('text' => $orderedProduct->getModel()),
		array(
			'align' => 'right',
			'text'  => $orderedProduct->getTaxRateEdit()
		),
		array(
			'align' => 'right',
			'text'  => $orderedProduct->getPriceEdit()
		),
		array(
			'align' => 'right',
			'text'  => $orderedProduct->getPriceEdit(false, true)
		),
		array(
			'align' => 'right',
			'text'  => $orderedProduct->getPriceEdit(true, false)
		),
		array(
			'align' => 'right',
			'text'  => $orderedProduct->getPriceEdit(true, true)
		),
		array(
			'align' => 'right',
			'text'  => '<span class="ui-icon ui-icon-closethick deleteProductIcon"></span>'
		)
	);

	$sizeOf = sizeof($bodyColumns);
	foreach($bodyColumns as $idx => $colInfo){
		$bodyColumns[$idx]['addCls'] = 'ui-widget-content';
		$bodyColumns[$idx]['valign'] = 'top';
		$bodyColumns[$idx]['css'] = array(
			'border-top' => 'none'
		);

		if ($idx > 0 && $idx < $sizeOf){
			$bodyColumns[$idx]['css']['border-left'] = 'none';
		}
	}
	$bodyColumns[2]['addCls'] .= ' barcodeCol';

	$productsTable->addBodyRow(array(
		'attr'    => array(
			'data-id' => $orderedProduct->getId()
		),
		'columns' => $bodyColumns
	));
}
echo $productsTable->draw();
?>