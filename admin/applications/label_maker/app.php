<?php
$appContent = $App->getAppContentFile();

//require(sysConfig::getDirFsAdmin() . 'includes/classes/pdf_labels.php');
require(dirname(__FILE__) . '/classes/labels.php');

$App->addJavascriptFile('ext/jQuery/ui/jquery.ui.labelPrinter.js');
sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
