<?php
require(sysConfig::getDirFsCatalog() . 'includes/classes/Order/Base.php');

$appContent = $App->getAppContentFile();
AccountsReceivableModules::loadModules();

sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
