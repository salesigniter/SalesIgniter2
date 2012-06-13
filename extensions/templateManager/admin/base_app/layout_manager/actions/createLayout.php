<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

function parseElement(&$el, &$parent) {
	global $Layout;

	if (!is_object($parent)){
		$Container = $Layout->Containers->getTable()->create();
		$Layout->Containers->add($Container);
	}
	elseif ($el->hasClass('container')) {
		$Container = $parent->Children->getTable()->create();
		$parent->Children->add($Container);
	}
	else {
		$Container = $parent->Columns->getTable()->create();
		$parent->Columns->add($Container);
	}
	if ($Container->Styles){
		$Container->Styles->clear();
	}
	if ($Container->Configuration){
		$Container->Configuration->clear();
	}
	$Container->sort_order = (int)$el->attr('data-sort_order');

	// process css for id and classes
	if ($el->attr('data-styles')){
		$Styles = json_decode(urldecode($el->attr('data-styles')));
		$InputVals = json_decode(urldecode($el->attr('data-inputs')));

		foreach($Styles as $k => $v){
			if ($k == 'boxShadow'){
				continue;
			}
			if (substr($k, 0, 10) == 'background'){
				continue;
			}

			$Style = $Container->Styles->getTable()->create();
			$Style->definition_key = $k;
			if (is_array($v) || is_object($v)){
				$Style->definition_value = json_encode($v);
			}
			else {
				$Style->definition_value = $v;
			}
			$Container->Styles->add($Style);
		}

		if (!empty($InputVals)){
			foreach($InputVals as $k => $v){
				if ($k == 'boxShadow'){
					continue;
				}

				$Configuration = $Container->Configuration->getTable()->create();
				$Configuration->configuration_key = $k;
				if (is_array($v) || is_object($v)){
					$Configuration->configuration_value = json_encode($v);
				}
				else {
					$Configuration->configuration_value = $v;
				}
				$Container->Configuration->add($Configuration);
			}
		}
	}

	foreach($el->children() as $child){
		$childObj = pq($child);
		if ($childObj->is('ul')){
		}
		else {
			$newParent = ($el->hasClass('column') ? null : (isset($Container) ? $Container : null));
			parseElement($childObj, $newParent);
		}
	}
}

$TemplateLayouts = Doctrine_Core::getTable('TemplateManagerLayouts');

if (isset($_GET['layout_id'])){
	$Layout = $TemplateLayouts->find((int)$_GET['layout_id']);
}
else {
	$Layout = $TemplateLayouts->create();
	$Layout->template_id = (int)$_GET['template_id'];
}
$Layout->layout_name = $_POST['layoutName'];
$Layout->layout_type = $_POST['layoutType'];
$Layout->page_type = $_POST['pageType'];

$Module = TemplateManagerLayoutTypeModules::getModule($Layout->page_type);
if (isset($_POST['layout_template'])){
	$TemplateLayoutSource = file_get_contents($Module->getStartingLayoutPath() . $_POST['layout_template'] . '/layout_content_source.php');
	if ($Module->hasSetWidth()){
		$TemplateLayoutSource = str_replace('{$LAYOUT_WIDTH}', $Module->getMaxWidth(), $TemplateLayoutSource);
	}

	$TemplateLayout = phpQuery::newDocumentHTML($TemplateLayoutSource);
	foreach($TemplateLayout->children() as $child){
		$childObj = pq($child);
		$parent = null;
		parseElement($childObj, $parent);
	}
}

$Module->onSave();

//echo '<pre>';print_r($Layout->toArray(true));itwExit();
$Layout->save();

EventManager::attachActionResponse(array(
	'success'    => true,
	'layoutId'   => $Layout->layout_id,
	'layoutName' => $Layout->layout_name,
	'layoutType' => $Layout->layout_type
), 'json');
