<?php
$_GET['key'] = (isset($_GET['key']) ? $_GET['key'] : 'coreMyStore');

require(sysConfig::getDirFsCatalog() . 'includes/classes/fileSystemBrowser.php');

$appContent = $App->getAppContentFile();

switch($App->getPageName()){
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
	case 'product_listing':
		sysLanguage::set('PAGE_TITLE', 'Product Listing Order');
		break;
	case 'product_sort_listing':
		sysLanguage::set('PAGE_TITLE', 'Product Sort Listing');
		break;
}
?>