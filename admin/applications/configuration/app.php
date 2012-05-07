<?php
$_GET['key'] = (isset($_GET['key']) ? $_GET['key'] : 'coreMyStore');

require(sysConfig::getDirFsCatalog() . 'includes/classes/fileSystemBrowser.php');

$appContent = $App->getAppContentFile();

sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
?>