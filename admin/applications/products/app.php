<?php
	require(sysConfig::getDirFsAdmin() . 'includes/classes/upload.php');

	//PurchaseTypeModules::loadModules();

	$appContent = $App->getAppContentFile();
    $infoBoxId = null;
		if (isset($_GET['pID'])){
			$infoBoxId = $_GET['pID'];
		}elseif ($action == 'new'){
			$infoBoxId = 'new';
		}
		$App->setInfoBoxId($infoBoxId);
	if (!$App->getPageName() != 'expected'){


		if ($App->getAppPage() == 'new_product'){
			$Product = new Product(
				(isset($_GET['pID']) && empty($_POST) ? $_GET['pID'] : ''),
				true
			);
			if (!isset($_GET['pID']) && isset($_GET['productType'])){
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
			array('id' => 'quantity', 'text' => 'Use Quantity Tracking'),
			array('id' => 'barcode', 'text' => 'Use Barcode Tracking')
		);

		$dir = sysConfig::getDirFsCatalog().'images';
		if (is_dir($dir)){
			if (!is_writeable($dir)){
				$messageStack->add('footerStack', sysLanguage::get('ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE'), 'error');
			}
		}else{
			$messageStack->add('footerStack', sysLanguage::get('ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST'), 'error');
		}
	}
?>