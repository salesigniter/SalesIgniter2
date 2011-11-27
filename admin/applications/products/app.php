<?php
	require(sysConfig::getDirFsCatalog() . 'includes/classes/ProductBase.php');
	require(sysConfig::getDirFsAdmin() . 'includes/classes/upload.php');

	//PurchaseTypeModules::loadModules();

	$appContent = $App->getAppContentFile();
    $infoBoxId = null;
		if (isset($_GET['pID'])){
			$infoBoxId = $_GET['pID'];
		}elseif (isset($_GET['mID'])){
			$infoBoxId = $_GET['mID'];
		}elseif ($action == 'new'){
			$infoBoxId = 'new';
		}
		$App->setInfoBoxId($infoBoxId);
	if (!$App->getPageName() != 'expected' && $App->getPageName() != 'manufacturers'){


		if ($App->getAppPage() == 'new_product'){
			$App->addJavascriptFile('ext/jQuery/ui/jquery.ui.datepicker.js');
			$App->addJavascriptFile('ext/jQuery/external/datepick/jquery.datepick.js');
			$App->addJavascriptFile('ext/jQuery/ui/jquery.ui.tabs.js');
			$App->addJavascriptFile('ext/jQuery/external/autocomplete/jquery.autocomplete.js');
			$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
			$App->addJavascriptFile('ext/jQuery/external/uploadify/swfobject.js');
			$App->addJavascriptFile('ext/jQuery/external/uploadify/jquery.uploadify.js');
			$App->addJavascriptFile('ext/jQuery/external/fancybox/jquery.fancybox.js');
		
			$App->addStylesheetFile('ext/jQuery/external/datepick/css/jquery.datepick.css');
			$App->addStylesheetFile('ext/jQuery/external/uploadify/jquery.uploadify.css');
			$App->addStylesheetFile('ext/jQuery/external/fancybox/jquery.fancybox.css');
		}
	
		require(sysConfig::getDirFsCatalog() . 'includes/classes/currencies.php');
		$currencies = new currencies();
	
		// Ultimate SEO URLs v2.1
		// If the action will affect the cache entries
		if (isset($_GET['action'])){
			if (preg_match('/(insert|update|setflag|setfflag)/', $_GET['action'])) include_once('includes/reset_seo_cache.php');
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