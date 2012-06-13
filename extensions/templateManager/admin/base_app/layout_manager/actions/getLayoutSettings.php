<?php
$TemplateManagerLayouts = Doctrine_Core::getTable('TemplateManagerLayouts');
if (isset($_GET['layout_id'])){
	$Layout = $TemplateManagerLayouts->find((int) $_GET['layout_id']);
	$PageType = $Layout->page_type;
}else{
	$Layout = $TemplateManagerLayouts->getRecord();
	$PageType = $_GET['layout_type'];
}

$Module = TemplateManagerLayoutTypeModules::getModule($PageType);

EventManager::attachActionResponse($Module->getLayoutSettings($Layout), 'html');
