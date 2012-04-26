<?php
$_GET['key'] = (isset($_GET['key']) ? $_GET['key'] : 'coreMyStore');

require(sysConfig::getDirFsCatalog() . 'includes/classes/fileSystemBrowser.php');
require(sysConfig::getDirFsAdmin() . 'includes/classes/upload.php');

$appContent = $App->getAppContentFile();
?>