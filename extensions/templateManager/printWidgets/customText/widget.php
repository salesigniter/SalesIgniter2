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

class TemplateManagerPrintWidgetCustomText extends TemplateManagerPrintWidget
{

	public function __construct() {
		global $App;
		$this->init('customText');
	}

	public function showLayoutPreview($WidgetSettings) {
		global $appExtension;
		$return = '';
		if (isset($WidgetSettings['settings']->custom_text) && !empty($WidgetSettings['settings']->custom_text)) {
			$return = $WidgetSettings['settings']->custom_text;
		}
		else {
			$return = $this->getTitle();
		}
		return $return;
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		if (isset($boxWidgetProperties->custom_text) && !empty($boxWidgetProperties->custom_text)) {
			$htmlText = $boxWidgetProperties->custom_text;
		}
		$this->setBoxContent($htmlText);
		return $this->draw();
	}
}

?>