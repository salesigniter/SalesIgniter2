<?php
$Editor->addProduct($_GET['pID']);

if ($Editor->hasErrors() === false){
	$html = '<tr data-product_id="' . (int)$_GET['pID'] . '" data-id="' . $OrderProduct->getId() . '">' .
		'<td class="ui-widget-content" valign="top" align="right" style="border-top:none;">' . $OrderProduct->getQuantityEdit() . '</td>' .
		'<td class="ui-widget-content" valign="top" style="border-top:none;border-left:none;">' . $OrderProduct->getNameEdit() . '</td>' .
		'<td class="ui-widget-content barcodeCol" valign="top" style="border-top:none;border-left:none;">' . $OrderProduct->getBarcodeEdit() . '</td>' .
		'<td class="ui-widget-content" valign="top" style="border-top:none;border-left:none;">' . $OrderProduct->getModel() . '</td>' .
		'<td class="ui-widget-content" valign="top" align="right" style="border-top:none;border-left:none;">' . $OrderProduct->getTaxRateEdit() . '</td>' .
		'<td class="ui-widget-content" valign="top" align="right" style="border-top:none;border-left:none;">' . $OrderProduct->getPriceEdit() . '</td>' .
		'<td class="ui-widget-content" valign="top" align="right" style="border-top:none;border-left:none;">' . $OrderProduct->getPriceEdit(false, true) . '</td>' .
		'<td class="ui-widget-content" valign="top" align="right" style="border-top:none;border-left:none;">' . $OrderProduct->getPriceEdit(true, false) . '</td>' .
		'<td class="ui-widget-content" valign="top" align="right" style="border-top:none;border-left:none;">' . $OrderProduct->getPriceEdit(true, true) . '</td>' .
		'<td class="ui-widget-content" valign="top" align="right" style="border-top:none;border-left:none;"><span class="ui-icon ui-icon-closethick deleteProductIcon"></span></td>' .
		'</tr>';

	$response = array(
		'success' => true,
		'hasError' => false,
		'html' => $html
	);
}
else {
	$response = array(
		'success' => true,
		'hasError' => true,
		'errorMessage' => $Editor->getErrors()
	);
}

$Editor->getSaleModule()->saveProgress($Editor);

EventManager::attachActionResponse($response, 'json');
