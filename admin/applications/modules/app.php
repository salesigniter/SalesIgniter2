<?php
$appContent = $App->getAppContentFile();

$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');

$moduleType = $_GET['moduleType'];
switch($moduleType){
	case 'infobox':
		$accessorClass = 'Infoboxes';
		$headingTitle = sysLanguage::get('HEADING_TITLE_INFOBOX');
		$moduleDirectory = 'infoboxes';
		break;
	case 'purchaseType':
		$accessorClass = 'PurchaseTypeModules';
		$headingTitle = sysLanguage::get('HEADING_TITLE_PURCHASE_TYPE');
		$moduleDirectory = 'purchaseTypeModules';
		break;
	case 'productType':
		$accessorClass = 'ProductTypeModules';
		$headingTitle = sysLanguage::get('HEADING_TITLE_PRODUCT_TYPE');
		$moduleDirectory = 'productTypeModules';
		ProductTypeModules::loadModules();
		break;
	case 'orderShipping':
		$accessorClass = 'OrderShippingModules';
		$headingTitle = sysLanguage::get('HEADING_TITLE_ORDER_SHIPPING');
		$moduleDirectory = 'orderShippingModules';
		break;
	case 'orderTotal':
		$accessorClass = 'OrderTotalModules';
		$headingTitle = sysLanguage::get('HEADING_TITLE_ORDER_TOTAL');
		$moduleDirectory = 'orderTotalModules';
		break;
	case 'orderPayment':
	default:
		$accessorClass = 'OrderPaymentModules';
		$headingTitle = sysLanguage::get('HEADING_TITLE_ORDER_PAYMENT');
		$moduleDirectory = 'orderPaymentModules';
		break;
}
?>