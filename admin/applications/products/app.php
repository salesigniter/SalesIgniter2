<?php
//PurchaseTypeModules::loadModules();

$appContent = $App->getAppContentFile();
switch($App->getPageName()){
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_DEFAULT'));
		break;
	case 'new_product':
		if (isset($_GET['product_id'])){
			sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_EDIT_PRODUCT'));
		}
		else {
			sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_NEW_PRODUCT'));
			$messageStack->add('pageStack', sysLanguage::get('INFO_MESSAGE_NEW_PRODUCT'), 'info');
		}
		break;
	case 'expected':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_EXPECTED'));
		break;
}
if (!$App->getPageName() != 'expected'){

	if ($App->getAppPage() == 'new_product'){
		$Product = new Product(
			(isset($_GET['product_id']) && empty($_POST) ? $_GET['product_id'] : ''),
			true
		);
		if (!isset($_GET['product_id']) && isset($_GET['productType'])){
			$Product->setProductType($_GET['productType']);
		}

		$App->addJavascriptFile('ext/jQuery/external/datepick/jquery.datepick.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
		$App->addJavascriptFile('ext/jQuery/external/fancybox/jquery.fancybox.js');
		$App->addJavascriptFile('ext/dymo_label_framework.js');
		$App->addJavascriptFile('ext/jQuery/ui/jquery.ui.labelPrinter.js');

		$App->addStylesheetFile('ext/jQuery/external/datepick/css/jquery.datepick.css');
		$App->addStylesheetFile('ext/jQuery/external/fancybox/jquery.fancybox.css');

		$ProductType = $Product->getProductTypeClass();
		if (file_exists($ProductType->getPath() . 'admin/applications/products/javascript/new_product.js')){
			$App->addJavascriptFile($ProductType->getRelativePath() . 'admin/applications/products/javascript/new_product.js');
		}
	}

	$trackMethods = array(
		array(
			'id'   => 'quantity',
			'text' => 'Use Quantity Tracking'
		),
		array(
			'id'   => 'barcode',
			'text' => 'Use Barcode Tracking'
		)
	);
}
?>