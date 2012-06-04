<?php
require(sysConfig::getDirFsCatalog() . 'includes/classes/template.php');
require(sysConfig::getDirFsCatalog() . 'ext/mpdf/mpdf.php');

$appContent = $App->getAppContentFile();
$TemplateManager = $appExtension->getExtension('templateManager');
