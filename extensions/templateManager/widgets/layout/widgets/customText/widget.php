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

class TemplateManagerWidgetCustomText extends TemplateManagerWidget
{

	public function __construct() {
		global $App;
		$this->init('customText');
	}

	public function showLayoutPreview($WidgetSettings) {
		global $appExtension;
		$return = '';
		$infoPageExt = $appExtension->getExtension('infoPages');
		if ($infoPageExt && isset($WidgetSettings['settings']->selected_page) && !empty($WidgetSettings['settings']->selected_page)){
			$return = $infoPageExt->displayContentBlock($WidgetSettings['settings']->selected_page);
		}
		elseif (isset($WidgetSettings['settings']->custom_text) && !empty($WidgetSettings['settings']->custom_text)) {
			$return = $WidgetSettings['settings']->custom_text;
		}
		else {
			$return = $this->getTitle();
		}
		return $return;
	}

	public function show() {
		global $appExtension;
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		$infoPageExt = $appExtension->getExtension('infoPages');
		if ($infoPageExt && isset($boxWidgetProperties->selected_page) && !empty($boxWidgetProperties->selected_page)){
			$htmlText = $infoPageExt->displayContentBlock($boxWidgetProperties->selected_page);
		}
		elseif (isset($boxWidgetProperties->custom_text) && !empty($boxWidgetProperties->custom_text)) {
			$htmlText = $boxWidgetProperties->custom_text;
		}
		$this->setBoxContent($htmlText);
		if ($this->getBoxHeading() == ''){
			//$this->setBoxHeading($htmlPage['PagesDescription'][Session::get('languages_id')]['pages_title']);
		}
		return $this->draw();
	}
}

?>