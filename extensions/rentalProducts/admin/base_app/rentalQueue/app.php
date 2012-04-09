<?php
$appContent = $App->getAppContentFile();
$App->addJavascriptFile('ext/jQuery/ui/jquery.ui.labelPrinter.js');

require(sysConfig::getDirFsCatalog() . 'extensions/rentalProducts/admin/classes/RentalQueueAdmin.php');
$RentalQueue = new RentalQueueAdmin($_GET['cID']);
